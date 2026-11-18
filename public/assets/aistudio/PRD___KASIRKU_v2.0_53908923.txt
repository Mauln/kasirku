# PRD — KASIRKU v2.0

**Versi:** 2.0 | **Status:** Final | **Tanggal:** 2026-11-16

---

## Daftar Isi

```
§1   Ringkasan Produk
§2   Masalah & Solusi
§3   Target User & Persona
§4   Scope
§5   User Journey & Fitur (19 Journey)
§6   User Flow (F1–F60)
§7   Business Rules (§7.1–§7.35)
§8   Non-Functional Requirements
§9   Pricing & Monetization
§10  Timeline & Milestone
§11  Risks & Assumptions
§12  Analytics (54 Events)
§13  Compliance & Legal
§14  Support Model & SLA
§15  Acceptance Criteria
§16  Success Metrics
§17  Product Decisions Log (118 PD)
§18  Sign-off
```

---

## §1. Ringkasan Produk

### 1.1 Apa Itu Kasirku

Kasirku adalah **SaaS POS (Point of Sale) online untuk UMKM Indonesia**.

- **Tagline:** *"Kasir online UMKM Indonesia — cepat, murah, tanpa ribet."*
- **Bentuk:** Web responsif (desktop, tablet, HP). Tidak ada aplikasi native/APK.
- **Pengguna:** Merchant (owner, admin, kasir), end customer (penerima struk/invoice), dan operator platform internal (5 role).
- **Kompetitor:** Majoo, Moka POS, Pawoon, Olsera.
- **Celah pasar:** Harga entry agresif + onboarding kilat + fitur inventori/CRM/modifier/kasbon/QRIS/promosi yang biasanya hanya di paket mahal kompetitor.

### 1.2 Deskripsi Lengkap

Kasirku menyediakan dua sisi:

**Merchant** mendapat: landing page, registrasi & login, **Setup Awal Owner terpadu (10 langkah)**, dashboard, pengaturan POS lengkap (pajak 3 metode, service charge, pembulatan, struk, metode pembayaran kustom, invoice, **e-wallet/QRIS statis**, **pengaturan kasir**), manajemen produk/kategori/varian/modifier, manajemen inventori (supplier, purchase order, stock movement, transfer antar outlet), manajemen tim & akses, manajemen outlet, device POS, CRM & loyalty point, shift report, transaksi POS (split bill per produk, partial payment, void/refund, cashier session, **hold order, kasbon, kas keluar/masuk, QRIS statis, split rata, refund parsial, print ulang struk, koneksi putus handling**), **promosi (voucher, diskon terjadwal)**, **notifikasi perubahan data**, **pencarian global**, **laporan produk terlaris**, **Bluetooth thermal printer**, dan halaman langganan.

**Operator internal** mendapat: backoffice untuk tenant management, langganan, feature gating, support access, perangkat POS, kuota tim, partner, audit, dan monitoring.

Semua diakses via browser responsif. MVP dibangun di lokal dan divalidasi di staging, lalu diluncurkan ke production.

### 1.3 Status Rilis

| Fase | Deliverable |
|---|---|
| MVP (Fase 0–8) | Fitur bisnis + compliance + support + staging |
| Production (Fase 9) | Go-live + stabilisasi |
| Post-MVP (Fase 10+) | PWA, Web Push, payment gateway (QRIS dinamis), offline POS penuh, KDS, delivery management, membership tier, custom role, deposit/uang muka, exchange/tukar barang, bundle promo, happy hour, partner login dashboard |

### 1.4 Biaya & Infrastructure

| Environment | Provider | Biaya |
|---|---|---|
| Local (Fase 0–6) | Docker Compose laptop | Rp0 |
| Staging (Fase 7–8) | Render.com + TiDB Cloud + Upstash + Cloudflare R2 + Instatus | Rp0 |
| Production (Fase 9) | VPS 8 GB Jakarta | ~Rp400–500rb/bln |
| **Total MVP (Fase 0–8)** | | **Rp0** |
| **Total Fase 9** | | **~Rp500–800rb/bln** |

### 1.5 Staging Stack Detail

| Komponen | Provider | Free Tier | Login |
|---|---|---|---|
| App Laravel + Queue + Cron | Render.com | 512 MB RAM | GitHub |
| Database MySQL | TiDB Cloud Serverless | 5 GB storage | GitHub/Google |
| Redis (Session + Queue) | Upstash | 10.000 cmd/hari, 256 MB | GitHub |
| Storage (Export CSV) | Cloudflare R2 | 10 GB | Cloudflare |
| Status Page | Instatus.com | Free tier | Email |
| Error Tracking | Sentry free | 5K events/bulan | GitHub |

---

## §2. Masalah & Solusi

### 2.1 Masalah

| # | Untuk | Masalah | Solusi Kasirku |
|---|---|---|---|
| 1 | Merchant | Kasir lambat, daftar berbelit, butuh pelatihan | Registrasi 3 menit + onboarding 4 langkah + POS 1 layar |
| 2 | Merchant | Bingung paket, takut kaget saat trial habis | Trial 14 hari semua fitur + transisi eksplisit |
| 3 | Merchant | Tools terpisah untuk inventori & CRM | Inventori, modifier, CRM & loyalty terintegrasi |
| 4 | Internal | Belum ada backoffice terpusat | Backoffice lengkap |
| 5 | Operasional | VPS mahal untuk MVP | Free-first: lokal + staging gratis tanpa CC |
| 6 | Kompetitif | Majoo/Moka/Pawoon dominan | Entry price agresif + fitur kaya di Starter+ |
| 7 | Merchant | Kasbon/piutang tidak terkelola | Fitur kasbon terintegrasi CRM |
| 8 | Merchant | Kas tidak akurat saat shift | Kas keluar/masuk tercatat real-time |
| 9 | Merchant | Customer belum siap bayar, order hilang | Hold order + resume |
| 10 | Merchant | Tidak bisa terima pembayaran non-tunai | QRIS statis tanpa payment gateway |
| 11 | Kasir | Login POS lambat (email+password) | Login PIN-only berbasis device (≤5 detik) |
| 12 | Merchant | Tidak ada printer thermal | Dukungan Bluetooth thermal printer |
| 13 | Merchant | Tidak ada setup awal terpadu | Setup Awal Owner 10 langkah |
| 14 | Merchant | Tidak ada promosi/voucher | Voucher + diskon terjadwal |
| 15 | Merchant | Tidak ada notifikasi perubahan data | Notifikasi + audit trail perubahan |
| 16 | Merchant | Tidak ada pengaturan e-wallet | Setup QRIS + e-wallet custom |

### 2.2 Goals MVP

| # | Goal | Target |
|---|---|---|
| G1 | Landing → register conversion | ≥ 8% |
| G2 | Registration completion | ≥ 70% |
| G3 | Time-to-first-transaction | ≤ 15 menit |
| G4 | Setup POS lengkap dalam 3 hari | ≥ 90% merchant |
| G5 | Trial → paid conversion (14 hari) | ≥ 25% |
| G6 | Feature gating accuracy | 100% |
| G7 | Kuota enforcement | 100% |
| G8 | Tenant isolation | 100% |
| G9 | POS transaction success | ≥ 99% |
| G10 | Landing LCP | < 2.5 detik |
| G11 | POS transaksi P95 | < 1.5 detik |
| G12 | Audit log coverage | 100% aksi tulis |
| G13 | Staging UAT Complete | ✅ |
| G14 | Biaya MVP | Rp0 |
| G15 | POS login time (PIN-only) | ≤ 5 detik |
| G16 | Hold order resume success | ≥ 99% |
| G17 | Kasbon repayment rate | ≥ 80% dalam 30 hari |
| G18 | Setup Awal Owner completion | ≥ 85% dalam 3 hari |
| G19 | QRIS usage rate | ≥ 30% merchant aktif |

### 2.3 Goals Production

| # | Goal | Target |
|---|---|---|
| P1 | Production readiness gate lulus | 10/10 |
| P2 | Cutover tanpa downtime > 5 menit | ✅ |
| P3 | Smoke test production lulus | 20/20 |
| P4 | Uptime 24 jam pertama | ≥ 99.5% |
| P5 | Error rate 24 jam pertama | < 1% |
| P6 | Biaya production | ≤ Rp800rb/bulan |

### 2.4 Non-Goals

❌ APK/Capacitor/TWA/PWA · ❌ Akuntansi penuh · ❌ Payroll · ❌ BI/Analytics internal · ❌ Payment gateway (QRIS dinamis) · ❌ iOS · ❌ Multi-bahasa · ❌ Multi-region production · ❌ Blue-green deployment · ❌ Canary release · ❌ Kitchen Display System (KDS) · ❌ Delivery management · ❌ Membership tier (silver/gold/platinum) · ❌ Custom role/permission · ❌ Deposit/uang muka · ❌ Exchange/tukar barang

---

## §3. Target User & Persona

### 3.1 External (Merchant) — 7 Persona

| # | Persona | Kebutuhan Utama | Device |
|---|---|---|---|
| 1 | **Visitor** | Paham value prop, coba gratis, daftar cepat | HP & desktop |
| 2 | **Owner** | Dashboard, setup POS, pilih paket, kontrol outlet, karyawan, inventori, CRM, kasbon, promosi, e-wallet | HP, tablet, desktop |
| 3 | **Admin** | Kelola produk, outlet, inventori, pengaturan POS, laporan, kelola kasir, kas keluar/masuk | Tablet, desktop |
| 4 | **Kasir** | Login cepat (PIN-only ≤5 detik), transaksi cepat, buka/tutup kasir, cetak bill, hold order, kas keluar/masuk, QRIS | Tablet & HP |
| 5 | **End Customer** | Menerima struk/invoice, lihat nomor antrian, scan QR loyalty | HP |
| 6 | **Akuntan/Pembukuan** | Akses laporan keuangan, export transaksi, rekonsiliasi kas, laporan kasbon outstanding | Desktop |
| 7 | **Partner/Reseller** | Dashboard referral, tracking komisi, onboarding tenant | Desktop |

### 3.2 Persona Detail

**End Customer:**
- Siapa: Pelanggan akhir merchant (yang membeli produk)
- Login: **Tidak login** — bukan user sistem
- Touchpoints: Struk cetak (58mm/80mm), link invoice digital, QR code di struk
- Kebutuhan: Transaksi cepat, struk jelas, nomor antrian terlihat, loyalty point tercatat
- Data yang dikumpulkan: Nama, telepon (jika diberikan), riwayat transaksi
- Hak UU PDP: Hak akses, koreksi, hapus data melalui merchant

**Akuntan/Pembukuan:**
- Siapa: Orang yang mengelola pembukuan merchant (bisa owner sendiri atau karyawan khusus)
- Login: Login sebagai role `admin` merchant
- Kebutuhan: Laporan penjualan per periode, laporan per metode pembayaran, export transaksi, shift report agregat, laporan kasbon outstanding

**Partner/Reseller:**
- Siapa: Pihak yang mereferensikan merchant ke Kasirku
- Login MVP: Dikelola internal oleh Partnership (F26)
- Login Post-MVP: Dashboard sendiri, tracking komisi

### 3.3 Internal (Operator) — 5 Persona

| Persona | Tanggung Jawab | Akses |
|---|---|---|
| Super Admin | Tenant, staff, role, config global, feature flag, audit | MFA wajib |
| Support/CS | Reset password, resend OTP, support access, panduan | MFA + RBAC terbatas |
| Finance | Trial, langganan, add-on, perpanjangan, override paket | MFA wajib |
| Ops | Perangkat POS, kuota, kesehatan platform, monitoring | MFA wajib + on-call |
| Partnership | Partner/reseller, tenant referral, onboarding | MFA wajib |

### 3.4 Non-User Persona

Calon merchant (target landing), trial tidak convert (target notifikasi H-7/H-3/H-1), merchant upgrade (target ekspansi), partner (channel acquisition).

---

## §4. Scope

### 4.1 Yang Dikerjakan di MVP

| Kategori | Fitur |
|---|---|
| Merchant — Onboarding | Landing, legal, cookie consent, registrasi (email + OTP WA), verifikasi email, login dashboard, onboarding 4 langkah |
| Merchant — Setup Awal Owner | 10 langkah setup terpadu (outlet, produk, karyawan, pajak/struk, kasir, device, e-wallet, pelanggan, promosi, notifikasi) |
| Merchant — Login POS | Login POS PIN-only (device-based), Switch User, session tied to shift |
| Merchant — Dashboard | Status trial, outlet, ringkasan penjualan, profil bisnis, data & privacy, langganan, help |
| Merchant — Produk | CRUD kategori, produk, varian, modifier, pajak per produk, import CSV (Should), nonaktifkan produk |
| Merchant — Inventori | CRUD supplier, purchase order, penerimaan barang, stock movement immutable, stock adjustment, transfer antar outlet (gated), low stock alert, laporan stok |
| Merchant — Tim & Akses | Undang user, role owner/admin/cashier, PIN kasir, nonaktifkan user, user-outlet mapping, kuota, reset PIN |
| Merchant — Outlet | CRUD outlet, switch outlet, laporan per outlet, multi-outlet (gated Prime+) |
| Merchant — Device POS | Aktivasi device, revoke, kuota device, lihat device aktif |
| Merchant — Pengaturan POS | Pajak 3 metode, tax rate CRUD, service charge, pembulatan, struk, custom payment, invoice |
| Merchant — Pengaturan Kasir | Kas awal default, batas diskon kasir, izin kasir (void/refund/diskon), auto-close shift |
| Merchant — Pengaturan E-Wallet | QRIS statis (upload QR code), e-wallet custom (GoPay, OVO, Dana, dll) |
| Merchant — POS Core | Transaksi, split bill per produk, partial payment, void/refund, buka/tutup kasir, multi-pembayaran, laporan, manajemen meja (F&B) |
| Merchant — POS Core Tambahan | Hold order, kasbon/piutang, kas keluar/masuk, QRIS statis, split rata, refund parsial, print ulang struk, koneksi putus handling |
| Merchant — Bluetooth Printer | Dukungan thermal printer 58mm/80mm via Web Bluetooth |
| Merchant — CRM & Loyalty | CRUD customer, auto-create saat transaksi, earn/redeem poin, riwayat transaksi, manual adjust poin |
| Merchant — Shift Report | Report otomatis per cashier session, immutable, print/export |
| Merchant — Promosi | Voucher/kupon, diskon terjadwal |
| Merchant — Notifikasi Perubahan Data | Notifikasi saat produk/karyawan/settings diubah, preferensi notifikasi |
| Merchant — Pencarian Global | Ctrl+K search lintas entitas (produk, customer, order, PO) |
| Merchant — Laporan Produk Terlaris | Top N produk per periode, filter outlet/kategori |
| Merchant — Langganan | Trial 14 hari, 6 paket, 21 feature gating, kuota enforcement, expired behavior, state machine |
| Merchant — Compliance | Export CSV, hapus akun (soft delete 30 hari), cookie withdrawal |
| Merchant — Support | Help menu, in-app chat, status page, KB |
| Internal — Backoffice | Auth MFA, RBAC 5 role, tenant management, subscription & billing, feature flag, kuota, support access, device & session, audit log |
| Dev & Staging | Local Docker, staging Render+TiDB+Upstash+R2, environment indicator, demo mode, CI/CD staging |

