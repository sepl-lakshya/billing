import { Request, Response, NextFunction } from 'express';
import { randomUUID } from 'crypto';
import { logger } from '../utils/logger';

declare global {
  // eslint-disable-next-line @typescript-eslint/no-namespace
  namespace Express {
    interface Request {
      /** Correlation id attached to every request (also sent back as X-Request-Id). */
      requestId?: string;
    }
  }
}

/** Log every API request with duration, status and a correlation id. */
export function requestLogger(req: Request, res: Response, next: NextFunction) {
  const start = process.hrtime.bigint();
  req.requestId = randomUUID().slice(0, 8);
  res.setHeader('X-Request-Id', req.requestId);

  res.on('finish', () => {
    const ms = Number(process.hrtime.bigint() - start) / 1e6;
    const meta = {
      id: req.requestId,
      status: res.statusCode,
      ms: Math.round(ms * 10) / 10,
      user: (req as any).user?.id,
    };
    const line = `${req.method} ${req.originalUrl}`;
    if (res.statusCode >= 500) logger.error(line, meta);
    else if (res.statusCode >= 400) logger.warn(line, meta);
    else logger.debug(line, meta);
  });

  next();
}
