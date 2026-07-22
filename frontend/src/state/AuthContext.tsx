import React, { createContext, useContext, useEffect, useState, ReactNode } from 'react';
import { http, setTokenProvider } from '../api/client';
import { logger } from '../utils/logger';
import { getMsal, isEntraEnabled, loginRequest, apiTokenRequest } from '../lib/authConfig';

interface User {
  id: number;
  email: string;
  name: string;
  roles: string[];
}

interface AuthContextType {
  user: User | null;
  loading: boolean;
  isAdmin: boolean;
  logout: () => void;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // On startup, establish auth (Entra when enabled) then fetch identity.
    async function init() {
      try {
        // When Entra ID is enabled, establish an MSAL session first and wire
        // the token provider so every API call carries a fresh bearer token.
        if (isEntraEnabled) {
          const msal = await getMsal();
          if (msal) {
            const redirect = await msal.handleRedirectPromise();
            if (redirect?.account) msal.setActiveAccount(redirect.account);

            const account = msal.getActiveAccount() ?? msal.getAllAccounts()[0] ?? null;
            if (!account) {
              // No session yet → full-page redirect to Microsoft sign-in.
              await msal.loginRedirect(loginRequest);
              return; // browser navigates away; nothing more to do here
            }
            msal.setActiveAccount(account);

            setTokenProvider(async () => {
              const active = msal.getActiveAccount();
              if (!active) return null;
              try {
                const res = await msal.acquireTokenSilent({ ...apiTokenRequest, account: active });
                return res.accessToken;
              } catch {
                // Silent acquisition failed (consent/expiry) → interactive.
                await msal.acquireTokenRedirect(apiTokenRequest);
                return null;
              }
            });
          }
        }

        const res = await http.get('/api/users/me');
        if (res.data?.status === 1) {
          setUser(res.data.data);
        }
      } catch (err: any) {
        if (err.response?.status === 401) {
          // Auth required but not available; user stays null.
          logger.debug('Not authenticated (401 on /me)');
        } else {
          logger.error('Failed to fetch current identity', { msg: err.message });
        }
      } finally {
        setLoading(false);
      }
    }
    init();

    // On a 401 error, clear the user (token refresh is handled per-request).
    http.interceptors.response.use(
      (res) => res,
      (err: any) => {
        if (err?.response?.status === 401) {
          setUser(null);
        }
        return Promise.reject(err);
      }
    );
  }, []);

  const logout = () => {
    setUser(null);
    if (isEntraEnabled) {
      getMsal()
        .then((m) => m?.logoutRedirect())
        .catch(() => {});
    }
  };

  return (
    <AuthContext.Provider value={{ user, loading, isAdmin: user?.roles?.includes('admin') ?? false, logout }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used within AuthProvider');
  return ctx;
}
