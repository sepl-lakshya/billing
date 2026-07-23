import dotenv from 'dotenv';
import fs from 'fs';
import path from 'path';

/**
 * Environment-specific configuration.
 *
 * NODE_ENV = runtime class (development | production) — controls hardening.
 * APP_ENV  = deployment target (demo | production) — selects which .env file
 *            and which domain/URLs to use. Defaults to NODE_ENV when unset.
 *
 * Load order (first value found wins per variable):
 *   1. real environment variables (set by PM2 / the deployment platform)
 *   2. .env.local          (per-VM secret overrides — git-ignored, never committed)
 *   3. .env.<APP_ENV>      (e.g. .env.demo, .env.production — committed, no secrets)
 *   4. .env.<NODE_ENV>     (runtime-class defaults)
 *   5. .env                (shared defaults)
 */
const nodeEnv = (process.env.NODE_ENV || 'development').toLowerCase();
const appEnv = (process.env.APP_ENV || nodeEnv).toLowerCase();
const loaded = new Set<string>();
for (const file of ['.env.local', `.env.${appEnv}`, `.env.${nodeEnv}`, '.env']) {
  if (loaded.has(file)) continue;
  loaded.add(file);
  const p = path.resolve(process.cwd(), file);
  if (fs.existsSync(p)) dotenv.config({ path: p });
}

const env = appEnv;
const isProd = nodeEnv === 'production';

function toInt(v: string | undefined, fallback: number): number {
  const n = parseInt(v || '', 10);
  return Number.isFinite(n) ? n : fallback;
}

export const config = {
  env,
  nodeEnv,
  isProd,
  port: toInt(process.env.PORT, 4000),
  db: {
    host: process.env.DB_HOST || 'localhost',
    port: toInt(process.env.DB_PORT, 3306),
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'billing',
    connectionLimit: toInt(process.env.DB_POOL_SIZE, 10),
  },
  corsOrigin: (process.env.CORS_ORIGIN || 'http://localhost:5173')
    .split(',')
    .map((s) => s.trim())
    .filter(Boolean),
  uploadDir: path.resolve(process.cwd(), process.env.UPLOAD_DIR || 'uploads'),
  frontendDistDir: path.resolve(process.cwd(), process.env.FRONTEND_DIST || '../frontend/dist'),
  maxUploadMb: toInt(process.env.MAX_UPLOAD_MB, 30),
  logDir: path.resolve(process.cwd(), process.env.LOG_DIR || 'logs'),
  logLevel: (process.env.LOG_LEVEL || (isProd ? 'info' : 'debug')) as 'debug' | 'info' | 'warn' | 'error',
  rateLimit: {
    windowMs: toInt(process.env.RATE_LIMIT_WINDOW_MS, 60_000),
    max: toInt(process.env.RATE_LIMIT_MAX, isProd ? 300 : 10_000),
  },
  /**
   * Authentication / access control.
   *   AUTH_MODE=none  -> no auth (current behaviour); every request acts as the
   *                      system user with the admin role.
   *   AUTH_MODE=entra -> validate Microsoft Entra ID (Azure AD) bearer tokens.
   *                      Users are auto-provisioned into the `users` table and
   *                      roles come from the token's `roles` claim (app roles).
   */
  auth: {
    mode: (process.env.AUTH_MODE || 'none').toLowerCase() as 'none' | 'entra' | 'central',
    entra: {
      tenantId: process.env.ENTRA_TENANT_ID || process.env.AZURE_TENANT_ID || '',
      clientId: process.env.ENTRA_CLIENT_ID || process.env.AZURE_CLIENT_ID || '', // audience of the API app registration
      // Entra app-role value that maps to the local "admin" role.
      adminRole: process.env.ENTRA_ADMIN_ROLE || process.env.AZURE_ADMIN_ROLE || 'Billing.Admin',
    },
    /**
     * Central SSO (AUTH_MODE=central): the SEPL suite's shared sign-in. Users
     * authenticate once at CENTRAL_LOGIN_ORIGIN (Microsoft) and receive a shared
     * `spm_token` JWT cookie on *.surbhi.net that every portal trusts. Access is
     * governed by the central `user_permissions` table. Nothing is hardcoded.
     */
    central: {
      loginOrigin: (process.env.CENTRAL_LOGIN_ORIGIN || '').replace(/\/+$/, ''),
      // Central portal DB (same MySQL server) holding users + user_permissions.
      authDbName: process.env.AUTH_DB_NAME || 'easyreminder',
      // Candidate signing secrets for the shared spm_token. The signature is
      // ALWAYS verified (forged tokens rejected); multiple candidates only
      // tolerate secret drift so a validly-signed central token is never
      // wrongly rejected.
      jwtSecrets: Array.from(
        new Set(
          [
            process.env.JWT_SECRET,
            'your-super-secret-key-change-in-production',
            'testing-surbhi-main-jwt-secret-2026',
          ].filter((v): v is string => Boolean(v))
        )
      ),
    },
  },
  // Fallback identity when AUTH_MODE=none.
  systemUserId: toInt(process.env.SYSTEM_USER_ID, 1),
};

/** Validate configuration at startup; throws in production, warns in dev. */
export function validateConfig(): string[] {
  const problems: string[] = [];
  if (config.auth.mode === 'entra') {
    if (!config.auth.entra.tenantId) problems.push('AUTH_MODE=entra requires ENTRA_TENANT_ID');
    if (!config.auth.entra.clientId) problems.push('AUTH_MODE=entra requires ENTRA_CLIENT_ID');
  }
  if (config.auth.mode === 'central' && !config.auth.central.loginOrigin) {
    problems.push('AUTH_MODE=central requires CENTRAL_LOGIN_ORIGIN');
  }
  if (isProd) {
    if (!process.env.CORS_ORIGIN) problems.push('CORS_ORIGIN should be set explicitly in production');
    if (config.auth.mode === 'none') problems.push('AUTH_MODE=none in production — anyone can access the API');
  }
  return problems;
}
