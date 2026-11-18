# VERIFIKASI STEP 0.1 — FOUNDATION SETUP

Dokumen ini berisi checklist verifikasi lengkap untuk **Fase 0 — Step 0.1** sesuai panduan Kasirku, dijalankan di **laptop lokal** setelah setup Docker, Composer, Git, dan GitHub selesai.

---

## A. Verifikasi Prasyarat Terminal

| # | Item Cek | Perintah Uji | Hasil yang Diharapkan | Status |
|---|---|---|---|---|
| 1 | PHP 8.4 | `php -v` | `PHP 8.4.x` | ☐ |
| 2 | Composer 2.x | `composer --version` | `Composer version 2.x` | ☐ |
| 3 | Docker 24.x+ | `docker --version` | `Docker version 24.x+` | ☐ |
| 4 | Docker Compose v2 | `docker compose version` | `Docker Compose version v2.x+` | ☐ |
| 5 | Node.js 18+ | `node -v` | `v18.x+` | ☐ |
| 6 | npm 9+ | `npm -v` | `9.x+` | ☐ |
| 7 | Git 2.x | `git --version` | `git version 2.x` | ☐ |

---

## B. Verifikasi Laravel & Dependencies Backend

| # | Item Cek | Perintah Uji | Hasil yang Diharapkan | Status |
|---|---|---|---|---|
| 1 | Laravel 13 terinstall | `docker compose exec app php artisan --version` | `Laravel Framework 13.x` | ☐ |
| 2 | Package backend terinstall | `docker compose exec app composer show \| grep -E "sanctum\|predis\|horizon\|intervention\|dompdf\|excel"` | 6 package terdaftar | ☐ |
| 3 | `APP_KEY` ter-generate | `grep APP_KEY .env` | `APP_KEY=base64:...` (tidak kosong) | ☐ |

---

## C. Verifikasi Konfigurasi `.env` & `.env.example`

| # | Item Cek | Perintah Uji | Hasil yang Diharapkan | Status |
|---|---|---|---|---|
| 1 | `.env.example` punya 50+ env vars | `grep -cE "^[A-Z0-9_]+=" .env.example` | ≥ 50 variabel | ☐ |
| 2 | Tidak ada secret di `.env.example` | `grep "^APP_KEY=" .env.example` | `APP_KEY=` (kosong) | ☐ |
| 3 | Session driver = Redis | `grep "^SESSION_DRIVER=" .env.example` | `SESSION_DRIVER=redis` | ☐ |
| 4 | Queue & Cache = Redis | `grep -E "^(QUEUE_CONNECTION\|CACHE_DRIVER)=" .env.example` | `redis` | ☐ |
| 5 | Charset & collation DB | `grep -E "^(DB_CHARSET\|DB_COLLATION)=" .env.example` | `utf8mb4` & `utf8mb4_unicode_ci` | ☐ |
| 6 | 8 Adapter drivers terkonfigurasi | `grep -E "^(OTP_DRIVER\|MAIL_MAILER\|STORAGE_DRIVER\|SUPPORT_CHAT_DRIVER\|KB_DRIVER\|STATUS_DRIVER\|ERROR_TRACKER_DRIVER\|PRINTER_DRIVER)=" .env.example` | 8 baris tampil | ☐ |

---

## D. Verifikasi Struktur Folder (Architecture §4)

| # | Item Cek | Perintah Uji | Hasil yang Diharapkan | Status |
|---|---|---|---|---|
| 1 | Struktur `app/` lengkap | `ls -d app/Http/Controllers/{Merchant,Admin,Auth,Production} app/Contracts app/Services/*` | Semua folder ada | ☐ |
| 2 | Struktur `resources/` lengkap | `ls -d resources/views/{layouts,components,merchant,admin,auth,legal,errors,partials} resources/css/{components,layouts,pages,states} resources/js/{core,modules,components,utils}` | Semua folder ada | ☐ |
| 3 | Struktur `docker/`, `docs/`, `tests/` | `ls -d docker/{nginx,php,mysql} docs/runbooks tests/{Feature,Unit,E2E}` | Semua folder ada | ☐ |

