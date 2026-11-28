# ARCHITECTURE — KASIRKU v2.0

**Versi:** 2.0 | **Status:** Final | **Tanggal:** 2026-11-16

---

## Daftar Isi

```
§1   Overview & Prinsip (28)
§2   Stack (Frontend + Backend + DB + Infra + Tooling)
§3   Diagram Arsitektur
§4   Struktur Folder
§5   Adapter Pattern (8 Contracts)
§6   Auth & Session (PIN-only POS)
§7   Middleware & Routing (18 Middleware)
§8   Guard Rules Production
§9   Environment & Env Vars
§10  Analytics Pipeline (54 Events)
§11  Observability
§12  Test Strategy (50 E2E Flow)
§13  Deployment & CI/CD
§14  Aturan Arsitektur (68)
§15  ADR (88 Architecture Decision Records)
§16  Production Readiness Subsystem [PROD]
§17  Fitur Baru — Arsitektur Detail
§18  Verifikasi & Sign-off
```

---

## §1. Overview & Prinsip

### 1.1 Tentang Dokumen Ini

Architecture v2.0 adalah panduan teknis lengkap untuk membangun Kasirku — SaaS POS UMKM Indonesia.

- **Backend:** Laravel 13 + PHP 8.4 + MySQL 8.4 + Redis 7.4
- **Frontend:** HTML + CSS + JavaScript vanilla murni (ES modules) + Blade templating — tanpa React, Vue, Svelte, Angular, TypeScript, Tailwind, Inertia, Alpine, HTMX, atau Livewire
- **Staging:** Render.com + TiDB Cloud + Upstash + Cloudflare R2 + Instatus (100% tanpa CC)
- **Production:** VPS 8 GB Jakarta (IDCloudHost / Biznet Gio)

### 1.2 28 Prinsip Arsitektur

| # | Prinsip | Konsekuensi |
|---|---|---|
| 1 | Responsive web only | Tidak ada APK, wrapper, native bridge |
| 2 | Server = sumber kebenaran | Logic bisnis di backend; frontend render & interaksi saja |
| 3 | Session-based auth | Cookie HttpOnly + CSRF; session di Redis |
| 4 | Stateless request | State di cookie & Redis; mudah scale |
| 5 | Security by default | HTTPS, CSP, CSRF, OWASP, defense-in-depth |
| 6 | Compliance by default | UU PDP, consent, export, delete, retention |
| 7 | Support by default | Chat, status, KB, SLA, support access |
| 8 | Multi-tenant isolation | WHERE tenant_id = ? di semua query domain |
| 9 | Mobile-first responsive | HP → tablet POS → desktop backoffice |
| 10 | Trial = plan terpisah | is_trial_plan = TRUE, kuota unlimited |
| 11 | Future-ready | PWA, Web Push, payment gateway, offline POS tanpa rewrite |
| 12 | Free-first / open-source-first | Target MVP Rp0; staging tanpa CC |
| 13 | Environment separation | Lokal/staging tanpa PII; production Jakarta |
| 14 | Dev-first, Setup-at-End | Fitur lokal (F0–6) → staging (F7) → UAT (F8) → prod (F9) |
| 15 | MVP = Fase 0–8 | Berakhir saat Staging UAT Complete (M10) |
| 16 | Production Readiness = Fase 9 | Release Gate, Cutover, Rollback = §16 |
| 17 | Environment indicator + demo mode = Fase 7 | Safety-critical |
| 18 | Zero-surprise staging | Environment jelas via badge |
| 19 | Adapter Pattern | Semua layanan eksternal via interface + config/env |
| 20 | Guard Rules Production | Gagal start production jika driver dev aktif |
| 21 | Analytics by default | 54 events via queue, tanpa PII |
| 22 | Test wajib pakai fake adapter | Provider asli hanya smoke test production-test |
| 23 | Vanilla-first frontend | HTML + CSS + JS murni; tanpa framework SPA; render engine manual + event-driven |
| 24 | Low-latency POS rendering | DOM mutation presisi, debounced render, tanpa virtual DOM; target <100ms feedback POS |
| 25 | Speed-first POS login | Login PIN-only ≤5 detik; device-based session persistent 30 hari |
| 26 | Guided setup | Setup Awal Owner 10 langkah; checklist tracking di DB |
| 27 | Immutable ledger pattern | stock_movements, cash_movements, shift_reports, audit_logs, voucher_redemptions — INSERT only |
| 28 | Offline-resilient POS | Cart di localStorage saat koneksi putus; auto-sync saat online |

### 1.3 Referensi Kompetitor — Arsitektur yang Diadopsi

