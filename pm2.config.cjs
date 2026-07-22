// PM2 process config for the SEPL Billing portal.
//
// One Node process serves BOTH the API and the built React SPA
// (FRONTEND_DIST). nginx only reverse-proxies the domain to PORT.
//
// Pick the deployment target with PM2's --env flag:
//   pm2 start   pm2.config.cjs --env demo
//   pm2 start   pm2.config.cjs --env production
//   pm2 reload  pm2.config.cjs --env demo --update-env
//
// APP_ENV then selects backend/.env.<APP_ENV>; NODE_ENV stays "production"
// on both VMs so hardening (rate limits, error masking) is always on.
const base = {
  name: 'billing-api',
  cwd: './backend',
  script: './dist/index.js',
  instances: 1,
  exec_mode: 'fork',
  autorestart: true,
  watch: false,
  max_memory_restart: '512M',
};

module.exports = {
  apps: [
    {
      ...base,
      // Default (no --env) behaves like production.
      env: {
        NODE_ENV: 'production',
        APP_ENV: 'production',
        PORT: 4000,
        FRONTEND_DIST: '../frontend/dist',
      },
      env_demo: {
        NODE_ENV: 'production',
        APP_ENV: 'demo',
        PORT: 4000,
        FRONTEND_DIST: '../frontend/dist',
      },
      env_production: {
        NODE_ENV: 'production',
        APP_ENV: 'production',
        PORT: 4000,
        FRONTEND_DIST: '../frontend/dist',
      },
    },
  ],
};
