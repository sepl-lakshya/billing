import { Request, Response, NextFunction, RequestHandler } from 'express';

/** Standard API envelope matching the legacy { status, msg } shape, plus data. */
export interface ApiResult<T = any> {
  status: 0 | 1;
  msg: string;
  data?: T;
}

export function ok<T>(msg: string, data?: T): ApiResult<T> {
  return { status: 1, msg, data };
}

export function fail(msg: string): ApiResult {
  return { status: 0, msg };
}

/** Wrap async route handlers so thrown errors go to the error middleware. */
export function asyncHandler(fn: (req: Request, res: Response, next: NextFunction) => Promise<any>): RequestHandler {
  return (req, res, next) => {
    Promise.resolve(fn(req, res, next)).catch(next);
  };
}

/** Random alphanumeric string (used for record hashes, like legacy getName()). */
export function getName(n: number): string {
  const chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  let out = '';
  for (let i = 0; i < n; i++) {
    out += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  return out;
}

/** MySQL DATETIME string for "now". */
export function nowDateTime(): string {
  const d = new Date();
  const pad = (x: number) => String(x).padStart(2, '0');
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(
    d.getHours()
  )}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
}

/** Parse a comma-separated string of values into an array (legacy explode()). */
export function explode(value: unknown): string[] {
  if (value === undefined || value === null) return [];
  const str = String(value).trim();
  if (str === '') return [];
  return str.split(',').map((s) => s.trim());
}

/** Accept either a real JS array or a legacy comma-separated string. */
export function asArray(value: unknown): string[] {
  if (Array.isArray(value)) return value.map((v) => String(v));
  return explode(value);
}

/** Coerce to a number, returning fallback when not parseable. */
export function toNum(value: unknown, fallback = 0): number {
  const n = Number(value);
  return Number.isFinite(n) ? n : fallback;
}

/** Coerce to a trimmed string. */
export function toStr(value: unknown): string {
  if (value === undefined || value === null) return '';
  return String(value).trim();
}

const MONTH_NAMES = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December',
];

/**
 * Build the list of {month, year, name} from a start date up to (and including)
 * the current month. Mirrors cloudprojectmonthlistsection.php which lists every
 * month from the project start date onward for billing.
 */
export function generateMonths(startDate: string | Date | null): Array<{ month: number; year: number; name: string; label: string }> {
  const out: Array<{ month: number; year: number; name: string; label: string }> = [];
  if (!startDate) return out;
  const start = new Date(startDate);
  if (isNaN(start.getTime())) return out;
  const now = new Date();
  let y = start.getFullYear();
  let m = start.getMonth() + 1; // 1-based
  const endY = now.getFullYear();
  const endM = now.getMonth() + 1;
  let guard = 0;
  while ((y < endY || (y === endY && m <= endM)) && guard < 600) {
    out.push({ month: m, year: y, name: MONTH_NAMES[m - 1], label: `${MONTH_NAMES[m - 1]} ${y}` });
    m += 1;
    if (m > 12) {
      m = 1;
      y += 1;
    }
    guard += 1;
  }
  return out;
}

