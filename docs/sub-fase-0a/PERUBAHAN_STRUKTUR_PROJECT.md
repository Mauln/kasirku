# Dokumentasi Perubahan Struktur Project — Sub-Fase 0A

**Sub-Fase:** 0A (Inisialisasi Repository, Docker Lokal & Pipeline CI)  
**Tanggal:** 2026-11-28  
**Ringkasan:** Dokumentasi lengkap seluruh file dan folder yang ditambahkan (*New*), diperbarui (*Modified*), dihapus (*Deleted*), dan dipindahkan (*Moved*) selama pengerjaan Sub-Fase 0A.

---

## 1. Ditambahkan (New)

| Path | Fungsi | Alasan Penambahan | Status |
|---|---|---|---|
| `composer.json` | Manifest dependensi PHP 8.4, Laravel 12/13, Sanctum, Horizon, Predis, Pint, PHPStan, Pest | Fondasi backend Laravel sesuai Architecture §2.2 & §4 | Selesai |
| `pint.json` | Konfigurasi Laravel Pint (preset `laravel`) | Memenuhi CI langkah #1 (Lint PHP) | Selesai |
| `phpstan.neon` | Konfigurasi analisis statis PHPStan Level 6+ untuk `app/` | Memenuhi standar kualitas kode PHP (Rule §8.5) | Selesai |
| `docker-compose.yml` | Orkestrasi 7 container lokal (`app`, `nginx`, `mysql`, `redis`, `queue`, `scheduler`, `phpmyadmin`) | Memenuhi Roadmap Sub-Fase 0A & Architecture §13.5 | Selesai |
| `docker/php/Dockerfile` | Image PHP 8.4-FPM Alpine beserta ekstensi MySQL, Redis, GD, Intl, Zip, dan Composer 2 | Runtime backend Laravel terisolasi | Selesai |
| `docker/php/php.ini` | Pengaturan PHP (`memory_limit=256M`, `opcache.enable=1`, `Asia/Jakarta`) | Konsistensi konfigurasi PHP antar-developer | Selesai |
| `docker/nginx/default.conf` | Konfigurasi Nginx 1.28 untuk Laravel `public/index.php` dan cache aset statis `/js`, `/css` | Web server lokal sesuai stack resmi | Selesai |
| `docker/mysql/my.cnf` | Konfigurasi MySQL 8.4 (`utf8mb4_unicode_ci`, strict mode, `+07:00`) | Memenuhi Schema §1.2 & §8.1 | Selesai |
| `.dockerignore` | Pengecualian direktori berat & sensitif saat build Docker | Mempercepat build image dan menjaga keamanan | Selesai |
| `eslint.config.js` | ESLint 9+/10 flat config pemblokir React/Vue/TS/Inertia/Alpine/HTMX dan dynamic `innerHTML` | Enforcement Vanilla JS (Rule §8.5 & Aturan Emas #41-43) | Selesai |
| `.stylelintrc.json` | Stylelint config dengan `color-no-hex: true` dan selector BEM | Enforcement design token CSS (Rule §8.5) | Selesai |
| `.prettierrc` | Konfigurasi formatting Prettier untuk JS dan CSS | Menjamin konsistensi format kode (CI langkah #4) | Selesai |
| `.prettierignore` | Daftar file yang diabaikan oleh Prettier | Menghindari formatting pada vendor/assets | Selesai |
| `.gitleaks.toml` | Aturan pemindaian secret Gitleaks dengan allowlist `.env.example` & `docs/` | Memenuhi CI langkah #8 & pre-commit security | Selesai |
| `.github/workflows/ci.yml` | Pipeline GitHub Actions 11 langkah wajib | Memenuhi Rule §10.4 & Architecture §13.1 | Selesai |
| `.github/PULL_REQUEST_TEMPLATE.md` | Template Pull Request berisi checklist Vanilla, Guard, Adapter, dan Fitur POS | Memenuhi Rule §6.1 | Selesai |
| `.husky/pre-commit` | Pre-commit hook pemblokir commit ke `main`, file `.env`, error lint, dan secret | Memenuhi Roadmap Sub-Fase 0A & Rule §8.5 | Selesai |
| `.husky/commit-msg` | Commit-msg hook validasi Conventional Commits (maks 72 karakter) | Memenuhi Rule §7.1–§7.7 | Selesai |
| `scripts/build-vanilla.mjs` | Verifikator kepatuhan Vanilla Stack dan penyalin aset `resources/` ke `public/` tanpa bundler | Memenuhi Rule §8.2 & Architecture §13.6 | Selesai |
| `scripts/dev-server.mjs` | Server HTTP Node.js murni (port 3000) untuk pratinjau portal Sub-Fase 0A & `/up` | Menjaga preview AI Studio tetap aktif tanpa React/Vite | Selesai |
| `resources/css/subfase-0a.css` | Stylesheet CSS murni berbasis custom properties & BEM (tanpa hex) untuk portal 0A | Memenuhi Stylelint & tampilan portal Sub-Fase 0A | Selesai |
| `resources/js/subfase-0a.js` | Modul ES Vanilla JS (menggunakan `createElement` + `textContent`) untuk portal 0A | Interaktivitas portal 0A tanpa `innerHTML` dinamis | Selesai |
| `tests/Unit/SubFase0A/tooling.test.mjs` | Suite pengujian `node:test` untuk linter, Docker, CI 11 langkah, dan manifest | Verifikasi otomatis kualitas Sub-Fase 0A | Selesai |
| `README.md` | Dokumentasi ringkas root proyek, quick start, dan konvensi branch | Panduan masuk awal repository | Selesai |
| `docs/prd.md`, `docs/design.md`, `docs/schema.md`, `docs/architecture.md`, `docs/rule.md`, `docs/roadmap.md` | Salinan resmi 6 dokumen master Kasirku v2.0 di dalam folder `/docs/` | Memenuhi Rule §9.2 (dokumen disimpan di `/docs` repo) | Selesai |
| `docs/sub-fase-0a/PANDUAN_SETUP_LARAVEL_DAN_MIGRASI_AISTUDIO.md` | Panduan lengkap setup Laravel 12/13, MySQL, migrasi file AI Studio, Git/GitHub, dan Docker | Materi pembelajaran & panduan eksekusi bertahap | Selesai |
| `docs/sub-fase-0a/PANDUAN_SUB_FASE_0A.md` | Panduan teknis, pembelajaran, dan verifikasi Sub-Fase 0A | Dokumentasi & materi pembelajaran Sub-Fase 0A | Selesai |
| `docs/sub-fase-0a/PERUBAHAN_STRUKTUR_PROJECT.md` | Dokumen riwayat perubahan struktur file dan folder Sub-Fase 0A | Keterlacakan riwayat perkembangan struktur project | Selesai |
| `docs/sub-fase-0a/MANIFEST_PERUBAHAN.json` | Manifest JSON terstruktur daftar New/Modified/Deleted/Moved Sub-Fase 0A | Sumber data otomatis untuk verifikasi & portal UI | Selesai |

---

## 2. Diperbarui / Diubah (Modified)

| Path | Fungsi | Alasan Perubahan | Status |
|---|---|---|---|
| `package.json` | Konfigurasi npm scripts & devDependencies | Menghapus React, TypeScript, Tailwind, Vite (terlarang oleh Aturan Emas #41-42) dan memasang ESLint, Stylelint, Prettier, Husky | Selesai |
| `index.html` | Halaman root aplikasi pratinjau | Menyelaraskan `<title>` & meta OpenGraph dengan `Kasirku v2.0`, mengganti mount React `/src/main.tsx` menjadi portal Vanilla ES module `/js/subfase-0a.js` | Selesai |
| `metadata.json` | Metadata aplikasi AI Studio | Mengisi `name` (`Kasirku v2.0`) dan `description` dari placeholder kosong | Selesai |
| `.env.example` | Template variabel environment lokal | Menambahkan seluruh key env Kasirku v2.0 (MySQL 8.4, Redis 7.4, Adapter drivers, POS/Kasbon config) tanpa secret | Selesai |
| `.gitignore` | Daftar pengecualian Git | Menambahkan pengecualian Laravel (`vendor/`, `storage/`, `.env`), build `public/js/`, `public/css/`, dan `.playwright-cli/` | Selesai |

---

## 3. Dihapus (Deleted)

| Path | Fungsi Sebelumnya | Alasan Penghapusan | Status |
|---|---|---|---|
| `src/App.tsx` | Komponen starter React+TS kosong bawaan AI Studio | Dilarang oleh Aturan Emas #41-42 (tidak boleh ada React/TypeScript); digantikan `resources/js/subfase-0a.js` | Selesai |
| `src/main.tsx` | Entry point ReactDOM bawaan AI Studio | Digantikan modul ES Vanilla di `resources/js/subfase-0a.js` | Selesai |
| `src/index.css` | Stylesheet Tailwind v4 bawaan AI Studio | Dilarang oleh Aturan Emas #41 (tidak boleh Tailwind); digantikan `resources/css/subfase-0a.css` | Selesai |
| `src/` | Direktori sumber TypeScript/React starter | Kosong setelah penghapusan file starter React/TS | Selesai |
| `tsconfig.json` | Konfigurasi compiler TypeScript | Dilarang oleh Rule §8.5 ("Tidak ada TypeScript compiler / tsconfig.json") | Selesai |
| `vite.config.ts` | Konfigurasi bundler Vite + plugin React & Tailwind | Dilarang oleh Rule §8.5 & Aturan Emas #42 (tanpa bundler JS) | Selesai |

---

## 4. Dipindahkan / Diubah Lokasinya (Moved)

| Path Asal | Path Tujuan | Alasan Pemindahan | Status |
|---|---|---|---|
| `public/assets/aistudio/*.txt` (referensi lampiran awal) | Disinkronkan ke `docs/{prd,design,schema,architecture,rule,roadmap}.md` (salinan kerja) | Memenuhi Rule §9.2 agar seluruh dokumen induk tersimpan terstruktur di `/docs/`, sambil mempertahankan file sumber asli di `public/assets/aistudio/` | Selesai |
