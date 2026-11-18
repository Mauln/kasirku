#!/usr/bin/env bash
set -euo pipefail

echo "==> [1/6] Menyalin .env.example ke .env (jika belum ada)..."
if [ ! -f .env ]; then
  cp .env.example .env
fi

echo "==> [2/6] Menyalin aset vanilla JS & CSS ke public/..."
mkdir -p public/js public/css
cp -r resources/js/* public/js/
cp -r resources/css/* public/css/

echo "==> [3/6] Menjalankan Docker Compose (7 services)..."
docker compose up -d --build

echo "==> [4/6] Install dependencies PHP via Composer..."
docker compose exec app composer install --no-interaction

echo "==> [5/6] Generate APP_KEY..."
docker compose exec app php artisan key:generate --ansi

echo "==> [6/6] Verifikasi koneksi DB & Redis..."
docker compose exec app php artisan --version
docker compose exec app php artisan tinker --execute="DB::connection()->getPdo(); echo 'DB OK' . PHP_EOL;"
docker compose exec app php artisan tinker --execute="Redis::ping(); echo 'Redis OK' . PHP_EOL;"

echo ""
echo "Setup lokal selesai! Buka:"
echo "  - Kasirku    : http://localhost:8000"
echo "  - PhpMyAdmin : http://localhost:8080"
echo "Lanjutkan pengecekan dengan checklist di docs/verifikasi-step-0.1.md"