### 4.2 Yang Dikerjakan di Production

| Sub-Fase | Deliverable |
|---|---|
| Fase 9A — Production-test | Verifikasi production readiness + smoke test |
| Fase 9B — Production Live | Go-live + cutover + DNS switch |
| Fase 9C — Stabilization | Monitoring 24 jam + post-mortem + review |

### 4.3 Post-MVP

PWA, Web Push, payment gateway (QRIS dinamis), offline POS penuh, multi-device sync, laporan lanjutan, invoice penuh, multi-outlet kompleks, automated dunning, API publik, resep/bahan baku, akuntansi, iOS, KDS, delivery management, membership tier, custom role, deposit/uang muka, exchange/tukar barang, bundle promo, happy hour, partner login dashboard.

---

## §5. User Journey & Fitur (19 Journey)

### 5.1 Journey 1 — Onboarding

| Fitur | Prioritas |
|---|---|
| Landing page (hero, modul, harga, CTA, footer legal) | Must |
| Legal publik: /privacy, /terms, /cookies | Must |
| Cookie consent banner + settings modal + withdrawal | Must |
| Register: email + WA + consent → OTP WA | Must |
| OTP 6 digit, expiry 5 menit, maks 5 percobaan | Must |
| Password 8–20, ≥1 huruf/angka/simbol | Must |
| Verifikasi email (link, expiry 24 jam, resend maks 3×/24 jam) | Must |
| Onboarding 4 langkah: usaha → lokasi → survei → konfirmasi | Must |
| Outlet pertama dibuat otomatis saat onboarding Step 2 | Must |
| Dashboard trial aktif | Must |

**Flow:** Landing → Cookie Consent → Register → OTP → Password → Verifikasi Email → Onboarding (4 langkah, outlet otomatis dibuat) → Dashboard Trial Aktif → Setup Awal Owner Checklist

### 5.2 Journey 2 — Setup Awal Owner

Setelah onboarding 4 langkah selesai, owner masuk ke dashboard dan melihat **Setup Checklist**:

| # | Langkah | Konten | Wajib? | CTA |
|---|---|---|---|---|
| 1 | Outlet | Outlet pertama sudah dibuat otomatis saat onboarding. Owner bisa edit nama, alamat, timezone | ✅ Otomatis | "Edit Outlet" |
| 2 | Produk | Tambah minimal 1 produk (nama + harga). Quick add tanpa kategori/SKU | ✅ | "Tambah Produk" |
| 3 | Informasi Karyawan | Undang kasir pertama: nama + email + role cashier + PIN 6 digit | ⚠️ Bisa skip | "Tambah Kasir" |
| 4 | Pengaturan Struk, Biaya, dan Pajak | Pilih metode pajak (ON/OFF), header struk, logo, pembulatan | ⚠️ Bisa skip | "Atur Pajak & Struk" |
| 5 | Pengaturan Kasir | Kas awal default, batas diskon kasir, izin kasir (void/refund/diskon) | ⚠️ Bisa skip | "Atur Kasir" |
| 6 | Pengaturan Perangkat | Aktivasi device POS pertama via kode aktivasi | ⚠️ Bisa skip | "Aktivasi Device" |
| 7 | Pengaturan E-Wallet | Upload QR code QRIS statis, tambah metode e-wallet custom | ⚠️ Bisa skip | "Atur E-Wallet" |
| 8 | Data Pelanggan | Aktifkan CRM & loyalty (toggle ON/OFF, config earn/redeem) | ⚠️ Bisa skip | "Atur CRM" |
| 9 | Promosi | Buat voucher pertama atau diskon terjadwal pertama | ⚠️ Bisa skip | "Buat Promosi" |
| 10 | Notifikasi Perubahan Data | Pilih notifikasi mana yang ingin diterima (default: semua ON) | ⚠️ Bisa skip | "Atur Notifikasi" |

**Aturan Setup Checklist:**
- Tampil di dashboard sampai semua langkah selesai atau di-dismiss
- Progress bar: X/10 selesai
- Setiap langkah punya CTA langsung ke halaman setup
- Tombol "Lewati, saya tahu caranya" untuk dismiss seluruh checklist
- Checklist tidak blocking — owner bisa langsung pakai POS tanpa menyelesaikan semua
- Analytics: setup_checklist_step_completed, setup_checklist_dismissed

### 5.3 Journey 3 — Login

| Fitur | Prioritas |
|---|---|
| Login dashboard: email + password | Must |
| Login POS: pilih karyawan → PIN 6 digit (device-based) | Must |
| Login admin: email + password + MFA TOTP | Must |
| Forgot/reset password (email, 30 menit, single-use) | Must |
| Session: HttpOnly + CSRF; 8 jam admin, 30 hari remember me | Must |
| Rate limit: 5/menit/IP (dashboard), 10/menit/IP (POS), 5/menit/IP (admin) | Must |
| Lockout setelah 5 percobaan gagal (admin) | Must |
| Switch User POS (tanpa logout penuh) | Must |
| Session kasir tied to shift | Must |
| Concurrent session rules | Must |

**Login POS (PIN-only):**

```
Device sudah aktif?
├── YA → Grid karyawan (nama + foto/avatar) → Pilih → PIN 6 digit → Masuk POS (≤5 detik)
└── TIDAK → Masukkan Activation Code → Device terdaftar
            → Grid karyawan → Pilih → PIN → Masuk POS
```

**Referensi:** Majoo, Moka, Square, Toast — semua menggunakan PIN-only untuk kasir.

**Switch User:**
```
Kasir A aktif → klik "Switch User" (ikon user di header POS)
→ Konfirmasi: "Ganti kasir? Order yang belum disimpan akan hilang."
→ Grid karyawan → Kasir B pilih nama → PIN 6 digit → masuk
→ Session user berubah (A→B), device session tetap
→ Cashier session A tetap terbuka (belum ditutup)
```

**Concurrent Session Rules:**

| Rule | Detail |
|---|---|
| Kasir | 1 active POS session per device. Login di device lain → session lama revoked + audit |
| Owner/Admin | Boleh multiple sessions (dashboard + POS + mobile) |
| Backoffice admin | 1 active session per admin. Login baru → session lama revoked |

**Logout Behavior:**

| Aksi | Yang Terjadi |
|---|---|
| Logout POS | Revoke user POS session; cashier session tetap terbuka; device session tetap |
| Tutup Kasir | Close cashier_session; generate shift report; user session tetap (bisa switch user) |
| Logout Dashboard | Revoke semua session user tersebut (POS + dashboard) |
| Logout Admin | Revoke admin session; MFA reset required untuk login berikutnya |

### 5.4 Journey 4 — Pengaturan POS

| Fitur | Prioritas |
|---|---|
| Pajak: toggle ON/OFF, 3 metode (sebelum diskon, sesudah diskon, termasuk pajak) | Must |
| Tax Rate Management: CRUD tax rate per tenant; default PPN 11% | Must |
| Service Charge: toggle, %, dikenai pajak | Must |
| Pembulatan: tunai Rp100/Rp500 (ke atas), non-tunai Rp1 | Must |
| Kustomisasi Struk: header/footer/catatan, tampil % pajak, batas cetak, logo, preview | Must |
| Metode Pembayaran Kustom | Must |
| Invoice & Cetak Bill | Must |
| Pengaturan E-Wallet: QRIS statis (upload QR), e-wallet custom | Must |
| Pengaturan Kasir: kas awal default, batas diskon, izin kasir, auto-close | Must |

**Pengaturan Kasir:**

| Setting | Detail | Default |
|---|---|---|
| Kas awal default | Nominal kas awal yang disarankan saat buka shift | Rp0 |
| Batas diskon kasir | Maks % diskon yang bisa diberikan kasir | Tidak terbatas |
| Izin void | Kasir boleh void order? | TRUE |
| Izin refund | Kasir boleh refund? | FALSE (hanya owner/admin) |
| Izin diskon | Kasir boleh beri diskon? | TRUE |
| Auto-close shift | Tutup shift otomatis setelah X jam | OFF |
| Kas keluar/masuk | Aktifkan fitur kas keluar/masuk | TRUE |

**Pengaturan E-Wallet:**

| Setting | Detail |
|---|---|
| QRIS statis | Owner upload QR code QRIS (PNG/JPG, maks 2MB). Tampil di POS saat kasir pilih "QRIS" |
| E-wallet custom | Tambah metode pembayaran: GoPay, OVO, Dana, ShopeePay, dll. Type: 'ewallet' |
| Settlement account | Catat nomor rekening settlement per e-wallet (opsional) |
| Konfirmasi | Manual oleh kasir ("Sudah Diterima") — tidak ada verifikasi otomatis di MVP |

### 5.5 Journey 5 — POS Operations

| Fitur | Prioritas |
|---|---|
| Transaksi: pilih produk, qty, modifier, diskon, proses bayar | Must |
| Multi pembayaran | Must |
| Split bill per produk | Must |
| Partial payment | Must |
| Void & refund | Must |
| Buka/tutup kasir | Must |
| Manajemen meja (F&B) | Should (gated) |
| Struk: cetak kasir/dapur/label | Must |
| Modifier picker: required enforced, max_selection enforced | Must |
| Auto-create customer (opsional) | Must |
| Loyalty point redeem di checkout | Must |
| Shift report preview sebelum close | Must |
| Hold/Suspend Order | Must |
| Kasbon/Piutang Customer | Must (gated) |
| Kas Keluar/Masuk (Petty Cash) | Must |
| QRIS Statis | Must |
| Split Rata (Equal Split) | Should |
| Refund Parsial per Item | Should |
| Print Ulang Struk | Must |
| Koneksi Putus Saat Transaksi | Must |
| Bluetooth Thermal Printer | Should |

### 5.6 Journey 6 — Langganan (Trial → Paid)

| Fitur | Prioritas |
|---|---|
| Trial: 14 hari semua fitur, kuota unlimited | Must |
| Notifikasi trial: banner sisa hari, in-app H-7/H-3/H-1 | Must |
| Paket: Starter Basic / Starter / Advance / Prime / Prime+ | Must |
| Halaman Langganan: paket aktif, sisa trial, perbandingan, quota meter | Must |
| Upgrade: aktif segera; prorated | Must |
| Downgrade: periode berikutnya; grace 7 hari | Must |
| Trial habis / expired behavior | Must |
| State machine langganan | Must |
| Add-on purchase | Must |

### 5.7 Journey 7 — Compliance & Privacy (UU PDP)

| Fitur | Prioritas |
|---|---|
| Data & Privacy page | Must |
| Export data: CSV, async job, rate limit 1×/24 jam, file expire 24 jam | Must |
| Hapus akun: soft delete 30 hari, konfirmasi ketik nama tenant | Must |
| Cookie settings | Must |
| Consent withdrawal | Must |
| Data retention info | Must |

### 5.8 Journey 8 — Support

| Fitur | MVP | Production |
|---|---|---|
| In-app chat | mailto:support@kasirku.id | Chatwoot self-hosted |
| Email | ✅ | ✅ |
| WhatsApp | ✅ | ✅ |
| KB | Static Markdown /help | BookStack self-hosted |
| Status page | Instatus.com free | Uptime Kuma self-hosted |
| Support access | ≤30 menit, read-only default, audit | Sama |
| Support write escalation | Merchant approve, maks 15 menit | Sama |

### 5.9 Journey 9 — Backoffice (Internal)

| Fitur | Prioritas |
|---|---|
| Auth admin: email + password + MFA TOTP, 8 jam, lockout 5× | Must |
| MFA lifecycle: enrollment, recovery codes, reset oleh Super Admin | Must |
| RBAC: 5 role | Must |
| Tenant management: list, filter, detail, suspend, extend trial, ubah plan | Must |
| Subscription & billing | Must |
| Kuota & add-on management | Must |
| Support access: grant, ≤30 menit, banner dua sisi, read-only default | Must |
| Device & session management | Must |
| Audit log: immutable, filter, export CSV | Must |

### 5.10 Journey 10 — Manajemen Produk & Kategori

| Fitur | Prioritas |
|---|---|
| CRUD kategori | Must |
| CRUD produk (nama, SKU, barcode, harga, harga grosir, status, kategori, pajak per produk) | Must |
| CRUD varian produk | Must |
| Pajak per produk (pilih tax rate; default PPN 11%) | Must |
| Import produk CSV | Should |
| Nonaktifkan produk | Must |
| Low stock alert configuration | Must |
| Barcode scan | Should |

### 5.11 Journey 11 — Tim, Karyawan, Role & Akses

| Fitur | Prioritas |
|---|---|
| Undang user (email + role; invitation expiry 7 hari; kuota dicek) | Must |
| Role merchant: owner, admin, cashier | Must |
| PIN kasir (6 digit, hash, reset oleh owner/admin) | Must |
| Nonaktifkan user | Must |
| User-outlet mapping | Must |
| Kuota karyawan/admin | Must |
| Reset PIN | Must |

### 5.12 Journey 12 — Outlet & Multi-Outlet

| Fitur | Prioritas |
|---|---|
| CRUD outlet tunggal (nama, alamat, timezone, status) | Must |
| Switch outlet di dashboard/POS | Must |
| Multi-outlet (gated Prime+) | Gated |
| Laporan per outlet | Must |
| Nonaktifkan outlet | Must |
| Timezone per outlet | Must |

### 5.13 Journey 13 — Device POS & Session

| Fitur | Prioritas |
|---|---|
| Aktivasi device POS (kode aktivasi; expiry 15 menit) | Must |
| Revoke device | Must |
| Kuota device | Must |
| Lihat device aktif | Must |

### 5.14 Journey 14 — Manajemen Inventori

| Fitur | Prioritas |
|---|---|
| CRUD supplier | Must |
| Purchase Order: buat, edit draft, kirim, terima, batal | Must |
| Penerimaan barang (partial/full) | Must |
| Stock movement otomatis (immutable) | Must |
| Stock adjustment manual (alasan wajib) | Must |
| Transfer stok antar outlet (gated) | Should |
| Low stock alert | Should |
| Laporan stok & movement | Must |
| Export stock movement CSV | Must |

### 5.15 Journey 15 — Modifier Produk

