/**
 * Frontend logger: writes to console (with dev/prod filtering) and sends
 * errors to backend for centralized monitoring (future).
 */
const isDev = import.meta.env.DEV;

export const logger = {
  debug: (...args: any[]) => {
    if (isDev) console.log('[debug]', ...args);
  },
  info: (...args: any[]) => {
    if (isDev) console.log('[info]', ...args);
    else console.info(...args);
  },
  warn: (...args: any[]) => {
    console.warn('[warn]', ...args);
  },
  error: (...args: any[]) => {
    console.error('[error]', ...args);
    // Future: send to backend error tracking.
  },
};
