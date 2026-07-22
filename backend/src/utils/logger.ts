import fs from 'fs';
import path from 'path';
import { config } from '../config';

/**
 * Minimal structured logger: writes levelled lines to the console and, in
 * addition, appends JSON lines to a daily log file under LOG_DIR. Zero deps so
 * it works the same on a laptop and on a server.
 */
const LEVELS = { debug: 10, info: 20, warn: 30, error: 40 } as const;
type Level = keyof typeof LEVELS;

const minLevel = LEVELS[config.logLevel] ?? LEVELS.info;

function logFilePath(): string {
  const d = new Date();
  const day = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
  return path.join(config.logDir, `app-${day}.log`);
}

function write(level: Level, msg: string, meta?: Record<string, unknown>) {
  if (LEVELS[level] < minLevel) return;
  const ts = new Date().toISOString();
  const line = meta && Object.keys(meta).length ? `${msg} ${JSON.stringify(meta)}` : msg;
  // Console
  const prefix = `[${ts}] [${level.toUpperCase()}]`;
  if (level === 'error') console.error(prefix, line);
  else if (level === 'warn') console.warn(prefix, line);
  else console.log(prefix, line);
  // File (best-effort; never crash the app because logging failed)
  try {
    if (!fs.existsSync(config.logDir)) fs.mkdirSync(config.logDir, { recursive: true });
    fs.appendFileSync(logFilePath(), JSON.stringify({ ts, level, msg, ...meta }) + '\n');
  } catch {
    /* ignore */
  }
}

export const logger = {
  debug: (msg: string, meta?: Record<string, unknown>) => write('debug', msg, meta),
  info: (msg: string, meta?: Record<string, unknown>) => write('info', msg, meta),
  warn: (msg: string, meta?: Record<string, unknown>) => write('warn', msg, meta),
  error: (msg: string, meta?: Record<string, unknown>) => write('error', msg, meta),
};
