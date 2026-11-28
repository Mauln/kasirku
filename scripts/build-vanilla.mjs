import fs from "node:fs";
import path from "node:path";

const rootDir = process.cwd();
const verifyOnly = process.argv.includes("--verify-only");

const FORBIDDEN_PACKAGES = [
  "react",
  "react-dom",
  "vue",
  "@inertiajs/inertia",
  "@inertiajs/vue3",
  "@inertiajs/react",
  "alpinejs",
  "htmx.org",
  "svelte",
  "@angular/core",
  "tailwindcss",
  "@tailwindcss/vite",
  "typescript",
  "vite",
  "@vitejs/plugin-react",
  "webpack",
  "chart.js",
  "d3",
  "hammerjs",
];

function assertVanillaCompliance() {
  const pkgPath = path.join(rootDir, "package.json");
  const pkg = JSON.parse(fs.readFileSync(pkgPath, "utf8"));
  const allDeps = {
    ...(pkg.dependencies || {}),
    ...(pkg.devDependencies || {}),
  };

  const foundForbidden = Object.keys(allDeps).filter((dep) => FORBIDDEN_PACKAGES.includes(dep));
  if (foundForbidden.length > 0) {
    throw new Error(
      `Aturan Emas #41-42 dilanggar! Dependency terlarang ditemukan di package.json: ${foundForbidden.join(", ")}`
    );
  }

  const tsFiles = [];
  function scanForForbiddenFiles(dir) {
    if (!fs.existsSync(dir)) return;
    for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
      if (entry.name === "node_modules" || entry.name === "vendor" || entry.name === ".git") {
        continue;
      }
      const fullPath = path.join(dir, entry.name);
      if (entry.isDirectory()) {
        scanForForbiddenFiles(fullPath);
      } else if (/\.(ts|tsx|jsx|vue|svelte)$/.test(entry.name)) {
        tsFiles.push(path.relative(rootDir, fullPath));
      }
    }
  }
  scanForForbiddenFiles(path.join(rootDir, "resources"));

  if (tsFiles.length > 0) {
    throw new Error(`File non-vanilla terdeteksi di resources/: ${tsFiles.join(", ")}`);
  }
}

function copyDirRecursive(src, dest) {
  if (!fs.existsSync(src)) return 0;
  fs.mkdirSync(dest, { recursive: true });
  let count = 0;
  for (const entry of fs.readdirSync(src, { withFileTypes: true })) {
    const srcPath = path.join(src, entry.name);
    const destPath = path.join(dest, entry.name);
    if (entry.isDirectory()) {
      count += copyDirRecursive(srcPath, destPath);
    } else {
      fs.copyFileSync(srcPath, destPath);
      count += 1;
    }
  }
  return count;
}

assertVanillaCompliance();

if (!verifyOnly) {
  const jsCount = copyDirRecursive(
    path.join(rootDir, "resources", "js"),
    path.join(rootDir, "public", "js")
  );
  const cssCount = copyDirRecursive(
    path.join(rootDir, "resources", "css"),
    path.join(rootDir, "public", "css")
  );
  console.log(
    `[Kasirku Vanilla Build] Selesai: ${jsCount} file JS dan ${cssCount} file CSS disalin ke public/ tanpa bundler.`
  );
} else {
  console.log("[Kasirku Vanilla Check] Kepatuhan Vanilla Stack (Aturan Emas #41-42) LULUS.");
}
