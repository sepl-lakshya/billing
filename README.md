# Billing System — Modern Enterprise-Grade Application

**Status: ✅ Production-Ready | v1.0.0**  
**Built with**: Node.js + Express + TypeScript | React + Vite | MariaDB  
**Deployed**: 2026-07-18

---

## 🎯 Quick Start

### Development (5 minutes)

**Prerequisites**: Node.js 18+, MariaDB (portable included)

```bash
# One-time install
npm install
cd backend && npm install
cd ../frontend && npm install

# Start both backend + frontend from repo root
cd .. && npm run dev
```

**Open**: http://localhost:5174 → See the dashboard with:
- 2 cloud projects, 1 licence project
- 40 products, 4 OEMs, 4 distributors  
- "Billing Pending" alerts (Test: 21 months, Demo: 16 months)
- "Bills In Progress" tracking

**Login**: Any username works (system user active in dev)

### Production

See [QUICKSTART.md](./QUICKSTART.md) and [DEPLOYMENT.md](./DEPLOYMENT.md) for full deployment guide.

---

## 📚 Documentation

| Document | Purpose |
|---|---|
| [README.md](./README.md) | This file — project overview |
| [DEPLOYMENT.md](./DEPLOYMENT.md) | Architecture, checklist, monitoring, troubleshooting |
| [QUICKSTART.md](./QUICKSTART.md) | Dev setup, production deployment, maintenance |
| [backend/.env.example](./backend/.env.example) | All backend config options |
| [backend/.env.production](./backend/.env.production) | Production template |
| [frontend/.env](./frontend/.env) | Frontend API endpoint |

---

## ✨ What's Been Built

### Phase 1: Operational Efficiency ✅
- **Idempotent Imports**: Re-upload monthly data safely without duplicates
- **Actionable Dashboard**: Real-time alerts for pending billing, in-progress items, trends
- **Database Optimization**: Composite indexes for fast operations

### Phase 2: Enterprise Deployment ✅
- **Environment Config**: Hierarchical loading (dev/staging/prod in one codebase)
- **Structured Logging**: JSON logs with correlation IDs
- **JWT + Entra ID**: Ready for Microsoft Entra ID integration
- **Central Error Handler**: Friendly messages, no stack traces in production
- **Security**: Rate limiting, headers, validation
- **Health Monitoring**: Deep endpoint for load balancers
- **Graceful Shutdown**: Clean connection draining
- **Frontend Robustness**: Error boundary, auth context, system status page

---

## 🏗️ Architecture

### Middleware Stack
```
Security Headers
    ↓
Rate Limiter (300 req/min per IP)
    ↓
Request Logger (correlation IDs)
    ↓
CORS
    ↓
Body Parser
    ↓
Auth Middleware (JWT validation)
    ↓
Routes
    ↓
404 Handler
    ↓
Central Error Handler
    ↓
Graceful Shutdown
```

### Data Flow
```
Frontend (React)
    ↓
API Client (Axios + interceptors)
    ↓
Backend (Express + TypeScript)
    ↓
Database (MariaDB)
    ↓
Logging (JSON to daily files)
```

---

## 🔑 Key Features

### Dashboard
- **Pending Billing**: Projects with months not yet invoiced
- **Bills In Progress**: Items in pricing pipeline (portal vs sales)
- **Monthly Trends**: 6-month cost vs revenue chart
- **Summary Counts**: Quick view of all entities

### Idempotent Imports
- Match items by `(project_id, resource_id, product, model)`
- Upsert bill items automatically
- Apply discounts without manual re-entry
- Safe for monthly re-uploads

### Authentication (Production Ready)
- **Dev**: `AUTH_MODE=none` → System user active
- **Prod**: `AUTH_MODE=entra` → Microsoft Entra ID with JWT validation
- **RBAC**: Role-based guards on endpoints
- **Auto-Provisioning**: Users created on first auth

### Monitoring & Diagnostics
- **Health Check** (`GET /api/health`): Backend + DB + auth status
- **System Status UI** (`/system`): Backend health + current identity
- **Structured Logs**: JSON-formatted with timestamps and correlation IDs
- **Error Translation**: Database errors → user-friendly messages

---

## 📊 Project Structure

