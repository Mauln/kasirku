import http from 'node:http';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const ROOT_DIR = path.resolve(__dirname, '..');
const PORT = Number(process.env.PORT || 3000);
const HOST = '0.0.0.0';

const MIME_TYPES = {
  '.css': 'text/css; charset=utf-8',
  '.js': 'application/javascript; charset=utf-8',
  '.mjs': 'application/javascript; charset=utf-8',
  '.html': 'text/html; charset=utf-8',
  '.json': 'application/json; charset=utf-8',
  '.svg': 'image/svg+xml',
  '.png': 'image/png',
  '.ico': 'image/x-icon',
  '.md': 'text/markdown; charset=utf-8',
};

function renderLandingBlade() {
  const bladePath = path.join(ROOT_DIR, 'resources/views/landing.blade.php');
  const template = fs.readFileSync(bladePath, 'utf8');
  const version = process.env.APP_VERSION || '0.1.0';
  const deployEnv = process.env.DEPLOY_ENV || 'local';

  return template
    .replaceAll('{{ csrf_token() }}', 'local-preview-csrf-token')
    .replaceAll("{{ config('app.deploy_env', 'local') }}", deployEnv)
    .replaceAll("{{ config('app.version', '0.1.0') }}", version)
    .replaceAll("{{ strtoupper((string) config('app.deploy_env', 'local')) }}", deployEnv.toUpperCase())
    .replace(/\{\{\s*asset\('([^']+)'\)\s*\}\}/g, '/$1');
}

function serveSafeFile(res, baseDir, reqSubPath) {
  const safeBase = path.resolve(ROOT_DIR, baseDir);
  const target = path.resolve(safeBase, reqSubPath.replace(/^\/+/, ''));
  if (!target.startsWith(safeBase + path.sep) && target !== safeBase) {
    res.writeHead(403, { 'Content-Type': 'text/plain; charset=utf-8' });
    res.end('Forbidden');
    return;
  }
  if (!fs.existsSync(target) || fs.statSync(target).isDirectory()) {
    res.writeHead(404, { 'Content-Type': 'text/plain; charset=utf-8' });
    res.end('Not Found');
    return;
  }
  const ext = path.extname(target).toLowerCase();
  res.writeHead(200, { 'Content-Type': MIME_TYPES[ext] || 'application/octet-stream' });
  fs.createReadStream(target).pipe(res);
}

const server = http.createServer((req, res) => {
  const urlPath = (req.url || '/').split('?')[0];

  if (urlPath === '/' || urlPath === '/index.html') {
    const html = renderLandingBlade();
    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    res.end(html);
    return;
  }

  if (urlPath === '/up' || urlPath === '/api/health') {
    res.writeHead(200, { 'Content-Type': 'application/json; charset=utf-8' });
    res.end(
      JSON.stringify({
        status: 'ok',
        app: 'Kasirku',
        version: process.env.APP_VERSION || '0.1.0',
        environment: process.env.DEPLOY_ENV || 'local',
        note: 'Static preview server for AI Studio; run Docker Compose locally for full Laravel 13 + MySQL 8.4 + Redis 7.4.',
      })
    );
    return;
  }

  if (urlPath.startsWith('/css/')) {
    serveSafeFile(res, 'resources/css', urlPath.slice('/css/'.length));
    return;
  }

  if (urlPath.startsWith('/js/')) {
    serveSafeFile(res, 'resources/js', urlPath.slice('/js/'.length));
    return;
  }

  if (urlPath.startsWith('/docs/')) {
    serveSafeFile(res, 'docs', urlPath.slice('/docs/'.length));
    return;
  }

  res.writeHead(404, { 'Content-Type': 'text/plain; charset=utf-8' });
  res.end('Not Found');
});

server.listen(PORT, HOST, () => {
  console.log(`Kasirku preview server running at http://${HOST}:${PORT}`);
});
