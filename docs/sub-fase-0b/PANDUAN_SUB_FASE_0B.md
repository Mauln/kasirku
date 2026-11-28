# Panduan Pengerjaan & Pembelajaran — Sub-Fase 0B

**Sub-Fase:** 0B — Kontrak Adapter, Guard Rules & Skema Database Core  
**Referensi Resmi:** `docs/roadmap.md` (Sub-fase 0B), `docs/architecture.md` (§5 & §8), `docs/schema.md` (§4 Bagian 1–2 & §7.1), `docs/prd.md` (§7.3)

---

## 1. Tujuan Sub-Fase 0B
Sub-Fase 0B membangun **tiga fondasi inti aplikasi Laravel** sebelum modul autentikasi merchant dan fitur bisnis dimulai:
1. **Adapter Pattern (8 Contracts + 25 Implementations):** Mengisolasi seluruh layanan eksternal (OTP WhatsApp, Email, Object Storage, Error Tracking, Support Chat, Knowledge Base, Status Page, dan Printer Thermal) di balik interface PHP pada `app/Contracts/` dan di-binding di `AppServiceProvider::register()` berdasar `config/services.php`.
2. **8 Guard Rules Production:** Melindungi environment produksi agar aplikasi otomatis gagal *boot* (fail-fast) apabila ada driver dev/unsafe (`log`, `fake`, `sync`, `APP_DEBUG=true`, kredensial kosong, non-HTTPS) yang tertinggal aktif, serta menyediakan perintah `php artisan app:check-production` untuk gate CI/CD.
3. **Skema Database Core (#1–#7) & Reference Seeders Idempoten:** Membuat 7 tabel domain Shared (`plans`, `features`, `plan_features`, `add_ons`) dan Admin/Internal (`admin_users`, `admin_sessions`, `partners`) lengkap dengan `utf8mb4_unicode_ci`, method `down()` untuk rollback, 7 Model Eloquent, serta 5 Reference Seeders (`PlanSeeder`, `FeatureSeeder`, `PlanFeatureSeeder` 21×6 = 126 baris, `AddOnSeeder`, dan `AdminSeeder`).

---

## 2. Materi Pembelajaran

### 2.1 Mengapa Menggunakan Adapter Pattern & 8 Contracts?
Tanpa Adapter Pattern, kode controller/service sering memanggil SDK pihak ketiga atau `Http::post('https://api.fonnte.com/...')` secara langsung. Dampaknya:
- Sulit diuji secara lokal/CI tanpa biaya pulsa OTP atau koneksi internet eksternal.
- Sulit berpindah vendor (misal dari `Mailto` di MVP menjadi `Chatwoot` di Production, atau `LocalStorage` di lokal menjadi `Cloudflare R2`/`MinIO` di cloud).

Dengan memisahkan **Contract (`app/Contracts/*.php`)** dan **Implementations (`app/Services/Adapters/*`)**:
- Controller cukup meminta `OtpSender $otp` melalui constructor injection.
- Saat pengujian Pest, cukup set `OTP_DRIVER=fake` agar container menyuntikkan `FakeOtpSender`.
- Untuk ketahanan produksi, `CompositeOtpSender` memanggil `FonnteOtpSender` sebagai primary dan otomatis jatuh (*fallback*) ke `EmailOtpSender` sambil mencatat log audit `otp.fallback`.

### 2.2 Bagaimana Guard Rules Production Bekerja?
Di Laravel, kesalahan konfigurasi `.env` saat deploy (misal lupa mengubah `APP_DEBUG=false` atau `OTP_DRIVER=fonnte`) bisa membocorkan stack trace atau membuat OTP pengguna tidak terkirim.
`ProductionGuardService::evaluate()` memeriksa 8 kondisi wajib (`OTP_DRIVER`, `MAIL_MAILER`, `APP_DEBUG`, `TELESCOPE_ENABLED`, `SESSION_DRIVER`, `QUEUE_CONNECTION`, `PROVIDER_CREDENTIALS`, serta `APP_URL & FORCE_HTTPS`).
- Di `AppServiceProvider::boot()`, pengecekan berjalan otomatis apabila `app()->isProduction()` dan `GUARD_RULES_ENFORCED=true`.
- Di pipeline CI (`.github/workflows/ci.yml` langkah 10), perintah `php artisan app:check-production --dry-run` dijalankan untuk memastikan command guard berjalan bebas error sintaks.

### 2.3 Dependency-Ordered Migrations & Idempotent Seeders
1. **Urutan Tabel (#1–#7):** `plan_features` (#3) memiliki foreign key ke `plans` (#1) dan `features` (#2), sedangkan `admin_sessions` (#6) memiliki foreign key ke `admin_users` (#5). Penomoran file migrasi memastikan tabel induk selalu tercipta lebih dahulu.
2. **Charset & Engine:** Setiap tabel menegakkan `InnoDB` dengan `utf8mb4` + `utf8mb4_unicode_ci` agar kompatibel antara MySQL 8.4 lokal/VPS dan TiDB Cloud Serverless.
3. **Idempotency Seeder:** Semua reference seeder memakai `Model::query()->updateOrCreate(...)`. Jika `php artisan db:seed` dijalankan 2× atau 10×, jumlah baris tetap tepat **6 plans, 21 features, 126 plan_features, 3 add_ons, dan 1 super admin**.
4. **Keamanan Password AdminSeeder:** Password super admin dibaca dari `env('SEED_ADMIN_PASSWORD')`; tidak ada password literal yang di-hardcode di kode sumber.

---

## 3. Ringkasan Implementasi File Utama

| Kelompok | Lokasi | Penjelasan Teknis |
|---|---|---|
| **8 Contracts** | `app/Contracts/*.php` | Interface `OtpSender`, `EmailSender`, `StorageDriver`, `ErrorReporter`, `SupportLinkProvider`, `KnowledgeBaseLinkProvider`, `StatusPageLinkProvider`, `PrinterDriver`. |
| **25 Adapters** | `app/Services/Adapters/{Otp,Email,Storage,Error,Link,Printer}/` | Implementasi `Log*`, `Fake*`, `CompositeOtpSender`, `FonnteOtpSender`, `LocalStorageDriver` (anti path-traversal), `S3StorageDriver`, `SentryErrorReporter`, `BluetoothPrinterDriver`, `CssPrintDriver`, dll. |
| **Guard Rules** | `app/Services/Guard/ProductionGuardService.php`, `app/Console/Commands/CheckProduction.php` | Evaluasi 8 guard rules, audit log `env_guard_failed`, dan command artisan `app:check-production`. |
| **Provider & Config** | `app/Providers/AppServiceProvider.php`, `config/{services,kasirku}.php` | Container binding seluruh 8 contracts + shared Blade `$app` variables + guard check saat boot. |
| **Migrations (#1–#7)** | `database/migrations/2026_11_28_000001..000007_*.php` | DDL tabel `plans`, `features`, `plan_features`, `add_ons`, `admin_users`, `admin_sessions`, `partners` beserta `down()`. |
| **Models (7)** | `app/Models/{Plan,Feature,PlanFeature,AddOn,AdminUser,AdminSession,Partner}.php` | Eloquent casts, relasi `belongsToMany`/`hasMany`, dan `$hidden` untuk field sensitif admin. |
| **Reference Seeders (5)** | `database/seeders/{Plan,Feature,PlanFeature,AddOn,Admin}Seeder.php` | Seeder idempoten menghasilkan 6 plans, 21 features, 126 plan_features, 3 add_ons, dan 1 super admin. |

---

## 4. Prosedur Verifikasi & Pengujian

### 4.1 Pengujian di Workspace AI Studio (Node.js + ESLint/Stylelint/Prettier + Browser)
1. **Validasi Linter & Format:**
   ```bash
   npm run lint
   npm run build
   ```
   Harus lulus 0 error pada ESLint, Stylelint, Prettier, dan skrip vanilla build.
2. **Unit Test Kontrak, Guard & Skema (`node:test`):**
   ```bash
   npm test
   ```
   Memverifikasi 8 Contracts, 25 Adapters, `AppServiceProvider` bindings, 8 Guard Rules (gagal pada `local`/`staging`, lulus pada `production-safe`), 7 file migrasi (`utf8mb4_unicode_ci` + `down()`), 6×21 = 126 matriks seeder, serta sinkronisasi `MANIFEST_PERUBAHAN.json`.
3. **Verifikasi Portal & Guard Simulator (Port 3000 + Playwright):**
   - Akses `http://localhost:3000` → buka tab **Sub-Fase 0B: Contracts, Guard & Schema**.
   - Uji klik tombol simulator **Local Dev**, **Staging**, dan **Production Aman** → cek respons `/api/subfase-0b/guard-check` dan perubahan tabel 8 aturan.

### 4.2 Pengujian Backend & Database di Mesin Lokal Anda (PHP 8.4 + Docker MySQL 8.4)
Setelah menyalin/menggabungkan file ke project Laravel lokal Anda:
```bash
docker compose up -d --build
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan migrate:rollback --step=7
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan app:check-production --dry-run
docker compose exec app ./vendor/bin/pest tests/Unit/SubFase0B tests/Feature/SubFase0B
```
**Kriteria Keberhasilan:**
- Seluruh 7 tabel tercipta bersih dengan collation `utf8mb4_unicode_ci` dan dapat di-rollback tanpa error FK.
- Seed ulang `php artisan db:seed` tetap mempertahankan tepat 6 plans, 21 features, 126 plan_features, 3 add_ons, dan 1 super_admin.
- Pest suite `AdapterContractTest`, `GuardRulesTest`, dan `DatabaseCoreSeederTest` seluruhnya berstatus **PASS**.
