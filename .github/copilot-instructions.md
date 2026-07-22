# SEPL Billing — AI Working Instructions

Read this fully before making changes. These are hard rules, not suggestions.
Goal: keep this a clean, production-grade app. Every change must leave the repo
tidier than you found it — no stray scripts, backups, or docs.

## What this app is
SEPL is an MSP that resells Azure cloud to customers (mostly govt departments)
and buys it through distributors (Ingram, TechData, etc.). This app is the
**cloud-project billing & purchase-reconciliation portal** — a modern rewrite of
a legacy PHP app (kept read-only in `reference/legacy/` for parity reference only).

Three monthly ledgers per project/month:
1. **Portal** = real Azure consumption (source of truth).
2. **Sales** = what SEPL invoices the customer.
3. **Purchase** = what the distributor invoices SEPL (consumption after agreed discount).
Core purpose = reconcile expected purchase (portal − agreed RI%/PAYG% discount)
vs actual distributor invoice; gaps close via Debit Notes (+ matching Credit Notes).

## Scope — DO NOT re-add removed features
This app is **cloud-projects ONLY**. The following were deliberately deleted and
must NOT be reintroduced without explicit user approval: licence projects,
expenses, cloud inward/outward, standalone Reports & Users pages.

## Stack
- **Backend**: `backend/` — Express + TypeScript + `mysql2`. API base `/api/*`.
  Response envelope is always `{ status: 0|1, msg, data }`.
- **Frontend**: `frontend/` — React 19 + Vite 7 + Mantine v7 + Tailwind v4
  (`@tailwindcss/postcss`, preflight OFF) + framer-motion. Font: Inter (+ IBM Plex Mono for numerals).
- **DB**: portable MariaDB, database `billing`, user `root`, no password, port 3306.

## Non-negotiable UI rules
- **Blue-white theme is PERMANENT.** Never change the palette (bg `#eef6ff`,
  primary `#1e5bf0`, blue gradient sidebar, white panels). Layout/density may change; colors may not.
- **Icons come ONLY from `frontend/src/lib/icons.ts`** (semantic names re-exporting
  Lucide). Never import from `lucide-react` or `@tabler/icons-react` directly (tabler is removed).
- **UI is Mantine v7 + design tokens.** Do NOT add shadcn/Radix or a second component
  library. Use Mantine inputs directly (`components/form.tsx` no longer exists).
- Reusable primitives live in `frontend/src/components/ui.tsx` (PageHeader, Section,
  StatCard, EmptyState, OverflowMenu, IconButton) and `DataTable.tsx`. Prefer these over raw markup.
- Design tokens (radius/shadow/motion/z-index) are in `index.css` `:root` and `theme.ts` — reuse, don't hardcode hex.

## Architecture / where things go
- Backend route per domain in `backend/src/routes/` (bills, cloudProjects, imports,
  invoices, notes, products, purchaseHeaders, distributors, oem, lookups, dashboard).
  Shared logic in `backend/src/utils/`. DB access via `backend/src/db.ts`; config in `config.ts`.
- Frontend pages in `frontend/src/pages/` (`cloud/`, `finance/`, `masters/`), API
  wrappers in `src/api/`, shared state in `src/state/`, helpers in `src/lib` / `src/utils`.
- `ViewCloudProject.tsx` is ONE flowing page (hero → dashboard → discounts →
  Headers & Products → monthly billing → attachments). No pill tabs.
- Import flow: `backend/src/routes/imports.ts` + `backend/src/utils/consumption.ts`;
  UI in `frontend/src/pages/cloud/import/ImportConsumption.tsx`. Commit is idempotent
  (`project_item` matched by `project_id, resource_id, product, model`).

## Database safety
- Schema: `backend/db/schema.sql`; lookup seeds: `backend/db/seed.sql`.
- `npm run db:setup` is DESTRUCTIVE — it DROPs every table. Only for first-time
  setup/reset, never on normal runs. `npm run dev` (backend) auto-starts the DB.
- There are no enforced FK constraints (indexes only). No JSON/local DB — MariaDB only.
- Before ANY schema change: ask the user first, and keep column names legacy-faithful.

## Build / verify (always run after changes)
```powershell
# prefix terminals with portable node on PATH
$env:Path="$env:LOCALAPPDATA\nodejs-portable;"+$env:Path
npm run typecheck; "TC=$LASTEXITCODE"   # trust $LASTEXITCODE, not pipeline exit code
npm run build
```
- A change is not "done" until `typecheck` and `build` are green.
- Quirk: piping npm through `Select-String`/`Select-Object` can report exit 1 even on
  success — trust the `✓ built` / `TC=0` line, or read `$LASTEXITCODE` directly.

## Anti-clutter rules (read carefully)
- Do NOT create markdown docs, summary files, or changelogs unless explicitly asked.
- Do NOT leave behind ad-hoc scripts, `*backup*`, `*.old`, or temp files. Delete any
  scratch file you create once done.
- Do NOT commit logs, build output, or `.env`. `.gitignore` already covers `logs/`,
  `*.log`, `dist/`, `release/`, `next-env.d.ts`, and the compiled `vite.config.*`.
- Edit existing files over creating new ones. No one-off helpers/abstractions for a
  single use. Only change what's asked — no drive-by refactors, comments, or type churn.
- This is a Vite app, NOT Next.js. Never add Next.js files/config.

## Windows env notes
- Node v20 portable at `%LOCALAPPDATA%\nodejs-portable`; MariaDB portable at `%LOCALAPPDATA%\mariadb-portable`.
- `$pid` is a PowerShell reserved var — use `$projId` etc. in scripts.
- PowerShell 5.1: no `??` operator. Run multi-line scripts from a temp `.ps1` file, not pasted inline.
