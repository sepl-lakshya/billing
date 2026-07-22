import { Request, Response, NextFunction } from 'express';
import { config } from '../config';
import { fail } from '../utils/helpers';

/** Basic security headers (equivalent to helmet's most useful defaults for an API). */
export function securityHeaders(_req: Request, res: Response, next: NextFunction) {
  res.setHeader('X-Content-Type-Options', 'nosniff');
  res.setHeader('X-Frame-Options', 'DENY');
  res.setHeader('Referrer-Policy', 'no-referrer');
  res.setHeader('X-XSS-Protection', '0');
  res.removeHeader('X-Powered-By');
  next();
}

/**
 * Simple sliding-window in-memory rate limiter (per IP). Good enough for a
 * single-instance deployment; swap for a shared store if you scale out.
 */
const hits = new Map<string, { count: number; windowStart: number }>();

export function rateLimiter(req: Request, res: Response, next: NextFunction) {
  const now = Date.now();
  const key = req.ip || 'unknown';
  const entry = hits.get(key);

  if (!entry || now - entry.windowStart > config.rateLimit.windowMs) {
    hits.set(key, { count: 1, windowStart: now });
    return next();
  }

  entry.count += 1;
  if (entry.count > config.rateLimit.max) {
    res.setHeader('Retry-After', Math.ceil((entry.windowStart + config.rateLimit.windowMs - now) / 1000));
    return res.status(429).json(fail('Too many requests — please slow down'));
  }
  next();
}

// Prune stale entries occasionally so the map can't grow forever.
setInterval(() => {
  const cutoff = Date.now() - config.rateLimit.windowMs;
  for (const [k, v] of hits) if (v.windowStart < cutoff) hits.delete(k);
}, 5 * 60 * 1000).unref();
