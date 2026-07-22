import express from 'express';
import cors from 'cors';
import fs from 'fs';
import path from 'path';
import { config, validateConfig } from './config';
import { pool, queryOne } from './db';
import { logger } from './utils/logger';
import { requestLogger } from './middleware/requestLogger';
import { securityHeaders, rateLimiter } from './middleware/security';
import { authenticate } from './middleware/auth';
import { notFoundHandler, errorHandler } from './middleware/errors';

import lookupsRouter from './routes/lookups';
import dashboardRouter from './routes/dashboard';
import productsRouter from './routes/products';
import oemRouter from './routes/oem';
import distributorsRouter from './routes/distributors';
import usersRouter from './routes/users';
import purchaseHeadersRouter from './routes/purchaseHeaders';
import cloudProjectsRouter from './routes/cloudProjects';
import billsRouter from './routes/bills';
import invoicesRouter from './routes/invoices';
import notesRouter from './routes/notes';
import reportsRouter from './routes/reports';
import importsRouter from './routes/imports';

const app = express();
app.set('trust proxy', 1); // correct client IPs / protocol behind a reverse proxy

// Startup configuration sanity check.
const configProblems = validateConfig();
for (const p of configProblems) logger.warn(`[config] ${p}`);
if (config.isProd && configProblems.some((p) => p.includes('requires'))) {
  logger.error('Invalid production configuration — refusing to start');
  process.exit(1);
}

app.use(securityHeaders);
app.use(rateLimiter);
app.use(requestLogger);
app.use(cors({ origin: config.corsOrigin, credentials: true }));
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true }));

// Ensure the uploads directory exists and expose it statically.
if (!fs.existsSync(config.uploadDir)) {
  fs.mkdirSync(config.uploadDir, { recursive: true });
}
app.use('/uploads', express.static(config.uploadDir));

// Optional single-environment mode: serve the built SPA from the backend.
const frontendIndex = path.join(config.frontendDistDir, 'index.html');
const serveFrontend = fs.existsSync(frontendIndex);
if (serveFrontend) {
  app.use(express.static(config.frontendDistDir));
  logger.info(`Frontend static files served from ${config.frontendDistDir}`);
}

// Deep health check: reports env + DB connectivity (used by monitors/load balancers).
app.get('/api/health', async (_req, res) => {
  let db = 'up';
  try {
    await queryOne('SELECT 1 AS ok');
  } catch {
    db = 'down';
  }
  res.status(db === 'up' ? 200 : 503).json({
    status: db === 'up' ? 1 : 0,
    msg: db === 'up' ? 'ok' : 'database unreachable',
    data: { env: config.env, db, auth: config.auth.mode, uptime: Math.round(process.uptime()) },
  });
});

// All /api routes require authentication (no-op identity when AUTH_MODE=none).
app.use('/api', authenticate);

// ---- Module routers ----
app.use('/api/lookups', lookupsRouter);
app.use('/api/dashboard', dashboardRouter);
app.use('/api/products', productsRouter);
app.use('/api/oem', oemRouter);
app.use('/api/distributors', distributorsRouter);
app.use('/api/users', usersRouter);
app.use('/api/purchase-headers', purchaseHeadersRouter);
app.use('/api/cloud-projects', cloudProjectsRouter);
app.use('/api/bills', billsRouter);
app.use('/api/invoices', invoicesRouter);
app.use('/api/notes', notesRouter);
app.use('/api/reports', reportsRouter);
app.use('/api/imports', importsRouter);

// 404 for unknown API routes
app.use('/api', notFoundHandler);

if (serveFrontend) {
  // SPA fallback for non-API routes (supports BrowserRouter deep links).
  app.get(/^\/(?!api|uploads).*/, (_req, res) => {
    res.sendFile(frontendIndex);
  });
}

// Central error handler -> always returns the { status, msg } envelope.
app.use(errorHandler);

const server = app.listen(config.port, () => {
  logger.info(`Billing API listening on http://localhost:${config.port}`, {
    env: config.env,
    auth: config.auth.mode,
  });
  logger.info(`Uploads served from ${path.relative(process.cwd(), config.uploadDir)}/`);
});

// ---------------------------------------------------------------------------
// Resilience: graceful shutdown + last-resort error logging
// ---------------------------------------------------------------------------
async function shutdown(signal: string) {
  logger.info(`${signal} received — shutting down gracefully`);
  server.close(async () => {
    try {
      await pool.end();
    } catch {
      /* ignore */
    }
    process.exit(0);
  });
  // Force-exit if connections refuse to drain.
  setTimeout(() => process.exit(1), 10_000).unref();
}
process.on('SIGINT', () => shutdown('SIGINT'));
process.on('SIGTERM', () => shutdown('SIGTERM'));

process.on('unhandledRejection', (reason: any) => {
  logger.error('Unhandled promise rejection', { message: reason?.message || String(reason) });
});
process.on('uncaughtException', (err) => {
  logger.error('Uncaught exception — exiting', { message: err.message, stack: err.stack });
  process.exit(1);
});
