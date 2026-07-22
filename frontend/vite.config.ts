import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

// The frontend talks to the backend under /api. In dev we proxy to the
// Express server (default http://localhost:4000). Override with VITE_API_TARGET.
export default defineConfig(() => {
  const apiTarget = process.env.VITE_API_TARGET || 'http://localhost:4000';
  return {
    plugins: [react()],
    server: {
      port: 5173,
      proxy: {
        '/api': { target: apiTarget, changeOrigin: true },
        '/uploads': { target: apiTarget, changeOrigin: true },
      },
    },
  };
});
