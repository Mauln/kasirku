import assert from "node:assert/strict";
import fs from "node:fs";
import path from "node:path";
import test from "node:test";
import { ESLint } from "eslint";
import stylelint from "stylelint";

const rootDir = process.cwd();

test("package.json tidak mengandung framework atau bundler terlarang (Aturan Emas #41-42)", () => {
  const pkg = JSON.parse(fs.readFileSync(path.join(rootDir, "package.json"), "utf8"));
  const deps = Object.keys({ ...(pkg.dependencies || {}), ...(pkg.devDependencies || {}) });
  const forbidden = [
    "react",
    "react-dom",
    "vue",
    "svelte",
    "@angular/core",
    "@inertiajs/inertia",
    "alpinejs",
    "htmx.org",
    "tailwindcss",
    "typescript",
    "vite",
    "webpack",
    "chart.js",
    "d3",
    "hammerjs",
  ];
  for (const name of forbidden) {
    assert.equal(deps.includes(name), false, `Dependency terlarang ditemukan: ${name}`);
  }
});

test("tidak ada file TypeScript (.ts/.tsx) di proyek", () => {
  const foundTs = [];
  function walk(dir) {
    for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
      if (["node_modules", "vendor", ".git", ".playwright-cli"].includes(entry.name)) continue;
      const fullPath = path.join(dir, entry.name);
      if (entry.isDirectory()) walk(fullPath);
      else if (/\.(ts|tsx)$/.test(entry.name)) foundTs.push(fullPath);
    }
  }
  walk(rootDir);
  assert.deepEqual(foundTs, []);
});

test("ESLint flat config memblokir import React dan assignment innerHTML dinamis", async () => {
  const eslint = new ESLint({
    overrideConfigFile: path.join(rootDir, "eslint.config.js"),
  });

  const [reactResult] = await eslint.lintText('import React from "react"; console.warn(React);', {
    filePath: path.join(rootDir, "resources", "js", "bad-react.js"),
  });
  assert.ok(
    reactResult.messages.some((m) => m.ruleId === "no-restricted-imports"),
    "ESLint harus menolak import react"
  );

  const [xssResult] = await eslint.lintText(
    "const el = document.body; const input = 'x'; el.innerHTML = input;",
    { filePath: path.join(rootDir, "resources", "js", "bad-xss.js") }
  );
  assert.ok(
    xssResult.messages.some((m) => m.ruleId === "no-restricted-syntax"),
    "ESLint harus menolak assignment innerHTML dinamis"
  );
});

test("Stylelint memblokir warna hex (#ff0000) sesuai color-no-hex", async () => {
  const result = await stylelint.lint({
    code: ".card-box { color: #ff0000; }",
    configFile: path.join(rootDir, ".stylelintrc.json"),
  });
  assert.equal(result.errored, true, "Stylelint harus gagal untuk warna hex");
  assert.ok(
    result.results[0].warnings.some((w) => w.rule === "color-no-hex"),
    "Aturan color-no-hex harus terpicu"
  );
});

test("docker-compose.yml mendefinisikan 7 layanan wajib Sub-Fase 0A", () => {
  const compose = fs.readFileSync(path.join(rootDir, "docker-compose.yml"), "utf8");
  const requiredServices = [
    "app:",
    "nginx:",
    "mysql:",
    "redis:",
    "queue:",
    "scheduler:",
    "phpmyadmin:",
  ];
  for (const svc of requiredServices) {
    assert.ok(compose.includes(svc), `Service Docker wajib tidak ditemukan: ${svc}`);
  }
  assert.ok(compose.includes("mysql:8.4"), "Versi MySQL harus 8.4");
  assert.ok(compose.includes("redis:7.4"), "Versi Redis harus 7.4");
  assert.ok(compose.includes("nginx:1.28"), "Versi Nginx harus 1.28");
});

test(".github/workflows/ci.yml memuat 11 langkah wajib Rule §10.4", () => {
  const ci = fs.readFileSync(path.join(rootDir, ".github", "workflows", "ci.yml"), "utf8");
  for (let i = 1; i <= 11; i += 1) {
    assert.ok(ci.includes(`"${i}/11`), `Langkah CI ${i}/11 tidak ditemukan di ci.yml`);
  }
});

test("MANIFEST_PERUBAHAN.json sinkron dengan file nyata yang dibuat dan dihapus", () => {
  const manifest = JSON.parse(
    fs.readFileSync(path.join(rootDir, "docs", "sub-fase-0a", "MANIFEST_PERUBAHAN.json"), "utf8")
  );
  for (const entry of manifest.entries) {
    if (entry.changeType === "New" || entry.changeType === "Modified") {
      assert.equal(
        fs.existsSync(path.join(rootDir, entry.path)),
        true,
        `File '${entry.path}' tercatat ${entry.changeType} tetapi tidak ditemukan di workspace`
      );
    }
    if (entry.changeType === "Deleted") {
      assert.equal(
        fs.existsSync(path.join(rootDir, entry.path)),
        false,
        `File '${entry.path}' tercatat Deleted tetapi masih ada di workspace`
      );
    }
  }
});
