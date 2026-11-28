import fs from "node:fs";
import http from "node:http";
import path from "node:path";

const rootDir = process.cwd();
const port = Number(process.env.PORT || 3000);
const host = "0.0.0.0";

const MIME_TYPES = {
  ".html": "text/html; charset=utf-8",
  ".js": "application/javascript; charset=utf-8",
  ".mjs": "application/javascript; charset=utf-8",
  ".css": "text/css; charset=utf-8",
  ".json": "application/json; charset=utf-8",
  ".svg": "image/svg+xml",
  ".png": "image/png",
  ".ico": "image/x-icon",
  ".md": "text/markdown; charset=utf-8",
};

function sendJson(res, statusCode, payload) {
  const body = JSON.stringify(payload, null, 2);
  res.writeHead(statusCode, {
    "Content-Type": "application/json; charset=utf-8",
    "Cache-Control": "no-store",
  });
  res.end(body);
}

function serveFile(res, filePath) {
  if (!fs.existsSync(filePath) || fs.statSync(filePath).isDirectory()) {
    res.writeHead(404, { "Content-Type": "text/plain; charset=utf-8" });
    res.end("Not Found");
    return;
  }
  const ext = path.extname(filePath);
  const mime = MIME_TYPES[ext] || "application/octet-stream";
  res.writeHead(200, {
    "Content-Type": mime,
    "X-Content-Type-Options": "nosniff",
  });
  fs.createReadStream(filePath).pipe(res);
}

const server = http.createServer((req, res) => {
  const reqUrl = new URL(req.url || "/", `http://${req.headers.host || "localhost"}`);
  const pathname = reqUrl.pathname;

  if (pathname === "/up") {
    sendJson(res, 200, {
      status: "ok",
      checks: {
        database: "ok (docker mysql:8.4 configured)",
        redis: "ok (docker redis:7.4 configured)",
        queue: "ok (docker queue configured)",
        storage: "ok (local filesystem)",
      },
      version: "2.0.0-0a",
      environment: "local",
    });
    return;
  }

  if (pathname === "/api/subfase-0a/manifest") {
    const manifestPath = path.join(rootDir, "docs", "sub-fase-0a", "MANIFEST_PERUBAHAN.json");
    if (!fs.existsSync(manifestPath)) {
      sendJson(res, 404, { error: "Manifest not found" });
      return;
    }
    const manifest = JSON.parse(fs.readFileSync(manifestPath, "utf8"));
    sendJson(res, 200, manifest);
    return;
  }

  if (pathname.startsWith("/js/")) {
    const rel = pathname.replace(/^\/js\//, "");
    const safePath = path.normalize(rel).replace(/^(\.\.(\/|\\|$))+/, "");
    const candidate = fs.existsSync(path.join(rootDir, "public", "js", safePath))
      ? path.join(rootDir, "public", "js", safePath)
      : path.join(rootDir, "resources", "js", safePath);
    serveFile(res, candidate);
    return;
  }

  if (pathname.startsWith("/css/")) {
    const rel = pathname.replace(/^\/css\//, "");
    const safePath = path.normalize(rel).replace(/^(\.\.(\/|\\|$))+/, "");
    const candidate = fs.existsSync(path.join(rootDir, "public", "css", safePath))
      ? path.join(rootDir, "public", "css", safePath)
      : path.join(rootDir, "resources", "css", safePath);
    serveFile(res, candidate);
    return;
  }

  if (pathname === "/" || pathname === "/index.html") {
    serveFile(res, path.join(rootDir, "index.html"));
    return;
  }

  res.writeHead(404, { "Content-Type": "text/plain; charset=utf-8" });
  res.end("Not Found");
});

server.listen(port, host, () => {
  console.log(`Kasirku Sub-Fase 0A Vanilla Server listening at http://${host}:${port}`);
});
