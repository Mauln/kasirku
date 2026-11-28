Roadmap Pengembangan Kasirku v2.0
https://gemini.google.com/app/0b769518d73265b3

User prompt: buatkan roadmap pengerjaan dari panduan ini buatlah per fase dan jika panjang fase di pecah jadi sub fase
Response: Peta Jalan Pengembangan Kasirku v2.0
Pembangunan Kasirku menerapkan prinsip Dev-First, Setup-at-End, yakni seluruh fitur bisnis dan kualitas dibangun di lingkungan lokal terlebih dahulu (Fase 0–6), dilanjutkan penyiapan staging tanpa kartu kredit (Fase 7), pengujian QA/UAT hingga tonggak M10 sebagai batas akhir MVP (Fase 8), serta kesiapan produksi dan go-live di VPS Jakarta (Fase 9).

Fase	Fokus Utama	Lingkungan	Target Capaian
Fase 0	Fondasi Teknis, Kontrak Adapter & Design System	Lokal (Docker)	Repo, CI 11 langkah, 8 contracts, 87 token CSS & komponen dasar
Fase 1	Auth, Multi-Tenant Core, Onboarding & Setup Owner	Lokal (Docker)	Login dashboard, MFA admin, RBAC, tenant/outlet, onboarding & setup 10 langkah
Fase 2	Katalog Produk, Pajak, Pengaturan POS & Perangkat	Lokal (Docker)	Master produk/varian, tax rates, 7 tabel settings & aktivasi device POS
Fase 3	Kasir (POS) Inti & Fitur Operasional Kasir Lanjutan	Lokal (Docker)	Login PIN-only, transaksi, hold, kasbon, kas keluar/masuk, QRIS & offline cart
Fase 4	Ekosistem Bisnis: Inventori, Modifier, CRM, Promosi & Laporan	Lokal (Docker)	Stock ledger, PO, modifier, loyalty, voucher, diskon terjadwal & analitik
Fase 5	Monetisasi, Subscription, Feature Gating & Kuota	Lokal (Docker)	Trial plan, state machine, 21 feature gates, kuota & expired route exemption
Fase 6	Compliance (UU PDP), Support, Analytics & Backoffice	Lokal (Docker)	Cookie/export/delete, 12 retention jobs, 54 analytics events & panel admin
Fase 7	Setup Staging Infrastructure & Dev-First Safety	Staging (Cloud)	Stack gratis tanpa CC (Render, TiDB, Upstash, R2), env badge & 12 runbook
Fase 8	Staging QA, Security Audit & UAT (MVP Complete)	Staging (Cloud)	50 E2E Playwright, audit keamanan, performance & sign-off M10
Fase 9	Production Readiness, Cutover & Go-Live [PROD]	Prod-Test & VPS Jakarta	RG-1 s.d. RG-10 PASS, 6 self-hosted tools, Cutover #1–#10 & monitoring 24 jam
Fase 0: Fondasi Arsitektur, Infrastruktur Lokal & Design System
Sub-fase 0A: Inisialisasi Repository, Docker Lokal & Pipeline CI
●	Setup stack backend & container lokal: Konfigurasi Laravel 13, PHP 8.4, MySQL 8.4, Redis 7.4, dan Nginx 1.28 menggunakan docker-compose.yml (7 layanan: app, nginx, mysql, redis, queue, scheduler, phpmyadmin).
●	Konfigurasi strategi branch & Git hooks: Terapkan proteksi branch main, branch integrasi develop, konvensi Conventional Commits, serta pre-commit hook (Husky/CaptainHook) untuk formatting, gitleaks, ESLint, dan Stylelint.
●	Enforcement Vanilla Frontend di tooling: Konfigurasi ESLint 9+ flat config (eslint.config.js) dan Stylelint untuk memblokir React, Vue, Svelte, Angular, TypeScript, Tailwind, Inertia, Alpine, HTMX, Livewire, serta larangan innerHTML dinamis.
●	Setup CI 11 langkah (.github/workflows/ci.yml): Aktifkan pengecekan Pint, ESLint, Stylelint, Prettier, Pest, audit dependensi (composer audit + npm audit), gitleaks, Trivy, dry-run app:check-production, dan coverage check.
Sub-fase 0B: Kontrak Adapter, Guard Rules & Skema Database Core
●	Implementasi 8 Contracts & Fake Adapters: Buat interface di app/Contracts/ (OtpSender, EmailSender, StorageDriver, ErrorReporter, SupportLinkProvider, KnowledgeBaseLinkProvider, StatusPageLinkProvider, PrinterDriver) beserta binding di AppServiceProvider dan fake adapter untuk pengujian.
●	Implementasi 8 Guard Rules produksi: Bangun pengecekan otomatis di AppServiceProvider::boot() dan command app:check-production agar aplikasi gagal berjalan di produksi jika driver dev/log/sync masih aktif.
●	Migrasi awal & Reference Seeders: Bangun migrasi tabel domain Shared (#1–#4) dan Admin/Internal (#5–#7) dengan charset utf8mb4_unicode_ci, lalu jalankan reference seeders idempoten (PlanSeeder, FeatureSeeder 21 fitur, PlanFeatureSeeder 21×6, AddOnSeeder, AdminSeeder).
Sub-fase 0C: Vanilla Frontend Engine & Design System
●	Aset merek & 87 Design Tokens: Sediakan 5 file aset merek (logo-wordmark.svg, logo-icon.svg, logo-lockup.svg, favicon.ico, apple-touch-icon.png) dan definisikan 87 token CSS di resources/css/tokens.css (warna dasar, gradien, elevasi, glass, modul, chart, dan persiapan dark mode).
●	Core Vanilla JS & Render Engine: Bangun modul ES murni di resources/js/core/ mencakup state.js, render.js (render() debounced 80ms dan renderNow()), dom.js (el(), on(), qs(), qsa(), patch()), fetch.js (apiFetch() dengan header X-CSRF-TOKEN dan X-Partial), serta error tracking global (window.onerror + unhandledrejection).
●	20 Grup Komponen UI & Layout Dasar: Bangun komponen Blade dan CSS murni untuk layout (app, admin, pos, auth), tombol (10 varian), input/form, kartu (8 varian), modal/bottom sheet, toast, navigasi responsif, serta kerangka penanganan 24 state halaman.
Fase 1: Auth, Multi-Tenant Core, Onboarding & Setup Awal Owner
Sub-fase 1A: Autentikasi Dashboard, Session Redis, RBAC & MFA Admin
●	Autentikasi merchant berbasis sesi: Implementasikan registrasi, verifikasi OTP (dengan CompositeOtpSender), verifikasi email (email_verifications), login dashboard, serta reset password menggunakan session di Redis dan metadata di user_sessions.
●	Otorisasi RBAC & Middleware inti: Terapkan kolom role pada users (owner, admin, cashier) dan admin_users (super_admin, support, finance, ops, partnership) beserta urutan wajib middleware (tenant, role, audit).
●	Keamanan MFA Admin: Bangun alur aktivasi dan verifikasi TOTP 6 digit serta 10 recovery codes terenkripsi untuk seluruh staf backoffice dengan rate limit 5 percobaan per sesi.
Sub-fase 1B: Multi-Tenant Core, Outlet, Tim & Onboarding 4 Langkah
●	Skema & isolasi multi-tenant: Buat migrasi tabel #8–#17 (tenants dengan UUID CHAR(36), outlets, users, user_outlets, tenant_invitations, pos_devices), selesaikan circular dependency #1 (tenants.owner_user_id) via ALTER TABLE, dan terapkan global scope tenant_id di semua model domain.
●	Wizard Onboarding 4 Langkah: Bangun alur pengisian informasi usaha, lokasi (otomatis membuat outlet pertama), survei opsional, dan konfirmasi, dikawal middleware onboarded (onboarding_step = 4).
●	Manajemen outlet & tim: Sediakan fitur undangan staf via email, penetapan akses outlet (user_outlets), serta pengaturan PIN 6 digit (hash Argon2id) untuk karyawan kasir.
Sub-fase 1C: Setup Awal Owner (10 Langkah) & Pencarian Global
●	Checklist Setup Awal Owner: Implementasikan SetupChecklistService dan komponen <x-setup-checklist> yang melacak 10 langkah pasca-onboarding di tenants.setup_step tanpa memblokir akses langsung ke POS.
●	Pencarian Global (Ctrl+K): Bangun GlobalSearchService dan partial route /app/api/partials/search/results dengan debounce 300ms, maksimal 5 hasil per kategori (produk, customer, order, PO, supplier), serta pembatasan hasil berdasarkan role kasir vs owner/admin.
Fase 2: Katalog Produk, Pajak, Pengaturan POS & Perangkat
Sub-fase 2A: Kategori, Master Produk, Varian & Pajak
●	Struktur katalog produk: Buat migrasi tabel #23–#27 (categories, tax_rates, products, product_variants, product_taxes) lengkap dengan CHECK (stock_qty >= 0) dan jalankan TaxRateSeeder (PPN 11%, PB1 10%, Service Tax 5%).
●	CRUD Produk & Varian: Bangun antarmuka daftar produk (tampilan list card di mobile dan tabel/grid di desktop), formulir produk dengan harga grosir, pelacakan stok, serta pemetaan pajak per produk.
Sub-fase 2B: Pengaturan POS (7 Tabel) & Manajemen Perangkat POS
●	7 tabel pengaturan terpisah: Buat migrasi dan layanan dengan cache 5 menit untuk receipt_settings (#53), tax_settings (#54), service_charge_settings (#55), rounding_settings (#56), cashier_settings (#57), ewallet_settings (#58), dan notification_preferences (#59).
●	Aktivasi & Revoke Perangkat POS: Bangun pembuatan kode aktivasi perangkat (pos_devices) dengan masa berlaku token sesi perangkat 30 hari serta fitur pencabutan akses perangkat dari dashboard.
Fase 3: Kasir (POS) Inti & Fitur Operasional Kasir Lanjutan
Sub-fase 3A: Login POS PIN-Only, Switch User & Buka/Tutup Kasir
●	Login POS berbasis perangkat & PIN: Bangun alur pemilihan grid karyawan dan <x-pin-pad> 6 digit (tombol 64×64px) yang divalidasi dengan Argon2id, middleware device-active dan pin-lockout (kunci 15 menit setelah 5 kali gagal), tanpa menggunakan email/password untuk kasir.
●	Switch User & Sesi Kasir: Implementasikan perpindahan kasir tanpa menutup sesi perangkat maupun sesi kasir aktif (switch_user_pos), serta alur buka/tutup kasir (cashier_sessions) yang otomatis menghasilkan shift_reports immutable.
Sub-fase 3B: Transaksi POS Inti, Split Bill/Rata & Cetak Struk/Bluetooth
●	Antarmuka POS responsif & low-latency: Bangun tata letak POS 2 panel (30/70) untuk tablet dan single column + bottom sheet untuk HP menggunakan event delegation dan renderNow() agar respons interaksi di bawah 100ms.
●	Kalkulasi transaksi & Snapshot: Buat migrasi tabel #40–#49 (orders, order_items, order_splits, order_split_items, order_taxes, payments, refunds, refund_items, invoices, invoice_items) dengan penyimpanan snapshot harga, nama produk, SKU, dan modifier saat transaksi.
●	Split Bill, Split Rata & Cetak Struk: Implementasikan pembagian tagihan per item (SplitBillService), pembagian rata 2–20 orang dengan pembulatan sisa pada orang terakhir (SplitEqualService), cetak ulang dengan batas max_print_count, CSS @media print (58mm/80mm), serta integrasi Web Bluetooth API untuk printer thermal.
Sub-fase 3C: Hold Order, Kasbon, Kas Keluar/Masuk, QRIS Statis & Refund Parsial
●	Hold Order: Implementasikan penyimpanan sementara pesanan (HoldOrderService) dengan batas maksimal 10 per outlet (middleware hold-limit), tanpa mengurangi stok, dan pemblokiran tutup kasir jika masih ada hold aktif.
●	Kasbon / Piutang: Bangun CreditSaleService dengan validasi limit sisi server (credit_limit - credit_balance >= grand_total), pengurangan stok saat transaksi dibuat, pencatatan poin loyalitas hanya saat lunas, serta pengingat jatuh tempo (CheckCreditDueDate).
●	Kas Keluar/Masuk & QRIS Statis: Buat tabel immutable cash_movements (#50) dengan syarat alasan minimal 10 karakter (CashMovementService), serta alur tampilan QR statis dan konfirmasi pembayaran manual oleh kasir (QrisService).
●	Refund Parsial: Implementasikan pengembalian dana per item (RefundPartialService) khusus role owner/admin dengan validasi kuantitas sisa dan pencatatan mutasi stok masuk (return) otomatis.
Sub-fase 3D: Offline Cart (localStorage) & Sinkronisasi Otomatis
●	Ketahanan koneksi putus: Bangun resources/js/core/offline.js yang menyimpan keranjang ke localStorage (kasirku_cart_{tenant_id}) saat navigator.onLine === false, menampilkan banner status koneksi, dan melakukan sinkronisasi otomatis beserta dialog resolusi konflik saat koneksi kembali pulih.
Fase 4: Ekosistem Bisnis — Inventori, Modifier, CRM, Promosi & Laporan
Sub-fase 4A: Modifier Produk & Manajemen Meja F&B
●	Grup Modifier & Picker: Buat migrasi tabel #28–#30 (modifiers, modifier_options, product_modifiers) dan komponen POS Modifier Picker yang menegakkan aturan is_required, min_selection, dan max_selection.
●	Manajemen Meja F&B: Buat tabel tables (#35) dan table_sessions (#36) untuk mode denah meja F&B, status ketersediaan meja, dan penggabungan meja induk (parent_table_id).
Sub-fase 4B: Inventori Ledger Immutable, Supplier & Purchase Order
●	Buku besar stok immutable: Buat migrasi tabel #31–#34 (suppliers, purchase_orders, purchase_order_items, stock_movements) dan pastikan stock_movements hanya mendukung operasi INSERT untuk seluruh jenis mutasi stok.
●	Siklus hidup PO & Rekonsiliasi: Bangun PurchaseOrderService (draft → sent → partial → received / cancelled) di mana stok bertambah saat penerimaan barang, notifikasi low stock, serta job harian ReconcileStockJob.
Sub-fase 4C: CRM, Loyalty Point & Pengelolaan Kasbon Pelanggan
●	Profil pelanggan & buku besar poin: Buat tabel customers (#39) dan tabel immutable customer_loyalty_points (#51) dengan aturan perolehan poin (1 poin/Rp1.000) dan penukaran maksimal 50% dari grand_total (LoyaltyService).
●	Riwayat transaksi & pelunasan kasbon: Sediakan halaman detail pelanggan yang menampilkan riwayat kunjungan, saldo poin, riwayat cicilan/pelunasan kasbon, dan proteksi penghapusan pelanggan jika credit_balance > 0.
Sub-fase 4D: Promosi, Notifikasi Perubahan Data & Laporan Analitik
●	Voucher & Diskon Terjadwal: Buat migrasi tabel #60–#62 (vouchers, tabel immutable voucher_redemptions, scheduled_discounts), terapkan larangan penggabungan voucher dengan kasbon (VoucherService), dan aktifkan penerapan otomatis diskon terjadwal (ScheduledDiscountService & command ApplyScheduledDiscounts).
●	Notifikasi perubahan data: Bangun Observer (ChangeTracker) dan NotificationService untuk mengirim notifikasi in-app kepada owner/admin saat ada perubahan produk, karyawan, pajak, atau pengaturan, dengan menghormati preferensi per pengguna.
●	Visualisasi Canvas API & Laporan: Implementasikan mesin grafik Canvas API vanilla (drawLine, drawBar, drawDonut, drawSparkline, drawHeatmap, drawProgressRing) tanpa library pihak ketiga untuk dashboard 8 widget, laporan penjualan, laporan shift, dan laporan produk terlaris.
Fase 5: Monetisasi — Subscription, Trial, Feature Gating & Kuota
Sub-fase 5A: Trial Plan & Subscription State Machine
●	Skema langganan: Buat migrasi tabel #63–#65 (subscriptions dengan generated column current_flag untuk menjamin 1 langganan aktif per tenant, tenant_add_ons, plan_change_requests) dan selesaikan circular dependency #2 (billing_invoices.subscription_id) via ALTER TABLE.
●	Siklus hidup Trial & Grace Period: Implementasikan penetapan paket trial otomatis saat registrasi (is_trial_plan = TRUE, kuota 999, 21 fitur aktif), SubscriptionStateMachine, serta scheduled command ExpireTrials dan ExpireGracePeriods.
Sub-fase 5B: Middleware Feature Gating (21 Fitur), Kuota & Add-on
●	Penegakan 21 Feature Gates: Terapkan middleware feature:{code} pada seluruh rute fitur premium (prioritas: tenant_feature_overrides dengan cache 5 menit > plan_features), dilengkapi tampilan overlay gembok dan halaman 403 khusus beserta CTA upgrade.
●	Penegakan Kuota & Add-on: Terapkan middleware quota pada penambahan perangkat POS (max_pos), karyawan (max_employees), dan admin (max_admins), lengkap dengan komponen Quota Meter dan alur pembelian add-on.
Sub-fase 5C: Expired Route Exemption & Billing
●	Aturan pengecualian rute kedaluwarsa: Terapkan middleware subscription-active dan read-only yang membuka tepat 12 rute wajib (dashboard, langganan, profil, privasi/cookie/export/delete sesuai UU PDP, laporan read-only, bantuan) dan memblokir 12 rute tulis dengan respons 403 JSON SUBSCRIPTION_EXPIRED yang ditangkap apiFetch() untuk memunculkan modal blokir.
Fase 6: Compliance (UU PDP), Support, Analytics & Backoffice Internal
Sub-fase 6A: Compliance UU PDP & Retention Jobs
●	Skema & fitur kepatuhan: Buat migrasi tabel #66–#71 (audit_logs immutable, notifications, cookie_consents, export_jobs, account_deletions, retention_jobs) dan jalankan RetentionJobSeeder (12 job).
●	Cookie Consent, Export & Hapus Akun: Bangun banner persetujuan cookie kustom (tanpa pre-check, pencatatan versi insert-only), halaman legal publik (/privacy, /terms, /cookies), ekspor CSV asinkron (batas 1×/24 jam, hapus otomatis 24 jam), serta penghapusan akun dengan masa tenggang 30 hari dan retensi data finansial 5 tahun (PurgeDeletedTenants).
Sub-fase 6B: Sistem Support Dua Sisi & Tiket Bantuan
●	Tiket & saluran bantuan: Buat tabel support_tickets (#72) dan support_ticket_messages (#73), integrasikan adapter chat (mailto: di MVP), halaman status (Instatus.com), dan Knowledge Base Markdown.
●	Impersonasi Support Terbatas Waktu: Bangun fitur Support Access (support_access_sessions) maksimal 30 menit dengan persetujuan merchant, mode read-only bawaan (eskalasi tulis maksimal 15 menit), banner oranye di kedua sisi layar, dan pencatatan audit wajib.
Sub-fase 6C: Analytics Pipeline (54 Events) & Observability
●	Pipeline analitik bebas PII: Buat tabel analytics_events (#74) berpartisi bulanan, terapkan ConsentChecker, filter bot, hashing IP menggunakan HMAC-SHA256, dan pengiriman non-blocking via navigator.sendBeacon ke queue analytics untuk seluruh 54 events (29 MVP + 15 fitur baru + 10 produksi).
●	Health Check /up: Bangun endpoint /up yang memeriksa 4 komponen kesehatan sistem (Database, Redis, Queue, Storage).
Sub-fase 6D: Backoffice Internal & Demo Seeders Lengkap
●	Modul Backoffice (/admin/*): Bangun 17 menu backoffice untuk pengelolaan tenant, langganan, feature flag override, kuota, tiket support, monitoring ekspor/penghapusan akun, audit log immutable, partner, dan reset MFA.
●	Data Seeder & Uji Rollback Lokal: Lengkapi 16 Demo Seeders (DemoTenantSeeder hingga DemoCashMovementSeeder) dan verifikasi siklus migrasi penuh (migrate:fresh → migrate:rollback --step=80 → migrate).
Fase 7: Setup Staging Infrastructure & Dev-First Safety (Tanpa CC)
Sub-fase 7A: Provisioning Stack Staging Gratis (Tanpa Kartu Kredit)
●	Konfigurasi layanan cloud gratis: Siapkan Render.com (web service, queue worker, cron scheduler via render.yaml), TiDB Cloud Serverless (database kompatibel MySQL 8.4), Upstash Redis, Cloudflare R2 (10 GB object storage), Instatus.com (status page), dan Sentry free tier dengan total biaya Rp0.
●	Verifikasi koneksi & isolasi data: Pastikan penyimpanan ekspor otomatis beralih ke Cloudflare R2 di staging dan tidak ada data PII asli yang masuk ke lingkungan staging.
Sub-fase 7B: Environment Indicator, Demo Mode & Dokumentasi Runbook
●	Komponen keselamatan visual: Aktifkan variabel global Blade $app dan komponen <x-env-badge> (badge STAGING biru pada z-index 750) serta banner Demo Mode yang dapat ditutup selama 24 jam.
●	Penyusunan 12 Runbook Operasional: Lengkapi dokumen di docs/runbooks/ meliputi deploy.md, rollback.md, incident-response.md, restore-backup.md, rotate-secret.md, render-deploy.md, expired-flow.md, vanilla-asset-deploy.md, offline-cart-recovery.md, dan bluetooth-printer-troubleshoot.md.
Sub-fase 7C: Pipeline CI/CD Staging & First Staging Deploy
●	Otomasi deploy staging: Hubungkan branch staging dengan GitHub Actions agar melakukan deploy ke Render, menjalankan migrasi, cache konfigurasi/rute/view, serta menyalin aset vanilla ES modules (resources/js/* → public/js/*) dan minifikasi CSS dengan cache busting ?v={version}.
●	Verifikasi DoD Fase 7 (9 item): Pastikan staging aktif, endpoint /up mengembalikan status 200, Sentry menerima laporan error backend/frontend, dan smoke test awal staging lulus.
Fase 8: Staging QA, Security Audit & UAT (Milestone M10 — MVP Complete)
Sub-fase 8A: Otomasi 50 E2E Playwright & Performance Testing
●	Eksekusi 50 alur E2E wajib: Jalankan pengujian Playwright lintas browser (Chromium, WebKit, Firefox) untuk 29 alur dasar MVP dan 21 alur fitur baru (login PIN, lockout 5×, switch user, setup 10 langkah, hold order, kasbon, kas keluar/masuk, QRIS, split rata, refund parsial, print ulang, offline cart, Bluetooth printer, voucher, diskon terjadwal, Ctrl+K, notifikasi, laporan produk terlaris).
●	Verifikasi target Coverage & Performa: Pastikan coverage backend mencapai 100% pada seluruh Controller (Merchant, Admin, Auth), 100% Middleware, 100% routes/partials.php, minimal 70% Services, serta audit performa dengan Lighthouse dan k6 (P95 POS < 3 detik).
Sub-fase 8B: Security Audit, Manual QA Matrix & Sign-Off M10
●	Audit keamanan & isolasi tenant: Verifikasi kebersihan hasil gitleaks, Trivy, OWASP checklist, 15 rate limit, 10 tabel immutable, dan pengujian isolasi multi-tenant.
●	Manual QA & Pencapaian M10: Lakukan pengujian manual lintas perangkat (mobile, tablet, desktop) untuk 104 wireframe dan 24 state, selesaikan sign-off kepatuhan & support, dan nyatakan Milestone M10 (Staging UAT Complete) sebagai penutup fase MVP.
Fase 9: Production Readiness, Cutover & Go-Live [PROD]
Sub-fase 9A: Production-Test & Evaluasi Release Gate (RG-1 s.d. RG-10)
●	Subsistem Production Readiness: Bangun 6 tabel produksi (#75–#80: releases, release_gates, deployments, cutover_runs, cutover_checklist_items, rollback_events), jalankan 3 production seeders, serta bangun halaman manajemen rilis di backoffice dengan perlindungan middleware ReAuth (password + TOTP).
●	Uji coba di production-test & Gerbang Rilis: Deploy ke lingkungan production-test dengan adapter asli (Fonnte OTP, SES Email, SPF/DKIM/DMARC), uji restore backup, dan evaluasi seluruh Release Gate RG-1 hingga RG-10 hingga berstatus PASS.
Sub-fase 9B: Provisioning VPS Jakarta, Self-Hosted Tools & Cutover (#1–#10)
●	Infrastruktur Produksi Jakarta: Siapkan VPS 8 GB di Jakarta (IDCloudHost / Biznet Gio) untuk kepatuhan residensi data UU PDP, dan jalankan docker-compose.prod.yml yang memuat 13 layanan termasuk 6 self-hosted tools (MinIO, Chatwoot, BookStack, Uptime Kuma, GoAlert, GlitchTip).
●	Eksekusi Berurutan Cutover Checklist (#1–#10): Aktifkan cutover lock di Redis (TTL 1 jam), lakukan backup pra-cutover, deploy rilis v1.0.0, jalankan migrasi 80 tabel dan reference seeders, verifikasi /up 200, luluskan smoke test 20/20, arahkan DNS/SSL Cloudflare, dan aktifkan jadwal on-call.
Sub-fase 9C: Post-Cutover Monitoring 24 Jam & Stabilisasi
●	Pengawasan pasca-rilis & kriteria sukses: Pantau metrik produksi selama 24 jam pertama (uptime ≥ 99.5%, error rate < 1%, 0 insiden P1 tidak terselesaikan, biaya operasional ≤ Rp800.000/bulan, 100% staf aktif MFA), serta pastikan prosedur rollback instan (konfirmasi ketik ROLLBACK + re-auth) siap digunakan sewaktu-waktu hingga seluruh 14 kriteria sukses produksi terpenuhi.
________________________________________
