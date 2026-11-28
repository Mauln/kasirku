# Panduan Setup Awal Laravel (12/13) & Migrasi File AI Studio — Kasirku v2.0

> **Catatan Keselarasan Versi:**  
> Pada permintaan awal disebutkan *Setup Awal Laravel 12*, sedangkan dokumen resmi **Roadmap, Architecture v2.0, dan Rule v2.0** menetapkan stack **Laravel 13 + PHP 8.4 + MySQL 8.4 + Redis 7.4 + Nginx 1.28**. Karena itu, file `composer.json` pada project ini dikonfigurasi mendukung `"laravel/framework": "^12.0|^13.0"` dengan syarat PHP `^8.4`, sehingga kompatibel baik untuk Laravel 12 maupun Laravel 13.

---

## Bagian 1: Instalasi Tools yang Dibutuhkan di Komputer Lokal

Sebelum membuat project Laravel di mesin lokal Anda, pastikan seluruh perangkat lunak berikut terinstal:

| Tool | Versi Minimal | Fungsi | Cara Cek di Terminal |
|---|---|---|---|
| **PHP** | `8.4.x` | Runtime backend Laravel | `php -v` |
| **Composer** | `2.7+` | Package manager PHP | `composer -V` |
| **MySQL** | `8.4 LTS` | Database relasional utama (`utf8mb4_unicode_ci`) | `mysql --version` |
| **Redis** | `7.4+` | Driver session, cache, dan queue | `redis-cli ping` |
| **Node.js & npm** | `22 LTS` / `10+` | Menjalankan ESLint, Stylelint, Prettier, Husky, Playwright | `node -v && npm -v` |
| **Docker & Compose** | `Compose v2` | Containerisasi 7 layanan lokal | `docker compose version` |
| **Git** | `2.34+` | Version control & Git hooks | `git --version` |

### Contoh Instalasi Cepat
- **macOS (Homebrew):**
  ```bash
  brew install php@8.4 composer mysql@8.4 redis node@22 git gitleaks
  brew install --cask docker
  ```
- **Windows (Laragon / WSL2 Ubuntu 24.04 — Disarankan WSL2):**
  Aktifkan WSL2 Ubuntu 24.04 + Docker Desktop (integrasi WSL2), lalu instal PHP 8.4 (`ppa:ondrej/php`), Composer, Node 22, dan Git di dalam WSL2.
- **Linux (Ubuntu 24.04):**
  ```bash
  sudo add-apt-repository ppa:ondrej/php -y
  sudo apt update
  sudo apt install -y php8.4-cli php8.4-fpm php8.4-mysql php8.4-redis php8.4-mbstring \
    php8.4-xml php8.4-bcmath php8.4-intl php8.4-zip php8.4-gd unzip curl git
  ```

---

## Bagian 2: Pembuatan Project Laravel Baru & Konfigurasi Awal

1. **Buat project Laravel baru** di folder kerja Anda:
   ```bash
   # Untuk Laravel versi terbaru (12/13 sesuai release Composer):
   composer create-project laravel/laravel kasirku
   cd kasirku
   ```
   *(Jika ingin mengunci khusus Laravel 12 saat membuat awal: `composer create-project laravel/laravel:^12.0 kasirku`)*

2. **Generate Application Key:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Konfigurasi Awal `config` & `.env`:**
   Sesuaikan `.env` agar menggunakan zona waktu Indonesia (`Asia/Jakarta`), locale `id`, charset `utf8mb4`, serta Redis untuk session/cache/queue:
   ```env
   APP_NAME=Kasirku
   APP_ENV=local
   APP_DEBUG=true
   APP_URL=http://localhost:8000
   APP_TIMEZONE=Asia/Jakarta
   APP_LOCALE=id

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=kasirku_local
   DB_USERNAME=kasirku
   DB_PASSWORD=kasirku_local_secret

   SESSION_DRIVER=redis
   CACHE_STORE=redis
   QUEUE_CONNECTION=redis
   ```

---

## Bagian 3: Setup Database MySQL 8.4

