# RULE — KASIRKU v2.0

**Versi:** 2.0 | **Status:** Final | **Tanggal:** 2026-11-16

---

## Daftar Isi

```
BAGIAN A — RINGKASAN
§1   Overview & Prinsip
§2   48 Aturan Emas
§3   Enforcement Framework

BAGIAN B — WORKFLOW
§4   Workflow Wajib
§5   Branch Strategy
§6   PR & Review
§7   Commit Convention

BAGIAN C — STANDAR
§8   Standar Kode (Vanilla Frontend)
§9   Dokumentasi + ADR

BAGIAN D — KUALITAS
§10  Test Strategy
§11  Seeder
§12  Review Checklist

BAGIAN E — KEAMANAN & COMPLIANCE
§13  Keamanan
§14  Compliance
§15  Support

BAGIAN F — ATURAN KHUSUS (64, 14 Tema)
§16  Auth & Session (6)
§17  Subscription & Trial (6)
§18  Feature Gating & Kuota (4)
§19  Inventory & Immutable Data (4)
§20  Modifier, CRM & Shift (4)
§21  POS Baru — Hold, Kasbon, Kas, QRIS (6)
§22  Promosi & Notifikasi (4)
§23  Data & Isolation (4)
§24  Compliance & Support (6)
§25  Adapter, Guard & Analytics (8)
§26  Migration & Retention (4)
§27  Dev-First & Environment (4)
§28  Vanilla Frontend & Chart (4)
§29  Setup Awal Owner & Pencarian Global (2)

BAGIAN G — DOD & RILIS
§30  Definition of Done
§31  Rilis

BAGIAN H — VERIFIKASI
§32  Verifikasi Kelengkapan
§33  RQ-01 s.d. RQ-32
§34  Referensi
§35  Sign-off
§36  Ringkasan Eksekutif
```

---

## BAGIAN A — RINGKASAN

### §1. Overview & Prinsip

#### 1.1 Tentang Dokumen Ini

Rule Kasirku = aturan main mengikat untuk semua orang yang kerja di project ini:

- **48 Aturan Emas** — tidak boleh dilanggar dalam kondisi apa pun (§2)
- **Workflow wajib** — branch, PR, review, commit (§4–§7)
- **Standar kode & test** — FE vanilla, BE, DB, coverage, E2E, partial (§8–§12)
- **Keamanan & compliance** — guard rules, UU PDP, support access (§13–§15)
- **64 aturan khusus** — dikelompokkan 14 tema (§16–§29)
- **DoD & rilis** — kapan fitur selesai, kapan boleh deploy (§30–§31)
- **Enforcement** — siapa owner, audit, sanksi 3 level (§3)

#### 1.2 Konflik Dokumen

| Situasi | Yang Menang |
|---|---|
| Kebiasaan vs Rule | Rule |
| Rule vs dokumen spesifik (PRD/Design/Schema/Architecture) | Dokumen spesifik (Rule hanya mengatur proses, bukan konten) |
| Ketidakjelasan di Rule | Rule owner (founder + tech lead) putuskan |
| Perubahan besar | PR ke Rule + 2 approval |

#### 1.3 Snapshot — 12 Aturan Paling Kritis

| # | Aturan | Konsekuensi |
|---|---|---|
| 1 | Fitur belum selesai sebelum ada test + seeder | Tidak boleh masuk rilis |
| 2 | CI wajib hijau (11 langkah) sebelum merge | PR ditolak |
| 3 | Tidak ada secret di repo | Rotate + audit |
| 4 | Tidak ada push langsung ke main | Revert + teguran |
| 5 | Perubahan schema wajib update dokumen Schema di PR yang sama | PR ditolak |
| 6 | Test wajib pakai fake adapter — provider asli hanya smoke test | CI gagal |
| 7 | Adapter pattern wajib — dilarang hardcode provider | Revert + teguran Level 2 |
| 8 | Analytics tanpa consent = pelanggaran UU PDP | Revert + audit + teguran Level 3 |
| 9 | Tidak ada React/Vue/TypeScript/Tailwind/Inertia — frontend vanilla murni | PR ditolak |
| 10 | Data immutable (stock_movements, cash_movements, shift_reports, audit_logs, voucher_redemptions) tidak boleh UPDATE/DELETE | Revert + audit |
| 11 | Login POS wajib PIN-only — dilarang email/password untuk kasir | PR ditolak |
| 12 | Kasbon wajib validasi limit server-side — dilarang hanya frontend | Revert + teguran Level 2 |

#### 1.4 Referensi Kompetitor

| Kompetitor | Yang Diadopsi |
|---|---|
| Majoo | Git, code review, Agile/Scrum, CI/CD, AI pair programming dengan manual review, setup wizard |
| Moka POS | Automated pipeline, continuous testing, disciplined testing, PIN-based staff lock |
| Pawoon | Agile/Scrum, quality-driven (DRY, OOP), test plans, regression testing |
| Olsera | Agile workflow, hybrid testing (manual + automation), black box testing |
| PanelControl / ShelfOne | textContent bukan innerHTML, createElement + data-id, event delegation, native ES modules, render engine debounced |
| Square POS | Passcode-based staff login, quick amount buttons, switch employee |
| Toast | Employee PIN lock, split payment, hold order |

---

### §2. 48 Aturan Emas (Checklist)

Format: 48 aturan dalam 6 kategori. Tidak boleh dilanggar dalam kondisi apa pun.

#### 2.1 Fondasi (Aturan 1–10)

| # | Aturan |
|---|---|
| 1 | Fitur belum selesai sebelum ada test + seeder |
| 2 | Tidak ada secret di repo (API key, password, token) |
| 3 | Tidak ada push langsung ke main — semua via PR |
| 4 | CI wajib hijau — 11 langkah (§10.4) |
| 5 | Tidak ada fitur di luar PRD tanpa update PRD dulu |
| 6 | Perubahan schema wajib update dokumen Schema di PR yang sama |
| 7 | Perubahan visual signifikan wajib update dokumen Design |
| 8 | Keputusan teknis besar wajib ADR di dokumen Architecture |
| 9 | Jangan hardcode — warna/font/spacing dari token, config dari env, teks dari konstanta |
| 10 | Data user tidak boleh hilang — soft delete, konfirmasi eksplisit, jangan hard-delete transaksi |

#### 2.2 Project Spesifik (Aturan 11–22)

| # | Aturan |
|---|---|
| 11 | Session di Redis, metadata di DB — jangan simpan session data di DB |
| 12 | Trial = plan terpisah (is_trial_plan = TRUE, kuota unlimited) |
| 13 | Feature gating & kuota di middleware — bukan hanya UI; dites di CI |
| 14 | Tidak ada build APK / Gradle / Play Store |
| 15 | Compliance wajib sejak MVP — cookie, privacy, export, delete, retention |
| 16 | Support wajib sejak MVP — chat, status, KB, SLA, support access |
| 17 | Data residency — production Jakarta (Fase 9); lokal/staging tanpa PII |
| 18 | Retention job wajib — setiap tabel dengan retensi wajib punya scheduled command + terdaftar di retention_jobs |
| 19 | Runbook wajib — deploy, rollback, incident, restore, rotate secret di docs/runbooks/ |
| 20 | Third-party non-core — chat, status, KB, error tracking: fallback di MVP |
| 21 | Future tables tidak dibuat di MVP — migration baru saat fase dimulai |
| 22 | Migration sama di semua environment — tidak ada migration khusus |

#### 2.3 Dev-First & Fase (Aturan 23–30)

| # | Aturan |
|---|---|
| 23 | Dev-first, Setup-at-End — fitur lokal (F0–6) → staging (F7) → UAT (F8) → prod (F9) |
| 24 | MVP = Fase 0–8 — berakhir saat M10 (Staging UAT Complete) |
| 25 | Production-at-End — production live hanya Fase 9, setelah RG-1–RG-10 PASS |
| 26 | Tidak ada production readiness di MVP — Release Gate, Cutover, Rollback = Fase 9 |
| 27 | Environment indicator + demo mode = Fase 7 — safety-critical |
| 28 | Tidak ada deploy production sebelum Fase 9 |
| 29 | Cost target — MVP Rp0; production ≤Rp800rb/bln (Fase 9) |
| 30 | Verifikasi Stack Combo (Render+TiDB+Upstash+R2+Instatus) di Fase 7 — tanpa CC |

#### 2.4 Modern (Aturan 31–40)

| # | Aturan |
|---|---|
| 31 | Adapter pattern wajib — semua layanan eksternal via interface + config/env |
| 32 | Guard rules wajib — app gagal start production jika driver dev aktif |
| 33 | Analytics consent wajib — 3 kategori event (operational/marketing/support) |
| 34 | Expired route exemption eksplisit — 12 route dibuka, 12 diblokir |
| 35 | Coverage target — 100% endpoint Must, 100% middleware, 70% service |
| 36 | 50 E2E flow wajib — daftar konkret (§10.3) |
| 37 | IP analytics di-hash HMAC-SHA256 — tidak plain |
| 38 | Migration rollback test wajib di CI — migrate:rollback --step=80 |
| 39 | Test wajib fake adapter — provider asli hanya smoke test production-test |
| 40 | Rule owner = Founder + Tech Lead — perubahan Rule wajib 2 approval |

#### 2.5 Vanilla Frontend (Aturan 41–44)

| # | Aturan | Konsekuensi |
|---|---|---|
| 41 | Tidak ada React, Vue, Svelte, Angular, TypeScript, Tailwind, Inertia, Alpine, HTMX, Livewire | PR ditolak |
| 42 | Frontend = HTML + CSS + JS vanilla (ES modules) + Blade; tanpa bundler JS di production | Deploy = copy files; cache busting via query string |
| 43 | textContent/createElement wajib — dilarang innerHTML untuk data user | Revert + teguran Level 1 |
| 44 | Data immutable (stock_movements, cash_movements, shift_reports, audit_logs, voucher_redemptions, release_gates, rollback_events) hanya INSERT — tidak boleh UPDATE/DELETE | Revert + audit + teguran Level 2 |

#### 2.6 Fitur Baru POS & Auth (Aturan 45–48) 🆕

| # | Aturan | Konsekuensi |
|---|---|---|
| 45 | Login POS wajib PIN-only (pilih karyawan + PIN 6 digit) — dilarang email/password untuk kasir; device activation wajib sebelum login | PR ditolak |
| 46 | Kasbon wajib validasi limit server-side (customer.credit_limit - credit_balance >= grand_total) — dilarang hanya validasi frontend | Revert + teguran Level 2 |
| 47 | Hold order wajib resolve (resume/void) sebelum tutup kasir — sistem blokir tutup kasir jika ada hold aktif | CI test wajib |
| 48 | Kas keluar/masuk wajib alasan minimal 10 karakter — immutable; tercatat di shift report | CI test wajib |

---

### §3. Enforcement Framework

#### 3.1 Rule Owner

| Role | Tanggung Jawab |
|---|---|
| Founder | Rule owner final; override authority; monthly audit; compliance reviewer |
| Tech Lead | Rule co-owner; weekly audit; PR review; teguran Level 1–2 |
| Ops | Infrastructure compliance; environment separation |
| Support Staff | Support access reviewer |
| All developers | Follow rules; report violations |

#### 3.2 Perubahan Rule

- Perubahan Rule wajib PR + 2 approval (founder + tech lead)
- Perubahan yang berdampak ke dokumen lain → wajib review 4 dokumen di PR yang sama
- Perubahan breaking → wajib migration plan + komunikasi tim

#### 3.3 3 Level Sanksi

| Level | Violation | Sanksi | PIC |
|---|---|---|---|
| Level 1 | Violation ringan (miss test, seeder, doc, innerHTML untuk data user) | Slack DM dari tech lead + fix sebelum merge | Tech lead |
| Level 2 | Violation berulang (2×+) atau adapter/guard dilanggar atau kasbon tanpa server-side validation | Written warning via email + fix wajib | Tech lead |
| Level 3 | Violation berulang 3×+ atau kritis (secret leak, analytics tanpa consent, PII di non-prod, immutable data di-UPDATE, PIN kasir via email/password) | Escalate ke founder + audit + revert | Founder |

Untuk tim MVP ≤15 orang — Level 1 cukup untuk 90% kasus.

#### 3.4 Audit Schedule

| Jenis | Frekuensi | PIC |
|---|---|---|
| Weekly audit | Cek 5 PR terakhir comply | Tech lead |
| Monthly audit | Cek Rule compliance keseluruhan | Founder |
| Quarterly audit | Cek ADR, Rule, dokumen sync | Founder + Ops |

#### 3.5 Override Authority

- Founder berwenang override Rule dalam keadaan darurat
- Setiap override wajib: tertulis di ADR; update Rule di PR berikutnya; review kuartalan

#### 3.6 Pelaporan Violation

- Siapa pun boleh laporkan violation ke tech lead atau founder
- Violation ditangani dalam 1 hari kerja
- Sanksi sesuai §3.3

#### 3.7 Enforcement Khusus Vanilla