| Fitur | Prioritas |
|---|---|
| CRUD modifier group (nama, is_required, max_selection, min_selection) | Must |
| CRUD modifier option (nama, price_adjustment, sort) | Must |
| Attach modifier ke produk | Must |
| Pilih modifier di POS (required enforced, max_selection enforced) | Must |
| Snapshot modifier di order | Must |

### 5.16 Journey 16 — CRM & Loyalty

| Fitur | Prioritas |
|---|---|
| CRUD customer | Must |
| Auto-create customer saat transaksi (opsional) | Must |
| Loyalty point earn (1 poin per Rp1.000, config) | Must |
| Loyalty point redeem (1 poin = Rp1, config; maks 50% grand_total) | Must |
| Riwayat transaksi customer | Must |
| Loyalty point adjustment manual (alasan wajib, audit) | Must |

### 5.17 Journey 17 — Shift Report

| Fitur | Prioritas |
|---|---|
| Report otomatis per shift (dibuat saat cashier close) | Must |
| Ringkasan: total transaksi, sales, cash, non-cash, refunds, diskon, tax, service | Must |
| Lihat/print shift report | Must |
| Export CSV shift report | Must |
| Cash reconciliation | Must |
| Total kas keluar/masuk per shift | Must |

### 5.18 Journey 18 — Promosi

| Fitur | Prioritas | Gated? |
|---|---|---|
| Voucher/Kupon: buat kode, diskon %/nominal, min purchase, expiry, max penggunaan | Should | ✅ `promo.voucher` |
| Diskon Terjadwal: diskon otomatis di periode tertentu, scope per kategori/produk | Should | ✅ `promo.scheduled` |
| Daftar promosi aktif | Must | ✅ |
| Riwayat penggunaan voucher | Must | ✅ |
| Nonaktifkan promosi | Must | ✅ |

### 5.19 Journey 19 — Notifikasi Perubahan Data

| Fitur | Prioritas |
|---|---|
| Notifikasi saat produk ditambah/diubah/dihapus | Should |
| Notifikasi saat karyawan ditambah/dihapus/diubah role | Should |
| Notifikasi saat settings POS diubah | Should |
| Notifikasi saat langganan berubah | Should |
| Preferensi notifikasi (toggle per kategori) | Should |
| Audit trail perubahan (siapa mengubah apa, kapan, nilai lama → baru) | Must |

---

## §6. User Flow (F1–F60)

### 6.1 External (Merchant) — F1–F19

| # | Flow | Ringkasan |
|---|---|---|
| F1 | Landing → Register → Onboarding → Dashboard | Landing → cookie → register → OTP → password → verif email → onboarding (outlet otomatis) → dashboard trial → Setup Checklist |
| F2 | Login dashboard | /login → email/password → dashboard |
| F3 | Login POS | /app/pos → device aktif? → pilih karyawan → PIN → kasir (≤5 detik) |
| F4 | Profil bisnis | Dashboard → Pengaturan → Akun → simpan |
| F5 | Pengaturan POS | Dashboard/POS → Pengaturan → Pajak/Biaya/Struk/Pembayaran/Kasir/E-Wallet → simpan |
| F6 | Transaksi POS | Produk → modifier → split/diskon → bayar → struk → tutup kasir |
| F7 | Bill/Invoice | Bill manual; invoice digital dari Dashboard → Penjualan |
| F8 | Pilih paket & upgrade | Dashboard → Langganan → bandingkan → pilih → bayar → aktif |
| F9 | Trial habis | Read-only → banner → pilih paket → aktif |
| F10 | Export data | Data & Privacy → Export → job → CSV |
| F11 | Hapus akun | Konfirmasi ketik tenant → soft delete 30 hari |
| F12 | Support | Help → chat/KB/status; support access bila perlu |
| F13 | Kelola produk | Dashboard → Produk → CRUD kategori/produk/varian/modifier |
| F14 | Kelola tim | Dashboard → Tim → undang user → role → PIN kasir → kuota |
| F15 | Kelola outlet | Dashboard → Outlet → CRUD → switch outlet → laporan |
| F16 | Kelola device POS | Dashboard → Device → aktivasi → revoke → kuota |
| F17 | Kelola inventori | Dashboard → Inventori → supplier → PO → terima → stock movement |
| F18 | Kelola CRM | Dashboard → Customer → CRUD → riwayat → adjust poin |
| F19 | Shift report | POS → tutup kasir → shift report → print/export |

### 6.2 Internal — F20–F28

| # | Flow | Ringkasan |
|---|---|---|
| F20 | Admin login | /admin/login → MFA → dashboard |
| F21 | Kelola tenant | Cari → detail → aksi + alasan → audit |
| F22 | Feature flag | Tenant → tab Features → aktifkan + alasan → audit |
| F23 | Support access | Merchant grant → mode support → read-only/write → selesai → audit |
| F24 | Device & kuota | Devices → filter → revoke → cek kuota → add-on |
| F25 | Subscription & add-on | Subscriptions → cari → aksi → audit |
| F26 | Partner onboarding | Partners → daftar → referral → aktivasi → audit |
| F27 | Export & deletion monitoring | Status, file size, expires_at |
| F28 | MFA reset | Super Admin → re-auth → reset MFA → audit |

### 6.3 Dev & Staging — F29–F30

| # | Flow | Ringkasan |
|---|---|---|
| F29 | Deploy staging | Push → CI/CD → Render deploy → migrate + seed → health check → smoke test |
| F30 | Staging QA | QA matrix → E2E → security → performance → compliance → UAT |

### 6.4 Production — F31–F37

| # | Flow | Ringkasan |
|---|---|---|
| F31 | Production Readiness Evaluation | Evaluasi gate → semua PASS → status READY |
| F32 | Production Cutover | Start → checklist → complete |
| F33 | Rollback | Deploy history → konfirmasi → rollback |
| F34 | 24 Jam Monitoring | On-call → monitoring → incident response → post-mortem |
| F35 | Go-Live | Go-live checklist → final approval → production live |
| F36 | Post-Live Verification | 24 jam pertama → target check → Production Stable |
| F37 | Maintenance | Jadwal → komunikasi → maintenance → smoke test |

### 6.5 Flow Baru — F38–F60

| # | Flow | Kategori | Ringkasan |
|---|---|---|---|
| F38 | Setup Awal Owner | Onboarding | Dashboard → Setup Checklist → 10 langkah → selesai/dismiss |
| F39 | Hold Order | POS | Kasir klik "Hold" → order tersimpan → cart kosong → badge "Hold: N" |
| F40 | Resume Hold Order | POS | Kasir klik badge "Hold: N" → pilih order → resume → cart terisi |
| F41 | Kasbon Checkout | POS + CRM | Kasir pilih customer → checkout → "Kasbon" → validasi limit → order credit |
| F42 | Pembayaran Kasbon | POS + CRM | Customer datang → kasir cari customer → lihat kasbon → terima pembayaran |
| F43 | Kas Keluar | POS | Kasir klik "Kas Keluar" → nominal + alasan wajib → tercatat di cash_movements |
| F44 | Kas Masuk | POS | Kasir klik "Kas Masuk" → nominal + alasan wajib → tercatat di cash_movements |
| F45 | QRIS Statis Payment | POS | Kasir pilih QRIS → tampilkan QR → customer scan → kasir konfirmasi → paid |
| F46 | Switch User POS | Auth | Kasir A klik "Switch User" → Kasir B pilih nama + PIN → session berubah |
| F47 | Split Rata | POS | Kasir pilih "Split Rata" → input jumlah orang → sistem hitung per orang |
| F48 | Refund Parsial | POS | Owner/admin pilih item spesifik → qty → alasan → refund per item |
| F49 | Print Ulang Struk | POS | Kasir/owner cari order → "Cetak Ulang" → cek max_print_count → print |
| F50 | Koneksi Putus Saat Transaksi | POS | Koneksi putus → cart di localStorage → koneksi pulih → sync otomatis |
| F51 | Onboarding Kasir Baru | Auth | Owner invite kasir → kasir terima email → set password → set PIN → pertama kali login POS |
| F52 | Bluetooth Printer Setup | Settings | Owner → Pengaturan → Printer → scan Bluetooth → pair → test print |
| F53 | Laporan Produk Terlaris | Laporan | Dashboard → Laporan → Produk Terlaris → filter periode → top N produk |
| F54 | Pencarian Global | UX | Ctrl+K / tap search → cari produk, customer, order, PO → hasil terkelompok |
| F55 | Buat Voucher | Promosi | Dashboard → Promosi → Buat Voucher → kode, tipe, min purchase, expiry → aktif |
| F56 | Redeem Voucher di POS | POS + Promosi | Kasir input kode voucher di checkout → validasi → diskon diterapkan |
| F57 | Buat Diskon Terjadwal | Promosi | Dashboard → Promosi → Diskon Terjadwal → periode, scope, tipe → aktif |
| F58 | Pengaturan Kasir | Settings | Dashboard → Pengaturan → Kasir → kas awal, batas diskon, izin → simpan |
| F59 | Pengaturan E-Wallet | Settings | Dashboard → Pengaturan → E-Wallet → upload QR QRIS / tambah e-wallet → simpan |
| F60 | Notifikasi Perubahan Data | Settings | Dashboard → Pengaturan → Notifikasi → toggle per kategori → simpan |

### 6.6 Detail Flow Kunci

**F39 — Hold Order:**
```
Kasir sedang proses order (ada item di cart)
↓
Klik "Hold" (ikon pause di header POS)
↓
Konfirmasi: "Simpan order sementara?"
↓
Order disimpan dengan status 'hold'
↓
Cart dikosongkan, siap untuk order berikutnya
↓
Badge "Hold: N" di header POS (N = jumlah hold aktif)
↓
Limit: maks 10 hold aktif per outlet
↓
Stock TIDAK dikurangi saat hold
```

**F41 — Kasbon Checkout:**
```
Kasir pilih customer (wajib untuk kasbon)
↓
Checkout → pilih metode pembayaran "Kasbon"
↓
Validasi: customer.limit_credit - customer.credit_balance >= grand_total
├── YA → order status 'credit' → selesai
└── TIDAK → error "Limit kasbon tidak cukup"
↓
Kasbon tercatat di customer.credit_balance
↓
Notifikasi in-app ke owner: "Kasbon baru: [customer] Rp [amount]"
↓
Stock dikurangi saat order dibuat
```

**F45 — QRIS Statis:**
```
Kasir pilih metode pembayaran "QRIS"
↓
Modal QRIS:
┌─────────────────────────────────┐
│  QRIS — Toko Budi              │
│  ┌─────────────────────────┐   │
│  │    [QR CODE STATIS]     │   │
│  └─────────────────────────┘   │
│  Total: Rp 61.050              │
│  [ Sudah Diterima ]             │
│  [ Belum / Batal ]              │
└─────────────────────────────────┘
↓
Customer scan QR dari HP mereka → bayar via e-wallet/bank
↓
Kasir klik "Sudah Diterima"
↓
Order paid, payment method = 'qris_static'
↓
Audit: qris_payment_confirmed
```

**F50 — Koneksi Putus Saat Transaksi:**
```
navigator.onLine = false saat checkout
↓
Cart tersimpan di localStorage (key: kasirku_cart_{tenant_id})
↓
Banner offline: "⚠ Koneksi terputus. Cart tersimpan."
↓
Saat koneksi pulih (navigator.onLine = true):
↓
Auto-sync: kirim cart ke server
↓
Toast: "Transaksi tersinkron"
↓
Jika sync gagal 3×: toast error + tombol "Coba Lagi"
```

---

## §7. Business Rules

### 7.1 Trial

| Aspek | Aturan |
|---|---|
| Durasi | 14 hari |
| Fitur | Semua 21 fitur enabled |
| Kuota | Unlimited (999) |
| Notifikasi | Banner sisa hari; in-app H-7/H-3/H-1 |
| Plan | Trial = plan terpisah (is_trial_plan = TRUE) |
| Transisi | Trial habis → pending_payment/expired → read-only; pilih paket → aktif |
| Trial extension | Hanya via Finance (internal), alasan wajib, audit |

### 7.2 Trial Habis / Expired Behavior

| Kondisi | Perilaku |
|---|---|
| Trial habis, belum pilih paket | Read-only; banner merah; data tetap terlihat |
| Trial habis, sudah pilih paket | Paket otomatis aktif |
| Trial habis, pilih paket, belum bayar | Read-only sampai Finance konfirmasi; grace 7 hari |
| Langganan expired | Read-only + banner; data tidak dihapus min 30 hari |
| Grace expired (7 hari) | Read-only tetap; setelah 30 hari → arsip |
| Downgrade berlaku | Fitur di luar paket terkunci; kuota disesuaikan |
| Akun suspended | Login ditolak; banner di landing page |

