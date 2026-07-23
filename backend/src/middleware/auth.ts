import { Request, Response, NextFunction } from 'express';
import jwt, { JwtHeader, SigningKeyCallback } from 'jsonwebtoken';
import { JwksClient } from 'jwks-rsa';
import { config } from '../config';
import { query, execute, queryOne } from '../db';
import { fail } from '../utils/helpers';
import { logger } from '../utils/logger';

/** Central-SSO permission flags relevant to the billing portal. */
export interface BillingPermissions {
  is_admin?: boolean;
  can_view_billing_portal?: boolean;
  can_create_billing_portal?: boolean;
  can_delete_billing_portal?: boolean;
}

/** Identity attached to every request. */
export interface AuthUser {
  id: number;            // local users.id (auto-provisioned for Entra/central users)
  email: string;
  name: string;
  roles: string[];       // e.g. ['admin'] or ['user']
  oid?: string;          // external object id (Entra oid / central user id)
  permissions?: BillingPermissions; // central-SSO access flags
}

declare global {
  // eslint-disable-next-line @typescript-eslint/no-namespace
  namespace Express {
    interface Request {
      user?: AuthUser;
    }
  }
}

const SYSTEM_USER: AuthUser = {
  id: config.systemUserId,
  email: 'system@local',
  name: 'System',
  roles: ['admin'],
  permissions: {
    is_admin: true,
    can_view_billing_portal: true,
    can_create_billing_portal: true,
    can_delete_billing_portal: true,
  },
};

// ---------------------------------------------------------------------------
// Microsoft Entra ID (Azure AD) token validation
// ---------------------------------------------------------------------------
const jwks = config.auth.mode === 'entra'
  ? new JwksClient({
      jwksUri: `https://login.microsoftonline.com/${config.auth.entra.tenantId}/discovery/v2.0/keys`,
      cache: true,
      cacheMaxAge: 60 * 60 * 1000, // 1h
      rateLimit: true,
    })
  : null;

function getKey(header: JwtHeader, callback: SigningKeyCallback) {
  if (!jwks || !header.kid) return callback(new Error('No signing key'));
  jwks.getSigningKey(header.kid, (err, key) => {
    if (err) return callback(err);
    callback(null, key?.getPublicKey());
  });
}

function verifyEntraToken(token: string): Promise<jwt.JwtPayload> {
  const { tenantId, clientId } = config.auth.entra;
  return new Promise((resolve, reject) => {
    jwt.verify(
      token,
      getKey,
      {
        audience: [clientId, `api://${clientId}`],
        issuer: [
          `https://login.microsoftonline.com/${tenantId}/v2.0`, // v2 tokens
          `https://sts.windows.net/${tenantId}/`,               // v1 tokens
        ],
        algorithms: ['RS256'],
      },
      (err, decoded) => (err ? reject(err) : resolve(decoded as jwt.JwtPayload))
    );
  });
}

/** In-memory cache: Entra oid -> local user, to avoid a DB hit per request. */
const userCache = new Map<string, { user: AuthUser; expires: number }>();
const USER_CACHE_MS = 5 * 60 * 1000;

/** Find-or-create the local user row for an Entra identity. */
async function provisionUser(payload: jwt.JwtPayload): Promise<AuthUser> {
  const oid = String(payload.oid || payload.sub || '');
  const email = String(payload.preferred_username || payload.email || payload.upn || '').toLowerCase();
  const name = String(payload.name || email || 'Entra User');
  const tokenRoles: string[] = Array.isArray(payload.roles) ? payload.roles.map(String) : [];
  const isAdmin = tokenRoles.includes(config.auth.entra.adminRole);
  const roles = isAdmin ? ['admin'] : ['user'];

  const cached = userCache.get(oid);
  if (cached && cached.expires > Date.now()) {
    return { ...cached.user, roles }; // roles always come fresh from the token
  }

  let row = email
    ? await queryOne('SELECT id, full_name, is_active FROM users WHERE email = ? LIMIT 1', [email])
    : undefined;

  if (!row) {
    const r = await execute(
      'INSERT INTO users (hash, email, username, full_name, user_type, is_active) VALUES (?,?,?,?,?,1)',
      [oid.slice(0, 60), email, email, name, isAdmin ? 1 : 2]
    );
    row = { id: r.insertId, full_name: name, is_active: 1 };
    logger.info('Auto-provisioned Entra user', { email, id: r.insertId });
  } else if (!row.is_active) {
    throw Object.assign(new Error('Your account has been deactivated. Contact an administrator.'), { statusCode: 403 });
  }

  const user: AuthUser = { id: row.id, email, name: row.full_name || name, roles, oid };
  userCache.set(oid, { user, expires: Date.now() + USER_CACHE_MS });
  return user;
}

