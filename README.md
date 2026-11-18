# Kasirku

**Kasir online UMKM Indonesia — cepat, murah, tanpa ribet.**

Repository fondasi **Fase 0 — Step 0.1** untuk platform SaaS POS Kasirku.

---

## Arsitektur & Aturan Wajib

- **Backend:** Laravel 13 + PHP 8.4 + MySQL 8.4 (`utf8mb4_unicode_ci`) + Redis 7.4 (session, cache, queue)
- **Frontend:** HTML + CSS murni (custom properties) + JavaScript vanilla (ES modules) + Laravel Blade SSR
- **Tanpa Framework / Bundler JS:** Tidak menggunakan React, Vue, Svelte, Angular, TypeScript, Tailwind CSS, Inertia, Alpine, HTMX, atau Livewire
- **Container Lokal:** Docker Compose 7 services (`app`, `nginx`, `mysql`, `redis`, `queue`, `scheduler`, `phpmyadmin`)
- **Database Conventions:** Uang menggunakan `DECIMAL(15,2)`, status menggunakan `VARCHAR(20-30)`, UUID menggunakan `CHAR(36)`

---

## Quick Start (Laptop Lokal)

```bash
# 1. Salin environment config
cp .env.example .env

# 2. Jalankan setup lokal (atau ikuti langkah manual di docs/setup-guide.md)
./scripts/setup-local.sh

# 3. Buka di browser
# Aplikasi : http://localhost:8000
# PhpMyAdmin: http://localhost:8080
```

---

## Dokumentasi

- [`docs/setup-guide.md`](./docs/setup-guide.md) — Panduan prasyarat, instalasi Docker/Laravel lokal, dan troubleshooting
- [`docs/git-github-guide.md`](./docs/git-github-guide.md) — Panduan branch strategy (`develop` & `main`), GitHub private repo, dan commit convention
- [`docs/verifikasi-step-0.1.md`](./docs/verifikasi-step-0.1.md) — Checklist verifikasi lengkap Step 0.1 untuk pengujian di laptop lokal