1. Masuk ke MySQL client lokal (atau gunakan container MySQL dari Docker):
   ```sql
   CREATE DATABASE IF NOT EXISTS kasirku_local
     CHARACTER SET utf8mb4
     COLLATE utf8mb4_unicode_ci;

   CREATE USER IF NOT EXISTS 'kasirku'@'%' IDENTIFIED BY 'kasirku_local_secret';
   GRANT ALL PRIVILEGES ON kasirku_local.* TO 'kasirku'@'%';
   FLUSH PRIVILEGES;
   ```
2. Verifikasi koneksi dan migrasi bawaan Laravel:
   ```bash
   php artisan migrate:status
   php artisan migrate
   ```

---

## Bagian 4: Pengenalan Struktur Folder Laravel + Kasirku v2.0

| Folder / File | Fungsi Utama di Laravel & Kasirku v2.0 |
|---|---|
| `app/` | Logika backend: `Http/Controllers`, `Http/Middleware` (18 middleware), `Services/`, `Contracts/` (8 interface), `Models/` (80 model). |
| `bootstrap/` | Bootstrap framework Laravel dan registrasi middleware/exception handler. |
| `config/` | File konfigurasi aplikasi (`services.php` untuk adapter, `database.php`, `session.php`). |
| `database/` | `migrations/` (80 tabel berurutan) & `seeders/` (27 seeder). |
| `resources/views/` | Blade templates server-side rendering (`layouts/`, `components/`, `partials/`). |
| `resources/css/` | CSS murni + CSS Custom Properties (`tokens.css`, tanpa Tailwind). |
| `resources/js/` | JavaScript Vanilla ES Modules murni (tanpa React/Vue/TS/bundler). |
| `public/` | Entry point web (`index.php`) serta salinan aset `public/js/` & `public/css/`. |
| `routes/` | `web.php`, `api.php`, `partials.php` (AJAX fragment), `console.php`. |
| `docker/` | Konfigurasi container PHP 8.4 (`Dockerfile`, `php.ini`), Nginx 1.28 (`default.conf`), MySQL 8.4 (`my.cnf`). |
| `docs/` | Seluruh dokumen master (`prd.md`, `design.md`, `schema.md`, `architecture.md`, `rule.md`, `roadmap.md`) dan log per sub-fase. |
| `tests/` | Pest (Backend Unit/Feature) dan Playwright (50 E2E flow) serta `node:test` untuk verifikasi tooling. |

---

## Bagian 5: Menjalankan & Memverifikasi Project Laravel Awal

Sebelum memindahkan file dari AI Studio, pastikan Laravel murni berjalan normal:
```bash
php artisan about
php artisan serve --port=8000
```
Verifikasi:
1. Buka `http://localhost:8000` → halaman welcome Laravel tampil tanpa error.
2. Buka `http://localhost:8000/up` → endpoint health check bawaan Laravel merespons HTTP `200 OK`.

---

## Bagian 6: Memindahkan File Project AI Studio ke Folder Laravel Secara Aman

