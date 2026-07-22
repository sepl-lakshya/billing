import axios, { AxiosError } from 'axios';

// When VITE_API_URL is set (production), use it; otherwise rely on the Vite dev
// proxy that forwards /api to the backend.
const baseURL = import.meta.env.VITE_API_URL || '';

export const http = axios.create({
  baseURL,
  headers: { 'Content-Type': 'application/json' },
  timeout: 60_000,
});

// ---------------------------------------------------------------------------
// Auth plumbing (Microsoft Entra ID-ready).
// When auth is enabled, call setTokenProvider(() => msalInstance.acquireToken...)
// once at startup; every request will then carry a Bearer token.
// ---------------------------------------------------------------------------
let tokenProvider: (() => Promise<string | null>) | null = null;

export function setTokenProvider(provider: () => Promise<string | null>) {
  tokenProvider = provider;
}

http.interceptors.request.use(async (req) => {
  if (tokenProvider) {
    try {
      const token = await tokenProvider();
      if (token) req.headers.Authorization = `Bearer ${token}`;
    } catch {
      /* proceed unauthenticated; backend will 401 */
    }
  }
  return req;
});

// ---------------------------------------------------------------------------
// Error normalization: guarantee err.response.data.msg is always a friendly,
// human-readable message so page-level catch blocks can show it directly.
// ---------------------------------------------------------------------------
http.interceptors.response.use(
  (res) => res,
  (err: AxiosError<any>) => {
    let msg: string;
    if (err.code === 'ECONNABORTED') {
      msg = 'The request timed out — please try again';
    } else if (!err.response) {
      msg = 'Cannot reach the server — check that the backend is running';
    } else if (err.response.status === 401) {
      msg = err.response.data?.msg || 'Your session has expired — please sign in again';
    } else if (err.response.status === 403) {
      msg = err.response.data?.msg || 'You do not have permission for this action';
    } else if (err.response.status === 429) {
      msg = 'Too many requests — please wait a moment';
    } else {
      msg = err.response.data?.msg || `Server error (${err.response.status})`;
    }
    (err as any).response = err.response ?? { data: {} };
    (err as any).response.data = { ...(err as any).response.data, status: 0, msg };
    return Promise.reject(err);
  }
);

export interface ApiResult<T = any> {
  status: 0 | 1;
  msg: string;
  data?: T;
}

/** GET and return the `data` payload (throws on network error). */
export async function apiGet<T = any>(url: string): Promise<T> {
  const res = await http.get<ApiResult<T>>(`/api${url}`);
  return res.data.data as T;
}

/** GET and return the full envelope. */
export async function apiGetResult<T = any>(url: string): Promise<ApiResult<T>> {
  const res = await http.get<ApiResult<T>>(`/api${url}`);
  return res.data;
}

/** POST/PUT/DELETE and return the full { status, msg, data } envelope. */
export async function apiSend<T = any>(
  method: 'post' | 'put' | 'delete',
  url: string,
  body?: any
): Promise<ApiResult<T>> {
  const res = await http.request<ApiResult<T>>({ method, url: `/api${url}`, data: body });
  return res.data;
}

export const apiPost = <T = any>(url: string, body?: any) => apiSend<T>('post', url, body);
export const apiPut = <T = any>(url: string, body?: any) => apiSend<T>('put', url, body);
export const apiDelete = <T = any>(url: string, body?: any) => apiSend<T>('delete', url, body);

/** Multipart upload helper (attachments). */
export async function apiUpload<T = any>(url: string, form: FormData): Promise<ApiResult<T>> {
  const res = await http.post<ApiResult<T>>(`/api${url}`, form, {
    headers: { 'Content-Type': 'multipart/form-data' },
  });
  return res.data;
}
