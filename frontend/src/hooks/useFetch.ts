import { useCallback, useEffect, useState } from 'react';
import { apiGet, ApiResult } from '../api/client';
import { logger } from '../utils/logger';

/** Fetch a GET endpoint's `data` payload with loading + reload support. */
export function useFetch<T>(url: string | null, initial: T) {
  const [data, setData] = useState<T>(initial);
  const [loading, setLoading] = useState<boolean>(!!url);
  const [error, setError] = useState<string | null>(null);

  const reload = useCallback(() => {
    if (!url) return;
    setLoading(true);
    setError(null);
    apiGet<T>(url)
      .then((d) => setData((d ?? initial) as T))
      .catch((err: any) => {
        const msg = err?.response?.data?.msg || err?.message || 'Failed to fetch';
        setError(msg);
        setData(initial);
        logger.error(`Fetch ${url} failed`, { msg });
      })
      .finally(() => setLoading(false));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [url]);

  useEffect(() => { reload(); }, [reload]);

  return { data, loading, error, reload, setData };
}

/**
 * Hook for mutations (POST/PUT/DELETE) with loading and granular error handling.
 * Usage: const { execute, loading, error } = useMutation();
 *        await execute(() => http.post('/api/...', body));
 */
export interface UseMutationOptions<T = any> {
  onSuccess?: (data: T | null) => void;
  onError?: (msg: string) => void;
}

export function useMutation<T = any>(options: UseMutationOptions<T> = {}) {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [data, setData] = useState<T | null>(null);

  const execute = useCallback(
    async (fn: () => Promise<ApiResult<T>>) => {
      setLoading(true);
      setError(null);
      try {
        const res = await fn();
        if (res.status === 1) {
          setData(res.data ?? null);
          options.onSuccess?.(res.data ?? null);
          return res.data ?? null;
        } else {
          const msg = res.msg || 'Operation failed';
          setError(msg);
          options.onError?.(msg);
          throw new Error(msg);
        }
      } catch (err: any) {
        const msg = err.response?.data?.msg || err.message || 'Unknown error';
        setError(msg);
        options.onError?.(msg);
        logger.error('Mutation failed', { msg });
        throw err;
      } finally {
        setLoading(false);
      }
    },
    [options]
  );

  return { execute, loading, error, data };
}
