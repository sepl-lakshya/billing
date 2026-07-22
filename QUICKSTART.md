# Quick Start Guide

## Local Development

### Prerequisites
- Node.js (v18+) with npm
- MariaDB 5.7+ or MySQL 8.0+ (MariaDB portable included)
- 512MB+ available RAM

### Setup (First Time)

```bash
# 1. Backend setup
cd backend
npm install
npm run db:setup        # Initialize database schema
npm run dev             # Start with auto-reload

# 2. Frontend setup (new terminal)
cd frontend
npm install
npm run dev             # Starts on http://localhost:5174 (5173 if available)
```

### Daily Development

```bash
# From repository root (starts backend + frontend together)
npm run dev
```

Open **http://localhost:5174** in your browser.

**Login**: Any username works in dev mode (AUTH_MODE=none). System user is active.

---

## Production Deployment

### Prerequisites
- Node.js v18+ LTS installed
- MariaDB 5.7+ running (remote or local)
- Linux server (Ubuntu 20.04+, CentOS 8+, or similar)
- ~500MB disk space for app + logs
- SSL/TLS certificate (for HTTPS)

### One-Time Setup

```bash
# 1. Clone or copy code to production server
scp -r billing/ user@prod-server:/opt/billing
cd /opt/billing

# 2. Create application user
sudo useradd -r -s /bin/bash -d /opt/billing billing-app

# 3. Create directories
sudo mkdir -p /var/log/billing /var/billing/uploads
sudo chown billing-app:billing-app /var/log/billing /var/billing/uploads

# 4. Configure environment
cp backend/.env.production backend/.env
# Edit backend/.env with production values:
# - Database credentials
# - Entra ID config (if using)
# - Azure secrets

# 5. Install dependencies
cd backend
npm install --omit=dev
npm run build

cd ../frontend
npm install --omit=dev
npm run build
```

### Starting the Application

### Single Release Folder (Recommended for handoff)

Create one deployable folder from the repository root:

```bash
npm run release:bundle
```

This creates `release/` with:
- `release/backend/dist` (compiled API)
- `release/frontend/dist` (compiled UI)
- `release/backend/package.json` + `package-lock.json`
- `release/pm2.config.cjs`

Deploy only the `release/` folder to your target server.

#### Option A: PM2 Single-Service (Recommended)
Build once from root, then run only the backend process. Backend serves built frontend from `../frontend/dist`.

```bash
# from repo root
npm run build

# start/restart
pm2 start pm2.config.cjs
pm2 restart billing-api
pm2 save
```

Health checks:
```bash
curl http://localhost:4000/api/health
curl -I http://localhost:4000/
```

#### Option B: Direct Node (Development Testing)
```bash
cd backend
NODE_ENV=production FRONTEND_DIST=../frontend/dist npm run start:backend
```

#### Option C: systemd Service
Create `/etc/systemd/system/billing-app.service`:
```ini
[Unit]
Description=Billing Application
After=network.target mariadb.service

[Service]
Type=simple
User=billing-app
WorkingDirectory=/opt/billing/backend
Environment="NODE_ENV=production"
ExecStart=/usr/bin/node dist/index.js
Restart=always
RestartSec=10
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
```

Then:
```bash
sudo systemctl daemon-reload
sudo systemctl enable billing-app
sudo systemctl start billing-app
sudo systemctl status billing-app
```

#### Option D: Docker (Advanced)
```bash
# Build
docker build -t billing-app:1.0 .

# Run
docker run -d \
  --name billing-app \
  -p 3000:3000 \
  -v /var/log/billing:/app/logs \
  -v /var/billing/uploads:/app/uploads \
  -e NODE_ENV=production \
  -e DB_HOST=db.example.com \
  billing-app:1.0
```

### Verify Deployment

```bash
# Check service is running
systemctl status billing-app

# Test health endpoint
curl http://localhost:3000/api/health

# Expected response:
# {"status":1,"msg":"ok","data":{"env":"production","db":"up","auth":"entra","uptime":123}}

# Monitor logs
tail -f /var/log/billing/app-$(date +%Y-%m-%d).log

# Check for errors
grep ERROR /var/log/billing/app-*.log
```

---

## Monitoring & Maintenance

### Daily Checks
```bash
# Monitor health endpoint (every 5 min)
while true; do
  curl -s http://localhost:3000/api/health | jq .
  sleep 300
done

# Watch logs for errors
tail -f /var/log/billing/app-*.log | grep ERROR
```