```
billing/
├── backend/
│   ├── src/
│   │   ├── index.ts              # Middleware wiring, health check
│   │   ├── config.ts             # Env loading with validation
│   │   ├── middleware/
│   │   │   ├── auth.ts           # JWT + provisioning
│   │   │   ├── errors.ts         # Error translation
│   │   │   ├── security.ts       # Rate limiting + headers
│   │   │   ├── requestLogger.ts  # Correlation IDs
│   │   │   └── ...
│   │   ├── routes/
│   │   │   ├── dashboard.ts      # Pending, progress, trends
│   │   │   ├── imports.ts        # Idempotent commit
│   │   │   ├── users.ts          # User CRUD + /me
│   │   │   └── ...
│   │   └── utils/logger.ts       # JSON logging
│   ├── .env                      # Dev config (git-ignored)
│   ├── .env.example              # Example values
│   └── .env.production           # Prod template
├── frontend/
│   ├── src/
│   │   ├── main.tsx              # ErrorBoundary + AuthProvider
│   │   ├── pages/
│   │   │   ├── Dashboard.tsx     # Alerts + trends
│   │   │   ├── System.tsx        # Health + identity
│   │   │   └── ...
│   │   ├── components/
│   │   │   ├── ErrorBoundary.tsx # Crash protection
│   │   │   └── Layout.tsx        # Sidebar + header
│   │   ├── api/client.ts         # Axios + interceptors
│   │   └── ...
│   └── .env                      # API endpoint
├── DEPLOYMENT.md                 # Complete guide
├── QUICKSTART.md                 # Setup + monitoring
└── db/schema.sql                 # Schema + indexes
```

---

## 🚀 API Endpoints

### Health & System
```
GET /api/health              # Backend status (env, DB, auth, uptime)
GET /api/users/me            # Current user identity
```

### Dashboard
```
GET /api/dashboard           # Pending, in-progress, trends
```

### Imports
```
POST /api/imports/cloud/commit  # Idempotent upsert with discounts
```

### Core Resources
```
GET/POST/PUT/DELETE /api/cloud-projects
GET/POST/PUT/DELETE /api/cloud-inward
GET/POST/PUT/DELETE /api/cloud-outward
GET/POST/PUT/DELETE /api/licence-projects
GET/POST/PUT/DELETE /api/invoices
GET/POST/PUT/DELETE /api/debit-notes
GET/POST/PUT/DELETE /api/credit-notes
GET/POST/PUT/DELETE /api/expenses
... and others
```

---

## 🔐 Security

- **Rate Limiting**: 300 req/min per IP (configurable)
- **Security Headers**: X-Content-Type-Options, X-Frame-Options, Referrer-Policy
- **Parameterized Queries**: All SQL queries use placeholders
- **Error Masking**: Stack traces hidden in production
- **CORS**: Configurable origin (dev: localhost, prod: your domain)
- **JWT Validation**: Token verification + key caching (1h TTL)

---

## 📈 Monitoring

### Health Endpoint
```bash
curl http://localhost:4000/api/health

{
  "status": 1,
  "msg": "ok",
  "data": {
    "env": "development",
    "db": "up",
    "auth": "none",
    "uptime": 3600
  }
}
```

### Logs
```bash
# Today's errors
grep ERROR logs/app-$(date +%Y-%m-%d).log

# Last 50 lines
tail -50 logs/app-*.log

# Search by correlation ID
grep "abc123" logs/app-*.log
```

---

## 🛠️ Development Commands

### Backend
```bash
npm run dev              # Start with auto-reload
npm run typecheck       # Verify TypeScript
npm run build           # Compile to dist/
npm run db:check        # Verify DB connection
npm run db:setup        # Initialize schema
```

### Frontend
```bash
npm run dev             # Start dev server
npm run build           # Build for production
npm run preview         # Preview production build
npm run typecheck       # Verify TypeScript
```

---

## 🐛 Troubleshooting

| Issue | Solution |
|---|---|
| "Port 4000 already in use" | `lsof -ti:4000 \| xargs kill -9` |
| "Cannot connect to database" | `mysql -h 127.0.0.1 -u root -e "SELECT 1"` |
| "Frontend won't load" | Check `VITE_API_TARGET` in `frontend/.env` |
| "401 unauthorized" | Verify `AUTH_MODE=none` for dev, check Entra config for prod |
| "Rate limited (429)" | Too many requests; wait 1 minute or increase `RATE_LIMIT_MAX` |

