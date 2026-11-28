# SCHEMA — KASIRKU v2.0

**Versi:** 2.0 | **Status:** Final | **Tanggal:** 2026-11-16

---

## Daftar Isi

```
§1   Overview & Konvensi
§2   ERD Global (Semua Domain)
§3   ERD per Domain (Detail)
§4   DDL Lengkap (80 Tabel)
§5   Circular Dependency Handling
§6   Immutable Tables
§7   Seeder (27)
§8   Migration Rules
§9   Environment Database Matrix
§10  Verifikasi & Sign-off
```

---

## §1. Overview & Konvensi

### 1.1 Ringkasan

| Aspek | Nilai |
|---|---|
| Total tabel | 80 |
| Tabel MVP | 74 (#1–#74) |
| Tabel Production | 6 (#75–#80) |
| Domain | 15 |
| Circular dependency | 2 (via ALTER TABLE) |
| Immutable tables | 10 |
| Seeder | 27 (8 reference + 16 demo + 3 production) |
| Database engine | MySQL 8.4 LTS |
| Staging DB | TiDB Cloud Serverless (MySQL-compatible) |
| Production DB | MySQL 8.4 self-managed di VPS Jakarta |

### 1.2 Konvensi

| Aspek | Aturan |
|---|---|
| Engine | InnoDB |
| Charset | utf8mb4 |
| Collation | utf8mb4_unicode_ci |
| Primary key | BIGINT UNSIGNED AUTO_INCREMENT atau CHAR(36) UUID |
| UUID | CHAR(36) untuk: tenants, releases, cutover_runs, deployments, rollback_events, analytics_events, export_jobs, support_access_sessions |
| Uang | DECIMAL(15,2) — bukan FLOAT |
| Persen | DECIMAL(5,2) |
| Boolean | BOOLEAN NOT NULL DEFAULT FALSE/TRUE |
| Status | VARCHAR(20–30) dengan COMMENT — bukan ENUM |
| Timestamp | TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP |
| Soft delete | deleted_at TIMESTAMP NULL |
| Kolom standar | id, created_at, updated_at (+deleted_at jika soft delete) |
| Naming tabel | snake_case, plural |
| Naming kolom | snake_case |
| FK | Wajib dideklarasikan eksplisit |
| Index | Wajib untuk kolom WHERE/JOIN/ORDER BY |
| CHECK constraint | Wajib untuk validasi bisnis di level DB (MySQL 8.4 mendukung) |
| Migration naming | YYYY_MM_DD_HHMMSS_create_{table}_table.php |
| Snapshot | Kolom snapshot disimpan langsung di tabel (product_name, unit_price, cost_price, sku, modifiers) |

### 1.3 Urutan Migration

Nomor urut tabel mengikuti **migration dependency order** — semua FK menunjuk ke tabel yang sudah dibuat sebelumnya, kecuali 2 circular dependency yang di-handle via ALTER TABLE.

### 1.4 Aturan TiDB Cloud (Staging)

| Aspek | MySQL 8.4 (Production) | TiDB Cloud (Staging) |
|---|---|---|
| FOREIGN KEY | Fully enforced | Supported (sejak TiDB 6.6+) |
| AUTO_INCREMENT | Standard | Standard |
| UUID CHAR(36) | Standard | Standard |
| DECIMAL | Standard | Standard |
| JSON | Standard | Standard |
| TIMESTAMP | Standard | Standard |
| Partition | Supported | Supported |
| utf8mb4 | Standard | Standard |
| CHECK constraint | Fully enforced | Supported (sejak TiDB 6.6+) |
| Generated column | Supported | Supported |

> **Catatan:** Semua DDL di dokumen ini kompatibel dengan MySQL 8.4 dan TiDB Cloud Serverless. Tidak ada fitur MySQL-specific yang tidak didukung TiDB.

---

## §2. ERD Global (Semua Domain)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        ERD GLOBAL — KASIRKU (80 TABEL)                     │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐                    │
│  │   SHARED    │    │   ADMIN /   │    │   TENANT    │                    │
│  │  (4 tabel)  │    │  INTERNAL   │    │  CORE &     │                    │
│  │             │    │  (3 tabel)  │    │  AKSES      │                    │
│  │ plans       │    │             │    │ (10 tabel)  │                    │
│  │ features    │◄───┤ admin_users │    │             │                    │
│  │ plan_feat.  │    │ admin_sess. │    │ tenants ◄───┼─── (pusat semua)  │
│  │ add_ons     │    │ partners    │    │ outlets     │                    │
│  └──────┬──────┘    └──────┬──────┘    │ users       │                    │
│         │                  │           │ user_outlets│                    │
│         │                  │           │ user_sess.  │                    │
│         │                  │           │ pwd_resets  │                    │
│         │                  │           │ otp_verif.  │                    │
│         │                  │           │ email_verif.│                    │
│         │                  │           │ invitations │                    │
│         │                  │           │ pos_devices │                    │
│         │                  │           └──────┬──────┘                    │
│         │                  │                  │                           │
│  ┌──────▼──────┐    ┌──────▼──────┐    ┌──────▼──────┐                    │
│  │  INTERNAL   │    │  SETTINGS   │    │  PRODUK &   │                    │
│  │  (5 tabel)  │    │  AWAL &     │    │  KATEGORI   │                    │
│  │             │    │  PRODUK     │    │  (6 tabel)  │                    │
│  │ tenant_feat.│    │  (6 tabel)  │    │             │                    │
│  │ usage_rec.  │    │             │    │ categories  │                    │
│  │ billing_inv.│    │ categories  │    │ tax_rates   │                    │
│  │ partner_ref.│    │ tax_rates   │    │ products    │                    │
│  │ support_acc.│    │ products    │    │ prod_var.   │                    │
│  │             │    │ prod_var.   │    │ prod_taxes  │                    │
│  │             │    │ prod_taxes  │    │ modifiers   │                    │
│  │             │    │ modifiers   │    └──────┬──────┘                    │
│  └─────────────┘    └──────┬──────┘           │                           │
│                            │                  │                           │
│                     ┌──────▼──────┐    ┌──────▼──────┐                    │
│                     │  MODIFIER,  │    │     POS     │                    │
│                     │  INVENTORI, │    │  (10 tabel) │                    │
│                     │  MEJA       │    │             │                    │
│                     │ (10 tabel)  │    │ orders ◄────┼─── (pusat POS)    │
│                     │             │    │ order_items │                    │
│                     │ modifier_opt│    │ order_splits│                    │
│                     │ prod_modif. │    │ split_items │                    │
│                     │ suppliers   │    │ order_taxes │                    │
│                     │ purchase_ord│    │ payments    │                    │
│                     │ po_items    │    │ refunds     │                    │
│                     │ stock_mov.  │    │ refund_items│ 🆕                 │
│                     │ tables      │    │ invoices    │                    │
│                     │ table_sess. │    │ inv_items   │                    │
│                     │ pay_methods │    └──────┬──────┘                    │
│                     │ cashier_ses.│           │                           │
│                     │ cash_mov. 🆕│    ┌──────▼──────┐                    │
│                     └──────┬──────┘    │  CRM &      │                    │
│                            │           │  LOYALTY &  │                    │
│                            │           │  SHIFT      │                    │
│                            │           │  (3 tabel)  │                    │
│                            │           │             │                    │
│                            │           │ customers   │                    │
│                            │           │ loyalty_pts │                    │
│                            │           │ shift_repts │                    │
│                            │           └──────┬──────┘                    │
│                            │                  │                           │
│  ┌─────────────┐    ┌──────▼──────┐    ┌──────▼──────┐                    │
│  │  LANGGANAN  │    │  SETTINGS   │    │ CROSS-CUT   │                    │
│  │  (3 tabel)  │    │  (7 tabel)  │    │ & COMPLIANCE│                    │
│  │             │    │             │    │  (6 tabel)  │                    │
│  │ subscriptions│   │ receipt_set.│    │             │                    │
│  │ tenant_addon│    │ tax_settings│    │ audit_logs  │                    │
│  │ plan_change │    │ svc_charge  │    │ notifications│                   │
│  └─────────────┘    │ rounding_set│    │ cookie_cons.│                    │
│                     │ cashier_set.│🆕  │ export_jobs │                    │
│  ┌─────────────┐    │ ewallet_set.│🆕  │ acct_del.   │                    │
│  │  PROMOSI    │    │ notif_pref. │🆕  │ retention_j.│                    │
│  │  (4 tabel)  │    └─────────────┘    └─────────────┘                    │
│  │             │                                                           │
│  │ vouchers 🆕 │    ┌─────────────┐                                       │
│  │ voucher_red.│🆕  │  SUPPORT &  │                                       │
│  │ sched_disc. │🆕  │  ANALYTICS  │                                       │
│  │             │    │  (3 tabel)  │    ┌─────────────┐                    │
│  └─────────────┘    │             │    │ PRODUCTION  │                    │
│                     │ support_tix │    │ READINESS   │                    │
│                     │ tix_msgs    │    │ [PROD]      │                    │
│                     │ analytics_e.│    │ (6 tabel)   │                    │
│                     └─────────────┘    │             │                    │
│                                        │ releases    │                    │
│                                        │ rel_gates   │                    │
│                                        │ deployments │                    │
│                                        │ cutover_runs│                    │
│                                        │ cutover_item│                    │
│                                        │ rollback_ev.│                    │
│                                        └─────────────┘                    │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 2.1 Relasi Utama

| Dari | Ke | Relasi | FK |
|---|---|---|---|
| tenants | — | Pusat semua domain | — |
| users | tenants | N:1 | users.tenant_id → tenants.id |
| tenants | users | 1:1 (owner) | tenants.owner_user_id → users.id ⚠️ ALTER |
| outlets | tenants | N:1 | outlets.tenant_id → tenants.id |
| products | tenants | N:1 | products.tenant_id → tenants.id |
| products | categories | N:1 | products.category_id → categories.id |
| products | tax_rates | N:1 | products.tax_rate_id → tax_rates.id |
| orders | tenants | N:1 | orders.tenant_id → tenants.id |
| orders | outlets | N:1 | orders.outlet_id → outlets.id |
| orders | users (cashier) | N:1 | orders.cashier_user_id → users.id |
| orders | customers | N:1 | orders.customer_id → customers.id |
| order_items | orders | N:1 | order_items.order_id → orders.id |
| payments | orders | N:1 | payments.order_id → orders.id |
| subscriptions | tenants | N:1 | subscriptions.tenant_id → tenants.id |
| subscriptions | plans | N:1 | subscriptions.plan_id → plans.id |
| stock_movements | tenants | N:1 | stock_movements.tenant_id → tenants.id |
| stock_movements | products | N:1 | stock_movements.product_id → products.id |
| customer_loyalty_points | customers | N:1 | customer_loyalty_points.customer_id → customers.id |
| shift_reports | cashier_sessions | 1:1 | shift_reports.cashier_session_id → cashier_sessions.id |
| audit_logs | tenants | N:1 | audit_logs.tenant_id → tenants.id |
| cash_movements | cashier_sessions | N:1 | cash_movements.cashier_session_id → cashier_sessions.id |
| vouchers | tenants | N:1 | vouchers.tenant_id → tenants.id |
| voucher_redemptions | vouchers | N:1 | voucher_redemptions.voucher_id → vouchers.id |
| voucher_redemptions | orders | N:1 | voucher_redemptions.order_id → orders.id |
| scheduled_discounts | tenants | N:1 | scheduled_discounts.tenant_id → tenants.id |
| refund_items | refunds | N:1 | refund_items.refund_id → refunds.id |
| refund_items | order_items | N:1 | refund_items.order_item_id → order_items.id |
| cashier_settings | outlets | N:1 | cashier_settings.outlet_id → outlets.id |
| notification_preferences | users | 1:1 | notification_preferences.user_id → users.id |

---

## §3. ERD per Domain (Detail)

*(ERD per domain mengikuti struktur yang sama dengan ERD global. Domain baru ditambahkan di bawah.)*

### 3.1 ERD — Shared (4 Tabel)

```
┌──────────────────┐       ┌──────────────────┐
│     plans        │       │    features      │
├──────────────────┤       ├──────────────────┤
│ id (PK)          │       │ id (PK)          │
│ code (UQ)        │       │ code (UQ)        │
│ name             │       │ name             │
│ monthly_price    │       │ description      │
│ yearly_price     │       │ category         │
│ max_pos          │       │ created_at       │
│ max_employees    │       └────────┬─────────┘
│ max_admins       │                │
│ trial_days       │                │
│ is_trial_plan    │                │
│ is_active        │                │
│ sort_order       │                │
│ created_at       │                │
│ updated_at       │                │
└────────┬─────────┘                │
         │                          │
         │ 1:N                      │ 1:N
         ▼                          ▼
┌──────────────────────────────────────────────┐
│              plan_features                    │
├──────────────────────────────────────────────┤
│ id (PK)                                       │
│ plan_id (FK → plans.id)                       │
│ feature_id (FK → features.id)                 │
│ is_enabled                                    │
│ created_at                                    │
│ UQ(plan_id, feature_id)                       │
└──────────────────────────────────────────────┘

┌──────────────────┐
│     add_ons      │
├──────────────────┤
│ id (PK)          │
│ code (UQ)        │
│ name             │
│ unit_price       │
│ unit             │
│ is_active        │
│ created_at       │
│ updated_at       │
└──────────────────┘
```

### 3.2 ERD — Admin/Internal (3 Tabel)

```
┌──────────────────────────────┐
│        admin_users           │
├──────────────────────────────┤
│ id (PK)                      │
│ name                         │
│ email (UQ)                   │
│ password_hash                │
│ role                         │
│ mfa_secret                   │
│ mfa_recovery_codes           │
│ mfa_enabled_at               │
│ is_active                    │
│ last_login_at                │
│ created_at, updated_at       │
│ deleted_at                   │
└──────────┬───────────────────┘
           │ 1:N
           ▼
┌──────────────────────────────┐
│       admin_sessions         │
├──────────────────────────────┤
│ id (PK)                      │
│ session_id (UQ)              │
│ admin_user_id (FK)           │
│ ip_address                   │
│ user_agent                   │
│ mfa_verified_at              │
│ reauth_at                    │
│ last_activity_at             │
│ revoked_at                   │
│ created_at                   │
└──────────────────────────────┘

┌──────────────────────────────┐
│         partners             │
├──────────────────────────────┤
│ id (PK)                      │
│ name                         │
│ code (UQ)                    │
│ email                        │
│ phone                        │
│ commission_rate              │
│ is_active                    │
│ created_at, updated_at       │
└──────────────────────────────┘
```

### 3.3 ERD — Tenant Core & Akses (10 Tabel)

```
┌────────────────────────────────────────────────────────────────────┐
│                          tenants                                    │
├────────────────────────────────────────────────────────────────────┤
│ id (PK, CHAR(36) UUID)                                              │
│ name                                                                │
│ slug (UQ)                                                           │
│ owner_user_id (FK → users.id) ⚠️ ALTER (circular #1)               │
│ industry, business_type                                             │
│ phone, email, address                                               │
│ province, city, district, postal_code                               │
│ logo_url                                                            │
│ social_media (JSON)                                                 │
│ status ('trial/pending_payment/active/grace/expired/archived/      │
│         suspended')                                                 │
│ trial_ends_at                                                       │
│ read_only_at                                                        │
│ expired_banner_dismissed_at                                         │
│ suspended_reason                                                    │
│ created_at, updated_at, deleted_at                                  │
└────────────┬───────────────────────────────────────────────────────┘
             │ 1:N
             ├──────────────────────────────────────────────────────┐
             │                                                      │
             ▼                                                      ▼
┌────────────────────────┐                           ┌────────────────────────┐
│       outlets          │                           │        users           │
├────────────────────────┤                           ├────────────────────────┤
│ id (PK)                │                           │ id (PK)                │
│ tenant_id (FK)         │                           │ tenant_id (FK)         │
│ name                   │                           │ name                   │
│ code                   │                           │ email                  │
│ address                │                           │ email_verified_at      │
│ phone                  │                           │ password_hash          │
│ timezone               │                           │ phone                  │
│ tax_inclusive          │                           │ role                   │
│ is_active              │                           │ pin_hash               │
│ created_at, updated_at │                           │ avatar_url             │
│ deleted_at             │                           │ is_active              │
│ UQ(tenant_id, code)    │                           │ onboarding_step        │
└──────────┬─────────────┘                           │ onboarding_completed_at│
           │                                         │ onboarding_skipped_    │
           │ 1:N                                     │   survey               │
           ├──────────────────┐                      │ last_login_at          │
           │                  │                      │ created_at, updated_at │
           ▼                  ▼                      │ deleted_at             │
┌──────────────────┐  ┌──────────────────┐          │ UQ(tenant_id, email)   │
│  user_outlets    │  │  pos_devices     │          └──────────┬─────────────┘
├──────────────────┤  ├──────────────────┤                     │ 1:N
│ id (PK)          │  │ id (PK)          │                     │
│ tenant_id (FK)   │  │ tenant_id (FK)   │                     ▼
│ user_id (FK)     │  │ outlet_id (FK)   │          ┌──────────────────┐
│ outlet_id (FK)   │  │ user_id (FK)     │          │  user_sessions   │
│ role_in_outlet   │  │ device_name      │          ├──────────────────┤
│ created_at       │  │ device_id (UQ)   │          │ id (PK)          │
│ UQ(user,outlet)  │  │ platform         │          │ session_id (UQ)  │
└──────────────────┘  │ activation_code  │          │ tenant_id (FK)   │
                      │ activation_      │          │ user_id (FK)     │
                      │   expires_at     │          │ ip_address       │
                      │ last_seen_at     │          │ user_agent       │
                      │ revoked_at       │          │ is_remember_me   │
                      │ created_at       │          │ last_activity_at │
                      └──────────────────┘          │ revoked_at       │
                                                    │ created_at       │
                                                    └──────────────────┘

┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐
│ password_resets  │  │ otp_verifications│  │email_verifications│
├──────────────────┤  ├──────────────────┤  ├──────────────────┤
│ id (PK)          │  │ id (PK)          │  │ id (PK)          │
│ user_id (FK)     │  │ tenant_id        │  │ user_id (FK)     │
│ token_hash       │  │ target           │  │ email            │
│ expires_at       │  │ channel          │  │ token_hash       │
│ used_at          │  │ purpose          │  │ expires_at       │
│ created_at       │  │ code_hash        │  │ verified_at      │
└──────────────────┘  │ attempts         │  │ resend_count     │
                      │ expires_at       │  │ created_at       │
                      │ used_at          │  └──────────────────┘
                      │ created_at       │
                      └──────────────────┘  ┌──────────────────┐
                                            │tenant_invitations│
                                            ├──────────────────┤
                                            │ id (PK)          │
                                            │ tenant_id (FK)   │
                                            │ email            │
                                            │ role             │
                                            │ token_hash       │
                                            │ invited_by_user  │
                                            │   _id (FK)       │
                                            │ expires_at       │
                                            │ accepted_at      │
                                            │ created_at       │
                                            └──────────────────┘
```

### 3.4 ERD — Internal Tenant-Dependent (5 Tabel)

```
┌──────────────────────────────┐
│   tenant_feature_overrides   │
├──────────────────────────────┤
│ id (PK)                      │
│ tenant_id (FK → tenants)     │
│ feature_id (FK → features)   │
│ is_enabled                   │
│ reason                       │
│ set_by_admin_user_id         │
│   (FK → admin_users)         │
│ created_at, updated_at       │
│ UQ(tenant_id, feature_id)    │
└──────────────────────────────┘

┌──────────────────────────────┐
│       usage_records          │
├──────────────────────────────┤
│ id (PK)                      │
│ tenant_id (FK)               │
│ period ('YYYY-MM')           │
│ pos_count                    │
│ employee_count               │
│ admin_count                  │
│ created_at, updated_at       │
│ UQ(tenant_id, period)        │
└──────────────────────────────┘

┌──────────────────────────────┐
│      billing_invoices        │
├──────────────────────────────┤
│ id (PK)                      │
│ tenant_id (FK)               │
│ subscription_id (FK)         │
│   ⚠️ ALTER (circular #3)     │
│ invoice_number (UQ)          │
│ amount, tax_amount, total    │
│ status                       │
│ due_date, paid_at            │
│ notes                        │
│ created_at, updated_at       │
└──────────────────────────────┘

┌──────────────────────────────┐
│      partner_referrals       │
├──────────────────────────────┤
│ id (PK)                      │
│ partner_id (FK → partners)   │
│ tenant_id (FK)               │
│ status                       │
│ created_at, updated_at       │
│ UQ(tenant_id)                │
└──────────────────────────────┘

┌──────────────────────────────────┐
│    support_access_sessions       │
├──────────────────────────────────┤
│ id (PK, CHAR(36) UUID)           │
│ admin_user_id (FK)               │
│ tenant_id (FK)                   │
│ granted_by_user_id (FK)          │
│ granted_at                       │
│ reason                           │
│ mode ('read/write')              │
│ started_at, expires_at           │
│ ended_at                         │
│ approved_at                      │
│ created_at                       │
└──────────────────────────────────┘
```

### 3.5 ERD — Produk, Kategori, Modifier, Inventori (16 Tabel)

```
┌──────────────────┐       ┌──────────────────┐
│    categories    │       │    tax_rates     │
├──────────────────┤       ├──────────────────┤
│ id (PK)          │       │ id (PK)          │
│ tenant_id (FK)   │       │ tenant_id (FK)   │
│ name             │       │ name             │
│ sort_order       │       │ code             │
│ is_active        │       │ rate             │
│ created_at       │       │ is_default       │
│ updated_at       │       │ is_inclusive     │
└────────┬─────────┘       │ is_active        │
         │ 1:N             │ created_at       │
         │                 │ updated_at       │
         │                 │ deleted_at       │
         │                 └────────┬─────────┘
         │                          │ 1:N
         ▼                          ▼
┌──────────────────────────────────────────────────────────────┐
│                       products                                │
├──────────────────────────────────────────────────────────────┤
│ id (PK)                                                       │
│ tenant_id (FK)                                                │
│ category_id (FK → categories, SET NULL)                       │
│ sku, barcode                                                  │
│ name                                                          │
│ description                                                   │
│ price, cost_price, wholesale_price                            │
│ wholesale_min_qty                                             │
│ image_url                                                     │
│ unit                                                          │
│ track_stock (BOOLEAN)                                         │
│ stock_qty (INT, denormalized)                                 │
│ low_stock_alert (INT)                                         │
│ tax_rate_id (FK → tax_rates, SET NULL)                        │
│ tax_inclusive (BOOLEAN)                                       │
│ is_bundle (BOOLEAN)                                           │
│ is_active (BOOLEAN)                                           │
│ metadata (JSON)                                               │
│ created_at, updated_at, deleted_at                            │
└────────────┬─────────────────────────────────────────────────┘
             │ 1:N
             ├──────────────────────────────────────────────────┐
             │                                                  │
             ▼                                                  ▼
┌────────────────────────┐                       ┌────────────────────────┐
│   product_variants     │                       │    product_taxes       │
├────────────────────────┤                       ├────────────────────────┤
│ id (PK)                │                       │ id (PK)                │
│ tenant_id (FK)         │                       │ tenant_id (FK)         │
│ product_id (FK)        │                       │ product_id (FK)        │
│ name                   │                       │ tax_rate_id (FK)       │
│ sku, barcode           │                       │ tax_name               │
│ price_adjustment       │                       │ tax_rate               │
│ cost_price             │                       │ created_at             │
│ stock_qty              │                       │ UQ(product_id,         │
│ low_stock_alert        │                       │    tax_name)           │
│ is_active              │                       └────────────────────────┘
│ sort_order             │
│ created_at, updated_at │
│ UQ(product_id, name)   │
└────────────────────────┘

┌──────────────────────────────┐
│        modifiers             │
├──────────────────────────────┤
│ id (PK)                      │
│ tenant_id (FK)               │
│ name                         │
│ description                  │
│ is_required (BOOLEAN)        │
│ max_selection (TINYINT)      │
│ min_selection (TINYINT)      │
│ sort_order                   │
│ is_active                    │
│ created_at, updated_at       │
└──────────┬───────────────────┘
           │ 1:N
           ├──────────────────────────────────────────┐
           │                                          │
           ▼                                          ▼
┌──────────────────────────────┐       ┌──────────────────────────────┐
│      modifier_options        │       │      product_modifiers       │
├──────────────────────────────┤       ├──────────────────────────────┤
│ id (PK)                      │       │ id (PK)                      │
│ modifier_id (FK)             │       │ tenant_id (FK)               │
│ tenant_id (FK)               │       │ product_id (FK)              │
│ name                         │       │ modifier_id (FK)             │
│ price_adjustment             │       │ sort_order                   │
│ sort_order                   │       │ created_at                   │
│ is_active                    │       │ UQ(product_id, modifier_id)  │
│ created_at, updated_at       │       └──────────────────────────────┘
│ UQ(modifier_id, name)        │
└──────────────────────────────┘

┌──────────────────────────────┐
│        suppliers             │
├──────────────────────────────┤
│ id (PK)                      │
│ tenant_id (FK)               │
│ name                         │
│ contact_name                 │
│ phone, email                 │
│ address, notes               │
│ is_active                    │
│ created_at, updated_at       │
│ deleted_at                   │
└──────────┬───────────────────┘
           │ 1:N
           ▼
┌──────────────────────────────────────────────────────────┐
│              purchase_orders                              │
├──────────────────────────────────────────────────────────┤
│ id (PK)                                                   │
│ tenant_id (FK)                                            │
│ outlet_id (FK → outlets)                                  │
│ supplier_id (FK → suppliers, SET NULL)                    │
│ po_number                                                 │
│ status ('draft/sent/partial/received/cancelled')          │
│ order_date, expected_date, received_at                    │
│ subtotal, tax_total, shipping_cost, total                 │
│ notes                                                     │
│ created_by_user_id (FK → users)                           │
│ approved_by_user_id (FK → users, SET NULL)                │
│ created_at, updated_at                                    │
│ UQ(tenant_id, po_number)                                  │
└──────────┬───────────────────────────────────────────────┘
           │ 1:N
           ▼
┌──────────────────────────────────────────────────────────┐
│            purchase_order_items                           │
├──────────────────────────────────────────────────────────┤
│ id (PK)                                                   │
│ purchase_order_id (FK)                                    │
│ tenant_id (FK)                                            │
│ product_id (FK → products, SET NULL)                      │
│ variant_id (FK → product_variants, SET NULL)              │
│ product_name (snapshot)                                   │
│ sku                                                       │
│ quantity, received_qty                                    │
│ unit_cost                                                 │
│ discount_amount, tax_amount, total                        │
│ notes                                                     │
│ created_at, updated_at                                    │
└──────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────┐
│            stock_movements (IMMUTABLE)                    │
├──────────────────────────────────────────────────────────┤
│ id (PK)                                                   │
│ tenant_id (FK)                                            │
│ outlet_id (FK → outlets)                                  │
│ product_id (FK → products, SET NULL)                      │
│ variant_id (FK → product_variants, SET NULL)              │
│ movement_type ('sale/purchase/transfer_in/transfer_out/   │
│                adjustment/return/waste')                   │
│ quantity (positif = masuk, negatif = keluar)              │
│ unit_cost                                                 │
│ balance_after                                             │
│ reference_type, reference_id                              │
│ notes (wajib untuk adjustment/waste)                      │
│ created_by_user_id (FK → users, SET NULL)                 │
│ created_at                                                │
└──────────────────────────────────────────────────────────┘
```

### 3.6 ERD — Meja F&B & Payment (4 Tabel)

```
┌──────────────────────────────┐
│         tables               │
├──────────────────────────────┤
│ id (PK)                      │
│ tenant_id (FK)               │
│ outlet_id (FK)               │
│ parent_table_id (FK → tables,│
│   SET NULL)                  │
│ name                         │
│ capacity                     │
│ area                         │
│ qr_code                      │
│ status                       │
│ is_active                    │
│ created_at, updated_at       │
│ UQ(outlet_id, name)          │
└──────────┬───────────────────┘
           │ 1:N
           ▼
┌──────────────────────────────┐
│      table_sessions          │
├──────────────────────────────┤
│ id (PK)                      │
│ tenant_id (FK)               │
│ table_id (FK)                │
│ order_id (FK → orders)       │
│   ⚠️ ALTER (circular #2)     │
│ opened_by_user_id (FK)       │
│ customer_name                │
│ pax                          │
│ opened_at, closed_at         │
│ status                       │
│ created_at, updated_at       │
└──────────────────────────────┘

┌──────────────────────────────┐       ┌──────────────────────────────┐
│      payment_methods         │       │      cashier_sessions        │
├──────────────────────────────┤       ├──────────────────────────────┤
│ id (PK)                      │       │ id (PK)                      │
│ tenant_id (FK)               │       │ tenant_id (FK)               │
│ name                         │       │ outlet_id (FK)               │
│ type                         │       │ cashier_user_id (FK)         │
│ provider                     │       │ pos_device_id (FK, SET NULL) │
│ settlement_account           │       │ opened_at, closed_at         │
│ mdr_percent, mdr_fixed       │       │ opening_cash                 │
│ is_active                    │       │ closing_cash                 │
│ created_at, updated_at       │       │ expected_cash                │
│ UQ(tenant_id, name)          │       │ difference                   │
└──────────────────────────────┘       │ notes                        │
                                       │ created_at                   │
                                       └──────────────────────────────┘
```

### 3.7 ERD — POS (9 Tabel)

```
┌────────────────────────────────────────────────────────────────────────┐
│                            orders                                       │
├────────────────────────────────────────────────────────────────────────┤
│ id (PK)                                                                 │
│ tenant_id (FK)                                                          │
│ outlet_id (FK → outlets)                                                │
│ order_number                                                            │
│ cashier_user_id (FK → users)                                            │
│ cashier_session_id (FK → cashier_sessions, SET NULL)                    │
│ table_id (FK → tables, SET NULL)                                        │
│ table_session_id (FK → table_sessions, SET NULL)                        │
│ customer_id (FK → customers, SET NULL)                                  │
│ customer_name                                                           │
│ order_type                                                              │
│ status ('draft/open/partially_paid/paid/void/refunded')                 │
│ subtotal                                                                │
│ discount_total                                                          │
│ tax_total                                                               │
│ service_charge_total                                                    │
│ rounding_adjustment                                                     │
│ grand_total                                                             │
│ paid_total                                                              │
│ change_total                                                            │
│ notes                                                                   │
│ void_reason                                                             │
│ voided_by_user_id (FK → users, SET NULL)                                │
│ voided_at                                                               │
│ metadata (JSON)                                                         │
│ created_at, updated_at, deleted_at                                      │
│ UQ(tenant_id, order_number)                                             │
└──────────┬─────────────────────────────────────────────────────────────┘
           │ 1:N
           ├──────────────────────────────────────────────────────────────┐
           │                                                              │
           ▼                                                              ▼
┌──────────────────────────────────┐               ┌──────────────────────────────────┐
│          order_items             │               │          order_splits            │
├──────────────────────────────────┤               ├──────────────────────────────────┤
│ id (PK)                          │               │ id (PK)                          │
│ order_id (FK)                    │               │ order_id (FK)                    │
│ tenant_id (FK)                   │               │ tenant_id (FK)                   │
│ product_id (FK, SET NULL)        │               │ split_number                     │
│ variant_id (FK, SET NULL)        │               │ subtotal                         │
│ product_name (snapshot)          │               │ tax_amount                       │
│ variant_name                     │               │ service_charge_amount            │
│ sku (snapshot)                   │               │ total                            │
│ unit_price (snapshot)            │               │ paid_amount                      │
│ cost_price (snapshot)            │               │ status                           │
│ quantity                         │               │ created_at                       │
│ discount_amount                  │               │ UQ(order_id, split_number)       │
│ tax_amount                       │               └──────────┬───────────────────────┘
│ total                            │                          │ 1:N
│ modifiers (JSON, snapshot)       │                          ▼
│ notes                            │               ┌──────────────────────────────────┐
│ created_at                       │               │       order_split_items          │
└──────────────────────────────────┘               ├──────────────────────────────────┤
                                                   │ id (PK)                          │
┌──────────────────────────────────┐               │ order_split_id (FK)              │
│         order_taxes              │               │ order_item_id (FK)               │
├──────────────────────────────────┤               │ tenant_id (FK)                   │
│ id (PK)                          │               │ quantity                         │
│ order_id (FK)                    │               │ unit_price                       │
│ tenant_id (FK)                   │               │ discount_amount                  │
│ tax_name                         │               │ tax_amount                       │
│ tax_rate                         │               │ amount                           │
│ tax_base                         │               │ created_at                       │
│ tax_amount                       │               │ UQ(order_split_id, order_item_id)│
│ created_at                       │               └──────────────────────────────────┘
└──────────────────────────────────┘

┌──────────────────────────────────┐       ┌──────────────────────────────────┐
│          payments                │       │          refunds                 │
├──────────────────────────────────┤       ├──────────────────────────────────┤
│ id (PK)                          │       │ id (PK)                          │
│ order_id (FK)                    │       │ order_id (FK)                    │
│ order_split_id (FK, SET NULL)    │       │ tenant_id (FK)                   │
│ tenant_id (FK)                   │       │ amount                           │
│ payment_method_id (FK, SET NULL) │       │ reason                           │
│ method_name                      │       │ refund_method                    │
│ amount                           │       │ processed_by_user_id (FK)        │
│ reference_number                 │       │ refunded_at                      │
│ status                           │       │ created_at                       │
│ paid_at                          │       └──────────────────────────────────┘
│ created_at                       │
└──────────────────────────────────┘

┌──────────────────────────────────┐       ┌──────────────────────────────────┐
│         invoices                 │       │        invoice_items             │
├──────────────────────────────────┤       ├──────────────────────────────────┤
│ id (PK)                          │       │ id (PK)                          │
│ tenant_id (FK)                   │       │ invoice_id (FK)                  │
│ invoice_number                   │       │ tenant_id (FK)                   │
│ customer_name                    │       │ product_name                     │
│ customer_address                 │       │ unit_price                       │
│ status                           │       │ quantity                         │
│ subtotal, discount_total         │       │ discount_amount                  │
│ shipping_cost, other_cost        │       │ total                            │
│ tax_total, grand_total           │       │ created_at                       │
│ due_date                         │       └──────────────────────────────────┘
│ notes                            │
│ created_at, updated_at           │
│ UQ(tenant_id, invoice_number)    │
└──────────────────────────────────┘
```

### 3.8 ERD — CRM & Loyalty & Shift (3 Tabel)

```
┌──────────────────────────────────┐
│         customers                │
├──────────────────────────────────┤
│ id (PK)                          │
│ tenant_id (FK)                   │
│ name                             │
│ phone                            │
│ email                            │
│ address                          │
│ birth_date                       │
│ gender                           │
│ notes                            │
│ loyalty_points (cache)           │
│ total_spent (cache)              │
│ visit_count (cache)              │
│ last_visit_at                    │
│ is_active                        │
│ created_at, updated_at           │
│ deleted_at                       │
└──────────┬───────────────────────┘
           │ 1:N
           ▼
┌──────────────────────────────────────────────────────────┐
│     customer_loyalty_points (IMMUTABLE)                   │
├──────────────────────────────────────────────────────────┤
│ id (PK)                                                   │
│ tenant_id (FK)                                            │
│ customer_id (FK → customers)                              │
│ points (positif = earn, negatif = redeem/adjust)          │
│ type ('earn/redeem/adjust/expire/refund')                 │
│ order_id (FK → orders, SET NULL)                          │
│ balance_after                                             │
│ notes                                                     │
│ created_by_user_id (FK → users, SET NULL)                 │
│ created_at                                                │
└──────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────┐
│     shift_reports (IMMUTABLE)                             │
├──────────────────────────────────────────────────────────┤
│ id (PK)                                                   │
│ tenant_id (FK)                                            │
│ outlet_id (FK → outlets)                                  │
│ cashier_session_id (FK → cashier_sessions)                │
│ cashier_user_id (FK → users)                              │
│ total_transactions                                        │
│ total_sales                                               │
│ total_cash                                                │
│ total_non_cash                                            │
│ total_refunds                                             │
│ total_discounts                                           │
│ total_tax                                                 │
│ total_service_charge                                      │
│ opening_cash                                              │
│ closing_cash                                              │
│ expected_cash                                             │
│ cash_variance                                             │
│ notes                                                     │
│ generated_at                                              │
│ created_at                                                │
│ UQ(cashier_session_id)                                    │
└──────────────────────────────────────────────────────────┘
```

### 3.9 ERD — Settings (4 Tabel)

```
┌──────────────────────────────────┐       ┌──────────────────────────────────┐
│      receipt_settings            │       │       tax_settings               │
├──────────────────────────────────┤       ├──────────────────────────────────┤
│ id (PK)                          │       │ id (PK)                          │
│ tenant_id (FK)                   │       │ tenant_id (FK)                   │
│ outlet_id (FK, SET NULL)         │       │ outlet_id (FK, SET NULL)         │
│ header_text                      │       │ is_enabled                       │
│ footer_text                      │       │ calculation_method               │
│ notes_text                       │       │ tax_per_transaction              │
│ show_tax_percentage              │       │ tax_per_product                  │
│ max_print_count                  │       │ default_tax_rate                 │
│ queue_number_enabled             │       │ order_type_checklist (JSON)      │
│ queue_prefix                     │       │ created_at, updated_at           │
│ logo_display                     │       └──────────────────────────────────┘
│ display_options (JSON)           │
│ created_at, updated_at           │       ┌──────────────────────────────────┐
└──────────────────────────────────┘       │   service_charge_settings        │
                                           ├──────────────────────────────────┤
┌──────────────────────────────────┐       │ id (PK)                          │
│      rounding_settings           │       │ tenant_id (FK)                   │
├──────────────────────────────────┤       │ outlet_id (FK, SET NULL)         │
│ id (PK)                          │       │ is_enabled                       │
│ tenant_id (FK)                   │       │ percentage                       │
│ outlet_id (FK, SET NULL)         │       │ is_taxable                       │
│ cash_rounding_enabled            │       │ order_type_checklist (JSON)      │
│ cash_rounding_step               │       │ created_at, updated_at           │
│ non_cash_rounding_enabled        │       └──────────────────────────────────┘
│ non_cash_rounding_step           │
│ created_at, updated_at           │
└──────────────────────────────────┘
```

### 3.10 ERD — Langganan (3 Tabel)

```
┌──────────────────────────────────────────────────────────┐
│                    subscriptions                          │
├──────────────────────────────────────────────────────────┤
│ id (PK)                                                   │
│ tenant_id (FK)                                            │
│ plan_id (FK → plans)                                      │
│ status ('trial/pending_payment/active/grace/expired/      │
│         archived')                                        │
│ billing_cycle ('monthly/yearly')                          │
│ started_at                                                │
│ ends_at                                                   │
│ trial_ends_at                                             │
│ grace_ends_at                                             │
│ expired_at                                                │
│ grace_type ('grace/expired')                              │
│ pending_plan_id (FK → plans, SET NULL)                    │
│ cancelled_at                                              │
│ auto_renew                                                │
│ created_at, updated_at                                    │
└──────────────────────────────────────────────────────────┘

┌──────────────────────────────────┐       ┌──────────────────────────────────┐
│       tenant_add_ons             │       │     plan_change_requests         │
├──────────────────────────────────┤       ├──────────────────────────────────┤
│ id (PK)                          │       │ id (PK)                          │
│ tenant_id (FK)                   │       │ tenant_id (FK)                   │
│ add_on_id (FK → add_ons)         │       │ from_plan_id (FK → plans)        │
│ quantity                         │       │ to_plan_id (FK → plans)          │
│ unit_price                       │       │ change_type ('upgrade/downgrade')│
│ period_start, period_end         │       │ requested_by_user_id (FK)        │
│ is_active                        │       │ status                           │
│ created_at, updated_at           │       │ prorated_amount                  │
└──────────────────────────────────┘       │ effective_at                     │
                                           │ created_at, updated_at           │
                                           └──────────────────────────────────┘
```

### 3.11 ERD — Cross-Cutting & Compliance (6 Tabel)

```
┌──────────────────────────────────────────────────────────┐
│     audit_logs (IMMUTABLE)                                │
├──────────────────────────────────────────────────────────┤
│ id (PK)                                                   │
│ actor_type ('user/admin/system')                          │
│ actor_id                                                  │
│ tenant_id (FK, SET NULL)                                  │
│ action                                                    │
│ entity_type, entity_id                                    │
│ metadata (JSON)                                           │
│ ip_address                                                │
│ created_at                                                │
└──────────────────────────────────────────────────────────┘

┌──────────────────────────────────┐       ┌──────────────────────────────────┐
│       notifications              │       │     cookie_consents              │
├──────────────────────────────────┤       │     (INSERT only)                │
│ id (PK)                          │       ├──────────────────────────────────┤
│ tenant_id (FK, SET NULL)         │       │ id (PK)                          │
│ user_id (FK, SET NULL)           │       │ tenant_id (FK, SET NULL)         │
│ admin_user_id (FK, SET NULL)     │       │ user_id (FK, SET NULL)           │
│ type                             │       │ session_id                       │
│ title                            │       │ necessary                        │
│ body                             │       │ analytics                        │
│ data (JSON)                      │       │ marketing                        │
│ read_at                          │       │ consent_version                  │
│ created_at                       │       │ ip_address                       │
└──────────────────────────────────┘       │ user_agent                       │
                                           │ created_at                       │
┌──────────────────────────────────┐       └──────────────────────────────────┘
│        export_jobs               │
├──────────────────────────────────┤       ┌──────────────────────────────────┐
│ id (PK, CHAR(36) UUID)           │       │     account_deletions            │
│ tenant_id (FK)                   │       ├──────────────────────────────────┤
│ requested_by_user_id (FK)        │       │ id (PK)                          │
│ type                             │       │ tenant_id (FK)                   │
│ format                           │       │ requested_by_user_id (FK)        │
│ period_from, period_to           │       │ reason                           │
│ status                           │       │ status                           │
│ file_path                        │       │ grace_period_ends_at             │
│ file_size_bytes                  │       │ archived_at                      │
│ row_count                        │       │ cancelled_at                     │
│ storage_driver                   │       │ cancelled_by_user_id (FK)        │
│ signed_url_expires_at            │       │ notes                            │
│ checksum                         │       │ created_at, updated_at           │
│ error_message                    │       └──────────────────────────────────┘
│ expires_at                       │
│ completed_at                     │       ┌──────────────────────────────────┐
│ created_at, updated_at           │       │      retention_jobs              │
└──────────────────────────────────┘       ├──────────────────────────────────┤
                                           │ id (PK)                          │
                                           │ job_name (UQ)                    │
                                           │ table_name                       │
                                           │ retention_days                   │
                                           │ schedule                         │
                                           │ last_run_at                      │
                                           │ last_run_status                  │
                                           │ last_run_affected                │
                                           │ is_active                        │
                                           │ notes                            │
                                           │ created_at, updated_at           │
                                           └──────────────────────────────────┘
```

### 3.12 ERD — Support & Analytics (3 Tabel)

```
┌──────────────────────────────────┐
│      support_tickets             │
├──────────────────────────────────┤
│ id (PK)                          │
│ tenant_id (FK)                   │
│ user_id (FK, SET NULL)           │
│ ticket_number (UQ)               │
│ subject                          │
│ description                      │
│ category                         │
│ priority                         │
│ status                           │
│ assigned_to_admin_user_id (FK)   │
│ attachment_path                  │
│ first_response_at                │
│ resolved_at                      │
│ closed_at                        │
│ created_at, updated_at           │
└──────────┬───────────────────────┘
           │ 1:N
           ▼
┌──────────────────────────────────┐
│   support_ticket_messages        │
├──────────────────────────────────┤
│ id (PK)                          │
│ ticket_id (FK)                   │
│ tenant_id (FK)                   │
│ admin_user_id (FK, SET NULL)     │
│ user_id (FK, SET NULL)           │
│ body                             │
│ attachment_path                  │
│ is_internal                      │
│ created_at                       │
└──────────────────────────────────┘

┌──────────────────────────────────────────────────────────┐
│     analytics_events                                      │
├──────────────────────────────────────────────────────────┤
│ id (PK, CHAR(36) UUID)                                    │
│ event_name                                                │
│ tenant_id (FK, SET NULL)                                  │
│ user_id (FK, SET NULL)                                    │
│ admin_user_id (FK, SET NULL)                              │
│ environment                                               │
│ properties (JSON)                                         │
│ session_id                                                │
│ ip_address                                                │
│ created_at                                                │
└──────────────────────────────────────────────────────────┘
```

### 3.13 ERD — Production Readiness [PROD] (6 Tabel)

```
┌──────────────────────────────────────────────────────────┐
│                    releases [PROD]                        │
├──────────────────────────────────────────────────────────┤
│ id (PK, CHAR(36) UUID)                                    │
│ version (UQ)                                              │
│ name                                                      │
│ description                                               │
│ status ('draft/gate_evaluation/ready/cutover/released/    │
│         rolled_back/cancelled')                           │
│ target_environment                                        │
│ planned_at                                                │
│ deployed_at                                               │
│ released_by_admin_user_id (FK, SET NULL)                  │
│ git_tag                                                   │
│ changelog_url                                             │
│ created_at, updated_at                                    │
└──────────┬───────────────────────────────────────────────┘
           │ 1:N
           ├──────────────────────────────────────────────────────────┐
           │                                                          │
           ▼                                                          ▼
┌──────────────────────────────────────┐       ┌──────────────────────────────────────┐
│    release_gates [PROD] (IMMUTABLE)  │       │     deployments [PROD]               │
├──────────────────────────────────────┤       ├──────────────────────────────────────┤
│ id (PK)                               │       │ id (PK, CHAR(36) UUID)               │
│ release_id (FK)                       │       │ release_id (FK, SET NULL)            │
│ gate_code ('RG-1..RG-10')             │       │ environment                          │
│ gate_name                             │       │ version                              │
│ criteria                              │       │ commit_hash                          │
│ status ('pending/in_progress/pass/    │       │ docker_image                         │
│         fail/forced')                 │       │ status ('pending/in_progress/        │
│ evidence_url                          │       │         success/failed/rolled_back) │
│ evidence_notes                        │       │ deployed_by_admin_user_id (FK)       │
│ reviewer_admin_user_id (FK)           │       │ triggered_by                         │
│ reviewed_at                           │       │ started_at, completed_at             │
│ is_forced                             │       │ duration_seconds                     │
│ forced_by_admin_user_id (FK)          │       │ health_check_status                  │
│ forced_reason                         │       │ health_check_at                      │
│ forced_at                             │       │ error_message                        │
│ created_at, updated_at                │       │ notes                                │
│ UQ(release_id, gate_code)             │       │ created_at, updated_at               │
└──────────────────────────────────────┘       └──────────────────────────────────────┘

┌──────────────────────────────────────┐       ┌──────────────────────────────────────┐
│   cutover_runs [PROD] (IMMUTABLE)    │       │  rollback_events [PROD] (IMMUTABLE)  │
├──────────────────────────────────────┤       ├──────────────────────────────────────┤
│ id (PK, CHAR(36) UUID)               │       │ id (PK, CHAR(36) UUID)               │
│ release_id (FK)                      │       │ deployment_id (FK)                   │
│ environment                          │       │ release_id (FK, SET NULL)            │
│ status ('not_started/in_progress/    │       │ cutover_run_id (FK, SET NULL)        │
│         completed/aborted/           │       │ target_version                       │
│         rolled_back')                │       │ target_deployment_id (FK, SET NULL)  │
│ started_by_admin_user_id (FK)        │       │ reason                               │
│ started_at, completed_at             │       │ trigger_type                         │
│ aborted_at                           │       │ status ('pending/in_progress/        │
│ abort_reason                         │       │         completed/failed)           │
│ duration_seconds                     │       │ requested_by_admin_user_id (FK)      │
│ cutover_lock_key                     │       │ approved_by_admin_user_id (FK)       │
│ notes                                │       │ re_auth_at                           │
│ created_at, updated_at               │       │ started_at, completed_at             │
└──────────┬───────────────────────────┘       │ duration_seconds                     │
           │ 1:N                               │ health_check_status                  │
           ▼                                   │ error_message                        │
┌──────────────────────────────────────┐       │ audit_log_id (FK, SET NULL)          │
│ cutover_checklist_items [PROD]       │       │ post_mortem_url                      │
│ (IMMUTABLE)                          │       │ created_at, updated_at               │
├──────────────────────────────────────┤       └──────────────────────────────────────┘
│ id (PK)                              │
│ cutover_run_id (FK)                  │
│ item_order (1..10)                   │
│ item_code                            │
│ title                                │
│ description                          │
│ pic_role                             │
│ is_completed                         │
│ completed_by_admin_user_id (FK)      │
│ completed_at                         │
│ notes                                │
│ evidence_url                         │
│ created_at, updated_at               │
│ UQ(cutover_run_id, item_order)       │
│ UQ(cutover_run_id, item_code)        │
└──────────────────────────────────────┘
```


### 3.14 ERD — Promosi (4 Tabel) 🆕

```
┌──────────────────────────────┐
│        vouchers              │
├──────────────────────────────┤
│ id (PK)                      │
│ tenant_id (FK)               │
│ code (UQ per tenant)         │
│ name                         │
│ discount_type                │
│   ('percent/fixed')          │
│ discount_value               │
│ min_purchase                 │
│ max_usage_total              │
│ max_usage_per_customer       │
│ usage_count                  │
│ starts_at                    │
│ ends_at                      │
│ is_stackable                 │
│ is_active                    │
│ created_by_user_id (FK)      │
│ created_at, updated_at       │
│ UQ(tenant_id, code)          │
└──────────┬───────────────────┘
           │ 1:N
           ▼
┌──────────────────────────────┐
│   voucher_redemptions        │
│   (IMMUTABLE)                │
├──────────────────────────────┤
│ id (PK)                      │
│ tenant_id (FK)               │
│ voucher_id (FK)              │
│ order_id (FK)                │
│ customer_id (FK, SET NULL)   │
│ discount_applied             │
│ redeemed_at                  │
│ created_at                   │
└──────────────────────────────┘

┌──────────────────────────────┐
│   scheduled_discounts        │
├──────────────────────────────┤
│ id (PK)                      │
│ tenant_id (FK)               │
│ name                         │
│ discount_type                │
│   ('percent/fixed')          │
│ discount_value               │
│ scope_type                   │
│   ('all/category/product')   │
│ scope_ids (JSON)             │
│ starts_at                    │
│ ends_at                      │
│ time_start (TIME)            │
│ time_end (TIME)              │
│ days_of_week (JSON)          │
│ is_active                    │
│ created_by_user_id (FK)      │
│ created_at, updated_at       │
└──────────────────────────────┘
```

### 3.15 ERD — Kas Keluar/Masuk & Pengaturan Kasir (2 Tabel) 🆕

```
┌──────────────────────────────────────────────────────────┐
│     cash_movements (IMMUTABLE)                            │
├──────────────────────────────────────────────────────────┤
│ id (PK)                                                   │
│ tenant_id (FK)                                            │
│ outlet_id (FK → outlets)                                  │
│ cashier_session_id (FK → cashier_sessions)                │
│ movement_type ('cash_in/cash_out')                        │
│ amount (DECIMAL, selalu positif)                          │
│ reason (TEXT, wajib min 10 karakter)                      │
│ created_by_user_id (FK → users)                           │
│ created_at                                                │
└──────────────────────────────────────────────────────────┘

┌──────────────────────────────┐
│     cashier_settings         │
├──────────────────────────────┤
│ id (PK)                      │
│ tenant_id (FK)               │
│ outlet_id (FK, SET NULL)     │
│ default_opening_cash         │
│ max_discount_percent         │
│ can_void (BOOLEAN)           │
│ can_refund (BOOLEAN)         │
│ can_discount (BOOLEAN)       │
│ auto_close_shift_hours       │
│ cash_movement_enabled        │
│ created_at, updated_at       │
│ UQ(tenant_id, outlet_id)     │
└──────────────────────────────┘
```

### 3.16 ERD — Refund Parsial & E-Wallet & Notifikasi (3 Tabel) 🆕

```
┌──────────────────────────────┐
│       refund_items           │
├──────────────────────────────┤
│ id (PK)                      │
│ refund_id (FK → refunds)     │
│ order_item_id (FK)           │
│ tenant_id (FK)               │
│ quantity                     │
│ unit_price                   │
│ discount_amount              │
│ tax_amount                   │
│ refund_amount                │
│ created_at                   │
│ UQ(refund_id, order_item_id) │
└──────────────────────────────┘

┌──────────────────────────────┐
│     ewallet_settings         │
├──────────────────────────────┤
│ id (PK)                      │
│ tenant_id (FK)               │
│ qris_enabled (BOOLEAN)       │
│ qris_qr_url (VARCHAR)        │
│ qris_merchant_name           │
│ created_at, updated_at       │
│ UQ(tenant_id)                │
└──────────────────────────────┘

┌──────────────────────────────────┐
│   notification_preferences       │
├──────────────────────────────────┤
│ id (PK)                          │
│ tenant_id (FK)                   │
│ user_id (FK)                     │
│ notify_product_changes           │
│ notify_employee_changes          │
│ notify_settings_changes          │
│ notify_subscription_changes      │
│ notify_low_stock                 │
│ notify_credit_due                │
│ created_at, updated_at           │
│ UQ(user_id)                      │
└──────────────────────────────────┘
```

---

## §4. DDL Lengkap (80 Tabel)

### BAGIAN 1 — SHARED (4 Tabel)

```sql
-- 1/80: plans
CREATE TABLE plans (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code            VARCHAR(30) NOT NULL UNIQUE,
    name            VARCHAR(100) NOT NULL,
    monthly_price   DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    yearly_price    DECIMAL(15,2) NULL,
    max_pos         INT NOT NULL DEFAULT 1,
    max_employees   INT NOT NULL DEFAULT 1,
    max_admins      INT NOT NULL DEFAULT 1,
    trial_days      INT NOT NULL DEFAULT 14,
    is_trial_plan   BOOLEAN NOT NULL DEFAULT FALSE,
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    sort_order      INT NOT NULL DEFAULT 0,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active_sort (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2/80: features (21 fitur gated)
CREATE TABLE features (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code            VARCHAR(50) NOT NULL UNIQUE,
    name            VARCHAR(100) NOT NULL,
    description     TEXT NULL,
    category        VARCHAR(30) NULL,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3/80: plan_features
CREATE TABLE plan_features (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_id         BIGINT UNSIGNED NOT NULL,
    feature_id      BIGINT UNSIGNED NOT NULL,
    is_enabled      BOOLEAN NOT NULL DEFAULT TRUE,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_plan_feature (plan_id, feature_id),
    FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE CASCADE,
    FOREIGN KEY (feature_id) REFERENCES features(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4/80: add_ons
CREATE TABLE add_ons (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code            VARCHAR(50) NOT NULL UNIQUE,
    name            VARCHAR(100) NOT NULL,
    unit_price      DECIMAL(15,2) NOT NULL,
    unit            VARCHAR(30) NOT NULL,
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### BAGIAN 2 — ADMIN/INTERNAL AUTH (3 Tabel)

```sql
-- 5/80: admin_users
CREATE TABLE admin_users (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name                VARCHAR(100) NOT NULL,
    email               VARCHAR(255) NOT NULL UNIQUE,
    password_hash       VARCHAR(255) NOT NULL,
    role                VARCHAR(30) NOT NULL COMMENT 'super_admin/support/finance/ops/partnership',
    mfa_secret          VARCHAR(255) NULL,
    mfa_recovery_codes  TEXT NULL COMMENT 'encrypted JSON array 10 single-use codes',
    mfa_enabled_at      TIMESTAMP NULL,
    is_active           BOOLEAN NOT NULL DEFAULT TRUE,
    last_login_at       TIMESTAMP NULL,
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at          TIMESTAMP NULL,
    INDEX idx_role_active (role, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6/80: admin_sessions
CREATE TABLE admin_sessions (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id          VARCHAR(40) NOT NULL UNIQUE,
    admin_user_id       BIGINT UNSIGNED NOT NULL,
    ip_address          VARCHAR(45) NULL,
    user_agent          VARCHAR(255) NULL,
    mfa_verified_at     TIMESTAMP NULL,
    reauth_at           TIMESTAMP NULL COMMENT 'untuk aksi kritikal PROD',
    last_activity_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    revoked_at          TIMESTAMP NULL,
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admin (admin_user_id),
    INDEX idx_revoked (revoked_at),
    FOREIGN KEY (admin_user_id) REFERENCES admin_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7/80: partners
CREATE TABLE partners (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name                VARCHAR(150) NOT NULL,
    code                VARCHAR(50) NOT NULL UNIQUE,
    email               VARCHAR(255) NULL,
    phone               VARCHAR(20) NULL,
    commission_rate     DECIMAL(5,2) NULL,
    is_active           BOOLEAN NOT NULL DEFAULT TRUE,
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### BAGIAN 3 — TENANT CORE & AKSES (10 Tabel)

```sql
-- 8/80: tenants (owner_user_id FK via ALTER setelah users)
CREATE TABLE tenants (
    id                              CHAR(36) PRIMARY KEY,
    name                            VARCHAR(150) NOT NULL,
    slug                            VARCHAR(100) NOT NULL UNIQUE,
    owner_user_id                   BIGINT UNSIGNED NULL,
    industry                        VARCHAR(100) NULL,
    business_type                   VARCHAR(50) NULL,
    phone                           VARCHAR(20) NULL,
    email                           VARCHAR(255) NULL,
    address                         TEXT NULL,
    province                        VARCHAR(100) NULL,
    city                            VARCHAR(100) NULL,
    district                        VARCHAR(100) NULL,
    postal_code                     VARCHAR(10) NULL,
    logo_url                        VARCHAR(500) NULL,
    social_media                    JSON NULL,
    status                          VARCHAR(20) NOT NULL DEFAULT 'trial'
                                    COMMENT 'trial/pending_payment/active/grace/expired/archived/suspended',
    trial_ends_at                   TIMESTAMP NULL,
    read_only_at                    TIMESTAMP NULL,
    expired_banner_dismissed_at     TIMESTAMP NULL,
    suspended_reason                TEXT NULL,
    setup_step                      TINYINT NOT NULL DEFAULT 0 COMMENT 'Setup Awal Owner 0-10',
    setup_completed_at              TIMESTAMP NULL,
    setup_dismissed_at              TIMESTAMP NULL,
    created_at                      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at                      TIMESTAMP NULL,
    INDEX idx_status (status),
    INDEX idx_trial_ends (trial_ends_at),
    INDEX idx_setup (setup_step, setup_completed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9/80: outlets
CREATE TABLE outlets (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id       CHAR(36) NOT NULL,
    name            VARCHAR(150) NOT NULL,
    code            VARCHAR(50) NOT NULL,
    address         TEXT NULL,
    phone           VARCHAR(20) NULL,
    timezone        VARCHAR(50) NOT NULL DEFAULT 'Asia/Jakarta',
    tax_inclusive   BOOLEAN NOT NULL DEFAULT FALSE,
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      TIMESTAMP NULL,
    UNIQUE KEY uk_tenant_code (tenant_id, code),
    INDEX idx_tenant (tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10/80: users
CREATE TABLE users (
    id                          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id                   CHAR(36) NOT NULL,
    name                        VARCHAR(150) NOT NULL,
    email                       VARCHAR(255) NOT NULL,
    email_verified_at           TIMESTAMP NULL,
    password_hash               VARCHAR(255) NOT NULL,
    phone                       VARCHAR(20) NULL,
    role                        VARCHAR(30) NOT NULL DEFAULT 'cashier' COMMENT 'owner/admin/cashier',
    pin_hash                    VARCHAR(255) NULL COMMENT 'PIN 6 digit kasir POS (Argon2id)',
    pin_failed_attempts         TINYINT NOT NULL DEFAULT 0 COMMENT 'untuk lockout PIN',
    pin_locked_until            TIMESTAMP NULL COMMENT 'lockout 15 menit setelah 5 gagal',
    avatar_url                  VARCHAR(500) NULL,
    is_active                   BOOLEAN NOT NULL DEFAULT TRUE,
    onboarding_step             TINYINT NOT NULL DEFAULT 0,
    onboarding_completed_at     TIMESTAMP NULL,
    onboarding_skipped_survey   BOOLEAN NOT NULL DEFAULT FALSE,
    last_login_at               TIMESTAMP NULL,
    created_at                  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at                  TIMESTAMP NULL,
    UNIQUE KEY uk_tenant_email (tenant_id, email),
    INDEX idx_tenant_role (tenant_id, role),
    INDEX idx_tenant_active (tenant_id, is_active),
    INDEX idx_onboarding (onboarding_completed_at),
    INDEX idx_pin_locked (pin_locked_until),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ⚠ ALTER: tenants.owner_user_id FK (circular fix #1)
ALTER TABLE tenants
ADD CONSTRAINT fk_tenants_owner
FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE SET NULL;

-- 11/80: user_outlets
CREATE TABLE user_outlets (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id       CHAR(36) NOT NULL,
    user_id         BIGINT UNSIGNED NOT NULL,
    outlet_id       BIGINT UNSIGNED NOT NULL,
    role_in_outlet  VARCHAR(30) NULL,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_user_outlet (user_id, outlet_id),
    INDEX idx_outlet (outlet_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (outlet_id) REFERENCES outlets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12/80: user_sessions
CREATE TABLE user_sessions (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id          VARCHAR(40) NOT NULL UNIQUE,
    tenant_id           CHAR(36) NOT NULL,
    user_id             BIGINT UNSIGNED NOT NULL,
    ip_address          VARCHAR(45) NULL,
    user_agent          VARCHAR(255) NULL,
    is_remember_me      BOOLEAN NOT NULL DEFAULT FALSE,
    last_activity_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    revoked_at          TIMESTAMP NULL,
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_revoked (revoked_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13/80: password_resets
CREATE TABLE password_resets (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    token_hash      VARCHAR(255) NOT NULL,
    expires_at      TIMESTAMP NOT NULL,
    used_at         TIMESTAMP NULL,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_token (token_hash),
    INDEX idx_expires (expires_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14/80: otp_verifications
CREATE TABLE otp_verifications (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id       CHAR(36) NULL,
    target          VARCHAR(255) NOT NULL,
    channel         VARCHAR(20) NOT NULL COMMENT 'whatsapp/email',
    purpose         VARCHAR(30) NOT NULL COMMENT 'register/login/reset',
    code_hash       VARCHAR(255) NOT NULL,
    attempts        TINYINT NOT NULL DEFAULT 0,
    expires_at      TIMESTAMP NOT NULL,
    used_at         TIMESTAMP NULL,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_target_purpose (target, purpose),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15/80: email_verifications
CREATE TABLE email_verifications (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    email           VARCHAR(255) NOT NULL,
    token_hash      VARCHAR(255) NOT NULL,
    expires_at      TIMESTAMP NOT NULL,
    verified_at     TIMESTAMP NULL,
    resend_count    TINYINT NOT NULL DEFAULT 0,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_token (token_hash),
    INDEX idx_expires (expires_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16/80: tenant_invitations
CREATE TABLE tenant_invitations (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id           CHAR(36) NOT NULL,
    email               VARCHAR(255) NOT NULL,
    role                VARCHAR(30) NOT NULL,
    token_hash          VARCHAR(255) NOT NULL,
    invited_by_user_id  BIGINT UNSIGNED NOT NULL,
    expires_at          TIMESTAMP NOT NULL,
    accepted_at         TIMESTAMP NULL,
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_email (email),
    INDEX idx_expires (expires_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (invited_by_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17/80: pos_devices
CREATE TABLE pos_devices (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id               CHAR(36) NOT NULL,
    outlet_id               BIGINT UNSIGNED NOT NULL,
    user_id                 BIGINT UNSIGNED NULL,
    device_name             VARCHAR(100) NULL,
    device_id               VARCHAR(255) NOT NULL UNIQUE,
    platform                VARCHAR(20) NOT NULL DEFAULT 'web',
    activation_code         VARCHAR(20) NULL,
    activation_expires_at   TIMESTAMP NULL,
    device_session_token    VARCHAR(255) NULL COMMENT 'token persistent 30 hari untuk device-based login',
    device_session_expires  TIMESTAMP NULL,
    last_seen_at            TIMESTAMP NULL,
    revoked_at              TIMESTAMP NULL,
    created_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_revoked (revoked_at),
    INDEX idx_device_session (device_session_token, device_session_expires),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (outlet_id) REFERENCES outlets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### BAGIAN 4 — INTERNAL (5 Tabel)

```sql
-- 18/80: tenant_feature_overrides
CREATE TABLE tenant_feature_overrides (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id               CHAR(36) NOT NULL,
    feature_id              BIGINT UNSIGNED NOT NULL,
    is_enabled              BOOLEAN NOT NULL,
    reason                  TEXT NULL,
    set_by_admin_user_id    BIGINT UNSIGNED NOT NULL,
    created_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_tenant_feature (tenant_id, feature_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (feature_id) REFERENCES features(id),
    FOREIGN KEY (set_by_admin_user_id) REFERENCES admin_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19/80: usage_records
CREATE TABLE usage_records (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id           CHAR(36) NOT NULL,
    period              VARCHAR(7) NOT NULL COMMENT 'YYYY-MM',
    pos_count           INT NOT NULL DEFAULT 0,
    employee_count      INT NOT NULL DEFAULT 0,
    admin_count         INT NOT NULL DEFAULT 0,
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_tenant_period (tenant_id, period),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20/80: billing_invoices (subscription_id FK via ALTER setelah subscriptions)
CREATE TABLE billing_invoices (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id           CHAR(36) NOT NULL,
    subscription_id     BIGINT UNSIGNED NULL,
    invoice_number      VARCHAR(50) NOT NULL,
    amount              DECIMAL(15,2) NOT NULL,
    tax_amount          DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total               DECIMAL(15,2) NOT NULL,
    status              VARCHAR(20) NOT NULL DEFAULT 'unpaid' COMMENT 'unpaid/paid/refunded',
    due_date            DATE NULL,
    paid_at             TIMESTAMP NULL,
    notes               TEXT NULL,
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_tenant_invoice (tenant_id, invoice_number),
    INDEX idx_tenant_status (tenant_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 21/80: partner_referrals
CREATE TABLE partner_referrals (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    partner_id      BIGINT UNSIGNED NOT NULL,
    tenant_id       CHAR(36) NOT NULL,
    status          VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_tenant (tenant_id),
    INDEX idx_partner (partner_id),
    FOREIGN KEY (partner_id) REFERENCES partners(id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 22/80: support_access_sessions
CREATE TABLE support_access_sessions (
    id                      CHAR(36) PRIMARY KEY,
    admin_user_id           BIGINT UNSIGNED NOT NULL,
    tenant_id               CHAR(36) NOT NULL,
    granted_by_user_id      BIGINT UNSIGNED NULL,
    granted_at              TIMESTAMP NULL,
    reason                  TEXT NOT NULL,
    mode                    VARCHAR(20) NOT NULL DEFAULT 'read' COMMENT 'read/write',
    started_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at              TIMESTAMP NOT NULL,
    ended_at                TIMESTAMP NULL,
    approved_at             TIMESTAMP NULL,
    created_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_admin (admin_user_id),
    INDEX idx_expires (expires_at),
    FOREIGN KEY (admin_user_id) REFERENCES admin_users(id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (granted_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### BAGIAN 5 — SETTINGS AWAL & PRODUK (6 Tabel)

```sql
-- 23/80: categories
CREATE TABLE categories (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id       CHAR(36) NOT NULL,
    name            VARCHAR(100) NOT NULL,
    sort_order      INT NOT NULL DEFAULT 0,
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 24/80: tax_rates
CREATE TABLE tax_rates (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id       CHAR(36) NOT NULL,
    name            VARCHAR(100) NOT NULL COMMENT 'PPN/PB1/Service Tax',
    code            VARCHAR(20) NULL,
    rate            DECIMAL(5,2) NOT NULL,
    is_default      BOOLEAN NOT NULL DEFAULT FALSE,
    is_inclusive    BOOLEAN NOT NULL DEFAULT FALSE,
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      TIMESTAMP NULL,
    UNIQUE KEY uk_tenant_name (tenant_id, name),
    INDEX idx_tenant_active (tenant_id, is_active),
    INDEX idx_tenant_default (tenant_id, is_default),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 25/80: products
CREATE TABLE products (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id           CHAR(36) NOT NULL,
    category_id         BIGINT UNSIGNED NULL,
    sku                 VARCHAR(100) NULL,
    barcode             VARCHAR(100) NULL,
    name                VARCHAR(150) NOT NULL,
    description         TEXT NULL,
    price               DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    cost_price          DECIMAL(15,2) NULL,
    wholesale_price     DECIMAL(15,2) NULL,
    wholesale_min_qty   INT NULL,
    image_url           VARCHAR(500) NULL,
    unit                VARCHAR(30) NULL,
    track_stock         BOOLEAN NOT NULL DEFAULT FALSE,
    stock_qty           INT NOT NULL DEFAULT 0,
    low_stock_alert     INT NULL,
    tax_rate_id         BIGINT UNSIGNED NULL,
    tax_inclusive       BOOLEAN NOT NULL DEFAULT FALSE,
    is_bundle           BOOLEAN NOT NULL DEFAULT FALSE,
    is_active           BOOLEAN NOT NULL DEFAULT TRUE,
    metadata            JSON NULL,
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at          TIMESTAMP NULL,
    CHECK (stock_qty >= 0),
    INDEX idx_tenant (tenant_id),
    INDEX idx_tenant_category (tenant_id, category_id),
    INDEX idx_tenant_sku (tenant_id, sku),
    INDEX idx_tenant_barcode (tenant_id, barcode),
    INDEX idx_low_stock (tenant_id, track_stock, stock_qty, low_stock_alert),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (tax_rate_id) REFERENCES tax_rates(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 26/80: product_variants
CREATE TABLE product_variants (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id           CHAR(36) NOT NULL,
    product_id          BIGINT UNSIGNED NOT NULL,
    name                VARCHAR(100) NOT NULL,
    sku                 VARCHAR(100) NULL,
    barcode             VARCHAR(100) NULL,
    price_adjustment    DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    cost_price          DECIMAL(15,2) NULL,
    stock_qty           INT NOT NULL DEFAULT 0,
    low_stock_alert     INT NULL,
    is_active           BOOLEAN NOT NULL DEFAULT TRUE,
    sort_order          INT NOT NULL DEFAULT 0,
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CHECK (stock_qty >= 0),
    UNIQUE KEY uk_product_name (product_id, name),
    INDEX idx_tenant (tenant_id),
    INDEX idx_product (product_id),
    INDEX idx_barcode (barcode),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 27/80: product_taxes
CREATE TABLE product_taxes (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id       CHAR(36) NOT NULL,
    product_id      BIGINT UNSIGNED NOT NULL,
    tax_rate_id     BIGINT UNSIGNED NULL,
    tax_name        VARCHAR(100) NOT NULL,
    tax_rate        DECIMAL(5,2) NOT NULL,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_product_tax (product_id, tax_name),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (tax_rate_id) REFERENCES tax_rates(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 28/80: modifiers
CREATE TABLE modifiers (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id       CHAR(36) NOT NULL,
    name            VARCHAR(100) NOT NULL,
    description     TEXT NULL,
    is_required     BOOLEAN NOT NULL DEFAULT FALSE,
    max_selection   TINYINT NULL,
    min_selection   TINYINT NOT NULL DEFAULT 0,
    sort_order      INT NOT NULL DEFAULT 0,
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_tenant_active (tenant_id, is_active),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### BAGIAN 6 — MODIFIER, INVENTORI, MEJA (10 Tabel)

```sql
-- 29/80: modifier_options
CREATE TABLE modifier_options (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    modifier_id         BIGINT UNSIGNED NOT NULL,
    tenant_id           CHAR(36) NOT NULL,
    name                VARCHAR(100) NOT NULL,
    price_adjustment    DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    sort_order          INT NOT NULL DEFAULT 0,
    is_active           BOOLEAN NOT NULL DEFAULT TRUE,
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_modifier_name (modifier_id, name),
    INDEX idx_modifier (modifier_id),
    INDEX idx_tenant (tenant_id),
    FOREIGN KEY (modifier_id) REFERENCES modifiers(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 30/80: product_modifiers
CREATE TABLE product_modifiers (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id       CHAR(36) NOT NULL,
    product_id      BIGINT UNSIGNED NOT NULL,
    modifier_id     BIGINT UNSIGNED NOT NULL,
    sort_order      INT NOT NULL DEFAULT 0,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_product_modifier (product_id, modifier_id),
    INDEX idx_product (product_id),
    INDEX idx_modifier (modifier_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (modifier_id) REFERENCES modifiers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 31/80: suppliers
CREATE TABLE suppliers (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id       CHAR(36) NOT NULL,
    name            VARCHAR(150) NOT NULL,
    contact_name    VARCHAR(100) NULL,
    phone           VARCHAR(20) NULL,
    email           VARCHAR(255) NULL,
    address         TEXT NULL,
    notes           TEXT NULL,
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      TIMESTAMP NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_tenant_active (tenant_id, is_active),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 32/80: purchase_orders
CREATE TABLE purchase_orders (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id               CHAR(36) NOT NULL,
    outlet_id               BIGINT UNSIGNED NOT NULL,
    supplier_id             BIGINT UNSIGNED NULL,
    po_number               VARCHAR(50) NOT NULL,
    status                  VARCHAR(20) NOT NULL DEFAULT 'draft'
                            COMMENT 'draft/sent/partial/received/cancelled',
    order_date              DATE NOT NULL,
    expected_date           DATE NULL,
    received_at             TIMESTAMP NULL,
    subtotal                DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    tax_total               DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    shipping_cost           DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total                   DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    notes                   TEXT NULL,
    created_by_user_id      BIGINT UNSIGNED NOT NULL,
    approved_by_user_id     BIGINT UNSIGNED NULL,
    created_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_tenant_po (tenant_id, po_number),
    INDEX idx_tenant_status (tenant_id, status),
    INDEX idx_outlet (outlet_id),
    INDEX idx_supplier (supplier_id),
    INDEX idx_order_date (order_date),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (outlet_id) REFERENCES outlets(id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id),
    FOREIGN KEY (approved_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 33/80: purchase_order_items
CREATE TABLE purchase_order_items (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    purchase_order_id   BIGINT UNSIGNED NOT NULL,
    tenant_id           CHAR(36) NOT NULL,
    product_id          BIGINT UNSIGNED NULL,
    variant_id          BIGINT UNSIGNED NULL,
    product_name        VARCHAR(150) NOT NULL COMMENT 'snapshot',
    sku                 VARCHAR(100) NULL,
    quantity            INT NOT NULL,
    received_qty        INT NOT NULL DEFAULT 0,
    unit_cost           DECIMAL(15,2) NOT NULL,
    discount_amount     DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    tax_amount          DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total               DECIMAL(15,2) NOT NULL,
    notes               TEXT NULL,
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_po (purchase_order_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_product (product_id),
    FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 34/80: stock_movements (IMMUTABLE)
CREATE TABLE stock_movements (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id           CHAR(36) NOT NULL,
    outlet_id           BIGINT UNSIGNED NOT NULL,
    product_id          BIGINT UNSIGNED NULL,
    variant_id          BIGINT UNSIGNED NULL,
    movement_type       VARCHAR(30) NOT NULL
                        COMMENT 'sale/purchase/transfer_in/transfer_out/adjustment/return/waste',
    quantity            INT NOT NULL COMMENT 'positif = masuk, negatif = keluar',
    unit_cost           DECIMAL(15,2) NULL,
    balance_after       INT NULL,
    reference_type      VARCHAR(30) NULL,
    reference_id        VARCHAR(36) NULL,
    notes               TEXT NULL COMMENT 'wajib untuk adjustment/waste',
    created_by_user_id  BIGINT UNSIGNED NULL,
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant_product (tenant_id, product_id, created_at),
    INDEX idx_tenant_outlet (tenant_id, outlet_id),
    INDEX idx_movement_type (movement_type),
    INDEX idx_reference (reference_type, reference_id),
    INDEX idx_created (created_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (outlet_id) REFERENCES outlets(id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 35/80: tables
CREATE TABLE tables (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id           CHAR(36) NOT NULL,
    outlet_id           BIGINT UNSIGNED NOT NULL,
    parent_table_id     BIGINT UNSIGNED NULL COMMENT 'NULL = standalone',
    name                VARCHAR(50) NOT NULL,
    capacity            TINYINT NOT NULL DEFAULT 4,
    area                VARCHAR(50) NULL,
    qr_code             VARCHAR(255) NULL,
    status              VARCHAR(20) NOT NULL DEFAULT 'available',
    is_active           BOOLEAN NOT NULL DEFAULT TRUE,
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_outlet_name (outlet_id, name),
    INDEX idx_tenant (tenant_id),
    INDEX idx_outlet (outlet_id),
    INDEX idx_parent (parent_table_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (outlet_id) REFERENCES outlets(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_table_id) REFERENCES tables(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 36/80: table_sessions (tanpa order_id — orders.table_session_id sudah cukup)
CREATE TABLE table_sessions (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id               CHAR(36) NOT NULL,
    table_id                BIGINT UNSIGNED NOT NULL,
    opened_by_user_id       BIGINT UNSIGNED NOT NULL,
    customer_name           VARCHAR(150) NULL,
    pax                     TINYINT NULL,
    opened_at               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    closed_at               TIMESTAMP NULL,
    status                  VARCHAR(20) NOT NULL DEFAULT 'open',
    created_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_table (table_id),
    INDEX idx_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE CASCADE,
    FOREIGN KEY (opened_by_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 37/80: payment_methods
CREATE TABLE payment_methods (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id               CHAR(36) NOT NULL,
    name                    VARCHAR(100) NOT NULL,
    type                    VARCHAR(30) NOT NULL COMMENT 'cash/transfer/ewallet/qris_static/custom',
    provider                VARCHAR(100) NULL,
    settlement_account      VARCHAR(150) NULL,
    mdr_percent             DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    mdr_fixed               DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    is_active               BOOLEAN NOT NULL DEFAULT TRUE,
    created_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_tenant_name (tenant_id, name),
    INDEX idx_tenant (tenant_id),
    INDEX idx_type (type),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 38/80: cashier_sessions
CREATE TABLE cashier_sessions (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id           CHAR(36) NOT NULL,
    outlet_id           BIGINT UNSIGNED NOT NULL,
    cashier_user_id     BIGINT UNSIGNED NOT NULL,
    pos_device_id       BIGINT UNSIGNED NULL,
    opened_at           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    closed_at           TIMESTAMP NULL,
    opening_cash        DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    closing_cash        DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    expected_cash       DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    difference          DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    notes               TEXT NULL,
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant_outlet (tenant_id, outlet_id),
    INDEX idx_cashier (cashier_user_id),
    INDEX idx_closed (closed_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (outlet_id) REFERENCES outlets(id),
    FOREIGN KEY (cashier_user_id) REFERENCES users(id),
    FOREIGN KEY (pos_device_id) REFERENCES pos_devices(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### BAGIAN 7 — CRM (1 Tabel)

```sql
-- 39/80: customers (dengan kredit/kasbon)
CREATE TABLE customers (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id       CHAR(36) NOT NULL,
    name            VARCHAR(150) NOT NULL,
    phone           VARCHAR(20) NULL,
    email           VARCHAR(255) NULL,
    address         TEXT NULL,
    birth_date      DATE NULL,
    gender          VARCHAR(10) NULL,
    notes           TEXT NULL,
    loyalty_points  INT NOT NULL DEFAULT 0 COMMENT 'cache',
    total_spent     DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'cache',
    visit_count     INT NOT NULL DEFAULT 0 COMMENT 'cache',
    last_visit_at   TIMESTAMP NULL,
    credit_limit    DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'limit kasbon per customer',
    credit_balance  DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'kasbon outstanding',
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      TIMESTAMP NULL,
    CHECK (credit_balance >= 0),
    CHECK (credit_limit >= 0),
    UNIQUE KEY uk_tenant_phone (tenant_id, phone),
    INDEX idx_tenant_email (tenant_id, email),
    INDEX idx_tenant_active (tenant_id, is_active),
    INDEX idx_last_visit (tenant_id, last_visit_at),
    INDEX idx_credit (tenant_id, credit_balance),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### BAGIAN 8 — POS (10 Tabel)

```sql
-- 40/80: orders (dengan hold, kasbon, print_count)
CREATE TABLE orders (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id               CHAR(36) NOT NULL,
    outlet_id               BIGINT UNSIGNED NOT NULL,
    order_number            VARCHAR(50) NOT NULL,
    cashier_user_id         BIGINT UNSIGNED NOT NULL,
    cashier_session_id      BIGINT UNSIGNED NULL,
    table_id                BIGINT UNSIGNED NULL,
    table_session_id        BIGINT UNSIGNED NULL,
    customer_id             BIGINT UNSIGNED NULL,
    customer_name           VARCHAR(150) NULL,
    order_type              VARCHAR(30) NULL,
    status                  VARCHAR(20) NOT NULL DEFAULT 'draft'
                            COMMENT 'draft/open/hold/partially_paid/paid/void/refunded/credit/partially_refunded',
    subtotal                DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    discount_total          DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    voucher_discount        DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    voucher_id              BIGINT UNSIGNED NULL,
    tax_total               DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    service_charge_total    DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    rounding_adjustment     DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    grand_total             DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    paid_total              DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    change_total            DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    print_count             INT NOT NULL DEFAULT 0 COMMENT 'untuk print ulang struk',
    held_at                 TIMESTAMP NULL COMMENT 'saat order di-hold',
    held_by_user_id         BIGINT UNSIGNED NULL,
    credit_due_at           TIMESTAMP NULL COMMENT 'jatuh tempo kasbon',
    notes                   TEXT NULL,
    void_reason             TEXT NULL,
    voided_by_user_id       BIGINT UNSIGNED NULL,
    voided_at               TIMESTAMP NULL,
    metadata                JSON NULL,
    created_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at              TIMESTAMP NULL,
    UNIQUE KEY uk_tenant_order (tenant_id, order_number),
    INDEX idx_tenant_created (tenant_id, created_at),
    INDEX idx_outlet_created (outlet_id, created_at),
    INDEX idx_status (status),
    INDEX idx_table (table_id),
    INDEX idx_customer (customer_id),
    INDEX idx_cashier_session (cashier_session_id),
    INDEX idx_hold (tenant_id, status, held_at),
    INDEX idx_credit (tenant_id, status, credit_due_at),
    INDEX idx_voucher (voucher_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id),
    FOREIGN KEY (outlet_id) REFERENCES outlets(id),
    FOREIGN KEY (cashier_user_id) REFERENCES users(id),
    FOREIGN KEY (cashier_session_id) REFERENCES cashier_sessions(id) ON DELETE SET NULL,
    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE SET NULL,
    FOREIGN KEY (table_session_id) REFERENCES table_sessions(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (voided_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (held_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 41/80: order_items
CREATE TABLE order_items (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id            BIGINT UNSIGNED NOT NULL,
    tenant_id           CHAR(36) NOT NULL,
    product_id          BIGINT UNSIGNED NULL,
    variant_id          BIGINT UNSIGNED NULL,
    product_name        VARCHAR(150) NOT NULL COMMENT 'snapshot',
    variant_name        VARCHAR(100) NULL,
    sku                 VARCHAR(100) NULL,
    unit_price          DECIMAL(15,2) NOT NULL COMMENT 'snapshot',
    cost_price          DECIMAL(15,2) NULL COMMENT 'snapshot',
    quantity            INT NOT NULL,
    discount_amount     DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    tax_amount          DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total               DECIMAL(15,2) NOT NULL,
    modifiers           JSON NULL COMMENT 'snapshot: [{group,option,price_adjustment}]',
    notes               TEXT NULL,
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order (order_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_product (product_id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 42/80: order_splits
CREATE TABLE order_splits (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id                BIGINT UNSIGNED NOT NULL,
    tenant_id               CHAR(36) NOT NULL,
    split_number            INT NOT NULL,
    subtotal                DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    tax_amount              DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    service_charge_amount   DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total                   DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    paid_amount             DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    status                  VARCHAR(20) NOT NULL DEFAULT 'unpaid',
    created_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_order_split (order_id, split_number),
    INDEX idx_order (order_id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 43/80: order_split_items
CREATE TABLE order_split_items (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_split_id      BIGINT UNSIGNED NOT NULL,
    order_item_id       BIGINT UNSIGNED NOT NULL,
    tenant_id           CHAR(36) NOT NULL,
    quantity            INT NOT NULL,
    unit_price          DECIMAL(15,2) NOT NULL,
    discount_amount     DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    tax_amount          DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    amount              DECIMAL(15,2) NOT NULL,
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_split_item (order_split_id, order_item_id),
    INDEX idx_split (order_split_id),
    INDEX idx_order_item (order_item_id),
    INDEX idx_tenant (tenant_id),
    FOREIGN KEY (order_split_id) REFERENCES order_splits(id) ON DELETE CASCADE,
    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 44/80: order_taxes
CREATE TABLE order_taxes (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id        BIGINT UNSIGNED NOT NULL,
    tenant_id       CHAR(36) NOT NULL,
    tax_name        VARCHAR(100) NOT NULL,
    tax_rate        DECIMAL(5,2) NOT NULL,
    tax_base        DECIMAL(15,2) NOT NULL,
    tax_amount      DECIMAL(15,2) NOT NULL,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order (order_id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 45/80: payments
CREATE TABLE payments (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id                BIGINT UNSIGNED NOT NULL,
    order_split_id          BIGINT UNSIGNED NULL,
    tenant_id               CHAR(36) NOT NULL,
    payment_method_id       BIGINT UNSIGNED NULL,
    method_name             VARCHAR(50) NOT NULL,
    amount                  DECIMAL(15,2) NOT NULL,
    reference_number        VARCHAR(100) NULL,
    status                  VARCHAR(20) NOT NULL DEFAULT 'success'
                            COMMENT 'success/pending/failed/refunded',
    paid_at                 TIMESTAMP NULL,
    created_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order (order_id),
    INDEX idx_split (order_split_id),
    INDEX idx_tenant_paid (tenant_id, paid_at),
    INDEX idx_payment_method (payment_method_id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (order_split_id) REFERENCES order_splits(id) ON DELETE SET NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id),
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 46/80: refunds
CREATE TABLE refunds (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id                BIGINT UNSIGNED NOT NULL,
    tenant_id               CHAR(36) NOT NULL,
    amount                  DECIMAL(15,2) NOT NULL,
    reason                  TEXT NULL,
    refund_method           VARCHAR(30) NULL,
    refund_type             VARCHAR(20) NOT NULL DEFAULT 'full' COMMENT 'full/partial',
    processed_by_user_id    BIGINT UNSIGNED NOT NULL,
    refunded_at             TIMESTAMP NULL,
    created_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order (order_id),
    INDEX idx_tenant_refunded (tenant_id, refunded_at),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id),
    FOREIGN KEY (processed_by_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 47/80: refund_items (untuk refund parsial)
CREATE TABLE refund_items (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    refund_id           BIGINT UNSIGNED NOT NULL,
    order_item_id       BIGINT UNSIGNED NOT NULL,
    tenant_id           CHAR(36) NOT NULL,
    quantity            INT NOT NULL,
    unit_price          DECIMAL(15,2) NOT NULL,
    discount_amount     DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    tax_amount          DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    refund_amount       DECIMAL(15,2) NOT NULL,
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_refund_item (refund_id, order_item_id),
    INDEX idx_refund (refund_id),
    INDEX idx_order_item (order_item_id),
    INDEX idx_tenant (tenant_id),
    FOREIGN KEY (refund_id) REFERENCES refunds(id) ON DELETE CASCADE,
    FOREIGN KEY (order_item_id) REFERENCES order_items(id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 48/80: invoices
CREATE TABLE invoices (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id           CHAR(36) NOT NULL,
    invoice_number      VARCHAR(50) NOT NULL,
    customer_name       VARCHAR(150) NOT NULL,
    customer_address    TEXT NULL,
    status              VARCHAR(20) NOT NULL DEFAULT 'unpaid',
    subtotal            DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    discount_total      DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    shipping_cost       DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    other_cost          DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    tax_total           DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    grand_total         DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    due_date            DATE NULL,
    notes               TEXT NULL,
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_tenant_invoice (tenant_id, invoice_number),
    INDEX idx_tenant_status (tenant_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 49/80: invoice_items
CREATE TABLE invoice_items (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id          BIGINT UNSIGNED NOT NULL,
    tenant_id           CHAR(36) NOT NULL,
    product_name        VARCHAR(150) NOT NULL,
    unit_price          DECIMAL(15,2) NOT NULL,
    quantity            INT NOT NULL,
    discount_amount     DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total               DECIMAL(15,2) NOT NULL,
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_invoice (invoice_id),
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### BAGIAN 9 — KAS KELUAR/MASUK (1 Tabel) 🆕

```sql
-- 50/80: cash_movements (IMMUTABLE)
CREATE TABLE cash_movements (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id               CHAR(36) NOT NULL,
    outlet_id               BIGINT UNSIGNED NOT NULL,
    cashier_session_id      BIGINT UNSIGNED NOT NULL,
    movement_type           VARCHAR(20) NOT NULL COMMENT 'cash_in/cash_out',
    amount                  DECIMAL(15,2) NOT NULL,
    reason                  TEXT NOT NULL COMMENT 'wajib, min 10 karakter',
    created_by_user_id      BIGINT UNSIGNED NOT NULL,
    created_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CHECK (amount > 0),
    INDEX idx_tenant_outlet (tenant_id, outlet_id),
    INDEX idx_session (cashier_session_id),
    INDEX idx_type (movement_type),
    INDEX idx_created (created_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (outlet_id) REFERENCES outlets(id),
    FOREIGN KEY (cashier_session_id) REFERENCES cashier_sessions(id),
    FOREIGN KEY (created_by_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### BAGIAN 10 — CRM LOYALTY & SHIFT (2 Tabel)

```sql
-- 51/80: customer_loyalty_points (IMMUTABLE)
CREATE TABLE customer_loyalty_points (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id               CHAR(36) NOT NULL,
    customer_id             BIGINT UNSIGNED NOT NULL,
    points                  INT NOT NULL COMMENT 'positif=earn, negatif=redeem/adjust',
    type                    VARCHAR(20) NOT NULL COMMENT 'earn/redeem/adjust/expire/refund',
    order_id                BIGINT UNSIGNED NULL,
    balance_after           INT NOT NULL,
    notes                   TEXT NULL,
    created_by_user_id      BIGINT UNSIGNED NULL,
    created_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_customer (customer_id, created_at),
    INDEX idx_tenant (tenant_id),
    INDEX idx_order (order_id),
    INDEX idx_type (type),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 52/80: shift_reports (IMMUTABLE)
CREATE TABLE shift_reports (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id               CHAR(36) NOT NULL,
    outlet_id               BIGINT UNSIGNED NOT NULL,
    cashier_session_id      BIGINT UNSIGNED NOT NULL,
    cashier_user_id         BIGINT UNSIGNED NOT NULL,
    total_transactions      INT NOT NULL DEFAULT 0,
    total_sales             DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_cash              DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_non_cash          DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_refunds           DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_discounts         DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_tax               DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_service_charge    DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_cash_in           DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_cash_out          DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_credit_sales      DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    opening_cash            DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    closing_cash            DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    expected_cash           DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    cash_variance           DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    notes                   TEXT NULL,
    generated_at            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_session (cashier_session_id),
    INDEX idx_tenant_outlet (tenant_id, outlet_id),
    INDEX idx_cashier (cashier_user_id),
    INDEX idx_generated (generated_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (outlet_id) REFERENCES outlets(id),
    FOREIGN KEY (cashier_session_id) REFERENCES cashier_sessions(id),
    FOREIGN KEY (cashier_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### BAGIAN 11 — SETTINGS (7 Tabel)

```sql
-- 53/80: receipt_settings
CREATE TABLE receipt_settings (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id               CHAR(36) NOT NULL,
    outlet_id               BIGINT UNSIGNED NULL,
    header_text             TEXT NULL,
    footer_text             TEXT NULL,
    notes_text              TEXT NULL,
    show_tax_percentage     BOOLEAN NOT NULL DEFAULT TRUE,
    max_print_count         INT NULL,
    queue_number_enabled    BOOLEAN NOT NULL DEFAULT FALSE,
    queue_prefix            VARCHAR(10) NULL,
    logo_display            VARCHAR(20) NOT NULL DEFAULT 'normal',
    display_options         JSON NULL,
    created_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_outlet (tenant_id, outlet_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (outlet_id) REFERENCES outlets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 54/80: tax_settings
CREATE TABLE tax_settings (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id               CHAR(36) NOT NULL,
    outlet_id               BIGINT UNSIGNED NULL,
    is_enabled              BOOLEAN NOT NULL DEFAULT FALSE,
    calculation_method      VARCHAR(30) NOT NULL DEFAULT 'after_discount'
                            COMMENT 'before_discount/after_discount/inclusive',
    tax_per_transaction     BOOLEAN NOT NULL DEFAULT TRUE,
    tax_per_product         BOOLEAN NOT NULL DEFAULT FALSE,
    default_tax_rate        DECIMAL(5,2) NOT NULL DEFAULT 11.00,
    order_type_checklist    JSON NULL,
    created_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_outlet (tenant_id, outlet_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (outlet_id) REFERENCES outlets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 55/80: service_charge_settings
CREATE TABLE service_charge_settings (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id               CHAR(36) NOT NULL,
    outlet_id               BIGINT UNSIGNED NULL,
    is_enabled              BOOLEAN NOT NULL DEFAULT FALSE,
    percentage              DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    is_taxable              BOOLEAN NOT NULL DEFAULT FALSE,
    order_type_checklist    JSON NULL,
    created_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_outlet (tenant_id, outlet_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (outlet_id) REFERENCES outlets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 56/80: rounding_settings
CREATE TABLE rounding_settings (
    id                          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id                   CHAR(36) NOT NULL,
    outlet_id                   BIGINT UNSIGNED NULL,
    cash_rounding_enabled       BOOLEAN NOT NULL DEFAULT FALSE,
    cash_rounding_step          INT NOT NULL DEFAULT 100,
    non_cash_rounding_enabled   BOOLEAN NOT NULL DEFAULT FALSE,
    non_cash_rounding_step      INT NULL,
    created_at                  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_outlet (tenant_id, outlet_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (outlet_id) REFERENCES outlets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 57/80: cashier_settings
CREATE TABLE cashier_settings (
    id                          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id                   CHAR(36) NOT NULL,
    outlet_id                   BIGINT UNSIGNED NULL COMMENT 'NULL = default tenant',
    default_opening_cash        DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    max_discount_percent        DECIMAL(5,2) NULL COMMENT 'NULL = tidak terbatas',
    can_void                    BOOLEAN NOT NULL DEFAULT TRUE,
    can_refund                  BOOLEAN NOT NULL DEFAULT FALSE,
    can_discount                BOOLEAN NOT NULL DEFAULT TRUE,
    auto_close_shift_hours      INT NULL COMMENT 'NULL = OFF',
    cash_movement_enabled       BOOLEAN NOT NULL DEFAULT TRUE,
    created_at                  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_tenant_outlet (tenant_id, outlet_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (outlet_id) REFERENCES outlets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 58/80: ewallet_settings
CREATE TABLE ewallet_settings (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id               CHAR(36) NOT NULL,
    qris_enabled            BOOLEAN NOT NULL DEFAULT FALSE,
    qris_qr_url             VARCHAR(500) NULL COMMENT 'URL file QR code QRIS statis',
    qris_merchant_name      VARCHAR(150) NULL,
    created_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_tenant (tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 59/80: notification_preferences
CREATE TABLE notification_preferences (
    id                          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id                   CHAR(36) NOT NULL,
    user_id                     BIGINT UNSIGNED NOT NULL,
    notify_product_changes      BOOLEAN NOT NULL DEFAULT TRUE,
    notify_employee_changes     BOOLEAN NOT NULL DEFAULT TRUE,
    notify_settings_changes     BOOLEAN NOT NULL DEFAULT TRUE,
    notify_subscription_changes BOOLEAN NOT NULL DEFAULT TRUE,
    notify_low_stock            BOOLEAN NOT NULL DEFAULT TRUE,
    notify_credit_due           BOOLEAN NOT NULL DEFAULT TRUE,
    created_at                  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_user (user_id),
    INDEX idx_tenant (tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### BAGIAN 12 — PROMOSI (4 Tabel) 🆕

```sql
-- 60/80: vouchers
CREATE TABLE vouchers (
    id                          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id                   CHAR(36) NOT NULL,
    code                        VARCHAR(50) NOT NULL,
    name                        VARCHAR(150) NOT NULL,
    discount_type               VARCHAR(20) NOT NULL COMMENT 'percent/fixed',
    discount_value              DECIMAL(15,2) NOT NULL,
    min_purchase                DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    max_usage_total             INT NULL COMMENT 'NULL = unlimited',
    max_usage_per_customer      INT NULL COMMENT 'NULL = unlimited',
    usage_count                 INT NOT NULL DEFAULT 0,
    starts_at                   TIMESTAMP NOT NULL,
    ends_at                     TIMESTAMP NOT NULL,
    is_stackable                BOOLEAN NOT NULL DEFAULT FALSE,
    is_active                   BOOLEAN NOT NULL DEFAULT TRUE,
    created_by_user_id          BIGINT UNSIGNED NOT NULL,
    created_at                  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CHECK (discount_value > 0),
    UNIQUE KEY uk_tenant_code (tenant_id, code),
    INDEX idx_tenant_active (tenant_id, is_active),
    INDEX idx_dates (starts_at, ends_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 61/80: voucher_redemptions (IMMUTABLE)
CREATE TABLE voucher_redemptions (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id               CHAR(36) NOT NULL,
    voucher_id              BIGINT UNSIGNED NOT NULL,
    order_id                BIGINT UNSIGNED NOT NULL,
    customer_id             BIGINT UNSIGNED NULL,
    discount_applied        DECIMAL(15,2) NOT NULL,
    redeemed_at             TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_voucher (voucher_id),
    INDEX idx_order (order_id),
    INDEX idx_customer (customer_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_redeemed (redeemed_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (voucher_id) REFERENCES vouchers(id),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 62/80: scheduled_discounts
CREATE TABLE scheduled_discounts (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id               CHAR(36) NOT NULL,
    name                    VARCHAR(150) NOT NULL,
    discount_type           VARCHAR(20) NOT NULL COMMENT 'percent/fixed',
    discount_value          DECIMAL(15,2) NOT NULL,
    scope_type              VARCHAR(20) NOT NULL DEFAULT 'all' COMMENT 'all/category/product',
    scope_ids               JSON NULL COMMENT '[1,2,3] untuk category/product IDs',
    starts_at               TIMESTAMP NOT NULL,
    ends_at                 TIMESTAMP NOT NULL,
    time_start              TIME NULL COMMENT 'NULL = sepanjang hari',
    time_end                TIME NULL,
    days_of_week            JSON NULL COMMENT '[1,2,3,4,5] Senin-Jumat; NULL = semua hari',
    is_active               BOOLEAN NOT NULL DEFAULT TRUE,
    created_by_user_id      BIGINT UNSIGNED NOT NULL,
    created_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CHECK (discount_value > 0),
    INDEX idx_tenant_active (tenant_id, is_active),
    INDEX idx_dates (starts_at, ends_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### BAGIAN 13 — LANGGANAN (3 Tabel)

```sql
-- 63/80: subscriptions (dengan generated column untuk 1 active per tenant)
CREATE TABLE subscriptions (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id           CHAR(36) NOT NULL,
    plan_id             BIGINT UNSIGNED NOT NULL,
    status              VARCHAR(20) NOT NULL DEFAULT 'trial'
                        COMMENT 'trial/pending_payment/active/grace/expired/archived',
    billing_cycle       VARCHAR(20) NOT NULL DEFAULT 'monthly',
    started_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ends_at             TIMESTAMP NOT NULL,
    trial_ends_at       TIMESTAMP NULL,
    grace_ends_at       TIMESTAMP NULL,
    expired_at          TIMESTAMP NULL,
    grace_type          VARCHAR(20) NULL COMMENT 'grace/expired',
    pending_plan_id     BIGINT UNSIGNED NULL,
    cancelled_at        TIMESTAMP NULL,
    auto_renew          BOOLEAN NOT NULL DEFAULT FALSE,
    current_flag        TINYINT AS (
                            IF(status IN ('trial','pending_payment','active','grace'), 1, NULL)
                        ) STORED COMMENT '1 = subscription aktif; NULL = tidak aktif',
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_tenant_current (tenant_id, current_flag),
    INDEX idx_tenant_status (tenant_id, status),
    INDEX idx_ends (ends_at),
    INDEX idx_grace (grace_ends_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id),
    FOREIGN KEY (plan_id) REFERENCES plans(id),
    FOREIGN KEY (pending_plan_id) REFERENCES plans(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ⚠ ALTER: billing_invoices.subscription_id FK (circular fix #2)
ALTER TABLE billing_invoices
ADD CONSTRAINT fk_billing_subscription
FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE SET NULL;

-- 64/80: tenant_add_ons
CREATE TABLE tenant_add_ons (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id       CHAR(36) NOT NULL,
    add_on_id       BIGINT UNSIGNED NOT NULL,
    quantity        INT NOT NULL DEFAULT 1,
    unit_price      DECIMAL(15,2) NOT NULL,
    period_start    DATE NULL,
    period_end      DATE NULL,
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_addon (add_on_id),
    INDEX idx_active (is_active),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (add_on_id) REFERENCES add_ons(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 65/80: plan_change_requests
CREATE TABLE plan_change_requests (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id               CHAR(36) NOT NULL,
    from_plan_id            BIGINT UNSIGNED NOT NULL,
    to_plan_id              BIGINT UNSIGNED NOT NULL,
    change_type             VARCHAR(20) NOT NULL COMMENT 'upgrade/downgrade',
    requested_by_user_id    BIGINT UNSIGNED NOT NULL,
    status                  VARCHAR(20) NOT NULL DEFAULT 'pending'
                            COMMENT 'pending/approved/rejected/cancelled',
    prorated_amount         DECIMAL(15,2) NULL,
    effective_at            TIMESTAMP NULL,
    created_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_status (tenant_id, status),
    INDEX idx_created (created_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id),
    FOREIGN KEY (from_plan_id) REFERENCES plans(id),
    FOREIGN KEY (to_plan_id) REFERENCES plans(id),
    FOREIGN KEY (requested_by_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### BAGIAN 14 — CROSS-CUTTING & COMPLIANCE (6 Tabel)

```sql
-- 66/80: audit_logs (IMMUTABLE)
CREATE TABLE audit_logs (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    actor_type      VARCHAR(20) NOT NULL,
    actor_id        BIGINT UNSIGNED NULL,
    tenant_id       CHAR(36) NULL,
    action          VARCHAR(100) NOT NULL,
    entity_type     VARCHAR(50) NULL,
    entity_id       VARCHAR(36) NULL,
    metadata        JSON NULL,
    ip_address      VARCHAR(45) NULL,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_actor (actor_type, actor_id),
    INDEX idx_action (action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 67/80: notifications
CREATE TABLE notifications (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id       CHAR(36) NULL,
    user_id         BIGINT UNSIGNED NULL,
    admin_user_id   BIGINT UNSIGNED NULL,
    type            VARCHAR(50) NOT NULL,
    title           VARCHAR(150) NOT NULL,
    body            TEXT NOT NULL,
    data            JSON NULL,
    read_at         TIMESTAMP NULL,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_read (user_id, read_at),
    INDEX idx_admin_read (admin_user_id, read_at),
    INDEX idx_tenant (tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_user_id) REFERENCES admin_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 68/80: cookie_consents (INSERT only)
CREATE TABLE cookie_consents (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id           CHAR(36) NULL,
    user_id             BIGINT UNSIGNED NULL,
    session_id          VARCHAR(40) NULL,
    necessary           BOOLEAN NOT NULL DEFAULT TRUE,
    analytics           BOOLEAN NOT NULL DEFAULT FALSE,
    marketing           BOOLEAN NOT NULL DEFAULT FALSE,
    consent_version     VARCHAR(10) NOT NULL,
    ip_address          VARCHAR(45) NULL,
    user_agent          VARCHAR(255) NULL,
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_user (user_id),
    INDEX idx_created (created_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 69/80: export_jobs
CREATE TABLE export_jobs (
    id                          CHAR(36) PRIMARY KEY,
    tenant_id                   CHAR(36) NOT NULL,
    requested_by_user_id        BIGINT UNSIGNED NOT NULL,
    type                        VARCHAR(30) NOT NULL
                                COMMENT 'products/transactions/customers/stock_movements/shift_reports/cash_movements/vouchers',
    format                      VARCHAR(10) NOT NULL DEFAULT 'csv',
    period_from                 DATE NULL,
    period_to                   DATE NULL,
    status                      VARCHAR(20) NOT NULL DEFAULT 'pending',
    file_path                   VARCHAR(500) NULL,
    file_size_bytes             BIGINT NULL,
    row_count                   INT NULL,
    storage_driver              VARCHAR(20) NOT NULL DEFAULT 'local',
    signed_url_expires_at       TIMESTAMP NULL,
    checksum                    VARCHAR(64) NULL,
    error_message               TEXT NULL,
    expires_at                  TIMESTAMP NOT NULL,
    completed_at                TIMESTAMP NULL,
    created_at                  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_status (tenant_id, status),
    INDEX idx_expires (expires_at),
    INDEX idx_created (created_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (requested_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 70/80: account_deletions
CREATE TABLE account_deletions (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id               CHAR(36) NOT NULL,
    requested_by_user_id    BIGINT UNSIGNED NOT NULL,
    reason                  TEXT NULL,
    status                  VARCHAR(20) NOT NULL DEFAULT 'pending',
    grace_period_ends_at    TIMESTAMP NOT NULL,
    archived_at             TIMESTAMP NULL,
    cancelled_at            TIMESTAMP NULL,
    cancelled_by_user_id    BIGINT UNSIGNED NULL,
    notes                   TEXT NULL,
    created_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_status (status),
    INDEX idx_grace (grace_period_ends_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id),
    FOREIGN KEY (requested_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (cancelled_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 71/80: retention_jobs
CREATE TABLE retention_jobs (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_name                VARCHAR(100) NOT NULL UNIQUE,
    table_name              VARCHAR(100) NOT NULL,
    retention_days          INT NOT NULL,
    schedule                VARCHAR(50) NOT NULL,
    last_run_at             TIMESTAMP NULL,
    last_run_status         VARCHAR(20) NULL,
    last_run_affected       INT NOT NULL DEFAULT 0,
    is_active               BOOLEAN NOT NULL DEFAULT TRUE,
    notes                   TEXT NULL,
    created_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### BAGIAN 15 — SUPPORT & ANALYTICS (3 Tabel)

```sql
-- 72/80: support_tickets
CREATE TABLE support_tickets (
    id                              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id                       CHAR(36) NOT NULL,
    user_id                         BIGINT UNSIGNED NULL,
    ticket_number                   VARCHAR(20) NOT NULL UNIQUE,
    subject                         VARCHAR(255) NOT NULL,
    description                     TEXT NOT NULL,
    category                        VARCHAR(30) NULL,
    priority                        VARCHAR(20) NOT NULL DEFAULT 'normal',
    status                          VARCHAR(20) NOT NULL DEFAULT 'open',
    assigned_to_admin_user_id       BIGINT UNSIGNED NULL,
    attachment_path                 VARCHAR(500) NULL,
    first_response_at               TIMESTAMP NULL,
    resolved_at                     TIMESTAMP NULL,
    closed_at                       TIMESTAMP NULL,
    created_at                      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_status (tenant_id, status),
    INDEX idx_assigned (assigned_to_admin_user_id, status),
    INDEX idx_created (created_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to_admin_user_id) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 73/80: support_ticket_messages
CREATE TABLE support_ticket_messages (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id           BIGINT UNSIGNED NOT NULL,
    tenant_id           CHAR(36) NOT NULL,
    admin_user_id       BIGINT UNSIGNED NULL,
    user_id             BIGINT UNSIGNED NULL,
    body                TEXT NOT NULL,
    attachment_path     VARCHAR(500) NULL,
    is_internal         BOOLEAN NOT NULL DEFAULT FALSE,
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ticket_created (ticket_id, created_at),
    INDEX idx_tenant (tenant_id),
    FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_user_id) REFERENCES admin_users(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 74/80: analytics_events
CREATE TABLE analytics_events (
    id              CHAR(36) PRIMARY KEY,
    event_name      VARCHAR(100) NOT NULL,
    tenant_id       CHAR(36) NULL,
    user_id         BIGINT UNSIGNED NULL,
    admin_user_id   BIGINT UNSIGNED NULL,
    environment     VARCHAR(20) NOT NULL,
    properties      JSON NULL,
    session_id      VARCHAR(40) NULL,
    ip_address      VARCHAR(45) NULL,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event_created (event_name, created_at),
    INDEX idx_tenant_event (tenant_id, event_name),
    INDEX idx_created (created_at),
    INDEX idx_environment (environment),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (admin_user_id) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### BAGIAN 16 — PRODUCTION READINESS [PROD] (6 Tabel)

```sql
-- 75/80: releases [PROD]
CREATE TABLE releases (
    id                              CHAR(36) PRIMARY KEY,
    version                         VARCHAR(20) NOT NULL UNIQUE,
    name                            VARCHAR(100) NOT NULL,
    description                     TEXT NULL,
    status                          VARCHAR(20) NOT NULL DEFAULT 'draft'
                                    COMMENT 'draft/gate_evaluation/ready/cutover/released/rolled_back/cancelled',
    target_environment              VARCHAR(20) NOT NULL DEFAULT 'production',
    planned_at                      TIMESTAMP NULL,
    deployed_at                     TIMESTAMP NULL,
    released_by_admin_user_id       BIGINT UNSIGNED NULL,
    git_tag                         VARCHAR(50) NULL,
    changelog_url                   VARCHAR(500) NULL,
    created_at                      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_target_env (target_environment),
    INDEX idx_planned (planned_at),
    INDEX idx_deployed (deployed_at),
    FOREIGN KEY (released_by_admin_user_id) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 76/80: release_gates [PROD] (IMMUTABLE)
CREATE TABLE release_gates (
    id                          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    release_id                  CHAR(36) NOT NULL,
    gate_code                   VARCHAR(10) NOT NULL COMMENT 'RG-1..RG-10',
    gate_name                   VARCHAR(100) NOT NULL,
    criteria                    TEXT NOT NULL,
    status                      VARCHAR(20) NOT NULL DEFAULT 'pending'
                                COMMENT 'pending/in_progress/pass/fail/forced',
    evidence_url                VARCHAR(500) NULL,
    evidence_notes              TEXT NULL,
    reviewer_admin_user_id      BIGINT UNSIGNED NULL,
    reviewed_at                 TIMESTAMP NULL,
    is_forced                   BOOLEAN NOT NULL DEFAULT FALSE,
    forced_by_admin_user_id     BIGINT UNSIGNED NULL,
    forced_reason               TEXT NULL,
    forced_at                   TIMESTAMP NULL,
    created_at                  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_release_gate (release_id, gate_code),
    INDEX idx_status (status),
    INDEX idx_reviewer (reviewer_admin_user_id),
    INDEX idx_forced (is_forced),
    FOREIGN KEY (release_id) REFERENCES releases(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_admin_user_id) REFERENCES admin_users(id) ON DELETE SET NULL,
    FOREIGN KEY (forced_by_admin_user_id) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 77/80: deployments [PROD]
CREATE TABLE deployments (
    id                              CHAR(36) PRIMARY KEY,
    release_id                      CHAR(36) NULL,
    environment                     VARCHAR(20) NOT NULL COMMENT 'staging/production-test/production',
    version                         VARCHAR(20) NOT NULL,
    commit_hash                     VARCHAR(40) NULL,
    docker_image                    VARCHAR(255) NULL,
    status                          VARCHAR(20) NOT NULL DEFAULT 'pending'
                                    COMMENT 'pending/in_progress/success/failed/rolled_back',
    deployed_by_admin_user_id       BIGINT UNSIGNED NULL,
    triggered_by                    VARCHAR(30) NULL COMMENT 'manual/ci_cd/scheduler/rollback/hotfix',
    started_at                      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at                    TIMESTAMP NULL,
    duration_seconds                INT NULL,
    health_check_status             VARCHAR(20) NULL COMMENT 'healthy/degraded/unhealthy',
    health_check_at                 TIMESTAMP NULL,
    error_message                   TEXT NULL,
    notes                           TEXT NULL,
    created_at                      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_release (release_id),
    INDEX idx_env_status (environment, status),
    INDEX idx_started (started_at),
    INDEX idx_version (version),
    INDEX idx_health (health_check_status),
    FOREIGN KEY (release_id) REFERENCES releases(id) ON DELETE SET NULL,
    FOREIGN KEY (deployed_by_admin_user_id) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 78/80: cutover_runs [PROD] (IMMUTABLE)
CREATE TABLE cutover_runs (
    id                              CHAR(36) PRIMARY KEY,
    release_id                      CHAR(36) NOT NULL,
    environment                     VARCHAR(20) NOT NULL DEFAULT 'production',
    status                          VARCHAR(20) NOT NULL DEFAULT 'not_started'
                                    COMMENT 'not_started/in_progress/completed/aborted/rolled_back',
    started_by_admin_user_id        BIGINT UNSIGNED NULL,
    started_at                      TIMESTAMP NULL,
    completed_at                    TIMESTAMP NULL,
    aborted_at                      TIMESTAMP NULL,
    abort_reason                    TEXT NULL,
    duration_seconds                INT NULL,
    cutover_lock_key                VARCHAR(100) NULL,
    notes                           TEXT NULL,
    created_at                      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_release (release_id),
    INDEX idx_status (status),
    INDEX idx_started (started_at),
    INDEX idx_env (environment),
    FOREIGN KEY (release_id) REFERENCES releases(id),
    FOREIGN KEY (started_by_admin_user_id) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 79/80: cutover_checklist_items [PROD] (IMMUTABLE)
CREATE TABLE cutover_checklist_items (
    id                              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cutover_run_id                  CHAR(36) NOT NULL,
    item_order                      INT NOT NULL COMMENT '1..10',
    item_code                       VARCHAR(50) NOT NULL,
    title                           VARCHAR(200) NOT NULL,
    description                     TEXT NULL,
    pic_role                        VARCHAR(30) NULL COMMENT 'Release Manager/Ops/QA',
    is_completed                    BOOLEAN NOT NULL DEFAULT FALSE,
    completed_by_admin_user_id      BIGINT UNSIGNED NULL,
    completed_at                    TIMESTAMP NULL,
    notes                           TEXT NULL,
    evidence_url                    VARCHAR(500) NULL,
    created_at                      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_run_order (cutover_run_id, item_order),
    UNIQUE KEY uk_run_code (cutover_run_id, item_code),
    INDEX idx_completed (is_completed),
    FOREIGN KEY (cutover_run_id) REFERENCES cutover_runs(id) ON DELETE CASCADE,
    FOREIGN KEY (completed_by_admin_user_id) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 80/80: rollback_events [PROD] (IMMUTABLE)
CREATE TABLE rollback_events (
    id                              CHAR(36) PRIMARY KEY,
    deployment_id                   CHAR(36) NOT NULL,
    release_id                      CHAR(36) NULL,
    cutover_run_id                  CHAR(36) NULL,
    target_version                  VARCHAR(20) NOT NULL,
    target_deployment_id            CHAR(36) NULL,
    reason                          TEXT NOT NULL,
    trigger_type                    VARCHAR(30) NULL
                                    COMMENT 'smoke_test_fail/error_rate/health_check/data_corruption/security_p1/manual',
    status                          VARCHAR(20) NOT NULL DEFAULT 'pending'
                                    COMMENT 'pending/in_progress/completed/failed',
    requested_by_admin_user_id      BIGINT UNSIGNED NOT NULL,
    approved_by_admin_user_id       BIGINT UNSIGNED NULL,
    re_auth_at                      TIMESTAMP NULL,
    started_at                      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at                    TIMESTAMP NULL,
    duration_seconds                INT NULL,
    health_check_status             VARCHAR(20) NULL,
    error_message                   TEXT NULL,
    audit_log_id                    BIGINT UNSIGNED NULL,
    post_mortem_url                 VARCHAR(500) NULL,
    created_at                      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_deployment (deployment_id),
    INDEX idx_release (release_id),
    INDEX idx_cutover (cutover_run_id),
    INDEX idx_status (status),
    INDEX idx_target_version (target_version),
    INDEX idx_started (started_at),
    INDEX idx_trigger (trigger_type),
    FOREIGN KEY (deployment_id) REFERENCES deployments(id),
    FOREIGN KEY (release_id) REFERENCES releases(id) ON DELETE SET NULL,
    FOREIGN KEY (cutover_run_id) REFERENCES cutover_runs(id) ON DELETE SET NULL,
    FOREIGN KEY (target_deployment_id) REFERENCES deployments(id) ON DELETE SET NULL,
    FOREIGN KEY (requested_by_admin_user_id) REFERENCES admin_users(id),
    FOREIGN KEY (approved_by_admin_user_id) REFERENCES admin_users(id) ON DELETE SET NULL,
    FOREIGN KEY (audit_log_id) REFERENCES audit_logs(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## §5. Circular Dependency Handling

2 circular dependency di-handle via **ALTER TABLE** setelah tabel terkait dibuat:

| # | Circular | Solusi | Posisi ALTER |
|---|---|---|---|
| 1 | tenants.owner_user_id → users.id | tenants dibuat tanpa FK; ALTER setelah users (#10) | Setelah #10 |
| 2 | billing_invoices.subscription_id → subscriptions.id | billing_invoices dibuat tanpa FK; ALTER setelah subscriptions (#63) | Setelah #63 |

> **Catatan:** Circular dependency `table_sessions.order_id → orders.id` telah dihilangkan. Relasi cukup menggunakan `orders.table_session_id` yang sudah ada. Circular dependency `products.tax_rate_id → tax_rates.id` bukan circular karena tax_rates (#24) dibuat sebelum products (#25).

---

## §6. Immutable Tables

10 tabel yang **hanya boleh INSERT** — tidak boleh UPDATE/DELETE:

| # | Tabel | Alasan | Retensi |
|---|---|---|---|
| 1 | audit_logs | Audit trail wajib immutable | 90 hari |
| 2 | stock_movements | Stock ledger harus akurat | 5 tahun |
| 3 | customer_loyalty_points | Poin ledger harus akurat | Selama customer aktif + 30 hari |
| 4 | shift_reports | Shift snapshot harus immutable | 5 tahun |
| 5 | release_gates | Gate evidence tidak boleh diubah | 5 tahun |
| 6 | cutover_runs | Cutover record tidak boleh diubah | 5 tahun |
| 7 | deployments | Status berubah selama deploy, tapi setelah final immutable | 2 tahun |
| 8 | rollback_events | Rollback record tidak boleh diubah | 5 tahun |
| 9 | cash_movements | Kas keluar/masuk harus akurat | 5 tahun |
| 10 | voucher_redemptions | Redemption record tidak boleh diubah | 5 tahun |

**Enforcement:** Di level aplikasi (service layer menolak UPDATE/DELETE) + review code + audit kuartalan.

---

## §7. Seeder (27)

### 7.1 Reference Seeder (8) — Semua Environment

| # | Seeder | Target | Isi |
|---|---|---|---|
| 1 | PlanSeeder | plans | 6 paket: trial, starter_basic, starter, advance, prime, prime_plus |
| 2 | FeatureSeeder | features | 21 fitur gated |
| 3 | PlanFeatureSeeder | plan_features | Mapping 21 × 6 = 126 baris |
| 4 | AddOnSeeder | add_ons | 3 add-on: extra_pos, extra_employee, extra_admin |
| 5 | AdminSeeder | admin_users | 1 super admin default |
| 6 | TaxRateSeeder | tax_rates | PPN 11% (default), PB1 10%, Service Tax 5% |
| 7 | RetentionJobSeeder | retention_jobs | 12 retention job |
| 8 | PaymentMethodSeeder | payment_methods | Default methods: Tunai, Transfer, QRIS, Kasbon |

### 7.2 Demo Seeder (16) — Lokal/Staging Only

| # | Seeder | Target | Isi |
|---|---|---|---|
| 9 | DemoTenantSeeder | tenants, outlets, users | 3 tenant demo |
| 10 | DemoProductSeeder | categories, products, product_variants | 20 produk + 3 varian |
| 11 | DemoOrderSeeder | orders, order_items, payments, order_splits | 50 order |
| 12 | DemoTableSeeder | tables, table_sessions | 10 meja |
| 13 | CookieConsentSeeder | cookie_consents | 1 consent |
| 14 | SupportTicketSeeder | support_tickets, support_ticket_messages | 3 tiket + 5 pesan |
| 15 | ExportJobSeeder | export_jobs | 1 export |
| 16 | AnalyticsEventSeeder | analytics_events | 100 event |
| 17 | ModifierSeeder | modifiers, modifier_options, product_modifiers | 3 grup + 8 option |
| 18 | SupplierSeeder | suppliers | 3 supplier |
| 19 | CustomerSeeder | customers, customer_loyalty_points | 10 customer + 100 poin |
| 20 | VoucherSeeder | vouchers | 3 voucher demo |
| 21 | ScheduledDiscountSeeder | scheduled_discounts | 2 diskon terjadwal |
| 22 | CashierSettingsSeeder | cashier_settings | Default settings per tenant |
| 23 | NotificationPreferencesSeeder | notification_preferences | Default preferences |
| 24 | DemoCashMovementSeeder | cash_movements | 5 cash movements |

### 7.3 Production Seeder (3) — Production-test Only

| # | Seeder | Target | Isi |
|---|---|---|---|
| 25 | ReleaseSeeder | releases | 1 release v2.0.0 |
| 26 | ReleaseGateSeeder | release_gates | 10 RG per release |
| 27 | CutoverChecklistTemplateSeeder | cutover_checklist_items | 10 item template |

### 7.4 Aturan Seeder

| Aturan | Detail |
|---|---|
| Idempotent | Bisa dijalankan ulang tanpa duplikasi |
| Dipisah dari migration | Seeder bukan migration |
| Demo seeder tidak jalan di production | Cek APP_ENV |
| Tidak berisi PII | Semua data fiktif |
| Nama deskriptif | DemoProductSeeder, bukan Seeder1 |
| FeatureSeeder wajib 21 fitur | Sinkron dengan feature matrix |
| PlanFeatureSeeder wajib 21×6 | Sinkron dengan feature matrix |
| RetentionJobSeeder wajib 12 job | Sinkron dengan retention policy |
| Modifier/Supplier/Customer/Voucher Seeder hanya lokal/staging | Tidak di production |
| PaymentMethodSeeder wajib include QRIS dan Kasbon | Sinkron dengan payment methods |

---

## §8. Migration Rules

### 8.1 Aturan Umum

| Aturan | Detail |
|---|---|
| Naming | YYYY_MM_DD_HHMMSS_create_{table}_table.php |
| Charset | utf8mb4 + utf8mb4_unicode_ci |
| UUID | CHAR(36) untuk tabel yang ditentukan |
| Kolom standar | id, created_at, updated_at (+deleted_at jika soft delete) |
| down() | Wajib lengkap — bisa rollback |
| Sama di semua env | Tidak ada migration khusus per environment |
| CI test rollback | migrate:fresh → migrate:rollback --step=80 → migrate |
| CHECK constraint | Wajib untuk validasi bisnis (stock_qty >= 0, amount > 0, dll) |
| Generated column | Untuk subscriptions.current_flag (1 active per tenant) |

### 8.2 Urutan Migration

Migration harus mengikuti urutan nomor tabel (#1–#80) agar FK bisa dibuat tanpa error. 2 circular dependency di-handle via ALTER TABLE terpisah.

### 8.3 Rollback Test

```bash
php artisan migrate:fresh
php artisan migrate:rollback --step=80
php artisan migrate
```

Wajib lulus di CI setiap PR yang mengubah migration.

---

## §9. Environment Database Matrix

| Environment | Provider | Engine | Charset | Data |
|---|---|---|---|---|
| **Local** | Docker Compose MySQL 8.4 | MySQL 8.4 | utf8mb4_unicode_ci | Seed |
| **Staging** | TiDB Cloud Serverless | MySQL-compatible | utf8mb4_unicode_ci | Seed (no PII) |
| **Production-test** | TiDB Cloud Serverless | MySQL-compatible | utf8mb4_unicode_ci | Seed (no PII) |
| **Production** | MySQL 8.4 self-managed (VPS Jakarta) | MySQL 8.4 | utf8mb4_unicode_ci | PII asli |

### 9.1 Koneksi Staging (TiDB Cloud)

```
TIDB_HOST=xxx.tidbcloud.com
TIDB_PORT=4000
TIDB_DATABASE=kasirku_staging
TIDB_USERNAME=xxx
TIDB_PASSWORD=***
TIDB_SSL_CA=/etc/ssl/certs/ca-certificates.crt
```

### 9.2 Koneksi Production (MySQL 8.4)

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kasirku_production
DB_USERNAME=kasirku_app
DB_PASSWORD=***
```

### 9.3 Perbedaan MySQL vs TiDB

| Fitur | MySQL 8.4 (Production) | TiDB Cloud (Staging) |
|---|---|---|
| FOREIGN KEY | Fully enforced | Supported (TiDB 6.6+) |
| AUTO_INCREMENT | Standard | Standard |
| JSON | Standard | Standard |
| TIMESTAMP | Standard | Standard |
| Partition | Supported | Supported |
| utf8mb4 | Standard | Standard |
| DECIMAL | Standard | Standard |
| CHAR(36) UUID | Standard | Standard |
| CHECK constraint | Fully enforced | Supported (TiDB 6.6+) |
| Generated column (STORED) | Supported | Supported |

> **Catatan:** Tidak ada perbedaan signifikan yang mempengaruhi DDL Kasirku. Semua 80 tabel kompatibel dengan kedua engine.

---

## §10. Verifikasi & Sign-off

### 10.1 Verifikasi 80 Tabel

| Kategori | Jumlah | Nomor |
|---|---|---|
| Shared | 4 | #1–#4 |
| Admin/Internal | 3 | #5–#7 |
| Tenant Core & Akses | 10 | #8–#17 |
| Internal Tenant-Dependent | 5 | #18–#22 |
| Settings Awal & Produk | 6 | #23–#28 |
| Modifier, Inventori, Meja | 10 | #29–#38 |
| CRM | 1 | #39 |
| POS | 10 | #40–#49 |
| Kas Keluar/Masuk | 1 | #50 |
| CRM Loyalty & Shift | 2 | #51–#52 |
| Settings | 7 | #53–#59 |
| Promosi | 3 | #60–#62 |
| Langganan | 3 | #63–#65 |
| Cross-Cutting & Compliance | 6 | #66–#71 |
| Support & Analytics | 3 | #72–#74 |
| Production Readiness [PROD] | 6 | #75–#80 |
| **TOTAL** | **80** | ✅ |

### 10.2 Sign-off Checklist

- ☑ 80 tabel terdefinisi (#1–#80)
- ☑ DDL lengkap untuk semua 80 tabel
- ☑ ERD global + 15 ERD domain lengkap
- ☑ 2 circular dependency di-handle via ALTER TABLE
- ☑ 10 immutable tables teridentifikasi
- ☑ Semua FK & index eksplisit
- ☑ CHECK constraints untuk validasi bisnis
- ☑ Generated column untuk 1 active subscription per tenant
- ☑ Charset utf8mb4_unicode_ci konsisten
- ☑ 27 seeder terdefinisi
- ☑ Migration rules terdokumentasi
- ☑ Rollback test terdefinisi
- ☑ Environment database matrix terdokumentasi
- ☑ TiDB Cloud compatibility verified

### 10.3 Stakeholder Approval

| Role | Nama | Tanggal |
|---|---|---|
| DBA | _________________ | ______ |
| Engineering Lead | _________________ | ______ |
| Tech Lead | _________________ | ______ |
| Backend Lead | _________________ | ______ |

---

## Ringkasan Eksekutif

**Schema v2.0** adalah panduan database final untuk Kasirku. Mencakup **80 tabel** dengan DDL lengkap, **ERD global + 15 ERD domain**, **2 circular dependency** di-handle via ALTER TABLE, **10 immutable tables**, dan **27 seeder**.

**Domain utama:**
- **Tenant Core** (10 tabel) — tenants sebagai pusat semua data, dengan setup_step untuk Setup Awal Owner
- **POS** (10 tabel) — orders sebagai pusat transaksi, dengan hold/kasbon/print_count/voucher
- **Kas Keluar/Masuk** (1 tabel) — cash_movements immutable
- **Produk & Inventori** (16 tabel) — products, modifiers, suppliers, PO, stock_movements
- **CRM & Shift** (3 tabel) — customers (dengan credit_limit/credit_balance), loyalty_points, shift_reports
- **Settings** (7 tabel) — termasuk cashier_settings, ewallet_settings, notification_preferences
- **Promosi** (3 tabel) — vouchers, voucher_redemptions, scheduled_discounts
- **Refund Parsial** (1 tabel) — refund_items
- **Langganan** (3 tabel) — subscriptions dengan generated column untuk 1 active per tenant
- **Compliance** (6 tabel) — audit, notifications, cookie, export, deletion, retention
- **Production [PROD]** (6 tabel) — releases, gates, deployments, cutover, rollback

**Konvensi:** InnoDB, utf8mb4_unicode_ci, DECIMAL untuk uang, VARCHAR untuk status, UUID CHAR(36) untuk tabel tertentu, FK eksplisit, index wajib, CHECK constraints, generated columns.

**Environment database:**
- Local: Docker Compose MySQL 8.4
- Staging: TiDB Cloud Serverless (MySQL-compatible, tanpa CC)
- Production: MySQL 8.4 self-managed di VPS Jakarta

**Status:** ✅ Siap eksekusi. 80 tabel, 15 domain, 2 circular dependency, 10 immutable, 27 seeder.