### Log Rotation
Add to `/etc/logrotate.d/billing`:
```
/var/log/billing/*.log {
  daily
  rotate 30
  compress
  delaycompress
  missingok
  notifempty
  postrotate
    systemctl reload billing-app > /dev/null 2>&1 || true
  endscript
}
```

### Backup Strategy
```bash
# Backup database weekly
mysqldump -h db.example.com -u billing_prod -p billing_prod > backup-$(date +%Y-%m-%d).sql
gzip backup-*.sql

# Archive logs monthly
tar czf logs-archive-$(date +%Y-%m).tar.gz /var/log/billing/app-*.log
```

---

## Troubleshooting

### Application won't start
```bash
# Check logs
journalctl -u billing-app -n 50

# Verify database connection
mysql -h $DB_HOST -u $DB_USER -p $DB_NAME -e "SELECT 1"

# Check port availability
netstat -tuln | grep 3000
```

### High memory usage
```bash
# Check Node process
ps aux | grep node

# Restart service
systemctl restart billing-app

# Monitor memory
watch -n 1 'ps -p $(pidof node) -o pid,vsz,rss'
```

### Database issues
```bash
# Check connection pool
npm run db:check

# Verify tables exist
mysql -u $DB_USER -p $DB_NAME -e "SHOW TABLES;"

# Rebuild indexes
npm run db:setup
```

### Authentication failing (Entra ID)
```bash
# Verify Entra config
echo $AZURE_TENANT_ID $AZURE_CLIENT_ID $AZURE_ADMIN_ROLE

# Check JWT tokens in logs
grep "token" /var/log/billing/app-*.log
```

---

## Performance Tuning

### Database Optimization
```sql
-- Add indexes for common queries
CREATE INDEX idx_project_item_resource ON project_item(project_id, resource_id, product, model);
CREATE INDEX idx_bill_created ON bill_item(created_at);
CREATE INDEX idx_user_email ON users(email);

-- Monitor slow queries
SET GLOBAL slow_query_log = 1;
SET GLOBAL long_query_time = 2;
```

### Node.js Optimization
```bash
# Increase max open files
ulimit -n 65536

# Set max connections
export UV_THREADPOOL_SIZE=4

# Enable clustering (in production)
NODE_CLUSTER_ENABLED=true npm start
```

### Rate Limiting Adjustment
Edit `.env`:
```
RATE_LIMIT_MAX=1000     # More requests per minute
RATE_LIMIT_WINDOW_MS=60000  # Per 60 seconds
```

---

## Updating to New Version

```bash
# 1. Backup current version
cp -r /opt/billing /opt/billing.backup-$(date +%Y-%m-%d)

# 2. Deploy new code
git pull  # or copy new files

# 3. Install new dependencies
npm install --omit=dev

# 4. Rebuild
npm run build

# 5. Restart service
systemctl restart billing-app

# 6. Verify
curl http://localhost:3000/api/health

# 7. Monitor logs
tail -f /var/log/billing/app-*.log
```

---

## Security Checklist

- [ ] `.env` file has restricted permissions (600)
- [ ] Database user has minimal required privileges
- [ ] Firewall blocks direct DB access (only from app server)
- [ ] HTTPS configured on reverse proxy (nginx, HAProxy)
- [ ] CORS_ORIGIN set to your domain only
- [ ] Regular backups verified and tested
- [ ] Log files don't contain sensitive data
- [ ] Rate limiting enabled and monitored
- [ ] Admin users provisioned with strong passwords
- [ ] Audit logging enabled for sensitive operations

---

## Support & Debugging

### Enable Debug Logging
```bash
LOG_LEVEL=debug systemctl restart billing-app
```

### Export Logs for Analysis
```bash
# Last 1000 lines with errors
tail -n 1000 /var/log/billing/app-*.log | grep -E "ERROR|WARN" > debug.log

# Convert JSON logs to CSV for Excel
jq -r '[.level, .msg, .timestamp, .path] | @csv' /var/log/billing/app-*.log > audit.csv
```

### Performance Profiling
```bash
# CPU profiling (5 seconds)
node --prof dist/index.js &
PID=$!
sleep 5
kill $PID
node --prof-process isolate-*.log > profile.txt
```

---

**For additional help, check logs first, then refer to DEPLOYMENT.md**
