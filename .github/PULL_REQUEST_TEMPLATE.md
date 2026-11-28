## Apa yang diubah
<!-- Jelaskan perubahan utama secara ringkas -->

## Kenapa
<!-- Jelaskan alasan bisnis atau teknis -->

## Cara test
<!-- Langkah verifikasi yang sudah dijalankan -->

## Environment
- [ ] Local (Fase 0–6)
- [ ] Staging (Fase 7–8)
- [ ] Production (Fase 9)

## Adapter / Guard / Analytics
- [ ] Adapter pattern diterapkan
- [ ] Guard rules tidak dilanggar
- [ ] Analytics consent diterapkan
- [ ] Expired route exemption dipatuhi

## Vanilla Frontend (WAJIB JIKA MENYENTUH FRONTEND)
- [ ] Tidak ada React/Vue/Svelte/Angular/TypeScript/Tailwind/Inertia/Alpine/HTMX/Livewire
- [ ] Blade view + vanilla JS ES module + CSS murni (custom properties)
- [ ] Tidak ada `innerHTML` untuk data user — pakai `textContent`/`createElement`
- [ ] Tidak ada bundler JS di `package.json`
- [ ] CSS menggunakan token dari `tokens.css` (87 token)
- [ ] Partial route test ditambahkan (jika AJAX fragment)
- [ ] Cache busting `?v={version}` untuk `public/js/*` dan `public/css/*`
- [ ] Canvas API untuk chart (bukan library eksternal)
- [ ] Touch events untuk gesture (bukan library eksternal)

## Fitur Baru POS (WAJIB JIKA MENYENTUH FITUR POS BARU)
- [ ] Login POS PIN-only (device activation + PIN pad)
- [ ] Hold order: limit 10, stock tidak berkurang, resolve sebelum tutup kasir
- [ ] Kasbon: server-side validation limit, loyalty saat lunas
- [ ] Kas keluar/masuk: alasan wajib min 10 char, immutable
- [ ] QRIS statis: konfirmasi manual, audit
- [ ] Split rata: sisa di orang terakhir
- [ ] Refund parsial: qty tidak melebihi sisa
- [ ] Print ulang: max_print_count, counter increment
- [ ] Offline cart: localStorage, auto-sync
- [ ] Bluetooth printer: Web Bluetooth API, fallback CSS print

## Promosi & Notifikasi (WAJIB JIKA MENYENTUH)
- [ ] Voucher: immutable setelah redeem, tidak gabung kasbon
- [ ] Diskon terjadwal: otomatis diterapkan, scope valid
- [ ] Notifikasi perubahan data: preferensi per user

## Setup Awal Owner (WAJIB JIKA MENYENTUH)
- [ ] Checklist 10 langkah tracking di `tenants.setup_step`
- [ ] Analytics `setup_checklist_step_completed` / `dismissed`

## Inventory / Modifier / CRM / Shift (WAJIB JIKA MENYENTUH FITUR BARU)
- [ ] Feature gate middleware `feature:{code}` diterapkan
- [ ] Kuota middleware `quota:{resource}` diterapkan (jika insert)
- [ ] Immutable data hanya INSERT
- [ ] Analytics event ditambahkan (jika perlu)
- [ ] Seeder reference + demo ditambahkan (idempotent)

## Checklist Umum
- [ ] Unit/feature test ada (coverage target §10.2)
- [ ] Partial test ada (jika AJAX)
- [ ] Data seeder ada (jika menambah fitur/tabel)
- [ ] Dokumen update (PRD/Design/Schema/Architecture/ADR)
- [ ] Tidak ada secret ter-commit
- [ ] Dites mobile/tablet/desktop
- [ ] 24 state di-handle
- [ ] Feature gating dites (21 fitur)
- [ ] Kuota dites
- [ ] Tenant isolation dites
- [ ] Compliance check
- [ ] Support check
- [ ] Retention job check
- [ ] Environment check (no PII di lokal/staging)
- [ ] Adapter pattern check
- [ ] Analytics consent check
- [ ] CI hijau (11 langkah)