---

## E. Verifikasi Docker & Layanan Lokal

| # | Item Cek | Perintah Uji | Hasil yang Diharapkan | Status |
|---|---|---|---|---|
| 1 | 7 Docker services running | `docker compose ps` | `app`, `nginx`, `mysql`, `redis`, `queue`, `scheduler`, `phpmyadmin` status `Up` | ☐ |
| 2 | Halaman Laravel tampil | Buka `http://localhost:8000` | Tampil **"Kasirku — Coming Soon"** | ☐ |
| 3 | PhpMyAdmin tampil | Buka `http://localhost:8080` | Halaman login PhpMyAdmin tampil | ☐ |
| 4 | Koneksi MySQL 8.4 OK | `docker compose exec app php artisan tinker --execute="DB::connection()->getPdo(); echo 'DB OK';"` | Output `DB OK` | ☐ |
| 5 | Koneksi Redis 7.4 OK | `docker compose exec app php artisan tinker --execute="Redis::ping(); echo 'Redis OK';"` | Output `Redis OK` | ☐ |

---

## F. Verifikasi Backend Config & Vanilla Frontend

| # | Item Cek | Perintah Uji | Hasil yang Diharapkan | Status |
|---|---|---|---|---|
| 1 | `config/services.php` (8 adapter) | Cek `config/services.php` | Berisi `otp`, `email`, `storage`, `support`, `error_tracker`, `printer`, `analytics` | ☐ |
| 2 | `config/app.php` metadata | Cek `config/app.php` | `timezone=Asia/Jakarta`, `locale=id`, `faker_locale=id_ID`, `deploy_env`, `version`, `git_commit`, `guard_rules_enforced`, `demo_mode` | ☐ |
| 3 | `routes/web.php` landing | Cek `routes/web.php` | `Route::get('/', fn () => view('landing'));` | ☐ |
| 4 | `package.json` bersih (vanilla) | Cek `package.json` | HANYA ada `eslint`, `globals`, `stylelint`, `stylelint-config-standard`, `prettier`, `@playwright/test` | ☐ |
| 5 | TIDAK ADA framework terlarang | `grep -iE "react\|vue\|svelte\|angular\|typescript\|tailwind\|inertia\|alpine\|htmx\|livewire" package.json` | Tidak ada hasil (exit code 1) | ☐ |
| 6 | Linter JS & CSS lokal | `npm install && npm run lint:js && npm run lint:css` | Lulus tanpa error | ☐ |

---

## G. Verifikasi Git, GitHub & Dokumentasi

| # | Item Cek | Perintah Uji | Hasil yang Diharapkan | Status |
|---|---|---|---|---|
| 1 | Git terinisialisasi | `git status` | Working tree clean di branch `develop` | ☐ |
| 2 | Branch `develop` & `main` ada | `git branch -a` | Ada `develop` & `main` (lokal dan `origin/*`) | ☐ |
| 3 | Repo GitHub **Private** | Cek halaman repo GitHub | Visibility = `Private` | ☐ |
| 4 | File `.env` tidak ter-commit | `git ls-files .env` | Output kosong (hanya `.env.example` yang di-track) | ☐ |
| 5 | `docs/setup-guide.md` tersedia | `test -f docs/setup-guide.md && echo OK` | `OK` | ☐ |
| 6 | `docs/git-github-guide.md` tersedia | `test -f docs/git-github-guide.md && echo OK` | `OK` | ☐ |
| 7 | `README.md` tersedia | `test -f README.md && echo OK` | `OK` | ☐ |

---

## Ringkasan Sign-off Step 0.1

- **Tanggal Uji Lokal:** `____-__-__`
- **Tester / Developer:** `_________________`
- **Hasil Akhir Step 0.1:** ☐ PASS / ☐ PERLU PERBAIKAN

> Setelah seluruh item di atas berstatus **PASS** di laptop lokal, commit dokumentasi/verifikasi jika perlu dan lanjutkan ke **Fase 0 — Step 0.2 (Contracts, Adapters & Guard Rules)**.
