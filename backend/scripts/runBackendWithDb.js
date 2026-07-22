const net = require('net');
const { spawn } = require('child_process');
const path = require('path');

const mode = (process.argv[2] || 'dev').toLowerCase(); // dev | start | check
const dbHost = process.env.DB_HOST || '127.0.0.1';
const dbPort = parseInt(process.env.DB_PORT || '3306', 10);

function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

function canConnect(host, port, timeoutMs = 1000) {
  return new Promise((resolve) => {
    const socket = new net.Socket();
    let done = false;

    const finish = (ok) => {
      if (done) return;
      done = true;
      try { socket.destroy(); } catch {}
      resolve(ok);
    };

    socket.setTimeout(timeoutMs);
    socket.once('connect', () => finish(true));
    socket.once('timeout', () => finish(false));
    socket.once('error', () => finish(false));

    socket.connect(port, host);
  });
}

async function ensureDbRunning() {
  const alreadyUp = await canConnect(dbHost, dbPort);
  if (alreadyUp) {
    console.log(`[db] MariaDB already running on ${dbHost}:${dbPort}`);
    return;
  }

  if (process.platform !== 'win32') {
    throw new Error('Auto-start DB is only configured for Windows in this project. Start MariaDB manually.');
  }

  const localAppData = process.env.LOCALAPPDATA;
  if (!localAppData) throw new Error('LOCALAPPDATA is not set.');

  const base = path.join(localAppData, 'mariadb-portable');
  const mariadbd = path.join(base, 'bin', 'mariadbd.exe');
  const datadir = path.join(base, 'data');

  console.log(`[db] Starting MariaDB: ${mariadbd}`);
  const dbProc = spawn(
    mariadbd,
    [`--datadir=${datadir}`, `--port=${dbPort}`, '--bind-address=127.0.0.1', '--console'],
    {
      detached: true,
      stdio: 'ignore',
      windowsHide: true,
    }
  );
  dbProc.unref();

  const maxWaitMs = 20000;
  const startedAt = Date.now();
  while (Date.now() - startedAt < maxWaitMs) {
    if (await canConnect(dbHost, dbPort, 1200)) {
      console.log('[db] MariaDB started successfully.');
      return;
    }
    await sleep(600);
  }

  throw new Error(`MariaDB did not become ready on ${dbHost}:${dbPort} within ${maxWaitMs}ms`);
}

function runBackend(target) {
  const scriptName = target === 'start' ? 'start:backend' : 'dev:backend';

  console.log(`[api] Running npm run ${scriptName}`);
  const child = process.platform === 'win32'
    ? spawn('cmd.exe', ['/d', '/s', '/c', `npm run ${scriptName}`], {
        stdio: 'inherit',
        shell: false,
        windowsHide: false,
      })
    : spawn('npm', ['run', scriptName], {
        stdio: 'inherit',
        shell: false,
        windowsHide: false,
      });

  child.on('error', (err) => {
    console.error('[fatal]', err.message || err);
    process.exit(1);
  });

  child.on('exit', (code) => {
    process.exit(code || 0);
  });
}

(async () => {
  await ensureDbRunning();

  if (mode === 'check') {
    console.log('[ok] DB ready.');
    process.exit(0);
  }

  if (mode !== 'dev' && mode !== 'start') {
    throw new Error(`Unknown mode: ${mode}. Use dev, start, or check.`);
  }

  runBackend(mode);
})().catch((err) => {
  console.error('[fatal]', err.message || err);
  process.exit(1);
});
