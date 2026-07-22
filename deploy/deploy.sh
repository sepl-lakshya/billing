#!/usr/bin/env bash
set -euo pipefail

# ============================================================================
# One-command deploy for the SEPL Billing portal.
#
# Usage:
#   bash deploy/deploy.sh [demo|production] [--pull]
#
# - Target may be omitted if you created deploy/.target once on this VM
#   (a git-ignored one-line file, e.g.  echo demo > deploy/.target ).
# - --pull runs `git pull --ff-only` before building.
#
# What it does: install deps -> build backend -> build frontend for the target
# -> (re)start the single Node process under PM2 with the right environment.
# nginx just reverse-proxies the domain to 127.0.0.1:4000.
# ============================================================================

cd "$(dirname "$0")/.."        # -> repo root
ROOT="$(pwd)"

TARGET=""
DO_PULL=0
for arg in "$@"; do
  case "$arg" in
    demo|production) TARGET="$arg" ;;
    --pull) DO_PULL=1 ;;
    *) echo "Unknown argument: $arg" >&2; exit 1 ;;
  esac
done

# Fall back to the per-VM target marker.
if [ -z "$TARGET" ] && [ -f deploy/.target ]; then
  TARGET="$(tr -d ' \t\r\n' < deploy/.target)"
fi

if [ "$TARGET" != "demo" ] && [ "$TARGET" != "production" ]; then
  echo "ERROR: deployment target must be 'demo' or 'production'." >&2
  echo "  Pass it as an argument, or set it once per VM:" >&2
  echo "    echo demo > deploy/.target        # on the demo VM" >&2
  echo "    echo production > deploy/.target   # on the apps VM" >&2
  exit 1
fi

echo "=================================================="
echo " Deploying target : $TARGET"
echo " Repository       : $ROOT"
echo "=================================================="

if [ "$DO_PULL" -eq 1 ]; then
  echo "==> git pull --ff-only"
  git pull --ff-only
fi

echo "==> Installing backend dependencies"
npm --prefix backend ci || npm --prefix backend install

echo "==> Installing frontend dependencies"
npm --prefix frontend ci || npm --prefix frontend install

echo "==> Building backend (TypeScript -> dist)"
npm --prefix backend run build

echo "==> Building frontend (Vite, mode: $TARGET)"
npm --prefix frontend run build -- --mode "$TARGET"

echo "==> (Re)starting the app with PM2 (env: $TARGET)"
if pm2 describe billing-api >/dev/null 2>&1; then
  pm2 reload pm2.config.cjs --env "$TARGET" --update-env
else
  pm2 start pm2.config.cjs --env "$TARGET"
fi
pm2 save >/dev/null

echo "=================================================="
echo " Done. '$TARGET' is live on 127.0.0.1:4000 (behind nginx)."
echo " Health: curl -s http://127.0.0.1:4000/api/health"
echo "=================================================="