| Kompetitor | Yang Diadopsi |
|---|---|
| Majoo | Microservices, Golang + PHP, MySQL + MongoDB, Redis, RabbitMQ — referensi scale-up [POST]. Event-driven untuk analytics & notifikasi diadopsi. Setup wizard pasca-registrasi diadopsi |
| Moka POS | PHP + Vue.js — Vue tidak diadopsi; diganti vanilla JS. Adapter pattern untuk payment & OTP diadopsi. PIN-based staff lock diadopsi |
| Pawoon | Yii framework — diganti Laravel. API untuk mobile [POST] |
| Olsera | Web-based POS + Back Office, cloud-based — diadopsi dua sisi /app/* + /admin/*. 2-level inventory via stock movement ledger |
| PanelControl / ShelfOne | Vanilla JS POS, debounced render 80ms, renderNow(), zero-latency POS, granular listener — diadopsi render engine & low-latency |
| Square POS | Passcode-based staff login; quick amount buttons; switch employee tanpa logout |
| Toast | Employee PIN lock; split payment; hold order |

### 1.4 Ringkasan Eksekutif

Architecture v2.0 adalah panduan teknis final untuk Kasirku. Backend: Laravel 13 + PHP 8.4 + MySQL 8.4 + Redis 7.4. Frontend: HTML + CSS + JavaScript vanilla murni (ES modules) + Blade — tanpa React, Vue, TypeScript, Tailwind, Inertia, Alpine, HTMX, Livewire.

**Staging (tanpa CC):** Render.com (app + cron) + TiDB Cloud Serverless (MySQL-compatible) + Upstash (Redis) + Cloudflare R2 (storage) + Instatus.com (status page).

**Production:** VPS 8 GB Jakarta + 6 self-hosted tools (MinIO, Chatwoot, BookStack, Uptime Kuma, GoAlert, GlitchTip).

**Prinsip utama (28):** responsive web only, server = sumber kebenaran, session-based auth, multi-tenant isolation, adapter pattern, guard rules, vanilla-first frontend, low-latency POS rendering, speed-first POS login, guided setup, immutable ledger pattern, offline-resilient POS.

**Stack frontend:** Blade templating (server-side), vanilla JS ES modules (tanpa bundler), CSS murni dengan custom properties (design token), Fetch API untuk AJAX, partial route (X-Partial: true) untuk update DOM, navigator.sendBeacon untuk analytics, window.onerror + unhandledrejection untuk error tracking, Web Bluetooth API untuk printer thermal, localStorage untuk offline cart, Canvas API untuk chart, Touch events untuk gesture.

**Render engine:** global state object, render() debounced 80ms, renderNow() untuk aksi kritis POS, partial DOM update (bukan innerHTML seluruh halaman), event delegation.

**Middleware (18):** tenant, onboarded, role, mfa, audit, feature-gate (21 fitur), quota, read-only, consent, subscription-active, analytics, guard-check, ForceHttps [PROD], partial, device-active, pin-lockout, cashier-permission, hold-limit.

**Route group (5):** public, verify, merchant (9 middleware), backoffice (7 middleware), API/partial.

**Analytics:** 54 events, 3 kategori, consent gate, bot filter, IP hashed HMAC-SHA256, partition bulanan, client-side sendBeacon.

**Test:** 7 lapisan, 50 E2E flow, Playwright wajib + Vitest opsional, 12 larangan test, migration rollback test 80 tabel.

**Deployment:** CI 11 langkah, deploy vanilla assets (copy ES modules + minify CSS + HTTP/2 + CDN + cache busting), Docker Compose 3 konfigurasi, rollback procedure.

**Production readiness [PROD]:** Release Gate (RG-1–RG-10), Cutover Checklist (#1–#10), Deploy History, Rollback Modal (re-auth + konfirmasi ROLLBACK), Force Gate (re-auth + alasan), Monitoring Dashboard, 6 self-hosted tools, production success criteria 14 item.

**ADR:** 88 ADR. **Aturan:** 68 aturan.

**Status:** ✅ Siap eksekusi. 88 ADR, 68 aturan, 54 events, 7 lapisan test, 18 middleware, 5 route group, 100% terisi.

---

## §2. Stack (Final — Vanilla Frontend)

### 2.1 Frontend

| Aspek | Pilihan | Alasan |
|---|---|---|
| Templating | Laravel Blade | Server-side rendering; SEO-friendly landing |
| Bahasa | JavaScript vanilla (ES2020+, ES modules) | Tanpa framework; bundle kecil; kontrol penuh DOM |
| Styling | CSS murni + custom properties (design token) | Tanpa Tailwind; token dari Design v2.0 §2 |
| Build | Tanpa bundler untuk JS; Vite opsional untuk minify CSS | Zero build step; deploy sederhana |
| State management | Global state object + event-driven render | Pola PanelControl: render() debounced 80ms + renderNow() |
| Data fetching | Fetch API native | Tanpa Axios; promise-based; cukup untuk POS |
| Form | Native HTML form + vanilla JS validation + Laravel Form Request | Server-side validasi wajib |
| Komponen UI | Blade components + vanilla JS DOM helpers | Reusable partials; tanpa framework komponen |
| Ikon | Lucide via SVG sprite | Konsisten dengan Design v2.0 §3.5 |
| Analytics client | navigator.sendBeacon + fetch fallback | Non-blocking; tanpa library |
| Cookie consent | Vanilla JS custom (ringan) | Tanpa library eksternal |
| Chat | mailto: (MVP) → Chatwoot [PROD] | Adapter pattern |
| Print | CSS @media print + Web Bluetooth API | Struk & invoice; thermal printer |
| PDF | Browser print-to-PDF (MVP); DomPDF (backend, Should) | Tanpa library frontend |
| Offline storage | localStorage | Cart persistence saat koneksi putus |
| Bluetooth | Web Bluetooth API | Thermal printer 58mm/80mm (Chrome/Edge) |
| Chart | Canvas API vanilla | Dashboard charts tanpa library |
| Gesture | Touch events native | Swipe, long-press, pull-to-refresh |

**Aturan frontend:** Tidak ada React/Vue/Svelte/Angular/TypeScript/Tailwind/Inertia/Alpine/HTMX/Livewire; tidak ada bundler JS; CSS murni; Blade components; vanilla JS helpers (el(), on(), qs(), qsa(), patch()).

### 2.2 Backend

| Aspek | Pilihan |
|---|---|
| Bahasa | PHP 8.4 |
| Framework | Laravel 13.x |
| Templating | Blade |
| ORM | Eloquent + Query Builder |
| Auth | Sanctum SPA (session) |
| Session driver | Redis |
| Otorisasi | Policies + Gates + role column |
| Validasi | Form Request |
| Queue | Laravel Queue (Redis) |
| Scheduler | Laravel Task Scheduling |
| Logging | Monolog structured JSON |
| PDF | DomPDF / Snappy |
| Excel/CSV | Laravel Excel |
| Image | Intervention Image |
| Error tracking | Sentry free |
| Adapter pattern | Interface + Service Provider |
| Guard rules | AppServiceProvider::boot() |
| Analytics | AnalyticsService + TrackEvent job |
| Partial rendering | Blade fragment + X-Partial: true |
| Search | Eloquent LIKE + index (MVP); Meilisearch [POST] |
| Notification | Database notifications + polling (MVP); Web Push [POST] |

### 2.3 Database & Infrastruktur

| Aspek | Pilihan |
|---|---|
| Database | MySQL 8.4 LTS |
| Charset | utf8mb4 + utf8mb4_unicode_ci |
| UUID | CHAR(36) |
| Cache/Session/Queue | Redis 7.4 |
| Object storage | Local filesystem (MVP) → MinIO [PROD] |
| Container | Docker + Compose v2 |
| Web server | Nginx 1.28 |
| Hosting local | Docker Compose laptop |
| Hosting staging | Render.com + TiDB + Upstash + Cloudflare R2 |
| Hosting production | VPS 8 GB Jakarta |
| CDN | Cloudflare free |
| Analytics partition | PARTITION BY RANGE |

### 2.4 Tooling & CI/CD

| Aspek | Pilihan |
|---|---|
| Version control | Git + GitHub private |
| Branching | GitHub Flow + develop |
| Commit | Conventional Commits |
| CI/CD | GitHub Actions (11 langkah) |
| Code quality PHP | Pint + PHPStan level 6+ |
| Code quality JS | ESLint 9+ (flat config, vanilla) + Prettier |
| CSS quality | Stylelint |
| Testing backend | Pest |
| Testing frontend | Playwright (E2E wajib); Vitest (opsional) |
| Coverage | Pest --coverage --min=100 |
| Secret | GitHub Secrets + Doppler |
| Dependency update | Dependabot |
| Image scan | Trivy |
| Secret scan | gitleaks |

**Aturan tooling:** Tidak ada TypeScript compiler, tidak ada Vite React plugin, tidak ada Tailwind CLI.

---

## §3. Diagram Arsitektur

### 3.1 Diagram MVP (Vanilla)

```
┌─────────────────────────────────────────────────────┐
│  Browser — Responsive Web                           │
│  HTML + CSS (custom properties) + vanilla JS (ESM)  │
│  Blade-rendered + AJAX partial (fetch)              │
│  Env badge (DEV/STAGING) — Fase 7                   │
│  Analytics: navigator.sendBeacon                    │
│  Offline: localStorage cart                         │
│  Bluetooth: Web Bluetooth API (printer)             │
│  Chart: Canvas API                                  │
└────────────────────┬────────────────────────────────┘
                     │ HTTPS (HTML + fetch partial)
┌────────────────────▼────────────────────────────────┐
│  Nginx 1.28 — TLS, rate limit, static cache         │
└────────────────────┬────────────────────────────────┘
                     │
        ┌────────────┼─────────────┐
        │            │             │
┌───────▼──────┐ ┌───▼──────┐ ┌───▼──────┐
│ Laravel 13   │ │ Queue    │ │ Scheduler│
│ PHP 8.4      │ │ Worker   │ │ (cron)   │
│ Blade + API  │ │ Redis 7.4│ │ Trial    │
│ Guard + Adp  │ │(analytics│ │ Grace    │
│ 80 tables    │ │  events) │ │ Retention│
└──────┬───────┘ └───┬──────┘ └───┬──────┘
       │              │            │
       └──────────────┼────────────┘
                      │
        ┌─────────────┼─────────────┐
        │             │             │
┌───────▼──────┐ ┌───▼──────┐ ┌───▼──────┐
│ MySQL 8.4    │ │ Redis 7.4│ │ Local /  │
│ 80 tabel     │ │ session  │ │ R2       │
│ utf8mb4      │ │ queue    │ │ Storage  │
└──────────────┘ └──────────┘ └──────────┘
```

### 3.2 Diagram Staging (Render + TiDB + Upstash + R2)

```
┌─────────────────────────────────────────────────────┐
│  Browser                                            │
│  https://kasirku-staging.onrender.com               │
└────────────────────┬────────────────────────────────┘
                     │ HTTPS
┌────────────────────▼────────────────────────────────┐
│  Render.com (Free Tier)                             │
│  ┌─────────────┐ ┌─────────────┐ ┌──────────────┐  │
│  │ Web Service │ │ Worker      │ │ Cron Job     │  │
│  │ Laravel 13  │ │ queue:work  │ │ schedule:run │  │
│  │ PHP 8.4     │ │             │ │              │  │
│  └──────┬──────┘ └──────┬──────┘ └──────┬───────┘  │
└─────────┼───────────────┼───────────────┼───────────┘
          │               │               │
          └───────────────┼───────────────┘
                          │
        ┌─────────────────┼─────────────────┐
        │                 │                 │
┌───────▼───────┐ ┌───────▼───────┐ ┌──────▼──────┐
│ TiDB Cloud    │ │ Upstash       │ │ Cloudflare  │
│ Serverless    │ │ Redis         │ │ R2          │
│ (MySQL-comp)  │ │ 10K cmd/day   │ │ 10 GB       │
│ 5 GB storage  │ │ 256 MB        │ │ export file │
│ kasirku_stg   │ │ session+queue │ │             │
└───────────────┘ └───────────────┘ └─────────────┘

Status Page: Instatus.com (free tier)
Error Tracking: Sentry free
```

### 3.3 Diagram Production (VPS Jakarta)

```
┌─────────────────────────────────────────────────────┐
│  Browser                                            │
│  https://kasirku.id / admin.kasirku.id              │
└────────────────────┬────────────────────────────────┘
                     │ HTTPS (Cloudflare)
┌────────────────────▼────────────────────────────────┐
│  VPS 8 GB Jakarta (IDCloudHost / Biznet Gio)        │
│  Ubuntu 24.04 LTS                                   │
│                                                     │
│  ┌─────────────────────────────────────────────┐   │
│  │ Docker Compose (13 services)                │   │
│  │                                             │   │
│  │  ┌─────────┐ ┌─────────┐ ┌──────────────┐  │   │
│  │  │ app     │ │ worker  │ │ scheduler    │  │   │
│  │  │ Laravel │ │ queue   │ │ cron         │  │   │
│  │  └─────────┘ └─────────┘ └──────────────┘  │   │
│  │  ┌─────────┐ ┌─────────┐ ┌──────────────┐  │   │
│  │  │ mysql   │ │ redis   │ │ nginx        │  │   │
│  │  │ 8.4     │ │ 7.4     │ │ 1.28         │  │   │
│  │  └─────────┘ └─────────┘ └──────────────┘  │   │
│  │  ┌─────────┐ ┌─────────┐ ┌──────────────┐  │   │
│  │  │ minio   │ │chatwoot │ │ bookstack    │  │   │
│  │  │ storage │ │ chat    │ │ KB           │  │   │
│  │  └─────────┘ └─────────┘ └──────────────┘  │   │
│  │  ┌─────────┐ ┌─────────┐ ┌──────────────┐  │   │
│  │  │ uptime  │ │ goalert │ │ glitchtip    │  │   │
│  │  │ kuma    │ │ on-call │ │ error        │  │   │
│  │  └─────────┘ └─────────┘ └──────────────┘  │   │
│  └─────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────┘
```

### 3.4 Diagram Render Engine Vanilla

```
┌────────────────────────────────────────────────────┐
│  Vanilla JS App (ES Modules)                       │
│                                                    │
│  ┌──────────────────────────────────────────────┐  │
│  │  Global State Object                         │  │
│  │  { cart: [...], products: [...], user: {...},│  │
│  │    holdOrders: [...], offlineQueue: [...] }  │  │
│  └──────────────────┬───────────────────────────┘  │
│                     │ state change                  │
│  ┌──────────────────▼───────────────────────────┐  │
│  │  render() — debounced 80ms                   │  │
│  │  renderNow() — immediate (critical POS)      │  │
│  └──────────────────┬───────────────────────────┘  │
│                     │                              │
│  ┌──────────────────▼───────────────────────────┐  │
│  │  DOM Mutation Helpers                        │  │
│  │  el(), on(), qs(), qsa(), patch()            │  │
│  └──────────────────┬───────────────────────────┘  │
│                     │                              │
│  ┌──────────────────▼───────────────────────────┐  │
│  │  Component Renderers (per module)            │  │
│  │  renderProductGrid(), renderCart(),          │  │
│  │  renderOrderPanel(), renderPaymentModal(),   │  │
│  │  renderHoldBadge(), renderPinPad(),          │  │
│  │  renderQRISModal(), renderSetupChecklist(),  │  │
│  │  renderCharts(), renderSearchResults()       │  │
│  └──────────────────────────────────────────────┘  │
│                                                    │
│  ┌──────────────────────────────────────────────┐  │
│  │  Offline Manager                             │  │
│  │  navigator.onLine → localStorage save/load   │  │
│  │  auto-sync saat koneksi pulih                │  │
│  └──────────────────────────────────────────────┘  │
│                                                    │
│  ┌──────────────────────────────────────────────┐  │
│  │  Bluetooth Manager                           │  │
│  │  navigator.bluetooth.requestDevice()          │  │
│  │  ESC/POS command builder                     │  │
│  └──────────────────────────────────────────────┘  │
│                                                    │
│  ┌──────────────────────────────────────────────┐  │
│  │  Chart Engine (Canvas API)                   │  │
│  │  drawLine(), drawBar(), drawDonut(),         │  │
│  │  drawSparkline(), drawHeatmap()              │  │
│  └──────────────────────────────────────────────┘  │
└────────────────────────────────────────────────────┘
```

### 3.5 Diagram Adapter Layer


```
┌────────────────────────────────────────────────┐
│  Application Code                              │
│  Controllers, Services, Jobs                   │
│  TIDAK akses provider langsung                 │
└────────────────────┬───────────────────────────┘
                     │ Resolve dari container
┌────────────────────▼───────────────────────────┐
│  Contracts (Interfaces) — app/Contracts/       │
│  OtpSender, EmailSender, StorageDriver,        │
│  ErrorReporter, SupportLinkProvider,           │
│  KnowledgeBaseLinkProvider,                    │
│  StatusPageLinkProvider                        │
└────────────────────┬───────────────────────────┘
                     │ Binding di AppServiceProvider
┌────────────────────▼───────────────────────────┐
│  Implementations — app/Services/Adapters/      │
│  LogOtpSender, FakeOtpSender, FonnteOtpSender, │
│  LocalStorageDriver, S3StorageDriver, ...      │
└────────────────────────────────────────────────┘
```


### 3.6 Diagram Environment

| Environment | Provider | Fase | Data | Biaya |
|---|---|---|---|---|
| Local | Docker Compose | 0–6 | Seed | Rp0 |
| Staging | Render + TiDB + Upstash + R2 | 7–8 | Seed | Rp0 (tanpa CC) |
| Production-test | Render + TiDB + Upstash + R2 | 9A | Seed | Rp0 (tanpa CC) |
| Production live | VPS 8 GB Jakarta | 9B | PII | ~Rp400–500rb/bln |

---

## §4. Struktur Folder (Vanilla)

```
/
├── app/
│   ├── Http/
│   │   ├── Controllers/{Merchant,Admin,Auth,Production}
│   │   ├── Middleware/{Tenant,Onboarded,Role,Mfa,Audit,FeatureGate,
│   │   │              Quota,ReadOnly,Consent,SubscriptionActive,
│   │   │              TrackEvent,GuardCheck,ForceHttps,Partial,
│   │   │              DeviceActive,PinLockout,CashierPermission,
│   │   │              HoldLimit}.php
│   │   ├── Requests/
│   │   └── View/Composers/
│   ├── Models/                          ← 80 model
│   ├── Policies/
│   ├── Services/
│   │   ├── Pos/{PosPricingService,SplitBillService,SplitEqualService,
│   │   │        CashierSessionService,ReceiptService,HoldOrderService,
│   │   │        CreditSaleService,CashMovementService,QrisService,
│   │   │        RefundPartialService,PrintReceiptService}.php
│   │   ├── Inventory/{InventoryService,PurchaseOrderService,
│   │   │              StockMovementService}.php
│   │   ├── Modifier/ModifierService.php
│   │   ├── Crm/{LoyaltyService,CustomerService}.php
│   │   ├── Shift/ShiftReportService.php
│   │   ├── Billing/{SubscriptionStateMachine,ProrationService}.php
│   │   ├── FeatureGating/FeatureGateService.php
│   │   ├── Compliance/{ExportService,DeletionService,
│   │   │               RetentionService}.php
│   │   ├── Adapters/{Otp,Email,Storage,Error,Link,Printer}/
│   │   ├── Analytics/{AnalyticsService,ConsentChecker,
│   │   │              BotDetector,EventRegistry}.php
│   │   ├── Promo/{VoucherService,ScheduledDiscountService}.php
│   │   ├── Notification/{NotificationService,ChangeTracker}.php
│   │   ├── Setup/{SetupChecklistService}.php
│   │   ├── Search/{GlobalSearchService}.php
│   │   └── Production/{ReleaseService,ReleaseGateService,
│   │                   CutoverService,DeploymentService,
│   │                   RollbackService}.php [PROD]
│   ├── Contracts/                       ← 8 interface
│   │   └── {OtpSender,EmailSender,StorageDriver,ErrorReporter,
│   │        SupportLinkProvider,KnowledgeBaseLinkProvider,
│   │        StatusPageLinkProvider,PrinterDriver}.php
│   ├── Jobs/{TrackEvent,ProcessExport,SendOtp,
│   │         SendEmailVerification,ReconcileStockJob,
│   │         ReconcileCustomerStatsJob,GenerateShiftReport,
│   │         SyncOfflineCart,CheckCreditDueDate,
│   │         ApplyScheduledDiscounts}.php
│   ├── Console/Commands/{ExpireTrials,ExpireGracePeriods,
│   │                     CheckProduction,CreateAnalyticsPartition,
│   │                     ReconcileStock,Cleanup*.php}
│   ├── Observers/
│   └── Enums/
│
├── resources/
│   ├── views/                           ← Blade
│   │   ├── layouts/{app,admin,pos,auth}.blade.php
│   │   ├── components/{button,modal,card,input,table,toast,
│   │   │              immutable-badge,env-badge,quota-meter,
│   │   │              setup-checklist,pin-pad,qris-display,
│   │   │              hold-badge,cash-movement-modal,chart,
│   │   │              pos/*}.blade.php
│   │   ├── merchant/{dashboard,pos,products,modifiers,inventory,
│   │   │            customers,shifts,outlets,team,devices,
│   │   │            subscription,settings,promotions,
│   │   │            reports,search}/
│   │   ├── admin/{auth,dashboard,tenants,subscriptions,features,
│   │   │         support,audit,exports,production}/
│   │   ├── auth/{login,register,onboarding,forgot-password,
│   │   │        pos-login,pos-pin}/
│   │   ├── legal/{privacy,terms,cookies}.blade.php
│   │   ├── errors/{403-gated,403-rbac,404,429,500-guard,
│   │   │          503-readonly,503-maintenance}.blade.php
│   │   ├── partials/{pos,inventory,admin,promotions,search}/
│   │   └── landing.blade.php
│   ├── css/                             ← CSS murni
│   │   ├── tokens.css                   ← :root custom properties (87 token)
│   │   ├── base.css
│   │   ├── components/{button,input,card,modal,table,toast,badge,
│   │   │              chart,pin-pad,qris,setup-checklist}.css
│   │   ├── layouts/{app,admin,pos,auth}.css
│   │   ├── pages/{dashboard,pos,inventory,crm,shift,
│   │   │         subscription,admin,promotions,search}.css
│   │   ├── states/{expired,error,loading,offline}.css
│   │   └── utilities.css
│   └── js/                              ← ES modules
│       ├── app.js
│       ├── core/{state,render,dom,fetch,csrf,analytics,router,
│       │        offline,bluetooth,gesture,chart}.js
│       ├── modules/{pos,inventory,modifier,crm,shift,tax,
│       │           subscription,auth,compliance,admin,production,
│       │           promotions,search,setup,hold,kasbon,qris,
│       │           cash-movement}.js
│       ├── components/{toast,modal,dropdown,tabs,accordion,
│       │              env-badge,pin-pad,chart,sparkline}.js
│       └── utils/{currency,date,debounce,sanitize,format}.js
│
├── public/
│   ├── index.php
│   ├── js/                              ← copy dari resources/js
│   ├── css/                             ← minify dari resources/css
│   ├── images/{logo-wordmark,logo-icon,logo-lockup}.svg
│   │         + favicon.ico
│   └── fonts/inter-*.woff2
│
├── routes/{web,api,partials,console}.php
├── config/{services,design}.php
├── database/{migrations,seeders,factories}/
├── docker/{nginx,php,mysql}/
├── tests/{Feature,Unit,E2E}/
├── docs/{prd,design,schema,architecture,rule}.md + runbooks/
├── .github/workflows/{ci,deploy-staging,deploy-production}.yml
├── docker-compose{,.staging,.prod}.yml
├── render.yaml                          ← Render.com config
├── package.json                         ← hanya ESLint, Stylelint,
│                                          Prettier, Playwright
└── README.md
```

### 4.1 Aturan Struktur Folder

| Aturan | Detail |
|---|---|
| Blade = server-side rendering | Semua halaman adalah Blade view; tidak ada SPA |
| Vanilla JS = progressive enhancement | Halaman tetap berfungsi tanpa JS; JS menambah interaktivitas |
| Partial = AJAX fragment | resources/views/partials/ di-return oleh endpoint partial |
| CSS = token-based | Semua nilai dari tokens.css; tidak boleh hardcode |
| JS = ES modules | Gunakan import/export; `<script type="module">`; tanpa bundler |
| [PROD] folder production/ | Hanya dibuat saat Fase 9 dimulai |
| Contracts & Adapters wajib Fase 0 | Interface + binding sebelum fitur apapun |

---

## §5. Adapter Pattern (8 Contracts)

### 5.1 Prinsip

Kode bisnis tidak boleh tahu apakah OTP dikirim via log, fake, atau provider WhatsApp. Semua layanan eksternal diakses lewat interface + config/env. Dilarang hardcode URL, API key, atau kredensial provider di kode.

Berlaku juga untuk frontend: komponen frontend tidak boleh hardcode nama provider; label UI konsisten ("WhatsApp", "Chat Support").

### 5.2 8 Contracts Wajib

| Contract | File | Metode | Dev Impl | Prod Impl |
|---|---|---|---|---|
| OtpSender | app/Contracts/OtpSender.php | send($target, $code, $channel) | LogOtpSender, FakeOtpSender | FonnteOtpSender, EmailOtpSender |
| EmailSender | app/Contracts/EmailSender.php | send($to, $subject, $body) | Mailpit | SES/Postmark |
| StorageDriver | app/Contracts/StorageDriver.php | put($path, $content), url($path) | LocalStorageDriver | S3StorageDriver, MinioStorageDriver |
| ErrorReporter | app/Contracts/ErrorReporter.php | report($exception) | LogErrorReporter | SentryErrorReporter |
| SupportLinkProvider | app/Contracts/SupportLinkProvider.php | url() | MailtoLinkProvider | ChatwootLinkProvider |
| KnowledgeBaseLinkProvider | app/Contracts/KnowledgeBaseLinkProvider.php | url() | StaticMarkdownLinkProvider | BookStackLinkProvider |
| StatusPageLinkProvider | app/Contracts/StatusPageLinkProvider.php | url() | InstatusLinkProvider | UptimeKumaLinkProvider |
| **PrinterDriver** | app/Contracts/PrinterDriver.php | print($data, $format), testPrint() | LogPrinterDriver | BluetoothPrinterDriver (client-side), CssPrintDriver (fallback) |

### 5.3 Config — config/services.php

```php
return [
    'otp' => [
        'driver' => env('OTP_DRIVER', 'log'),
        'fonnte' => [
            'token' => env('FONNTE_TOKEN'),
            'url'   => env('FONNTE_URL'),
        ],
    ],
    'storage' => [
        'driver' => env('STORAGE_DRIVER', 'local'),
    ],
    'support' => [
        'chat_driver'   => env('SUPPORT_CHAT_DRIVER', 'mailto'),
        'kb_driver'     => env('KB_DRIVER', 'markdown'),
        'status_driver' => env('STATUS_DRIVER', 'instatus'),
        'chat_url'      => env('SUPPORT_CHAT_URL', 'mailto:support@kasirku.id'),
        'kb_url'        => env('KB_URL', '/help'),
        'status_url'    => env('STATUS_URL', 'https://kasirku.instatus.com'),
    ],
    'error_tracker' => [
        'driver' => env('ERROR_TRACKER_DRIVER', 'log'),
    ],
];
```

### 5.4 Binding — AppServiceProvider::register()

```php
$this->app->bind(OtpSender::class, fn() => match(config('services.otp.driver')) {
    'log'    => new LogOtpSender(),
    'fake'   => new FakeOtpSender(),
    'fonnte' => new FonnteOtpSender(config('services.otp.fonnte')),
    'email'  => new EmailOtpSender(),
    default  => throw new RuntimeException('Unknown OTP driver'),
});
// dst. untuk StorageDriver, SupportLinkProvider, ErrorReporter, dll.
```

### 5.5 Composite Adapter (Fallback)

Production OTP = CompositeOtpSender — Fonnte primary → Email fallback. Setiap fallback → audit otp.fallback. Fallback gagal → audit otp.fallback_failed + Slack alert P2.


### 5.6 Adapter Matrix per Environment

| Layanan | Local | Staging | Production-test [PROD] | Production [PROD] |
|---|---|---|---|---|
| Email | log/Mailpit | Mailpit | SES | SES |
| OTP WhatsApp | log | fake | Fonnte | Fonnte |
| Storage | local | Cloudflare R2 | MinIO | MinIO |
| Chat | mailto | mailto | Chatwoot | Chatwoot |
| KB | Markdown | Markdown | BookStack | BookStack |
| Status | — | Instatus.com | Uptime Kuma | Uptime Kuma |
| Error | Log | Sentry | Sentry | GlitchTip |
| On-call | — | Slack | GoAlert | GoAlert |
| Database | MySQL 8.4 (Docker) | TiDB Cloud | TiDB Cloud | MySQL 8.4 (VPS) |
| Redis | Redis 7.4 (Docker) | Upstash | Upstash | Redis 7.4 (VPS) |
| **Printer** | **Log** | **Log** | **Web Bluetooth** | **Web Bluetooth** |

### 5.7 Aturan Adapter (9 Aturan)

1. Wajib interface di app/Contracts/
2. Wajib config/env — bukan if (app()->isProduction())
3. Wajib binding di AppServiceProvider
4. Dilarang new ProviderName() di controller — constructor injection
5. Dilarang hardcode URL/API key
6. Test wajib fake adapter — provider asli hanya smoke test
7. Label UI konsisten — user tidak lihat nama provider
8. Provider baru → contract + config + binding + test
9. Frontend tidak boleh tahu nama provider — label di Blade view wajib generik

---

## §6. Auth & Session (PIN-only POS)

### 6.1 Strategi

| Aspek | Detail |
|---|---|
| Auth | Session-based auth (Sanctum SPA / web guard) |
| Cookie | HttpOnly, Secure, SameSite=Lax, Domain=.[domain] |
| CSRF | VerifyCsrfToken untuk semua non-GET |
| Session data | Redis; metadata di DB (user_sessions, admin_sessions) |
| Session lifetime | 8 jam (admin), 8 jam (merchant), 30 hari (remember me) |
| MFA | TOTP wajib admin |
| Env indicator | Disuntik via Blade global variable |
| POS login | PIN-only (device-based); session persistent 30 hari |
| Switch User | Tanpa logout penuh; hanya perlu PIN kasir baru |
| Session kasir | Tied to cashier_session (shift) |

### 6.2 Alur Login Dashboard

```
User submit email + password
↓
Laravel validasi → buat session ID di Redis
↓
Simpan metadata di user_sessions/admin_sessions
↓
Regenerate session ID (anti fixation)
↓
Set cookie HttpOnly
↓
Redirect dashboard
↓
Request berikutnya: cookie + CSRF
↓
Middleware cek session Redis + revoked_at NULL
```

### 6.3 Alur Login POS (PIN-only)

```
POS device sudah aktif?
├── YA → Tampilkan grid karyawan
│        ↓
│        Kasir pilih nama
│        ↓
│        Input PIN 6 digit
│        ↓
│        Validasi PIN (Argon2id hash)
│        ├── BENAR → Buat POS session → Masuk POS (≤5 detik)
│        └── SALAH → Increment pin_failed_attempts
│            ├── < 5 → "PIN salah, coba lagi"
│            └── ≥ 5 → Lockout 15 menit (pin_locked_until)
│                       + audit pos_login_pin_failed
│
└── TIDAK → Tampilkan form activation code
            ↓
            Input activation code
            ↓
            Validasi code + expiry
            ├── VALID → Daftarkan device → Grid karyawan → PIN
            └── INVALID → "Kode tidak valid atau expired"
```

**Switch User POS:**
```
Kasir A aktif di POS
↓
Klik "Switch User"
↓
Konfirmasi: "Ganti kasir? Order yang belum disimpan akan hilang."
↓
Grid karyawan → Kasir B pilih nama → PIN 6 digit
↓
Session user berubah (A→B), device session tetap
↓
Cashier session A tetap terbuka (belum ditutup)
↓
Audit: switch_user_pos
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

### 6.4 Aturan Keamanan Auth

| Aturan | Nilai |
|---|---|
| Password hash | Argon2id / bcrypt cost ≥12 |
| PIN hash | Argon2id; lockout 5 percobaan → 15 menit |
| Rate limit login | 5×/menit/IP + akun |
| Rate limit PIN | 5 percobaan per PIN per 15 menit |
| Pesan error | Generik (tidak bocorkan info) |
| MFA admin | Wajib TOTP |
| HTTPS | Wajib |
| Session regeneration | Setelah login |
| Cleanup session revoked | >30 hari |
| Email verification | Tabel email_verifications + resend count |
| OTP | 6 digit, expiry 5 menit, maks 5 percobaan |
| CSRF di form native | @csrf Blade directive di semua form |
| CSRF di fetch | X-CSRF-TOKEN header dari `<meta name="csrf-token">` |

### 6.5 Rate Limit (15)

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

### 6.6 Expired Redirect Flow

```
Request masuk → middleware subscription-active cek
↓
Jika expired/past_due/grace habis:
  API/partial → 403 JSON {code: 'SUBSCRIPTION_EXPIRED', redirect}
  Web → 302 redirect /app/subscription?reason=expired
↓
Frontend vanilla menangkap SUBSCRIPTION_EXPIRED dari fetch
→ tampilkan modal blocking
```

**Vanilla JS handling:**

```javascript
async function apiFetch(url, options = {}) {
    const res = await fetch(url, {
        ...options,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
            ...options.headers,
        },
        credentials: 'same-origin',
    });
    if (res.status === 403) {
        const data = await res.json();
        if (data.code === 'SUBSCRIPTION_EXPIRED') {
            showModal({ type: 'expired', redirect: data.redirect });
            throw new Error('SUBSCRIPTION_EXPIRED');
        }
    }
    if (!res.ok) throw new Error('Request failed');
    return res;
}
```

### 6.7 MFA Admin Flow (Vanilla)

1. Admin login email + password → session 8 jam (belum MFA-verified)
2. Redirect ke /admin/mfa (setup atau verify)
3. Setup: QR TOTP + input 6 digit + 10 recovery codes
4. Verifikasi: input 6 digit → validasi → set admin_sessions.mfa_verified_at
5. Recovery code: single-use → hapus dari list → set MFA verified
6. Lockout: 5 percobaan gagal → session revoked + audit

### 6.8 Session Cleanup

- Job CleanupUserSessions + CleanupAdminSessions
- Hapus session revoked >30 hari
- Hapus session expired (Redis TTL otomatis)

---

## §7. Middleware & Routing (18 Middleware)

### 7.1 5 Route Group

| Group | Prefix | Middleware | Tujuan |
|---|---|---|---|
| Public | / | web | Landing, register, login, OTP, legal |
| Verify | /verify-email/* | web | Email verification |
| Merchant | /app/* | web, auth, tenant, onboarded, subscription-active, read-only, role, audit, analytics | Dashboard, POS, produk, inventori, CRM, shift, settings, promosi, pencarian |
| Backoffice | /admin/* | web, auth:admin, mfa, role, audit | Tenant, subscription, support, flag, release [PROD] |
| API/Partial | /app/api/*, /admin/api/* | web, auth, tenant, throttle | JSON endpoint + AJAX fragment |

### 7.2 18 Middleware Kustom

| # | Middleware | Fungsi | Fase |
|---|---|---|---|
| 1 | tenant | Set tenant aktif dari session user | MVP |
| 2 | onboarded | Pastikan onboarding_step = 4 | MVP |
| 3 | role | Cek role merchant (role:{permission}) | MVP |
| 4 | mfa | Wajib MFA admin | MVP |
| 5 | audit | Catat aksi tulis ke audit_logs | MVP |
| 6 | feature-gate | Cek plan_features + overrides → 403 (21 fitur) | MVP |
| 7 | quota | Cek plans.max_* + add_ons → 403 + prompt | MVP |
| 8 | read-only | Blokir aksi tulis tenant suspended/archived | MVP |
| 9 | consent | Cek cookie consent analytics/marketing | MVP |
| 10 | subscription-active | Blokir aksi tulis expired → 403/302 | MVP |
| 11 | analytics | Auto-track page_view via queue | MVP |
| 12 | guard-check | Cek guard rules di production | MVP |
| 13 | ForceHttps | Redirect HTTP → HTTPS | PROD |
| 14 | partial | Deteksi request partial (X-Partial: true) | MVP |
| 15 | device-active | Pastikan POS device sudah aktif sebelum login POS | MVP |
| 16 | pin-lockout | Cek pin_locked_before before PIN verification | MVP |
| 17 | cashier-permission | Cek cashier_settings (can_void, can_refund, can_discount) | MVP |
| 18 | hold-limit | Pastikan hold order tidak melebihi 10 per outlet | MVP |

### 7.3 Urutan Wajib Merchant (9 Langkah)

```
1. web                    (session, CSRF)
2. auth                   (Sanctum SPA / web guard)
3. tenant                 (set tenant aktif)
4. onboarded              (cek onboarding_step = 4)
5. subscription-active    (cek expired — route exemption)
6. read-only              (cek suspended/archived)
7. role                   (cek role merchant)
8. audit                  (log aksi tulis)
9. analytics              (track page_view — terakhir)
```

### 7.4 Urutan Wajib Backoffice (7 Langkah)

```
1. web
2. auth:admin
3. mfa
4. role (RBAC)
5. audit
6. analytics
7. [PROD] admin.release (khusus route release/cutover/rollback)
```

### 7.5 Route Exemption Expired (12 Dibuka + 12 Diblokir)

**Dibuka (UU PDP + wajib akses):**
/app/dashboard, /app/subscription, /app/settings/profile, /app/settings/data-privacy, /app/settings/cookie, /app/help, /help, /app/reports/*, /logout, /verify-email, /login, /register

**Diblokir (403 read-only):**
/app/pos/*, /app/products/*, /app/categories/*, /app/modifiers/*, /app/inventory/*, /app/customers/*, /app/tables/*, /app/settings/* (selain profil/privacy/cookie), /app/employees/*, /app/invoices/*, /app/outlets/*, /app/devices/*

### 7.6 Feature Gating di Route (21 Fitur)

```php
Route::middleware(['feature:pos.split_bill'])->group(function () {
    Route::post('/app/pos/split', [SplitBillController::class, 'store']);
});
Route::middleware(['feature:pos.credit_sale'])->group(function () {
    Route::post('/app/pos/credit', [CreditSaleController::class, 'store']);
    Route::post('/app/pos/credit/pay', [CreditSaleController::class, 'pay']);
});
Route::middleware(['feature:promo.voucher'])->group(function () {
    Route::resource('/app/promotions/vouchers', VoucherController::class);
});
Route::middleware(['feature:promo.scheduled'])->group(function () {
    Route::resource('/app/promotions/scheduled-discounts', ScheduledDiscountController::class);
});
```

**21 fitur gated:** pos.transaction, pos.split_bill, pos.void_refund, pos.table_management, pos.credit_sale, invoice.digital, report.basic, report.advanced, outlet.multi, export.csv, payment.custom_method, tax.three_methods, service_charge.rounding, receipt.customization, inventory.tracking, product.modifiers, crm.loyalty, shift.report, promo.voucher, promo.scheduled, cashier.advanced_settings.

### 7.7 Partial Route (AJAX Fragment)

```php
// routes/partials.php
Route::middleware(['web', 'auth', 'tenant'])
    ->prefix('app/api/partials')->group(function () {
    Route::get('/pos/product-grid', [PosPartialController::class, 'productGrid']);
    Route::get('/pos/cart', [PosPartialController::class, 'cart']);
    Route::get('/pos/hold-list', [PosPartialController::class, 'holdList']);
    Route::get('/inventory/po-table', [InventoryPartialController::class, 'poTable']);
    Route::get('/admin/tenant-table', [AdminPartialController::class, 'tenantTable']);
    Route::get('/search/results', [SearchPartialController::class, 'results']);
    Route::get('/promotions/voucher-list', [PromotionPartialController::class, 'voucherList']);
    Route::get('/dashboard/widgets', [DashboardPartialController::class, 'widgets']);
});
```

---

## §8. Guard Rules Production

### 8.1 8 Guard Rules Wajib

| # | Rule | Check |
|---|---|---|
| 1 | OTP_DRIVER bukan log/fake | config('services.otp.driver') |
| 2 | MAIL_MAILER bukan log/array | config('mail.default') |
| 3 | APP_DEBUG = false | config('app.debug') |
| 4 | TELESCOPE_ENABLED = false | config('telescope.enabled') |
| 5 | SESSION_DRIVER = redis | config('session.driver') |
| 6 | QUEUE_CONNECTION ≠ sync | config('queue.default') |
| 7 | Kredensial provider non-fake tidak kosong | Cek env value |
| 8 | APP_URL https + ForceHttps middleware | Config + middleware |

### 8.2 Implementasi di AppServiceProvider::boot()

```php
if (app()->isProduction() && config('app.guard_rules_enforced')) {
    $this->checkProductionGuards();
}
```

### 8.3 Command app:check-production

Memeriksa 8 rules, log ke audit_logs.action='env_guard_failed', return exit code 1 jika gagal.

### 8.4 Guard Failure Page

- **Ops/Developer:** halaman 500 full-width, list guard gagal (nama env *** + harapan)
- **Merchant/User:** halaman 503 generik "Layanan sedang tidak tersedia"
- **Log:** audit_logs + Slack + Sentry

### 8.5 Integrasi CI/CD

- CI (ci.yml): app:check-production dengan env production simulate → expect fail/pass
- Pipeline production [PROD]: app:check-production sebagai gate sebelum deploy

### 8.6 Guard Rules Matrix per Environment

| Rule | Local | Staging | Production-test [PROD] | Production [PROD] |
|---|---|---|---|---|
| OTP_DRIVER | log | fake | fonnte | fonnte |
| MAIL_MAILER | log | log/Mailpit | ses | ses |
| APP_DEBUG | true | true | false | false |
| SESSION_DRIVER | redis | redis (Upstash) | redis (Upstash) | redis |
| QUEUE_CONNECTION | redis | redis (Upstash) | redis (Upstash) | redis |
| FORCE_HTTPS | false | true (Render auto) | true | true |
| Guard enforced? | ❌ | ❌ | ✅ | ✅ |

---

## §9. Environment & Env Vars

### 9.1 Environment (4)

| Environment | Tujuan | URL | Provider | Data | Fase |
|---|---|---|---|---|---|
| local | Developer | http://localhost:8000 | Docker laptop | Seed | 0–6 |
| staging | QA & UAT | https://kasirku-staging.onrender.com | Render+TiDB+Upstash+R2 | Seed/demo | 7–8 |
| production-test | Production-like | https://kasirku-prodtest.onrender.com | Render+TiDB+Upstash+R2 | Seed | 9A |
| production | Live | https://kasirku.id | VPS 8 GB Jakarta | PII asli | 9B |

### 9.2 Env Vars Wajib (MVP)

**App:**
```
APP_ENV, APP_DEBUG, APP_URL, APP_VERSION, GIT_COMMIT, DEPLOY_ENV
```

**Adapter:**
```
OTP_DRIVER, MAIL_MAILER, STORAGE_DRIVER,
SUPPORT_CHAT_DRIVER, KB_DRIVER, STATUS_DRIVER, ERROR_TRACKER_DRIVER,
PRINTER_DRIVER
```

**Staging (Render + TiDB + Upstash + R2):**
```
TIDB_HOST, TIDB_PORT, TIDB_DATABASE, TIDB_USERNAME, TIDB_PASSWORD, TIDB_SSL_CA
UPSTASH_REDIS_URL
R2_ACCOUNT_ID, R2_ACCESS_KEY_ID, R2_SECRET_ACCESS_KEY, R2_BUCKET, R2_ENDPOINT
RENDER_SERVICE_ID, RENDER_API_KEY
INSTATUS_API_KEY, INSTATUS_PAGE_ID
```

**Analytics:**
```
ANALYTICS_ENABLED, ANALYTICS_QUEUE, ANALYTICS_RETENTION_DAYS
```

**Guard:**
```
GUARD_RULES_ENFORCED, EXPIRED_GRACE_DAYS, SUBSCRIPTION_REDIRECT_URL
```

**POS & Auth:**
```
POS_PIN_LOCKOUT_ATTEMPTS=5
POS_PIN_LOCKOUT_MINUTES=15
POS_DEVICE_SESSION_DAYS=30
POS_HOLD_LIMIT=10
POS_OFFLINE_CART_KEY_PREFIX=kasirku_cart_
```

**Kasbon & Promosi:**
```
CREDIT_SALE_DEFAULT_LIMIT=500000
CREDIT_SALE_DEFAULT_DUE_DAYS=30
VOUCHER_MAX_PER_TENANT=100
```

**DB/Redis, Session, Secret, Compliance:**
*(Sama dengan standar)*

### 9.3 Env Vars Fase 9 [PROD]

```
AWS_*, CHATWOOT_*, UPTIME_KUMA_BASE_URL, BOOKSTACK_BASE_URL,
GOALERT_BASE_URL, MINIO_*, GLITCHTIP_DSN,
RELEASE_GATE_ENFORCE, CUTOVER_LOCK_TTL, DEPLOY_LOCK_TTL,
REAUTH_TTL_MINUTES, ROLLBACK_CONFIRMATION_PHRASE,
FORCE_GATE_REQUIRE_REAUTH, PRODUCTION_STATUS_POLL_SECONDS,
EXPORT_DISK=s3
```

### 9.4 Blade Global Variables

```php
// AppServiceProvider::boot()
View::share('app', [
    'env'            => app()->environment(),
    'deploy_env'     => config('app.deploy_env'),
    'version'        => config('app.version'),
    'git_commit'     => config('app.git_commit'),
    'is_production'  => app()->environment('production'),
    'demo_mode'      => config('app.demo_mode', false),
    'show_env_badge' => !app()->environment('production')
                        || request()->is('admin/*'),
]);
View::share('csrf_token', csrf_token());
```

### 9.5 Environment Indicator (Vanilla)

Blade component `<x-env-badge>` + CSS components/badge.css:

- DEV (ungu), STAGING (biru), PRODUCTION-TEST (oranye), PRODUCTION (hijau)
- role="status" + aria-label
- Z-index --z-env-badge (750) — di atas cookie consent
- Production merchant bersih; production backoffice badge PRODUCTION hijau

### 9.6 render.yaml (Render.com Config)

```yaml
services:
  - type: web
    name: kasirku-staging
    runtime: docker
    plan: free
    healthCheckPath: /up
    envVars:
      - key: APP_ENV
        value: staging
      - key: DB_CONNECTION
        value: mysql
      - key: DB_HOST
        fromDatabase:
          name: kasirku-staging-db
          property: host
      - key: REDIS_URL
        fromService:
          type: redis
          name: kasirku-staging-redis
          property: connectionString

  - type: worker
    name: kasirku-staging-worker
    runtime: docker
    plan: free
    dockerCommand: php artisan queue:work --queue=default,analytics

  - type: cron
    name: kasirku-staging-scheduler
    runtime: docker
    plan: free
    schedule: "* * * * *"
    dockerCommand: php artisan schedule:run

databases:
  - name: kasirku-staging-db
    databaseName: kasirku_staging
    user: kasirku
    plan: free  # TiDB Cloud external
```

---

## §10. Analytics Pipeline (54 Events)

### 10.1 Arsitektur

```
Request → Middleware `analytics` → AnalyticsService::track()
    ↓
Job `TrackEvent` (queue: analytics, retry 3x, timeout 30s)
    ↓
INSERT INTO analytics_events
    ↓
Dashboard /admin/analytics (Should)
Client-side: Vanilla JS event → navigator.sendBeacon('/app/api/analytics/track', JSON)
→ Controller → AnalyticsService::track() → TrackEvent job
```

### 10.2 3 Kategori Event (Consent Rules)

| Kategori | Consent | Basis UU PDP | Contoh |
|---|---|---|---|
| Operational | Tidak perlu | Bagian dari layanan | pos_transaction, plan_paid, feature_blocked |
| Marketing | Wajib analytics consent | Consent (Pasal 20) | landing_view, cta_click, register_start |
| Support/Audit | Tidak perlu | Legitimate interest | support_access_granted, export_completed |

### 10.3 AnalyticsService::track()

```php
public function track(string $eventName, array $properties = [], array $context = []): void
{
    if (!config('services.analytics.enabled')) return;
    $category = EventRegistry::getCategory($eventName);
    if (!app(ConsentChecker::class)->canTrack($category)) return;
    TrackEvent::dispatch([
        'event_name'    => $eventName,
        'tenant_id'     => $context['tenant_id'] ?? null,
        'user_id'       => $context['user_id'] ?? null,
        'admin_user_id' => $context['admin_user_id'] ?? null,
        'environment'   => app()->environment(),
        'properties'    => $this->sanitize($properties),
        'session_id'    => session()->getId(),
        'ip_address'    => $this->hashIp(request()->ip()),
    ])->onQueue('analytics');
}
```

### 10.4 54 Events Wajib

**29 MVP:** landing_view, cta_click, register_start, register_complete, onboarding_complete, pos_first_transaction, pos_transaction, pos_transaction_failed, trial_expiring, plan_selected, plan_paid, feature_blocked, quota_blocked, support_access_granted, support_access_expired, export_completed, export_failed, account_deleted, cookie_consent_saved, env_guard_failed, inventory_po_created, inventory_stock_movement, inventory_low_stock_alert, modifier_selected, customer_created, loyalty_point_earned, loyalty_point_redeemed, shift_report_generated, split_bill_created

**15 Fitur Baru:** hold_order_created, hold_order_resumed, hold_order_voided, credit_sale_created, credit_payment_received, cash_movement_created, qris_payment_confirmed, switch_user_pos, pos_login_pin, split_equal_created, partial_refund_created, receipt_reprinted, offline_cart_synced, bluetooth_printer_connected, global_search_used

**10 Production [PROD]:** release_gate_evaluated, cutover_started, cutover_completed, rollback_triggered, rollback_completed, incident_reported, post_mortem_completed, backup_success, backup_failed, smoke_test_passed

**Total: 54 events.**

### 10.5 Client-Side Analytics (Vanilla)

```javascript
export function track(eventName, properties = {}, category = 'operational') {
    if (category === 'marketing' && !hasAnalyticsConsent()) return;
    const payload = JSON.stringify({
        event_name: eventName,
        properties,
        url: window.location.pathname,
    });
    if (navigator.sendBeacon) {
        navigator.sendBeacon('/app/api/analytics/track',
            new Blob([payload], { type: 'application/json' }));
    } else {
        fetch('/app/api/analytics/track', {
            method: 'POST', body: payload, keepalive: true,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
        }).catch(() => {});
    }
}
```

### 10.6 10 Aturan Analytics

1. Event marketing wajib cookie consent analytics = true; tanpa consent → drop
2. Event operational & support selalu track (legitimate interest)
3. Dikirim via queue (analytics), retry 3×, timeout 30s
4. Tidak ada PII di properties — email/phone di-hash
5. IP di-hash HMAC-SHA256 dengan APP_KEY salt
6. Bot filter untuk landing_view/cta_click/register_*
7. Retensi 90 hari + CleanupAnalyticsEvents + partition bulanan
8. Event production disimpan di analytics_events dengan environment = 'production'
9. Client-side pakai navigator.sendBeacon; fallback fetch({ keepalive: true })
10. Frontend tidak boleh hardcode endpoint analytics provider

---

## §11. Observability

### 11.1 SLA Monitoring per Environment

| Aspek | Local | Staging | Production [PROD] |
|---|---|---|---|
| Uptime & Status | — | Instatus.com | Uptime Kuma |
| Error tracking (backend) | Sentry free | Sentry free | Sentry + GlitchTip |
| Error tracking (frontend) | Console log | Sentry browser SDK | Sentry / GlitchTip |
| Latency | Log lokal | Render dashboard | Uptime Kuma |
| Queue | Horizon lokal | Render worker | Horizon |
| On-call | — | Slack | GoAlert |
| Analytics | analytics_events | analytics_events | analytics_events |

### 11.2 Health Check /up — 4 Komponen

```php
Route::get('/up', function () {
    $checks = ['database' => 'ok', 'redis' => 'ok', 'queue' => 'ok', 'storage' => 'ok'];
    // cek DB, Redis, queue (pending <10000 && failed <100), storage
    $status = in_array('fail', $checks) ? 'fail' : 'ok';
    return response()->json([
        'status' => $status, 'checks' => $checks,
        'version' => config('app.version'),
        'environment' => app()->environment(),
    ], $status === 'ok' ? 200 : 503);
});
```


### 11.3 Frontend Error Tracking (Vanilla)

```javascript
window.addEventListener('error', (event) => reportError({
    type: 'javascript_error',
    message: event.message,
    filename: event.filename,
    lineno: event.lineno,
    colno: event.colno,
    stack: event.error?.stack,
}));

window.addEventListener('unhandledrejection', (event) => reportError({
    type: 'unhandled_rejection',
    reason: event.reason?.message || String(event.reason),
    stack: event.reason?.stack,
}));
```

### 11.4 Alert Threshold

| Kondisi | Severity | Aksi |
|---|---|---|
| /up gagal 2× | P2 | Slack |
| Error rate >1% | P2 | Slack |
| POS P95 >3 dtk | P3 | Slack |
| Queue failed >100 | P2 | Slack |
| Retention job gagal | P3 | Slack |
| env_guard_failed | P1 (production) | Page on-call + Slack |
| Frontend error rate >5/menit | P3 | Slack |
| Frontend JS crash >10/menit | P2 | Slack + GoAlert [PROD] |
| POS login P95 >5 detik | P3 | Slack |
| Hold order >10 per outlet | P3 | Slack |
| Kasbon outstanding >30 hari | P3 | Slack |
| Offline sync gagal 3× | P3 | Slack |

### 11.5 Incident Management

| Severity | Response | Status Page |
|---|---|---|
| P1 Critical | <15 menit | Tiap 30 menit |
| P2 High | <1 jam | Tiap 2 jam |
| P3 Medium | <8 jam | — |
| P4 Low | <2 hari | — |

---
## §12. Test Strategy (50 E2E Flow)

### 12.1 3 Prinsip

1. Test otomatis wajib pakai fake adapter — provider asli tidak boleh dipanggil di CI
2. Provider asli hanya diuji di smoke test production-test [PROD]
3. Tenant isolation test wajib di CI setiap PR yang mengubah query domain

### 12.2 7 Lapisan Test

| Lapisan | Tool | Cakupan | Wajib CI |
|---|---|---|---|
| Unit | Pest | Service, middleware, policy | ✅ |
| Feature | Laravel HTTP | Route, middleware chain, auth | ✅ |
| Partial | Pest + HTTP | Partial route (AJAX fragment) | ✅ |
| E2E | Playwright | 50 alur kritis | ✅ |
| Security | gitleaks, Trivy, OWASP | Secret, dependency | ✅ |
| Tenant isolation | Custom test suite | Setiap query multi-tenant | ✅ |
| Performance | Lighthouse, k6 | LCP, POS P95 | Fase 8 |

### 12.3 Coverage Target

| Path | Target |
|---|---|
| app/Http/Controllers/Merchant/* | 100% |
| app/Http/Controllers/Admin/* | 100% |
| app/Http/Controllers/Auth/* | 100% |
| app/Http/Middleware/* | 100% |
| app/Services/* aggregate | 70% |
| routes/partials.php | 100% |
| resources/js/modules/pos/* (opsional) | 60% (Vitest) |
| resources/js/utils/* (opsional) | 80% (Vitest) |

### 12.4 50 E2E Flow Wajib

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

### 12.5 Migration Rollback Test

```bash
php artisan migrate:fresh
php artisan migrate:rollback --step=80
php artisan migrate
```

### 12.6 Vanilla JS Testing

**Playwright (E2E — wajib):**

```javascript
test('POS transaction cash', async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'kasir@demo.test');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.goto('/app/pos');
    await page.click('[data-product-id="1"]');
    await page.click('[data-action="checkout"]');
    await page.fill('input[name="amount"]', '100000');
    await page.click('button[data-action="pay"]');
    await expect(page.locator('.receipt-preview')).toBeVisible();
});
```

### 12.7 12 Larangan Test

1. ❌ Test yang memanggil provider asli
2. ❌ Test di-skip tanpa alasan
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

---


## §13. Deployment & CI/CD

### 13.1 Pipeline CI (ci.yml) — 11 Langkah

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

### 13.2 Pipeline Deploy Staging [MVP — Fase 7]

```
Push branch staging → GitHub Actions
↓
CI 11 langkah hijau
↓
Deploy ke Render.com via API/Git
↓
Render auto-build Docker image
↓
php artisan migrate --force
↓
php artisan config:cache route:cache view:cache
↓
Health check /up
↓
Notifikasi Slack
```

### 13.3 Pipeline Deploy Production [PROD]

```
Pre-check: Release v* ada, semua gate PASS, backup tersedia
↓
Build: Docker image tag v*, push registry
↓
Deploy ke VPS:
  docker compose -f docker-compose.prod.yml pull
  docker compose -f docker-compose.prod.yml up -d
  php artisan migrate --force (setelah backup)
  php artisan config:cache route:cache view:cache
↓
Health check /up
↓
Smoke test (script)
↓
Record deployments
↓
Rollback otomatis jika health check gagal
↓
Notifikasi Slack + status page
```

### 13.4 Branching

| Branch | Fungsi | Aturan |
|---|---|---|
| main | Production-ready | Protected, tidak bisa push langsung |
| develop | Integrasi (tim >3) | Wajib — tim MVP ≤15 |
| feat/* | Feature | Dari develop, merge via PR |
| fix/* | Bug fix | Dari develop |
| chore/* | Non-fitur | Dari develop |
| hotfix/* | Darurat | Dari main + post-merge review 24 jam |
| staging | Auto-deploy staging | Dari main, merge via PR |
| release/* [PROD] | Persiapan release | Dari main |

### 13.5 Docker Compose (3 Konfigurasi)

| Config | Service | Environment |
|---|---|---|
| docker-compose.yml | app + nginx + mysql + redis + queue + scheduler + phpmyadmin (7) | Local |
| docker-compose.staging.yml | Tidak dipakai — staging via Render.com | Staging |
| docker-compose.prod.yml | 13 service (app, worker, scheduler, mysql, redis, nginx, minio, chatwoot, bookstack, uptime-kuma, goalert, glitchtip, chatwoot-postgres) | Production |

### 13.6 Deploy Vanilla Assets (Tanpa Bundler)

| Aspek | Cara |
|---|---|
| JS modules | Copy resources/js/* → public/js/*; cache busting ?v={version} |
| CSS | Minify via Vite (hanya CSS) atau cssnano → public/css/app.min.css |
| Fonts | Copy ke public/fonts/ |
| Images | Copy ke public/images/ |
| Cache busting | Query string ?v={{ config('app.version') }} |
| HTTP/2 | Nginx HTTP/2 — multiple ES modules paralel |
| CDN | Cloudflare cache untuk public/js/*, public/css/* |

**Blade asset loading:**

```blade
<link rel="stylesheet" href="{{ asset('css/app.min.css') }}?v={{ config('app.version') }}">
<script type="module" src="{{ asset('js/app.js') }}?v={{ config('app.version') }}"></script>
```

### 13.7 Rollback Procedure

**Rollback otomatis** (jika health check gagal): revert docker image, restore backup, cache clear, health check, record rollback_events.

**Rollback manual via backoffice [PROD]:** /admin/deploy-history → Rollback → modal (target versi + alasan + konfirmasi ROLLBACK + re-auth) → rollback_events (immutable).

### 13.8 Deploy Checklist per Fase

**Fase 7:** Render deploy jalan, .env staging, CI hijau, health check 200, smoke test 20/20, tidak ada PII, Slack.

**Fase 9A:** Render prod-test deploy, .env production-test, RG-1..RG-10 PASS, guard PASS, health 200, smoke 20/20, SPF/DKIM/DMARC.

**Fase 9B:** VPS Jakarta, 6 tools jalan, domain+SSL, cutover #1..#10, guard PASS, health 200, smoke 20/20, monitoring 24 jam.

---


## §14. Aturan Arsitektur (68)

### 14.1 26 Aturan MVP

| # | Aturan |
|---|---|
| 1 | Web = sumber kebenaran |
| 2 | Frontend tidak menyimpan logic bisnis |
| 3 | API versioning wajib (/v2 jika breaking) |
| 4 | Secret tidak pernah masuk repo / client bundle |
| 5 | Keputusan besar wajib ADR |
| 6 | Package/dependency baru wajib alasan |
| 7 | CI hijau sebelum merge |
| 8 | Deploy = tag, bukan edit server |
| 9 | HTTPS wajib |
| 10 | Multi-tenant isolation |
| 11 | Feature gating di middleware (21 fitur) |
| 12 | Kuota di middleware |
| 13 | Session di Redis, metadata di DB |
| 14 | Trial = plan terpisah |
| 15 | Perubahan arsitektur signifikan → update dokumen di PR yang sama |
| 16 | Compliance by default |
| 17 | Support by default |
| 18 | Third-party non-core = fallback di MVP |
| 19 | Retention job wajib (12 job) |
| 20 | Future-ready |
| 21 | Runbook wajib |
| 22 | Free-first (staging tanpa CC) |
| 23 | Dev-first, Setup-at-End |
| 24 | MVP = Fase 0–8 |
| 25 | Vanilla-first frontend |
| 26 | Progressive enhancement |

### 14.2 16 Aturan Lanjutan

| # | Aturan |
|---|---|
| 27 | Adapter pattern wajib — semua layanan eksternal via interface |
| 28 | Guard rules wajib — app gagal start production jika driver dev aktif |
| 29 | Test wajib fake adapter |
| 30 | 54 events via queue |
| 31 | Analytics consent per kategori |
| 32 | IP hashed HMAC-SHA256 |
| 33 | JSON column shape validasi |
| 34 | Migration naming standar |
| 35 | Charset utf8mb4_unicode_ci |
| 36 | UUID CHAR(36) |
| 37 | Expired blocking middleware |
| 38 | Route exemption eksplisit 12+12 |
| 39 | Onboarding step middleware |
| 40 | Support thread model |
| 41 | Email verification tabel terpisah |
| 42 | Retention jobs registry (12 job) |

### 14.3 8 Aturan [PROD]

| # | Aturan |
|---|---|
| 43 | Gate-based release |
| 44 | Release gate immutable |
| 45 | Force gate re-auth |
| 46 | Cutover lock (Redis TTL 1 jam) |
| 47 | Deploy lock (Redis TTL 30 menit) |
| 48 | Rollback konfirmasi ROLLBACK + re-auth |
| 49 | Deploy history immutable |
| 50 | No PII di production readiness tables |

### 14.4 8 Aturan Vanilla

| # | Aturan |
|---|---|
| 51 | Tidak ada React/Vue/Svelte/Angular/TypeScript/Tailwind/Inertia/Alpine/HTMX/Livewire |
| 52 | Tidak ada bundler JS di production |
| 53 | CSS murni dengan custom properties (token Design v2.0 §2) |
| 54 | Render engine manual: render() debounced 80ms + renderNow() |
| 55 | Partial DOM update (bukan innerHTML seluruh halaman) |
| 56 | Partial route wajib untuk AJAX fragment |
| 57 | Client-side error tracking wajib |
| 58 | Blade = server-side rendering; JS = progressive enhancement |

### 14.5 10 Aturan Fitur Baru

| # | Aturan |
|---|---|
| 59 | Login POS PIN-only: device activation wajib; PIN 6 digit Argon2id; lockout 5×/15 menit |
| 60 | Hold order: maks 10 per outlet; stock tidak berkurang; wajib resolve sebelum tutup kasir |
| 61 | Kasbon: customer terdaftar; limit config; stock berkurang saat order; loyalty saat lunas |
| 62 | Kas keluar/masuk: alasan wajib min 10 char; immutable; tercatat di shift report |
| 63 | QRIS statis: konfirmasi manual; tidak ada verifikasi otomatis; audit wajib |
| 64 | Refund parsial: hanya owner/admin; qty tidak melebihi sisa; stock movement return per item |
| 65 | Print ulang: max_print_count dari receipt_settings; counter increment; audit |
| 66 | Offline cart: localStorage; auto-sync saat online; konflik dialog |
| 67 | Bluetooth printer: Web Bluetooth API; fallback CSS print; Chrome/Edge only |
| 68 | Promosi: voucher immutable setelah redeem; diskon terjadwal otomatis; tidak bisa gabung kasbon |

### 14.6 Enforcement

| Aturan | Enforcement |
|---|---|
| 1–26 | Code review + CI |
| 27–42 | Guard rules + adapter pattern + test coverage |
| 43–50 | Middleware + Redis lock + re-auth [PROD] |
| 51–58 | ESLint rule, Stylelint, CI check, code review |
| 59–68 | Feature test + middleware test + E2E Playwright |

---

## §15. ADR (88 Architecture Decision Records)

### 15.1 Fondasi & Stack (15 ADR)

| ADR | Keputusan |
|---|---|
| ADR-001 | Responsive web only — tidak ada APK |
| ADR-002 | Session-based auth dengan cookie HttpOnly + CSRF |
| ADR-003 | Frontend vanilla JS + Blade (bukan React/Inertia) |
| ADR-004 | MySQL 8.4 LTS dengan utf8mb4 |
| ADR-005 | Laravel 13 + vanilla JS (bukan full-stack framework) |
| ADR-006 | Sanctum SPA untuk session auth |
| ADR-007 | Docker Compose untuk lokal |
| ADR-008 | Redis untuk session, queue, cache |
| ADR-009 | Trial = plan terpisah (is_trial_plan = TRUE) |
| ADR-010 | Feature gating di middleware |
| ADR-011 | Data residency Jakarta untuk production |
| ADR-012 | Chat support: mailto → Chatwoot |
| ADR-013 | Status page: Instatus → Uptime Kuma |
| ADR-014 | KB: Markdown → BookStack |
| ADR-015 | Cookie consent vanilla JS custom |

### 15.2 Deployment & Ops (5 ADR)

| ADR | Keputusan |
|---|---|
| ADR-016 | Export async via queue |
| ADR-017 | Soft delete 30 hari |
| ADR-018 | Laravel Horizon untuk queue monitoring |
| ADR-019 | On-call: Slack → GoAlert |
| ADR-020 | Backup: Restic harian + off-site mingguan |

### 15.3 Storage & Error (5 ADR)

| ADR | Keputusan |
|---|---|
| ADR-021 | Storage: local → Cloudflare R2 (staging) → MinIO (prod) |
| ADR-022 | Error tracking: Sentry free → GlitchTip [PROD] |
| ADR-023 | Staging: Render.com free (tanpa CC) |
| ADR-024 | Staging DB: TiDB Cloud Serverless (MySQL-compatible) |
| ADR-025 | Staging Redis: Upstash free |

### 15.4 Adapter & Pattern (10 ADR)

| ADR | Keputusan |
|---|---|
| ADR-026 | Adapter pattern wajib untuk semua layanan eksternal |
| ADR-027 | Guard rules di AppServiceProvider::boot() |
| ADR-028 | Analytics events via queue |
| ADR-029 | JSON column shape validasi |
| ADR-030 | Migration naming YYYY_MM_DD_HHMMSS |
| ADR-031 | RBAC role column (tanpa tabel roles) |
| ADR-032 | 7 settings specialized tables |
| ADR-033 | MFA column di admin_users |
| ADR-034 | Expired middleware + route exemption |
| ADR-035 | Test strategy 7 lapisan |

### 15.5 Data & Behavior (10 ADR)

| ADR | Keputusan |
|---|---|
| ADR-036 | Onboarding step di users table |
| ADR-037 | F&B tables (tables, table_sessions tanpa order_id) |
| ADR-038 | Support threading (support_ticket_messages) |
| ADR-039 | Email verification tabel terpisah |
| ADR-040 | Wireframe-first, tanpa Figma |
| ADR-041 | Analytics retention 90 hari |
| ADR-042 | UUID CHAR(36) untuk tabel tertentu |
| ADR-043 | Charset utf8mb4_unicode_ci |
| ADR-044 | Retention jobs registry (12 job) |
| ADR-045 | Nama produk: Kasirku |

### 15.6 Detail Implementasi (10 ADR)

| ADR | Keputusan |
|---|---|
| ADR-046 | Analytics consent 3 kategori |
| ADR-047 | Route exemption 12+12 |
| ADR-048 | Guard rules 8 items detail |
| ADR-049 | Coverage 100% endpoint Must |
| ADR-050 | 50 E2E flow wajib |
| ADR-051 | IP hashing HMAC-SHA256 |
| ADR-052 | Composite adapter fallback |
| ADR-053 | Event categories registry |
| ADR-054 | TrackEvent job config |
| ADR-055 | Sentry↔GlitchTip migration path |

### 15.7 Fase 9 Production (10 ADR)

| ADR | Keputusan |
|---|---|
| ADR-056 | Production Readiness Subsystem |
| ADR-057 | Dev-First, Setup-at-End |
| ADR-058 | Release Gate RG-1..RG-10 |
| ADR-059 | Cutover Checklist & Lock |
| ADR-060 | Deploy History & Rollback |
| ADR-061 | Environment Indicator Final |
| ADR-062 | Force Gate Re-Auth |
| ADR-063 | Rollback Confirmation |
| ADR-064 | Self-Hosted Tools Production |
| ADR-065 | Staging Stack Verification (Render+TiDB+Upstash+R2) |

### 15.8 Vanilla Frontend (7 ADR)

| ADR | Keputusan |
|---|---|
| ADR-066 | Frontend vanilla menggantikan React + Inertia + TS + Tailwind |
| ADR-067 | Blade = server-side rendering; JS = progressive enhancement |
| ADR-068 | Render engine manual: render() debounced 80ms + renderNow() |
| ADR-069 | Partial route untuk AJAX fragment (X-Partial: true) |
| ADR-070 | CSS murni dengan custom properties (design token) |
| ADR-071 | Tanpa bundler JS di production — native ES modules + HTTP/2 |
| ADR-072 | Client-side error tracking vanilla → backend forward |

### 15.9 Fitur Baru v2.0 (16 ADR)

| ADR | Keputusan |
|---|---|
| ADR-073 | Login POS PIN-only: device-based, tanpa email/password |
| ADR-074 | Switch User POS tanpa logout penuh; device session persistent |
| ADR-075 | Hold order: maks 10 per outlet; stock tidak berkurang; status 'hold' di orders |
| ADR-076 | Kasbon: customer.credit_limit/credit_balance; order status 'credit'; loyalty saat lunas |
| ADR-077 | Kas keluar/masuk: cash_movements immutable; alasan wajib; tercatat di shift report |
| ADR-078 | QRIS statis: ewallet_settings.qris_qr_url; konfirmasi manual; type 'qris_static' |
| ADR-079 | Split rata: kalkulasi frontend; sisa di orang terakhir; tidak bisa gabung split per produk |
| ADR-080 | Refund parsial: refund_items tabel; qty tidak melebihi sisa; stock movement return per item |
| ADR-081 | Print ulang: orders.print_count; receipt_settings.max_print_count; audit receipt_reprinted |
| ADR-082 | Offline cart: localStorage kasirku_cart_{tenant_id}; auto-sync; konflik dialog |
| ADR-083 | Bluetooth printer: Web Bluetooth API; ESC/POS; fallback CSS print; PrinterDriver contract |
| ADR-084 | Setup Awal Owner: tenants.setup_step (0-10); checklist tidak blocking; analytics tracking |
| ADR-085 | Promosi: vouchers + voucher_redemptions immutable + scheduled_discounts; tidak gabung kasbon |
| ADR-086 | Notifikasi perubahan data: notification_preferences per user; ChangeTracker observer |
| ADR-087 | Pencarian global: Ctrl+K; Eloquent LIKE + index; maks 5 per kategori; permission-based |
| ADR-088 | Laporan produk terlaris: query agregasi order_items; filter periode/outlet/kategori |

---

## §16. Production Readiness Subsystem [PROD]

### 16.1 Komponen

| Komponen | Tabel | Service | Halaman |
|---|---|---|---|
| Release | releases | ReleaseService | /admin/releases |
| Gate | release_gates | ReleaseGateService | /admin/release-gates |
| Cutover | cutover_runs + cutover_checklist_items | CutoverService | /admin/cutover |
| Deploy | deployments | DeploymentService | /admin/deploy-history |
| Rollback | rollback_events | RollbackService | /admin/deploy-history + modal |

### 16.2 Gate Evaluation — RG-1 s.d. RG-10

| Gate | Nama | Kriteria | Evidence |
|---|---|---|---|
| RG-1 | Staging UAT Complete | M10 sign-off; semua AC lulus | Sign-off dokumen |
| RG-2 | Security Audit | gitleaks bersih; Trivy 0 critical; OWASP reviewed | CI artifacts |
| RG-3 | Tenant Isolation | CI test 100% hijau | CI run |
| RG-4 | Backup Restore Test | Backup + restore test lulus | Log restore |
| RG-5 | Guard Rules Verified | 8 rules gagal start saat dilanggar | Log verifikasi |
| RG-6 | Email Deliverability | SPF/DKIM/DMARC verified | DNS + screenshot |
| RG-7 | OTP Provider Verified | Fonnte primary + fallback lulus | Log test |
| RG-8 | Health Check | /up 200 di production-test | Screenshot |
| RG-9 | Smoke Test | 20/20 PASS | Checklist signed |
| RG-10 | Production Readiness Review | Semua RG-1–RG-9 PASS; no PII; biaya ≤Rp800rb | Review notes |

### 16.3 Cutover Service

```php
class CutoverService
{
    public function start(Release $release, Admin $admin): CutoverRun;
    public function completeItem(CutoverChecklistItem $item, Admin $admin, ?string $evidence): void;
    public function abort(CutoverRun $run, Admin $admin, string $reason): void;
    public function complete(CutoverRun $run, Admin $admin): void;
    public function canStart(Release $release): bool;
}
```

**10 Checklist item (sequential):**

| # | Item | PIC |
|---|---|---|
| 1 | Backup pre-cutover (DB + files + config) | Ops |
| 2 | Verifikasi semua RG-1–RG-10 PASS | Release Manager |
| 3 | Activate cutover lock (Redis TTL 1 jam) | Ops |
| 4 | Deploy release v1.0.0 ke production | Ops |
| 5 | Run migration + seed (production-safe) | Ops |
| 6 | Health check /up 200 (DB + Redis + queue + storage) | Ops |
| 7 | Smoke test 20/20 PASS | QA + Ops |
| 8 | DNS switch (jika belum) | Ops |
| 9 | SSL verification (HTTPS, HSTS) | Ops |
| 10 | Activate monitoring + on-call schedule | Ops |

### 16.4 Rollback Service

```php
class RollbackService
{
    public function request(Deployment $deployment, string $targetVersion,
                            string $reason, Admin $admin): RollbackEvent;
    public function execute(RollbackEvent $event): void;
    public function markSuccess(RollbackEvent $event, bool $healthCheckPass): void;
    public function markFailed(RollbackEvent $event, string $error): void;
}
```

**Aturan:** Hanya untuk production deployment success; konfirmasi ketik ROLLBACK + alasan + re-auth (password + TOTP); audit wajib; deployment status rolled_back; post-mortem wajib untuk P1.

### 16.5 6 Self-Hosted Tools [PROD]

| Tool | Fungsi | Subdomain |
|---|---|---|
| Chatwoot | Support chat | chat.kasirku.id |
| Uptime Kuma | Status page | status.kasirku.id |
| BookStack | KB | help.kasirku.id |
| GlitchTip | Error fallback | errors.kasirku.id (internal) |
| GoAlert | On-call | oncall.kasirku.id (internal) |
| MinIO | Object storage | minio.kasirku.id |

### 16.6 Production Environment

| Aspek | Detail |
|---|---|
| VPS | 8 GB Jakarta (IDCloudHost/Biznet Gio) |
| DNS | Cloudflare |
| SSL | Let's Encrypt auto-renew |
| Data residency | Jakarta (UU PDP) |
| Backup | Restic harian + off-site mingguan |
| Uptime target | 99.5% |
| Approval | Manual via GitHub Environments (Super Admin + Ops) |
| Docker Compose | 13 service |

### 16.7 Production Success Criteria (14 Item)

1. ✅ Semua RG-1–RG-10 PASS
2. ✅ Cutover tanpa downtime >5 menit
3. ✅ Smoke test 20/20 PASS
4. ✅ 24 jam pertama target terpenuhi
5. ✅ Backup sukses + restore test lulus
6. ✅ Guard rules verified
7. ✅ SPF/DKIM/DMARC verified
8. ✅ Monitoring aktif + on-call
9. ✅ Tidak ada P1 unresolved
10. ✅ Post-mortem selesai <48 jam (jika ada P1)
11. ✅ Biaya ≤ Rp800rb/bln
12. ✅ No PII di production readiness tables
13. ✅ 0 insiden akses tanpa otorisasi
14. ✅ 100% staff MFA

### 16.8 Re-Auth Flow [PROD]

```
Aksi kritikal (force gate, cutover, rollback)
↓
Modal re-auth: password + TOTP
↓
Validasi → admin_sessions.reauth_at = now() (TTL 15 menit)
↓
Aksi dieksekusi + audit
↓
Jika gagal → shake 300ms + toast error
```

**Middleware re-auth:**

```php
class ReAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $session = $request->user('admin')->currentSession();
        if (!$session->reauth_at || $session->reauth_at->diffInMinutes(now()) > 15) {
            return response()->json([
                'code' => 'REAUTH_REQUIRED',
                'message' => 'Aksi ini memerlukan verifikasi ulang.',
            ], 428);
        }
        return $next($request);
    }
}
```

### 16.9 Production Readiness Tables (6 Tabel)

| Tabel | Immutable? | No PII? | Retensi |
|---|---|---|---|
| releases | ✅ (setelah released/rolled_back) | ✅ | 5 tahun |
| release_gates | ✅ (setelah pass/forced) | ✅ | 5 tahun |
| deployments | ⚠️ (status berubah selama deploy) | ✅ | 2 tahun |
| cutover_runs | ✅ (setelah completed/aborted) | ✅ | 5 tahun |
| cutover_checklist_items | ✅ (setelah completed) | ✅ | 5 tahun |
| rollback_events | ✅ (INSERT only) | ✅ | 5 tahun |

---


## §17. Fitur Baru — Arsitektur Detail

### 17.1 Login POS PIN-only

**Backend:**
```php
class PosAuthController extends Controller
{
    public function verifyPin(Request $request)
    {
        $request->validate(['user_id' => 'required', 'pin' => 'required|digits:6']);
        $user = User::findOrFail($request->user_id);

        if ($user->pin_locked_until && $user->pin_locked_until->isFuture()) {
            return response()->json(['error' => 'PIN terkunci'], 423);
        }

        if (!Hash::check($request->pin, $user->pin_hash)) {
            $user->increment('pin_failed_attempts');
            if ($user->pin_failed_attempts >= 5) {
                $user->update(['pin_locked_until' => now()->addMinutes(15)]);
                AnalyticsService::track('pos_login_pin_locked', [...]);
            }
            return response()->json(['error' => 'PIN salah'], 422);
        }

        $user->update(['pin_failed_attempts' => 0, 'pin_locked_until' => null]);
        AnalyticsService::track('pos_login_pin', [...]);
        return redirect('/app/pos');
    }
}
```

**Frontend (resources/js/modules/auth/pos-login.js):**
```javascript
export function initPinPad(container) {
    let pin = '';
    const dots = container.querySelectorAll('.pin-dot');

    function onKeyPress(digit) {
        if (pin.length >= 6) return;
        pin += digit;
        dots.forEach((dot, i) => dot.classList.toggle('filled', i < pin.length));
        if (navigator.vibrate) navigator.vibrate(10);
        if (pin.length === 6) submitPin(pin);
    }

    async function submitPin(pinValue) {
        const res = await fetch('/app/pos/login/pin', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ user_id: selectedUserId, pin: pinValue }),
        });
        if (res.ok) window.location.href = '/app/pos';
        else if (res.status === 423) showLockoutOverlay();
        else { pin = ''; shakeAnimation(container); showToast('PIN salah', 'error'); }
    }
}
```

### 17.2 Hold Order

**Backend:**
```php
class HoldOrderService
{
    public function hold(Order $order, User $user): Order
    {
        $holdCount = Order::where('tenant_id', $order->tenant_id)
            ->where('outlet_id', $order->outlet_id)
            ->where('status', 'hold')->count();

        if ($holdCount >= 10) throw new HoldLimitExceededException();

        $order->update([
            'status' => 'hold',
            'held_at' => now(),
            'held_by_user_id' => $user->id,
        ]);
        // Stock TIDAK dikurangi saat hold
        AnalyticsService::track('hold_order_created', [...]);
        return $order;
    }

    public function resume(Order $order, User $user): Order
    {
        $order->update(['status' => 'open', 'held_at' => null, 'held_by_user_id' => null]);
        AnalyticsService::track('hold_order_resumed', [...]);
        return $order;
    }
}
```

### 17.3 Kasbon/Piutang

**Backend:**
```php
class CreditSaleService
{
    public function createCreditOrder(Order $order, Customer $customer): Order
    {
        $remainingLimit = $customer->credit_limit - $customer->credit_balance;
        if ($order->grand_total > $remainingLimit) {
            throw new CreditLimitExceededException($remainingLimit);
        }

        DB::transaction(function () use ($order, $customer) {
            $order->update([
                'status' => 'credit',
                'credit_due_at' => now()->addDays(config('kasirku.credit_due_days', 30)),
            ]);
            $customer->increment('credit_balance', $order->grand_total);
            StockMovementService::deductForOrder($order);
            AnalyticsService::track('credit_sale_created', [...]);
        });
        return $order;
    }

    public function payCredit(Customer $customer, Order $order, float $amount): void
    {
        DB::transaction(function () use ($customer, $order, $amount) {
            $customer->decrement('credit_balance', $amount);
            Payment::create([
                'order_id' => $order->id,
                'tenant_id' => $order->tenant_id,
                'method_name' => 'credit_payment',
                'amount' => $amount,
                'status' => 'success',
                'paid_at' => now(),
            ]);
            if ($customer->credit_balance == 0) {
                $order->update(['status' => 'paid']);
                LoyaltyService::earnForOrder($order);
            }
            AnalyticsService::track('credit_payment_received', [...]);
        });
    }
}
```

### 17.4 Kas Keluar/Masuk

**Backend:**
```php
class CashMovementService
{
    public function create(CashMovementRequest $request): CashMovement
    {
        $validated = $request->validated();

        $movement = CashMovement::create([
            'tenant_id' => $request->tenantId(),
            'outlet_id' => $validated['outlet_id'],
            'cashier_session_id' => $validated['cashier_session_id'],
            'movement_type' => $validated['movement_type'],
            'amount' => abs($validated['amount']),
            'reason' => $validated['reason'],
            'created_by_user_id' => $request->user()->id,
        ]);

        $session = CashierSession::find($validated['cashier_session_id']);
        if ($validated['movement_type'] === 'cash_out') {
            $session->decrement('expected_cash', $validated['amount']);
        } else {
            $session->increment('expected_cash', $validated['amount']);
        }

        AnalyticsService::track('cash_movement_created', [...]);
        return $movement;
    }
}
```

### 17.5 QRIS Statis

**Backend:**
```php
class QrisService
{
    public function confirmPayment(Order $order, User $cashier): Payment
    {
        $ewallet = EwalletSetting::where('tenant_id', $order->tenant_id)->first();
        if (!$ewallet || !$ewallet->qris_enabled) {
            throw new QrisNotConfiguredException();
        }

        return DB::transaction(function () use ($order, $cashier) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'tenant_id' => $order->tenant_id,
                'method_name' => 'qris_static',
                'amount' => $order->grand_total,
                'status' => 'success',
                'paid_at' => now(),
            ]);
            $order->update(['status' => 'paid', 'paid_total' => $order->grand_total]);
            AnalyticsService::track('qris_payment_confirmed', [...]);
            return $payment;
        });
    }
}
```

### 17.6 Offline Cart (Koneksi Putus)

**Frontend (resources/js/core/offline.js):**
```javascript
const CART_KEY_PREFIX = 'kasirku_cart_';

export function saveCartToLocalStorage(tenantId, cart) {
    localStorage.setItem(CART_KEY_PREFIX + tenantId, JSON.stringify({
        cart, saved_at: new Date().toISOString(),
    }));
}

export function initOfflineManager(tenantId) {
    window.addEventListener('offline', () => {
        showOfflineBanner();
        saveCartToLocalStorage(tenantId, getCartState());
    });
    window.addEventListener('online', () => {
        hideOfflineBanner();
        syncOfflineCart(tenantId);
    });
}

async function syncOfflineCart(tenantId) {
    const saved = loadCartFromLocalStorage(tenantId);
    if (!saved) return;
    try {
        const res = await fetch('/app/api/pos/sync-offline-cart', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ cart: saved.cart }),
        });
        if (res.ok) {
            clearCartFromLocalStorage(tenantId);
            showToast('Transaksi tersinkron', 'success');
            track('offline_cart_synced', {});
        }
    } catch (e) {
        showToast('Gagal sinkron. Coba lagi.', 'error');
    }
}
```

### 17.7 Bluetooth Thermal Printer

**Frontend (resources/js/core/bluetooth.js):**
```javascript
export class BluetoothPrinter {
    async connect() {
        if (!navigator.bluetooth) {
            showToast('Browser tidak mendukung Bluetooth', 'warning');
            return false;
        }
        this.device = await navigator.bluetooth.requestDevice({
            filters: [{ services: ['000018f0-0000-1000-8000-00805f9b34fb'] }],
        });
        this.server = await this.device.gatt.connect();
        const service = await this.server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb');
        this.characteristic = await service.getCharacteristic('00002af1-0000-1000-8000-00805f9b34fb');
        track('bluetooth_printer_connected', { device_name: this.device.name });
        return true;
    }

    async printReceipt(receiptData) {
        if (!this.characteristic) { window.print(); return; }
        const commands = this.buildEscPosCommands(receiptData);
        await this.characteristic.writeValue(commands);
    }
}
```

### 17.8 Promosi (Voucher + Diskon Terjadwal)

**Backend:**
```php
class VoucherService
{
    public function redeem(string $code, Order $order, ?Customer $customer): float
    {
        $voucher = Voucher::where('tenant_id', $order->tenant_id)
            ->where('code', $code)->where('is_active', true)->firstOrFail();

        if (now()->lt($voucher->starts_at) || now()->gt($voucher->ends_at)) {
            throw new VoucherExpiredException();
        }
        if ($order->subtotal < $voucher->min_purchase) {
            throw new MinPurchaseNotMetException($voucher->min_purchase);
        }
        if ($voucher->max_usage_total && $voucher->usage_count >= $voucher->max_usage_total) {
            throw new VoucherUsageLimitException();
        }
        if ($order->status === 'credit') {
            throw new VoucherCreditNotAllowedException();
        }

        $discount = $voucher->discount_type === 'percent'
            ? $order->subtotal * ($voucher->discount_value / 100)
            : $voucher->discount_value;

        DB::transaction(function () use ($voucher, $order, $customer, $discount) {
            VoucherRedemption::create([
                'tenant_id' => $order->tenant_id,
                'voucher_id' => $voucher->id,
                'order_id' => $order->id,
                'customer_id' => $customer?->id,
                'discount_applied' => $discount,
            ]);
            $voucher->increment('usage_count');
            $order->update(['voucher_discount' => $discount, 'voucher_id' => $voucher->id]);
            AnalyticsService::track('voucher_redeemed', [...]);
        });
        return $discount;
    }
}
```

### 17.9 Pencarian Global

**Backend:**
```php
class GlobalSearchService
{
    public function search(string $query, User $user): array
    {
        $tenantId = $user->tenant_id;
        $results = [];

        $results['products'] = Product::where('tenant_id', $tenantId)
            ->where('name', 'LIKE', "%{$query}%")
            ->orWhere('sku', 'LIKE', "%{$query}%")
            ->limit(5)->get(['id', 'name', 'price', 'stock_qty']);

        if (in_array($user->role, ['owner', 'admin'])) {
            $results['customers'] = Customer::where('tenant_id', $tenantId)
                ->where('name', 'LIKE', "%{$query}%")
                ->orWhere('phone', 'LIKE', "%{$query}%")
                ->limit(5)->get(['id', 'name', 'phone', 'loyalty_points']);
        }

        $results['orders'] = Order::where('tenant_id', $tenantId)
            ->where('order_number', 'LIKE', "%{$query}%")
            ->limit(5)->get(['id', 'order_number', 'grand_total', 'status', 'created_at']);

        return $results;
    }
}
```

### 17.10 Notifikasi Perubahan Data

**Backend (Observer):**
```php
class ProductObserver
{
    public function updated(Product $product)
    {
        $changes = $product->getChanges();
        $oldValues = $product->getOriginal();

        if (isset($changes['price'])) {
            NotificationService::sendToTenantOwners($product->tenant_id, [
                'type' => 'product_price_changed',
                'title' => "Harga produk '{$product->name}' diubah",
                'body' => sprintf('%s mengubah harga produk "%s": %s → %s',
                    auth()->user()->name, $product->name,
                    formatRupiah($oldValues['price']), formatRupiah($changes['price'])),
            ]);
        }
    }
}
```

### 17.11 Setup Awal Owner

**Backend:**
```php
class SetupChecklistService
{
    private const STEPS = [
        1 => 'outlet', 2 => 'product', 3 => 'employee',
        4 => 'tax_receipt', 5 => 'cashier_settings', 6 => 'device',
        7 => 'ewallet', 8 => 'customer', 9 => 'promotion', 10 => 'notification',
    ];

    public function completeStep(Tenant $tenant, int $step): void
    {
        $tenant->update(['setup_step' => max($tenant->setup_step, $step)]);
        if ($step === 10) {
            $tenant->update(['setup_completed_at' => now()]);
        }
        AnalyticsService::track('setup_checklist_step_completed', [
            'step' => $step, 'step_name' => self::STEPS[$step],
        ]);
    }
}
```

---

## §18. Verifikasi & Sign-off

### 18.1 Verifikasi Kelengkapan

| Aspek | Jumlah | Status |
|---|---|---|
| Prinsip arsitektur | 28 | ✅ |
| Aturan arsitektur | 68 | ✅ |
| ADR | 88 | ✅ |
| Middleware | 18 | ✅ |
| Route group | 5 | ✅ |
| Contracts | 8 | ✅ |
| Env vars MVP | 50 | ✅ |
| Env vars PROD | 18 | ✅ |
| Analytics events | 54 | ✅ |
| E2E flow | 50 | ✅ |
| Lapisan test | 7 | ✅ |
| Docker Compose config | 3 | ✅ |
| Tabel Phase 9 | 6 | ✅ |
| Section | 18 | ✅ |
| Vanilla frontend enforced | ✅ | ✅ |
| Placeholder/TBD | 0 | ✅ |
| Fitur baru tercover | 16/16 | ✅ |

### 18.2 Checklist Vanilla Stack

| Aspek | Status |
|---|---|
| Tidak ada React di package.json | ✅ |
| Tidak ada Vue di package.json | ✅ |
| Tidak ada TypeScript (tsconfig.json) | ✅ |
| Tidak ada Tailwind (tailwind.config.js) | ✅ |
| Tidak ada Inertia (@inertiajs/*) | ✅ |
| Tidak ada Alpine/HTMX/Livewire | ✅ |
| JS pakai `<script type="module">` | ✅ |
| CSS pakai custom properties | ✅ |
| Blade untuk server-side rendering | ✅ |
| Partial route untuk AJAX | ✅ |
| Render engine render() + renderNow() | ✅ |
| Client-side error tracking | ✅ |
| ESLint 9+ flat config | ✅ |
| Stylelint config | ✅ |
| Web Bluetooth untuk printer | ✅ |
| localStorage untuk offline cart | ✅ |
| Canvas API untuk chart | ✅ |
| Touch events untuk gesture | ✅ |

### 18.3 Checklist Staging (Tanpa CC)

| Aspek | Status |
|---|---|
| Render.com free tier (tanpa CC) | ✅ |
| TiDB Cloud Serverless (tanpa CC) | ✅ |
| Upstash free (tanpa CC) | ✅ |
| Cloudflare R2 free 10 GB (tanpa CC) | ✅ |
| Instatus.com free (tanpa CC) | ✅ |
| Sentry free (tanpa CC) | ✅ |
| Total biaya staging | Rp0 |

### 18.4 Sign-off

| Role | Nama | Tanggal |
|---|---|---|
| Product Manager | _________________ | ______ |
| Engineering Lead | _________________ | ______ |
| Backend Lead | _________________ | ______ |
| Frontend Lead | _________________ | ______ |
| DevOps / SRE Lead | _________________ | ______ |
| QA Lead | _________________ | ______ |
| Security Lead | _________________ | ______ |
| Stakeholder / Investor | _________________ | ______ |

---

## Ringkasan Eksekutif

**Architecture v2.0** adalah panduan teknis final untuk Kasirku.

**Backend:** Laravel 13 + PHP 8.4 + MySQL 8.4 + Redis 7.4.

**Frontend:** HTML + CSS + JavaScript vanilla murni (ES modules) + Blade — tanpa React, Vue, TypeScript, Tailwind, Inertia, Alpine, HTMX, Livewire.

**Staging (tanpa CC):** Render.com + TiDB Cloud + Upstash + Cloudflare R2 + Instatus.com — semua 100% gratis tanpa kartu kredit.

**Production:** VPS 8 GB Jakarta + 6 self-hosted tools.

**Prinsip utama (28):** responsive web only, server = sumber kebenaran, session-based auth, multi-tenant isolation, adapter pattern, guard rules, vanilla-first frontend, low-latency POS rendering, speed-first POS login, guided setup, immutable ledger pattern, offline-resilient POS.

**Komponen utama:**
- 18 middleware
- 5 route group
- 8 contracts adapter
- 8 guard rules
- 54 analytics events
- 7 lapisan test
- 50 E2E flow
- 88 ADR
- 68 aturan arsitektur
- CI 11 langkah
- Production readiness: RG-1–RG-10, Cutover #1–#10, Rollback + re-auth

**Fitur baru tercover:**
- Login POS PIN-only (device-based)
- Switch User POS
- Hold Order (maks 10 per outlet)
- Kasbon/Piutang (limit config, loyalty saat lunas)
- Kas Keluar/Masuk (immutable, alasan wajib)
- QRIS Statis (konfirmasi manual)
- Split Rata
- Refund Parsial per Item
- Print Ulang Struk
- Koneksi Putus (offline cart localStorage)
- Bluetooth Thermal Printer (Web Bluetooth API)
- Laporan Produk Terlaris
- Pencarian Global (Ctrl+K)
- Setup Awal Owner (10 langkah)
- Promosi (Voucher + Diskon Terjadwal)
- Notifikasi Perubahan Data

**Status:** ✅ Siap eksekusi. 88 ADR, 68 aturan, 54 events, 7 lapisan test, 18 middleware, 5 route group, 100% terisi.