### 6.1 Pemeriksaan Sebelum Memindahkan
Project awal bawaan AI Studio berisi template **React + TypeScript + Tailwind + Vite** (`src/App.tsx`, `src/main.tsx`, `src/index.css`, `tsconfig.json`, `vite.config.ts`, dan `package.json` berbasis React).  
Berdasarkan **Rule v2.0 §2.5 (Aturan Emas #41–42)** dan **Architecture v2.0 §2.1**, Kasirku **MELARANG** penggunaan React, Vue, Svelte, Angular, TypeScript, Tailwind, maupun bundler JS untuk frontend.

### 6.2 Aturan Pemindahan Aman (Agar Tidak Merusak Struktur Laravel)
1. **JANGAN menimpa** folder inti Laravel: `app/`, `bootstrap/`, `config/`, `database/`, `public/index.php`, `vendor/`, `artisan`, atau `routes/`.
2. **Jangan memindahkan** file template React/TS/Tailwind (`src/`, `tsconfig.json`, `vite.config.ts`) ke dalam project Laravel — file-file tersebut sudah dihapus dan digantikan oleh `resources/js/`, `resources/css/`, `eslint.config.js`, `.stylelintrc.json`, dan `scripts/build-vanilla.mjs` di workspace ini.
3. **Salin/gabungkan** aset dan konfigurasi dari workspace AI Studio ini ke project Laravel Anda:
   - `docker-compose.yml`, `docker/`, `.dockerignore`
   - `.github/workflows/ci.yml`, `.github/PULL_REQUEST_TEMPLATE.md`
   - `.husky/pre-commit`, `.husky/commit-msg`, `.gitleaks.toml`
   - `eslint.config.js`, `.stylelintrc.json`, `.prettierrc`, `.prettierignore`, `pint.json`, `phpstan.neon`
   - `package.json` (gabungkan devDependencies: `eslint`, `globals`, `stylelint`, `stylelint-config-standard`, `prettier`, `husky`)
   - `scripts/build-vanilla.mjs`, `scripts/dev-server.mjs`
   - `resources/css/subfase-0a.css`, `resources/js/subfase-0a.js`
   - `docs/` dan `public/assets/aistudio/` (dokumen referensi asli)
   - Gabungkan `composer.json` (pastikan `laravel/sanctum`, `laravel/horizon`, `predis/predis`, `larastan/larastan`, `laravel/pint`, `pestphp/pest` terpasang via `composer require`).

---

## Bagian 7: Setup Git dan GitHub

1. **Inisialisasi repository lokal dengan branch `main` dan `develop`:**
   ```bash
   git init -b main
   git config user.name "Nama Anda"
   git config user.email "email@anda.com"
   ```
2. **Aktifkan Git hooks (Husky):**
   ```bash
   npm install
   npx husky
   chmod +x .husky/pre-commit .husky/commit-msg
   ```
3. **Buat commit awal di `develop` (karena commit langsung ke `main` diblokir oleh `.husky/pre-commit` sesuai Aturan Emas #3):**
   - Pertama, buat commit fondasi awal saat inisialisasi di branch sementara atau langsung inisialisasi `main` sebelum mengaktifkan hook, lalu buat branch `develop`:
   ```bash
   git add .
   git commit --no-verify -m "chore(init): initial baseline repository setup"
   git checkout -b develop
   ```
4. **Hubungkan ke GitHub & Push:**
   - Buat repository **private** baru di GitHub bernama `kasirku` (kosong tanpa README/gitignore agar tidak konflik).
   - Hubungkan remote dan push kedua branch:
   ```bash
   git remote add origin https://github.com/<username>/kasirku.git
   git push -u origin main
   git push -u origin develop
   ```
5. **Aktifkan Branch Protection di GitHub (`Settings → Branches → Add branch protection rule`):**
   - Branch name pattern: `main`
   - Centang **Require a pull request before merging** (min 1 approval; 2 untuk kategori kritis).
   - Centang **Require status checks to pass before merging** → pilih job `CI 11 Langkah Kasirku`.
   - Centang **Do not allow bypassing the above settings**.

---

## Bagian 8: Setup Docker Lokal (7 Services)

1. Pastikan `.env` sudah ada (`cp .env.example .env`) dengan `DB_HOST=mysql` dan `REDIS_HOST=redis`.
2. Build & jalankan 7 container (`app`, `nginx`, `mysql`, `redis`, `queue`, `scheduler`, `phpmyadmin`):
   ```bash
   docker compose up -d --build
   ```
3. Periksa status seluruh container:
   ```bash
   docker compose ps
   ```
   Semua 7 layanan harus berstatus `Up` (`mysql` dan `redis` berstatus `healthy`).
4. Install dependency PHP di dalam container `app` dan jalankan migrasi awal:
   ```bash
   docker compose exec app composer install
   docker compose exec app php artisan key:generate
   docker compose exec app php artisan migrate
   ```
5. Verifikasi akses:
   - Aplikasi Laravel via Nginx: `http://localhost:8000`
   - phpMyAdmin: `http://localhost:8080` (Server: `mysql`, User: `kasirku`, Password: `kasirku_local_secret`)
   - Log queue worker: `docker compose logs -f queue`
   - Log scheduler: `docker compose logs -f scheduler`
