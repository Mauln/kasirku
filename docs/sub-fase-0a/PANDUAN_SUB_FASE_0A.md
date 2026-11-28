# Panduan Pengerjaan & Pembelajaran — Sub-Fase 0A

**Sub-Fase:** 0A — Inisialisasi Repository, Docker Lokal & Pipeline CI  
**Status:** Selesai Diimplementasikan & Diverifikasi (Menunggu Push GitHub & Eksekusi Docker/Composer di Mesin Lokal)  
**Referensi:** `docs/roadmap.md` (Fase 0: Sub-fase 0A), `docs/rule.md` (§2.5, §5, §6, §7, §8.5, §10.4), `docs/architecture.md` (§2, §4, §13)

---

## 1. Tujuan Sub-Fase 0A
Sub-Fase 0A bertujuan menyiapkan **fondasi rekayasa perangkat lunak** sebelum baris kode fitur bisnis pertama ditulis:
1. Lingkungan eksekusi lokal yang identik antar-developer melalui **Docker Compose (7 layanan)**.
2. Tata kelola kode melalui **strategi branch (`main` & `develop`), Git hooks (pre-commit & commit-msg), dan template Pull Request**.
3. Penegakan arsitektur **Vanilla Frontend murni** secara otomatis melalui ESLint 9+/10 flat config, Stylelint, Prettier, dan skrip validasi build tanpa bundler.
4. Pipeline **CI 11 langkah wajib** di GitHub Actions agar setiap perubahan otomatis diperiksa kualitas, keamanan, dan kesiapannya.

---

## 2. Materi Pembelajaran

