/**
 * Central SSO + env-driven navigation helpers.
 *
 * Everything is derived from build-time env (VITE_*). NOTHING is hardcoded, so
 * the same code targets demo (demo.surbhi.net) or production (apps.surbhi.net)
 * purely via `frontend/.env.<mode>`.
 */
const stripTrailingSlash = (s: string) => s.replace(/\/+$/, '');

export const authMode = (import.meta.env.VITE_AUTH_MODE || 'none').toLowerCase();

/** Origin of the central sign-in / dashboard (e.g. https://demo.surbhi.net). */
export const centralOrigin = stripTrailingSlash(import.meta.env.VITE_CENTRAL_LOGIN_ORIGIN || '');

/** Where the "Home" button goes (defaults to the central dashboard). */
export const homeUrl =
  stripTrailingSlash(import.meta.env.VITE_HOME_URL || (centralOrigin ? `${centralOrigin}/dashboard` : '')) ||
  (typeof window !== 'undefined' ? window.location.origin : '');

/** True only when central SSO is switched on AND an origin is configured. */
export const isCentralAuth = authMode === 'central' && !!centralOrigin;

function isAllowedReturnUrl(candidate: string): boolean {
  try {
    const u = new URL(candidate);
    if (u.protocol === 'https:' && (u.hostname === 'surbhi.net' || u.hostname.endsWith('.surbhi.net'))) return true;
    if (u.hostname === 'localhost' || u.hostname === '127.0.0.1') return true;
    return false;
  } catch {
    return false;
  }
}

/** Full central login URL that returns here after sign-in. */
export function centralLoginUrl(next: string = typeof window !== 'undefined' ? window.location.href : ''): string {
  const safeNext = isAllowedReturnUrl(next) ? next : centralOrigin;
  return `${centralOrigin}/login?next=${encodeURIComponent(safeNext)}`;
}

/** Central logout URL (clears the shared session at the source). */
export function centralLogoutUrl(): string {
  return `${centralOrigin}/login?logged_out=1`;
}

/** Navigate the browser to the central home / dashboard. */
export function goHome(): void {
  if (typeof window !== 'undefined') window.location.href = homeUrl;
}
