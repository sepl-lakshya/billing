import { Request, Response, NextFunction } from 'express';
import jwt, { JwtHeader, SigningKeyCallback } from 'jsonwebtoken';
import { JwksClient } from 'jwks-rsa';
import { config } from '../config';
import { query, execute, queryOne } from '../db';
import { fail } from '../utils/helpers';
import { logger } from '../utils/logger';

/** Identity attached to every request. */
export interface AuthUser {
  id: number;            // local users.id (auto-provisioned for Entra users)
  email: string;
  name: string;
  roles: string[];       // e.g. ['admin'] or ['user']
  oid?: string;          // Entra object id
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
// Middleware
// ---------------------------------------------------------------------------

/**
 * Authenticate every API request.
 *   AUTH_MODE=none  -> attach the system identity (open access, dev/local).
 *   AUTH_MODE=entra -> require a valid Entra ID bearer token.
 */
export async function authenticate(req: Request, res: Response, next: NextFunction) {
  if (config.auth.mode !== 'entra') {
    req.user = SYSTEM_USER;
    return next();
  }

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