See [QUICKSTART.md](./QUICKSTART.md) for comprehensive troubleshooting.

---

## 🔄 Deployment Checklist

- [ ] Review [DEPLOYMENT.md](./DEPLOYMENT.md)
- [ ] Configure `.env.production` with real values
- [ ] Test database connectivity from production server
- [ ] Create application logs directory (`/var/log/billing`)
- [ ] Set up log rotation (daily, keep 30 days)
- [ ] Configure reverse proxy (nginx/HAProxy) with SSL
- [ ] Set `NODE_ENV=production` on server
- [ ] Run `npm run build` and verify compilation
- [ ] Test health endpoint: `curl http://localhost:3000/api/health`
- [ ] Monitor error logs for 24 hours
- [ ] Set up automated backups

See [QUICKSTART.md](./QUICKSTART.md) for step-by-step production deployment.

---

## 📞 Support

### Check These First
1. **Logs**: `tail -f logs/app-*.log` for ERROR entries
2. **Health**: `curl http://localhost:4000/api/health`
3. **Config**: Verify `.env` has correct values
4. **Database**: `mysql -u root -e "SHOW DATABASES;"`

### Enable Debug Logging
```bash
LOG_LEVEL=debug npm run dev
```

### Export Debug Bundle
```bash
npm -v && node -v && mysql -V
ps aux | grep node
tar czf debug-$(date +%Y-%m-%d).tar.gz logs/
```

---

## 📋 Version History

| Version | Date | Highlights |
|---|---|---|
| 1.0.0 | 2026-07-18 | ✅ Production-ready release |
| | | • Idempotent imports |
| | | • Actionable dashboard |
| | | • Enterprise auth framework |
| | | • Structured logging |
| | | • Rate limiting + security |
| | | • System monitoring |

---

**Built with ❤️ for enterprise billing operations.**

```

Build for production with `npm run build && npm start`.

## 3. Run the frontend

```powershell
cd frontend
npm install
npm run dev                 # http://localhost:5173
```

The Vite dev server proxies `/api` and `/uploads` to the backend on port 4000
(configurable via `VITE_API_TARGET`). Build for production with `npm run build`.

---

## Modules (feature parity with the legacy app)

- **Dashboard** – summary counts.
- **Masters** – Products, OEM, Distributors, Purchase Headers, Users.
- **Cloud Projects** – projects, headers, line items (add/edit/disable/delete),
  discounts (with rolling history), project expenses, adjustments, attachments
  (file upload), user assignments, project invoices, and the full **monthly bill
  pricing engine** (portal → sales → purchase prices) plus cloud inward/outward.
- **Licence Projects** – projects, items, sales & purchase terms, sub‑purchases,
  user assignments, and monthly portal/sales/purchase pricing.
- **Invoices** – purchase invoices, tag/untag to a bill.
- **Debit Notes / Credit Notes** – listing + deletion (creation happens inside a
  project's monthly bill flow).
- **Expenses** – general company expenses.

## API overview

All endpoints live under `/api` and return `{ status: 0|1, msg, data }`.

| Prefix                     | Purpose                                    |
|----------------------------|--------------------------------------------|
| `/api/lookups`             | All reference/dropdown data                |
| `/api/dashboard`           | Summary counts                             |
| `/api/products` `/oem` `/distributors` `/users` `/purchase-headers` | Masters |
| `/api/cloud-projects`      | Cloud projects + all sub‑resources         |
| `/api/bills`               | Monthly bill pricing engine                |
| `/api/cloud-inward`        | Cloud inward / outward / invoice inward    |
| `/api/invoices`            | Purchase invoices + bill mapping           |
| `/api/notes`               | Debit & credit notes                       |
| `/api/expenses`            | General expenses                           |
| `/api/licence-projects`    | Licence projects + all sub‑resources       |

## Notes

- All SQL is parameterised (the legacy app concatenated strings and was open to
  SQL injection). Input validation mirrors the original business rules.
- Bill status progression: `0` draft → `1` portal priced → `2` sales priced →
  `3` purchase priced.
- The `reference/legacy/` folder is kept intact purely for reference and can be
  deleted once you are happy with the rewrite.
