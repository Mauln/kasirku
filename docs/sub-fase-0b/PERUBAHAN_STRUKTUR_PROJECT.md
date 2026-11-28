# Dokumentasi Perubahan Struktur Project — Sub-Fase 0B

**Sub-Fase:** 0B (Kontrak Adapter, Guard Rules & Skema Database Core)  
**Tanggal:** 2026-11-28  
**Ringkasan:** Catatan lengkap seluruh file dan folder yang ditambahkan (*New*), diperbarui (*Modified*), dihapus (*Deleted*), dan dipindahkan (*Moved*) selama pengerjaan Sub-Fase 0B.

---

## 1. Ditambahkan (New — 71 File)

| Kelompok | Path | Fungsi | Alasan Penambahan | Status |
|---|---|---|---|---|
| Contracts | `app/Contracts/OtpSender.php` | Interface pengiriman OTP | Memenuhi Adapter Pattern Architecture §5.2 | Selesai |
| Contracts | `app/Contracts/EmailSender.php` | Interface pengiriman email transaksional | Memenuhi Adapter Pattern Architecture §5.2 | Selesai |
| Contracts | `app/Contracts/StorageDriver.php` | Interface penyimpanan & URL file | Memenuhi Adapter Pattern Architecture §5.2 | Selesai |
| Contracts | `app/Contracts/ErrorReporter.php` | Interface pelaporan exception | Memenuhi Adapter Pattern Architecture §5.2 | Selesai |
| Contracts | `app/Contracts/SupportLinkProvider.php` | Interface URL chat support | Memenuhi Adapter Pattern Architecture §5.2 | Selesai |
| Contracts | `app/Contracts/KnowledgeBaseLinkProvider.php` | Interface URL pusat panduan | Memenuhi Adapter Pattern Architecture §5.2 | Selesai |
| Contracts | `app/Contracts/StatusPageLinkProvider.php` | Interface URL status page | Memenuhi Adapter Pattern Architecture §5.2 | Selesai |
| Contracts | `app/Contracts/PrinterDriver.php` | Interface cetak struk & test print POS | Memenuhi Adapter Pattern Architecture §5.2 | Selesai |
| Adapters OTP | `app/Services/Adapters/Otp/{Log,Fake,Fonnte,Email,Composite}OtpSender.php` (5 file) | Implementasi OTP lokal, test, Fonnte, Email fallback, dan Composite failover | Memenuhi §5.2 & §5.5 (failover + audit `otp.fallback`) | Selesai |
| Adapters Email | `app/Services/Adapters/Email/{Log,Fake,Mailpit,Ses}EmailSender.php` (4 file) | Implementasi pengiriman email per environment | Memenuhi Adapter Matrix §5.6 | Selesai |
| Adapters Storage | `app/Services/Adapters/Storage/{Local,Fake,S3}StorageDriver.php` (3 file) | Storage lokal (anti path traversal), fake test, dan S3/R2/MinIO | Memenuhi Adapter Matrix §5.6 & keamanan file path | Selesai |
| Adapters Error | `app/Services/Adapters/Error/{Log,Fake,Sentry}ErrorReporter.php` (3 file) | Error tracking log, fake test, dan Sentry/GlitchTip | Memenuhi Adapter Matrix §5.6 | Selesai |
| Adapters Link | `app/Services/Adapters/Link/{Mailto,Chatwoot,StaticMarkdown,BookStack,Instatus,UptimeKuma}LinkProvider.php` (6 file) | Provider link dukungan, KB, dan status page | Memenuhi Adapter Matrix §5.6 | Selesai |
| Adapters Printer | `app/Services/Adapters/Printer/{Log,Fake,Bluetooth,CssPrint}Driver.php` (4 file) | Driver cetak log, fake test, Web Bluetooth, dan CSS `@media print` | Memenuhi Adapter Matrix §5.6 | Selesai |
| Guard & Provider | `app/Exceptions/ProductionGuardException.php` | Exception khusus kegagalan guard produksi | Membawa daftar rule gagal tanpa membocorkan secret | Selesai |
| Guard & Provider | `app/Services/Guard/ProductionGuardService.php` | Evaluator 8 Guard Rules produksi + audit failure | Memenuhi Architecture §8.1 | Selesai |
| Guard & Provider | `app/Console/Commands/CheckProduction.php` | Command artisan `app:check-production` (`--dry-run`) | Memenuhi CI langkah #10 & Architecture §8.3 | Selesai |
| Guard & Provider | `app/Providers/AppServiceProvider.php` | Binding 8 Contracts, boot guard check, Blade `$app` globals | Memenuhi Architecture §5.4, §8.2, & §9.4 | Selesai |
| Config | `config/services.php` | Driver & parameter adapter berbasis `env()` | Memenuhi Architecture §5.3 | Selesai |
| Config | `config/kasirku.php` | Konfigurasi guard, versi, POS, kasbon, dan voucher | Memenuhi Aturan Emas #9 (no hardcode) | Selesai |
| Views Error | `resources/views/errors/500-guard.blade.php`, `resources/views/errors/503-readonly.blade.php` (2 file) | Halaman error guard ops/dev dan fallback 503 merchant | Memenuhi Architecture §8.4 | Selesai |
| Migrations (#1–#7) | `database/migrations/2026_11_28_000001..000007_create_*_table.php` (7 file) | Skema `plans`, `features`, `plan_features`, `add_ons`, `admin_users`, `admin_sessions`, `partners` | Memenuhi Roadmap 0B & Schema Bagian 1–2 (`utf8mb4_unicode_ci`) | Selesai |
| Models | `app/Models/{Plan,Feature,PlanFeature,AddOn,AdminUser,AdminSession,Partner}.php` (7 file) | Model Eloquent domain Shared & Admin/Internal | Mendukung seeder idempoten & relasi | Selesai |
| Seeders | `database/seeders/{Plan,Feature,PlanFeature,AddOn,Admin,Database}Seeder.php` (6 file) | 5 Reference Seeders idempoten + orchestrator `DatabaseSeeder` | Memenuhi Schema §7.1 (6 plans, 21 features, 126 mappings, 3 add-ons, 1 admin) | Selesai |
| Tests & Validator | `tests/Pest.php`, `tests/Unit/SubFase0B/{AdapterContractTest,GuardRulesTest}.php`, `tests/Feature/SubFase0B/DatabaseCoreSeederTest.php` (4 file) | Konfigurasi Pest binding `Tests\TestCase` dan test backend contracts, guard, migrasi, serta seeder | Mengikat Laravel container/DB ke Pest + verifikasi Sub-Fase 0B | Selesai |
| Tests & Validator | `scripts/check-coverage.php`, `scripts/subfase-0b-validator.mjs`, `tests/Unit/SubFase0B/contractsAndSchema.test.mjs` (3 file) | Coverage gate Rule §10.2 (Clover XML), validator Node.js & unit test `node:test` untuk kontrak, 8 guard rules, 7 migrasi, dan 126 matriks | Penjaga threshold coverage CI + verifikasi otomatis | Selesai |
| Dokumentasi 0B | `docs/sub-fase-0b/{PANDUAN_SUB_FASE_0B.md,PERUBAHAN_STRUKTUR_PROJECT.md,MANIFEST_PERUBAHAN.json}` (3 file) | Panduan pembelajaran, audit perubahan struktur, dan manifest JSON 0B | Memenuhi instruksi dokumentasi perubahan per sub-fase | Selesai |

---

## 2. Diperbarui / Diubah (Modified — 7 File)

| Path | Fungsi | Alasan Perubahan | Status |
|---|---|---|---|
| `package.json` | Script pengujian npm | Memperluas `npm test` agar ikut menjalankan `tests/Unit/SubFase0B/*.test.mjs` | Selesai |
| `.env.example` | Template variabel environment | Memperbarui `APP_VERSION=2.0.0-0b` dan menambahkan placeholder kosong `FONNTE_TOKEN`, `FONNTE_URL`, `SENTRY_DSN`, `FORCE_HTTPS`, `SEED_ADMIN_EMAIL`, `SEED_ADMIN_PASSWORD` | Selesai |
| `scripts/dev-server.mjs` | Server pratinjau Node.js lokal port 3000 | Menambahkan endpoint `/api/subfase-0b/overview` dan `/api/subfase-0b/guard-check` serta memperbarui versi `/up` | Selesai |
| `index.html` | Portal verifikasi browser | Memperbarui header/hero ke Sub-Fase 0B dan menambahkan tab `Sub-Fase 0B: Contracts, Guard & Schema` | Selesai |
| `resources/css/subfase-0a.css` | Stylesheet CSS murni portal | Menambahkan kelas BEM `.stack-column`, `.preset-bar`, `.guard-banner--*`, `.matrix-mark--*` tanpa warna hex | Selesai |
| `resources/js/subfase-0a.js` | Modul ES Vanilla JS portal | Menambahkan renderer panel Sub-Fase 0B, simulator 8 Guard Rules interaktif, tabel matriks 21×6, dan gabungan riwayat perubahan 0A & 0B | Selesai |
| `.github/workflows/ci.yml` | Workflow CI 11 langkah | Memperbarui `aquasecurity/trivy-action` ke pinned commit SHA `v0.36.0` (karena tag `0.28.0` dihapus upstream), auto-generate `.env` CI, dan env DB pada step coverage | Selesai |

---

## 3. Dihapus (Deleted)
Tidak ada file atau folder yang dihapus pada Sub-Fase 0B.

---

## 4. Dipindahkan / Diubah Lokasinya (Moved)
Tidak ada file atau folder yang dipindahkan pada Sub-Fase 0B.
