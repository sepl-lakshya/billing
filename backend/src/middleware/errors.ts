import { Request, Response, NextFunction } from 'express';
import { ApiResult, fail } from '../utils/helpers';
import { logger } from '../utils/logger';
import { config } from '../config';

/** Throw from route handlers for controlled errors: throw new AppError('...', 400) */
export class AppError extends Error {
  statusCode: number;
  constructor(message: string, statusCode = 400) {
    super(message);
    this.statusCode = statusCode;
  }
}

/** Translate low-level MySQL/driver errors into messages a human can act on. */
function friendlyDbMessage(err: any): string | null {
  switch (err?.code) {
    case 'ER_DUP_ENTRY':
      return 'A record with the same value already exists (duplicate entry)';
    case 'ER_NO_REFERENCED_ROW_2':
    case 'ER_ROW_IS_REFERENCED_2':
      return 'This record is linked to other data and the operation would break that link';
    case 'ER_DATA_TOO_LONG':
      return 'One of the values is too long for its field';
    case 'ER_TRUNCATED_WRONG_VALUE':
    case 'WARN_DATA_TRUNCATED':
      return 'One of the values has an invalid format';
    case 'ECONNREFUSED':
    case 'PROTOCOL_CONNECTION_LOST':
    case 'ETIMEDOUT':
      return 'Database is unreachable — please try again in a moment';
    case 'ER_LOCK_DEADLOCK':
      return 'The database was busy — please retry the operation';
    default:
      return null;
  }
}

/** 404 for unknown API routes. */
export function notFoundHandler(_req: Request, res: Response) {
  res.status(404).json(fail('Not found'));
}

/** Central error handler -> always returns the { status, msg } envelope. */
export function errorHandler(err: any, req: Request, res: Response, _next: NextFunction) {
  // Body parse / multer errors carry sensible status codes.
  const status =
    err instanceof AppError ? err.statusCode :
    err?.statusCode && Number.isInteger(err.statusCode) ? err.statusCode :
    err?.type === 'entity.too.large' ? 413 :
    err?.code === 'LIMIT_FILE_SIZE' ? 413 :
    500;

  const dbMsg = friendlyDbMessage(err);
  const msg =
    err instanceof AppError ? err.message :
    dbMsg ? dbMsg :
    err?.code === 'LIMIT_FILE_SIZE' ? `File is too large (max ${config.maxUploadMb} MB)` :
    err?.type === 'entity.too.large' ? 'Request body is too large' :
    status === 500 && config.isProd ? 'Internal server error' :
    err?.message || 'Internal server error';

  if (status >= 500) {
    logger.error(`Unhandled error on ${req.method} ${req.originalUrl}`, {
      id: req.requestId,
      code: err?.code,
      message: err?.message,
      stack: config.isProd ? undefined : err?.stack,
    });
  }

  res.status(status).json({ status: 0, msg } as ApiResult);
}
