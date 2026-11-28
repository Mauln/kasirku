# DESIGN — KASIRKU v2.0

**Versi:** 2.0 | **Status:** Final | **Tanggal:** 2026-11-16

---

## Daftar Isi

```
§1   Overview & Prinsip Desain (26 Prinsip)
§2   Design Token (87 Token)
§3   Brand Assets
§4   Komponen Dasar (20 Grup)
§5   State Wajib (24 State)
§6   Layout & Responsive
§7   Aksesibilitas & Motion (34 Micro-interactions)
§8   Expired Subscription UX
§9   Error Pages
§10  Onboarding & Setup Awal Owner
§11  Guard Failure UX
§12  Index Wireframe (104 Halaman)
§13  Data Visualization System
§14  Receipt & Print Design
§15  Mobile Gesture System
§16  Environment Provider Matrix
§17  Aturan Desain (72)
§18  Design Decisions Log (68 DD)
§19  Sign-off
```

---

## §1. Overview & Prinsip Desain

### 1.1 Tentang Dokumen Ini

Panduan visual & UX untuk dua sisi produk Kasirku:

- **Merchant Web App** — dashboard, POS, langganan, settings, inventori, CRM, modifier, shift report, promosi, kasbon, e-wallet, pencarian global
- **Backoffice Internal** — tenant management, subscription, audit, support, device, partner, production readiness

**Target visual:** Setara Majoo/Moka POS/Pawoon/Olsera — clean, cepat, familiar UMKM Indonesia, dengan sentuhan modern (gradient, elevation, micro-interactions).

**Frontend:** HTML + CSS + JavaScript vanilla murni (ES modules) + Blade templating. Tanpa React, Vue, TypeScript, Tailwind, Inertia, Alpine, HTMX, atau Livewire.

### 1.2 26 Prinsip Desain

| # | Prinsip | Konsekuensi |
|---|---|---|
| 1 | Mobile-first, POS-first | Layout HP dulu → tablet → desktop |
| 2 | Konsisten dua sisi | Token sama di merchant & backoffice |
| 3 | Jelas > cantik | Satu layar, satu tujuan utama |
| 4 | Cepat terasa | Skeleton, optimistic UI, feedback instan |
| 5 | Aksesibel & touch-friendly | Tap target ≥44×44px, kontras ≥4.5:1 |
| 6 | Transparan status langganan | Banner + modal + CTA saat expired |
| 7 | Transparan data & compliance | Export, hapus akun, privacy mudah diakses |
| 8 | Transparan support | Banner dua sisi, time-boxed |
| 9 | Tidak ada APK | Responsive web only |
| 10 | Environment-aware | Komponen sama, provider beda |
| 11 | Dev-first, setup-at-end | Fitur lokal dulu; staging Fase 7 |
| 12 | MVP = Fase 0–8 | Berakhir saat Staging UAT Complete |
| 13 | Production readiness = Fase 9 | Komponen ditandai [PROD] |
| 14 | Environment indicator + demo mode = Fase 7 | Safety-critical |
| 15 | Zero-surprise staging | Environment jelas |
| 16 | Zero-surprise go-live [PROD] | Status page, banner maintenance |
| 17 | Reversible & rollback-friendly [PROD] | Versi/tag, tombol rollback |
| 18 | Production readiness is observable [PROD] | Status real-time |
| 19 | Wireframe-first, tanpa Figma | §12 baseline visual |
| 20 | Snapshot immutability terlihat | Badge [IMMUTABLE] 🔒 di audit log, stock movement, shift report |
| 21 | Fitur gated terlihat, bukan disembunyikan | Overlay lock + CTA upgrade — konsisten 21 fitur gated |
| 22 | Ledger-based stock terlihat | Stock movement timeline, bukan sekadar angka |
| 23 | **Data-rich dashboard** | 8 widget dengan sparkline, chart interaktif, heatmap |
| 24 | **Delightful micro-interactions** | Animasi halus, feedback instan, transisi smooth |
| 25 | **Speed-first POS** | Login PIN-only ≤5 detik, hold order <200ms, kas keluar <200ms |
| 26 | **Guided setup** | Setup Awal Owner 10 langkah dengan checklist visual |

### 1.3 Referensi Kompetitor

| Kompetitor | Yang Diadopsi |
|---|---|
| Majoo | Dashboard 8 widget + filter periode; 6 mode POS; pengaturan kasir (kas awal, kas keluar); setup wizard pasca-registrasi |
| Moka POS | Dual touch screen; layout POS 2 panel 30/70 (universal); panel kiri (order) + panel kanan (menu grid); product card premium |
| Pawoon | Dashboard mobile view (card-based); tablet view sidebar collapsible; kustomisasi struk visual |
| Olsera | Dashboard masa aktif + notifikasi stok + paket aktif; denah meja & KDS; 2-level inventory |
| Toast/Square/Lightspeed | 2-panel split universal (30/70); quick actions; modifier di bawah nama item (indent 16px); thumb-zone layout; PIN-based staff lock; quick amount buttons di payment |

### 1.4 Ringkasan Eksekutif

Design v2.0 adalah panduan visual & UX final untuk Kasirku. Mencakup **104 wireframe** untuk 19 journey + production readiness + backoffice internal.

**Prinsip utama (26):** mobile-first, POS-first, konsisten dua sisi, jelas > cantik, aksesibel, transparan status langganan, snapshot immutability, fitur gated terlihat, ledger-based stock, data-rich dashboard, delightful micro-interactions, speed-first POS, guided setup.

**Design token:** 87 token — Teal #29B6B3 (primary) dengan gradient, elevation 5 level, glass effect, 12 chart token, 15 dark mode token (prepared), 37 warna dasar.

**Komponen utama (20 grup):** tombol (10 varian), input, card (8 varian), modal, toast, navigasi, list/table, POS (14 komponen), langganan, backoffice, support, compliance, dev-first, production readiness, MFA, **Setup Checklist**, **Chart**, **PIN Pad**, **QRIS Display**, **Hold Order Badge**.

**State wajib (24):** 20 state dasar + 4 state baru (hold-active, kasbon-outstanding, connection-lost, printer-connected).

**Micro-interactions (34):** add-to-cart animation, button ripple, payment success checkmark, page transitions, stat counter, chart draw animation, swipe gestures, pull-to-refresh, dan lainnya.

**Status:** ✅ Siap eksekusi. 104 wireframe, 26 prinsip, 72 aturan, 21 fitur gated, 87 token, 24 state, 34 micro-interactions.

---

## §2. Design Token (87 Token)

> **Aturan emas:** Semua nilai warna, spacing, font, gradient, elevation wajib dari token — tidak boleh hardcode. Diterapkan via CSS custom properties (`:root`).

### 2.1 Warna Dasar (37 Token)

| Token | Nilai | Fungsi |
|---|---|---|
| `--color-primary` | #29B6B3 | Tombol utama, link aktif, accent POS, tombol Bayar |
| `--color-primary-hover` | #239C99 | Hover / pressed |
| `--color-primary-dark` | #196D6B | Header POS, sidebar backoffice, pressed Bayar |
| `--color-secondary` | #81CF77 | Aksen sekunder, sukses ringan |
| `--color-bg` | #F7F9FA | Background halaman |
| `--color-surface` | #FFFFFF | Card, modal, panel POS |
| `--color-surface-alt` | #EEF3F5 | Background area produk, panel |
| `--color-border` | #E3E8EB | Garis, divider |
| `--color-border-strong` | #C4CDD1 | Border input, divider tebal |
| `--color-text` | #1A2B2E | Teks utama |
| `--color-text-muted` | #6B7A7E | Teks sekunder |
| `--color-text-inverse` | #FFFFFF | Teks di atas primary/dark |
| `--color-success` | #2E9E5B | Transaksi lunas, aktif |
| `--color-warning` | #E6A817 | Trial hampir habis, grace |
| `--color-danger` | #D64545 | Error, void, refund, expired |
| `--color-disabled` | #C4CDD1 | Elemen nonaktif |
| `--color-locked` | #9AA6AB | Ikon lock, fitur terkunci |
| `--color-trial` | #F5A623 | Badge trial, countdown |
| `--color-upgrade` | #7B61FF | CTA upgrade, badge premium |
| `--color-support` | #F97316 | Banner Mode Support |
| `--color-offline` | #B45309 | Banner offline |
| `--color-info` | #3B82F6 | Info, tooltip, KB |
| `--color-consent` | #6B7A7E | Cookie banner (netral) |
| `--color-legal` | #4B5563 | Teks legal |
| `--color-dev` | #7C3AED | Badge DEV |
| `--color-staging` | #0EA5E9 | Badge STAGING |
| `--color-prod-test` | #F59E0B | Badge PRODUCTION-TEST |
| `--color-production` | #059669 | Badge PRODUCTION (backoffice) |
| `--color-gate-pass` | #22C55E | Release gate PASS |
| `--color-gate-fail` | #EF4444 | Release gate FAIL |
| `--color-gate-pending` | #94A3B8 | Release gate PENDING |
| `--color-gate-forced` | #F97316 | Release gate FORCED |
| `--color-maintenance` | #F59E0B | Banner maintenance |
| `--color-immutable` | #8B5CF6 | Badge [IMMUTABLE] 🔒 |
| `--color-crm` | #EC4899 | Aksen CRM & loyalty |
| `--color-inventory` | #14B8A6 | Aksen inventory & stock |
| `--color-modifier` | #F59E0B | Aksen modifier |

### 2.2 Gradient Token (8 Token) 🆕

