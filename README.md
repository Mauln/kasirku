# Kasirku v2.0

**Tagline:** *"Kasir online UMKM Indonesia — cepat, murah, tanpa ribet."*

Kasirku adalah SaaS POS (Point of Sale) responsif berbasis web untuk UMKM Indonesia dengan arsitektur **Laravel (PHP 8.4) + MySQL 8.4 + Redis 7.4 + Nginx 1.28** dan **Vanilla Frontend murni (HTML + CSS Custom Properties + JavaScript ES Modules + Blade)** — tanpa React, Vue, Svelte, Angular, TypeScript, Tailwind, Inertia, Alpine, HTMX, maupun Livewire.

## Status Pengerjaan Saat Ini
- **Fase Aktif:** Fase 0 — **Sub-Fase 0A (Inisialisasi Repository, Docker Lokal & Pipeline CI)**
- **Panduan & Materi Pembelajaran:**
  - `docs/sub-fase-0a/PANDUAN_SETUP_LARAVEL_DAN_MIGRASI_AISTUDIO.md`
  - `docs/sub-fase-0a/PANDUAN_SUB_FASE_0A.md`
  - `docs/sub-fase-0a/PERUBAHAN_STRUKTUR_PROJECT.md`
- **Dokumen Master Kasirku v2.0:** `docs/roadmap.md`, `docs/prd.md`, `docs/architecture.md`, `docs/schema.md`, `docs/design.md`, `docs/rule.md`

## Quick Start (Lokal)

### 1. Verifikasi Tooling & Vanilla Stack (Node.js)
```bash
npm ci
npm run lint
npm test
npm run build
npm run dev   # Menjalankan portal verifikasi Sub-Fase 0A di http://localhost:3000
```

### 2. Menjalankan Stack Docker Lengkap (7 Services)
```bash
cp .env.example .env
docker compose up -d --build
docker compose ps
```
Layanan yang berjalan: `app` (PHP 8.4-FPM), `nginx` (`http://localhost:8000`), `mysql` (MySQL 8.4 port `3306`), `redis` (Redis 7.4 port `6379`), `queue`, `scheduler`, dan `phpmyadmin` (`http://localhost:8080`).

## Strategi Branch & Commit
- Branch utama: `main` (dilindungi, hanya menerima PR) dan `develop` (integrasi harian).
- Commit wajib mengikuti **Conventional Commits** (`<type>(<scope>): <deskripsi>`, maks 72 karakter) yang dijaga oleh `.husky/commit-msg` dan `.husky/pre-commit`.