| Aturan | Enforcement |
|---|---|
| Tidak ada React/Vue/TS/Tailwind/Inertia | ESLint 9+ flat config rule — blokir import react, vue, @inertiajs/*, alpinejs; CI check package.json tidak ada dependency terlarang |
| Tanpa bundler JS | CI check — tidak ada npm run build untuk JS; hanya cp + minify CSS |
| textContent/createElement | ESLint rule — blokir innerHTML untuk data user; code review |
| Data immutable | Test — 10 immutable tables hanya INSERT; audit kuartalan |

#### 3.8 Enforcement Khusus Fitur Baru 🆕

| Aturan | Enforcement |
|---|---|
| Login POS PIN-only | Middleware device-active + pin-lockout; E2E test wajib |
| Kasbon server-side validation | Feature test wajib; CreditSaleService unit test |
| Hold order limit 10 | Middleware hold-limit; E2E test wajib |
| Kas keluar/masuk alasan wajib | FormRequest validation min:10; Feature test |
| QRIS konfirmasi manual | Audit qris_payment_confirmed; tidak ada auto-verify |
| Voucher immutable setelah redeem | Test voucher_redemptions INSERT only |
| Offline cart localStorage | E2E test koneksi putus; sync test |

---

## BAGIAN B — WORKFLOW

### §4. Workflow Wajib

#### 4.1 Alur Setiap Perubahan

```
Ambil task (dari roadmap / issue)
↓
Buat branch dari `develop` (kecuali hotfix — dari `main`)
↓
Kerjakan (kode + test + seeder + partial view jika AJAX)
↓
Update dokumen terkait (Schema / Design / PRD / ADR)
↓
Commit (Conventional Commits §7)
↓
Push + buka PR (template §6.1)
↓
CI hijau (11 langkah) + reviewer approve
↓
Merge ke `develop`
↓
(Merge ke `main` untuk staging deploy)
```

#### 4.2 Aturan Workflow

| Aturan | Detail |
|---|---|
| Branch dari develop | Bukan dari main (kecuali hotfix darurat) |
| Nama branch kebab-case | feat/pos-hold-order, bukan feat/login2 |
| Branch umur >3 hari | Bot GitHub warning (tidak blocking) |
| Hapus branch setelah merge | — |
| Wajib self-review sebelum push | Baca diff sendiri — cek innerHTML, secret, console.log |
| Wajib update dokumen di PR yang sama | Bukan "nanti di PR terpisah" |
| Wajib partial route test | Jika menambah endpoint AJAX |
| Wajib E2E Playwright | Jika mengubah flow kritis (50 flow §10.3) |

#### 4.3 Alur Khusus Fitur Baru

```
1. Update PRD (feature matrix + journey baru)
2. Update Schema (tabel baru / kolom baru)
3. Update Design (wireframe baru + komponen baru)
4. Update Architecture (ADR jika keputusan teknis besar)
5. Update Rule (aturan khusus jika perlu)
6. Implementasi:
   - Migration + model
   - Seeder (reference + demo)
   - Controller + service + form request
   - Blade view + vanilla JS module + CSS
   - Route (web + api + partial jika perlu)
   - Feature gate middleware + quota middleware
   - Analytics event (jika perlu)
7. Test:
   - Feature test (happy path + validasi + autorisasi + tenant isolation
     + feature gating + kuota + read-only)
   - Partial test (jika AJAX)
   - E2E Playwright (jika flow kritis)
   - Seeder test (idempotent)
8. Update Review Checklist §12
9. PR + CI hijau + reviewer approve
```

#### 4.4 Alur Hotfix Darurat

```
1. Konfirmasi kategori hotfix (staging down >30 menit / security vulnerability active)
2. Buat branch hotfix/<nama> dari `main`
3. Fix minimal — tidak boleh refactor / fitur baru
4. Test manual lokal + CI hijau (11 langkah)
5. Push + PR ke `main` (bukan `develop`)
6. Self-merge diperbolehkan DENGAN alasan tertulis di PR
7. Deploy staging via pipeline
8. Post-merge review wajib minimal 1 reviewer dalam 24 jam
9. Jika reviewer reject → revert
10. Merge balik ke `develop`
11. Update Changelog + notifikasi Slack #hotfix
```

Aturan hotfix vanilla: Jika hotfix menyentuh public/js/* atau resources/css/*, wajib cache busting (?v={version}).

---

### §5. Branch Strategy

#### 5.1 8 Jenis Branch

| Branch | Fungsi | Aturan | Fase |
|---|---|---|---|
| main | Staging-ready | Protected | MVP |
| develop | Integrasi wajib (tim >3) | Wajib — tim MVP ≤15 | MVP |
| feat/* | Fitur baru | Dari develop, merge via PR | MVP |
| fix/* | Bug fix | Dari develop | MVP |
| chore/* | Non-fitur | Dari develop | MVP |
| hotfix/* | Darurat | Dari main + post-merge review 24 jam | MVP |
| staging | Auto-deploy Render.com | Dari main, merge via PR | MVP—F7 |
| release/* | Persiapan production | Dari main | PROD |

#### 5.2 Reviewer Wajib per Kategori Branch

| Branch | Reviewer Wajib |
|---|---|
| feat/compliance-* | Founder (legal) |
| feat/support-* | Support staff |
| feat/integration-* | Cek secret + fallback + adapter pattern |
| feat/adapter-* | 2 reviewer (tech lead + Ops) |
| feat/guard-* | 2 reviewer |
| feat/analytics-* | Cek consent rules + no PII |
| feat/inventory-* | 2 reviewer (tech lead + backend lead) |
| feat/modifier-* | Tech lead |
| feat/crm-* | Tech lead |
| feat/shift-* | Tech lead |
| feat/pos-* | 2 reviewer (tech lead + frontend lead) |
| feat/vanilla-* | 2 reviewer (frontend lead + tech lead) — cek tidak ada React/Vue/TS/Tailwind |
| feat/auth-pos-* | 2 reviewer (tech lead + security lead) — cek PIN-only, lockout |
| feat/promo-* | Tech lead + backend lead |
| feat/hold-* | Tech lead |
| feat/kasbon-* | 2 reviewer (tech lead + backend lead) — cek server-side validation |
| feat/qris-* | Tech lead + backend lead |
| feat/setup-* | Tech lead |
| release/* [PROD] | Super Admin + Ops |

#### 5.3 Aturan Branch Khusus Vanilla

Branch yang menyentuh frontend vanilla wajib:

| Aturan | Cek |
|---|---|
| Tidak ada import React/Vue/Svelte/Angular | ESLint 9+ flat config rule blokir |
| Tidak ada TypeScript (.ts, .tsx) | CI check file |
| Tidak ada Tailwind class | Stylelint rule blokir utility class |
| Tidak ada innerHTML untuk data user | ESLint rule blokir |
| Tidak ada bundler JS di package.json | CI check dependency |
| ES modules (import/export) | Code review |
| CSS custom properties dari tokens.css | Stylelint check |
| Partial route test (jika AJAX) | Test coverage |
| Canvas API untuk chart (bukan library) | Code review |
| Touch events untuk gesture (bukan library) | Code review |

#### 5.4 Branch Naming Convention

| Tipe | Format | Contoh |
|---|---|---|
| Fitur | feat/<scope>-<deskripsi> | feat/pos-hold-order, feat/pos-kasbon |
| Bug fix | fix/<scope>-<deskripsi> | fix/inventory-stock-drift |
| Chore | chore/<deskripsi> | chore/update-eslint-flat-config |
| Hotfix | hotfix/<deskripsi> | hotfix/otp-fallback |
| Release | release/v<semver> | release/v2.0.0 |

---

### §6. PR & Review

#### 6.1 Template PR (Wajib)

```markdown
## Apa yang diubah
## Kenapa
## Cara test
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
- [ ] Checklist 10 langkah tracking di tenants.setup_step
- [ ] Analytics setup_checklist_step_completed / dismissed

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
```

#### 6.2 Aturan PR

| Aturan | Detail |
|---|---|
| Ukuran PR | Soft limit ~400 baris (di luar lockfile/generated) |
| Reviewer boleh approve >400 baris jika | Refactor mekanis, file generated, modul kohesif, alasan di PR |
| Reviewer minimum | 1; 2 untuk 18 kategori kritis (§6.3) |
| Self-merge | Dilarang — kecuali hotfix darurat |
| Draft PR | Boleh untuk diskusi awal |
| Wajib update PR description | Jika scope berubah mid-review |
| Wajib resolve comment | Semua komentar harus di-resolve sebelum merge |
| Wajib squash merge | Untuk fitur; merge commit untuk release |

#### 6.3 18 Kategori PR Kritis (Wajib 2 Reviewer)

| # | Kategori | Contoh |
|---|---|---|
| 1 | Auth & session | Login, session, MFA, PIN |
| 2 | Billing & subscription | Plan, trial, upgrade, expired |
| 3 | Schema & migration | Migration baru, ALTER TABLE |
| 4 | Compliance | Cookie consent, export, delete, retention |
| 5 | Support access | Impersonation, grant, banner |
| 6 | Data residency | Region config, backup |
| 7 | Environment separation | PII separation |
| 8 | Adapter pattern | Interface, config, binding |
| 9 | Guard rules | app:check-production |
| 10 | Analytics consent | Event marketing, IP hash |
| 11 | Expired middleware | subscription-active, route exemption |
| 12 | RBAC | Role column, permission matrix |
| 13 | Vanilla frontend | Blade + JS module + CSS murni; tidak ada React/Vue/TS/Tailwind |
| 14 | Login POS PIN-only | Device activation, PIN pad, lockout, switch user |
| 15 | Kasbon & limit | Credit sale, credit_limit, credit_balance, server-side validation |
| 16 | Kas keluar/masuk | cash_movements immutable, alasan wajib |
| 17 | Promosi | Voucher, scheduled discount, redeem |
| 18 | Hold order | Hold limit, stock tidak berkurang, resolve sebelum tutup kasir |

#### 6.4 Aturan Review

- Reviewer wajib beri alasan saat request changes
- Penulis wajib respons semua komentar
- Review fokus substansi — formatting ditangani linter
- Perbedaan pendapat → diskusi maks 1 hari kerja → founder putuskan → catat di ADR
- Jangan approve PR yang belum memenuhi checklist
- Reviewer wajib cek vanilla compliance (Aturan Emas #41–44) jika PR menyentuh frontend
- Reviewer wajib cek immutable data (Aturan Emas #44) — 10 immutable tables
- Reviewer wajib cek partial route jika PR menambah AJAX endpoint
- Reviewer wajib cek feature gate + kuota jika PR menambah fitur gated (21 fitur)
- Reviewer wajib cek login POS PIN-only jika PR menyentuh auth POS
- Reviewer wajib cek kasbon server-side validation jika PR menyentuh kasbon

#### 6.5 Review Checklist Vanilla (10 Cek)

| # | Cek | Cara |
|---|---|---|
| 1 | Tidak ada import React/from 'vue'/@inertiajs | ESLint 9+ flat config / cari di diff |
| 2 | Tidak ada file .ts/.tsx | Cari di diff |
| 3 | Tidak ada class Tailwind | Stylelint / cari di diff |
| 4 | Tidak ada innerHTML = data untuk data user | ESLint / cari di diff |
| 5 | CSS pakai var(--color-*) dari tokens.css (87 token) | Stylelint / cari di diff |
| 6 | JS pakai import/export ES modules | Cek type="module" |
| 7 | Partial route punya test (X-Partial: true) | Cek test file |
| 8 | Cache busting ?v={version} di <script>/<link> | Cek Blade layout |
| 9 | Canvas API untuk chart (bukan Chart.js/D3) | Cari di diff |
| 10 | Touch events native untuk gesture (bukan Hammer.js) | Cari di diff |

#### 6.6 Review Checklist Fitur Baru POS (12 Cek)

| # | Cek |
|---|---|
| 1 | Login POS: device activation wajib sebelum PIN |
| 2 | Login POS: PIN 6 digit Argon2id hash |
| 3 | Login POS: lockout 5 percobaan → 15 menit |
| 4 | Switch User: tanpa logout penuh; device session tetap |
| 5 | Hold order: maks 10 per outlet; stock tidak berkurang |
| 6 | Hold order: wajib resolve sebelum tutup kasir |
| 7 | Kasbon: customer.credit_limit - credit_balance >= grand_total (server-side) |
| 8 | Kasbon: stock berkurang saat order; loyalty saat lunas |
| 9 | Kas keluar/masuk: alasan min 10 char; immutable |
| 10 | QRIS statis: konfirmasi manual; tidak ada auto-verify |
| 11 | Refund parsial: qty tidak melebihi sisa; stock movement return per item |
| 12 | Offline cart: localStorage; auto-sync saat online |

---

### §7. Commit Convention

#### 7.1 Format

```
<type>(<scope>): <deskripsi singkat>

[optional body]

[optional footer: Refs #42]
```

#### 7.2 Type (9)

feat, fix, docs, style, refactor, perf, test, chore, ci

#### 7.3 Scope (55+)

- **Inti:** compliance, support, cookie, export, delete-account, integration, env, retention, sla, runbook, adr, data-residency, hosting, adapter, guard, analytics, expired, rbac, settings, mfa, onboarding, middleware, coverage, e2e, json-shape, migration
- **Fitur:** inventory, supplier, purchase-order, stock-movement, modifier, modifier-option, crm, customer, loyalty, shift, shift-report, tax-rate
- **Fitur Baru POS:** pos-login, pos-pin, switch-user, hold-order, kasbon, credit-sale, cash-movement, qris, split-equal, refund-partial, print-receipt, offline-cart, bluetooth-printer
- **Fitur Baru Lainnya:** promotion, voucher, scheduled-discount, notification, setup-checklist, global-search, cashier-settings, ewallet, product-report
- **Vanilla frontend:** blade, vanilla-js, css, partial, render-engine, dom-helper, tokens, chart, gesture, pin-pad
- **Production:** release, release-gate, cutover, deploy, rollback, monitoring

#### 7.4 Contoh Commit

**Backend/fitur:**
```
feat(adapter): tambah OtpSender interface + CompositeOtpSender
feat(guard): tambah 8 guard rules + app:check-production
fix(analytics): perbaiki IP hashing SHA-256 → HMAC-SHA256
```

**Fitur baru POS:**
```
feat(pos-login): login POS PIN-only + device activation + lockout
feat(hold-order): hold order maks 10 per outlet + resolve sebelum tutup kasir
feat(kasbon): credit sale + limit validation + loyalty saat lunas
feat(cash-movement): kas keluar/masuk immutable + alasan wajib
feat(qris): QRIS statis + konfirmasi manual + audit
feat(switch-user): switch user POS tanpa logout penuh
feat(offline-cart): cart localStorage + auto-sync saat online
feat(bluetooth-printer): Web Bluetooth API + ESC/POS + fallback CSS print
```

**Fitur baru lainnya:**
```
feat(promotion): voucher + redeem di POS + immutable setelah redeem
feat(scheduled-discount): diskon terjadwal otomatis + scope kategori/produk
feat(notification): notifikasi perubahan data + preferensi per user
feat(setup-checklist): Setup Awal Owner 10 langkah + tracking
feat(global-search): Ctrl+K + Eloquent LIKE + permission-based
feat(cashier-settings): kas awal default + batas diskon + izin kasir
feat(ewallet): QRIS QR upload + e-wallet custom
feat(product-report): laporan produk terlaris + filter periode/outlet
```

**Vanilla frontend:**
```
feat(vanilla-js): tambah render engine render() + renderNow()
feat(blade): tambah Blade component <x-pos.product-card>
feat(css): tambah tokens.css + components/button.css
feat(chart): Canvas API line/bar/donut/sparkline/heatmap
feat(gesture): swipe left/right + long press + pull down
feat(pin-pad): PIN pad 6 digit + dots + lockout UI
fix(vanilla-js): ganti innerHTML → textContent di renderCart (XSS prevention)
```

**Production [PROD]:**
```
feat(release): tambah ReleaseService + ReleaseGateService
feat(cutover): tambah CutoverService + 10 checklist item
feat(rollback): tambah RollbackService + re-auth + konfirmasi ROLLBACK
```

#### 7.5 Aturan Commit

1. Satu commit = satu perubahan logis
2. Jangan commit WIP / "fix typo" / "asdf" — squash sebelum merge
3. Referensikan issue/task (Refs #42)
4. Wajib deskriptif — reviewer harus paham dari judul commit saja
5. Wajib type + scope
6. Commit yang menyentuh frontend vanilla wajib scope vanilla-js/blade/css/partial
7. Commit yang menyentuh data immutable wajib scope inventory/shift/audit/cash-movement
8. Commit yang menyentuh production readiness wajib scope release/cutover/rollback
9. Commit yang menyentuh login POS wajib scope pos-login/pos-pin/switch-user
10. Commit yang menyentuh kasbon wajib scope kasbon/credit-sale

#### 7.6 12 Larangan Commit

1. ❌ Secret di commit
2. ❌ File .env (kecuali .env.example)
3. ❌ File node_modules/ / vendor/
4. ❌ File build (public/js/* hasil copy tanpa source)
5. ❌ console.log / dd() / dump()
6. ❌ Kode mati / comment-out
7. ❌ innerHTML untuk data user
8. ❌ Import React/Vue/TS/Tailwind
9. ❌ npm run build output untuk JS
10. ❌ Migration tanpa down()
11. ❌ Email/password di login POS kasir
12. ❌ Kasbon validation hanya di frontend

#### 7.7 Commit Message Length

- Subject line ≤72 karakter
- Body line ≤100 karakter
- Body opsional; jelaskan "kenapa", bukan "apa"
- Footer: Refs #42, Closes #42, BREAKING CHANGE: ...

#### 7.8 Commit yang Wajib Squash

| Jenis | Squash? |
|---|---|
| Feature branch | ✅ |
| Fix branch | ✅ |
| Chore branch | ✅ |
| Hotfix branch | ❌ (audit trail) |
| Release branch [PROD] | ❌ (audit trail) |
| Dokumentasi | ✅ |

---

## BAGIAN C — STANDAR

### §8. Standar Kode (Vanilla Frontend)

#### 8.1 Umum

| Aturan | Detail |
|---|---|
| Bahasa kode | Inggris (variabel, fungsi, komentar) |
| Teks UI | Bahasa Indonesia — jangan campur di kode; pakai konstanta |
| Penamaan | camelCase (variabel/fungsi), PascalCase (class/komponen Blade), UPPER_SNAKE_CASE (konstanta) |
| Fungsi | Kecil — satu tanggung jawab; >40 baris pertimbangkan pecah |
| Komentar | Menjelaskan "kenapa", bukan "apa" |
| Kode mati | Hapus — jangan comment-out |
| Debug | Tidak ada console.log/dd()/dump() |
| DRY | Jangan duplikasi logic |
| SRP | Satu file = satu tanggung jawab utama |
| AI-generated code | Wajib manual review — cek vanilla compliance, secret, immutable data |

#### 8.2 Frontend Vanilla (HTML + CSS + JS + Blade)

**Aturan Fundamental:**

| Aturan | Detail |
|---|---|
| Tidak ada framework | Larangan React, Vue, Svelte, Angular, Alpine, HTMX, Livewire, Inertia |
| Tidak ada TypeScript | File .js murni; tanpa .ts/.tsx |
| Tidak ada Tailwind | CSS murni dengan custom properties |
| Blade = server-side rendering | JS = progressive enhancement |
| ES modules | <script type="module">; import/export |
| CSS murni | Token di tokens.css (87 token); komponen di components/ |
| Tanpa bundler JS | Copy resources/js/* → public/js/* |
| Chart = Canvas API vanilla | Dilarang Chart.js, D3, Highcharts di MVP |
| Gesture = Touch events native | Dilarang Hammer.js, Swiper di MVP |
| Bluetooth = Web Bluetooth API | Dilarang library Bluetooth pihak ketiga |
| Offline = localStorage | Dilarang IndexedDB di MVP (post-MVP) |

**Blade (10 Aturan):**

1. Layout: @extends('layouts.app') / admin / pos / auth
2. Komponen: <x-button>, <x-modal>, <x-card>, <x-pos.product-card>, <x-immutable-badge>, <x-setup-checklist>, <x-pin-pad>, <x-qris-display>, <x-hold-badge>, <x-chart>
3. Partial: resources/views/partials/ — tanpa @extends
4. CSRF: @csrf di setiap form native
5. CSRF meta: <meta name="csrf-token" content="{{ $csrf_token }}">
6. Env meta: <meta name="app-env" content="{{ $app['env'] }}">
7. Escape: {{ $var }} untuk data user — cegah XSS
8. Raw HTML: {!! !!} hanya untuk HTML terpercaya
9. Asset cache busting: ?v={{ config('app.version') }}
10. Tanpa inline JS besar — logic di ES modules

**JavaScript Vanilla (24 Aturan):**

1. textContent/createElement untuk data user
2. innerHTML hanya untuk template statis
3. Event delegation (container, bukan per elemen)
4. Render engine render() debounced 80ms + renderNow() untuk kritis
5. Partial DOM update — hindari innerHTML seluruh halaman
6. Fetch API native; wrapper apiFetch() di core/fetch.js
7. CSRF di fetch: header X-CSRF-TOKEN
8. Partial request: header X-Partial: true
9. Analytics: navigator.sendBeacon + fallback fetch({ keepalive: true })
10. Error tracking: window.onerror + unhandledrejection
11. State: global state object + render()
12. Debounce untuk search/filter/resize
13. Sanitize input sebelum render (utils/sanitize.js)
14. Format uang: utils/currency.js — formatRupiah()
15. Format tanggal: utils/date.js — formatDate('id-ID')
16. Tidak ada document.write
17. Tidak ada eval()
18. Tidak ada new Function()
19. Tidak ada setTimeout untuk flow kritis — event-driven
20. Event delegation untuk grid POS
21. Canvas API untuk semua chart (drawLine, drawBar, drawDonut, drawSparkline, drawHeatmap)
22. Touch events native untuk gesture (swipe, long-press, pull-down)
23. Web Bluetooth API untuk printer thermal; fallback window.print()
24. localStorage untuk offline cart; key: kasirku_cart_{tenant_id}

**CSS (18 Aturan):**

1. Token wajib dari tokens.css (87 token) — tidak hardcode
2. Custom properties var(--color-primary)
3. Struktur: tokens → base → components → layouts → pages → states → utilities
4. Class naming BEM: .pos-product-card__title--active
5. Tidak ada utility class Tailwind
6. Dark mode post-MVP; token disiapkan via :root[data-theme="dark"]
7. Media query @media (min-width: 640px)/1024px
8. Print @media print untuk struk & invoice
9. Font size input ≥16px
10. Tap target ≥44×44px; POS ≥48×48px; PIN pad ≥64×64px
11. Kontras ≥4.5:1 (WCAG AA)
12. :focus-visible outline 2px
13. prefers-reduced-motion hormati
14. prefers-color-scheme post-MVP
15. Safe area env(safe-area-inset-*)
16. Animation 150–250ms; ease-out/ease-in; POS maks 100ms
17. Tidak ada !important kecuali override library
18. Stylelint color-no-hex: true — pakai token

**24 State Wajib per Halaman:**

loading, empty, error, success, offline, read-only, locked, exporting, deleting, demo-mode, guard-failed, immutable-confirm, low-stock-alert, quota-warning, quota-blocked, gate-pending/failed, cutover-in-progress, rollback-in-progress, production-blocked, re-auth-required, hold-active, kasbon-outstanding, connection-lost, printer-connected.

#### 8.3 Backend (Laravel 13 + PHP 8.4)

| Aturan | Detail |
|---|---|
| Controller | Tipis — logic bisnis di app/Services/ |
| Validasi | Form Request — jangan manual di controller |
| Query DB | Eloquent/Query Builder — tidak ada SQL string concat |
| Error | Typed exception |
| Transaksi multi-tabel | DB::transaction |
| Log | Jangan log password/token/PII |
| Tenant scoping | WHERE tenant_id = ? (global scope) |
| Feature gating | Middleware feature-gate (21 fitur) |
| Kuota | Middleware quota |
| Read-only | Middleware read-only |
| Session | Session facade |
| Trial plan | Saat register, subscription.plan_id = plan trial |
| Scheduled command | app/Console/Commands/ |
| Partial view | Return view('partials.xxx') tanpa layout + header X-Partial-Response: true |
| Blade view | Render + kirim data; logic di controller/service |
| JSON endpoint | Error code terstruktur |
| Adapter | Interface + config + binding; dilarang new ProviderName() |
| Guard | AppServiceProvider::boot() + app:check-production |
| Analytics | AnalyticsService::track() + consent + queue |
| PIN kasir | Argon2id hash; lockout 5× → 15 menit; middleware pin-lockout |
| Kasbon | Server-side validation di CreditSaleService; dilarang hanya frontend |
| Hold order | Middleware hold-limit; wajib resolve sebelum tutup kasir |
| Kas keluar/masuk | FormRequest reason min:10; immutable |
| QRIS | Konfirmasi manual; tidak ada auto-verify; audit wajib |
| Voucher | Immutable setelah redeem; tidak gabung kasbon |

**Service Wajib:** InventoryService, PurchaseOrderService, StockMovementService, ModifierService, LoyaltyService, ShiftReportService, TaxRateService, SplitBillService, SplitEqualService, PosPricingService, SubscriptionStateMachine, HoldOrderService, CreditSaleService, CashMovementService, QrisService, RefundPartialService, PrintReceiptService, VoucherService, ScheduledDiscountService, NotificationService, SetupChecklistService, GlobalSearchService.

#### 8.4 Database (MySQL 8.4)

| Aturan | Detail |
|---|---|
| Perubahan schema | Lewat migration |
| Migration immutable | Setelah staging |
| Foreign key | Wajib dideklarasikan di DB |
| Index | Wajib untuk kolom WHERE/JOIN/ORDER BY |
| Seed | Dipisah dari migration |
| Uang/persen | DECIMAL — jangan FLOAT |
| Snapshot transaksi | order_items.product_name, unit_price, cost_price, sku, modifiers |
| Session | Data di Redis; metadata di DB |
| CHECK constraint | Wajib untuk validasi bisnis (stock_qty >= 0, amount > 0) |
| Generated column | Untuk subscriptions.current_flag (1 active per tenant) |

**Konvensi:**
- Migration naming: YYYY_MM_DD_HHMMSS_create_{table}_table.php
- Charset: utf8mb4 + utf8mb4_unicode_ci
- UUID: CHAR(36) untuk tenants, releases, cutover_runs, deployments, rollback_events, analytics_events, export_jobs, support_access_sessions
- Kolom standar: id, created_at, updated_at (+deleted_at jika soft delete)
- Setiap migration down() lengkap
- 80 tabel MVP
- 27 seeder (8 reference + 16 demo + 3 production)
- 10 immutable tables: audit_logs, stock_movements, customer_loyalty_points, shift_reports, release_gates, cutover_runs, deployments, rollback_events, cash_movements, voucher_redemptions
- 2 circular dependency: tenants.owner_user_id, billing_invoices.subscription_id
- CI wajib test rollback: migrate:fresh → migrate:rollback --step=80 → migrate

#### 8.5 Linting & Formatting

| Aspek | Tool | Target | Catatan |
|---|---|---|---|
| PHP format | Laravel Pint | app/, config/, routes/, tests/ | — |
| PHP static | PHPStan level 6+ | app/ | — |
| JS lint | ESLint 9+ (flat config) | resources/js/ | eslint.config.js, bukan .eslintrc.json |
| CSS lint | Stylelint | resources/css/ | Token compliance |
| Format | Prettier | JS + CSS + Blade | --check di CI |
| TypeScript | ❌ | — | Tanpa tsc |
| Tailwind | ❌ | — | Tanpa tailwind.config.js |
| Pre-commit hook | Husky / CaptainHook | format + gitleaks + ESLint + Stylelint | Wajib |

**ESLint 9+ flat config (eslint.config.js):**

```javascript
import globals from "globals";

export default [
  {
    files: ["resources/js/**/*.js"],
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: "module",
      globals: {
        ...globals.browser,
        ...globals.es2022,
      },
    },
    rules: {
      "no-restricted-imports": ["error", {
        paths: [
          { name: "react", message: "Frontend is vanilla JS — no React" },
          { name: "react-dom", message: "Frontend is vanilla JS — no React" },
          { name: "vue", message: "Frontend is vanilla JS — no Vue" },
          { name: "@inertiajs/inertia", message: "No Inertia" },
          { name: "@inertiajs/vue3", message: "No Inertia" },
          { name: "alpinejs", message: "No Alpine" },
          { name: "htmx.org", message: "No HTMX" },
          { name: "svelte", message: "Frontend is vanilla JS — no Svelte" },
          { name: "@angular/core", message: "Frontend is vanilla JS — no Angular" },
          { name: "chart.js", message: "Use Canvas API vanilla" },
          { name: "d3", message: "Use Canvas API vanilla" },
          { name: "hammerjs", message: "Use native Touch events" },
        ],
      }],
      "no-restricted-syntax": ["error", {
        selector: "AssignmentExpression[left.property.name='innerHTML'][right.type!='Literal']",
        message: "Use textContent/createElement for user data — innerHTML only for trusted static HTML"
      }],
      "no-eval": "error",
      "no-implied-eval": "error",
      "no-new-func": "error",
      "no-console": ["warn", { allow: ["error", "warn"] }],
      "no-var": "error",
      "prefer-const": "warn",
    },
  },
];
```

**Stylelint config:**

```json
{
  "extends": "stylelint-config-standard",
  "rules": {
    "color-no-hex": true,
    "custom-property-pattern": "^[a-z][a-z0-9-]*$",
    "selector-class-pattern": "^[a-z][a-z0-9-]*(__[a-z0-9-]+)?(--[a-z0-9-]+)?$"
  }
}
```

**package.json:**

```json
{
  "devDependencies": {
    "eslint": "^9.0.0",
    "globals": "^15.0.0",
    "stylelint": "^16.0.0",
    "stylelint-config-standard": "^36.0.0",
    "prettier": "^3.0.0",
    "@playwright/test": "^1.40.0"
  },
  "scripts": {
    "lint:js": "eslint resources/js/",
    "lint:css": "stylelint 'resources/css/**/*.css'",
    "format:check": "prettier --check 'resources/**/*.{js,css}'",
    "test:e2e": "playwright test"
  }
}
```

---

### §9. Dokumentasi + ADR

#### 9.1 Dokumen Wajib Update

| Dokumen | Diubah saat |
|---|---|
| PRD | Perubahan scope/fitur |
| Design | Perubahan visual/komponen/token |
| Schema | Migration baru |
| Architecture | Keputusan teknis besar (+ ADR) |
| Roadmap | Urutan/prioritas fase berubah |
| Rule (dokumen ini) | Lewat PR + 2 approval |
| docs/runbooks/* | Deploy/rollback/incident berubah |

#### 9.2 Aturan Dokumentasi

1. Dokumen wajib di-update di PR yang sama dengan perubahan kode — bukan "nanti"
2. Dokumen disimpan di repo (/docs), bukan di Google Docs
3. Setiap dokumen punya baris versi & tanggal update di atas
4. Semua dokumen sinkron: PRD, Schema, Design, Architecture, Rule
5. Perubahan kecil boleh PR terpisah; perubahan besar wajib PR yang sama

#### 9.3 ADR Reference

Semua ADR di dokumen Architecture (88 ADR). Kategori:
- Fondasi & Stack
- Deployment & Ops
- Storage & Error
- Adapter & Pattern
- Data & Behavior
- Detail Implementasi
- Fase 9
- Vanilla Frontend
- Fitur Baru POS & Auth

Aturan: ADR baru diperbolehkan; ADR harus ada di PR yang sama; review kuartalan.

#### 9.4 Runbook Wajib

| Runbook | Kapan Wajib |
|---|---|
| deploy.md | Fase 7 |
| rollback.md | Fase 7 |
| incident-response.md | Fase 7 |
| restore-backup.md | Fase 7 |
| rotate-secret.md | Fase 7 |
| render-deploy.md | Fase 7 |
| expired-flow.md | Fase 7 |
| vanilla-asset-deploy.md | Fase 7 |
| production-cutover.md | PROD |
| release-gate-evaluation.md | PROD |
| offline-cart-recovery.md | Fase 7 |
| bluetooth-printer-troubleshoot.md | Fase 7 |

---

## BAGIAN D — KUALITAS

### §10. Test Strategy

#### 10.1 3 Prinsip

1. Test otomatis wajib pakai fake adapter
2. Provider asli hanya smoke test production-test [PROD]
3. Tenant isolation test wajib di CI setiap PR yang mengubah query domain

#### 10.2 Coverage Target

| Path | Target |
|---|---|
| app/Http/Controllers/Merchant/* | 100% |
| app/Http/Controllers/Admin/* | 100% |
| app/Http/Controllers/Auth/* | 100% |
| app/Http/Middleware/* | 100% |
| app/Services/* aggregate | 70% |
| routes/partials.php | 100% |
| resources/js/modules/pos/* | 60% (Vitest opsional) |
| resources/js/utils/* | 80% (Vitest opsional) |

Tool: Pest --coverage --min=100 + check-coverage.php.

#### 10.3 50 E2E Flow Wajib

**29 MVP:** Landing→register→dashboard, login dashboard, login POS, setup profil, setup POS, transaksi tunai, split bill per produk, void, refund, buka/tutup kasir+shift report, cetak bill+invoice, pilih paket+upgrade, trial habis→read-only, expired→modal blocking, export CSV, hapus akun, admin login+MFA, support access 30 menit, feature gating 403, kuota 403, produk CRUD, tim & akses, outlet CRUD+switch, device aktivasi+revoke, inventory PO lifecycle, stock adjustment, transfer stok, modifier group CRUD, POS modifier picker.

**21 Fitur Baru:**

| # | Flow | Kategori |
|---|---|---|
| 30 | Login POS PIN-only (device aktif) | Auth |
| 31 | Login POS PIN salah 5× → lockout | Auth |
| 32 | Switch User POS | Auth |
| 33 | Setup Awal Owner 10 langkah | Onboarding |
| 34 | Hold order + resume | POS |
| 35 | Hold order limit 10 | POS |
| 36 | Kasbon checkout + validasi limit | POS |
| 37 | Pembayaran kasbon (cicilan + lunas) | POS |
| 38 | Kas keluar + alasan wajib | POS |
| 39 | Kas masuk + shift report update | POS |
| 40 | QRIS statis + konfirmasi manual | POS |
| 41 | Split rata 3 orang | POS |
| 42 | Refund parsial per item | POS |
| 43 | Print ulang struk + max_print_count | POS |
| 44 | Koneksi putus → cart localStorage → sync | POS |
| 45 | Bluetooth printer pair + test print | Settings |
| 46 | Voucher create + redeem di POS | Promosi |
| 47 | Diskon terjadwal aktif otomatis | Promosi |
| 48 | Pencarian global (Ctrl+K) | UX |
| 49 | Notifikasi perubahan data | UX |
| 50 | Laporan produk terlaris | Laporan |

Tool: Playwright (Chromium + WebKit + Firefox). CI setiap PR + nightly.

#### 10.4 CI 11 Langkah Wajib

| # | Step | Tool |
|---|---|---|
| 1 | Lint PHP | Pint |
| 2 | Lint JS (vanilla) | ESLint 9+ flat config |
| 3 | Lint CSS | Stylelint (token compliance) |
| 4 | Format check | Prettier (JS + CSS + Blade) |
| 5 | Test backend | Pest |
| 6 | Test JS (opsional) | Vitest |
| 7 | Audit dependency | composer audit + npm audit |
| 8 | Secret scan | gitleaks |
| 9 | Image scan | Trivy |
| 10 | Guard rules dry-run | app:check-production |
| 11 | Coverage check | Pest --coverage --min=100 |

CI gagal jika ada 1 langkah gagal.

#### 10.5 Test Wajib per Fitur (36 Checklist)

- **Test dasar:** happy path, validasi input, autorisasi 403, tenant isolation, feature gating (21 fitur), kuota, read-only
- **Compliance & support:** compliance, support, retention, environment
- **Edge case & error:** edge case, error handling
- **UI & state:** environment indicator, demo mode, 24 state
- **Adapter & guard:** adapter pattern, guard rules
- **Analytics & expired:** analytics consent, expired route exemption
- **JSON & coverage:** JSON shape, coverage target
- **Vanilla frontend:** tidak ada React/Vue/TS/Tailwind, textContent/createElement, partial route test, CSS token compliance
- **Immutable data:** stock_movements INSERT only, cash_movements INSERT only, shift_reports INSERT only, customer_loyalty_points INSERT only, audit_logs INSERT only, voucher_redemptions INSERT only
- **Login POS:** PIN-only, device activation, lockout, switch user
- **Kasbon:** server-side validation, limit, loyalty saat lunas
- **Hold order:** limit 10, stock tidak berkurang, resolve sebelum tutup kasir
- **Kas keluar/masuk:** alasan wajib, immutable
- **QRIS:** konfirmasi manual, audit
- **Offline cart:** localStorage, auto-sync
- **Promosi:** voucher redeem, tidak gabung kasbon

#### 10.6 14 Larangan Test

1. ❌ Test yang memanggil provider asli
2. ❌ Test di-skip tanpa alasan tertulis
3. ❌ Test selalu lulus
4. ❌ Hapus test untuk CI hijau
5. ❌ Test mengandalkan DB dev
6. ❌ Test tidak membersihkan state
7. ❌ Test tidak memverifikasi audit log
8. ❌ Test menggunakan PII asli
9. ❌ Test tanpa coverage check
10. ❌ Test partial yang tidak memverifikasi header X-Partial-Response
11. ❌ Test E2E yang bergantung pada setTimeout
12. ❌ Test yang menggunakan innerHTML untuk setup DOM
13. ❌ Test login POS yang menggunakan email/password untuk kasir
14. ❌ Test kasbon yang hanya validasi di frontend

---

### §11. Seeder

#### 11.1 Kapan Seeder Wajib

Menambah tabel baru, kolom default, fitur butuh data referensi, data demo QA, backfill, release/gate/deployment demo [PROD], fitur gated baru, modifier/supplier/customer/voucher demo.

#### 11.2 8 Reference Seeder (Semua Env)

PlanSeeder (6 paket), FeatureSeeder (21 fitur), PlanFeatureSeeder (21×6), AddOnSeeder (3 add-on), AdminSeeder (1 super admin), TaxRateSeeder (PPN/PB1/SVC), RetentionJobSeeder (12 job), PaymentMethodSeeder (Tunai, Transfer, QRIS, Kasbon).

#### 11.3 16 Demo Seeder (Lokal/Staging Only)

DemoTenantSeeder, DemoProductSeeder, DemoOrderSeeder, DemoTableSeeder, CookieConsentSeeder, SupportTicketSeeder, ExportJobSeeder, AnalyticsEventSeeder, ModifierSeeder, SupplierSeeder, CustomerSeeder, VoucherSeeder, ScheduledDiscountSeeder, CashierSettingsSeeder, NotificationPreferencesSeeder, DemoCashMovementSeeder.

#### 11.4 3 Seeder Production [PROD]

ReleaseSeeder, ReleaseGateSeeder (10 RG per release), CutoverChecklistTemplateSeeder (10 item).

#### 11.5 Aturan Seeder

1. Idempotent
2. Dipisah dari migration
3. Bisa dipanggil ulang
4. Demo seeder tidak jalan di production
5. Tidak berisi PII
6. Nama deskriptif
7. FeatureSeeder wajib 21 fitur
8. PlanFeatureSeeder 21×6
9. RetentionJobSeeder 12 job
10. Modifier/Supplier/Customer/Voucher Seeder hanya lokal/staging
11. PaymentMethodSeeder wajib include QRIS dan Kasbon
12. CashierSettingsSeeder default per tenant

#### 11.6 8 Larangan Seeder

1. ❌ migrate:fresh di staging/production
2. ❌ Seeder demo di production
3. ❌ Seeder tidak idempotent
4. ❌ Reset gate status di production
5. ❌ Seeder yang mengubah data existing tanpa alasan
6. ❌ Seeder yang memanggil provider asli
7. ❌ Seeder yang berisi PII asli
8. ❌ Seeder voucher dengan usage_count > 0 di reference

---

### §12. Review Checklist

#### 12.1 Yang Wajib Dicek Reviewer (55 Item)

**Kode (9):** sesuai standar §8, test ada, seeder ada, dokumen update, tidak ada secret, tidak ada console.log/dd(), error handling, nama jelas, tidak ada dependensi native/APK.

**Vanilla Frontend (10):** tidak ada React/Vue/Inertia/Alpine, tidak ada .ts/.tsx, tidak ada Tailwind class, tidak ada innerHTML data user, CSS pakai token (87 token), ES modules, partial test, cache busting, Canvas API untuk chart, Touch events untuk gesture.

**Multi-tenant & Gating (4):** tenant scoping, feature gating (21 fitur), kuota, read-only.

**Database (5):** migration + update Schema, down() lengkap, charset utf8mb4_unicode_ci, UUID CHAR(36), immutable table INSERT only (10 tabel).

**UI (4):** dites mobile/tablet/desktop, 24 state, env indicator, demo mode.

**Compliance & Support (8):** cookie, export, delete, retention, support access dua sisi, data residency, export file, backup.

**Adapter, Guard, Analytics (7):** adapter pattern, guard rules, analytics consent, expired route exemption, JSON shape, middleware order, coverage target.

**Fitur Baru POS (8):** login POS PIN-only, hold order limit + resolve, kasbon server-side validation, kas keluar/masuk alasan wajib, QRIS konfirmasi manual, offline cart sync, Bluetooth fallback, voucher tidak gabung kasbon.

#### 12.2 Reviewer Khusus per Kategori (20)

| Kategori | Reviewer |
|---|---|
| Compliance | Founder (legal) |
| Support access | Support staff |
| Integrasi third-party | Cek secret + fallback + adapter |
| Retention job | Cek cleanup menghapus data |
| Environment | Cek data residency & PII separation |
| Adapter | 2 reviewer (tech lead + Ops) |
| Guard | 2 reviewer |
| Analytics | Cek consent rules + no PII |
| Vanilla frontend | 2 reviewer (frontend lead + tech lead) |
| Inventory | 2 reviewer (tech lead + backend lead) |
| Modifier | Tech lead |
| CRM | Tech lead |
| Shift | Tech lead |
| POS | 2 reviewer (tech lead + frontend lead) |
| Login POS PIN-only | 2 reviewer (tech lead + security lead) |
| Kasbon | 2 reviewer (tech lead + backend lead) |
| Kas keluar/masuk | Tech lead |
| Promosi | Tech lead + backend lead |
| Hold order | Tech lead |
| Release gate/cutover/rollback [PROD] | Super Admin + Ops |

#### 12.3 Review Flow

1. Reviewer baca PR description + diff
2. Cek checklist §12.1 (55 item)
3. Cek kategori khusus §12.2
4. Request changes dengan alasan / approve
5. Penulis resolve semua komentar
6. Merge (squash untuk fitur, merge commit untuk release)

---

## BAGIAN E — KEAMANAN & COMPLIANCE

### §13. Keamanan

#### 13.1 Aturan Umum (18)

1. Secret tidak pernah di-commit (gitleaks + pre-commit)
2. Dependency baru cek audit
3. Input user validasi server-side
4. Tenant isolation
5. SQL injection (Eloquent)
6. XSS (Blade escape + vanilla textContent)
7. CSRF (@csrf + X-CSRF-TOKEN)
8. Session security
9. Session revocation
10. Rate limit 15
11. File upload MIME + magic bytes
12. Log tidak mencatat PII
13. Dependency update bulanan
14. MFA admin wajib TOTP
15. Audit log aksi tulis
16. eval()/new Function()/document.write() dilarang
17. PIN kasir Argon2id hash; lockout 5× → 15 menit
18. Kasbon limit server-side validation; dilarang bypass dari frontend

#### 13.2 15 Rate Limit

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
| Frontend error report | 50/menit/session |
| Analytics track (client) | 100/menit/session |
| Partial route | 60/menit/user |
| PIN verification | 5 percobaan per PIN per 15 menit |
| Hold order | 20/menit/user |

#### 13.3 8 Guard Rules Production

1. OTP_DRIVER log/fake
2. MAIL_MAILER log/array
3. APP_DEBUG true
4. TELESCOPE_ENABLED true
5. SESSION_DRIVER database (wajib Redis)
6. QUEUE_CONNECTION sync
7. Kredensial provider kosong saat driver non-fake
8. APP_URL bukan HTTPS / ForceHttps tidak aktif

Implementasi: AppServiceProvider::boot() + app:check-production + log env_guard_failed + halaman 500 guard failure + CI dry-run.

#### 13.4 Analytics Security

- Event marketing wajib consent
- Operational/support selalu track
- Tanpa PII
- IP di-hash HMAC-SHA256
- Bot filter
- Client-side ke /app/api/analytics/track
- Error ke /app/api/errors/report
- Frontend tidak hardcode provider
- 54 events total (44 MVP + 10 Production)

---

### §14. Compliance

#### 14.1 Compliance Security (9)

1. Cookie consent (script hanya jika consent analytics)
2. Export data (local MVP → Cloudflare R2 staging, hapus 24 jam, rate limit 1×/24 jam)
3. Delete account (soft delete 30 hari, data finansial 5 tahun, revoke session, audit)
4. Support access (≤30 menit, banner dua sisi, read-only default, alasan + audit)
5. In-app chat (jangan PII, fallback email)
6. Data residency (lokal/staging tanpa PII)
7. PII masking (log tidak catat email/phone penuh)
8. Retention (cleanup job + test)
9. Third-party (secret manager, fallback, rate limit), runbook incident

#### 14.2 Environment Security (8)

1. Lokal/staging tanpa PII
2. Data residency lokal/staging region mana saja
3. Migration sama semua env
4. Export file: local (lokal), Cloudflare R2 (staging)
5. Backup lokal tidak perlu/staging opsional
6. Self-hosted tools tidak di MVP
7. Stack Combo free (Render+TiDB+Upstash+R2) hanya staging
8. Verifikasi Stack Combo Fase 7

---

### §15. Support

#### 15.1 Support Access Policy

Merchant grant, ≤30 menit, read-only default, write escalation ≤15 menit merchant approve, banner dua sisi, audit + alasan wajib.

#### 15.2 Channel Support

| Channel | MVP | Production [PROD] |
|---|---|---|
| In-app chat | mailto | Chatwoot |
| Email | ✅ | ✅ |
| WhatsApp | ✅ | ✅ |
| KB | Markdown | BookStack |
| Status | Instatus.com | Uptime Kuma |
| On-call | Slack | GoAlert |

#### 15.3 SLA

| Tingkat | First Response | Resolution |
|---|---|---|
| Critical | <1 jam | <4 jam |
| High | <4 jam | <1 hari kerja |
| Medium | <8 jam | <3 hari kerja |
| Low | <2 hari kerja | Backlog |

Uptime [PROD]: 99.5%. MVP: ad-hoc.

#### 15.4 Incident Management

P1 <15 menit, P2 <1 jam, P3 <8 jam, P4 <2 hari.

---

## BAGIAN F — ATURAN KHUSUS (64, 14 Tema)

### §16. Auth & Session (6)

#### 16.1 Session-Based Auth

Session data di Redis, metadata di DB (user_sessions, admin_sessions). CSRF wajib (@csrf + X-CSRF-TOKEN). Session regeneration setelah login. Revocation: hapus Redis + revoked_at + audit. Test wajib.

#### 16.2 MFA Admin

MFA = mfa_secret column encrypted. Tidak ada tabel admin_mfa. mfa_enabled_at = NULL → login ditolak. Recovery codes 10 single-use encrypted. Lockout 5 percobaan. Aktivasi → audit mfa.enabled.

#### 16.3 RBAC

Role column VARCHAR(30). 5 role admin: super_admin, support, finance, ops, partnership. 3 role merchant: owner, admin, cashier. Tanpa tabel roles/permissions/user_roles di MVP. Permission matrix hardcoded di middleware.

#### 16.4 Onboarding Step

users.onboarding_step (0–4). Middleware onboarded cek onboarding_step = 4 atau onboarding_completed_at != NULL. Step 3 opsional → onboarding_skipped_survey = TRUE. Setiap step completion → audit. Analytics onboarding_complete.

#### 16.5 Login POS PIN-Only 🆕

Login POS wajib PIN-only: pilih karyawan + PIN 6 digit. Device activation wajib sebelum login (pos_devices.device_session_token, expiry 30 hari). PIN hash Argon2id. Lockout: 5 percobaan gagal → pin_locked_until = now() + 15 menit. Middleware: device-active, pin-lockout. Session kasir tied to cashier_session (shift). Concurrent session: 1 kasir = 1 active POS session; login di device lain → session lama revoked. Audit: pos_login_pin, pos_login_pin_failed, pos_login_pin_locked.

#### 16.6 Switch User & Logout Behavior 🆕

Switch User: tanpa logout penuh; hanya perlu PIN kasir baru; device session tetap; cashier session lama tetap terbuka. Logout POS ≠ Tutup Kasir: logout revoke user session; tutup kasir close cashier_session. Logout Dashboard: revoke semua session user (POS + dashboard). Audit: switch_user_pos.

---

### §17. Subscription & Trial (6)

#### 17.1 Trial Plan (Bukan Status)

Trial = plan terpisah (is_trial_plan = TRUE). subscription.plan_id = plan trial; kuota unlimited 999; 21 fitur enabled. Saat pilih paket: plan_id di-update, status = 'active', trial_ends_at dipertahankan. ExpireTrials command setiap jam.

#### 17.2 Grace Period

7 hari setelah expired, grace_ends_at. grace_type = grace/expired. ExpireGracePeriods command setiap jam. Setelah grace → read-only; setelah 30 hari → archived. Test wajib.

#### 17.3 Trial Habis / Expired (Read-Only)

Trial habis tanpa paket → tenants.status = 'expired', read_only_at di-set. Middleware subscription-active blokir aksi tulis. Data tidak dihapus min 30 hari. Trial habis di tengah transaksi: grace 5 menit untuk selesaikan transaksi berjalan.

#### 17.4 Expired Route Exemption

**Dibuka (12):** dashboard, subscription, profile, data-privacy, cookie, help, /help, reports/*, logout, verify-email, login, register.

**Diblokir (12):** pos, products POST/PUT/DELETE, categories, modifiers, inventory, customers, tables, settings (selain profil/privacy/cookie), employees, invoices, outlets, devices.

Response: API 403 JSON {code: 'SUBSCRIPTION_EXPIRED', redirect}; web 302 redirect; frontend vanilla apiFetch() tangkap → modal blocking; analytics subscription_expired_blocked_action.

#### 17.5 Trial→Paid Belum Bayar = Read-Only

Trial habis + paket dipilih + belum bayar → pending_payment + read-only sampai Finance konfirmasi. Grace 7 hari. Test wajib.

#### 17.6 Settings POS = 7 Tabel + Tax Rates

tax_settings, service_charge_settings, receipt_settings, rounding_settings, cashier_settings, ewallet_settings, notification_preferences — bukan pos_settings. Setiap settings punya tenant_id + optional outlet_id. Cache 5 menit dengan observer invalidation. tax_rates tabel terpisah untuk CRUD tax rate; product_taxes sebagai relasi opsional.

---

### §18. Feature Gating & Kuota (4)

#### 18.1 Feature Gating (21 Fitur)

Setiap endpoint premium wajib middleware feature:{code}. 21 fitur gated: pos.transaction, pos.split_bill, pos.void_refund, pos.table_management, pos.credit_sale, invoice.digital, report.basic, report.advanced, outlet.multi, export.csv, payment.custom_method, tax.three_methods, service_charge.rounding, receipt.customization, inventory.tracking, product.modifiers, crm.loyalty, shift.report, promo.voucher, promo.scheduled, cashier.advanced_settings. FeatureSeeder 21, PlanFeatureSeeder 21×6. UI overlay lock + CTA. Prioritas: override > plan_features. Test wajib 403/200/override.

#### 18.2 Kuota

Insert pos_devices/users lewat middleware quota. Test blokir saat melebihi. UI quota meter. Kuota POS = total per tenant lintas outlet. Kuota karyawan & admin = total per tenant. max_employees = owner+admin+cashier; max_admins = owner+admin.

#### 18.3 Cache Invalidation Feature Overrides

Cache key feature_override:tenant:{tenant_id}, TTL 5 menit, invalidasi via TenantFeatureOverrideObserver (saved + deleted), register di EventServiceProvider::boot().

#### 18.4 Multi-Outlet

Fitur outlet.multi (Prime+). user_outlets penugasan user. Owner/admin tidak wajib record; cashier wajib minimal 1. Multi-outlet tidak menambah kuota POS.

---

### §19. Inventory & Immutable Data (4)

#### 19.1 Stock Movement = Ledger Immutable

stock_movements hanya INSERT — tidak boleh UPDATE/DELETE. Setiap perubahan stok = 1 row baru (sale, purchase, transfer_in, transfer_out, adjustment, return, waste). products.stock_qty = cache; rekonsiliasi via ReconcileStockJob harian. CHECK (stock_qty >= 0) di DB. Test wajib update/delete ditolak.

#### 19.2 PO Lifecycle

Status PO: draft → sent → partial → received atau cancelled. stock_movements dibuat saat penerimaan (received_qty update), bukan saat PO sent. Test wajib.

#### 19.3 Low Stock Alert

Notifikasi in-app saat stock_qty ≤ low_stock_alert. low_stock_alert per produk. Event inventory_low_stock_alert. Test wajib.

#### 19.4 Inventory Feature Gate

Route inventory wajib middleware feature:inventory.tracking. Basic → 403; Starter+ → 200. Test wajib.

---

### §20. Modifier, CRM & Shift (4)

#### 20.1 Modifier Picker

is_required = TRUE → POS wajib pilih minimal 1. max_selection batasi. min_selection validasi. Snapshot order_items.modifiers JSON. Test wajib required/max.

#### 20.2 Loyalty Redeem

Earn 1 poin/Rp1.000 (config) dari grand_total. Redeem 1 poin = Rp1 (config), maks 50% grand_total. customers.loyalty_points = cache. customer_loyalty_points immutable. Kasbon: poin earned saat kasbon dibayar lunas (bukan saat order dibuat). Test wajib redeem > 50% ditolak; phone duplikat 422.

#### 20.3 Shift Report Immutable

shift_reports dibuat otomatis saat cashier_sessions.closed_at. Immutable. 1 cashier session = 1 shift report (UNIQUE). Variance ≠ 0 → notes wajib. Print 58mm/80mm; export CSV gated. Total kas keluar dan kas masuk tercatat di shift report. Test wajib edit ditolak.

#### 20.4 Modifier/CRM/Shift Feature Gate

Modifier → feature:product.modifiers (Basic ❌, Starter+ ✅). CRM → feature:crm.loyalty (Basic/Starter ❌, Advance+ ✅). Shift → feature:shift.report (semua plan ✅). Kasbon → feature:pos.credit_sale (Basic ❌, Starter+ ✅). Test wajib.

---

### §21. POS Baru — Hold, Kasbon, Kas, QRIS (6) 🆕

#### 21.1 Hold Order

Hold order: maks 10 per outlet (middleware hold-limit). Stock TIDAK dikurangi saat hold (hanya saat order paid). Hold wajib di-resume atau di-void sebelum tutup kasir — sistem blokir tutup kasir jika ada hold aktif. Hold bisa di-resume oleh kasir sama ATAU kasir lain di outlet yang sama. Order status 'hold' di orders table. held_at, held_by_user_id tercatat. Audit: hold_order_created, hold_order_resumed, hold_order_voided. Test wajib: limit 10, stock tidak berkurang, blokir tutup kasir.

#### 21.2 Kasbon/Piutang

Kasbon hanya untuk customer terdaftar (punya record di customers). Limit kasbon: config per tenant (default Rp500.000); bisa per customer override (customers.credit_limit). Validasi server-side wajib: customer.credit_limit - customer.credit_balance >= grand_total. Order status 'credit'. Stock dikurangi saat order dibuat (bukan saat dibayar). Loyalty point earned saat kasbon dibayar lunas. Jatuh tempo: config per tenant (default 30 hari); notifikasi H-3. Kasbon TIDAK masuk cash sales di shift report (masuk "non-cash credit"). Hapus customer dengan credit_balance > 0 → ditolak. Voucher tidak bisa digabung dengan kasbon. Feature gated: pos.credit_sale (Basic ❌, Starter+ ✅). Audit: credit_sale_created, credit_payment_received. Test wajib: limit validation, stock berkurang, loyalty saat lunas, hapus customer ditolak.

#### 21.3 Kas Keluar/Masuk

Kas keluar/masuk hanya saat cashier_session aktif. Alasan wajib minimal 10 karakter (FormRequest validation). Nominal positif (CHECK amount > 0). cash_movements immutable (INSERT only). Kas keluar mengurangi expected_cash; kas masuk menambah expected_cash. Total kas keluar dan kas masuk tercatat di shift report. Config per tenant: cashier_settings.cash_movement_enabled (default TRUE). Audit: cash_movement_created. Test wajib: alasan wajib, immutable, shift report update.

#### 21.4 QRIS Statis

QRIS statis: merchant upload QR code di Settings → E-Wallet (ewallet_settings.qris_qr_url). Format: PNG/JPG, maks 2MB. Konfirmasi manual oleh kasir ("Sudah Diterima") — TIDAK ada verifikasi otomatis di MVP. Payment method type 'qris_static'. QR code tampil di modal saat kasir pilih metode QRIS. Jika merchant belum upload QR code → error "QR code belum diatur". Audit: qris_payment_confirmed. Test wajib: QR tampil, konfirmasi manual, audit tercatat.

#### 21.5 Split Rata

Split rata: grand_total / jumlah_orang, dibulatkan ke atas per orang. Sisa di orang terakhir (agar total = grand_total). Min 2 orang, max 20. Setiap orang bisa bayar dengan metode berbeda. Split rata + split per produk tidak bisa bersamaan. Kalkulasi di frontend; server validasi total. Audit: split_equal_created. Test wajib: kalkulasi benar, sisa di orang terakhir.

#### 21.6 Refund Parsial & Print Ulang

Refund parsial: hanya owner/admin (bukan kasir). Pilih item spesifik + qty (tidak boleh melebihi qty original - qty sudah refunded). refund_items tabel; qty per item. Stock movement return per item yang di-refund. Order status: partially_refunded → refunded (jika semua item). Audit: partial_refund_created. Print ulang: orders.print_count increment setiap print. Limit: receipt_settings.max_print_count (default 5; NULL = unlimited). Audit: receipt_reprinted. Test wajib: qty validation, stock movement return, max_print_count.

---

### §22. Promosi & Notifikasi (4) 🆕

#### 22.1 Voucher

Voucher: kode unik per tenant (UQ tenant_id, code). Tipe: percent/fixed. Min purchase, max_usage_total, max_usage_per_customer, expiry (starts_at, ends_at). Stackable default FALSE. Voucher immutable setelah redeem (voucher_redemptions INSERT only). Voucher tidak bisa digabung dengan kasbon. Usage count increment saat redeem. Feature gated: promo.voucher (Basic/Starter ❌, Advance+ ✅). Audit: voucher_created, voucher_redeemed. Test wajib: min purchase, max usage, expiry, tidak gabung kasbon, immutable setelah redeem.

#### 22.2 Diskon Terjadwal

Diskon terjadwal: otomatis diterapkan di POS saat periode aktif (tanpa input kasir). Scope: all/category/product (scope_type + scope_ids JSON). Periode: starts_at, ends_at, time_start, time_end, days_of_week JSON. Feature gated: promo.scheduled (Basic/Starter/Advance ❌, Prime+ ✅). Audit: scheduled_discount_applied. Test wajib: otomatis diterapkan, scope valid, periode valid.

#### 22.3 Notifikasi Perubahan Data

Notifikasi perubahan data: trigger INSERT/UPDATE/DELETE pada products, users, outlets, tax_rates, receipt_settings, tax_settings, payment_methods, customers. Penerima: owner + admin tenant. Channel: in-app notification (tabel notifications). Format: "[User X] mengubah [Entitas Y]: [detail perubahan]". Preferensi per user: notification_preferences tabel; default semua ON. Tidak bisa matikan notifikasi keamanan (login, perubahan password, perubahan role). Implementasi: Observer + NotificationService. Audit: notification_preference_updated. Test wajib: notifikasi terkirim, preferensi dihormati, keamanan tidak bisa dimatikan.

#### 22.4 Pencarian Global & Laporan Produk Terlaris

Pencarian global: Ctrl+K (desktop) atau tap ikon search (mobile). Scope: produk, customer, order, PO, supplier. Hasil terkelompok per kategori, maks 5 per kategori. Debounce 300ms. Permission-based (kasir tidak lihat PO/supplier). Rate limit 60/menit/user. Implementasi: Eloquent LIKE + index. Audit: global_search_used. Laporan produk terlaris: query agregasi order_items; filter periode/outlet/kategori; top N produk berdasarkan qty/revenue. Bar chart horizontal (Canvas API). Export CSV gated export.csv. Test wajib: permission-based, debounce, rate limit.

---

### §23. Data & Isolation (4)

#### 23.1 Multi-Tenant Isolation

Model domain global scope tenant_id. Query wajib tenant_id. Test CI tenant A tidak bisa akses tenant B. Jangan percaya tenant_id dari request.

#### 23.2 Audit Log

Aksi tulis admin (suspend, extend trial, override plan, feature flag, support access, session revoke) + user kritis (login, register, transaksi, refund, void, hold, kasbon, kas keluar/masuk, QRIS, voucher redeem) + compliance (export, delete, cookie) + aksi baru (inventory, modifier, CRM, shift, frontend error, env guard, OTP fallback, subscription expired, onboarding, setup checklist, switch user, print ulang, offline sync) wajib tercatat. Immutable — INSERT only. Test wajib.

#### 23.3 JSON Column Rules

10 JSON shape: tenants.social_media, products.metadata, orders.metadata, tax_settings.order_type_checklist, service_charge_settings.order_type_checklist, receipt_settings.display_options, analytics_events.properties, order_items.modifiers, scheduled_discounts.scope_ids, scheduled_discounts.days_of_week. Validasi via Cast atau Form Request. Tidak ada PII. Migration baru → update dokumen Schema.

#### 23.4 Data Residency

Lokal/staging region mana saja (tanpa PII). Cloudflare edge cache (tidak simpan PII). Third-party PII wajib Indonesia. Production DB/object/backup di Jakarta. Test: config env, tidak hardcode region.

---

### §24. Compliance & Support (6)

#### 24.1 Compliance (Cookie, Privacy, Export, Delete)

Cookie consent (banner, tidak pre-checked, analytics setelah consent, versioning, audit INSERT only). Privacy/Terms/Cookie di /privacy, /terms, /cookies, akses tanpa login. Consent checkbox register tidak pre-checked. Export async CSV (local MVP, Cloudflare R2 staging), rate limit 1×/24 jam, audit. Export types: produk, transaksi, customer, stock movement, shift report, cash movements, vouchers. Delete soft delete 30 hari, data finansial 5 tahun, revoke session, audit. Retention cleanup job + test. PII masking log.

#### 24.2 Consent Withdrawal

Data & Privacy → Cookie Settings → toggle OFF → catat versi baru INSERT only. Hapus akun = tarik consent keseluruhan. Withdrawal tidak hapus data wajib.

#### 24.3 Support

In-app chat mailto MVP; widget setelah consent necessary; jangan PII; disembunyikan saat POS aktif. Status page Instatus.com. KB Markdown. Support access ≤30 menit, banner dua sisi, read-only default, alasan + audit. Support ticket (Should). SLA staging ad-hoc, production 99.5%. Incident staging Slack, production GoAlert. On-call staging Slack, production GoAlert.

#### 24.4 Support Thread

support_ticket_messages — bukan single description. Minimal salah satu admin_user_id atau user_id. is_internal = TRUE = catatan internal. Retention ikut parent ticket.

#### 24.5 Export Data

Trigger user klik Export → job async ExportTenantData → file local/R2 → hapus 24 jam → rate limit 1×/24 jam → audit. PII: hanya data tenant. Export types: produk, transaksi, customer, stock movement, shift report, cash movements, vouchers.

#### 24.6 Delete Account

Konfirmasi ketik nama tenant + alasan. Backend: tenants.deleted_at, status = 'archived'. Logout semua session. PurgeDeletedTenants harian setelah 30 hari: arsip permanen, data finansial 5 tahun tetap, data lain dihapus. Sebelum 30 hari bisa dipulihkan via support. Audit semua langkah. Customer dengan credit_balance > 0 → ditolak hapus.

---

### §25. Adapter, Guard & Analytics (8)

#### 25.1 Adapter Pattern

Interface di app/Contracts/ (8 contracts). Config/env di config/services.php. Binding di AppServiceProvider::boot(). Dilarang new ProviderName(). Dilarang hardcode URL/API key. Test fake adapter. Label UI konsisten. Provider baru → contract + config + binding + test. 8 contracts: OtpSender, EmailSender, StorageDriver, ErrorReporter, SupportLinkProvider, KnowledgeBaseLinkProvider, StatusPageLinkProvider, PrinterDriver.

#### 25.2 Composite Adapter Fallback

Production OTP = CompositeOtpSender — Fonnte primary, Email fallback. Dev/staging = Log/Fake. Fallback → audit otp.fallback. Gagal → audit otp.fallback_failed + Slack P2.

#### 25.3 Guard Rules

8 guard rules di AppServiceProvider::boot() + app:check-production. Rule #7 cek nilai env. Rule #8 HTTPS + ForceHttps + Nginx. GUARD_RULES_ENFORCED=true production. Guard failure → log env_guard_failed. Halaman error 500. CI dry-run. Pipeline production gate.

#### 25.4 Analytics Consent Rules

54 events wajib (44 MVP + 10 Production). 3 kategori: Operational (selalu track), Marketing (wajib cookie consent analytics=true), Support/Audit (legitimate interest). AnalyticsService::track() cek ConsentChecker. Queue analytics, retry 3x, timeout 30s. Tidak ada PII. IP hashed HMAC-SHA256. Bot filter landing/cta/register. Guest session_id tracked. Retention 90 hari + cleanup + partition. Client-side navigator.sendBeacon + consent gate.

#### 25.5 IP Hashing

HMAC-SHA256 dengan config('app.key'). VARCHAR(64) hex. Tidak bisa reverse. Geolocation via header CF-IPCountry. Tidak ada IP plain di analytics_events.

#### 25.6 Bot Filtering

Event landing/cta/register filter bot. Event dengan user_id tidak filter. Regex bot standar.

#### 25.7 TrackEvent Job Config

Queue analytics dedicated. Retry 3x backoff (10s, 60s, 300s). Timeout 30 detik. Failed job → audit analytics.fail (tidak blocking). Worker dedicated.

#### 25.8 Analytics Dashboard Metrics (Should)

Landing→register, registration completion, time-to-first-transaction, trial→paid, feature blocking per fitur (21), quota blocking, export success, active tenants, environment breakdown, support access count, inventory usage, CRM loyalty usage, modifier usage, shift report usage, hold order usage, kasbon usage + repayment rate, QRIS usage, kas keluar/masuk usage, voucher usage, setup checklist completion, POS login time, pencarian global usage.

---

### §26. Migration & Retention (4)

#### 26.1 Migration Rules

Naming YYYY_MM_DD_HHMMSS_create_{table}_table.php. Charset utf8mb4_unicode_ci. UUID CHAR(36) untuk tenant/release/cutover/deployment/rollback/analytics_events/export_jobs/support_access_sessions. Kolom standar id, created_at, updated_at (+deleted_at). down() lengkap. Sama di semua env. CI test rollback --step=80. 80 tabel MVP, 2 circular dependency via ALTER TABLE. CHECK constraint untuk validasi bisnis. Generated column untuk subscriptions.current_flag.

#### 26.2 Scheduled Command & Queue (18)

ExpireTrials, ExpireGracePeriods, PurgeDeletedTenants, CleanupExpiredExports, CleanupExpiredSessions, CleanupExpiredOtp, CleanupEmailVerifications, CleanupExpiredInvitations, CleanupAuditLogs, CleanupCookieConsents, CleanupExportJobs, CleanupClosedTickets, CleanupAnalyticsEvents, ReconcileStockJob, ReconcileCustomerStatsJob, CreateAnalyticsPartition, CheckCreditDueDate, ApplyScheduledDiscounts. Test wajib per command.

#### 26.3 Retention Jobs Registry (12)

Setiap retention job di retention_jobs (RetentionJobSeeder). Setiap eksekusi update last_run_at, last_run_status, last_run_affected. RG-5 check.

12 job: CleanupAnalyticsEvents (90 hari), CleanupAuditLogs (90 hari), CleanupUserSessions (30 hari revoked), CleanupAdminSessions (30 hari revoked), CleanupOtpVerifications (24 jam expired), CleanupEmailVerifications (7 hari), CleanupExportJobs (24 jam file), CleanupCookieConsents (2 tahun), ArchiveExpiredTenants (30 hari), CleanupSupportTickets (2 tahun closed), CleanupVoucherRedemptions (5 tahun), CleanupCashMovements (5 tahun).

#### 26.4 Future Tables

Tidak dibuat di MVP. Wajib update dokumen Schema di PR yang sama. Wajib update PRD jika scope berubah. Dependency dihormati (offline_transactions butuh PWA Fase 10). Tabel TBD (warehouses, journal_entries, chart_of_accounts) didetailkan Fase 14. Tidak boleh buat tabel future di migration MVP.

---

### §27. Dev-First & Environment (4)

#### 27.1 Responsive Web (Tanpa APK)

Tidak ada build APK/Gradle/Play Store. Mobile dites dulu. Tidak ada fitur native. Upload foto <input type="file">. Notifikasi in-app via polling. Print via window.print() + @media print + Web Bluetooth API. navigator.onLine deteksi offline. PWA post-MVP. Frontend vanilla — tanpa React/Vue/TS/Tailwind/Inertia.

#### 27.2 Print & Receipt

CSS @media print wajib untuk struk & invoice. Web Bluetooth API untuk thermal printer (Chrome/Edge only); fallback window.print(). Sembunyikan navigasi/sidebar/tombol. Ukuran 58mm/80mm — uji browser print preview. Test manual wajib Chrome. Modifier tercetak (snapshot order_items.modifiers). Shift report printable. Print ulang: max_print_count dari receipt_settings; orders.print_count increment.

#### 27.3 Dev-First, Setup-at-End

Seluruh fitur bisnis di lokal (Fase 0–6). Setup dev & staging Fase 7 — verifikasi Stack Combo (Render+TiDB+Upstash+R2+Instatus), env indicator, demo mode, CI/CD staging, Sentry, health check, runbook, first staging deploy. Staging QA + UAT Fase 8. MVP berakhir M10. Tidak ada production readiness di MVP. Production readiness & go-live Fase 9 (9A, 9B, 9C).

#### 27.4 Environment Indicator + Demo Mode

Dibangun Fase 7. Blade global variables $app['env'], $app['deploy_env'], $app['version'], $app['git_commit'], $app['is_production'], $app['demo_mode'], $app['show_env_badge']. Badge non-production (DEV/STAGING). Badge tidak dismiss; role="status" + aria-label. Z-index --z-env-badge (750). Demo mode banner lokal/staging dismissible 24 jam; tidak di production. Test wajib. Production badge final Fase 9. Vanilla implementation: <x-env-badge> + CSS components/badge.css.

---

### §28. Vanilla Frontend & Chart (4) 🆕

#### 28.1 Chart = Canvas API Vanilla

Semua chart wajib Canvas API vanilla — dilarang Chart.js, D3, Highcharts, ApexCharts di MVP. 6 chart types: drawLine(), drawBar(), drawDonut(), drawSparkline(), drawHeatmap(), drawProgressRing(). Semua warna dari --chart-color-* token. Animasi draw 600ms ease-out. Hover: highlight + tooltip. Responsive: resize otomatis. Accessibility: role="img" + aria-label. Data refresh: polling 30 detik untuk real-time. Export: PNG download.

#### 28.2 Gesture = Touch Events Native

Semua gesture wajib Touch events native — dilarang Hammer.js, Swiper di MVP. 9 gesture: swipe left/right, long press, pull down, double tap, pinch, swipe down, edge swipe. Threshold: swipe 50px, long press 500ms. Haptic feedback: navigator.vibrate(10) untuk mobile. Semua gesture harus punya alternatif button/tap (accessibility). prefers-reduced-motion: disable semua gesture animation.

#### 28.3 Offline Cart = localStorage

Cart tersimpan di localStorage saat koneksi putus. Key: kasirku_cart_{tenant_id}. Value: JSON { cart, saved_at }. Auto-sync saat koneksi pulih (navigator.onLine = true). Konflik: jika cart di server berbeda, tampilkan dialog resolusi. Sync gagal 3×: toast error + tombol "Coba Lagi". Browser ditutup saat offline: cart tetap ada saat buka lagi. Analytics: offline_cart_synced.

#### 28.4 Bluetooth Printer = Web Bluetooth API

Web Bluetooth API untuk thermal printer (Chrome 85+, Edge 85+). Firefox/Safari tidak support → fallback window.print(). ESC/POS command builder. Pairing: Settings → Printer → Scan → Pair → Test Print. Auto-print opsional. Error handling: toast + fallback print dialog. PrinterDriver contract di backend (untuk fallback logic). Analytics: bluetooth_printer_connected.

---

### §29. Setup Awal Owner & Pencarian Global (2) 🆕

#### 29.1 Setup Awal Owner

Setup Awal Owner: 10 langkah checklist setelah onboarding 4 langkah. Tracking di tenants.setup_step (0-10), setup_completed_at, setup_dismissed_at. Checklist tidak blocking — owner bisa langsung pakai POS tanpa menyelesaikan semua. Setiap langkah selesai → toast + analytics setup_checklist_step_completed. Semua selesai → toast "Setup selesai!" + checklist hilang. Dismiss → analytics setup_checklist_dismissed. 10 langkah: Outlet, Produk, Informasi Karyawan, Struk/Biaya/Pajak, Pengaturan Kasir, Pengaturan Perangkat, Pengaturan E-Wallet, Data Pelanggan, Promosi, Notifikasi. SetupChecklistService.completeStep() + dismiss().

#### 29.2 Pencarian Global

Pencarian global: Ctrl+K (desktop) atau tap ikon search (mobile). Scope: produk, customer, order, PO, supplier. Hasil terkelompok per kategori, maks 5 per kategori. Debounce 300ms. Permission-based: kasir tidak lihat PO/supplier. Rate limit 60/menit/user. Keyboard navigation (↑↓ Enter Esc). Implementasi: Eloquent LIKE + index. GlobalSearchService.search(). Partial route: /app/api/partials/search/results. Analytics: global_search_used.

---

## BAGIAN G — DOD & RILIS

### §30. Definition of Done

#### 30.1 DoD MVP — Fitur Selesai (30 Item)

**Kode & Test (7):** merge ke main via PR approved, unit/feature test (coverage §10.2), seeder tersedia, E2E test flow kritis (50), test manual lokal (mobile+tablet+desktop), CI hijau 11 langkah, partial test (jika AJAX).

**Vanilla Frontend (6):** tidak ada React/Vue/TS/Tailwind/Inertia, Blade + vanilla JS ES module + CSS murni, textContent/createElement untuk data user, cache busting ?v={version}, Canvas API untuk chart, Touch events untuk gesture.

**Gating & Isolation (5):** feature gating dites (21), kuota dites, tenant isolation dites, read-only dites, trial plan dites.

**Compliance & Support (5):** compliance dites, support dites, retention job dites, data residency dites, environment separation dites.

**Adapter, Guard, Analytics (4):** adapter pattern diterapkan, guard rules tidak dilanggar, analytics consent diterapkan, expired route exemption dipatuhi.

**Fitur Baru POS (3):** login POS PIN-only + lockout + switch user, hold order limit + resolve + stock tidak berkurang, kasbon server-side validation + loyalty saat lunas.

#### 30.2 DoD Fase 7 (9 Item)

1. Stack Combo (Render+TiDB+Upstash+R2+Instatus) terverifikasi tanpa CC
2. Staging live
3. Env indicator + demo mode live
4. CI/CD staging pipeline berfungsi
5. Sentry setup (backend + frontend vanilla)
6. Health check /up 4 komponen
7. Runbook final (12 file)
8. First staging deploy sukses
9. Smoke test staging lulus

#### 30.3 DoD Fase 8 (9 Item)

1. E2E test staging lulus (50 flow)
2. Manual QA matrix staging lulus
3. Security audit bersih
4. Performance target tercapai
5. Compliance final sign-off
6. Support final sign-off
7. UAT stakeholder lulus
8. Final staging deploy sukses
9. M10 Staging UAT Complete tercapai

#### 30.4 DoD Fase 9 [PROD] (12 Item)

1. RG-1–RG-10 PASS
2. Cutover complete #1–#10
3. Deploy history tercatat
4. Rollback plan siap
5. Env indicator final
6. Audit wajib
7. No PII di production readiness tables
8. Backup production readiness tables
9. Production live + smoke test 20/20
10. Incident simulation lulus
11. 24 jam monitoring bersih
12. Production success criteria 14 item terpenuhi

Aturan: Fitur belum memenuhi DoD = belum selesai. Tidak boleh masuk rilis staging, tidak boleh dicentang di roadmap.

---

### §31. Rilis

#### 31.1 Rilis Staging [MVP — FASE 7–8]

**Aturan umum:** rilis dari main lolos CI 11 langkah, tag semver, changelog update, rollback plan, migration dengan backup pre-migration, health check /up setelah deploy (auto rollback jika gagal), hindari rilis Jumat sore / menjelang libur panjang.

**Checklist 25 item:** task DoD, test manual lokal, compliance test, support test, env separation test, export file env-aware, adapter pattern verified, guard rules dry-run, analytics consent verified, expired route exemption verified, migration rollback test lulus (80 tabel), changelog update, rollback plan siap, health check /up berfungsi, backup pre-migration, queue worker & scheduler berjalan, retention job terdaftar di schedule:list (12 job), tidak ada PII asli di staging, tidak ada error di log 15 menit pertama, env indicator + demo mode tampil, vanilla assets ter-deploy benar (copy + cache busting), partial route test lulus, login POS PIN-only berfungsi, hold order limit berfungsi, kasbon validation berfungsi.

#### 31.2 Rilis Production [PROD]

**Aturan kunci:** Production live hanya setelah RG-1–RG-10 PASS; gate FAIL/PENDING → cutover blocked; backup pre-migration wajib; cutover lock (Redis TTL 1 jam); deploy lock (Redis TTL 30 menit); manual approval GitHub Environments; health check + smoke test wajib; rollback confirmation ROLLBACK + re-auth; deploy history immutable; post-mortem wajib untuk P1/P2.

**Checklist 18 item:** release tag v* dibuat, RG-1–RG-10 PASS, backup pre-migration + restore test, cutover lock, deploy lock, docker image tag v*, migration + seed lulus (80 tabel), cache commands lulus, vanilla assets ter-deploy benar, health check /up 200, smoke test 20/20, SPF/DKIM/DMARC verified, monitoring + on-call aktif, cutover #1–#10 completed, cutover_runs.status = 'completed', releases.status = 'released', 24 jam monitoring aktif, notifikasi Slack + status page.

#### 31.3 Hotfix Darurat

**Definisi:** Staging down >30 menit ATAU security vulnerability active ATAU production incident P1.

**Aturan:** Branch hotfix/<nama> dari main, CI 11 langkah hijau, self-merge dengan alasan tertulis, post-merge review 24 jam, jika reject revert, update changelog + Slack #hotfix, merge balik ke develop, cache busting vanilla assets.

**Bukan hotfix:** bug minor/cosmetic, fitur baru, refactor, typo, performance non-kritis.

#### 31.4 Komunikasi & Kolaborasi

Perubahan besar diskusi dulu; blocker >1 hari lapor; task luar scope backlog; keputusan di ADR; pair programming untuk fitur kritis; daily sync 15 menit; incident P1/P2 update status page + notifikasi; compliance diskusi founder; support diskusi Support; third-party diskusi tim (biaya, SLA, exit plan); environment diskusi Ops; vanilla frontend diskusi frontend lead + tech lead; fitur POS baru diskusi tech lead + backend lead.

Eskalasi disagreement: diskusi maks 1 hari kerja; founder putuskan; keputusan di ADR; update Rule via PR.

---

## BAGIAN H — VERIFIKASI

### §32. Verifikasi Kelengkapan

#### 32.1 Checklist Section

Semua 36 section terisi. Total 100% terisi.

#### 32.2 Checklist Sinkronisasi

| Dokumen | Section | Status |
|---|---|---|
| PRD | 19 journey, 21 fitur gated, 80 tabel, 118 PD | ✅ |
| Schema | 80 tabel, 27 seeder, 10 immutable tables, 2 circular dependency | ✅ |
| Design | 104 wireframe, 21 fitur gated, 87 token CSS, 24 state | ✅ |
| Architecture | 28 prinsip, 88 ADR, 68 aturan, vanilla frontend | ✅ |
| Rule (dokumen ini) | 48 aturan emas, 64 aturan khusus, 14 tema | ✅ |

#### 32.3 Checklist Vanilla Stack

- Tidak ada React/Vue/TypeScript/Tailwind/Inertia/Alpine/HTMX/Livewire di package.json
- JS <script type="module">
- CSS custom properties (87 token)
- Blade SSR
- Partial route
- Render engine render() + renderNow()
- Client-side error tracking
- ESLint 9+ flat config
- Stylelint config
- Canvas API untuk chart
- Touch events untuk gesture
- Web Bluetooth untuk printer
- localStorage untuk offline cart

✅

#### 32.4 Checklist Staging (Tanpa CC)

| Aspek | Status |
|---|---|
| Render.com free tier (tanpa CC) | ✅ |
| TiDB Cloud Serverless (tanpa CC) | ✅ |
| Upstash free (tanpa CC) | ✅ |
| Cloudflare R2 free 10 GB (tanpa CC) | ✅ |
| Instatus.com free (tanpa CC) | ✅ |
| Sentry free (tanpa CC) | ✅ |
| Total biaya staging | Rp0 |

#### 32.5 Verifikasi 100% Terisi

| Aspek | Jumlah | Status |
|---|---|---|
| Aturan Emas | 48/48 | ✅ |
| Aturan Khusus | 64/64 | ✅ |
| Tema | 14/14 | ✅ |
| Rate limit | 15/15 | ✅ |
| Guard rules | 8/8 | ✅ |
| CI langkah | 11/11 | ✅ |
| E2E flow | 50/50 | ✅ |
| Test wajib per fitur | 36/36 | ✅ |
| Larangan test | 14/14 | ✅ |
| Seeder | 27/27 | ✅ |
| Review checklist | 55/55 | ✅ |
| Retention job | 12/12 | ✅ |
| Scheduled command | 18/18 | ✅ |
| ADR reference | 88/88 | ✅ |
| Runbook | 12/12 | ✅ |
| DoD item | 60/60 | ✅ |
| RQ | 32/32 | ✅ |
| Section | 36/36 | ✅ |

Verdict: Rule v2.0 100% terisi — tidak ada placeholder, tidak ada TBD.

---

### §33. RQ-01 s.d. RQ-32 (Semua Terjawab)

| ID | Pertanyaan | Jawaban Final |
|---|---|---|
| RQ-01 | Wajib Figma? | Tidak. Wireframe Design baseline. |
| RQ-02 | Wajib React/Inertia/TypeScript? | Tidak. Frontend vanilla (HTML + CSS + JS ES modules) + Blade. |
| RQ-03 | Wajib Tailwind? | Tidak. CSS murni dengan custom properties (87 token). |
| RQ-04 | Coverage minimum? | 100% endpoint Must, 100% middleware, 70% service, 100% partial route, 60–80% JS opsional. |
| RQ-05 | E2E flow minimum? | 50 flow (29 MVP + 21 fitur baru). |
| RQ-06 | Seeder minimum? | 27 seeder (8 reference + 16 demo + 3 production). |
| RQ-07 | Retention job minimum? | 12 job terdaftar di retention_jobs. |
| RQ-08 | Fitur gated baru? | FeatureSeeder (21) + PlanFeatureSeeder (21×6); middleware; overlay lock. |
| RQ-09 | Data immutable? | 10 tabel: audit_logs, stock_movements, customer_loyalty_points, shift_reports, release_gates, cutover_runs, deployments, rollback_events, cash_movements, voucher_redemptions — INSERT only; test; reviewer cek. |
| RQ-10 | XSS di vanilla JS? | textContent/createElement untuk data user; innerHTML hanya statis; ESLint 9+ blokir. |
| RQ-11 | CSRF di vanilla JS? | @csrf di form; X-CSRF-TOKEN header di fetch; apiFetch(). |
| RQ-12 | Partial update DOM? | Partial route (X-Partial: true) → fragment Blade → innerHTML = html (fragment terpercaya). |
| RQ-13 | Client-side error tracking? | window.onerror + unhandledrejection → /app/api/errors/report → ErrorReporter adapter. |
| RQ-14 | Client-side analytics? | navigator.sendBeacon + consent gate → /app/api/analytics/track → AnalyticsService. |
| RQ-15 | Expired blocking vanilla? | apiFetch() tangkap SUBSCRIPTION_EXPIRED → modal blocking; Blade view expired. |
| RQ-16 | Rate limit? | 15 rate limit (12 lama + 3 baru: email verification resend, PIN verification, hold order). |
| RQ-17 | Guard rules? | 8 guard rules diverifikasi CI + production. |
| RQ-18 | Analytics events? | 54 events (44 MVP + 10 production). |
| RQ-19 | Audit log actions? | +20 baru (inventory, modifier, CRM, shift, hold, kasbon, kas movement, QRIS, voucher, setup, switch user, print ulang, offline sync, bluetooth, search, notification). |
| RQ-20 | Scheduler? | 18 scheduled command (16 lama + 2 baru: CheckCreditDueDate, ApplyScheduledDiscounts). |
| RQ-21 | JSON column? | 10 JSON shape (8 lama + 2 baru: scheduled_discounts.scope_ids, scheduled_discounts.days_of_week). |
| RQ-22 | Migration rollback? | migrate:fresh → migrate:rollback --step=80 → migrate; 2 circular dependency via ALTER TABLE. |
| RQ-23 | Support write escalation? | Merchant approve in-app, maks 15 menit, audit. |
| RQ-24 | Environment indicator vanilla? | Blade global variables + <x-env-badge> + CSS components/badge.css. |
| RQ-25 | Deploy vanilla assets? | Copy resources/js/* → public/js/*; minify CSS; cache busting ?v={version}; HTTP/2; CDN. |
| RQ-26 | Review vanilla compliance? | 10 cek (framework, TS, Tailwind, innerHTML, CSS token, ES modules, partial test, cache busting, Canvas API, Touch events). |
| RQ-27 | Login POS? | PIN-only (pilih karyawan + PIN 6 digit); device activation wajib; lockout 5×/15 menit; switch user tanpa logout. |
| RQ-28 | Kasbon? | Server-side validation limit; stock berkurang saat order; loyalty saat lunas; tidak gabung voucher; hapus customer ditolak jika credit_balance > 0. |
| RQ-29 | Hold order? | Maks 10 per outlet; stock tidak berkurang; wajib resolve sebelum tutup kasir. |
| RQ-30 | Kas keluar/masuk? | Alasan wajib min 10 char; immutable; tercatat di shift report. |
| RQ-31 | Promosi? | Voucher immutable setelah redeem; tidak gabung kasbon; diskon terjadwal otomatis. |
| RQ-32 | Chart & Gesture? | Canvas API vanilla (6 chart types); Touch events native (9 gesture); dilarang library eksternal. |

Verdict: 32 RQ terjawab — tidak ada pertanyaan terbuka.

---

### §34. Referensi Lintas Dokumen

| Aspek | Dokumen | Section |
|---|---|---|
| Fitur & alur bisnis | PRD | §1–§18 |
| Visual & komponen | Design | §1–§19 |
| Data model (SQL DDL 80 tabel) | Schema | §4 |
| JSON shapes | Schema | §4 |
| Migration & charset | Schema | §8 |
| Retention registry | Schema | §4 |
| Stack & deployment | Architecture | §3, §13 |
| Adapter pattern | Architecture | §5 |
| Guard rules | Architecture | §8 |
| Analytics pipeline | Architecture | §10 |
| Test strategy | Architecture | §12 |
| Middleware & routing | Architecture | §7 |
| Struktur folder vanilla | Architecture | §4 |
| Service baru | Architecture | §4, §14, §17 |
| ADR (88) | Architecture | §15 |
| Aturan arsitektur (68) | Architecture | §14 |
| Production readiness | Architecture | §16 |
| Fitur baru arsitektur detail | Architecture | §17 |
| Rule v2.0 (dokumen ini) | | §1–§36 |

**Dokumen eksternal:** UU PDP No. 27/2022, UU ITE, PPN 11%, Permenkominfo 5/2020, Laravel 13, PHP 8.4, MySQL 8.4, Redis 7.4, Sanctum, Horizon, Pest, Playwright, Nginx 1.28, Docker Compose, Render.com, TiDB Cloud, Upstash, Cloudflare R2, Instatus, Fonnte, Amazon SES, IDCloudHost, Biznet Gio, MinIO, Chatwoot, BookStack, Uptime Kuma, GoAlert, GlitchTip.

**Referensi kompetitor:** Majoo (Git, code review, CI/CD, AI pair programming, setup wizard), Moka POS (automated pipeline, continuous testing, PIN-based staff lock), Pawoon (Agile, quality-driven coding, test plans), Olsera (Agile, hybrid testing, black box), PanelControl/ShelfOne (vanilla JS POS, render engine, textContent), Square POS (passcode login, quick amount, switch employee), Toast (employee PIN lock, split payment, hold order).

---

### §35. Sign-off Checklist Rule v2.0 FINAL

#### 35.1 Dokumen

- □ Semua section §1–§36 lengkap
- □ Cross-reference valid (§34)
- □ Tidak ada placeholder / TBD
- □ 48 aturan emas terdokumentasi
- □ 64 aturan khusus (14 tema) terdokumentasi
- □ 32 RQ terjawab

#### 35.2 Aturan & Enforcement

- □ 48 aturan emas disetujui founder + tech lead
- □ 64 aturan khusus disetujui founder + tech lead
- □ 3 level sanksi disetujui
- □ Audit schedule disetujui
- □ Override authority disetujui
- □ Enforcement vanilla (ESLint 9+ flat config, Stylelint, CI check) disetujui
- □ Enforcement fitur baru (PIN-only, kasbon, hold, kas movement) disetujui

#### 35.3 Workflow & Standar

- □ Workflow wajib (8 langkah) disetujui
- □ Alur fitur baru (9 langkah) disetujui
- □ Alur hotfix (11 langkah) disetujui
- □ 8 branch strategy disetujui
- □ 18 kategori PR kritis disetujui
- □ Template PR (5 section baru) disetujui
- □ Commit convention (9 type, 55+ scope) disetujui

#### 35.4 Standar Kode Vanilla

- □ Blade aturan (10) disetujui
- □ JS vanilla aturan (24) disetujui
- □ CSS aturan (18) disetujui
- □ 24 state disetujui
- □ ESLint 9+ flat config disetujui
- □ Stylelint config disetujui
- □ CI 11 langkah disetujui

#### 35.5 Test & Seeder

- □ 3 prinsip test disetujui
- □ 7 lapisan test disetujui
- □ Coverage target (8 path) disetujui
- □ 50 E2E flow disetujui
- □ 36 test wajib per fitur disetujui
- □ 14 larangan test disetujui
- □ 27 seeder disetujui
- □ 8 larangan seeder disetujui

#### 35.6 Keamanan & Compliance

- □ 18 aturan keamanan disetujui
- □ 15 rate limit disetujui
- □ 8 guard rules disetujui
- □ 9 compliance security disetujui
- □ 8 environment security disetujui
- □ Support access policy disetujui
- □ SLA disetujui

#### 35.7 DoD & Rilis

- □ DoD MVP (30 item) disetujui
- □ DoD Fase 7 (9 item) disetujui
- □ DoD Fase 8 (9 item) disetujui
- □ DoD Fase 9 (12 item) disetujui
- □ Checklist rilis staging (25 item) disetujui
- □ Checklist rilis production (18 item) disetujui
- □ Hotfix procedure disetujui

#### 35.8 Staging Infrastructure (Tanpa CC)

- □ Render.com disetujui untuk app + cron
- □ TiDB Cloud disetujui untuk MySQL
- □ Upstash disetujui untuk Redis
- □ Cloudflare R2 disetujui untuk storage
- □ Instatus.com disetujui untuk status page
- □ Semua provider 100% tanpa CC dikonfirmasi

#### 35.9 Lintas Dokumen

- □ Rule v2.0 sinkron dengan PRD v2.0
- □ Rule v2.0 sinkron dengan Schema v2.0
- □ Rule v2.0 sinkron dengan Design v2.0
- □ Rule v2.0 sinkron dengan Architecture v2.0

#### 35.10 Stakeholder Approval

| Role | Nama | Tanggal |
|---|---|---|
| Founder / Rule Owner | _________________ | ______ |
| Tech Lead / Rule Co-Owner | _________________ | ______ |
| Engineering Lead | _________________ | ______ |
| Backend Lead | _________________ | ______ |
| Frontend Lead | _________________ | ______ |
| DevOps / SRE Lead | _________________ | ______ |
| QA Lead | _________________ | ______ |
| Security Lead | _________________ | ______ |
| Compliance / Legal | _________________ | ______ |

---

### §36. Ringkasan Eksekutif Rule v2.0 (1 Halaman)

Rule v2.0 adalah aturan main mengikat untuk Kasirku. Menyelaraskan aturan proses dengan PRD v2.0 (80 tabel, 19 journey, 21 fitur gated, 118 PD), Schema v2.0 (80 tabel, 27 seeder, 10 immutable, 2 circular dependency), Design v2.0 (104 wireframe, 26 prinsip, 72 aturan desain, 87 token), dan Architecture v2.0 (28 prinsip, 88 ADR, 68 aturan, vanilla frontend).

**48 Aturan Emas** dalam 6 kategori: Fondasi (1–10), Project Spesifik (11–22), Dev-First & Fase (23–30), Modern (31–40), Vanilla Frontend (41–44), Fitur Baru POS & Auth (45–48).

**12 Aturan Paling Kritis:** fitur belum selesai sebelum test + seeder, CI 11 langkah hijau, tidak ada secret di repo, tidak ada push langsung ke main, update Schema di PR yang sama, test wajib fake adapter, adapter pattern wajib, analytics tanpa consent = pelanggaran UU PDP, tidak ada React/Vue/TS/Tailwind/Inertia, data immutable hanya INSERT, login POS wajib PIN-only, kasbon wajib server-side validation.

**Enforcement:** Rule owner Founder + Tech Lead; 3 level sanksi; audit weekly/monthly/quarterly; override authority founder; enforcement vanilla (ESLint 9+ flat config, Stylelint, CI check, test immutable, code review); enforcement fitur baru (PIN-only, kasbon, hold, kas movement).

**Workflow:** 8 langkah setiap perubahan + 9 langkah fitur baru + 11 langkah hotfix. 8 branch strategy. 18 kategori PR kritis. Commit convention 9 type 55+ scope. 12 larangan commit.

**Standar Kode Vanilla:** Blade 10 aturan, JS 24 aturan, CSS 18 aturan. ESLint 9+ flat config, Stylelint config. CI 11 langkah. Canvas API untuk chart. Touch events untuk gesture. Web Bluetooth untuk printer. localStorage untuk offline cart.

**Test Strategy:** 3 prinsip, 7 lapisan, coverage 8 path, 50 E2E flow, 36 test wajib per fitur, 14 larangan test.

**Seeder:** 27 seeder. FeatureSeeder 21 fitur, PlanFeatureSeeder 21×6, RetentionJobSeeder 12 job, PaymentMethodSeeder include QRIS + Kasbon.

**Review Checklist:** 55 item dalam 8 kategori. 20 reviewer khusus per kategori.

**Keamanan:** 18 aturan, 15 rate limit, 8 guard rules, analytics security (client-side ke /app/api/analytics/track, error ke /app/api/errors/report).

**Compliance:** 9 compliance security, 8 environment security. UU PDP, data residency Jakarta, support access ≤30 menit.

**Staging Infrastructure (Tanpa CC):** Render.com (app + cron) + TiDB Cloud Serverless (MySQL) + Upstash (Redis) + Cloudflare R2 (storage) + Instatus.com (status page) + Sentry free (error tracking). Total Rp0 tanpa kartu kredit.

**64 Aturan Khusus (14 Tema):**
- §16 Auth & Session (6): session-based, MFA, RBAC, onboarding, login POS PIN-only, switch user
- §17 Subscription & Trial (6): trial plan, grace, read-only, expired route 12+12, trial→paid, settings POS + tax_rates + cashier_settings + ewallet_settings + notification_preferences
- §18 Feature Gating & Kuota (4): 21 fitur gated, kuota, cache invalidation, multi-outlet
- §19 Inventory & Immutable Data (4): stock ledger, PO lifecycle, low stock, feature gate
- §20 Modifier, CRM & Shift (4): modifier picker, loyalty redeem, shift report immutable, feature gate
- §21 POS Baru (6): hold order, kasbon, kas keluar/masuk, QRIS statis, split rata, refund parsial + print ulang
- §22 Promosi & Notifikasi (4): voucher, diskon terjadwal, notifikasi perubahan data, pencarian global + laporan terlaris
- §23 Data & Isolation (4): multi-tenant, audit log 20 action baru, JSON shape 10, data residency
- §24 Compliance & Support (6): cookie/privacy/export/delete, consent withdrawal, support, ticket, export data, delete account
- §25 Adapter, Guard & Analytics (8): adapter 8 contracts, composite fallback, guard, analytics consent 54 events, IP hashing, bot filter, TrackEvent, dashboard
- §26 Migration & Retention (4): 80 tabel, 18 scheduled command, 12 retention job, future tables
- §27 Dev-First & Environment (4): responsive web vanilla, print + Bluetooth, dev-first, env indicator
- §28 Vanilla Frontend & Chart (4): Canvas API chart, Touch events gesture, localStorage offline cart, Web Bluetooth printer
- §29 Setup Awal Owner & Pencarian Global (2): checklist 10 langkah, Ctrl+K search

**DoD:** MVP 30 item, Fase 7 9 item, Fase 8 9 item, Fase 9 12 item.

**Rilis:** Staging 25 item, Production 18 item, Hotfix 11 langkah.

**32 RQ terjawab.**

**Status:** ✅ Siap eksekusi. 48 aturan emas, 64 aturan khusus, 27 seeder, 50 E2E, 15 rate limit, 8 guard, 54 analytics events, 14 larangan test, 55 review checklist, 60 DoD item, 88 ADR, 100% terisi, sinkron 100% dengan PRD v2.0, Schema v2.0, Design v2.0, Architecture v2.0.

---

## ✅ RULE v2.0 FINAL COMPLETE — SIAP SIGN-OFF

| Aspek | Nilai | Status |
|---|---|---|
| Aturan emas | 48 | ✅ |
| Aturan khusus | 64 (14 tema) | ✅ |
| Fitur gated | 21 | ✅ |
| Tabel | 80 | ✅ |
| Seeder | 27 | ✅ |
| CI langkah | 11 | ✅ |
| E2E flow | 50 | ✅ |
| Test layer | 7 | ✅ |
| Test wajib per fitur | 36 | ✅ |
| Larangan test | 14 | ✅ |
| Rate limit | 15 | ✅ |
| Analytics events | 54 | ✅ |
| Audit log actions baru | 20 | ✅ |
| Scheduled command | 18 | ✅ |
| Retention job | 12 | ✅ |
| JSON shape | 10 | ✅ |
| Review checklist | 55 | ✅ |
| DoD item | 60 | ✅ |
| RQ | 32 | ✅ |
| Section | 36 | ✅ |
| Sinkron PRD/Schema/Design/Arch | 100% | ✅ |
| Placeholder/TBD | 0 | ✅ |
| Vanilla frontend enforced | ✅ | ✅ |
| Staging tanpa CC | ✅ | ✅ |
| Login POS PIN-only enforced | ✅ | ✅ |
| Kasbon server-side validation enforced | ✅ | ✅ |
| Hold order limit enforced | ✅ | ✅ |
| Kas movement immutable enforced | ✅ | ✅ |