// ---------------------------------------------------------------------------
// Central SSO (shared `spm_token`) — the SEPL suite's single sign-on.
// Users authenticate once at the central portal (CENTRAL_LOGIN_ORIGIN) and get
// a JWT in the `spm_token` cookie on *.surbhi.net. Access rights live in the
// central `user_permissions` table (read per identity, cached briefly).
// ---------------------------------------------------------------------------

/** Pull the shared SSO token from the Authorization header or the spm_token cookie. */
function extractCentralToken(req: Request): string | null {
  const header = req.headers.authorization || '';
  if (header.startsWith('Bearer ')) return header.slice(7);
  for (const c of (req.headers.cookie || '').split('; ')) {
    if (c.startsWith('spm_token=')) return decodeURIComponent(c.slice('spm_token='.length));
  }
  return null;
}

/** Verify the shared token's signature against the known central secrets. */
function verifyCentralToken(token: string): jwt.JwtPayload | null {
  for (const secret of config.auth.central.jwtSecrets) {
    try {
      return jwt.verify(token, secret) as jwt.JwtPayload;
    } catch {
      /* try the next candidate secret */
    }
  }
  return null;
}

const truthy = (v: any) => v === 1 || v === true || v === '1';

/**
 * Resolve a verified central identity into a billing AuthUser:
 *  - reads authoritative permissions from `<authDb>.user_permissions`
 *  - find-or-creates a local billing `users` row (keeps created_by valid)
 * Cached for USER_CACHE_MS to avoid a per-request DB round-trip.
 */
async function resolveCentralUser(payload: jwt.JwtPayload): Promise<AuthUser> {
  const centralId = payload.id ?? payload.sub;
  const email = String(payload.email || payload.preferred_username || payload.upn || '').toLowerCase();
  const name = String(payload.name || email || 'User');
  const cacheKey = String(payload.oid || centralId || email);

  const cached = userCache.get(cacheKey);
  if (cached && cached.expires > Date.now()) return cached.user;

  const authDb = config.auth.central.authDbName;
  // Authoritative permissions from the central portal's DB (same MySQL server).
  let permRow: any;
  if (centralId !== undefined && centralId !== null && String(centralId) !== '') {
    permRow = await queryOne(
      `SELECT can_view_billing_portal, can_create_billing_portal, can_delete_billing_portal, is_admin
       FROM ${authDb}.user_permissions WHERE user_id = ? LIMIT 1`,
      [centralId]
    );
  }
  if (!permRow && email) {
    permRow = await queryOne(
      `SELECT p.can_view_billing_portal, p.can_create_billing_portal, p.can_delete_billing_portal, p.is_admin
       FROM ${authDb}.users u
       JOIN ${authDb}.user_permissions p ON p.user_id = u.id
       WHERE LOWER(u.email) = LOWER(?) LIMIT 1`,
      [email]
    );
  }

  const permissions: BillingPermissions = {
    is_admin: truthy(permRow?.is_admin),
    can_view_billing_portal: truthy(permRow?.can_view_billing_portal),
    can_create_billing_portal: truthy(permRow?.can_create_billing_portal),
    can_delete_billing_portal: truthy(permRow?.can_delete_billing_portal),
  };
  const roles = permissions.is_admin ? ['admin'] : ['user'];

  // Find-or-create a local billing user so created_by references resolve.
  let row = email
    ? await queryOne('SELECT id, full_name, is_active FROM users WHERE email = ? LIMIT 1', [email])
    : undefined;
  if (!row) {
    const r = await execute(
      'INSERT INTO users (hash, email, username, full_name, user_type, is_active) VALUES (?,?,?,?,?,1)',
      [cacheKey.slice(0, 60), email, email, name, permissions.is_admin ? 1 : 2]
    );
    row = { id: r.insertId, full_name: name, is_active: 1 };
    logger.info('Auto-provisioned central-SSO user', { email, id: r.insertId });
  }

  const user: AuthUser = { id: row.id, email, name: row.full_name || name, roles, oid: cacheKey, permissions };
  userCache.set(cacheKey, { user, expires: Date.now() + USER_CACHE_MS });
  return user;
}