**Route yang tetap dibuka saat expired (UU PDP):** /app/subscription, /app/settings/profile, /app/settings/data-privacy, /app/settings/cookie, /app/help, /help, /app/dashboard, /app/reports/*, /logout

**Route yang diblokir saat expired:** /app/pos/*, /app/products/*, /app/categories/*, /app/modifiers/*, /app/inventory/*, /app/customers/*, /app/tables/*, /app/settings/* (selain profil & privacy), /app/employees/*, /app/invoices/*, /app/outlets/*, /app/devices/*

### 7.3 Feature Matrix — 21 Fitur Gated

| Fitur | Trial | Basic | Starter | Advance | Prime | Prime+ |
|---|---|---|---|---|---|---|
| POS transaksi | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Split bill | ✅ | ❌ | ✅ | ✅ | ✅ | ✅ |
| Void/refund | ✅ | ❌ | ✅ | ✅ | ✅ | ✅ |
| Manajemen meja | ✅ | ❌ | ❌ | ✅ | ✅ | ✅ |
| Invoice digital | ✅ | ❌ | ❌ | ✅ | ✅ | ✅ |
| Laporan dasar | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Laporan lanjutan | ✅ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Multi-outlet | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Export CSV | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Custom payment method | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Pengaturan pajak 3 metode | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Service charge & pembulatan | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Kustomisasi struk | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Inventory tracking | ✅ | ❌ | ✅ | ✅ | ✅ | ✅ |
| Product modifiers | ✅ | ❌ | ✅ | ✅ | ✅ | ✅ |
| CRM & Loyalty | ✅ | ❌ | ❌ | ✅ | ✅ | ✅ |
| Shift report | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Kasbon/Piutang | ✅ | ❌ | ✅ | ✅ | ✅ | ✅ |
| Promosi Voucher | ✅ | ❌ | ❌ | ✅ | ✅ | ✅ |
| Promosi Diskon Terjadwal | ✅ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Pengaturan Kasir Lanjutan | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |

> **Catatan:** Hold Order, Kas Keluar/Masuk, QRIS Statis, Split Rata, Print Ulang, Pencarian Global, Laporan Produk Terlaris, Notifikasi Perubahan Data tersedia di **semua plan** (bukan gated).

**Kuota:**

| Kuota | Trial | Basic | Starter | Advance | Prime | Prime+ |
|---|---|---|---|---|---|---|
| POS (total per tenant) | ∞ | 1 | 2 | 3 | 5 | 5 |
| Karyawan | ∞ | 3 | 5 | 10 | 20 | 25 |
| Hak akses admin | ∞ | 1 | 1 | 2 | 5 | 5 |

### 7.4 RBAC Internal (5 role × 24 modul)

| Modul | Super Admin | Support | Finance | Ops | Partnership |
|---|---|---|---|---|---|
| Dashboard | RW | R | R | R | R |
| Tenant list/detail | RW | R | R | R | R |
| Tenant suspend/reactivate | RW | ❌ | ❌ | RW | ❌ |
| Tenant extend trial | RW | ❌ | RW | ❌ | ❌ |
| Tenant ubah plan | RW | ❌ | RW | ❌ | ❌ |
| Feature flag per tenant | RW | ❌ | ❌ | R | ❌ |
| Subscription & billing | RW | ❌ | RW | R | ❌ |
| Add-on management | RW | ❌ | RW | RW | ❌ |
| Support access | RW | RW (≤30 mnt) | ❌ | R | ❌ |
| Device & session | RW | R | ❌ | RW | ❌ |
| Kuota management | RW | R | RW | RW | ❌ |
| Support tools | RW | RW | ❌ | ❌ | ❌ |
| Support ticket inbox | RW | RW | R | R | R |
| Partner management | RW | ❌ | ❌ | ❌ | RW |
| Audit log | R | R | R | R | ❌ |
| Export job monitoring | RW | R | R | RW | ❌ |
| Deletion request | RW | RW | ❌ | RW | ❌ |
| Staff & role | RW | ❌ | ❌ | ❌ | ❌ |
| MFA reset | RW | ❌ | ❌ | ❌ | ❌ |
| Release Management | RW | ❌ | ❌ | RW | ❌ |
| Cutover Execution | RW | ❌ | ❌ | RW | ❌ |
| Rollback | RW | ❌ | ❌ | RW | ❌ |
| Monitoring Dashboard | RW | R | ❌ | RW | ❌ |
| Tenant overview | RW | R | R | R | R |

> Legend: RW = read+write; R = read-only; ❌ = tidak ada akses.


### 7.5 RBAC Merchant

| Role | Hak |
|---|---|
| owner | Semua, termasuk billing, hapus tenant, kelola admin, kelola inventori, CRM, shift report, outlet, device, kasbon, promosi |
| admin | Produk, outlet, inventori, modifier, pengaturan POS, laporan, kelola cashier, CRM, kas keluar/masuk; tidak bisa billing/hapus tenant/kelola admin |
| cashier | POS, buka/tutup kasir, cetak struk, shift report lihat, hold order, kas keluar/masuk, QRIS; tidak bisa settings/team/reports penuh/inventori/CRM |

### 7.6 Enforcement

- Feature gating: middleware → 403 jika tidak punya akses
- Kuota: middleware → 403 + prompt add-on
- Read-only: blokir aksi tulis saat trial habis/expired → 403
- RBAC: middleware role di route backoffice dan merchant
- Audit: otomatis untuk semua aksi tulis
- Tenant isolation: semua query domain wajib tenant_id

### 7.7 State Machine Langganan

| Status | Arti | Transisi |
|---|---|---|
| trial | Trial 14 hari, semua fitur, kuota unlimited | → active jika pilih paket; → pending_payment jika trial habis belum bayar |
| pending_payment | Sudah pilih paket, belum dikonfirmasi Finance | → active setelah Finance tandai lunas |
| active | Langganan aktif | → grace saat subscription_end lewat |
| grace | 7 hari read-only setelah expired | → expired setelah 7 hari |
| expired | Read-only, data tetap terlihat | → archived setelah 30 hari |
| archived | Data diarsipkan | Final |

### 7.8 Aturan Transaksi POS

| Aturan | Keputusan |
|---|---|
| Diskon item | Persen atau nominal; tidak boleh negatif melebihi harga item |
| Diskon order | Dialokasikan proporsional ke item setelah diskon item |
| Service charge | Dihitung dari subtotal setelah diskon; jika taxable masuk basis pajak |
| Pajak sebelum diskon | Pajak dihitung dari gross + service charge taxable |
| Pajak sesudah diskon | Pajak dihitung dari net setelah diskon + service charge taxable |
| Pajak termasuk | Harga sudah termasuk pajak; ekstraksi tax = net × rate/(100+rate) |
| Pembulatan tunai | Ke atas ke kelipatan Rp100/Rp500; rounding_adjustment dicatat |
| Pembulatan non-tunai | Tidak dibulatkan; pecahan terkecil Rp1 |
| Split bill per produk | Mencatat produk/qty/amount per split |
| Partial payment | Status partially_paid; boleh lanjut sampai lunas |
| Void | Hanya order belum lunas; tidak mengubah kas |
| Refund | Full refund order lunas; cash refund mengurangi expected cash |
| Refund parsial | Per item; hanya owner/admin; stock movement return per item |
| Buka/tutup kasir | Expected cash = opening + cash sales − cash refunds + kas masuk − kas keluar |
| Tutup kasir | Wajib tidak ada order pending/split terbuka; semua hold harus di-resume/void |
| Nomor order | Auto-generate per tenant; format {outlet_code}-{yyyymmdd}-{seq} |
| Nomor struk | Auto-generate per kasir session; sequential |
| Multi-currency | ❌ Post-MVP; semua IDR |
| Timezone | Mengikuti outlet; default Asia/Jakarta |

### 7.9 Tax Rate Management

- Seeder menyediakan PPN 11% default
- Owner/admin dapat CRUD tax rate: nama, code, rate, default, aktif
- Produk memilih tax rate
- Tax rate nonaktif tidak muncul untuk produk baru, tetap valid di histori
- Satu tenant dapat punya banyak tax rate (PPN 11%, PB1 10%, Service Tax 5%)
- Produk dapat punya >1 tax rate (multi-tax)

### 7.10 MFA Admin Lifecycle

| Tahap | Aturan |
|---|---|
| Enrollment | Wajib saat login pertama admin; scan TOTP QR + verifikasi kode |
| Recovery codes | 10 kode single-use; tersimpan terenkripsi |
| Reset MFA | Hanya Super Admin; wajib re-auth + alasan + audit |
| Lockout | 5 percobaan gagal per sesi |
| Session | 8 jam; MFA wajib untuk semua role internal |

### 7.11 Support Access Escalation

- Default: read-only, maksimal 30 menit
- Write escalation: merchant harus approve in-app; maksimal 15 menit; alasan wajib; audit wajib
- Support tidak boleh ubah billing, hapus tenant, ubah role, atau hapus data
- Banner dua sisi wajib tampil (merchant & admin)
- Support access otomatis expired setelah durasi habis
- Setiap aksi support dicatat di audit log

### 7.12 Email Verification & Password Reset

| Fitur | Aturan |
|---|---|
| Verifikasi email | Link expiry 24 jam; resend maks 3×/24 jam |
| Forgot password | Link expiry 30 menit; single-use; rate limit 3/jam/email |
| Reset password | Session lama dicabut; audit |
| Password policy | 8–20 karakter; ≥1 huruf, ≥1 angka, ≥1 simbol |

### 7.13 Manajemen Meja F&B

- Merge/split/move meja hanya jika belum ada order lunas
- Meja gabungan menyimpan parent_table_id
- Tutup meja wajib semua order lunas/void
- Fitur gated `pos.table_management`
- QR code meja untuk self-order: Post-MVP
- Kapasitas meja untuk display, bukan enforcement

### 7.14 Export, Delete & Retention

- Export CSV: async job, rate limit 1×/24 jam, file expire 24 jam
- Export types: produk, transaksi, customer, stock movement, shift report
- Hapus akun: soft delete 30 hari; konfirmasi ketik nama tenant; cancel restore tersedia

**10 Retention Job:**

| # | Job | Retensi |
|---|---|---|
| 1 | Analytics events | 90 hari |
| 2 | Audit logs | 90 hari |
| 3 | User sessions (revoked) | 30 hari |
| 4 | Admin sessions (revoked) | 30 hari |
| 5 | OTP verifications (expired) | 24 jam |
| 6 | Email verifications | 7 hari |
| 7 | Export jobs (file) | 24 jam |
| 8 | Cookie consents | 2 tahun |
| 9 | Expired tenants | 30 hari |
| 10 | Support tickets (closed) | 2 tahun |

### 7.15 Inventori

| Aspek | Aturan |
|---|---|
| Stock tracking | Hanya untuk produk dengan track_stock = TRUE |
| Stock movement | Immutable — hanya INSERT |
| Sales auto-deduct | Saat order lunas, stock berkurang |
| Refund auto-restore | Saat refund, stock bertambah |
| PO received | Stock bertambah per item |
| Stock adjustment | Manual via owner/admin; alasan wajib |
| Transfer antar outlet | Dua row: keluar + masuk; gated outlet.multi |
| Low stock | Notifikasi in-app saat stock ≤ threshold |
| PO status | draft → sent → partial → received; atau cancelled |

### 7.16 Modifier

| Aspek | Aturan |
|---|---|
| Required modifier | Jika is_required = TRUE, POS wajib pilih minimal 1 option |
| Max selection | Jika max_selection di-set, POS batasi pilihan |
| Price adjustment | Dijumlahkan ke unit_price |
| Snapshot | Disimpan di order_items saat transaksi |
| Hapus modifier | Nonaktif saja; histori tetap |
| Modifier vs Variant | Variant = SKU berbeda; modifier = opsi tambahan |
| Multi-group | 1 produk bisa punya banyak modifier group |

### 7.17 CRM & Loyalty

| Aspek | Aturan |
|---|---|
| Earn | 1 poin per Rp1.000 (config per tenant); dihitung dari grand_total |
| Redeem | 1 poin = Rp1 (config); maksimal 50% dari grand_total |
| Loyalty points | Cached — di-recalculate dari ledger |
| Manual adjust | Owner/admin; alasan wajib; audit |
| Duplicate phone | Unik per tenant |
| Poin expired | Tidak expired di MVP |
| Auto-create | Opsional; merchant bisa masukkan nama + telepon di POS |

### 7.18 Shift Report

| Aspek | Aturan |
|---|---|
| Generate | Otomatis saat cashier session ditutup |
| Snapshot | Semua angka disimpan; immutable |
| Edit | Tidak boleh; koreksi via adjustment manual + audit |
| Print | Format struk 58mm/80mm |
| Unique | 1 cashier session = 1 shift report |


### 7.18 Shift Report

| Aspek | Aturan |
|---|---|
| Generate | Otomatis saat cashier session ditutup |
| Snapshot | Semua angka disimpan; immutable |
| Edit | Tidak boleh; koreksi via adjustment manual + audit |
| Print | Format struk 58mm/80mm |
| Unique | 1 cashier session = 1 shift report |
| Kas keluar/masuk | Total kas keluar dan kas masuk tercatat di shift report |

### 7.19 Split Bill per Produk

| Aspek | Aturan |
|---|---|
| Sumber data | Mencatat produk/qty/amount per split |
| Alokasi | Jumlah quantity per split ≤ quantity di order |
| Total | Sum semua split = grand total order |
| Status | unpaid → paid saat paid_amount ≥ total |
| Payment | Per split |
| Refund per split | Post-MVP; MVP full order refund |


### 7.20 Login POS PIN-Only

| Aspek | Aturan |
|---|---|
| Metode login POS | Pilih karyawan + PIN 6 digit (tanpa email/password) |
| Device activation | Device harus sudah diaktifkan via activation code sebelum kasir bisa login |
| Device session | Persistent 30 hari atau sampai revoked |
| PIN salah | Maks 5 percobaan → lockout 15 menit → audit |
| Switch User | Bisa ganti kasir tanpa logout penuh; hanya perlu PIN kasir baru |
| Session kasir | Tied to cashier_session (shift); tutup shift = kembali ke "Pilih Kasir" |
| Concurrent session | 1 kasir = 1 active POS session; login di device lain → session lama revoked |
| Logout POS ≠ Tutup Kasir | Logout revoke user session; tutup kasir close cashier_session |

### 7.21 Hold Order

| Aspek | Aturan |
|---|---|
| Siapa bisa hold | Kasir yang sedang aktif di POS |
| Siapa bisa resume | Kasir sama ATAU kasir lain di outlet yang sama |
| Limit hold | Maks 10 hold aktif per outlet |
| Hold expiry | Hold tidak expired (tetap sampai di-resume atau di-void) |
| Hold saat tutup kasir | Wajib: semua hold harus di-resume atau di-void sebelum tutup kasir |
| Hold dan stock | Stock TIDAK dikurangi saat hold (hanya saat order paid) |
| Hold dan shift report | Hold order tidak masuk shift report sampai paid |
| Audit | hold_order_created, hold_order_resumed, hold_order_voided |

### 7.22 Kasbon/Piutang

| Aspek | Aturan |
|---|---|
| Siapa bisa kasbon | Hanya customer terdaftar (punya record di `customers`) |
| Limit kasbon | Config per tenant (default Rp500.000); bisa per customer override |
| Validasi | customer.limit_credit - customer.credit_balance >= grand_total |
| Status order | `credit` (bukan `paid`) |
| Pembayaran | Bisa cicilan atau lunas; setiap pembayaran tercatat di `payments` |
| Jatuh tempo | Config per tenant (default 30 hari); notifikasi H-3 |
| Kasbon dan stock | Stock dikurangi saat order dibuat (bukan saat dibayar) |
| Kasbon dan shift report | Kasbon TIDAK masuk cash sales (masuk "non-cash credit") |
| Kasbon dan loyalty | Poin earned saat kasbon dibayar lunas (bukan saat order dibuat) |
| Hapus customer dengan kasbon | Ditolak jika credit_balance > 0 |
| Fitur gated | `pos.credit_sale` — Basic ❌, Starter+ ✅ |
| Audit | credit_sale_created, credit_payment_received |

### 7.23 Kas Keluar/Masuk

| Aspek | Aturan |
|---|---|
| Siapa bisa | Kasir, admin, owner |
| Kapan | Saat cashier_session aktif |
| Alasan | Wajib, minimal 10 karakter |
| Nominal | Positif (tidak boleh negatif atau nol) |
| Kas keluar | Mengurangi expected_cash di shift report |
| Kas masuk | Menambah expected_cash di shift report |
| Immutable | cash_movements INSERT only (seperti stock_movements) |
| Shift report | Total kas keluar dan kas masuk tercatat di shift report |
| Limit | Tidak ada limit nominal (tapi audit wajib) |
| Audit | cash_movement_created |

### 7.24 QRIS Statis

| Aspek | Aturan |
|---|---|
| Prasyarat | Merchant harus sudah punya QR code QRIS dari bank/e-wallet |
| Upload QR | Owner upload QR code di Settings → Pembayaran → QRIS |
| Format | PNG/JPG, maks 2MB |
| Konfirmasi | Manual oleh kasir ("Sudah Diterima") |
| Verifikasi otomatis | ❌ Tidak ada (post-MVP dengan payment gateway) |
| Payment method | Type `qris_static`, tercatat di `payments.method_name` |
| Audit | qris_payment_confirmed |
| Risiko | Kasir bisa konfirmasi tanpa customer benar-benar bayar → mitigasi: audit + CCTV merchant |

### 7.25 Split Rata

| Aspek | Aturan |
|---|---|
| Trigger | Kasir klik "Split Rata" di payment modal |
| Input | Jumlah orang (min 2, max 20) |
| Kalkulasi | grand_total / jumlah_orang, dibulatkan ke atas per orang |
| Sisa | Orang terakhir membayar sisa (agar total = grand_total) |
| Pembayaran | Setiap orang bisa bayar dengan metode berbeda |
| Split rata + split per produk | Tidak bisa bersamaan; pilih salah satu |

### 7.26 Refund Parsial per Item

| Aspek | Aturan |
|---|---|
| Siapa bisa | Owner/admin (bukan kasir) |
| Syarat | Order sudah `paid` atau `partially_refunded` |
| Item | Pilih item spesifik + qty (tidak boleh melebihi qty original - qty sudah refunded) |
| Stock | Stock movement `return` per item yang di-refund |
| Kas | Cash refund mengurangi expected_cash di shift report |
| Status order | `partially_refunded` jika belum semua item; `refunded` jika semua |
| Audit | partial_refund_created |

### 7.27 Print Ulang Struk

| Aspek | Aturan |
|---|---|
| Siapa bisa | Kasir (shift aktif), owner/admin |
| Syarat | Order sudah `paid` atau `refunded` |
| Limit | `receipt_settings.max_print_count` (default 5; NULL = unlimited) |
| Counter | `orders.print_count` increment setiap print |
| Audit | receipt_reprinted |
| Log | Setiap print ulang tercatat dengan timestamp dan user |

### 7.28 Koneksi Putus Saat Transaksi

| Aspek | Aturan |
|---|---|
| Deteksi | `navigator.onLine === false` |
| Cart | Tersimpan di localStorage (key: `kasirku_cart_{tenant_id}`) |
| Banner | "⚠ Koneksi terputus. Cart tersimpan." |
| Sync | Otomatis saat koneksi pulih |
| Konflik | Jika cart di server berbeda, tampilkan dialog resolusi |
| Timeout | Jika sync gagal 3×, toast error + tombol "Coba Lagi" |
| Data hilang | Jika browser ditutup saat offline, cart di localStorage tetap ada saat buka lagi |

### 7.29 Bluetooth Thermal Printer

| Aspek | Aturan |
|---|---|
| Teknologi | Web Bluetooth API (Chrome/Edge only) |
| Printer | ESC/POS compatible 58mm/80mm |
| Pairing | Settings → Printer → Scan → Pair → Test Print |
| Fallback | Jika Web Bluetooth tidak didukung, gunakan CSS @media print |
| Auto-print | Opsional: auto-print struk setelah pembayaran |
| Error handling | Jika printer error, toast + fallback ke print dialog |
| Browser support | Chrome 85+, Edge 85+; Firefox/Safari tidak support Web Bluetooth |

### 7.30 Pengaturan Kasir

| Aspek | Aturan |
|---|---|
| Kas awal default | Config per outlet (default Rp0). Ditampilkan sebagai suggestion saat buka shift |
| Batas diskon kasir | Config per tenant (default: tidak terbatas). Jika kasir coba diskon > batas → error |
| Izin kasir | Config per tenant: can_void (default TRUE), can_refund (default FALSE), can_discount (default TRUE) |
| Auto-close shift | Config per outlet (default OFF). Jika aktif, shift otomatis tutup setelah X jam |
| Kas keluar/masuk | Config per tenant (default TRUE). Toggle untuk aktifkan/nonaktifkan |
| Siapa bisa ubah | Owner/admin saja |

### 7.31 Pengaturan E-Wallet

| Aspek | Aturan |
|---|---|
| QRIS statis | Owner upload QR code (PNG/JPG, maks 2MB). Tampil di POS saat kasir pilih "QRIS" |
| E-wallet custom | Owner tambah metode pembayaran: nama, type 'ewallet', settlement account (opsional) |
| Konfirmasi | Manual oleh kasir. Tidak ada verifikasi otomatis di MVP |
| Audit | ewallet_qr_uploaded, ewallet_method_created |

### 7.32 Promosi

| Aspek | Aturan |
|---|---|
| Voucher | Kode unik per tenant, tipe %/Rp, min purchase, max penggunaan, expiry, stackable (default TIDAK) |
| Diskon Terjadwal | Periode (tanggal + jam), scope (semua/kategori/produk), tipe %/Rp |
| Voucher di POS | Kasir input kode → validasi → diskon diterapkan |
| Diskon terjadwal di POS | Otomatis diterapkan saat periode aktif, tanpa input kasir |
| Voucher + diskon item | Tidak bisa digabung kecuali stackable = TRUE |
| Voucher + kasbon | Tidak bisa digabung |
| Fitur gated | `promo.voucher` (Advance+), `promo.scheduled` (Prime+) |
| Audit | voucher_created, voucher_redeemed, scheduled_discount_applied |

### 7.33 Notifikasi Perubahan Data

| Aspek | Aturan |
|---|---|
| Trigger | INSERT/UPDATE/DELETE pada: products, users, outlets, tax_rates, receipt_settings, tax_settings, payment_methods, customers |
| Penerima | Owner + admin tenant |
| Channel | In-app notification (tabel notifications) |
| Format | "[User X] mengubah [Entitas Y]: [detail perubahan]" |
| Preferensi | Owner bisa toggle ON/OFF per kategori. Default: semua ON |
| Tidak bisa dimatikan | Notifikasi keamanan (login, perubahan password, perubahan role) |
| Audit | notification_preference_updated |

### 7.34 Pencarian Global

| Aspek | Aturan |
|---|---|
| Trigger | Ctrl+K (desktop) atau tap ikon search (mobile) |
| Scope | Produk, customer, order, PO, supplier |
| Hasil | Terkelompok per kategori, maks 5 per kategori |
| Debounce | 300ms |
| Permission | Hasil sesuai role (kasir tidak lihat PO/supplier) |
| Rate limit | 60/menit/user |

### 7.35 Laporan Produk Terlaris

| Aspek | Aturan |
|---|---|
| Akses | Semua role merchant (owner, admin, cashier) |
| Filter | Periode (harian/mingguan/bulanan), outlet, kategori |
| Data | Top N produk berdasarkan qty terjual dan/atau revenue |
| Grafik | Bar chart horizontal |
| Export | CSV (gated `export.csv`) |

---

## §8. Non-Functional Requirements

### 8.1 Performance

| Metrik | Target |
|---|---|
| Landing LCP | < 2.5 detik |
| Dashboard TTI (4G) | < 3 detik |
| POS transaksi P95 | < 1.5 detik |
| POS login (PIN-only) | ≤ 5 detik |
| Hold order save | < 200ms |
| Kas keluar/masuk save | < 200ms |
| Pencarian global | < 300ms |
| Backoffice list 10k tenant | < 2 detik |
| Stock movement insert | < 100ms |
| Shift report generate | < 2 detik |
| Export CSV 10k row | < 5 menit |
| Search produk (10k) | < 500ms |
| Production uptime | ≥ 99.5% |
| Staging cold start | < 60 detik (Render sleep) |

### 8.2 Compatibility

- Chrome/Edge/Firefox/Safari 2 versi terakhir (desktop & mobile)
- Responsive: <640px mobile; 640–1024px tablet; >1024px desktop
- Tidak ada IE
- Timezone: default Asia/Jakarta; outlet dapat override
- Currency: IDR; format Rp1.000.000
- Locale: id-ID; format tanggal dd/mm/yyyy
- Print: struk, bill, shift report (58mm/80mm)
- Barcode scanner: HID keyboard-emulation support
- Web Bluetooth: Chrome 85+, Edge 85+ (fallback: CSS print)

### 8.3 Security (Target)

| Area | Target |
|---|---|
| External | HTTPS/TLS 1.2+; HttpOnly + CSRF; rate limiting; password policy; OTP 5 menit; CSP; HSTS |
| Internal | MFA wajib; 8 jam; RBAC least privilege; audit immutable |
| Multi-tenant | 100% query domain + tenant_id |
| Data at-rest | Password hash; PIN hash; MFA secret encrypted |
| Data in-transit | TLS 1.2+; HSTS; secure cookies |
| Production | Re-auth aksi kritikal; secrets rotation 90 hari |
| PIN kasir | Argon2id hash; lockout 5 percobaan; 15 menit lockout |
| Kasbon limit | Server-side validation; tidak bisa di-bypass dari frontend |

### 8.4 Accessibility

- WCAG 2.1 AA basic
- Keyboard navigation untuk POS
- Focus visible
- Color contrast ≥ 4.5:1

### 8.5 SLA

| Environment | SLA |
|---|---|
| MVP (staging) | Ad-hoc |
| Production | Uptime 99.5% (~3.6 jam downtime/bulan) |

### 8.6 Rate Limiting (15 rate limits)

| Endpoint | Limit |
|---|---|
| Login dashboard | 5/menit/IP |
| Login POS (PIN) | 10/menit/IP |
| OTP request | 3/5 menit/nomor |
| OTP verifikasi | 5 percobaan per kode |
| Register | 3/jam/IP |
| Forgot password | 3/jam/email |
| Email verification resend | 3×/24 jam/user |
| Admin login | 5/menit/IP |
| Admin MFA | 5 percobaan per sesi |
| Support access grant | 10/menit/merchant |
| Export request | 1×/24 jam/tenant |
| POS checkout | 30/menit/user |
| Product search | 60/menit/user |
| PIN verification | 5 percobaan per PIN per 15 menit |
| Hold order | 20/menit/user |

---

## §9. Pricing & Monetization

### 9.1 Pricing

| Paket | Bulanan | Tahunan | Posisi vs Majoo |
|---|---|---|---|
| Trial | Rp0 (14 hari) | — | — |
| Starter Basic | Rp149.000 | Rp1.490.000 | Di bawah Majoo Starter |
| Starter | Rp249.000 | Rp2.490.000 | Setara Majoo Starter |
| Advance | Rp449.000 | Rp4.490.000 | Sedikit di bawah Majoo Advance |
| Prime | Rp899.000 | Rp8.990.000 | Sedikit di bawah Majoo Prime |
| Prime+ | Custom | Custom | Enterprise |

**Add-on:**

| Add-on | Harga |
|---|---|
| Ekstra Login POS | Rp100.000/bln |
| Ekstra Karyawan | Rp100.000/bln |
| Ekstra Hak Akses Admin | Rp200.000/bln |

Diskon tahunan: ~17% (2 bulan gratis).

### 9.2 Model Monetisasi

| Sumber | Status MVP |
|---|---|
| Langganan bulanan/tahunan | ✅ MVP |
| Add-on | ✅ MVP |
| Payment gateway fee | ❌ Post-MVP |
| Partner/reseller commission | ❌ Post-MVP |

### 9.3 Billing Flow

Manual: Finance kirim invoice via email/WA → merchant transfer → Finance tandai lunas. Grace period: 7 hari read-only → 30 hari arsip. Auto-debit = post-MVP.

### 9.4 Refund Policy

| Aspek | Aturan |
|---|---|
| Trial | Tidak ada pembayaran/refund |
| Bulanan | Prorated 50% sisa hari |
| Tahunan | Prorated penuh (sisa bulan ÷ 12 × harga tahunan) |
| Add-on | Prorated 50% sisa hari |
| Proses | Support → Finance review → refund 7 hari kerja |
| Refund method | Transfer bank |
| Refund window | Maks 14 hari setelah pembayaran |

### 9.5 Break-even

- MVP: Rp0
- Production: ~Rp500–800rb/bln
- Break-even: ~2–4 merchant Starter Basic atau ~1 Prime/bln

**Proyeksi 12 bulan:**

| Bulan | Target Merchant | MRR |
|---|---|---|
| 1 | 10 | ~Rp2.5jt |
| 3 | 50 | ~Rp12jt |
| 6 | 150 | ~Rp37jt |
| 12 | 500 | ~Rp125jt |

---

## §10. Timeline & Milestone

### 10.1 MVP — ~19 Minggu

| Fase | Durasi | Deliverable | Milestone |
|---|---|---|---|
| Fase 0 | 0.5 mg | Bootstrap lokal minimal | M0 |
| Fase 1 | 2 mg | Auth + onboarding + login POS PIN-only + Setup Awal Owner | M1 |
| Fase 2 | 2 mg | Produk + modifier + tim + outlet | M2 |
| Fase 3 | 2 mg | Inventori + CRM + kasbon | M3 |
| Fase 4 | 3.5 mg | POS core + shift report + hold + kas keluar/masuk + QRIS + split rata + refund parsial + print ulang + koneksi putus + Bluetooth + promosi | M4 |
| Fase 5 | 2 mg | Langganan + gating + backoffice | M5 |
| Fase 6 | 2 mg | Compliance + support + notifikasi perubahan data + pencarian global | M6 |
| Fase 7 | 2 mg | Setup staging & deploy | M7 |
| Fase 8 | 2 mg | Staging QA + UAT | M8–M10 |
| **Total MVP** | **~19 mg** | **M10 — Staging UAT Complete** | |

### 10.2 Production — ~4 Minggu

| Sub-Fase | Durasi | Deliverable | Milestone |
|---|---|---|---|
| Fase 9A — Production-test | 1.5 mg | Verifikasi production readiness + smoke test | M11 |
| Fase 9B — Production Live | 1.5 mg | Go-live + cutover + DNS switch | M12 |
| Fase 9C — Stabilization | 1 mg | Monitoring 24 jam + post-mortem + review | M13 |
| **Total Production** | **~4 mg** | **M13 — Production Stable** | |

### 10.3 Milestone Kunci (M0–M13)

| ID | Milestone | Fase | Target |
|---|---|---|---|
| M0 | Bootstrap lokal siap | Fase 0 | Minggu 0.5 |
| M1 | Auth + onboarding + login PIN-only + Setup Awal selesai | Fase 1 | Minggu 2.5 |
| M2 | Produk + modifier + tim + outlet selesai | Fase 2 | Minggu 4.5 |
| M3 | Inventori + CRM + kasbon selesai | Fase 3 | Minggu 6.5 |
| M4 | POS core + semua fitur POS baru selesai | Fase 4 | Minggu 10 |
| M5 | Langganan + gating selesai | Fase 5 | Minggu 12 |
| M6 | Compliance + support + notifikasi + pencarian selesai | Fase 6 | Minggu 14 |
| M7 | Staging deploy sukses | Fase 7 | Minggu 16 |
| M8 | Staging QA lulus | Fase 8 | Minggu 18 |
| M9 | UAT stakeholder lulus | Fase 8 | Minggu 19 |
| M10 | Staging UAT Complete = MVP selesai | Fase 8 | Minggu 19 |
| M11 | Production-test Ready | Fase 9A | Minggu 20.5 |
| M12 | Production Live | Fase 9B | Minggu 22 |
| M13 | Production Stable | Fase 9C | Minggu 23 |

### 10.4 Buffer

- Fase 4 (POS + fitur baru) kompleks: +1 mg
- Fase 8 (QA) bug besar: +0.5 mg
- Fase 9A gate gagal: +0.5 mg
- Fase 9B cutover issue: +0.5 mg
- Total buffer: ~2.5 mg tersebar

---

## §11. Risks & Assumptions

### 11.1 Asumsi

- 1 owner = 1 tenant; 1 outlet default; multi-outlet Prime+ (Should)
- Trial 14 hari semua fitur & kuota terbuka
- Bahasa Indonesia; browser modern; tim ≤15 orang
- Dev di lokal; staging ad-hoc Fase 7
- Production live di Fase 9B (bukan di MVP)
- Resep/bahan baku = Post-MVP
- Payment gateway = Post-MVP
- Tidak ada mobile app native; web responsif
- Tim developer: 2–4 orang full-time
- Staging menggunakan Stack Combo tanpa CC (Render+TiDB+Upstash+R2)
- Kasbon hanya untuk customer terdaftar
- QRIS statis memerlukan merchant sudah punya QR code dari bank/e-wallet
- Bluetooth printer hanya Chrome/Edge (fallback CSS print)
- Hold order tidak mengurangi stock
- Promosi voucher tidak bisa digabung dengan kasbon
- Setup Awal Owner tidak blocking — owner bisa langsung pakai POS tanpa menyelesaikan semua

### 11.2 Risiko MVP

| Risiko | Dampak | Mitigasi |
|---|---|---|
| WebView/browser usang | Medium | Deteksi + pesan upgrade |
| Render sleep 15 menit | Low | Acceptable untuk staging |
| TiDB FK behavior beda | Medium | Verifikasi di Fase 7; fallback Aiven Free |
| Upstash 10K limit habis | Low | Cukup untuk staging; monitor usage |
| Biaya OTP WhatsApp naik | Medium | Monitor; fallback email OTP |
| Fase 7 molor | Medium | Fokus setup, tanpa fitur baru |
| Bug besar di staging | High | QA ketat Fase 8 |
| PII di non-production | High | Dicegah via checklist + audit |
| Scope creep | High | Timeboxed; feature gated |
| Tim developer kurang | High | Kontrak jelas; buffer 2.5 mg |
| Kasbon tidak dibayar | Medium | Limit per customer; notifikasi jatuh tempo; laporan outstanding |
| QRIS konfirmasi palsu | Medium | Audit log; CCTV merchant; konfirmasi kasir + alasan |
| Web Bluetooth tidak support | Low | Fallback CSS @media print; deteksi browser |
| Hold order menumpuk | Low | Limit 10 hold; wajib resolve sebelum tutup kasir |
| Setup Awal Owner diabaikan | Low | Checklist tidak blocking; tooltip kontekstual |
| Promosi disalahgunakan | Medium | Max penggunaan per voucher; audit; limit per customer |

### 11.3 Risiko Production

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Deploy premature | Critical | Production readiness gate |
| Rollback gagal | Critical | Backup pre-migration wajib + restore test |
| Cutover ganda | High | Cutover lock |
| Email masuk spam | High | SPF/DKIM/DMARC |
| VPS overload | High | Monitoring 24 jam; scaling plan |
| SSL expired | High | Auto-renew + alert |
| Backup gagal | Critical | Daily backup + restore test bulanan |
| Incident P1 unresolved | Critical | Incident response procedure |
| Biaya membengkak | Medium | Cost review; budget alert |

---

## §12. Analytics

### 12.1 Events MVP (44)

| # | Event | Kategori |
|---|---|---|
| 1 | landing_view | Marketing |
| 2 | cta_click | Marketing |
| 3 | register_start | Marketing |
| 4 | register_complete | Support |
| 5 | onboarding_complete | Support |
| 6 | pos_first_transaction | Operational |
| 7 | pos_transaction | Operational |
| 8 | pos_transaction_failed | Operational |
| 9 | trial_expiring | Operational |
| 10 | plan_selected | Operational |
| 11 | plan_paid | Operational |
| 12 | feature_blocked | Operational |
| 13 | quota_blocked | Operational |
| 14 | support_access_granted | Support |
| 15 | support_access_expired | Support |
| 16 | export_completed | Support |
| 17 | export_failed | Support |
| 18 | account_deleted | Support |
| 19 | cookie_consent_saved | Support |
| 20 | env_guard_failed | Operational |
| 21 | inventory_po_created | Operational |
| 22 | inventory_stock_movement | Operational |
| 23 | inventory_low_stock_alert | Operational |
| 24 | modifier_selected | Operational |
| 25 | customer_created | Operational |
| 26 | loyalty_point_earned | Operational |
| 27 | loyalty_point_redeemed | Operational |
| 28 | shift_report_generated | Operational |
| 29 | split_bill_created | Operational |
| 30 | hold_order_created | Operational |
| 31 | hold_order_resumed | Operational |
| 32 | hold_order_voided | Operational |
| 33 | credit_sale_created | Operational |
| 34 | credit_payment_received | Operational |
| 35 | cash_movement_created | Operational |
| 36 | qris_payment_confirmed | Operational |
| 37 | switch_user_pos | Operational |
| 38 | pos_login_pin | Operational |
| 39 | split_equal_created | Operational |
| 40 | partial_refund_created | Operational |
| 41 | receipt_reprinted | Operational |
| 42 | offline_cart_synced | Operational |
| 43 | bluetooth_printer_connected | Operational |
| 44 | global_search_used | Operational |

### 12.2 Events Production (10)

| # | Event | Kategori |
|---|---|---|
| 45 | release_gate_evaluated | Production |
| 46 | cutover_started | Production |
| 47 | cutover_completed | Production |
| 48 | rollback_triggered | Production |
| 49 | rollback_completed | Production |
| 50 | incident_reported | Production |
| 51 | post_mortem_completed | Production |
| 52 | backup_success | Production |
| 53 | backup_failed | Production |
| 54 | smoke_test_passed | Production |

**Total: 54 events** (44 MVP + 10 Production).

### 12.3 Aturan Analytics

- Event marketing wajib cookie consent analytics = true; tanpa consent → tidak dikumpulkan
- Event operational & support selalu dikumpulkan (legitimate interest)
- Tidak ada PII di properties
- IP di-hash HMAC-SHA256 — tidak disimpan plain
- Bot filter untuk event marketing
- Retensi 90 hari

### 12.4 Dashboard Analytics

| Dashboard | Metrics | Audience |
|---|---|---|
| Growth | Register, onboarding, trial→paid | PM, Marketing |
| Product Usage | pos_transaction, inventory, CRM, modifier, hold, kasbon, QRIS | PM |
| Feature Gating | feature_blocked, quota_blocked | Engineering |
| Support | support_access, export, ticket | Support |
| Production | Release gate, cutover, rollback, incident | Ops, SRE |
| Finance | plan_paid, MRR, refund, kasbon outstanding | Finance |

---

## §13. Compliance & Legal

### 13.1 Regulasi

| Regulasi | Relevansi | Status |
|---|---|---|
| UU PDP No. 27/2022 | Perlindungan data | Wajib |
| UU ITE | Transaksi elektronik | Wajib |
| PPN | Pajak | Wajib — 11% |
| Perpajakan | PPh badan, PPh 21 | Wajib |
| UU Perlindungan Konsumen | S&K, refund | Wajib |
| Permenkominfo No. 5/2020 | Penyelenggara sistem elektronik | Wajib (registrasi) |

### 13.2 Data Residency

| Environment | PII asli? | Wajib Indonesia? |
|---|---|---|
| Local | ❌ seed | ❌ |
| Staging (Render/TiDB/Upstash/R2) | ❌ seed | ❌ |
| Production-test | ❌ seed | ❌ |
| Production | ✅ PII | ✅ Wajib (Jakarta) |

### 13.3 Data Retention

| Data | Retensi |
|---|---|
| Tenant aktif | Langganan + 30 hari |
| Tenant arsip | 30 hari → arsip |
| Audit logs | 90 hari |
| Sessions revoked | 30 hari |
| OTP expired | 24 jam |
| Email verifications | 7 hari |
| Billing invoices | 5 tahun |
| Subscriptions | 5 tahun |
| Data merchant dihapus | Soft delete 30 hari → arsip permanen |
| Export file | 24 jam |
| Cookie consents | 2 tahun |
| Support tickets closed | 2 tahun |
| Analytics events | 90 hari |
| Stock movements | 5 tahun (immutable) |
| Shift reports | 5 tahun (immutable) |
| Cash movements | 5 tahun (immutable) |

### 13.4 Hak Pengguna UU PDP

| Hak | Implementasi |
|---|---|
| Akses data | Export CSV via dashboard |
| Koreksi data | Edit profil |
| Hapus data | Ajukan hapus → soft delete 30 hari → arsip |
| Portabilitas | Export CSV |
| Tarik consent | Hapus akun = tarik consent; cookie settings = tarik analytics/marketing |
| Informasi | Privacy policy + cookie policy + data retention info |

### 13.5 Consent Withdrawal

- Data & Privacy → Cookie Settings → toggle OFF → catat versi baru (INSERT only)
- Hapus akun = tarik consent keseluruhan (soft delete 30 hari)
- Withdrawal tidak menghapus data wajib (billing 5 tahun, audit 90 hari)

### 13.6 Business Entity

- Bentuk: PT (Perseroan Terbatas)
- NPWP: Wajib untuk invoice pajak
- Rekening bank: BCA/Mandiri
- Alamat: Jakarta
- Akta pendirian: Wajib sebelum production live

### 13.7 Registrasi Penyelenggara Sistem Elektronik

- Wajib registrasi di Kominfo (Permenkominfo No. 5/2020)
- Dokumen: akta PT, NPWP, KTP direktur, domain, sistem deskripsi
- Timeline: sebelum production live (Fase 9B)

### 13.8 Terms of Service & Privacy Policy

- Wajib tersedia di /terms dan /privacy
- Bahasa Indonesia
- Mencakup: data collection, usage, retention, user rights, refund policy, limitation of liability
- Review legal sebelum production live



---

## §14. Support Model & SLA

### 14.1 Channel

| Channel | MVP | Production |
|---|---|---|
| In-app chat | mailto:support@kasirku.id | Chatwoot self-hosted |
| Email | ✅ | ✅ |
| WhatsApp | ✅ | ✅ |
| KB | Static Markdown /help | BookStack self-hosted |
| Status page | Instatus.com free | Uptime Kuma self-hosted |
| On-Call | Slack | GoAlert |
| Phone (critical only) | ❌ | ✅ (P1 only) |

### 14.2 SLA

| Tingkat | Contoh | First Response | Resolution |
|---|---|---|---|
| Critical (P1) | Tenant down, tidak bisa transaksi, data loss | < 1 jam | < 4 jam |
| High (P2) | Fitur utama tidak berfungsi | < 4 jam | < 1 hari kerja |
| Medium (P3) | Bug minor, pertanyaan | < 8 jam | < 3 hari kerja |
| Low (P4) | Feature request, feedback | < 2 hari kerja | Backlog |

Uptime production: 99.5%. MVP: SLA ad-hoc (best effort).

### 14.3 Support Access Policy

- Merchant grant → support mode ≤30 menit
- Banner dua sisi
- Read-only default
- Write escalation butuh approval merchant (maks 15 menit)
- Alasan wajib; audit wajib
- Support tidak boleh ubah billing, hapus tenant, ubah role, atau hapus data

### 14.4 Support Tiers

| Tier | Siapa | Handling |
|---|---|---|
| Tier 1 | Support/CS | Pertanyaan umum, reset password, panduan |
| Tier 2 | Support + Ops | Bug investigation, feature issue |
| Tier 3 | Engineering | Bug fix, incident response |
| Tier 4 | Super Admin + CTO | P1 incident, security incident |

### 14.5 Escalation

Merchant → Tier 1 → Tier 2 → Tier 3 → Tier 4 (P1) → Incident Commander

Timeline: Tier 1→2: 1 jam; Tier 2→3: 2 jam; Tier 3→4: 4 jam atau P1.

### 14.6 Incident Management

| Severity | Response | Status Page Update | Post-Mortem |
|---|---|---|---|
| P1 Critical | < 15 menit | Setiap 30 menit | Wajib (48 jam) |
| P2 High | < 1 jam | Setiap 2 jam | Opsional |
| P3 Medium | < 8 jam | — | — |
| P4 Low | < 2 hari | — | — |

### 14.7 On-Call Schedule (Production)

| Shift | Waktu (WIB) | PIC |
|---|---|---|
| Shift 1 | 08:00–20:00 | Ops on-call |
| Shift 2 | 20:00–08:00 | Ops on-call (backup) |
| Weekend | 24 jam | Rotasi Ops |
| Eskalasi | Jika on-call tidak respon 15 menit | Super Admin |

### 14.8 Support Metrics

| Metric | Target |
|---|---|
| First response time | Sesuai SLA |
| Resolution time | Sesuai SLA |
| CSAT | ≥ 4.5/5 |
| Ticket backlog | < 10 |
| Escalation rate | < 5% |
| Support access abuse | 0 |

---

## §15. Acceptance Criteria

### 15.1 Auth & Registrasi

- Register: Given email valid + WA + consent checked, When submit, Then OTP terkirim + user dibuat unverified
- OTP: Given kode valid dalam 5 menit, When verifikasi, Then akun verified + sesi dibuat. Given kadaluarsa/5× salah, Then error
- Login dashboard: Given kredensial valid, When login, Then session valid + CSRF; regenerate ID
- Verifikasi email: Given link >24 jam, When klik, Then expired; bisa resend maks 3×/24 jam
- Forgot password: Given email terdaftar, When request, Then link expiry 30 menit; single-use; rate limit 3/jam/email

### 15.2 Login POS PIN-Only

- Given device sudah aktif, When kasir pilih nama + PIN benar, Then masuk POS dalam ≤5 detik
- Given PIN salah 5×, When coba lagi, Then lockout 15 menit + audit
- Given device belum aktif, When kasir coba login, Then tampilkan form activation code
- Given kasir A aktif, When kasir B klik "Switch User" + PIN benar, Then session berubah ke B tanpa logout device
- Given kasir login di device B, When kasir yang sama login di device C, Then session di device B revoked + audit

### 15.3 Setup Awal Owner

- Given owner selesai onboarding, When masuk dashboard, Then Setup Checklist tampil dengan 10 langkah
- Given owner klik "Tambah Produk" di checklist, When redirect, Then langsung ke form produk
- Given owner klik "Lewati", When konfirmasi, Then checklist hilang + audit setup_checklist_dismissed
- Given owner selesaikan semua 10 langkah, When cek dashboard, Then checklist hilang + toast "Setup selesai!"

### 15.4 Cookie Consent

- Given banner pertama kali, When toggle analytics OFF + Simpan, Then consent tercatat dengan versi policy saat ini
- Given consent ditarik, When toggle OFF, Then tercatat; tidak ada penghapusan data lama

### 15.5 Pengaturan POS

- Given toggle pajak ON + metode "sebelum diskon", When transaksi, Then pajak dihitung sebelum diskon dan tercatat di struk
- Given service charge ON + dikenai pajak, When transaksi, Then service charge masuk basis pajak
- Given tax rate CRUD, When owner/admin tambah rate baru, Then rate tersedia untuk produk baru; rate nonaktif tidak muncul

### 15.6 Pengaturan Kasir

- Given batas diskon kasir 20%, When kasir coba diskon 25%, Then error "Diskon melebihi batas"
- Given can_refund = FALSE, When kasir coba refund, Then 403
- Given auto-close shift 8 jam, When 8 jam lewat, Then shift otomatis tutup + shift report generate

### 15.7 Transaksi POS

- Given produk + qty, When bayar tunai, Then order + payment tercatat; struk tercetak; kasir balance terupdate
- Given split bill per produk, When dua pembayaran berbeda, Then total per split sesuai; order lunas saat total tercapai
- Given partial payment, When bayar sebagian, Then status partially_paid; boleh lanjut sampai lunas
- Given void order belum lunas, When void, Then status void; tidak mengubah kas
- Given refund order lunas, When refund full, Then refund tercatat; expected cash berkurang; stock movement return dibuat
- Given kuota POS terlampaui, When login POS, Then 403 + prompt add-on
- Given grand total 10.230 + pembulatan tunai Rp100, When bayar tunai, Then dibulatkan ke atas 10.300
- Given ada order pending/split terbuka, When tutup kasir, Then ditolak
- Given ada hold order aktif, When tutup kasir, Then ditolak "Selesaikan atau void hold terlebih dahulu"

### 15.8 Hold Order

- Given kasir punya item di cart, When klik "Hold", Then order tersimpan dengan status 'hold' + cart kosong
- Given ada 10 hold aktif, When coba hold ke-11, Then error "Maksimal 10 hold"
- Given ada hold aktif, When tutup kasir, Then ditolak "Selesaikan atau void hold terlebih dahulu"
- Given kasir A hold order, When kasir B resume, Then cart terisi + status kembali 'open'
- Given hold order, When produk di-hold, Then stock TIDAK berkurang

### 15.9 Kasbon/Piutang

- Given customer dengan limit Rp500.000 dan credit_balance Rp400.000, When kasbon Rp150.000, Then ditolak "Limit tidak cukup"
- Given customer dengan limit cukup, When kasbon, Then order status 'credit' + credit_balance bertambah
- Given kasbon Rp100.000, When customer bayar Rp50.000, Then credit_balance berkurang Rp50.000 + payment tercatat
- Given kasbon lunas, When selesai, Then loyalty point earned
- Given `pos.credit_sale` tidak aktif (Basic), When akses kasbon, Then 403
- Given customer dengan credit_balance > 0, When hapus customer, Then ditolak

### 15.10 Kas Keluar/Masuk

- Given shift aktif, When kasir kas keluar Rp50.000 + alasan, Then cash_movements INSERT + expected_cash berkurang
- Given kas keluar tanpa alasan, When submit, Then error "Alasan wajib"
- Given kas keluar, When tutup kasir, Then shift report menampilkan total kas keluar
- Given cash_movements, When UPDATE/DELETE, Then ditolak (immutable)

### 15.11 QRIS Statis

- Given merchant sudah upload QR code, When kasir pilih QRIS, Then QR code tampil + tombol konfirmasi
- Given kasir klik "Sudah Diterima", When submit, Then order paid + payment method 'qris_static' + audit
- Given merchant belum upload QR code, When kasir pilih QRIS, Then error "QR code belum diatur"

### 15.12 Split Rata

- Given grand_total Rp100.000, When split rata 3 orang, Then 2 orang bayar Rp33.400 + 1 orang bayar Rp33.200
- Given split rata, When input 1 orang, Then error "Minimal 2 orang"

### 15.13 Refund Parsial

- Given order 5 item, When refund 1 item, Then status 'partially_refunded' + stock movement return 1 item
- Given item sudah refund 2 dari qty 3, When coba refund 2 lagi, Then error "Qty melebihi sisa"
- Given refund parsial, When semua item di-refund, Then status 'refunded'

### 15.14 Print Ulang

- Given max_print_count = 5 dan print_count = 5, When coba print ulang, Then error "Batas cetak tercapai"
- Given print ulang berhasil, When print, Then print_count increment + audit

### 15.15 Koneksi Putus

- Given koneksi putus saat checkout, When navigator.onLine = false, Then cart tersimpan di localStorage + banner offline
- Given koneksi pulih, When navigator.onLine = true, Then auto-sync + toast "Transaksi tersinkron"

### 15.16 Bluetooth Printer

- Given Chrome 85+, When pair printer, Then printer terhubung + test print berhasil
- Given Firefox, When buka settings printer, Then fallback "Gunakan print dialog browser"
- Given printer error saat print, When print, Then toast error + fallback print dialog

### 15.17 Promosi

- Given voucher "DISKON10" tipe 10% min purchase Rp50.000, When order Rp40.000, Then error "Min purchase belum terpenuhi"
- Given voucher sudah digunakan max kali, When coba redeem lagi, Then error "Voucher sudah habis"
- Given diskon terjadwal aktif Jumat 14:00-17:00, When transaksi Jumat 15:00, Then diskon otomatis diterapkan
- Given `promo.voucher` tidak aktif (Starter), When akses menu Promosi, Then 403

### 15.18 Notifikasi Perubahan Data

- Given admin mengubah harga produk, When simpan, Then owner terima notifikasi "[Admin] mengubah harga produk 'X': Rp25.000 → Rp28.000"
- Given owner toggle OFF notifikasi produk, When admin ubah produk, Then owner TIDAK terima notifikasi produk
- Given notifikasi keamanan (login), When owner toggle OFF, Then tetap terima (tidak bisa dimatikan)

### 15.19 Langganan

- Given trial aktif, When pilih paket, Then paket scheduled; trial tetap berjalan sampai habis
- Given trial habis tanpa paket, When akses fitur tulis, Then 403 read-only + banner
- Given trial habis + paket dipilih + belum bayar, When akses tulis, Then read-only sampai Finance konfirmasi
- Given downgrade menyebabkan kuota terlampaui, When downgrade berlaku, Then grace 7 hari
- Given fitur di luar paket, When akses, Then 403

### 15.20 Support Access

- Given merchant grant, When Support buka tenant, Then mode support aktif ≤30 menit; banner dua sisi; default read-only; audit tercatat
- Given 30 menit lewat, When aksi berikutnya, Then sesi support expired
- Given support read-only, When minta write escalation, Then merchant approve in-app; maks 15 menit; audit

### 15.21 Export & Delete

- Given merchant minta export produk, When job selesai, Then CSV tersedia; expire 24 jam
- Given merchant hapus akun, When konfirmasi nama tenant + alasan, Then soft delete 30 hari; cancel restore tersedia
- Given export rate limit, When request ke-2 dalam 24 jam, Then 429

### 15.22 Feature Gating & Kuota

- Given paket Starter Basic, When akses split bill, Then 403
- Given kuota karyawan 3 dan sudah 3, When tambah karyawan ke-4, Then 403 + prompt add-on
- Given kuota admin 1 dan sudah 1, When undang admin ke-2, Then 403 + prompt add-on
- Given kuota POS 2 dan sudah 2 device aktif, When aktivasi device ke-3, Then 403 + prompt add-on
- Given tenant A, When tenant B akses data tenant A, Then 403/404

### 15.23 Audit

- Given Super Admin override paket, When simpan, Then audit tercatat immutable dengan actor, tenant, aksi, alasan, timestamp
- Given audit log, When edit/delete, Then ditolak (immutable)

### 15.24 Tim & Akses

- Given kuota karyawan 3 dan sudah 3, When invite ke-4, Then 403 + prompt add-on
- Given cashier, When akses settings/team/reports penuh, Then 403
- Given owner/admin, When reset PIN kasir, Then PIN baru tersimpan; audit
- Given user dinonaktifkan, When user coba login, Then ditolak; session lama dicabut

### 15.25 Produk & Outlet

- Given produk dipakai order, When hapus, Then ditolak; harus nonaktif
- Given produk nonaktif, When buka POS, Then produk tidak muncul; tetap di histori
- Given multi-outlet tidak aktif, When tambah outlet ke-2, Then 403
- Given outlet dengan cashier session terbuka, When nonaktifkan outlet, Then ditolak

### 15.26 Device POS

- Given kuota POS habis, When aktivasi device, Then 403 + prompt add-on
- Given kode aktivasi, When expiry 15 menit lewat, Then kode tidak valid
- Given device di-revoke, When device coba akses POS, Then session dicabut; audit

### 15.27 MFA

- Given admin pertama login, When belum enroll MFA, Then wajib setup TOTP
- Given recovery codes, When digunakan, Then single-use
- Given Super Admin reset MFA, When re-auth + alasan, Then MFA direset; audit

### 15.28 Inventori

- Given PO draft, When submit, Then status sent; stock belum berubah
- Given PO sent, When receive full, Then stock bertambah; stock movement tercatat
- Given receive partial, When simpan, Then status partial
- Given order lunas dengan produk track_stock, When selesai, Then stock berkurang; movement sale tercatat
- Given refund, When proses, Then stock bertambah; movement return tercatat
- Given stock ≤ threshold, When cek dashboard, Then notifikasi muncul
- Given inventory.tracking tidak aktif (Basic), When akses menu Inventori, Then 403
- Given stock_movements di-update/delete, When request, Then ditolak (immutable)

### 15.29 Modifier

- Given modifier is_required = TRUE, When POS tambah produk, Then wajib pilih minimal 1 option
- Given max_selection = 2, When pilih 3 option, Then ditolak
- Given order dengan modifier, When struk cetak, Then modifier tampil di struk
- Given modifier nonaktif, When buka order lama, Then histori tetap tampil dari snapshot

### 15.30 CRM & Loyalty

- Given customer 100 poin, When redeem 50 poin, Then diskon sesuai config; poin berkurang; ledger tercatat
- Given order lunas dengan customer, When selesai, Then poin earned sesuai config; ledger tercatat
- Given phone duplikat dalam tenant, When tambah customer, Then 422
- Given crm.loyalty tidak aktif (Starter), When akses menu Customer, Then 403
- Given redeem > 50% grand_total, When submit, Then ditolak

### 15.31 Shift Report

- Given cashier session close, When simpan, Then shift report dibuat dengan semua ringkasan
- Given shift report sudah dibuat, When edit, Then ditolak (immutable)
- Given print shift report, When klik, Then print preview 58mm atau 80mm

### 15.32 Edge Cases

- Given kasir menutup browser tanpa logout, When login lagi, Then deteksi shift terbuka → "Lanjutkan atau tutup?"
- Given trial habis di tengah transaksi, When kasir sedang proses bayar, Then transaksi berjalan bisa selesai (grace 5 menit) → blokir berikutnya
- Given dua kasir edit produk bersamaan, When simpan, Then yang kedua error "Data sudah diubah oleh user lain"

---

## §16. Success Metrics

### 16.1 External (MVP)

| Metric | Target |
|---|---|
| Landing → register | ≥ 8% |
| Registration completion | ≥ 70% |
| Setup POS dalam 3 hari | ≥ 90% merchant |
| Time-to-first-transaction | ≤ 15 menit |
| Trial → paid | ≥ 25% |
| Feature gating accuracy | 100% |
| Kuota enforcement | 100% |
| Tenant isolation | 100% |
| POS success | ≥ 99% |
| Landing LCP | < 2.5 dtk |
| Cookie acceptance | ≥ 60% |
| Export completion | ≥ 95% (<5 menit) |
| Inventory usage | ≥ 40% merchant Starter+ |
| CRM loyalty usage | ≥ 30% merchant Advance+ |
| Modifier usage | ≥ 50% merchant F&B |
| POS login time (PIN-only) | ≤ 5 detik |
| Setup Awal Owner completion | ≥ 85% dalam 3 hari |
| Hold order usage | ≥ 20% merchant F&B |
| Kasbon usage | ≥ 15% merchant Starter+ |
| Kasbon repayment rate | ≥ 80% dalam 30 hari |
| QRIS usage | ≥ 30% merchant |
| Kas keluar/masuk usage | ≥ 40% merchant |
| Voucher usage | ≥ 10% merchant Advance+ |
| Pencarian global usage | ≥ 25% merchant |

### 16.2 Internal (MVP)

| Metric | Target |
|---|---|
| Audit coverage | 100% aksi tulis |
| Median time-to-resolve | ≤ 1 hari kerja |
| Insiden akses tanpa otorisasi | 0 |
| Staff MFA | 100% |
| Export success | ≥ 95% |
| Deletion processed ≤ 30 hari | 100% |
| Staging UAT Complete (M10) | ✅ |

### 16.3 Operational Cost

| Metric | Target |
|---|---|
| Biaya MVP | Rp0 |
| Biaya staging | Rp0 (tanpa CC) |
| Deploy staging | < 5 menit |

### 16.4 Production

| Metric | Target |
|---|---|
| Production readiness gate | 10/10 PASS |
| PII di non-production | 0 |
| Retention job berjalan | 10/10 |
| Backup restore test | Lulus |
| Security audit | Bersih |
| Health check | 200 |
| Smoke test | 20/20 |
| Uptime 24 jam pertama | ≥ 99.5% |
| Error 24 jam pertama | < 1% |
| Biaya production | ≤ Rp800rb/bln |

---

## §17. Product Decisions Log

| # | Keputusan | Status |
|---|---|---|
| PD-001 | Nama produk: Kasirku | ✅ Final |
| PD-002 | Bentuk: Web responsif, tanpa APK | ✅ Final |
| PD-003 | Tagline: "Kasir online UMKM Indonesia — cepat, murah, tanpa ribet" | ✅ Final |
| PD-004 | Trial 14 hari semua fitur, kuota unlimited | ✅ Final |
| PD-005 | Trial = plan terpisah (is_trial_plan = TRUE) | ✅ Final |
| PD-006 | 6 paket: Trial, Starter Basic, Starter, Advance, Prime, Prime+ | ✅ Final |
| PD-007 | Pricing: §9.1 | ✅ Final |
| PD-008 | Diskon tahunan ~17% | ✅ Final |
| PD-009 | 3 add-on: POS, Karyawan, Admin | ✅ Final |
| PD-010 | Billing manual via Finance (MVP) | ✅ Final |
| PD-011 | Refund: 50% prorated bulanan; prorated penuh tahunan | ✅ Final |
| PD-012 | Grace period 7 hari setelah expired | ✅ Final |
| PD-013 | State machine: trial → pending_payment → active → grace → expired → archived | ✅ Final |
| PD-014 | 21 fitur gated | ✅ Final |
| PD-015 | Kuota POS = total per tenant lintas outlet | ✅ Final |
| PD-016 | max_employees = owner+admin+cashier; max_admins = owner+admin | ✅ Final |
| PD-017 | RBAC merchant: owner/admin/cashier (tanpa tabel roles) | ✅ Final |
| PD-018 | RBAC internal: 5 role | ✅ Final |
| PD-019 | MFA wajib semua role internal | ✅ Final |
| PD-020 | Session 8 jam admin; 30 hari remember me | ✅ Final |
| PD-021 | OTP 6 digit, expiry 5 menit, maks 5 percobaan | ✅ Final |
| PD-022 | Email verification 24 jam; resend maks 3×/24 jam | ✅ Final |
| PD-023 | Password 8–20, ≥1 huruf/angka/simbol | ✅ Final |
| PD-024 | PIN kasir 6 digit, hash | ✅ Final |
| PD-025 | Onboarding 4 langkah | ✅ Final |
| PD-026 | Pajak 3 metode: sebelum diskon, sesudah diskon, termasuk pajak | ✅ Final |
| PD-027 | Tax rate CRUD oleh owner/admin; default PPN 11% | ✅ Final |
| PD-028 | Service charge dihitung dari subtotal setelah diskon | ✅ Final |
| PD-029 | Pembulatan tunai ke atas Rp100/Rp500; non-tunai Rp1 | ✅ Final |
| PD-030 | Split bill per produk | ✅ Final |
| PD-031 | Partial payment: status partially_paid | ✅ Final |
| PD-032 | Void: hanya order belum lunas | ✅ Final |
| PD-033 | Refund MVP: full refund order lunas | ✅ Final |
| PD-034 | Expected cash = opening + cash sales − cash refunds + kas masuk − kas keluar | ✅ Final |
| PD-035 | Tutup kasir wajib tidak ada order pending/split terbuka | ✅ Final |
| PD-036 | Nomor order: {outlet_code}-{yyyymmdd}-{seq} | ✅ Final |
| PD-037 | Semua IDR; multi-currency post-MVP | ✅ Final |
| PD-038 | Timezone mengikuti outlet; default Asia/Jakarta | ✅ Final |
| PD-039 | stock_movements immutable — hanya INSERT | ✅ Final |
| PD-040 | shift_reports immutable setelah cashier close | ✅ Final |
| PD-041 | customer_loyalty_points immutable | ✅ Final |
| PD-042 | audit_logs immutable | ✅ Final |
| PD-043 | Modifier ≠ variant | ✅ Final |
| PD-044 | Modifier required enforced; max_selection enforced | ✅ Final |
| PD-045 | Snapshot modifier di order_items | ✅ Final |
| PD-046 | Loyalty earn 1 poin/Rp1.000 (config) | ✅ Final |
| PD-047 | Loyalty redeem 1 poin = Rp1 (config); maks 50% grand_total | ✅ Final |
| PD-048 | Poin tidak expired di MVP | ✅ Final |
| PD-049 | PO status: draft → sent → partial → received; atau cancelled | ✅ Final |
| PD-050 | Stock adjustment alasan wajib | ✅ Final |
| PD-051 | Transfer antar outlet: dua row (keluar + masuk) | ✅ Final |
| PD-052 | Multi-outlet gated Prime+ | ✅ Final |
| PD-053 | Device POS aktivasi via kode 15 menit | ✅ Final |
| PD-054 | Support access ≤30 menit, read-only default | ✅ Final |
| PD-055 | Support write escalation: merchant approve, maks 15 menit | ✅ Final |
| PD-056 | Export CSV: async, rate limit 1×/24 jam, expire 24 jam | ✅ Final |
| PD-057 | Hapus akun: soft delete 30 hari, konfirmasi ketik nama tenant | ✅ Final |
| PD-058 | Cookie consent: tidak pre-checked, INSERT only | ✅ Final |
| PD-059 | 10 retention job wajib | ✅ Final |
| PD-060 | Data residency: production Jakarta | ✅ Final |
| PD-061 | Lokal/staging tanpa PII | ✅ Final |
| PD-062 | SLA production 99.5% | ✅ Final |
| PD-063 | Support SLA: Critical <1 jam, High <4 jam, Medium <8 jam, Low <2 hari | ✅ Final |
| PD-064 | Business entity: PT | ✅ Final |
| PD-065 | Registrasi Kominfo sebelum production live | ✅ Final |
| PD-066 | Analytics: 54 events, 3 kategori, consent gate | ✅ Final |
| PD-067 | IP analytics di-hash — tidak plain | ✅ Final |
| PD-068 | Retensi analytics 90 hari | ✅ Final |
| PD-069 | Feature gating di middleware, bukan hanya UI | ✅ Final |
| PD-070 | Kuota di middleware | ✅ Final |
| PD-071 | Tenant isolation: semua query domain wajib tenant_id | ✅ Final |
| PD-072 | Trial→paid belum bayar = read-only sampai Finance konfirmasi | ✅ Final |
| PD-073 | Expired route exemption: 12 dibuka, 12 diblokir | ✅ Final |
| PD-074 | MVP = Fase 0–8; berakhir M10 | ✅ Final |
| PD-075 | Production = Fase 9 (9A/9B/9C) | ✅ Final |
| PD-076 | Production readiness gate 10 item wajib PASS | ✅ Final |
| PD-077 | Cutover checklist 10 item wajib completed | ✅ Final |
| PD-078 | Rollback dengan re-auth + konfirmasi | ✅ Final |
| PD-079 | Monitoring 24 jam pertama | ✅ Final |
| PD-080 | Smoke test 20 checklist | ✅ Final |
| PD-081 | Production success criteria 14 item | ✅ Final |
| PD-082 | Biaya MVP Rp0 | ✅ Final |
| PD-083 | Biaya production ≤Rp800rb/bln | ✅ Final |
| PD-084 | Staging gratis tanpa CC (Render+TiDB+Upstash+R2+Instatus) | ✅ Final |
| PD-085 | Production VPS Jakarta | ✅ Final |
| PD-086 | SPF/DKIM/DMARC wajib production | ✅ Final |
| PD-087 | OTP WA: primary + fallback email | ✅ Final |
| PD-088 | Tim developer 2–4 orang full-time | ✅ Final |
| PD-089 | Total timeline 23 minggu (MVP 19 + Production 4) | ✅ Final |
| PD-090 | Status page staging: Instatus.com free | ✅ Final |
| PD-091 | Login POS = PIN-only (pilih karyawan + PIN), tanpa email/password | ✅ Final |
| PD-092 | Device activation wajib sebelum kasir login POS | ✅ Final |
| PD-093 | Switch User POS tanpa logout penuh; hanya perlu PIN kasir baru | ✅ Final |
| PD-094 | Session kasir tied to cashier_session (shift) | ✅ Final |
| PD-095 | Concurrent session: 1 kasir = 1 active POS session | ✅ Final |
| PD-096 | Hold order: maks 10 per outlet; stock tidak berkurang saat hold | ✅ Final |
| PD-097 | Hold order wajib di-resume/void sebelum tutup kasir | ✅ Final |
| PD-098 | Kasbon: hanya customer terdaftar; limit config per tenant | ✅ Final |
| PD-099 | Kasbon: stock dikurangi saat order dibuat; loyalty saat dibayar lunas | ✅ Final |
| PD-100 | Kasbon gated `pos.credit_sale`: Basic ❌, Starter+ ✅ | ✅ Final |
| PD-101 | Kas keluar/masuk: alasan wajib; immutable; semua plan | ✅ Final |
| PD-102 | QRIS statis: konfirmasi manual kasir; tanpa payment gateway | ✅ Final |
| PD-103 | QRIS statis: semua plan; merchant upload QR code sendiri | ✅ Final |
| PD-104 | Split rata: grand_total / jumlah_orang; sisa di orang terakhir | ✅ Final |
| PD-105 | Refund parsial: hanya owner/admin; per item + qty | ✅ Final |
| PD-106 | Print ulang: max_print_count dari receipt_settings; default 5 | ✅ Final |
| PD-107 | Koneksi putus: cart di localStorage; auto-sync saat online | ✅ Final |
| PD-108 | Bluetooth printer: Web Bluetooth API; fallback CSS print | ✅ Final |
| PD-109 | Laporan produk terlaris: semua plan; filter periode + outlet | ✅ Final |
| PD-110 | Pencarian global: Ctrl+K; scope produk/customer/order/PO/supplier | ✅ Final |
| PD-111 | End Customer = persona (bukan user login); menerima struk/invoice | ✅ Final |
| PD-112 | Akuntan/Pembukuan = role admin merchant dengan filter laporan | ✅ Final |
| PD-113 | Partner login = post-MVP; MVP dikelola internal | ✅ Final |
| PD-114 | Logout POS ≠ Tutup Kasir; logout revoke user session, cashier session tetap | ✅ Final |
| PD-115 | Trial habis di tengah transaksi: grace 5 menit untuk selesaikan | ✅ Final |
| PD-116 | PIN kasir lockout: 5 percobaan → 15 menit lockout | ✅ Final |
| PD-117 | Hold order tidak masuk shift report sampai paid | ✅ Final |
| PD-118 | Total analytics events: 54 (44 MVP + 10 Production) | ✅ Final |

---

## §18. Sign-off

### 18.1 Dokumen

- □ Semua section §1–§18 lengkap
- □ 19 journey di §5 lengkap
- □ 60 flow di §6 lengkap
- □ 21 fitur gated di §7.3 disetujui
- □ Business rules §7.1–§7.35 disetujui
- □ 54 analytics event disetujui
- □ 118 PD tercatat
- □ Tidak ada placeholder / TBD

### 18.2 Komersial & Timeline

- □ Pricing §9 disetujui Finance
- □ Timeline §10 disetujui Engineering
- □ Milestone M0–M13 disetujui
- □ Risks §11 disetujui

### 18.3 Compliance & Support

- □ Compliance §13 disetujui Legal
- □ Support model §14 disetujui Support Lead
- □ SLA disetujui

### 18.4 Production

- □ Production readiness milestone disetujui Ops/SRE
- □ 10 gate criteria disetujui
- □ Cutover 10 item disetujui
- □ Smoke test 20 item disetujui
- □ Production success criteria 14 item disetujui

### 18.5 Staging Infrastructure

- □ Render.com disetujui untuk app + cron
- □ TiDB Cloud disetujui untuk MySQL
- □ Upstash disetujui untuk Redis
- □ Cloudflare R2 disetujui untuk storage
- □ Instatus.com disetujui untuk status page
- □ Semua provider 100% tanpa CC dikonfirmasi

### 18.6 Stakeholder Approval

| Role | Nama | Tanggal |
|---|---|---|
| Product Manager | _________________ | ______ |
| Engineering Lead | _________________ | ______ |
| Design Lead | _________________ | ______ |
| QA Lead | _________________ | ______ |
| Ops/SRE Lead | _________________ | ______ |
| Finance Lead | _________________ | ______ |
| Super Admin | _________________ | ______ |
| Stakeholder / Investor | _________________ | ______ |

---

## Ringkasan Eksekutif

**Kasirku** adalah SaaS POS online untuk UMKM Indonesia — cepat, murah, tanpa ribet. Web responsif, tanpa APK.

**Masalah:** kasir lambat, daftar berbelit, tools terpisah untuk inventori & CRM, backoffice tidak terpusat, kasbon tidak terkelola, kas shift tidak akurat, tidak ada pembayaran non-tunai, login POS lambat, tidak ada setup awal terpadu, tidak ada promosi.

**Solusi:** registrasi 3 menit + onboarding 4 langkah + Setup Awal Owner 10 langkah + POS 1 layar + login PIN-only ≤5 detik + inventori + modifier + CRM & loyalty + shift report + kasbon + kas keluar/masuk + hold order + QRIS statis + promosi + backoffice lengkap.

**Fitur:** 19 journey, 21 fitur gated. Trial 14 hari semua fitur. 6 paket: Starter Basic Rp149rb, Starter Rp249rb, Advance Rp449rb, Prime Rp899rb, Prime+ custom. Diskon tahunan 17%.

**Persona:** 7 external (Visitor, Owner, Admin, Kasir, End Customer, Akuntan, Partner) + 5 internal (Super Admin, Support, Finance, Ops, Partnership).

**Timeline:** MVP 19 minggu (Fase 0–8) → M10 Staging UAT Complete. Production 4 minggu (Fase 9A–9C) → M13 Production Stable. Total 23 minggu.

**Biaya:** MVP Rp0. Staging Rp0 tanpa CC (Render+TiDB+Upstash+R2+Instatus). Production ~Rp500–800rb/bln. Break-even 2–4 merchant Starter Basic.

**Goals MVP:** landing→register ≥8%, registration completion ≥70%, time-to-first-transaction ≤15 menit, trial→paid ≥25%, gating 100%, POS success ≥99%, LCP <2.5 dtk, POS login ≤5 detik, kasbon repayment ≥80%, Setup Awal completion ≥85%, QRIS usage ≥30%.

**Goals Production:** 10 gate PASS, cutover <5 menit downtime, smoke test 20/20, uptime 24 jam ≥99.5%, error <1%, biaya ≤Rp800rb/bln, 14 success criteria terpenuhi.

**Compliance:** UU PDP No. 27/2022, UU ITE, PPN 11%, Permenkominfo 5/2020. Production wajib Jakarta. PT sebagai badan hukum. Registrasi Kominfo sebelum go-live.

**Status:** ✅ Siap eksekusi.
