<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            ['code' => 'pos.transaction', 'name' => 'POS Transaksi', 'description' => 'Pemrosesan transaksi kasir inti.', 'category' => 'pos'],
            ['code' => 'pos.split_bill', 'name' => 'Split Bill', 'description' => 'Pembagian tagihan per item pesanan.', 'category' => 'pos'],
            ['code' => 'pos.void_refund', 'name' => 'Void & Refund', 'description' => 'Pembatalan dan pengembalian dana transaksi.', 'category' => 'pos'],
            ['code' => 'pos.table_management', 'name' => 'Manajemen Meja F&B', 'description' => 'Denah meja, status meja, dan penggabungan meja.', 'category' => 'pos'],
            ['code' => 'pos.credit_sale', 'name' => 'Kasbon / Piutang', 'description' => 'Penjualan kredit dengan validasi limit pelanggan.', 'category' => 'pos'],
            ['code' => 'invoice.digital', 'name' => 'Invoice Digital', 'description' => 'Pembuatan dan pengiriman invoice B2B/digital.', 'category' => 'pos'],
            ['code' => 'report.basic', 'name' => 'Laporan Dasar', 'description' => 'Ringkasan penjualan harian dan metode bayar.', 'category' => 'report'],
            ['code' => 'report.advanced', 'name' => 'Laporan Lanjutan', 'description' => 'Analitik margin, tren, dan perbandingan mendalam.', 'category' => 'report'],
            ['code' => 'outlet.multi', 'name' => 'Multi-Outlet', 'description' => 'Pengelolaan banyak cabang dan transfer stok antar-outlet.', 'category' => 'outlet'],
            ['code' => 'export.csv', 'name' => 'Export CSV', 'description' => 'Ekspor data transaksi dan kepatuhan UU PDP.', 'category' => 'export'],
            ['code' => 'payment.custom_method', 'name' => 'Custom Payment Method', 'description' => 'Konfigurasi metode pembayaran tambahan.', 'category' => 'payment'],
            ['code' => 'tax.three_methods', 'name' => 'Pengaturan Pajak 3 Metode', 'description' => 'PPN, PB1, dan pajak layanan fleksibel.', 'category' => 'tax'],
            ['code' => 'service_charge.rounding', 'name' => 'Service Charge & Pembulatan', 'description' => 'Biaya layanan dan pembulatan otomatis struk.', 'category' => 'tax'],
            ['code' => 'receipt.customization', 'name' => 'Kustomisasi Struk', 'description' => 'Logo, header, footer, dan format struk 58mm/80mm.', 'category' => 'receipt'],
            ['code' => 'inventory.tracking', 'name' => 'Inventory Tracking', 'description' => 'Kartu stok immutable, PO, dan supplier.', 'category' => 'inventory'],
            ['code' => 'product.modifiers', 'name' => 'Product Modifiers', 'description' => 'Grup topping, level pedas, dan opsi berbayar.', 'category' => 'inventory'],
            ['code' => 'crm.loyalty', 'name' => 'CRM & Loyalty Point', 'description' => 'Profil pelanggan, perolehan, dan penukaran poin.', 'category' => 'crm'],
            ['code' => 'shift.report', 'name' => 'Shift Report', 'description' => 'Laporan tutup kasir immutable dan selisih kas.', 'category' => 'shift'],
            ['code' => 'promo.voucher', 'name' => 'Promosi Voucher', 'description' => 'Kode promo diskon persentase maupun nominal.', 'category' => 'promo'],
            ['code' => 'promo.scheduled', 'name' => 'Promosi Diskon Terjadwal', 'description' => 'Diskon otomatis per jam/hari/kategori.', 'category' => 'promo'],
            ['code' => 'cashier.advanced_settings', 'name' => 'Pengaturan Kasir Lanjutan', 'description' => 'Kontrol izin void, refund, diskon, dan kas kasir.', 'category' => 'pos'],
        ];

        foreach ($features as $feature) {
            Feature::query()->updateOrCreate(
                ['code' => $feature['code']],
                $feature
            );
        }
    }
}