// ---------------------------------------------------------------------------
// Middleware
// ---------------------------------------------------------------------------

/**
 * Authenticate every API request.
 *   AUTH_MODE=none    -> attach the system identity (open access, dev/local).
 *   AUTH_MODE=central -> trust the suite's shared spm_token (cookie or bearer).
 *   AUTH_MODE=entra   -> require a valid Entra ID bearer token.
 */
export async function authenticate(req: Request, res: Response, next: NextFunction) {
  // Open mode: every request acts as the system (admin) identity.
  if (config.auth.mode === 'none') {
    req.user = SYSTEM_USER;
    return next();
  }

  // Central SSO: trust the shared spm_token (cookie or bearer) from the suite.
  if (config.auth.mode === 'central') {
    const token = extractCentralToken(req);
    if (!token) return res.status(401).json(fail('Authentication required'));
    const payload = verifyCentralToken(token);
    if (!payload || !(payload.id ?? payload.sub ?? payload.email)) {
      return res.status(401).json(fail('Invalid or expired session'));
    }
    try {
      req.user = await resolveCentralUser(payload);
      return next();
    } catch (err: any) {
      logger.warn('Central auth failed', { id: req.requestId, reason: err?.message });
      return res.status(401).json(fail('Invalid or expired session'));
    }
  }

  // Entra ID bearer-token mode.
  const header = req.headers.authorization || '';
  const token = header.startsWith('Bearer ') ? header.slice(7) : '';
  if (!token) {
    return res.status(401).json(fail('Authentication required'));
  }

  try {
    const payload = await verifyEntraToken(token);
    req.user = await provisionUser(payload);
    next();
  } catch (err: any) {
    if (err?.statusCode === 403) {
      return res.status(403).json(fail(err.message));
    }
    logger.warn('Token validation failed', { id: req.requestId, reason: err?.message });
    return res.status(401).json(fail('Invalid or expired token'));
  }
}

/** Role guard: use after authenticate. e.g. router.delete('/', requireRole('admin'), ...) */
export function requireRole(...roles: string[]) {
  return (req: Request, res: Response, next: NextFunction) => {
    const user = req.user;
    if (!user) return res.status(401).json(fail('Authentication required'));
    if (!roles.some((r) => user.roles.includes(r))) {
      return res.status(403).json(fail('You do not have permission to perform this action'));
    }
    next();
  };
}

/** Clear the user cache (used when a user is edited/deactivated). */
export function invalidateUserCache() {
  userCache.clear();
}

/**
 * Central-SSO access control for the billing portal (no-op in other modes).
 *   GET / HEAD       -> can_view_billing_portal
 *   POST / PUT/PATCH -> can_create_billing_portal
 *   DELETE           -> can_delete_billing_portal
 * `is_admin` bypasses everything, and `/users/me` is always allowed so the SPA
 * can render an "access denied" screen for signed-in users without access.
 */
export function authorizeBilling(req: Request, res: Response, next: NextFunction) {
  if (config.auth.mode !== 'central') return next();
  const user = req.user;
  if (!user) return res.status(401).json(fail('Authentication required'));
  const perms = user.permissions || {};
  if (perms.is_admin) return next();
  if (req.path === '/users/me') return next();

  const method = req.method.toUpperCase();
  let allowed: boolean;
  if (method === 'GET' || method === 'HEAD') allowed = !!perms.can_view_billing_portal;
  else if (method === 'DELETE') allowed = !!perms.can_delete_billing_portal;
  else allowed = !!perms.can_create_billing_portal; // POST / PUT / PATCH

  if (!allowed) {
    return res.status(403).json(fail('You do not have permission to access the billing portal'));
  }
  next();
}
