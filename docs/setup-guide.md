# SETUP GUIDE — KASIRKU

**Fase:** Fase 0 — Step 0.1 (Foundation)  
**Stack:** Laravel 13 + PHP 8.4 + MySQL 8.4 + Redis 7.4 + Docker Compose + Vanilla HTML/CSS/JS  

---

## 1. Prasyarat (Laptop Lokal)

Pastikan semua tools berikut sudah terinstall di laptop lokal:

- **PHP 8.4.x** (`php -v`)
- **Composer 2.x** (`composer --version`)
- **Docker 24.x+** & **Docker Compose v2.x+** (`docker --version` & `docker compose version`)
- **Node.js 18+** & **npm 9+** — hanya untuk ESLint, Stylelint, Prettier, dan Playwright (`node -v` & `npm -v`)
- **Git 2.x+** (`git --version`)
- **GitHub CLI** (`gh --version`, opsional untuk membuat repo via terminal)

---

## 2. Langkah Setup Lokal (Menggunakan Repo Ini)

### Opsi A — Menggunakan Docker Compose (Direkomendasikan)

1. Clone repository atau masuk ke folder project `kasirku`:
   ```bash
   git clone git@github.com:USERNAME/kasirku.git
   cd kasirku
   ```
2. Salin `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   ```
3. Salin aset vanilla JS & CSS ke `public/` (tanpa bundler):
   ```bash
   cp -r resources/js/* public/js/
   cp -r resources/css/* public/css/
   ```
4. Build dan jalankan seluruh 7 container Docker:
   ```bash
   docker compose up -d --build
   ```
5. Cek seluruh service berjalan:
   ```bash
   docker compose ps
   ```
   Pastikan 7 service berstatus `Up`: `app`, `nginx`, `mysql`, `redis`, `queue`, `scheduler`, `phpmyadmin`.
6. Install dependencies PHP/Laravel di dalam container:
   ```bash
   docker compose exec app composer install --no-interaction
   ```
7. Generate application key:
   ```bash
   docker compose exec app php artisan key:generate
   ```
8. Verifikasi versi Laravel:
   ```bash
   docker compose exec app php artisan --version
   # Expected: Laravel Framework 13.x
   ```
9. Test koneksi MySQL & Redis:
   ```bash
   docker compose exec app php artisan tinker --execute="DB::connection()->getPdo(); echo 'DB OK';"
   docker compose exec app php artisan tinker --execute="Redis::ping(); echo 'Redis OK';"
   ```
10. Buka di browser:
    - Aplikasi Laravel: http://localhost:8000 → tampil **"Kasirku — Coming Soon"**
    - PhpMyAdmin: http://localhost:8080 → login `root` / `secret`

> **Shortcut:** Anda juga bisa menjalankan `./scripts/setup-local.sh` di laptop lokal untuk menjalankan langkah 2–9 secara otomatis.

### Opsi B — Setup Manual dari Nol (Jika Ingin Membuat Fresh via `composer create-project`)

Jika ingin mengulang bootstrap Laravel 13 dari awal di mesin lokal:

```bash
composer create-project laravel/laravel kasirku "13.*"
cd kasirku
composer require laravel/sanctum predis/predis laravel/horizon intervention/image barryvdh/laravel-dompdf maatwebsite/excel
cp .env.example .env
php artisan key:generate
```

Lalu ikuti konfigurasi `.env`, `config/app.php`, `config/services.php`, `docker-compose.yml`, `package.json`, dan struktur folder sesuai panduan Architecture §4.

---

## 3. Setup Tooling Frontend Vanilla (Lokal)

Frontend Kasirku murni menggunakan **HTML + CSS + Vanilla JS (ES modules) + Blade** tanpa bundler JS dan tanpa framework. Node.js hanya dipakai untuk linting, formatting, dan E2E test:

```bash
npm install
npm run lint:js
npm run lint:css
npm run format:check
```

---

## 4. Verifikasi Step 0.1

Lihat daftar cek lengkap beserta perintah uji dan expected output di:
👉 [`docs/verifikasi-step-0.1.md`](./verifikasi-step-0.1.md)

Ringkasan cek cepat:
- [ ] `php artisan --version` → Laravel 13.x
- [ ] `docker compose ps` → 7 services Up
- [ ] http://localhost:8000 → halaman "Kasirku — Coming Soon" tampil
- [ ] http://localhost:8080 → PhpMyAdmin tampil
- [ ] DB connection test OK
- [ ] Redis connection test OK
- [ ] `.env.example` punya 50+ env vars (tanpa secret)
- [ ] `package.json` HANYA punya `eslint`, `globals`, `stylelint`, `stylelint-config-standard`, `prettier`, `@playwright/test`
- [ ] TIDAK ADA `react`, `vue`, `typescript`, `tailwind` di `package.json`
- [ ] Repo GitHub dibuat (**private**), branch `develop` + `main` ada di remote
- [ ] `docs/setup-guide.md` & `docs/verifikasi-step-0.1.md` tersedia

---

## 5. Troubleshooting

| Masalah | Solusi |
|---|---|
| Port `8000` atau `8080` sudah dipakai | Ubah mapping port di `docker-compose.yml` (mis. `"8001:80"`) |
| Permission denied di `storage/` atau folder project | Jalankan `sudo chown -R $USER:$USER .` dan `chmod -R 775 storage bootstrap/cache` |
| `composer install` gagal atau timeout | Jalankan `docker compose exec app composer install --no-interaction --prefer-dist` |
| Koneksi MySQL ditolak saat pertama kali start | Tunggu 10–15 detik sampai MySQL selesai inisialisasi, lalu uji ulang `docker compose exec app php artisan tinker` |
| `CSS` atau `JS` tidak termuat di `localhost:8000` | Pastikan file dari `resources/css/*` dan `resources/js/*` sudah di-copy ke `public/css/` dan `public/js/` |
