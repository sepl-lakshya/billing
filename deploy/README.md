# Deploying the SEPL Billing portal

This app runs on two separate Linux VMs, one environment each:

| Environment  | Domain                     | VM                | `APP_ENV`    |
| ------------ | -------------------------- | ----------------- | ------------ |
| Demo/testing | `demobilling.surbhi.net`   | demo VM           | `demo`       |
| Production   | `billing.surbhi.net`       | apps VM           | `production` |

Nothing is hardcoded — every URL, DB credential and Entra ID value is read from
environment files, so the **same repo + same script** deploys either portal.

---

## 1. How it fits together (the "single build" question)

In **development** you run two dev servers (`npm run dev`): Vite on `:5173`
(hot-reload UI) and Express on `:4000` (API). They are split *only* so the UI
hot-reloads while you code.

In **production it is a single build / single process**, exactly like your
earlier app:

- `npm --prefix frontend run build` compiles the React app to
  `frontend/dist/` (plain static files).
- The Node backend serves those static files itself (see `FRONTEND_DIST` in
  `backend/src/index.ts`) **and** the `/api` + `/uploads` routes.
- So one Node process on `127.0.0.1:4000` serves the whole app.
- **nginx** only terminates TLS and reverse-proxies the domain to that port.

```
Browser ──HTTPS──> nginx (:443) ──proxy──> Node (:4000)
                                            ├─ /api/*      → Express API
                                            ├─ /uploads/*  → uploaded files
                                            └─ /*          → React SPA (frontend/dist)
```

`deploy/deploy.sh` produces this build and (re)starts the process with PM2.

---

## 2. Prerequisites (once per VM)

- **Node.js 20+** and **npm**
- **PM2**: `sudo npm install -g pm2`
- **nginx** and **certbot** (`sudo apt install nginx certbot python3-certbot-nginx`)
- **git**, and the SQL database (MySQL/MariaDB) reachable from the VM
- DNS: point the domain (`demobilling.surbhi.net` / `billing.surbhi.net`) at the VM's IP

---

## 3. First-time setup (once per VM)

```bash
# 1. Clone
git clone <your-repo-url> billing
cd billing

# 2. Tell this VM which environment it is (git-ignored marker)
echo demo > deploy/.target          # on the DEMO VM
# echo production > deploy/.target   # on the APPS/production VM

# 3. Put this VM's SECRETS in a git-ignored local env file.
#    Only values that differ from the committed template are needed.
cat > backend/.env.local <<'EOF'
DB_HOST=127.0.0.1
DB_PORT=3306
DB_USER=your_db_user
DB_PASSWORD=your_db_password
DB_NAME=billing
EOF

# 4. Create the database schema (FIRST TIME ONLY — this DROPs tables):
#    npm --prefix backend run db:setup

# 5. Deploy
bash deploy/deploy.sh          # target read from deploy/.target
```

Then wire up nginx + TLS:

```bash
# Demo VM:
sudo cp deploy/nginx/demobilling.surbhi.net.conf /etc/nginx/sites-available/demobilling.surbhi.net
sudo ln -sf /etc/nginx/sites-available/demobilling.surbhi.net /etc/nginx/sites-enabled/
# Production VM:
# sudo cp deploy/nginx/billing.surbhi.net.conf /etc/nginx/sites-available/billing.surbhi.net
# sudo ln -sf /etc/nginx/sites-available/billing.surbhi.net /etc/nginx/sites-enabled/

sudo nginx -t && sudo systemctl reload nginx
sudo certbot --nginx -d demobilling.surbhi.net     # (or billing.surbhi.net)
```

Make PM2 survive reboots:

```bash
pm2 startup        # run the command it prints
pm2 save
```

---

## 4. Routine updates (your workflow)

On your dev machine you push changes to the repo. Then:

```bash
# On the DEMO VM — deploy + test:
cd billing
git pull
bash deploy/deploy.sh --pull    # (--pull is optional; git pull already done)

# Once verified on demo, on the APPS/production VM:
cd billing
git pull
bash deploy/deploy.sh
```

`deploy.sh` installs deps, rebuilds backend + frontend for the target, and
reloads the PM2 process. Only one environment ever runs per VM.

Health check: `curl -s http://127.0.0.1:4000/api/health`

---

## 5. Turning on Microsoft Entra ID login (later)

Login ships **off** (`AUTH_MODE=none`). Backend validation and frontend MSAL
sign-in are already coded and dormant. To enable it:

1. **Register the app** in Entra ID (Azure portal → App registrations):
   - Platform **Single-page application**, Redirect URI = the site origin
     (`https://demobilling.surbhi.net` and/or `https://billing.surbhi.net`).
   - Note the **Application (client) ID** and **Directory (tenant) ID** (both public).
   - Optionally *Expose an API* + define an app role (e.g. `Billing.Admin`) and
     assign users; put that role name in `ENTRA_ADMIN_ROLE`.

2. **Backend** — add to `backend/.env.local`:
   ```
   AUTH_MODE=entra
   ENTRA_TENANT_ID=<tenant-id>
   ENTRA_CLIENT_ID=<api-client-id>
   ENTRA_ADMIN_ROLE=Billing.Admin
   ```

3. **Frontend** — add to `frontend/.env.<target>.local` (e.g. `.env.demo.local`):
   ```
   VITE_AUTH_MODE=entra
   VITE_ENTRA_TENANT_ID=<tenant-id>
   VITE_ENTRA_CLIENT_ID=<spa-client-id>
   VITE_ENTRA_API_SCOPE=api://<api-client-id>/access_as_user
   ```

4. Redeploy: `bash deploy/deploy.sh`. Users are auto-provisioned into the
   `users` table on first sign-in.

---

## 6. Where things live

| Purpose                     | File                                      |
| --------------------------- | ----------------------------------------- |
| Deploy script               | `deploy/deploy.sh`                        |
| nginx configs               | `deploy/nginx/*.conf`                     |
| Per-VM environment marker   | `deploy/.target` (git-ignored)            |
| Backend env (committed)     | `backend/.env.demo`, `backend/.env.production` |
| Backend secrets (per VM)    | `backend/.env.local` (git-ignored)        |
| Frontend env (committed)    | `frontend/.env.demo`, `frontend/.env.production` |
| Frontend secrets (per VM)   | `frontend/.env.<target>.local` (git-ignored) |
| PM2 process config          | `pm2.config.cjs`                          |
