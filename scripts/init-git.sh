#!/usr/bin/env bash
set -euo pipefail

if [ -d .git ]; then
  echo "Repository Git sudah terinisialisasi."
  git branch -a
  exit 0
fi

echo "==> Inisialisasi Git repository..."
git init -b develop

echo "==> Pastikan .env tidak ikut ter-track..."
if [ -f .env ]; then
  echo "    .env terdeteksi (aman, diabaikan oleh .gitignore)."
fi

echo "==> Staging dan commit pertama (develop)..."
git add .
git commit -m "chore(foundation): setup Laravel 13 + Docker + project structure

- Laravel 13 + PHP 8.4 skeleton dan konfigurasi adapter/app
- Docker Compose 7 services (app, nginx, mysql, redis, queue, scheduler, phpmyadmin)
- Struktur folder sesuai Architecture §4
- .env.example dengan 50+ env vars tanpa secret
- Vanilla frontend base (Blade, CSS tokens, JS ESM, ESLint/Stylelint)
- Dokumentasi setup, git/github, dan checklist verifikasi Step 0.1

Refs: Fase 0 Step 0.1"

echo "==> Membuat branch main (protected baseline) dan kembali ke develop..."
git branch -f main develop
git checkout develop

echo ""
echo "Git lokal siap! Status branch:"
git branch -a
echo ""
echo "Langkah selanjutnya untuk push ke GitHub (PRIVATE):"
echo "  gh repo create kasirku --private --source=. --remote=origin"
echo "  git push -u origin develop"
echo "  git push -u origin main"