| Token | Nilai | Fungsi |
|---|---|---|
| `--gradient-primary` | linear-gradient(135deg, #29B6B3 0%, #1A9E9B 100%) | Tombol CTA utama, header hero |
| `--gradient-primary-hover` | linear-gradient(135deg, #239C99 0%, #158481 100%) | Hover tombol gradient |
| `--gradient-hero` | linear-gradient(160deg, #0F2027 0%, #203A43 50%, #2C5364 100%) | Landing hero background |
| `--gradient-card-premium` | linear-gradient(145deg, #FFFFFF 0%, #F8FBFC 100%) | Card premium, plan card aktif |
| `--gradient-success` | linear-gradient(135deg, #2E9E5B 0%, #27AE60 100%) | Tombol sukses, badge aktif |
| `--gradient-danger` | linear-gradient(135deg, #D64545 0%, #C0392B 100%) | Tombol danger, banner expired |
| `--gradient-upgrade` | linear-gradient(135deg, #7B61FF 0%, #5B3FD4 100%) | CTA upgrade, badge premium |
| `--gradient-pos-header` | linear-gradient(180deg, #196D6B 0%, #29B6B3 100%) | Header POS tablet |

### 2.3 Elevation & Depth Token (6 Token) 🆕

| Token | Nilai | Fungsi |
|---|---|---|
| `--elevation-0` | none | Elemen flat |
| `--elevation-1` | 0 1px 3px rgba(26,43,46,.06), 0 1px 2px rgba(26,43,46,.04) | Card ringan, badge |
| `--elevation-2` | 0 4px 6px rgba(26,43,46,.07), 0 2px 4px rgba(26,43,46,.04) | Card standar, input focus |
| `--elevation-3` | 0 10px 15px rgba(26,43,46,.08), 0 4px 6px rgba(26,43,46,.04) | Dropdown, popover |
| `--elevation-4` | 0 20px 25px rgba(26,43,46,.10), 0 10px 10px rgba(26,43,46,.04) | Modal, bottom sheet |
| `--elevation-modal` | 0 25px 50px rgba(26,43,46,.15) | Modal besar, payment modal |

### 2.4 Glass & Blur Token (3 Token) 🆕

| Token | Nilai | Fungsi |
|---|---|---|
| `--glass-bg` | rgba(255,255,255,.85) | Background glassmorphism |
| `--glass-blur` | blur(12px) | Backdrop blur |
| `--glass-border` | 1px solid rgba(255,255,255,.3) | Border glass |

### 2.5 Module Accent Token (6 Token) 🆕

| Token | Nilai | Modul |
|---|---|---|
| `--color-module-pos` | #29B6B3 | POS |
| `--color-module-inventory` | #14B8A6 | Inventori |
| `--color-module-crm` | #EC4899 | CRM & Loyalty |
| `--color-module-finance` | #F59E0B | Keuangan |
| `--color-module-report` | #8B5CF6 | Laporan |
| `--color-module-settings` | #6B7280 | Pengaturan |

### 2.6 Chart Token (12 Token) 🆕

| Token | Nilai | Fungsi |
|---|---|---|
| `--chart-line-width` | 2px | Ketebalan garis chart |
| `--chart-grid-color` | var(--color-border) | Warna grid chart |
| `--chart-tooltip-bg` | var(--color-text) | Background tooltip |
| `--chart-tooltip-text` | var(--color-text-inverse) | Teks tooltip |
| `--chart-tooltip-radius` | var(--radius-sm) | Radius tooltip |
| `--chart-color-1` | var(--color-primary) | Seri data 1 |
| `--chart-color-2` | var(--color-secondary) | Seri data 2 |
| `--chart-color-3` | #F59E0B | Seri data 3 |
| `--chart-color-4` | #8B5CF6 | Seri data 4 |
| `--chart-color-5` | #EC4899 | Seri data 5 |
| `--chart-sparkline-height` | 24px | Tinggi sparkline |
| `--chart-sparkline-width` | 60px | Lebar sparkline |

### 2.7 Dark Mode Token (15 Token — Prepared, Not Active) 🆕

```css
:root[data-theme="dark"] {
  --color-bg: #0F1419;
  --color-surface: #1A2332;
  --color-surface-alt: #243447;
  --color-border: #2D3F52;
  --color-border-strong: #3D5166;
  --color-text: #E8EDF2;
  --color-text-muted: #8B9DAF;
  --color-text-inverse: #0F1419;
  --color-primary: #3DD6D3;
  --color-primary-hover: #29B6B3;
  --color-primary-dark: #1A8A87;
  --shadow-sm: 0 1px 3px rgba(0,0,0,.3);
  --shadow-md: 0 4px 12px rgba(0,0,0,.4);
  --glass-bg: rgba(26,35,50,.85);
  --elevation-1: 0 1px 3px rgba(0,0,0,.2), 0 1px 2px rgba(0,0,0,.15);
}
```

> **Catatan:** Token dark mode disiapkan sekarang, implementasi post-MVP. Semua warna menggunakan `var()` sehingga dark mode hanya perlu override di `:root[data-theme="dark"]`.

### 2.8 Tipografi

| Token | Ukuran | Berat | Line-height | Dipakai |
|---|---|---|---|---|
| `--font-family` | Inter, system-ui, -apple-system, sans-serif | — | — | Semua teks |
| `--text-h1` | 28px | 700 | 1.25 | Judul halaman |
| `--text-h2` | 22px | 600 | 1.3 | Judul section |
| `--text-h3` | 18px | 600 | 1.4 | Subjudul, nama produk POS |
| `--text-body` | 16px | 400 | 1.5 | Teks utama |
| `--text-sm` | 14px | 400 | 1.5 | Sekunder, label |
| `--text-xs` | 12px | 400 | 1.4 | Caption, badge |
| `--text-pos-total` | 24px | 700 | 1.2 | Total transaksi POS |
| `--text-pos-price` | 16px | 600 | 1.3 | Harga produk POS |
| `--text-mono` | 14px | 400 | 1.5 | OTP, audit log, versi, stock movement ID |
| `--text-legal` | 13px | 400 | 1.6 | Privacy, S&K, cookie |
| `--text-env-badge` | 11px | 700 | 1.2 | Badge environment |
| `--text-immutable` | 10px | 600 | 1.2 | Badge immutable |
| `--text-stat-value` | 28px | 700 | 1.1 | Nilai stat card dashboard |
| `--text-stat-label` | 12px | 500 | 1.3 | Label stat card dashboard |

### 2.9 Spacing

| Token | Nilai |
|---|---|
| `--space-1` | 4px |
| `--space-2` | 8px |
| `--space-3` | 12px |
| `--space-4` | 16px |
| `--space-6` | 24px |
| `--space-8` | 32px |
| `--space-12` | 48px |

### 2.10 Radius & Shadow

| Token | Nilai | Dipakai |
|---|---|---|
| `--radius-sm` | 4px | Input, badge, tombol kecil |
| `--radius-md` | 8px | Tombol, card, kartu POS |
| `--radius-lg` | 16px | Modal, bottom sheet |
| `--radius-full` | 9999px | Avatar, pill, chip |
| `--shadow-sm` | 0 1px 3px rgba(26,43,46,.08) | Card ringan |
| `--shadow-md` | 0 4px 12px rgba(26,43,46,.12) | Dropdown, modal |
| `--shadow-pos-card` | 0 1px 2px rgba(26,43,46,.06) | Kartu POS |
| `--shadow-upgrade` | 0 4px 16px rgba(123,97,255,.20) | CTA upgrade |
| `--shadow-consent` | 0 -2px 12px rgba(26,43,46,.12) | Cookie banner |
| `--shadow-chat` | 0 4px 16px rgba(41,182,179,.24) | Chat launcher |
| `--shadow-env-badge` | 0 1px 4px rgba(0,0,0,.12) | Badge env |
| `--shadow-expired` | 0 2px 8px rgba(214,69,69,.20) | Banner/modal expired |
| `--shadow-gate-pass` | 0 0 0 3px rgba(34,197,94,.15) | Release gate PASS |
| `--shadow-gate-fail` | 0 0 0 3px rgba(239,68,68,.15) | Release gate FAIL |
| `--shadow-immutable` | 0 0 0 2px rgba(139,92,246,.12) | Badge immutable |
| `--shadow-float` | 0 8px 30px rgba(41,182,179,.20) | Floating action, hold badge |

### 2.11 Z-index (14 Level)

| Token | Nilai | Fungsi |
|---|---|---|
| `--z-base` | 0 | Konten normal |
| `--z-dropdown` | 100 | Dropdown |
| `--z-sticky` | 200 | Header sticky |
| `--z-trial-banner` | 250 | Banner trial |
| `--z-modal` | 300 | Modal |
| `--z-toast` | 400 | Toast |
| `--z-loading` | 500 | Loading overlay |
| `--z-support-banner` | 600 | Banner support |
| `--z-chat` | 650 | Chat widget |
| `--z-consent` | 700 | Cookie consent |
| `--z-env-badge` | 750 | Badge environment |
| `--z-maintenance` | 800 | Banner maintenance |
| `--z-gate-modal` | 850 | Modal gate/rollback |
| `--z-immutable-tooltip` | 900 | Tooltip immutable |

---

## §3. Brand Assets

### 3.1 Wordmark

Teks **"Kasirku"** — Inter 700, ukuran `--text-h2` (22px), warna `--color-primary` #29B6B3. Tanpa ikon.

Digunakan di: header landing, footer, login, backoffice top bar.

### 3.2 App Icon / Favicon

Monogram **"K"** kapital — Inter 700, putih, di atas rounded square `--color-primary`. Radius 22%.

- Favicon: 32×32 SVG
- App icon: 512×512 PNG

### 3.3 Logo Lockup

Ikon K + wordmark "Kasirku" — jarak `--space-2`. Untuk landing hero, login page, email header.

### 3.4 File Wajib Fase 0

| File | Ukuran | Format |
|---|---|---|
| logo-wordmark.svg | — | SVG |
| logo-icon.svg | 512×512 | SVG |
| logo-lockup.svg | — | SVG |
| favicon.ico | 32×32 | ICO |
| apple-touch-icon.png | 180×180 | PNG |

### 3.5 Ikon

- **Library:** Lucide (primary), Heroicons (fallback)
- **Stroke:** 2px
- **Ukuran:** 16px (inline), 20px (tombol), 24px (nav), 32px (card), 64px (empty)
- **Ikon wajib:** inventory, modifier, CRM, shift, immutable, release gate, cutover, rollback, MFA, billing, ticket, partner, export, deletion, **hold, kasbon, kas-keluar, qris, bluetooth, voucher, search, bell**
- **Ilustrasi custom:** ❌ post-MVP

### 3.6 Aturan Brand

- Tidak ada ilustrasi kompleks di MVP
- Ikon konsisten 1 library
- Dark mode post-MVP (token disiapkan via CSS variables)
- Font fallback: Google Fonts Inter + `font-display: swap`

---

## §4. Komponen Dasar (20 Grup)

> Semua komponen wajib dibuat dulu sebelum halaman. Setiap komponen punya state: default, hover, active, disabled, loading, error, focus-visible.

### 4.1 Tombol (10 Varian)

| Varian | Kegunaan |
|---|---|
| Primary | Aksi utama (POS: Bayar & Simpan) |
| Secondary | Aksi alternatif (Cetak Bill, Split) |
| Ghost/Text | Aksi ringan (Batal) |
| Danger | Void, Refund, Suspend, Rollback [PROD] |
| Upgrade | CTA upgrade (gradient upgrade → primary) |
| Pos-Action | Aksi POS (min height 48px) |
| Link-External | Link eksternal (Privacy, status, KB) |
| Immutable-Action | Aksi membuat data immutable (konfirmasi eksplisit) |
| Gate-Action [PROD] | Mark PASS/FAIL gate (warna sesuai status) |
| Reauth-Action [PROD] | Cutover, Rollback (re-auth password+TOTP) |

**Aturan:**
- Tap target ≥44×44px; POS ≥48×48px
- Loading state wajib untuk aksi API
- Disabled: opacity + not-allowed
- POS: 1 tombol primary/layar
- Focus: `:focus-visible` outline 2px
- Immutable-Action wajib konfirmasi modal
- Gate/Reauth-Action wajib re-auth

**Tombol Bayar POS:**
- Full-width 56px
- `--gradient-primary`
- Teks putih 24px 700
- Padding `--space-4`
- Radius md
- Label: "BAYAR Rp 61.050"
- Pressed: `--color-primary-dark`
- Disabled: opacity 0.6

### 4.2 Input & Form

**Tipe:** text, textarea, select, checkbox, radio, switch, date picker, currency, OTP 6 digit, file upload, search (debounce), numeric stepper, range slider.

**State:** default, focus, filled, error, disabled, read-only, locked (gated).

**Aturan:**
- Label di atas input
- Error di bawah field (`aria-describedby`)
- Font ≥16px
- Field di luar paket: overlay lock + CTA upgrade

### 4.3 Card (8 Varian) 🆕

**Base:** `--color-surface` + `--radius-md` + `--elevation-1` + `--space-4` padding.

| Varian | Detail |
|---|---|
| Default | Card standar |
| Clickable | Hover elevation-2, cursor pointer |
| Plan card | Border primary 2px jika aktif; badge "Populer" |
| POS product card | Thumbnail 4:3, nama 2 baris, harga, badge stok |
| Stat card | Nilai besar + label + sparkline + trend |
| Feature card terkunci | Overlay lock + CTA upgrade |
| Setup checklist card | Ikon + judul + status + CTA |
| Notification card | Ikon + judul + waktu + aksi |

**Stat Card dengan Sparkline:**

```
┌──────────────────────────┐
│  Omzet Hari Ini          │
│  Rp 2.450.000            │  ← --text-stat-value (28px 700)
│  ↑ 12.5% vs kemarin     │  ← --color-success / --color-danger
│  ▁▃▅▇▅▃▅▇▅▃▅▇           │  ← sparkline 60×24px
│                          │
│  [Lihat Detail →]        │
└──────────────────────────┘
```

**POS Product Card Enhanced:**

```
┌──────────────────────────┐
│  ┌────────────────────┐  │
│  │                    │  │  ← Image 4:3, lazy loading
│  │   [IMAGE]          │  │     Skeleton shimmer saat load
│  │                    │  │
│  │   ┌──────────┐     │  │
│  │   │ Sisa 5   │     │  │  ← Badge warning jika ≤ threshold
│  │   └──────────┘     │  │
│  └────────────────────┘  │
│  Nasi Goreng             │  ← Nama, maks 2 baris
│  ★ 4.8 · Best Seller    │  ← Rating/badge (opsional)
│  Rp 25.000              │  ← Harga, --text-pos-price
│  ┌────────────────────┐  │
│  │ 🛒 Tambah          │  │  ← Tap area penuh
│  └────────────────────┘  │
└──────────────────────────┘
```

### 4.4 Modal / Bottom Sheet

**Mobile:** bottom sheet. **Tablet/desktop:** modal tengah.

**Aturan:**
- Close jelas
- Tap luar tutup (kecuali kritis)
- Focus trap
- ESC
- Scroll lock

**Kritis (tidak bisa tap luar + ESC):**
- Expired modal
- Delete account
- Rollback modal
- Force gate modal
- Close cashier modal (immutable)
- MFA reset modal
- **Payment modal POS**
- **QRIS confirmation modal**

**Size:** sm (400px), md (600px), lg (800px), xl (1000px).

### 4.5 Toast

- Bottom (mobile) / top-right (desktop)
- Auto 3–5 detik
- Varian: success/error/info/warning
- Max 1
- `aria-live="polite"`
- Export toast punya tombol Download
- Immutable toast: "Data telah dicatat (immutable)"

### 4.6 Navigasi

**Merchant Mobile (<640px):**
Bottom tab (maks 5): Dashboard, POS, Laporan, Produk, Pengaturan.

**Merchant Tablet (640–1024px):**
Sidebar kiri collapsible.

**Merchant Desktop (>1024px):**
Sidebar permanen + top bar. Env badge di header. Help menu.

**Merchant Sidebar (16 item):**
Dashboard, POS, Laporan, Produk (Kategori/Produk/Modifier), Inventori (Supplier/PO/Stock Movement), Customer, **Promosi**, Outlet, Tim, Device, Langganan, Pengaturan (Profil/POS/Kasir/E-Wallet/Tax Rate/Data Privacy/Cookie/Notifikasi).

**POS:**
- Tablet: 2 panel (kategori+grid+order)
- HP: single column (grid + bottom sheet order)
- Header: nama outlet, kasir, Buka/Tutup Kasir, env badge, notif low stock, ikon shift report, **Hold badge**, **Kas Keluar/Masuk button**

**Backoffice Sidebar (17 item):**
Dashboard, Tenant, Subscription, Feature Flag, Kuota, Support Access, Device, Partner, Audit Log, Export Job, Deletion Request, Support Ticket, Staff & Role, MFA Reset, Release Management [PROD].

### 4.7 List & Table

- Mobile: list card, bukan horizontal scroll
- Desktop: table header sticky
- Empty state wajib (ikon + teks + CTA)
- Backoffice: filter, sort, bulk action
- Pagination cursor-based; "Muat lebih banyak" mobile

### 4.8 Komponen Khusus POS (14 Komponen) 🆕

| Komponen | Perilaku |
|---|---|
| Product Grid | Grid kartu; Mode Grid (F&B) / SKU (retail) / Mode Meja (denah) |
| Category Sidebar | Tombol kategori kiri; collapsible; count per kategori |
| Order Panel | Item list, qty, catatan, modifier indent 16px, subtotal, diskon, pajak, service, loyalty redeem, total |
| Payment Modal | Metode, nominal, kembalian, Bayar. Tidak tap luar. Quick amount buttons |
| Numpad | Keyboard numerik kustom |
| Split Bill | Mode per produk; indikator sisa |
| **Split Rata** | Input jumlah orang → kalkulasi per orang |
| Receipt Preview | Preview struk + Cetak/Kirim/Selesai |
| Bill Button | Tombol Bill di header POS |
| Modifier Picker | Bottom sheet group tabs, checkbox/radio, max enforced, required indicator |
| Low Stock Alert | Badge header POS: "⚠ 3 produk low stock" |
| Shift Report Button | Tombol "Tutup Kasir" → modal konfirmasi → generate shift report |
| Loyalty Redeem | Input poin, slider maks 50%, preview diskon |
| Customer Quick Add | Form nama + telepon di checkout |

**Payment Modal Enhanced:**

```
┌─────────────────────────────────────────────────┐
│  Pembayaran                              [×]    │
├─────────────────────────────────────────────────┤
│                                                 │
│  Total Tagihan                                  │
│  Rp 61.050                                      │
│                                                 │
│  Metode Pembayaran                              │
│  ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐          │
│  │ 💵   │ │ 📱   │ │ 🏦   │ │ ⋯    │          │
│  │Tunai │ │ QRIS │ │Transf│ │Lainnya│         │
│  └──────┘ └──────┘ └──────┘ └──────┘          │
│                                                 │
│  Uang Diterima                                  │
│  ┌─────────────────────────────────────────┐   │
│  │  Rp 100.000                             │   │
│  └─────────────────────────────────────────┘   │
│                                                 │
│  Quick Amount                                   │
│  [Uang Pas] [50rb] [100rb] [150rb] [200rb]    │
│                                                 │
│  ┌─────────────────────────────────────────┐   │
│  │  [7]  [8]  [9]  [⌫]                    │   │
│  │  [4]  [5]  [6]  [000]                  │   │
│  │  [1]  [2]  [3]  [00]                   │   │
│  │  [0]  [.]   [C]  [0]                   │   │
│  └─────────────────────────────────────────┘   │
│                                                 │
│  Kembalian                                      │
│  ┌─────────────────────────────────────────┐   │
│  │  Rp 38.950                              │   │
│  └─────────────────────────────────────────┘   │
│                                                 │
│  ┌─────────────────────────────────────────┐   │
│  │         [ KONFIRMASI PEMBAYARAN ]       │   │
│  └─────────────────────────────────────────┘   │
│                                                 │
│  [ Split Bill ]  [ Split Rata ]  [ Kasbon ]    │
└─────────────────────────────────────────────────┘
```

**QRIS Statis Modal:**

```
┌─────────────────────────────────┐
│  QRIS — Toko Budi        [×]   │
├─────────────────────────────────┤
│  ┌─────────────────────────┐   │
│  │                         │   │
│  │    [QR CODE STATIS]     │   │  ← QR code dari merchant
│  │    (200×200px)          │   │
│  │                         │   │
│  └─────────────────────────┘   │
│                                 │
│  Total: Rp 61.050              │
│                                 │
│  Customer scan QR dari HP,      │
│  bayar via e-wallet/bank.       │
│                                 │
│  ┌─────────────────────────┐   │
│  │  [ Sudah Diterima ]     │   │  ← Primary button
│  └─────────────────────────┘   │
│  ┌─────────────────────────┐   │
│  │  [ Belum / Batal ]      │   │  ← Ghost button
│  └─────────────────────────┘   │
│                                 │
│  ⚠ Konfirmasi manual.           │
│  Pastikan pembayaran diterima.  │
└─────────────────────────────────┘
```

**Hold Order Badge & List:**

```
Badge di header POS:
┌─────────────────┐
│ ⏸ Hold: 3       │  ← Badge dengan count
└─────────────────┘

Hold List (klik badge):
┌─────────────────────────────────────────┐
│  Order Hold (3)                  [×]    │
├─────────────────────────────────────────┤
│  #0124 · 14:32 · Rp 25.000 · Andi     │
│  [ Resume ]  [ Void ]                   │
│                                         │
│  #0125 · 14:45 · Rp 48.000 · Budi     │
│  [ Resume ]  [ Void ]                   │
│                                         │
│  #0126 · 15:02 · Rp 18.000 · Andi     │
│  [ Resume ]  [ Void ]                   │
│                                         │
│  ⚠ Maks 10 hold. Selesaikan sebelum    │
│    tutup kasir.                         │
└─────────────────────────────────────────┘
```

**Kas Keluar/Masuk Modal:**

```
┌─────────────────────────────────────────┐
│  Kas Keluar                      [×]    │
├─────────────────────────────────────────┤
│                                         │
│  Nominal                                │
│  ┌─────────────────────────────────┐   │
│  │  Rp 50.000                      │   │
│  └─────────────────────────────────┘   │
│                                         │
│  Alasan (wajib, min 10 karakter)        │
│  ┌─────────────────────────────────┐   │
│  │  Beli es batu untuk minuman     │   │
│  └─────────────────────────────────┘   │
│                                         │
│  ┌─────────────────────────────────┐   │
│  │         [ Konfirmasi ]          │   │
│  └─────────────────────────────────┘   │
│                                         │
└─────────────────────────────────────────┘
```

**Kasbon Checkout (di Payment Modal):**

```
┌─────────────────────────────────────────┐
│  Pembayaran — Kasbon             [×]    │
├─────────────────────────────────────────┤
│                                         │
│  Customer: Budi Santoso                 │
│  Limit: Rp 500.000                      │
│  Sudah dipakai: Rp 400.000              │
│  Sisa limit: Rp 100.000                │
│                                         │
│  Total Tagihan: Rp 61.050              │
│                                         │
│  ✅ Limit cukup                         │
│                                         │
│  ┌─────────────────────────────────┐   │
│  │      [ Konfirmasi Kasbon ]      │   │
│  └─────────────────────────────────┘   │
│                                         │
│  ⚠ Customer harus bayar dalam 30 hari  │
└─────────────────────────────────────────┘
```

### 4.9 Komponen Khusus Langganan
| Komponen | Perilaku |
|---|---|
| Trial Banner | Sticky "Trial berakhir X hari — Pilih Paket". Warning; H-1 danger |
| Expired Banner | Sticky danger; tidak dismiss sampai pilih paket |
| Offline Banner | Sticky saat navigator.onLine === false |
| Plan Comparison Table | Tabel fitur × paket; mobile card vertikal |
| Locked Feature Overlay | Overlay locked + "Tersedia di paket [nama]" + CTA |
| Quota Meter | Progress bar: success (<80%), warning (80–99%), danger (100%) |
| Upgrade Modal | Perbandingan paket + biaya |
| Feature Flag Badge | Backoffice "Override" |
| Billing Cycle Toggle | Bulanan/Tahunan (~17% diskon) |
| Grace Period Banner | Sticky warning dismissible 24 jam |
| Add-on Card | Card add-on (POS/karyawan/admin) + tombol "Beli" |

**Quota Meter Detail:**
Progress per resource (POS: 2/5, Karyawan: 5/20, Admin: 1/5).

### 4.10 Komponen Khusus Backoffice

| Komponen | Perilaku |
|---|---|
| Tenant Table | Nama, email, plan, status trial, tanggal daftar, aksi |
| Tenant Detail Drawer | Ringkasan, langganan, perangkat, kuota, aksi |
| Support Access Banner | Oranye: "Mode Support — [tenant] — 28:14 — Keluar" |
| Audit Log Table | Immutable, filter, export. Badge immutable |
| Quota Card | Progress penggunaan vs limit |
| Feature Flag Toggle | Daftar + switch + alasan; audit |
| Confirmation Modal | Suspend/override: alasan + konfirmasi teks |
| Export Job Monitoring | Status, file size, expires_at, download |
| Deletion Request View | Status, tombol Cancel restore |
| Support Ticket Inbox | Nomor, tenant, subjek, status, SLA, assign |
| Dashboard Ringkas | Tenant baru, trial, konversi, suspend, distribusi, flag |
| MFA Setup Modal | QR TOTP + verifikasi + recovery codes |
| MFA Reset Modal | Re-auth + alasan + konfirmasi |

### 4.11 Komponen Khusus Support

- Help Menu
- In-App Chat Widget (bottom-right/full-screen mobile)
- Chat Launcher (56×56px)
- KB Link
- Status Page Link
- Support Access Banner
- Support Write Escalation Banner (merchant approve 15 menit)

**Aturan:** Banner dua sisi (admin + merchant).

### 4.12 Komponen Khusus Compliance

- Cookie Consent Banner
- Cookie Settings Modal
- Consent Checkbox (tidak pre-checked)
- Privacy/Terms/Cookies page
- Export Data Card+Modal
- Delete Account Card+Modal
- Data Retention Info Card

### 4.13 Komponen Dev-First [MVP—F7]

**Environment Indicator:**
- Badge header (DEV/STAGING/PRODUCTION-TEST/PRODUCTION)
- Tooltip env+URL+version
- Z-index `--z-env-badge` (750)
- `role="status"` + `aria-label`
- Wajib di merchant, POS, backoffice, footer landing
- Production merchant bersih; production backoffice PRODUCTION hijau

**Demo Mode Banner:**
- Tipis di bawah header env dev/staging/prod-test
- "Anda berada di environment [X] — data demo."
- Dismissible 24 jam
- Tidak di production

**Version Indicator Footer:**
- Backoffice: version+commit+env
- Merchant: version+changelog
- Landing: version+env

**Seed Data Warning:**
- Login dev/staging: "Gunakan akun demo"

### 4.14 Komponen Production Readiness [PROD]

**Release Gate Dashboard (`/admin/release-gates`):**
- Summary card (progress bar, BLOCKED/READY, last updated)
- 10 gate cards (code, nama, status badge, criteria, evidence, reviewer)
- Tombol: Mark PASS/FAIL, Rerun Check, View Details
- Force Gate: Super Admin+password+TOTP+alasan→audit; badge FORCED
- Polling 30 detik

**Cutover Checklist (`/admin/cutover`):**
- Prerequisite banner merah jika gate belum PASS
- 10 item sequential; #1&#2 prerequisite
- Progress bar
- Tombol: Start (disabled sampai #1+#2), Abort, Complete, Rollback

**Deploy History & Rollback (`/admin/deploy-history`):**
- Table (version, env, status, health, deployed by, commit)
- Rollback: modal → target versi + alasan + konfirmasi ketik ROLLBACK + re-auth

**Production Status Card:**
- Version, uptime 30 hari, last deploy, health, error rate, queue failed
- Polling 30 detik

**Force Gate Modal:**
- Super Admin+password+TOTP+alasan+konfirmasi ketik "FORCE RG-X"

**Rollback Modal:**
- Target versi dropdown, trigger type, alasan, konfirmasi ketik ROLLBACK, re-auth, checkbox pemahaman

**Monitoring Dashboard:**
- 6 metric card (uptime, error rate, P95, queue fail, DB conn, Redis mem) + uptime 30 hari bar
- Threshold warna (success/warning/danger)

### 4.15 Komponen MFA (Internal)

**MFA Setup Flow:**
- 3 langkah (QR scan → verifikasi 6 digit → recovery codes)
- Modal/centered card
- Recovery codes 10 single-use
- Wajib download/copy sebelum selesai

**MFA Verify Flow:**
- Input 6 digit setelah login
- Fallback recovery code
- Lockout 5 percobaan per sesi

**MFA Reset Modal:**
- Re-auth password + TOTP + alasan + konfirmasi ketik "RESET MFA" + checkbox pemahaman
- Hanya Super Admin
- Revoke all sessions setelah reset


### 4.16 Komponen Setup Awal Owner 🆕

**Setup Checklist Card:**

```
┌─────────────────────────────────────────────────────────────┐
│  👋 Selamat datang di Kasirku!                              │
│                                                             │
│  Selesaikan setup tokomu untuk mulai jualan:                │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  ① Outlet                 [Selesai ✅]              │   │
│  │    Outlet pertama dibuat otomatis saat onboarding   │   │
│  ├─────────────────────────────────────────────────────┤   │
│  │  ② Produk                 [Belum] → [Mulai]        │   │
│  │     Tambahkan minimal 1 produk untuk mulai jualan   │   │
│  ├─────────────────────────────────────────────────────┤   │
│  │  ③ Informasi Karyawan     [Belum] → [Mulai]        │   │
│  │     Undang kasir pertama untuk mulai transaksi       │   │
│  ├─────────────────────────────────────────────────────┤   │
│  │  ④ Struk, Biaya & Pajak   [Belum] → [Mulai]        │   │
│  │     Pilih metode pajak dan kustomisasi struk         │   │
│  ├─────────────────────────────────────────────────────┤   │
│  │  ⑤ Pengaturan Kasir       [Belum] → [Mulai]        │   │
│  │     Kas awal default, batas diskon, izin kasir      │   │
│  ├─────────────────────────────────────────────────────┤   │
│  │  ⑥ Pengaturan Perangkat   [Belum] → [Mulai]        │   │
│  │     Aktifkan tablet/HP sebagai device POS            │   │
│  ├─────────────────────────────────────────────────────┤   │
│  │  ⑦ Pengaturan E-Wallet    [Belum] → [Mulai]        │   │
│  │     Upload QR code QRIS, tambah e-wallet            │   │
│  ├─────────────────────────────────────────────────────┤   │
│  │  ⑧ Data Pelanggan         [Belum] → [Mulai]        │   │
│  │     Aktifkan CRM & loyalty point                    │   │
│  ├─────────────────────────────────────────────────────┤   │
│  │  ⑨ Promosi                [Belum] → [Mulai]        │   │
│  │     Buat voucher atau diskon terjadwal pertama       │   │
│  ├─────────────────────────────────────────────────────┤   │
│  │  ⑩ Notifikasi             [Belum] → [Mulai]        │   │
│  │     Pilih notifikasi yang ingin diterima            │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
│  Progress: █░░░░░░░░░ 1/10                                 │
│                                                             │
│  [ Lewati, saya tahu caranya ]                              │
└─────────────────────────────────────────────────────────────┘
```

**Aturan Setup Checklist:**
- Tampil di dashboard sampai semua langkah selesai atau di-dismiss
- Progress bar: X/10 selesai
- Setiap langkah punya CTA langsung ke halaman setup
- Tombol "Lewati, saya tahu caranya" untuk dismiss seluruh checklist
- Checklist tidak blocking — owner bisa langsung pakai POS tanpa menyelesaikan semua
- Setiap langkah selesai → toast "✅ [Nama langkah] selesai"
- Semua selesai → toast "🎉 Setup selesai! Mulai jualan sekarang" + checklist hilang
- Analytics: setup_checklist_step_completed, setup_checklist_dismissed

### 4.17 Komponen Login POS PIN-Only 🆕

**Grid Karyawan:**

```
┌─────────────────────────────────────────────────────────────┐
│  Pilih Kasir                                                │
│  Toko Budi · Cabang A                                       │
│                                                             │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐  │
│  │  👤      │  │  👤      │  │  👤      │  │  ＋      │  │
│  │  Andi    │  │  Budi    │  │  Cici    │  │  Kasir   │  │
│  │          │  │          │  │          │  │  Baru    │  │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘  │
│                                                             │
│  ┌──────────┐  ┌──────────┐                                │
│  │  👤      │  │  👤      │                                │
│  │  Dedi    │  │  Eka     │                                │
│  └──────────┘  └──────────┘                                │
│                                                             │
│  [ ← Kembali ke Dashboard ]                                │
└─────────────────────────────────────────────────────────────┘
```

**PIN Pad:**

```
┌─────────────────────────────────────────────────────────────┐
│  Masuk sebagai Andi                                         │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  ● ● ● ○ ○ ○                                       │   │  ← 6 digit PIN
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐   │
│  │                                                     │   │
│  │     [1]     [2]     [3]                             │   │
│  │     [4]     [5]     [6]                             │   │  ← Tombol 64×64px
│  │     [7]     [8]     [9]                             │   │     min 48×48px
│  │     [⌫]     [0]     [C]                             │   │
│  │                                                     │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
│  [ ← Ganti Kasir ]                                         │
│                                                             │
│  ⚠ 3 percobaan lagi sebelum lockout                        │
└─────────────────────────────────────────────────────────────┘
```

**Aturan PIN Pad:**
- Tombol angka 64×64px (min 48×48px)
- Feedback visual saat tekan: scale 0.95 + ripple
- PIN dot: filled circle untuk digit terisi, empty circle untuk belum
- Error: shake animation 300ms + toast "PIN salah"
- Lockout: overlay "PIN terkunci 15 menit" + countdown
- Success: transition ke POS ≤5 detik

### 4.18 Komponen Chart 🆕

| Chart Type | Penggunaan | Style |
|---|---|---|
| Line Chart | Penjualan harian/mingguan/bulanan | Gradient fill di bawah line, dot markers, tooltip |
| Bar Chart | Perbandingan produk terlaris, per outlet | Rounded corners (4px), hover highlight |
| Donut Chart | Metode pembayaran, kategori penjualan | Center text (total), legend di kanan |
| Sparkline | Trend mini di stat cards | 60×24px, tanpa axis, gradient stroke |
| Heatmap | Jam sibuk, hari ramai | 7×24 grid, color scale dari bg → primary |
| Progress Ring | Kuota, target pencapaian | Animated fill, center percentage |

**Chart style rules:**
- Semua warna dari `--chart-color-*` token
- Grid lines dari `--chart-grid-color`
- Tooltip: dark background, white text, radius sm
- Animasi draw: 600ms ease-out
- Hover: highlight + tooltip
- Responsive: resize otomatis
- Accessibility: `role="img"` + `aria-label`

### 4.19 Komponen Pencarian Global 🆕

**Trigger:** Ctrl+K (desktop) atau tap ikon search (mobile)

```
┌─────────────────────────────────────────────────────────────┐
│  🔍 Cari produk, customer, order, PO...                     │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Produk                                                     │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ 🍚 Nasi Goreng — Rp 25.000 — Stok: 45              │   │
│  │ 🍜 Mie Goreng — Rp 15.000 — Stok: 30               │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
│  Customer                                                   │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ 👤 Budi Santoso — 0812-3456-7890 — 150 poin        │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
│  Order                                                      │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ #0124 — 12/11/2026 — Rp 61.050 — Paid              │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
│  [ESC] Tutup    [↑↓] Navigasi    [Enter] Buka              │
└─────────────────────────────────────────────────────────────┘
```

**Aturan:**
- Debounce 300ms
- Maks 5 hasil per kategori
- Hasil sesuai role (kasir tidak lihat PO/supplier)
- Keyboard navigation (↑↓ Enter Esc)
- Rate limit 60/menit/user

### 4.20 Komponen Promosi 🆕

**Voucher Card:**

```
┌─────────────────────────────────────────────────────────────┐
│  🎫 DISKON10                                    [Aktif]     │
│  Diskon 10% · Min. Rp 50.000 · Maks 100× pakai            │
│  Periode: 01/12/2026 — 31/12/2026                         │
│  Terpakai: 45/100                                          │
│  ████████████████████░░░░░░░░░░░░░░░░░░░░  45%            │
│  [ Edit ]  [ Nonaktifkan ]                                  │
└─────────────────────────────────────────────────────────────┘
```

**Voucher Redeem di POS:**

```
Di payment modal, di bawah total:
┌─────────────────────────────────────────────────────────────┐
│  Punya voucher?                                             │
│  ┌─────────────────────────────────────┐  [ Terapkan ]     │
│  │  DISKON10                           │                   │
│  └─────────────────────────────────────┘                   │
│                                                             │
│  ✅ Voucher DISKON10 diterapkan: -Rp 6.105                 │
└─────────────────────────────────────────────────────────────┘
```

---

## §5. State Wajib (24 State)

> Setiap halaman/data wajib punya state berikut:

| # | State | Tampilan |
|---|---|---|
| 1 | Loading | Skeleton/spinner; POS skeleton grid |
| 2 | Empty | Ikon + pesan + aksi; POS "Keranjang kosong" |
| 3 | Error | Pesan + "Coba lagi"; POS error pembayaran |
| 4 | Success | Konten tampil; POS struk sukses |
| 5 | Offline | Banner + read-only; POS blokir transaksi |
| 6 | Read-only (expired) | Banner merah + modal blocking |
| 7 | Locked (fitur terkunci) | Overlay lock + CTA upgrade |
| 8 | Exporting | Progress + Download setelah selesai |
| 9 | Deleting | Konfirmasi → loading → success |
| 10 | Demo-Mode | Banner demo tipis |
| 11 | Guard-Failed | Error 500 (§11) |
| 12 | Immutable-Confirm | Modal konfirmasi (shift close, stock adjustment) |
| 13 | Low-Stock-Alert | Badge + notifikasi in-app |
| 14 | Quota-Warning | Banner kuning ≥80% |
| 15 | Quota-Blocked | Modal "Kuota habis, tambah add-on" |
| 16 | Gate-Pending/Failed | Status gate [PROD] |
| 17 | Cutover-In-Progress | Banner maintenance + progress + Abort [PROD] |
| 18 | Rollback-In-Progress | Overlay loading + polling [PROD] |
| 19 | Production-Blocked | Cutover disabled + banner "X gates belum PASS" [PROD] |
| 20 | Re-auth-Required | Modal re-auth untuk aksi kritikal [PROD] |
| 21 | **Hold-Active** 🆕 | Badge "Hold: N" di header POS; list hold order |
| 22 | **Kasbon-Outstanding** 🆕 | Badge customer punya kasbon; reminder di dashboard |
| 23 | **Connection-Lost** 🆕 | Banner "Koneksi terputus. Cart tersimpan." + auto-sync |
| 24 | **Printer-Connected** 🆕 | Badge printer aktif di header POS; test print button |

**Aturan:**
- Tidak boleh ada halaman hanya state success
- POS wajib offline, read-only, hold-active, connection-lost
- Langganan wajib locked & expired
- Inventori wajib low-stock-alert
- CRM wajib quota-warning, kasbon-outstanding
- Backoffice wajib MFA + re-auth state

---

## §6. Layout & Responsive

### 6.1 Breakpoint

| Nama | Lebar | Target |
|---|---|---|
| sm | <640px | HP |
| md | 640–1024px | Tablet |
| lg | >1024px | Desktop |

### 6.2 Layout per Halaman

| Halaman | Mobile | Desktop |
|---|---|---|
| Landing | Single col, CTA full-width | Max 1200px center |
| Auth | Single col | Max 480px center |
| Onboarding | Single col, 4 langkah | Max 640px |
| **Setup Awal Owner** | **Single col, checklist card** | **Max 800px center** |
| Dashboard | Single col, card, bottom tab | 3 kolom + sidebar |
| POS Tablet | — | 2 panel (kategori 200px + grid 1fr + order 360px) |
| POS HP | Single col + bottom sheet | — |
| **Login POS** | **Grid karyawan + PIN pad** | **Sama** |
| Langganan | Card vertikal | 3–4 kolom + perbandingan |
| Expired | Banner + modal | Sama |
| Error | Centered 480px | Same |
| Backoffice | Drawer minimal | Sidebar 240px + konten |
| Legal | Max 800px anchor atas | Max 800px anchor kiri |
| Produk List | List card vertikal + FAB | Grid/table + filter |
| Modifier | List card vertikal | Table + form modal |
| Inventori | List card vertikal | Table + filter |
| CRM | List card vertikal | Table + detail drawer |
| Shift | Card vertikal + print | Table + detail modal |
| Laporan | Card vertikal, grafik full-width | Grid 6 card + grafik + table |
| **Promosi** | **Card vertikal** | **Table + form modal** |
| **Pencarian Global** | **Full-screen overlay** | **Modal 600px center** |
| Release Gate [PROD] | Card vertikal | Grid 2 kolom + summary |
| Cutover [PROD] | Card sequential | Table sequential + progress |

### 6.3 Safe Area

`env(safe-area-inset-*)` untuk: bottom tab, bottom sheet, cookie consent, env badge, modifier picker, shift close modal, PIN pad.

### 6.4 Aturan Responsive

- Mobile dites dulu
- Input font ≥16px
- No hover andalan
- `touch-action: manipulation`
- No horizontal scroll
- Table >5 kolom → list card
- Modifier picker full-screen mobile / modal 600px tablet
- Inventory table → list card
- Release gate dashboard card → grid
- **PIN pad: tombol 64×64px di tablet, 56×56px di mobile**
- **Setup checklist: card vertikal di mobile, 2 kolom di desktop**

---

## §7. Aksesibilitas & Motion

### 7.1 Aksesibilitas (WCAG 2.1 AA Basic)

| Aspek | Aturan |
|---|---|
| Kontras | Teks ≥4.5:1; UI ≥3:1 |
| Keyboard | Semua interaktif accessible |
| Focus | `:focus-visible` outline 2px |
| Form | Label + `aria-describedby` error + `aria-required` |
| Modal | Focus trap + `role="dialog"` + `aria-modal="true"` |
| Toast | `aria-live="polite"` |
| POS | Kontras tinggi, font besar |
| Locked feature | `aria-disabled` + teks alternatif |
| Backoffice | Skip to content link |
| Cookie consent | `role="region"` + `aria-label` |
| Support banner | `role="status"` + `aria-live` |
| Env badge | `role="status"` + `aria-label` |
| Demo banner | `role="region"` |
| Expired banner | `role="alert"` + `aria-live="polite"` (grace) / assertive (expired) |
| Expired modal | `role="alertdialog"` + focus trap |
| Immutable badge | `aria-label="Data immutable"` |
| Modifier picker | `role="dialog"` + focus trap + `aria-required` |
| Stock movement table | `role="table"` + `aria-rowcount` |
| Loyalty slider | `role="slider"` + `aria-valuemin/max/now` |
| Shift close modal | `role="alertdialog"` |
| Gate status | `aria-live="polite"` [PROD] |
| Cutover progress | `role="progressbar"` [PROD] |
| Re-auth modal | `role="dialog"` + auto-focus password |
| MFA QR | `aria-label="QR code MFA"` |
| Recovery codes | `role="list"` |
| **PIN pad** | **`role="group"` + `aria-label="PIN input"` + dots `aria-live`** |
| **Setup checklist** | **`role="list"` + progress `role="progressbar"`** |
| **Chart** | **`role="img"` + `aria-label` deskripsi data** |
| **Search global** | **`role="combobox"` + `aria-expanded` + `aria-activedescendant`** |
| **QRIS modal** | **`role="dialog"` + QR code `aria-label="QR code QRIS"`** |
| **Hold badge** | **`role="status"` + `aria-label="N order hold aktif"`** |

### 7.2 Motion & Animasi

Durasi 150–250ms; ease-out masuk, ease-in keluar; hormati `prefers-reduced-motion`.

### 7.3 34 Micro-interactions 🆕

| # | Interaction | Trigger | Animasi | Durasi |
|---|---|---|---|---|
| 1 | Add to cart | Tap product card | Product image "terbang" ke cart (translate + scale + fade) | 300ms |
| 2 | Cart badge pulse | Item added | Badge jumlah cart scale 1→1.3→1 | 200ms |
| 3 | Payment success | Konfirmasi bayar | Checkmark animation (SVG stroke draw) + confetti subtle | 500ms |
| 4 | Button press | Tap/click | Scale 0.97 + ripple effect | 150ms |
| 5 | Page transition | Navigate | Slide-left/right 200ms (mobile), fade 150ms (desktop) | 200ms |
| 6 | Modal open | Open modal | Scale 0.95→1 + fade-in + backdrop blur | 250ms |
| 7 | Toast slide | Show toast | Slide-up + fade-in, auto slide-down setelah 3s | 250ms |
| 8 | Skeleton shimmer | Loading | Gradient sweep left→right | 1.2s loop |
| 9 | Stat counter | Dashboard load | Number count-up animation | 600ms |
| 10 | Chart draw | Chart render | Line/bar animate dari 0 ke nilai | 600ms |
| 11 | Progress ring fill | Quota display | Stroke-dashoffset animate | 800ms |
| 12 | Swipe to delete | Swipe order item | Item slide-left + fade + collapse height | 250ms |
| 13 | Pull to refresh | Pull down mobile | Spinner rotate + snap-back | 300ms |
| 14 | Tab switch | Switch tab | Underline slide + content crossfade | 200ms |
| 15 | Toggle switch | Toggle ON/OFF | Thumb slide + track color transition | 200ms |
| 16 | Low stock pulse | Stock ≤ threshold | Badge pulse 1× | 400ms |
| 17 | Trial countdown | H-3, H-1 | Number flip animation | 300ms |
| 18 | **Hold order save** | Klik Hold | Cart fade-out + badge "Hold: N" pulse | 200ms |
| 19 | **Hold order resume** | Klik Resume | Cart fade-in dari badge | 200ms |
| 20 | **Kas keluar/masuk** | Konfirmasi | Toast slide-up + cash amount counter update | 200ms |
| 21 | **QRIS display** | Pilih QRIS | QR code fade-in + scale 0.9→1 | 300ms |
| 22 | **PIN input** | Tap angka | Dot fill animation + subtle haptic (mobile) | 100ms |
| 23 | **PIN error** | PIN salah | Shake animation 300ms + red flash | 300ms |
| 24 | **PIN success** | PIN benar | Green flash + transition ke POS | 200ms |
| 25 | **Switch user** | Klik Switch | Fade-out kasir lama → fade-in grid karyawan | 250ms |
| 26 | **Setup step complete** | Selesaikan step | Checkmark draw + progress bar animate | 300ms |
| 27 | **Voucher apply** | Terapkan voucher | Discount amount slide-in + total update | 200ms |
| 28 | **Search open** | Ctrl+K | Overlay fade-in + input auto-focus | 200ms |
| 29 | **Search result** | Hasil muncul | Stagger fade-in per kategori (50ms delay) | 200ms |
| 30 | **Connection lost** | navigator.onLine = false | Banner slide-down + cart save indicator | 300ms |
| 31 | **Connection restored** | navigator.onLine = true | Banner slide-up + sync spinner | 300ms |
| 32 | **Printer connected** | Pair berhasil | Badge printer fade-in + test print toast | 200ms |
| 33 | **Kasbon confirm** | Konfirmasi kasbon | Order status badge animate ke "credit" | 200ms |
| 34 | **Notification bell** | Notifikasi baru | Bell shake 1× + badge count pulse | 400ms |

**Aturan animasi:**
- POS: maks 100ms untuk aksi kritis (add to cart, bayar, PIN)
- Non-POS: 150-300ms
- Selalu hormati `prefers-reduced-motion`
- Jangan animasi yang blocking user action
- Gunakan `transform` dan `opacity` saja (GPU-accelerated)

---

## §8. Expired Subscription UX

### 8.1 Copy Final

| Kondisi | Banner | Tombol utama | Tombol sekunder |
|---|---|---|---|
| Trial habis | "Masa trial berakhir. Data aman. Pilih paket untuk lanjut." | "Pilih Paket" | "Hubungi Support" |
| Paid expired | "Masa berlangganan berakhir. Data aman. Perpanjang sekarang." | "Perpanjang Langganan" | "Hubungi Support" |
| Grace period | "Berakhir dalam X hari. Perpanjang sekarang." | "Perpanjang" | "Nanti" (24 jam) |
| Pending payment | "Pembayaran diverifikasi. Akses tulis aktif setelah Finance konfirmasi." | "Lihat Status" | "Hubungi Support" |
| Suspended (internal) | "Akun ditangguhkan. Hubungi support." | "Hubungi Support" | — |

### 8.2 Blocking Matrix per Fitur

| Halaman/Fitur | Expired | Grace | Pending |
|---|---|---|---|
| Dashboard | ✅ + banner | ✅ + kuning | ✅ + info |
| Langganan | ✅ wajib | ✅ | ✅ |
| POS | ❌ modal | ✅ + banner | ❌ modal |
| Produk CRUD | ❌ read-only | ✅ | ❌ read-only |
| Modifier CRUD | ❌ read-only | ✅ | ❌ |
| Inventori | ❌ read-only | ✅ | ❌ |
| CRM | ❌ read-only | ✅ | ❌ |
| Shift Report | ❌ diblokir | ✅ | ❌ |
| Laporan | ✅ read-only | ✅ | ✅ read-only |
| Pengaturan POS | ❌ read-only | ✅ | ❌ |
| Tax Rate CRUD | ❌ read-only | ✅ | ❌ |
| Profil | ✅ | ✅ | ✅ |
| Data & Privacy | ✅ (UU PDP) | ✅ | ✅ |
| Support/Help | ✅ | ✅ | ✅ |
| Login POS | ❌ ditolak | ✅ | ❌ |
| Export/Delete/Cookie | ✅ (UU PDP) | ✅ | ✅ |

**Aturan:** Fitur wajib akses saat expired (UU PDP): Data & Privacy, Export, Delete, Cookie, Profil, Support, Langganan.

### 8.3 Modal Blocking

Trigger: Aksi tulis diblokir (POS, produk, modifier, inventori, CRM, shift, pengaturan, tax rate).

```
┌─────────────────────────────────────────┐
│  ⛔ Masa berlangganan berakhir          │
├─────────────────────────────────────────┤
│  Data Anda aman tersimpan.              │
│  Perpanjang langganan untuk             │
│  melanjutkan.                           │
│                                         │
│  Fitur yang diblokir:                   │
│  • Transaksi POS                        │
│  • Tambah/edit produk                   │
│  • Generate shift report                │
│                                         │
│  [ Perpanjang Sekarang ]                │
│  [ Nanti ]                              │
└─────────────────────────────────────────┘
```

**Aturan:**
- Tidak bisa tap luar + ESC
- `role="alertdialog"` + `aria-modal`
- Z-index modal 300

### 8.4 Redirect Behavior

- Akses `/app/pos` saat expired → redirect `/app/subscription?from=pos&reason=expired`
- Middleware → 403 JSON `{code: 'SUBSCRIPTION_EXPIRED', redirect: '/app/subscription?reason=expired'}`
- Frontend tangkap → tampilkan modal blocking
- Akses `/app/inventory/*`, `/app/customers/*`, `/app/modifiers/*` → redirect dengan reason

### 8.5 Beda Visual

| Aspek | Trial habis | Paid expired | Grace | Pending |
|---|---|---|---|---|
| Banner | danger | danger | warning | info |
| Ikon | clock-off | alert | clock | loader |
| Tombol utama | Pilih Paket | Perpanjang | Perpanjang | Lihat Status |
| Dismissible | Tidak | Tidak | Ya (24 jam) | Ya (24 jam) |
| Modal blocking | Ya | Ya | Tidak | Ya |

### 8.6 State Machine UI

| Status | Banner | Badge | Warna |
|---|---|---|---|
| trial | "Trial berakhir X hari" | TRIAL | trial |
| pending_payment | "Pembayaran diverifikasi" | PENDING | info |
| active | Tidak ada | ACTIVE | success |
| grace | "Berakhir X hari" | GRACE | warning |
| expired | "Berlangganan berakhir" | EXPIRED | danger |
| archived | "Akun diarsipkan" | ARCHIVED | disabled |

### 8.7 Aksesibilitas

- Banner: `role="alert"` + `aria-live` (assertive expired, polite grace)
- Modal: `role="alertdialog"` + focus trap
- Tidak bisa ESC
- Grace dismissible dengan `aria-label`

---

## §9. Error Pages

### 9.1 Error 403 Feature Gated

**Copy:** "Fitur ini tersedia di paket [nama]. Upgrade untuk mengakses."

**Layout:** Centered card, ikon lock. Benefit fitur (3–4 bullet). Tombol "Lihat Paket" + "Kembali Dashboard". `role="alert"`.

```
┌─────────────────────────────────────┐
│           🔒                        │
│  Inventory Tracking                 │
│  tersedia di paket Starter.         │
│                                     │
│  Upgrade untuk mengakses:           │
│  ✓ Stock movement ledger            │
│  ✓ Purchase order                   │
│  ✓ Supplier management              │
│  ✓ Low stock alert                  │
│                                     │
│  [ Lihat Paket ]                    │
│  [ Kembali Dashboard ]              │
└─────────────────────────────────────┘
```

### 9.2 Error 403 RBAC (Backoffice)

"Tidak memiliki akses. Hubungi Super Admin." Tidak menampilkan detail role/permission.

### 9.3 Error 503 Read-only

"Mode read-only. Perpanjang untuk melanjutkan." Fallback redirect `/app/subscription`.

### 9.4 Error 500 Guard Failure

- **Ops/Developer:** halaman 500 full-width, monospace list guard gagal (nama env `***`, harapan)
- **Merchant:** 503 generik
- Audit: `env_guard_failed` + Slack + Sentry
- 10 rules

```
┌──────────────────────────────────────────────────────┐
│ ⛔ Production Guard Failed                            │
├──────────────────────────────────────────────────────┤
│  OTP_DRIVER = ***                                    │
│    Diharapkan = fonnte atau whatsapp                 │
│  MAIL_MAILER = ***                                   │
│    Diharapkan = ses atau smtp                        │
│  APP_DEBUG = ***                                     │
│    Diharapkan = false                                │
│                                                      │
│  [ Reload ]  [ Lihat Log ]                           │
└──────────────────────────────────────────────────────┘
```

### 9.5 Error 404 Not Found

Tenant isolation failure, resource tidak ada. "Halaman tidak ditemukan." Tombol "Kembali Dashboard" + "Hubungi Support". `role="alert"`.

### 9.6 Error Page Matrix

| Error | Trigger | Aksesibel |
|---|---|---|
| 403 Gated | Fitur luar paket | `role="alert"` |
| 403 RBAC | Admin tanpa izin | `role="alert"` |
| 503 Read-only | Expired fallback | `role="alert"` |
| 500 Guard | Guard rules gagal | `role="alert"` |
| 404 Not Found | Resource/tenant isolation | `role="alert"` |
| 429 Rate Limited | Terlalu banyak request | `role="alert"` |
| 503 Maintenance [PROD] | Maintenance window | `role="alert"` |
---

## §10. Onboarding & Setup Awal Owner

### 10.1 Onboarding 4 Langkah

| Step | Nama | Field | Opsional |
|---|---|---|---|
| 1 | Informasi Usaha | Nama pemilik, nama usaha, bidang, lama operasi | ❌ |
| 2 | Lokasi | Provinsi, kota, kecamatan (cascading dropdown) + **outlet pertama dibuat otomatis** | ❌ |
| 3 | Survei | "Bagaimana tahu Kasirku?" + "Jenis usaha?" | ✅ skip |
| 4 | Konfirmasi | Ringkasan + "Selesai & Mulai" | ❌ |

**Aturan baru:** Saat Step 2 (Lokasi), sistem otomatis membuat outlet pertama dengan nama = nama usaha dan alamat dari onboarding. Owner bisa edit outlet setelah onboarding.

### 10.2 Setup Awal Owner (10 Langkah) 🆕

Setelah onboarding 4 langkah selesai, owner masuk ke dashboard dan melihat **Setup Checklist** (detail di §4.16).

| # | Langkah | Konten | Wajib? | CTA |
|---|---|---|---|---|
| 1 | Outlet | Outlet pertama sudah dibuat otomatis. Owner bisa edit nama, alamat, timezone | ✅ Otomatis | "Edit Outlet" |
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
- Setiap langkah selesai → toast "✅ [Nama langkah] selesai"
- Semua selesai → toast "🎉 Setup selesai! Mulai jualan sekarang" + checklist hilang
- Analytics: setup_checklist_step_completed, setup_checklist_dismissed

---

## §11. Guard Failure UX

### 11.1 Apa yang Dilihat

| Role | Dilihat |
|---|---|
| Ops/Dev | Halaman 500 full-width, list guard (env `***` + harapan) |
| Merchant | Halaman 503 generik |
| Log | `env_guard_failed` + Slack + Sentry |

### 11.2 Behavior

- Guard di boot atau command check
- App tidak start sampai fixed
- `/up` return 503
- Banner backoffice "Production guard failed — check logs"
- Tidak bisa bypass
- Hanya di production

### 11.3 Design Treatment

**Halaman 500:**
- Background `--color-bg`
- Card putih border danger 2px
- Judul "Production guard failed" + ikon alert
- List monospace
- Tombol "Reload"/"Lihat Log"
- Tidak ada ilustrasi

**Halaman 503 (merchant):**
- Card border
- Judul "Layanan sedang tidak tersedia"
- Tombol "Coba Lagi"/"Status Page"
- Tidak menampilkan detail guard

### 11.4 Banner Backoffice

"⛔ Production guard failed — 5 rules violated" + tombol "Lihat Detail"/"Lihat Log". `role="alert"` + `aria-live="assertive"`. Tidak dismiss.

### 11.5 10 Guard Rules

APP_ENV, APP_DEBUG, MAIL_MAILER, OTP_DRIVER, TELESCOPE_ENABLED, SESSION_DRIVER, QUEUE_CONNECTION, kredensial provider, HTTPS, debug tools.

### 11.6 Log & Monitoring

Audit log, Slack webhook #incident, Sentry fatal, Uptime Kuma 503, GoAlert on-call.

### 11.7 Recovery

1. Ops buka halaman 500
2. Lihat guard gagal
3. Fix .env.production
4. Restart app
5. `/up` 200
6. Banner hilang
7. Audit tercatat

Tidak auto-fix.

---

## §12. Index Wireframe (104 Halaman)

### 12.1 Eksternal (62 Wireframe)

| # | Wireframe | Device | Kategori |
|---|---|---|---|
| 1 | Landing | Mobile+Desktop | Onboarding |
| 2 | Register Step 1 | Mobile | Onboarding |
| 3 | Register OTP | Mobile | Onboarding |
| 4 | Register Password | Mobile | Onboarding |
| 5 | Register Verifikasi Email | Mobile | Onboarding |
| 6 | Onboarding Wizard 1–4 | Mobile | Onboarding |
| 7 | **Setup Awal Owner Checklist** | Mobile+Desktop | **Setup** 🆕 |
| 8 | Login Dashboard | Desktop | Auth |
| 9 | **Login POS — Grid Karyawan** | Tablet | **Auth** 🆕 |
| 10 | **Login POS — PIN Pad** | Tablet | **Auth** 🆕 |
| 11 | Dashboard Owner | Mobile+Desktop | Dashboard |
| 12 | Produk List | Desktop | Produk |
| 13 | Produk Form Create/Edit | Desktop | Produk |
| 14 | Kategori List + Modal | Desktop | Produk |
| 15 | Varian Form | Desktop | Produk |
| 16 | Modifier List | Desktop | Modifier |
| 17 | Modifier Group Form | Desktop | Modifier |
| 18 | POS Modifier Picker | Tablet | Modifier |
| 19 | PO List | Desktop | Inventori |
| 20 | PO Detail | Desktop | Inventori |
| 21 | PO Receive | Desktop | Inventori |
| 22 | Stock Movement Timeline | Desktop | Inventori |
| 23 | Stock Adjustment Modal | Tablet | Inventori |
| 24 | POS Tablet Landscape | Tablet | POS |
| 25 | POS Mobile | Mobile | POS |
| 26 | POS Bottom Sheet Order | Mobile | POS |
| 27 | POS Payment Modal | Tablet | POS |
| 28 | POS Receipt Preview | Tablet | POS |
| 29 | Empty Cart POS | Mobile | POS |
| 30 | Loading POS Grid | Mobile | POS |
| 31 | **POS Hold Order List** | Tablet | **POS** 🆕 |
| 32 | **POS Kasbon Checkout** | Tablet | **POS** 🆕 |
| 33 | **POS Kas Keluar/Masuk Modal** | Tablet | **POS** 🆕 |
| 34 | **POS QRIS Statis Modal** | Tablet | **POS** 🆕 |
| 35 | **POS Split Rata Modal** | Tablet | **POS** 🆕 |
| 36 | **POS Refund Parsial** | Desktop | **POS** 🆕 |
| 37 | **POS Print Ulang Struk** | Tablet | **POS** 🆕 |
| 38 | **POS Koneksi Putus Banner** | Mobile+Tablet | **POS** 🆕 |
| 39 | **POS Switch User** | Tablet | **POS** 🆕 |
| 40 | Pengaturan POS — Tab Pajak | Desktop | Pengaturan |
| 41 | Pengaturan POS — Tab Biaya | Desktop | Pengaturan |
| 42 | Pengaturan POS — Tab Struk | Desktop | Pengaturan |
| 43 | Pengaturan POS — Tab Pembayaran | Desktop | Pengaturan |
| 44 | **Pengaturan Kasir** | Desktop | **Pengaturan** 🆕 |
| 45 | **Pengaturan E-Wallet** | Desktop | **Pengaturan** 🆕 |
| 46 | **Pengaturan Bluetooth Printer** | Desktop | **Pengaturan** 🆕 |
| 47 | **Pengaturan Notifikasi** | Desktop | **Pengaturan** 🆕 |
| 48 | Tax Rate List | Desktop | Settings |
| 49 | Tax Rate Form | Desktop | Settings |
| 50 | Tax Rate Picker di Produk | Desktop | Settings |
| 51 | Tim & Akses | Desktop | Tim |
| 52 | Outlet List + Form | Desktop | Outlet |
| 53 | Device POS List + Aktivasi Modal | Desktop | Device |
| 54 | Customer List | Desktop | CRM |
| 55 | Customer Detail | Desktop | CRM |
| 56 | Loyalty Redeem Checkout | Tablet | CRM |
| 57 | **Customer Kasbon Detail** | Desktop | **CRM** 🆕 |
| 58 | Shift Close Modal | Tablet | Shift |
| 59 | Shift Report Detail | Desktop | Shift |
| 60 | Shift Report List | Desktop | Shift |
| 61 | Laporan Penjualan | Desktop | Laporan |
| 62 | **Laporan Produk Terlaris** | Desktop | **Laporan** 🆕 |

### 12.2 Promosi & Pencarian (5 Wireframe) 🆕

| # | Wireframe | Device | Kategori |
|---|---|---|---|
| 63 | **Promosi — Voucher List** | Desktop | **Promosi** |
| 64 | **Promosi — Voucher Form** | Desktop | **Promosi** |
| 65 | **Promosi — Diskon Terjadwal Form** | Desktop | **Promosi** |
| 66 | **Promosi — Voucher Redeem di POS** | Tablet | **Promosi** |
| 67 | **Pencarian Global (Ctrl+K)** | Desktop+Mobile | **UX** |

### 12.3 Langganan & Compliance (7 Wireframe)

| # | Wireframe | Device | Kategori |
|---|---|---|---|
| 68 | Halaman Langganan | Desktop | Langganan |
| 69 | Expired State | Mobile | Langganan |
| 70 | Upgrade Modal | Tablet | Langganan |
| 71 | Profile Bisnis | Mobile | Profil |
| 72 | Data & Privacy | Mobile | Compliance |
| 73 | Delete Account Modal + Export Data Modal | Mobile | Compliance |
| 74 | Cookie Consent | Mobile | Compliance |

### 12.4 Internal (23 Wireframe)

| # | Wireframe | Device | Kategori |
|---|---|---|---|
| 75 | Backoffice Login | Desktop | Auth |
| 76 | MFA Setup Enrollment | Desktop | Auth |
| 77 | MFA Reset Modal | Desktop | Auth |
| 78 | Backoffice Dashboard | Desktop | Dashboard |
| 79 | Tenant List | Desktop | Tenant |
| 80 | Tenant Detail Drawer | Desktop | Tenant |
| 81 | Feature Flag Management | Desktop | Feature |
| 82 | Kuota & Add-on Management | Desktop | Kuota |
| 83 | Subscription & Billing Management | Desktop | Subscription |
| 84 | Device & Session Management | Desktop | Device |
| 85 | Support Access Banner | Desktop | Support |
| 86 | Support Tools | Desktop | Support |
| 87 | Support Ticket Inbox | Desktop | Support |
| 88 | Audit Log | Desktop | Audit |
| 89 | Export Job Monitoring | Desktop | Compliance |
| 90 | Deletion Request View | Desktop | Compliance |
| 91 | Partner Management | Desktop | Partner |
| 92 | Staff & Role Management | Desktop | Staff |
| 93 | Release Gate Dashboard | Desktop | Production [PROD] |
| 94 | Cutover Checklist | Desktop | Production [PROD] |
| 95 | Deploy History + Rollback | Desktop | Production [PROD] |
| 96 | Monitoring Dashboard | Desktop | Production [PROD] |
| 97 | Force Gate + Rollback Modal | Desktop | Production [PROD] |

### 12.5 State/Error (7 Wireframe)

| # | Wireframe | Device |
|---|---|---|
| 98 | Error 403 Feature Gated | Mobile |
| 99 | Error 403 RBAC | Backoffice |
| 100 | Error 503 Read-only | Mobile |
| 101 | Error 500 Guard Failure | Production |
| 102 | Error 404 Not Found | Mobile |
| 103 | Support Access Banner (Merchant) | Desktop |
| 104 | **Connection Lost Full Page** | Mobile+Tablet |

**Total: 104 wireframe** (62 eksternal + 5 promosi/pencarian + 7 langganan/compliance + 23 internal + 7 state/error).

### 12.6 Wireframe Kunci — POS Tablet Landscape dengan Fitur Baru

```
┌───────────────────────────────────────────────────────────────────────┐
│ ☰ Kasirku·Toko Budi  🏪 Cabang A  🔔 3  👤 Andi  [STAGING]  [Kasir]│
│                              ⏸ Hold: 2  💰 Kas Keluar  🖨️ Printer   │
├──────────┬────────────────────────────────────────┬────────────────────┤
│          │  🔍 Cari produk...              [⊞][☰] │  Order #0124       │
│ KATEGORI │  ┌─────────┐ ┌─────────┐ ┌─────────┐  │  ──────────────    │
│          │  │  🍚     │ │  🍜     │ │  🥤     │  │                    │
│ ● Semua  │  │ [IMAGE] │ │ [IMAGE] │ │ [IMAGE] │  │  Nasi Goreng  ×1  │
│   12     │  │         │ │         │ │         │  │  Rp 25.000   [✕]  │
│ ○ Makanan│  │ Nasi    │ │ Mie     │ │ Es Teh  │  │   ├─ Pedas 🔥     │
│   8      │  │ Goreng  │ │ Ayam    │ │ Manis   │  │   └─ +Telur +5rb  │
│ ○ Minuman│  │ ★ 4.8   │ │ ★ 4.5   │ │         │  │                    │
│   6      │  │ Rp25rb  │ │ Rp15rb  │ │ Rp8rb   │  │  Mie Ayam     ×2  │
│ ○ Snack  │  │ [Sisa 5]│ │         │ │         │  │  Rp 30.000   [✕]  │
│   3      │  └─────────┘ └─────────┘ └─────────┘  │   └─ +Keju +3rb   │
│ ○ Diskon │  ┌─────────┐ ┌─────────┐ ┌─────────┐  │  ──────────────    │
│   2      │  │  🍔     │ │  🍟     │ │  🍰     │  │  Subtotal Rp55.000 │
│          │  │ [IMAGE] │ │ [IMAGE] │ │ [IMAGE] │  │  Diskon   -Rp    0 │
│ ──────── │  │ Burger  │ │ Kentang │ │ Kue     │  │  Voucher  -Rp    0 │
│ + Kategori│  │ Rp30rb  │ │ Rp12rb  │ │ Rp15rb  │  │  Pajak 11% Rp6.050│
│          │  │         │ │         │ │         │  │  Service  Rp    0 │
│          │  └─────────┘ └─────────┘ └─────────┘  │  ──────────────    │
│          │                                        │  TOTAL   Rp61.050  │
│          │  ┌──────────────────────────────────┐  │                    │
│          │  │  [1][2][3][4][5][6][7][8][9][0] │  │  👤 [Pilih Customer]│
│          │  │  [Clear]  [Del]  [+ Qty]  [- Qty]│  │  🎁 [Poin: 150]    │
│          │  └──────────────────────────────────┘  │  🎫 [Punya Voucher?]│
│          │                                        │                    │
│          │                                        │  [ BAYAR Rp61.050 ]│
│          │                                        │  [Split][Rata][⋮]  │
└──────────┴────────────────────────────────────────┴────────────────────┘
```

### 12.7 Wireframe Kunci — Dashboard dengan 8 Widget

```
┌─────────────────────────────────────────────────────────────────────┐
│  Dashboard                                    📅 [7 Hari ▼]  🔔  👤 │
├─────────────────────────────────────────────────────────────────────┤
│  ⚠ Trial: 12 hari lagi  [Pilih Paket]                              │
│                                                                     │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │  💰 Ringkasan Penjualan                    [Harian ▼]       │   │
│  │  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐      │   │
│  │  │ Omzet    │ │Transaksi │ │ Rata-rata│ │ Pelanggan│      │   │
│  │  │Rp 2.45jt │ │   124    │ │Rp 19.7rb │ │   38     │      │   │
│  │  │ ↑12.5%  │ │  ↑8.2%   │ │  ↑3.1%   │ │  ↑5.4%   │      │   │
│  │  │ ▁▃▅▇▅▃▅ │ │ ▃▅▇▅▃▅▇ │ │ ▅▃▅▇▅▃▅ │ │ ▁▃▅▇▅▃▅ │      │   │
│  │  └──────────┘ └──────────┘ └──────────┘ └──────────┘      │   │
│  └─────────────────────────────────────────────────────────────┘   │
│                                                                     │
│  ┌──────────────────────────────┐ ┌──────────────────────────────┐ │
│  │  📈 Grafik Penjualan         │ │  🕐 Jam Sibuk (Heatmap)      │ │
│  │  [Line chart interaktif]     │ │  ┌──────────────────────┐    │ │
│  │  ╭─────────────────────╮     │ │  │ 08 10 12 14 16 18 20│    │ │
│  │  │    ╱╲   ╱╲         │     │ │  │ Sen ░░░░████████░░░░│    │ │
│  │  │   ╱  ╲ ╱  ╲  ╱╲   │     │ │  │ Sel ░░░██████████░░░│    │ │
│  │  │  ╱    ╳    ╲╱  ╲  │     │ │  │ Rab ░░░░████████░░░░│    │ │
│  │  │ ╱          ╱    ╲ │     │ │  │ Kam ░░████████████░░│    │ │
│  │  ╰─────────────────────╯     │ │  │ Jum ░░░░████████░░░░│    │ │
│  │  Sen Sel Rab Kam Jum Sab Min │ │  │ Sab ░░████████████░░│    │ │
│  │  [Omzet] [Transaksi] [Profit]│ │  │ Min ░░░░░░████░░░░░░│    │ │
│  └──────────────────────────────┘ │  └──────────────────────┘    │ │
│                                   │  Puncak: Sabtu 12:00-14:00    │ │
│                                   └──────────────────────────────┘ │
│                                                                     │
│  ┌──────────────────────────────┐ ┌──────────────────────────────┐ │
│  │  🏆 Produk Terlaris          │ │  💳 Metode Pembayaran        │ │
│  │  1. Nasi Goreng     245 porsi│ │    Tunai        ████████ 62%│ │
│  │  2. Mie Ayam        198 porsi│ │    QRIS         ████     28%│ │
│  │  3. Es Teh Manis    187 porsi│ │    Transfer     █        8% │ │
│  │  4. Ayam Geprek     156 porsi│ │    Kasbon       ░        2% │ │
│  │  5. Nasi Uduk       134 porsi│ │  [Donut chart]               │ │
│  │  [Lihat Semua →]             │ │                              │ │
│  └──────────────────────────────┘ └──────────────────────────────┘ │
│                                                                     │
│  ┌──────────────────────────────┐ ┌──────────────────────────────┐ │
│  │  ⚡ Transaksi Terakhir       │ │  ⚠️ Perlu Perhatian          │ │
│  │  #0124 · 14:32 · Rp 25rb    │ │  🔴 Low Stock (3 produk)     │ │
│  │  #0123 · 14:28 · Rp 48rb    │ │  🟡 Trial: 8 hari lagi       │ │
│  │  #0122 · 14:15 · Rp 18rb    │ │  🟠 Kasbon outstanding: 5    │ │
│  │  #0121 · 14:02 · Rp 67rb    │ │  📦 Hold order: 2            │ │
│  │  [Lihat Semua →]             │ │                              │ │
│  └──────────────────────────────┘ └──────────────────────────────┘ │
│                                                                     │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │  📊 Kuota Penggunaan                                         │  │
│  │  POS: ████████░░ 80% (4/5)   Karyawan: ██████░░░░ 60% (6/10)│  │
│  │  Admin: ██░░░░░░░░ 20% (1/5)                                 │  │
│  └──────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────┘
```

---

## §13. Data Visualization System 🆕

### 13.1 Chart Types

| Chart Type | Penggunaan | Library | Style |
|---|---|---|---|
| Line Chart | Penjualan harian/mingguan/bulanan | Canvas API vanilla atau Chart.js (CDN) | Gradient fill di bawah line, dot markers, tooltip |
| Bar Chart | Perbandingan produk terlaris, per outlet | Sama | Rounded corners (4px), hover highlight |
| Donut Chart | Metode pembayaran, kategori penjualan | Sama | Center text (total), legend di kanan |
| Sparkline | Trend mini di stat cards | Canvas API vanilla (ringan) | 60×24px, tanpa axis, gradient stroke |
| Heatmap | Jam sibuk, hari ramai | CSS Grid + color scale | 7×24 grid, color scale dari bg → primary |
| Progress Ring | Kuota, target pencapaian | SVG circle + stroke-dasharray | Animated fill, center percentage |

### 13.2 Chart Rules

- Semua warna dari `--chart-color-*` token
- Grid lines dari `--chart-grid-color`
- Tooltip: dark background, white text, radius sm
- Animasi draw: 600ms ease-out
- Hover: highlight + tooltip
- Responsive: resize otomatis
- Accessibility: `role="img"` + `aria-label`
- Data refresh: polling 30 detik untuk real-time
- Export: PNG download untuk semua chart

---

## §14. Receipt & Print Design 🆕

### 14.1 Receipt Template (58mm)

```
┌─────────────────────────────┐  ← 58mm width
│       TOKO BUDI             │  ← Header (center, bold, 14px)
│   Jl. Sudirman No. 123      │  ← Address (center, 10px)
│   Telp: 021-1234567         │
│─────────────────────────────│  ← Separator (dashed)
│ 12/11/2026 14:32            │  ← Date/time (left, 10px)
│ Kasir: Andi                 │
│ Order: #0124                │
│─────────────────────────────│
│ Nasi Goreng            x1   │  ← Item (left, 11px)
│   Rp 25.000                 │  ← Price (left indent, 10px)
│   + Pedas                   │  ← Modifier (left indent, 9px, italic)
│   + Telur (+Rp 5.000)       │
│ Mie Ayam               x2   │
│   Rp 30.000                 │
│─────────────────────────────│
│ Subtotal          Rp 55.000 │  ← Summary (right-aligned)
│ Pajak 11%         Rp  6.050 │
│ TOTAL             Rp 61.050 │  ← Bold, 12px
│ Tunai             Rp100.000 │
│ Kembali           Rp 38.950 │
│─────────────────────────────│
│  Terima kasih!              │  ← Footer (center, 10px)
│  Scan QR untuk loyalty      │
│  [QR CODE]                  │  ← Optional QR
│─────────────────────────────│
│  kasirku.id                 │  ← Powered by (center, 8px, muted)
└─────────────────────────────┘
```

### 14.2 Receipt Customization Options

| Element | Options |
|---|---|
| Header | Logo (none/small/center), nama toko, alamat, telepon |
| Body | Font size (10/11/12px), tampilkan modifier, tampilkan SKU |
| Summary | Tampilkan pajak detail, tampilkan diskon, tampilkan service charge |
| Footer | Pesan custom (max 3 baris), QR code (loyalty/feedback/Instagram), "Powered by Kasirku" |
| Paper | 58mm / 80mm, margin (narrow/normal/wide) |

### 14.3 Receipt Live Preview di Settings

```
┌──────────────────────────────────────────────────────┐
│  Kustomisasi Struk                                   │
├────────────────────────┬─────────────────────────────┤
│  Pengaturan            │  Preview                    │
│                        │  ┌─────────────────────┐   │
│  Header:               │  │    TOKO BUDI        │   │
│  [x] Logo              │  │  Jl. Sudirman 123   │   │
│  [x] Alamat            │  │─────────────────────│   │
│  [x] Telepon           │  │  12/11/2026 14:32   │   │
│                        │  │  Kasir: Andi        │   │
│  Body:                 │  │─────────────────────│   │
│  Font size: [11px ▼]   │  │  Nasi Goreng   x1   │   │
│  [x] Modifier          │  │   Rp 25.000         │   │
│  [ ] SKU               │  │   + Pedas           │   │
│                        │  │─────────────────────│   │
│  Footer:               │  │  TOTAL Rp 61.050    │   │
│  Pesan: [Terima kasih] │  │─────────────────────│   │
│  [x] QR Code           │  │  Terima kasih!      │   │
│  [x] Powered by        │  │  [QR]               │   │
│                        │  └─────────────────────┘   │
│  [ Simpan ] [ Reset ]  │  [ Cetak Test ]            │
└────────────────────────┴─────────────────────────────┘
```

---

## §15. Mobile Gesture System 🆕

### 15.1 Gesture Definitions

| Gesture | Context | Aksi | Feedback |
|---|---|---|---|
| Swipe kiri | Order item di cart | Hapus item | Item slide-left + undo toast |
| Swipe kanan | Order item di cart | Edit qty/modifier | Modal edit terbuka |
| Long press | Product card di POS | Quick detail modal | Haptic + modal |
| Pull down | Dashboard/list | Refresh data | Spinner + snap-back |
| Swipe kiri/kanan | Tabs (kategori, settings) | Switch tab | Content slide |
| Double tap | Product image | Zoom image | Scale animation |
| Two-finger pinch | Product image zoom | Zoom in/out | Scale |
| Swipe down | Modal/bottom sheet | Close | Slide-down |
| Edge swipe kanan | Any page | Back navigation | Slide-right |

### 15.2 Gesture Rules

- Semua gesture harus punya alternatif button/tap (accessibility)
- Swipe threshold: 50px minimum
- Long press threshold: 500ms
- Haptic feedback: `navigator.vibrate(10)` untuk mobile
- Tidak ada gesture yang destructive tanpa konfirmasi
- `prefers-reduced-motion`: disable semua gesture animation

---

## §16. Environment Provider Matrix

| Fitur UI | Label | Local | Staging | Prod-test | Production |
|---|---|---|---|---|---|
| Email verifikasi | "Email" | Mailpit | Mailpit | SES | SES |
| OTP WhatsApp | "WhatsApp" | Log | Fake+1 no | Fonnte | Fonnte |
| OTP fallback | "Email" | Log | Fake | SES | SES |
| Chat | "Chat Support" | mailto | mailto | Chatwoot | Chatwoot |
| KB | "Knowledge Base" | Markdown | Markdown | BookStack | BookStack |
| Status | "Status Layanan" | — | Instatus | Uptime Kuma | Uptime Kuma |
| Error tracking | (tidak terlihat) | Log | Sentry | Sentry | GlitchTip |
| Storage export | (tidak terlihat) | local | Cloudflare R2 | MinIO | MinIO |
| On-call | (tidak terlihat) | — | Slack | GoAlert | GoAlert |

**Aturan:** User tidak boleh lihat nama provider — label UI konsisten. Semua provider via config/env.

---

## §17. Aturan Desain (72)

### Fondasi (1–10)

1. Semua nilai dari token — no hardcode
2. Komponen baru → daftar dulu
3. Semua state (default/hover/active/disabled/loading/error/focus)
4. Semua halaman: loading/empty/error/offline/read-only/locked/guard-failed/expired/onboarding/demo-mode
5. Mobile dulu
6. POS dites skenario nyata
7. Backoffice: cari tenant <10 detik
8. Langganan dites semua kondisi
9. Legal akses tanpa login
10. Cookie consent: sekali, bisa ditolak, aksesibel

### Compliance & Support (11–20)

11. Export & Delete di-review
12. Support access dua sisi, time-boxed
13. Compliance transparan
14. Chat disembunyikan saat POS aktif
15. Support banner `role="status"` + `aria-live`
16. Cookie z-consent di atas konten (kecuali env)
17. Harga plan final
18. Data Privacy accessible saat expired
19. Modal expired no tap luar
20. Modal Delete no tap luar

### Environment & Phase (21–30)

21. Env badge non-prod; production merchant bersih
22. Production backoffice badge hijau
23. Demo banner dev/staging — bukan prod
24. Env badge z di atas consent
25. Semua halaman MVP demoable staging
26. Tidak ada halaman hanya production
27. Production readiness ditandai [PROD]
28. Env indicator + demo mode ditandai [MVP—F7]
29. Tidak ada APK
30. `@media print` wajib struk & invoice

### Kualitas Visual (31–40)

31. Perubahan visual signifikan di DD Log
32. Konsistensi dua sisi
33. Aksesibilitas tidak dikorbankan
34. Fitur terkunci overlay + CTA
35. Banner sticky tidak ganggu POS
36. Environment-aware; label konsisten
37. Kontras ≥4.5:1
38. Jangan warna saja untuk status
39. POS: aksen hanya Bayar & total
40. `--color-locked` untuk overlay

### Fitur Baru (41–52)

41. 21 fitur gated → overlay lock + CTA
42. Data immutable → badge [IMMUTABLE] 🔒
43. Stock movement → timeline ledger
44. Modifier picker enforce required + max
45. Loyalty redeem maks 50% dengan slider
46. Shift close tampil variance + warning immutable
47. Release gate dashboard tampil 10 gate + evidence + reviewer
48. Cutover checklist sequential (#1+#2 prerequisite)
49. Rollback modal re-auth + konfirmasi ROLLBACK
50. Force gate: Super Admin+Ops; re-auth + alasan
51. MFA: 3 langkah setup; 10 recovery codes
52. Onboarding: 4 langkah; step 3 opsional

### Fitur Baru v2.0 (53–64) 🆕

53. Setup Awal Owner: 10 langkah checklist; tidak blocking; progress bar
54. Login POS: PIN-only; grid karyawan; PIN pad 6 digit; ≤5 detik
55. Switch User: tanpa logout penuh; hanya PIN kasir baru
56. Hold Order: maks 10; badge di header; wajib resolve sebelum tutup kasir
57. Kasbon: customer terdaftar; limit config; status credit; notifikasi owner
58. Kas Keluar/Masuk: alasan wajib min 10 char; immutable; tercatat di shift report
59. QRIS Statis: QR code merchant; konfirmasi manual; tidak ada verifikasi otomatis
60. Split Rata: min 2 orang max 20; sisa di orang terakhir
61. Refund Parsial: hanya owner/admin; per item + qty
62. Print Ulang: max_print_count; counter increment; audit
63. Koneksi Putus: cart di localStorage; auto-sync; banner offline
64. Bluetooth Printer: Web Bluetooth; fallback CSS print; test print

### Dashboard & Chart (65–68) 🆕

65. Dashboard: 8 widget; filter periode; sparkline di stat cards
66. Chart: semua warna dari token; animasi 600ms; responsive; accessible
67. Heatmap: 7×24 grid; color scale dari bg → primary
68. Laporan Produk Terlaris: bar chart horizontal; filter periode/outlet/kategori

### Promosi & Pencarian (69–72) 🆕

69. Voucher: card dengan progress bar penggunaan; redeem di POS payment modal
70. Diskon Terjadwal: otomatis diterapkan; badge "Promo aktif" di POS
71. Pencarian Global: Ctrl+K; debounce 300ms; maks 5 per kategori; keyboard navigation
72. Notifikasi Perubahan Data: toggle per kategori; default semua ON; tidak bisa matikan keamanan

---

## §18. Design Decisions Log (68 DD)

| # | Keputusan | Status |
|---|---|---|
| DD-001 | Mobile-first; POS tablet 2 panel 30/70 | ✅ |
| DD-002 | Warna primary #29B6B3 (teal) | ✅ |
| DD-003 | Font Inter + system fallback | ✅ |
| DD-004 | Cookie consent banner bawah; tidak pre-checked | ✅ |
| DD-005 | Trial banner sticky; tidak dismiss | ✅ |
| DD-006 | Expired: modal blocking + banner merah | ✅ |
| DD-007 | POS: 1 tombol primary per layar (Bayar) | ✅ |
| DD-008 | Backoffice sidebar permanen desktop | ✅ |
| DD-009 | Env badge non-prod wajib | ✅ |
| DD-010 | 21 fitur gated: overlay lock + CTA upgrade | ✅ |
| DD-011 | Data immutable: badge [IMMUTABLE] 🔒 | ✅ |
| DD-012 | Modifier picker: bottom sheet mobile / modal tablet | ✅ |
| DD-013 | Loyalty redeem: slider maks 50% grand_total | ✅ |
| DD-014 | Shift close: modal konfirmasi + warning immutable | ✅ |
| DD-015 | Release gate: 10 cards + evidence + reviewer | ✅ |
| DD-016 | Cutover: 10 item sequential; #1+#2 prerequisite | ✅ |
| DD-017 | Rollback: konfirmasi ketik ROLLBACK + re-auth | ✅ |
| DD-018 | Force gate: Super Admin+Ops; re-auth + alasan | ✅ |
| DD-019 | MFA: 3 langkah setup; 10 recovery codes | ✅ |
| DD-020 | Onboarding: 4 langkah; step 3 opsional | ✅ |
| DD-021 | Error 403 gated: benefit fitur + CTA upgrade | ✅ |
| DD-022 | Error 500 guard: monospace list untuk ops | ✅ |
| DD-023 | Cookie consent: INSERT only; versioning | ✅ |
| DD-024 | Support access: banner oranye dua sisi | ✅ |
| DD-025 | Support write escalation: merchant approve 15 menit | ✅ |
| DD-026 | Export: progress + download setelah selesai | ✅ |
| DD-027 | Delete account: konfirmasi ketik nama tenant | ✅ |
| DD-028 | Tax rate: CRUD list + assign ke produk | ✅ |
| DD-029 | Stock movement: timeline ledger; badge immutable | ✅ |
| DD-030 | Low stock: badge header POS + notifikasi | ✅ |
| DD-031 | POS product card: modifier badge ikon sliders | ✅ |
| DD-032 | Order panel: modifier indent 16px di bawah produk | ✅ |
| DD-033 | Split bill per produk: indikator sisa | ✅ |
| DD-034 | Payment modal: tidak bisa tap luar | ✅ |
| DD-035 | Receipt preview: Cetak/Kirim/Selesai | ✅ |
| DD-036 | Shift report: print 58mm/80mm | ✅ |
| DD-037 | Customer card: poin + total spent + kunjungan | ✅ |
| DD-038 | Loyalty config: earn/redeem/max% di settings | ✅ |
| DD-039 | Quota meter: progress bar per resource | ✅ |
| DD-040 | Plan card: 21 fitur expandable | ✅ |
| DD-041 | Billing cycle toggle: bulanan/tahunan | ✅ |
| DD-042 | Add-on card: POS/karyawan/admin | ✅ |
| DD-043 | Tenant table: nama, email, plan, status, aksi | ✅ |
| DD-044 | Tenant detail: drawer 480px | ✅ |
| DD-045 | Audit log: immutable; filter; export | ✅ |
| DD-046 | Export job monitoring: status + file size + expires | ✅ |
| DD-047 | Deletion request: status + cancel restore | ✅ |
| DD-048 | Support ticket inbox: nomor, tenant, subjek, SLA | ✅ |
| DD-049 | Partner management: list + referral + aktivasi | ✅ |
| DD-050 | Staff & role: 5 role internal; MFA wajib | ✅ |
| DD-051 | Release management: gate + cutover + deploy + rollback | ✅ |
| DD-052 | Monitoring dashboard: 6 metric card + uptime 30 hari | ✅ |
| DD-053 | Production status card: version + uptime + health | ✅ |
| DD-054 | Maintenance banner: kuning; H-3 komunikasi | ✅ |
| DD-055 | Smoke test visual: 20 checklist | ✅ |
| DD-056 | Go-live checklist: 15 item | ✅ |
| DD-057 | Post-live verification: 24 jam monitoring | ✅ |
| DD-058 | Maintenance windows: Minggu 02:00–04:00 WIB | ✅ |
| DD-059 | Gradient token untuk CTA utama dan hero | ✅ |
| DD-060 | Elevation 5 level untuk depth hierarchy | ✅ |
| DD-061 | Chart system: Canvas API vanilla; token-based colors | ✅ |
| DD-062 | Dashboard 8 widget dengan sparkline dan heatmap | ✅ |
| DD-063 | Login POS PIN-only: grid karyawan + PIN pad 6 digit | ✅ |
| DD-064 | Setup Awal Owner: 10 langkah checklist; tidak blocking | ✅ |
| DD-065 | Hold Order: badge di header POS; maks 10; wajib resolve | ✅ |
| DD-066 | QRIS Statis: QR code merchant; konfirmasi manual | ✅ |
| DD-067 | Kasbon: limit config; status credit; notifikasi owner | ✅ |
| DD-068 | 34 micro-interactions; hormati prefers-reduced-motion | ✅ |

---

## §19. Sign-off

### 19.1 Dokumen

- □ Semua section §1–§19 lengkap
- □ Token §2 dipakai konsisten di §4–§12
- □ 104 wireframe §12 mencakup 19 journey + production + backoffice
- □ 21 fitur gated punya wireframe atau overlay lock
- □ Tidak ada placeholder/TBD

### 19.2 Komponen & State

- □ 20 grup komponen §4 lengkap dengan state
- □ 24 state §5 tercakup wireframe
- □ Layout responsive §6 untuk semua halaman
- □ Aksesibilitas §7 diterapkan semua wireframe
- □ Motion §7.2–7.3 konsisten (34 micro-interactions)

### 19.3 UX Khusus

- □ Expired §8 (copy, blocking matrix, modal, redirect)
- □ Error §9 (403 gated, 403 RBAC, 503, 500, 404, 429, 503 maintenance)
- □ Onboarding §10 (4 langkah + Setup Awal Owner 10 langkah)
- □ Guard §11 (ops vs merchant, recovery)
- □ Data Visualization §13 (6 chart types)
- □ Receipt & Print §14 (template + customization + live preview)
- □ Mobile Gesture §15 (9 gestures)

### 19.4 Wireframe Eksternal (62)

- □ Onboarding 6
- □ Setup Awal Owner 1
- □ Auth 4 (login dashboard, login POS grid, login POS PIN, switch user)
- □ Dashboard 1
- □ Produk & Kategori 4
- □ Modifier 3
- □ Inventori 5
- □ POS 16 (termasuk hold, kasbon, kas keluar/masuk, QRIS, split rata, refund parsial, print ulang, koneksi putus)
- □ Pengaturan POS 8 (pajak, biaya, struk, pembayaran, kasir, e-wallet, printer, notifikasi)
- □ Tax Rate 3
- □ Tim & Akses 1
- □ Outlet 1
- □ Device 1
- □ CRM 4 (termasuk kasbon detail)
- □ Shift 3
- □ Laporan 2 (penjualan + produk terlaris)
- □ Promosi 4
- □ Pencarian Global 1
- □ Langganan 3
- □ Profile & Data 2
- □ Modal Compliance 1

### 19.5 Wireframe Internal (23)

- □ Auth 3 (login, MFA setup, MFA reset)
- □ Dashboard 1
- □ Tenant 2
- □ Feature & Kuota 2
- □ Subscription 1
- □ Device 1
- □ Support 4
- □ Compliance 2
- □ Partner 1
- □ Staff 1
- □ Production 5 [PROD]

### 19.6 Wireframe State/Error (7)

- □ 403 Gated, 403 RBAC, 503, 500, 404, Support Banner, Connection Lost

### 19.7 Stakeholder Approval

| Role | Nama | Tanggal |
|---|---|---|
| Product Manager | _________________ | ______ |
| Design Lead | _________________ | ______ |
| Engineering Lead | _________________ | ______ |
| Frontend Lead | _________________ | ______ |
| QA Lead | _________________ | ______ |
| Ops/SRE Lead | _________________ | ______ |
| Stakeholder / Investor | _________________ | ______ |

---

## Ringkasan Eksekutif

**Design v2.0** adalah panduan visual & UX final untuk Kasirku. Mencakup **104 wireframe** untuk 19 journey + production readiness + backoffice internal.

**Prinsip utama (26):** mobile-first, POS-first, konsisten dua sisi, jelas > cantik, aksesibel, transparan status langganan, snapshot immutability, fitur gated terlihat, ledger-based stock, data-rich dashboard, delightful micro-interactions, speed-first POS, guided setup.

**Design token (87):** Teal #29B6B3 (primary) dengan 8 gradient, 6 elevation, 3 glass, 6 module accent, 12 chart, 15 dark mode (prepared), 37 warna dasar.

**Komponen utama (20 grup):** tombol (10 varian), input, card (8 varian), modal, toast, navigasi, list/table, POS (14 komponen), langganan, backoffice, support, compliance, dev-first, production readiness, MFA, Setup Checklist, Chart, PIN Pad, QRIS Display, Hold Order Badge.

**State wajib (24):** 20 state dasar + hold-active, kasbon-outstanding, connection-lost, printer-connected.

**Micro-interactions (34):** add-to-cart, button ripple, payment success, page transitions, stat counter, chart draw, swipe gestures, PIN input, hold order, QRIS display, setup step complete, voucher apply, search, connection lost/restored, dan lainnya.

**Data Visualization (6 chart types):** Line, Bar, Donut, Sparkline, Heatmap, Progress Ring.

**Receipt & Print:** Template 58mm/80mm, customization options, live preview.

**Mobile Gestures (9):** swipe, long press, pull down, double tap, pinch, edge swipe.

**Aturan desain:** 72 aturan dalam 8 kategori.

**Design decisions:** 68 keputusan tercatat.

**Status:** ✅ Siap eksekusi. 104 wireframe, 26 prinsip, 72 aturan, 21 fitur gated, 87 token, 24 state, 34 micro-interactions, 6 chart types, 9 gestures.
