import { PublicClientApplication, type Configuration } from '@azure/msal-browser';

/**
 * Microsoft Entra ID (Azure AD) sign-in — configuration & lazy MSAL bootstrap.
 *
 * All values come from build-time env (`VITE_*`), so there are NO hardcoded
 * URLs or tenants. Entra is completely DORMANT unless it is both switched on
 * (VITE_AUTH_MODE=entra) AND configured (tenant + client id present), which
 * keeps the app fully usable in open mode (AUTH_MODE=none) until you flip it.
 */
const authMode = (import.meta.env.VITE_AUTH_MODE || 'none').toLowerCase();
const tenantId = import.meta.env.VITE_ENTRA_TENANT_ID || '';
const clientId = import.meta.env.VITE_ENTRA_CLIENT_ID || '';
const apiScope = import.meta.env.VITE_ENTRA_API_SCOPE || '';

/** True only when Entra sign-in is turned on AND fully configured. */
export const isEntraEnabled = authMode === 'entra' && !!tenantId && !!clientId;

const msalConfig: Configuration = {
  auth: {
    clientId,
    authority: `https://login.microsoftonline.com/${tenantId}`,
    redirectUri: window.location.origin,
    postLogoutRedirectUri: window.location.origin,
  },
  cache: {
    cacheLocation: 'localStorage',
  },
};

/** Scopes for interactive sign-in. Falls back to OIDC scopes if no API scope. */
export const loginRequest = {
  scopes: apiScope ? [apiScope] : ['openid', 'profile', 'email'],
};

/** Scopes used to silently acquire the API access token per request. */
export const apiTokenRequest = {
  scopes: apiScope ? [apiScope] : ['openid', 'profile', 'email'],
};

let instance: PublicClientApplication | null = null;
let initPromise: Promise<PublicClientApplication> | null = null;

/**
 * Lazily create and initialize the MSAL instance. Returns null when Entra is
 * disabled, so callers can safely `await getMsal()` in any auth mode.
 */
export async function getMsal(): Promise<PublicClientApplication | null> {
  if (!isEntraEnabled) return null;
  if (instance) return instance;
  if (!initPromise) {
    const app = new PublicClientApplication(msalConfig);
    initPromise = app.initialize().then(() => {
      instance = app;
      return app;
    });
  }
  return initPromise;
}