### 2.1 Mengapa Menggunakan Docker 7 Layanan?
Aplikasi kasir SaaS bukan hanya PHP yang melayani HTTP request. Kasirku membutuhkan:
- **`app` (PHP 8.4-FPM):** Menjalankan PHP secara terpisah dari web server.
- **`nginx` (Nginx 1.28):** Melayani file statis (`/js/*`, `/css/*`) dengan cepat dan meneruskan request PHP ke `app:9000` via FastCGI.
- **`mysql` (MySQL 8.4):** Menyimpan 80 tabel transaksional dengan `utf8mb4_unicode_ci` dan timezone `Asia/Jakarta` (`+07:00`).
- **`redis` (Redis 7.4):** Menyimpan session (Aturan Emas #11: session tidak di DB), cache feature flag, serta antrean queue.
- **`queue`:** Memproses job asinkron seperti pengiriman analytics (`analytics` queue) dan export CSV agar transaksi kasir tetap <1,5 detik.
- **`scheduler`:** Menjalankan 18 scheduled command (kedaluwarsa trial, retention cleanup, rekonsiliasi stok).
- **`phpmyadmin`:** Antarmuka web lokal untuk menginspeksi tabel MySQL tanpa repot instalasi klien desktop.

### 2.2 Mengapa Melarang Framework JS & Bundler? (Aturan Emas #41–44)
Kasirku dirancang untuk mesin kasir (tablet/HP kelas menengah di UMKM). Dengan HTML + Blade + CSS Custom Properties + Vanilla ES Modules:
- Tidak ada proses kompilasi TypeScript/JSX atau overhead Virtual DOM saat kasir mengetuk produk.
- Aturan ESLint `no-restricted-imports` dan `no-restricted-syntax` mencegah developer secara tidak sengaja memasukkan `react`, `vue`, `chart.js`, atau celah XSS (`element.innerHTML = userInput`).
- Aturan Stylelint `color-no-hex: true` memaksa developer memakai design token (CSS variables) atau format `rgb()`/`hsl()` yang konsisten, serta penamaan kelas **BEM** (`.block__element--modifier`).

### 2.3 Bagaimana Pipeline CI 11 Langkah Bekerja?
Setiap kali Anda melakukan `push` atau membuka Pull Request ke `develop`/`main`, GitHub Actions (`.github/workflows/ci.yml`) menjalankan 11 gerbang berurutan:
1. **Pint** → Memastikan gaya kode PHP rapi sesuai standar Laravel.
2. **ESLint** → Memblokir framework terlarang & celah `innerHTML`.
3. **Stylelint** → Memastikan token CSS & aturan BEM dipatuhi.
4. **Prettier** → Memastikan indentasi dan format file JS/CSS konsisten.
5. **Pest (Backend)** → Menjalankan test PHP melawan MySQL 8.4 & Redis 7.4 asli di container CI.
6. **Node Test (Frontend/Tooling)** → Menjalankan unit test JS vanilla (`node:test`).
7. **Audit Dependency** → Memeriksa kerentanan CVE di `composer audit` dan `npm audit`.
8. **Gitleaks** → Mencegah kebocoran API key/password di riwayat Git.
9. **Trivy** → Memindai misconfiguration pada `Dockerfile` dan `docker-compose.yml`.
10. **Guard Rules Dry-Run** → Simulasi `php artisan app:check-production` agar driver dev tidak lolos ke produksi.
11. **Coverage Check** → Memastikan target cakupan test terpenuhi.

---

## 3. Penjelasan File yang Dibuat & Diubah pada Sub-Fase 0A

| File | Penjelasan Teknis |
|---|---|
| `docker-compose.yml` & `docker/*` | Mendefinisikan 7 container, PHP extensions (`pdo_mysql`, `redis`, `gd`, `intl`, `bcmath`, `pcntl`), Nginx FastCGI + static asset caching, dan MySQL `utf8mb4_unicode_ci`. |
| `eslint.config.js` | ESLint Flat Config memblokir import React/Vue/Inertia/Alpine/HTMX/Svelte/Angular/Chart.js/D3/Hammer.js dan assignment dinamis ke `innerHTML`. |
| `.stylelintrc.json` | Memaksa `color-no-hex: true`, custom property kebab-case, dan selector class berpola BEM. |
| `.prettierrc` & `.prettierignore` | Menyeragamkan format penulisan JS dan CSS. |
| `pint.json` & `phpstan.neon` | Konfigurasi kualitas PHP (Pint preset Laravel & PHPStan Level 6 di `app/`). |
| `.github/workflows/ci.yml` | Definisi CI 11 langkah lengkap dengan service MySQL 8.4 & Redis 7.4. |
| `.github/PULL_REQUEST_TEMPLATE.md` | Checklist PR wajib berisi pengecekan Vanilla Frontend, Adapter/Guard, dan Keamanan. |
| `.husky/pre-commit` & `.husky/commit-msg` | Hook otomatis yang menolak commit langsung ke `main`, file `.env`, error lint/format, dan pesan commit non-Conventional. |
| `scripts/build-vanilla.mjs` | Memverifikasi tidak ada dependency terlarang di `package.json` dan menyalin aset `resources/js` & `resources/css` ke `public/` tanpa bundler JS. |
| `scripts/dev-server.mjs` | Server lokal Node.js murni (port `3000`) untuk pratinjau portal Sub-Fase 0A dan endpoint `/up` + `/api/subfase-0a/manifest`. |
| `resources/css/subfase-0a.css` & `resources/js/subfase-0a.js` | Antarmuka portal Sub-Fase 0A 100% Vanilla (zero hex di CSS, zero dynamic `innerHTML` di JS). |
| `tests/Unit/SubFase0A/tooling.test.mjs` | Suite verifikasi otomatis menggunakan `node:test` yang menguji semua aturan di atas secara nyata. |

---

## 4. Prosedur Verifikasi & Pengujian Sub-Fase 0A

### 4.1 Pengujian Tooling, Linter & Vanilla Compliance
- **Cara Menjalankan:**
  ```bash
  npm run lint
  npm run build
  ```
- **Hasil yang Diharapkan:**
  - `eslint resources/js/` keluar dengan kode `0` (0 error, 0 warning).
  - `stylelint 'resources/css/**/*.css'` keluar dengan kode `0`.
  - `prettier --check 'resources/**/*.{js,css}'` keluar dengan kode `0`.
  - `node scripts/build-vanilla.mjs` menampilkan pesan penyalinan file JS & CSS ke `public/` tanpa dependency terlarang.

### 4.2 Pengujian Unit & Integrasi Konfigurasi (`node:test`)
- **Cara Menjalankan:**
  ```bash
  npm test
  ```
- **Apa yang Diuji:**
  1. `package.json` bersih dari React, Vue, TypeScript, Tailwind, Vite, Webpack, Inertia, Alpine, HTMX.
  2. Tidak ada file `.ts`/`.tsx` di proyek.
  3. Uji negatif ESLint: kode yang mengimpor `react` atau menulis `el.innerHTML = userInput` **benar-benar ditolak** oleh konfigurasi ESLint kita.
  4. Uji negatif Stylelint: kode CSS dengan warna hex `#ff0000` **benar-benar ditolak** oleh konfigurasi Stylelint kita.
  5. `docker-compose.yml` memuat seluruh 7 service (`app`, `nginx`, `mysql`, `redis`, `queue`, `scheduler`, `phpmyadmin`).
  6. `.github/workflows/ci.yml` memuat 11 langkah sesuai Rule §10.4.
  7. `MANIFEST_PERUBAHAN.json` sinkron dengan file nyata di disk.

### 4.3 Pengujian Frontend & Endpoint Lokal (Port 3000 + Playwright Browser Check)
- **Cara Menjalankan:**
  1. Jalankan server lokal `npm run dev` (berjalan di `http://localhost:3000`).
  2. Cek endpoint `/up` dan `/api/subfase-0a/manifest` mengembalikan HTTP `200 OK` JSON valid.
  3. Buka di headless Chromium (`playwright-cli`), uji perpindahan 4 tab, uji filter perubahan file (`ALL`, `New`, `Modified`, `Deleted`), periksa 0 console error, 0 failed network request, serta responsivitas desktop (`1440×900`) dan mobile (`390×844`).

### 4.4 Pengujian Backend & Database (Di Mesin Lokal dengan PHP 8.4 & Docker)
- **Cara Menjalankan di Lokal Anda:**
  ```bash
  docker compose up -d --build
  docker compose ps
  docker compose exec app php -v
  docker compose exec mysql mysql -ukasirku -pkasirku_local_secret -e "SHOW VARIABLES LIKE 'character_set_server';"
  docker compose exec redis redis-cli ping
  ```
- **Kriteria Keberhasilan:**
  - `app` menjalankan PHP 8.4.x; `mysql` merespons `utf8mb4`; `redis` membalas `PONG`; Nginx melayani di `http://localhost:8000`; phpMyAdmin terbuka di `http://localhost:8080`.
