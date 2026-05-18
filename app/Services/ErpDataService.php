<?php

namespace App\Services;

class ErpDataService
{
    public static function getData(): array
    {
        return [
            'kpis'         => static::kpis(),
            'klien'        => static::klien(),
            'pipeline'     => static::pipeline(),
            'monthly'      => static::monthly(),
            'salesOrders'  => static::salesOrders(),
            'produk'       => static::produk(),
            'soDetailItems' => static::soDetailItems(),
            'kontak'       => static::kontak(),
            'akunKas'      => static::akunKas(),
            'chartOfAccounts' => static::chartOfAccounts(),
            'gudang'       => static::gudang(),
            'transaksiKas' => static::transaksiKas(),
            'jurnal'       => static::jurnal(),
            'dataCoverage' => static::dataCoverage(),
            'purchaseOrders'   => static::purchaseOrders(),
            'poDetailItems'    => static::poDetailItems(),
        ];
    }

    public static function kpis(): array
    {
        return [
            'penjualan' => ['value' => 4_287_650_000, 'delta' => 12.4, 'sparkline' => [12, 14, 13, 18, 17, 22, 21, 25, 24, 28, 30, 33]],
            'pembelian' => ['value' => 3_412_900_000, 'delta' =>  8.1, 'sparkline' => [10, 11, 12, 13, 15, 14, 17, 18, 19, 21, 22, 24]],
            'margin'    => ['value' => 20.4,           'delta' =>  1.6, 'sparkline' => [16, 17, 16, 18, 17, 19, 18, 20, 19, 20, 20, 21]],
            'stok'      => ['value' => 1_872_400_000, 'delta' => -3.2, 'sparkline' => [22, 23, 21, 22, 20, 21, 19, 20, 19, 18, 17, 18]],
        ];
    }

    public static function klien(): array
    {
        return [
            ['id' => 'K-008', 'nama' => 'PT Roti Sumber Rejeki',          'kota' => 'Bandung',  'jenis' => 'Customer', 'omzet' => 612_400_000,   'status' => 'sehat',  'piutang' => 84_200_000,   'transaksi' => 24],
            ['id' => 'K-014', 'nama' => 'CV Mie Mas Joko',                'kota' => 'Semarang', 'jenis' => 'Customer', 'omzet' => 488_000_000,   'status' => 'sehat',  'piutang' => 46_700_000,   'transaksi' => 19],
            ['id' => 'V-021', 'nama' => 'PT Bogasari Flour Mills',         'kota' => 'Jakarta',  'jenis' => 'Vendor',  'omzet' => 1_204_000_000, 'status' => 'sehat',  'piutang' => -180_000_000, 'transaksi' => 36],
            ['id' => 'K-031', 'nama' => 'Toko Bahan Kue Anggrek',          'kota' => 'Surabaya', 'jenis' => 'Customer', 'omzet' => 142_300_000,   'status' => 'review', 'piutang' => 62_800_000,   'transaksi' => 11],
            ['id' => 'V-009', 'nama' => 'CV Gula Manis Lestari',           'kota' => 'Lampung',  'jenis' => 'Vendor',  'omzet' => 890_500_000,   'status' => 'sehat',  'piutang' => -112_000_000, 'transaksi' => 28],
            ['id' => 'K-042', 'nama' => 'Warung Padang Sederhana',         'kota' => 'Padang',   'jenis' => 'Customer', 'omzet' => 74_900_000,    'status' => 'kritis', 'piutang' => 41_500_000,   'transaksi' => 6],
            ['id' => 'K-018', 'nama' => 'PT Catering Selera Nusantara',    'kota' => 'Tangerang', 'jenis' => 'Customer', 'omzet' => 318_600_000,   'status' => 'sehat',  'piutang' => 24_300_000,   'transaksi' => 17],
            ['id' => 'V-016', 'nama' => 'PT Salim Ivomas (Minyak)',         'kota' => 'Jakarta',  'jenis' => 'Vendor',  'omzet' => 1_478_200_000, 'status' => 'review', 'piutang' => -204_500_000, 'transaksi' => 31],
        ];
    }

    public static function pipeline(): array
    {
        return [
            ['stage' => 'Sales Order',    'count' => 28, 'value' => 1_420_000_000, 'color' => 'oklch(0.85 0.06 60)'],
            ['stage' => 'Pengiriman',     'count' => 19, 'value' => 980_400_000,  'color' => 'oklch(0.78 0.10 50)'],
            ['stage' => 'Tagihan Terbit', 'count' => 14, 'value' => 742_000_000,  'color' => 'oklch(0.70 0.14 42)'],
            ['stage' => 'Lunas',          'count' => 22, 'value' => 1_145_300_000, 'color' => 'oklch(0.62 0.18 38)'],
        ];
    }

    public static function monthly(): array
    {
        return [
            ['Jun', 312, 268],
            ['Jul', 358, 290],
            ['Agu', 401, 322],
            ['Sep', 384, 308],
            ['Okt', 426, 351],
            ['Nov', 470, 388],
            ['Des', 512, 410],
            ['Jan', 388, 318],
            ['Feb', 442, 360],
            ['Mar', 498, 402],
            ['Apr', 537, 425],
            ['Mei', 428, 341],
        ];
    }

    public static function salesOrders(): array
    {
        return [
            ['id' => 'SO-2026-0142', 'tanggal' => '06 Mei 2026', 'customer' => 'PT Roti Sumber Rejeki',        'gudang' => 'Gudang Bekasi',    'total' => 84_500_000,  'status' => 'pending', 'jatuhTempo' => '20 Mei 2026'],
            ['id' => 'SO-2026-0141', 'tanggal' => '06 Mei 2026', 'customer' => 'CV Mie Mas Joko',              'gudang' => 'Gudang Bekasi',    'total' => 142_800_000, 'status' => 'dikirim', 'jatuhTempo' => '21 Mei 2026'],
            ['id' => 'SO-2026-0140', 'tanggal' => '05 Mei 2026', 'customer' => 'PT Catering Selera Nusantara', 'gudang' => 'Gudang Tangerang', 'total' => 38_200_000,  'status' => 'tagihan', 'jatuhTempo' => '19 Mei 2026'],
            ['id' => 'SO-2026-0139', 'tanggal' => '05 Mei 2026', 'customer' => 'Toko Bahan Kue Anggrek',       'gudang' => 'Gudang Surabaya',  'total' => 22_750_000,  'status' => 'lunas',   'jatuhTempo' => '18 Mei 2026'],
            ['id' => 'SO-2026-0138', 'tanggal' => '04 Mei 2026', 'customer' => 'Warung Padang Sederhana',      'gudang' => 'Gudang Bekasi',    'total' => 14_900_000,  'status' => 'pending', 'jatuhTempo' => '18 Mei 2026'],
            ['id' => 'SO-2026-0137', 'tanggal' => '04 Mei 2026', 'customer' => 'PT Roti Sumber Rejeki',        'gudang' => 'Gudang Bekasi',    'total' => 96_300_000,  'status' => 'lunas',   'jatuhTempo' => '17 Mei 2026'],
            ['id' => 'SO-2026-0136', 'tanggal' => '03 Mei 2026', 'customer' => 'CV Mie Mas Joko',              'gudang' => 'Gudang Semarang',  'total' => 71_200_000,  'status' => 'dikirim', 'jatuhTempo' => '17 Mei 2026'],
            ['id' => 'SO-2026-0135', 'tanggal' => '03 Mei 2026', 'customer' => 'Bakery Sahabat Sehat',         'gudang' => 'Gudang Bekasi',    'total' => 48_650_000,  'status' => 'lunas',   'jatuhTempo' => '16 Mei 2026'],
            ['id' => 'SO-2026-0134', 'tanggal' => '03 Mei 2026', 'customer' => 'PT Bogasari Flour Mills',      'gudang' => 'Gudang Bekasi',    'total' => 312_000_000, 'status' => 'dikirim', 'jatuhTempo' => '20 Mei 2026'],
            ['id' => 'SO-2026-0133', 'tanggal' => '02 Mei 2026', 'customer' => 'Toko Bahan Kue Anggrek',       'gudang' => 'Gudang Surabaya',  'total' => 18_250_000,  'status' => 'pending', 'jatuhTempo' => '16 Mei 2026'],
            ['id' => 'SO-2026-0132', 'tanggal' => '02 Mei 2026', 'customer' => 'Warung Padang Sederhana',      'gudang' => 'Gudang Padang',    'total' => 7_900_000,   'status' => 'lunas',   'jatuhTempo' => '15 Mei 2026'],
            ['id' => 'SO-2026-0131', 'tanggal' => '02 Mei 2026', 'customer' => 'PT Roti Sumber Rejeki',        'gudang' => 'Gudang Bekasi',    'total' => 56_400_000,  'status' => 'tagihan', 'jatuhTempo' => '18 Mei 2026'],
            ['id' => 'SO-2026-0130', 'tanggal' => '01 Mei 2026', 'customer' => 'CV Mie Mas Joko',              'gudang' => 'Gudang Semarang',  'total' => 23_700_000,  'status' => 'pending', 'jatuhTempo' => '14 Mei 2026'],
            ['id' => 'SO-2026-0129', 'tanggal' => '01 Mei 2026', 'customer' => 'Bakery Sahabat Sehat',         'gudang' => 'Gudang Bekasi',    'total' => 12_650_000,  'status' => 'lunas',   'jatuhTempo' => '13 Mei 2026'],
            ['id' => 'SO-2026-0128', 'tanggal' => '01 Mei 2026', 'customer' => 'PT Catering Selera Nusantara', 'gudang' => 'Gudang Tangerang', 'total' => 44_800_000,  'status' => 'dikirim', 'jatuhTempo' => '19 Mei 2026'],
            ['id' => 'SO-2026-0127', 'tanggal' => '30 Apr 2026', 'customer' => 'Toko Bahan Kue Anggrek',       'gudang' => 'Gudang Surabaya',  'total' => 9_350_000,   'status' => 'pending', 'jatuhTempo' => '14 Mei 2026'],
            ['id' => 'SO-2026-0126', 'tanggal' => '30 Apr 2026', 'customer' => 'CV Gula Manis Lestari',         'gudang' => 'Gudang Bekasi',    'total' => 64_200_000,  'status' => 'tagihan', 'jatuhTempo' => '18 Mei 2026'],
            ['id' => 'SO-2026-0125', 'tanggal' => '30 Apr 2026', 'customer' => 'PT Salim Ivomas',              'gudang' => 'Gudang Tangerang', 'total' => 198_400_000, 'status' => 'dikirim', 'jatuhTempo' => '21 Mei 2026'],
            ['id' => 'SO-2026-0124', 'tanggal' => '29 Apr 2026', 'customer' => 'UD Beras Sumber Tani',         'gudang' => 'Gudang Surabaya',  'total' => 87_200_000,  'status' => 'lunas',   'jatuhTempo' => '17 May 2026'],
            ['id' => 'SO-2026-0123', 'tanggal' => '29 Apr 2026', 'customer' => 'PT Bogasari Flour Mills',      'gudang' => 'Gudang Bekasi',    'total' => 124_800_000, 'status' => 'lunas',   'jatuhTempo' => '16 Mei 2026'],
            ['id' => 'SO-2026-0122', 'tanggal' => '29 Apr 2026', 'customer' => 'Warung Padang Sederhana',      'gudang' => 'Gudang Padang',    'total' => 6_450_000,   'status' => 'pending', 'jatuhTempo' => '12 Mei 2026'],
            ['id' => 'SO-2026-0121', 'tanggal' => '28 Apr 2026', 'customer' => 'PT Roti Sumber Rejeki',        'gudang' => 'Gudang Bekasi',    'total' => 72_900_000,  'status' => 'tagihan', 'jatuhTempo' => '15 Mei 2026'],
            ['id' => 'SO-2026-0120', 'tanggal' => '28 Apr 2026', 'customer' => 'CV Mie Mas Joko',              'gudang' => 'Gudang Semarang',  'total' => 33_200_000,  'status' => 'dikirim', 'jatuhTempo' => '18 Mei 2026'],
            ['id' => 'SO-2026-0119', 'tanggal' => '28 Apr 2026', 'customer' => 'Bakery Sahabat Sehat',         'gudang' => 'Gudang Bekasi',    'total' => 28_450_000,  'status' => 'lunas',   'jatuhTempo' => '14 Mei 2026'],
            ['id' => 'SO-2026-0118', 'tanggal' => '27 Apr 2026', 'customer' => 'PT Catering Selera Nusantara', 'gudang' => 'Gudang Tangerang', 'total' => 51_300_000,  'status' => 'pending', 'jatuhTempo' => '13 Mei 2026'],
            ['id' => 'SO-2026-0117', 'tanggal' => '27 Apr 2026', 'customer' => 'Toko Bahan Kue Anggrek',       'gudang' => 'Gudang Surabaya',  'total' => 16_900_000,  'status' => 'dikirim', 'jatuhTempo' => '12 Mei 2026'],
            ['id' => 'SO-2026-0116', 'tanggal' => '26 Apr 2026', 'customer' => 'CV Gula Manis Lestari',         'gudang' => 'Gudang Bekasi',    'total' => 72_000_000,  'status' => 'tagihan', 'jatuhTempo' => '11 Mei 2026'],
            ['id' => 'SO-2026-0115', 'tanggal' => '26 Apr 2026', 'customer' => 'PT Salim Ivomas',              'gudang' => 'Gudang Tangerang', 'total' => 142_600_000, 'status' => 'lunas',   'jatuhTempo' => '10 Mei 2026'],
            ['id' => 'SO-2026-0114', 'tanggal' => '25 Apr 2026', 'customer' => 'UD Beras Sumber Tani',         'gudang' => 'Gudang Surabaya',  'total' => 39_800_000,  'status' => 'pending', 'jatuhTempo' => '09 Mei 2026'],
            ['id' => 'SO-2026-0113', 'tanggal' => '25 Apr 2026', 'customer' => 'PT Bogasari Flour Mills',      'gudang' => 'Gudang Bekasi',    'total' => 256_400_000, 'status' => 'dikirim', 'jatuhTempo' => '14 Mei 2026'],
            ['id' => 'SO-2026-0112', 'tanggal' => '24 Apr 2026', 'customer' => 'Warung Padang Sederhana',      'gudang' => 'Gudang Padang',    'total' => 5_200_000,   'status' => 'lunas',   'jatuhTempo' => '08 Mei 2026'],
            ['id' => 'SO-2026-0111', 'tanggal' => '24 Apr 2026', 'customer' => 'PT Roti Sumber Rejeki',        'gudang' => 'Gudang Bekasi',    'total' => 66_700_000,  'status' => 'pending', 'jatuhTempo' => '12 Mei 2026'],
            ['id' => 'SO-2026-0110', 'tanggal' => '23 Apr 2026', 'customer' => 'CV Mie Mas Joko',              'gudang' => 'Gudang Semarang',  'total' => 21_500_000,  'status' => 'tagihan', 'jatuhTempo' => '07 Mei 2026'],
        ];
    }

    public static function produk(): array
    {
        return [
            ['kode' => 'TPG-001', 'nama' => 'Tepung Terigu Cakra Kembar',  'kategori' => 'Tepung',  'satuan' => 'Kg',    'hargaBeli' => 188_000,   'hargaJual' => 215_000,   'stok' => 1240, 'gudang' => 'Bekasi'],
            ['kode' => 'GLP-002', 'nama' => 'Gula Pasir Kemasan Premium',  'kategori' => 'Gula',    'satuan' => 'Kg',    'hargaBeli' => 612_000,   'hargaJual' => 678_000,   'stok' => 420, 'gudang' => 'Bekasi'],
            ['kode' => 'MNY-003', 'nama' => 'Minyak Goreng Kelapa Sawit',  'kategori' => 'Minyak',  'satuan' => 'Kg', 'hargaBeli' => 248_000,   'hargaJual' => 282_000,   'stok' => 860, 'gudang' => 'Tangerang'],
            ['kode' => 'BRS-004', 'nama' => 'Beras Premium IR-64',          'kategori' => 'Beras',   'satuan' => 'Kg', 'hargaBeli' => 312_000,   'hargaJual' => 358_000,   'stok' => 680, 'gudang' => 'Surabaya'],
            ['kode' => 'GRM-005', 'nama' => 'Garam Halus Konsumsi',         'kategori' => 'Bumbu',   'satuan' => 'Kg',    'hargaBeli' => 62_000,    'hargaJual' => 78_000,    'stok' => 1620, 'gudang' => 'Bekasi'],
            ['kode' => 'KLP-006', 'nama' => 'Tepung Kelapa Halus',          'kategori' => 'Tepung',  'satuan' => 'Kg', 'hargaBeli' => 142_000,   'hargaJual' => 168_000,   'stok' => 340, 'gudang' => 'Tangerang'],
            ['kode' => 'KCP-007', 'nama' => 'Kecap Asin Curah',             'kategori' => 'Bumbu',   'satuan' => 'Kg',   'hargaBeli' => 1_280_000, 'hargaJual' => 1_440_000, 'stok' => 38,  'gudang' => 'Bekasi'],
            ['kode' => 'BWP-008', 'nama' => 'Bawang Putih Kating',          'kategori' => 'Bumbu',   'satuan' => 'Kg', 'hargaBeli' => 92_000,    'hargaJual' => 118_000,   'stok' => 216, 'gudang' => 'Surabaya'],
            ['kode' => 'CBI-009', 'nama' => 'Cabai Kering Giling',          'kategori' => 'Bumbu',   'satuan' => 'Kg',             'hargaBeli' => 78_000,    'hargaJual' => 96_000,    'stok' => 124, 'gudang' => 'Surabaya'],
            ['kode' => 'KCG-010', 'nama' => 'Kacang Tanah Kupas',           'kategori' => 'Kacang',  'satuan' => 'Kg', 'hargaBeli' => 612_000,   'hargaJual' => 692_000,   'stok' => 72,  'gudang' => 'Tangerang'],
        ];
    }

    public static function soDetailItems(): array
    {
        return [
            ['kode' => 'TPG-001', 'nama' => 'Tepung Terigu Cakra Kembar',  'qty' => 120, 'satuan' => 'Kg',    'harga' => 215_000],
            ['kode' => 'GLP-002', 'nama' => 'Gula Pasir Kemasan Premium',  'qty' => 40, 'satuan' => 'Kg',    'harga' => 678_000],
            ['kode' => 'MNY-003', 'nama' => 'Minyak Goreng Kelapa Sawit',  'qty' => 80, 'satuan' => 'Kg', 'harga' => 282_000],
        ];
    }

    public static function kontak(): array
    {
        return [
            ['id' => 'C-001', 'nama' => 'PT Roti Sumber Rejeki',        'tipe' => 'Customer', 'telepon' => '(022) 720-3441', 'email' => 'finance@sumberrejeki.id',  'kota' => 'Bandung', 'hutang' => 0,           'piutang' => 84_200_000],
            ['id' => 'C-002', 'nama' => 'CV Mie Mas Joko',              'tipe' => 'Customer', 'telepon' => '(024) 855-2210', 'email' => 'order@miemasjoko.com',      'kota' => 'Semarang', 'hutang' => 0,           'piutang' => 46_700_000],
            ['id' => 'V-001', 'nama' => 'PT Bogasari Flour Mills',       'tipe' => 'Vendor',  'telepon' => '(021) 4392-1234', 'email' => 'sales@bogasari.co.id',     'kota' => 'Jakarta', 'hutang' => 180_000_000, 'piutang' => 0],
            ['id' => 'V-002', 'nama' => 'CV Gula Manis Lestari',         'tipe' => 'Vendor',  'telepon' => '(0721) 770-8881', 'email' => 'admin@gmlestari.id',        'kota' => 'Lampung', 'hutang' => 112_000_000, 'piutang' => 0],
            ['id' => 'C-003', 'nama' => 'Toko Bahan Kue Anggrek',        'tipe' => 'Customer', 'telepon' => '(031) 503-4421', 'email' => 'tokoanggrek@gmail.com',     'kota' => 'Surabaya', 'hutang' => 0,           'piutang' => 62_800_000],
            ['id' => 'C-004', 'nama' => 'PT Catering Selera Nusantara',  'tipe' => 'Customer', 'telepon' => '(021) 5594-6711', 'email' => 'po@cateringselera.id',      'kota' => 'Tangerang', 'hutang' => 0,          'piutang' => 24_300_000],
            ['id' => 'V-003', 'nama' => 'PT Salim Ivomas',                'tipe' => 'Vendor',  'telepon' => '(021) 5778-9001', 'email' => 'b2b@salim-ivomas.co.id',   'kota' => 'Jakarta', 'hutang' => 204_500_000, 'piutang' => 0],
        ];
    }

    public static function akunKas(): array
    {
        return [
            ['id' => 'KB-001', 'nama' => 'BCA - Operasional', 'bank' => 'BCA',    'norek' => '8821-xxx-xxx', 'jenis' => 'Bank', 'saldo' => 1_482_300_000, 'tipe' => 'IDR', 'transaksi' => 42],
            ['id' => 'KB-002', 'nama' => 'Mandiri - Penjualan', 'bank' => 'Mandiri', 'norek' => '5601-xxx-xxx', 'jenis' => 'Bank', 'saldo' => 862_900_000, 'tipe' => 'IDR', 'transaksi' => 31],
            ['id' => 'KB-003', 'nama' => 'BNI - Pembelian',   'bank' => 'BNI',    'norek' => '9034-xxx-xxx', 'jenis' => 'Bank', 'saldo' => 418_600_000, 'tipe' => 'IDR', 'transaksi' => 18],
            ['id' => 'KB-004', 'nama' => 'Kas Kantor Pusat',  'bank' => 'Kas',    'norek' => '',            'jenis' => 'Kas', 'saldo' => 24_750_000,  'tipe' => 'IDR', 'transaksi' => 9],
            ['id' => 'KB-005', 'nama' => 'Kas Gudang Bekasi', 'bank' => 'Kas',    'norek' => '',            'jenis' => 'Kas', 'saldo' => 11_400_000,  'tipe' => 'IDR', 'transaksi' => 6],
            ['id' => 'KB-006', 'nama' => 'Petty Cash Marketing', 'bank' => 'Kas',  'norek' => '',            'jenis' => 'Kas', 'saldo' => 4_830_000,   'tipe' => 'IDR', 'transaksi' => 4],
        ];
    }

    public static function chartOfAccounts(): array
    {
        return [
            ['kode' => '1-1000', 'nama' => 'Aset Lancar',              'kategori' => 'Aset',     'parent' => null,    'saldo' => 3_842_000_000],
            ['kode' => '1-1100', 'nama' => 'Kas & Bank',               'kategori' => 'Aset',     'parent' => '1-1000', 'saldo' => 2_804_780_000],
            ['kode' => '1-1200', 'nama' => 'Piutang Usaha',            'kategori' => 'Aset',     'parent' => '1-1000', 'saldo' => 258_500_000],
            ['kode' => '1-1300', 'nama' => 'Persediaan Barang',        'kategori' => 'Aset',     'parent' => '1-1000', 'saldo' => 1_872_400_000],
            ['kode' => '2-1000', 'nama' => 'Kewajiban Lancar',         'kategori' => 'Kewajiban', 'parent' => null,    'saldo' => 496_500_000],
            ['kode' => '2-1100', 'nama' => 'Hutang Usaha',             'kategori' => 'Kewajiban', 'parent' => '2-1000', 'saldo' => 496_500_000],
            ['kode' => '3-1000', 'nama' => 'Modal',                    'kategori' => 'Ekuitas',  'parent' => null,    'saldo' => 3_500_000_000],
            ['kode' => '4-1000', 'nama' => 'Pendapatan Penjualan',     'kategori' => 'Pendapatan', 'parent' => null,   'saldo' => 4_287_650_000],
            ['kode' => '5-1000', 'nama' => 'Harga Pokok Penjualan',    'kategori' => 'Beban',    'parent' => null,    'saldo' => 3_412_900_000],
            ['kode' => '6-1000', 'nama' => 'Beban Operasional',        'kategori' => 'Beban',    'parent' => null,    'saldo' => 218_400_000],
            ['kode' => '6-1100', 'nama' => 'Beban Gaji & Tunjangan',   'kategori' => 'Beban',    'parent' => '6-1000', 'saldo' => 142_800_000],
            ['kode' => '6-1200', 'nama' => 'Beban Sewa Gudang',        'kategori' => 'Beban',    'parent' => '6-1000', 'saldo' => 54_000_000],
            ['kode' => '6-1300', 'nama' => 'Beban Listrik & Air',      'kategori' => 'Beban',    'parent' => '6-1000', 'saldo' => 21_600_000],
        ];
    }

    public static function gudang(): array
    {
        return [
            ['kode' => 'GDG-BKS', 'nama' => 'Gudang Bekasi',    'kota' => 'Bekasi',    'alamat' => 'Jl. Cibitung Raya No. 88, Bekasi',      'PIC' => 'Budi Santoso',    'kapasitas' => 5000, 'stok' => 1_120_000_000, 'sku' => 82, 'ratHpp' => 178_500],
            ['kode' => 'GDG-TGR', 'nama' => 'Gudang Tangerang', 'kota' => 'Tangerang', 'alamat' => 'Kawasan Industri Cikupa Blok F12',       'PIC' => 'Rini Widyastuti', 'kapasitas' => 3000, 'stok' => 486_400_000,  'sku' => 47, 'ratHpp' => 264_300],
            ['kode' => 'GDG-SBY', 'nama' => 'Gudang Surabaya',  'kota' => 'Surabaya',  'alamat' => 'Jl. Margomulyo Indah No. 22, Surabaya', 'PIC' => 'Arif Kurniawan',  'kapasitas' => 2000, 'stok' => 218_600_000,  'sku' => 31, 'ratHpp' => 142_800],
            ['kode' => 'GDG-SMG', 'nama' => 'Gudang Semarang',  'kota' => 'Semarang',  'alamat' => 'Jl. Yos Sudarso KM 10, Semarang',       'PIC' => 'Siti Rahayu',     'kapasitas' => 1500, 'stok' => 47_400_000,   'sku' => 18, 'ratHpp' => 94_700],
        ];
    }

    public static function transaksiKas(): array
    {
        return [
            ['tanggal' => '06 Mei 2026', 'akun' => 'BCA Operasional', 'keterangan' => 'Pelunasan SO-2026-0137 – PT Roti Sumber Rejeki',    'masuk' => 96_300_000, 'keluar' => 0,           'saldo' => 1_482_300_000, 'ref' => 'SO-0137'],
            ['tanggal' => '06 Mei 2026', 'akun' => 'BNI Pembelian',  'keterangan' => 'Pembayaran PO-2026-0091 – PT Bogasari Flour Mills', 'masuk' => 0,          'keluar' => 124_800_000, 'saldo' => 418_600_000,  'ref' => 'PO-0091'],
            ['tanggal' => '05 Mei 2026', 'akun' => 'BCA Operasional', 'keterangan' => 'Transfer ke Mandiri Penjualan',                     'masuk' => 0,          'keluar' => 50_000_000,  'saldo' => 1_386_000_000, 'ref' => 'TRF-0021'],
            ['tanggal' => '05 Mei 2026', 'akun' => 'BCA Operasional', 'keterangan' => 'Pelunasan SO-2026-0135 – Bakery Sahabat Sehat',     'masuk' => 48_650_000, 'keluar' => 0,           'saldo' => 1_436_000_000, 'ref' => 'SO-0135'],
            ['tanggal' => '04 Mei 2026', 'akun' => 'BCA Operasional', 'keterangan' => 'Bayar listrik gudang Bekasi April',                 'masuk' => 0,          'keluar' => 4_280_000,   'saldo' => 1_387_350_000, 'ref' => 'EXP-0042'],
            ['tanggal' => '04 Mei 2026', 'akun' => 'Mandiri Penjualan', 'keterangan' => 'Pelunasan parsial SO-2026-0132 – CV Mie Mas Joko', 'masuk' => 35_000_000, 'keluar' => 0,           'saldo' => 862_900_000,  'ref' => 'SO-0132'],
            ['tanggal' => '03 Mei 2026', 'akun' => 'Kas Kantor',     'keterangan' => 'Setoran tunai kas operasional ke BCA',              'masuk' => 18_400_000, 'keluar' => 0,           'saldo' => 24_750_000,   'ref' => 'TRF-0020'],
        ];
    }

    public static function jurnal(): array
    {
        return [
            [
                'tanggal'    => '06 Mei 2026',
                'noJurnal'   => 'JV-2026-0481',
                'keterangan' => 'Pelunasan SO-2026-0137 – PT Roti Sumber Rejeki',
                'entri'      => [
                    ['akun' => 'Kas & Bank – BCA Operasional', 'posisi' => 'debit',  'jumlah' => 96_300_000],
                    ['akun' => 'Piutang Usaha',                 'posisi' => 'kredit', 'jumlah' => 96_300_000],
                ],
            ],
            [
                'tanggal'    => '06 Mei 2026',
                'noJurnal'   => 'JV-2026-0480',
                'keterangan' => 'Pembayaran PO-2026-0091 – PT Bogasari Flour Mills',
                'entri'      => [
                    ['akun' => 'Hutang Usaha',                  'posisi' => 'debit',  'jumlah' => 124_800_000],
                    ['akun' => 'Kas & Bank – BNI Pembelian',    'posisi' => 'kredit', 'jumlah' => 124_800_000],
                ],
            ],
            [
                'tanggal'    => '05 Mei 2026',
                'noJurnal'   => 'JV-2026-0479',
                'keterangan' => 'Transfer antar bank – BCA ke Mandiri',
                'entri'      => [
                    ['akun' => 'Kas & Bank – Mandiri Penjualan', 'posisi' => 'debit',  'jumlah' => 50_000_000],
                    ['akun' => 'Kas & Bank – BCA Operasional',  'posisi' => 'kredit', 'jumlah' => 50_000_000],
                ],
            ],
            [
                'tanggal'    => '05 Mei 2026',
                'noJurnal'   => 'JV-2026-0478',
                'keterangan' => 'Pelunasan SO-2026-0135 – Bakery Sahabat Sehat',
                'entri'      => [
                    ['akun' => 'Kas & Bank – BCA Operasional',  'posisi' => 'debit',  'jumlah' => 48_650_000],
                    ['akun' => 'Piutang Usaha',                  'posisi' => 'kredit', 'jumlah' => 48_650_000],
                ],
            ],
            [
                'tanggal'    => '04 Mei 2026',
                'noJurnal'   => 'JV-2026-0477',
                'keterangan' => 'Bayar listrik gudang Bekasi April',
                'entri'      => [
                    ['akun' => 'Beban Listrik & Air',            'posisi' => 'debit',  'jumlah' => 4_280_000],
                    ['akun' => 'Kas & Bank – BCA Operasional',   'posisi' => 'kredit', 'jumlah' => 4_280_000],
                ],
            ],
        ];
    }

    public static function dataCoverage(): array
    {
        return [
            ['label' => 'Master Produk', 'pct' => 96, 'items' => '82 / 86'],
            ['label' => 'Master Kontak', 'pct' => 88, 'items' => '184 / 209'],
            ['label' => 'Harga Pokok', 'pct' => 74, 'items' => '64 / 86'],
            ['label' => 'Foto Produk', 'pct' => 41, 'items' => '36 / 86'],
        ];
    }

    public static function purchaseOrders(): array
    {
        return [
            ['id' => 'PO-2026-0094', 'tanggal' => '07 Mei 2026', 'vendor' => 'PT Bogasari Flour Mills',   'gudang' => 'Gudang Bekasi',   'total' => 376_000_000, 'status' => 'pending', 'jatuhTempo' => '21 Mei 2026'],
            ['id' => 'PO-2026-0093', 'tanggal' => '07 Mei 2026', 'vendor' => 'CV Gula Manis Lestari',     'gudang' => 'Gudang Bekasi',   'total' => 244_800_000, 'status' => 'dikirim', 'jatuhTempo' => '21 Mei 2026'],
            ['id' => 'PO-2026-0092', 'tanggal' => '06 Mei 2026', 'vendor' => 'PT Salim Ivomas',            'gudang' => 'Gudang Tangerang', 'total' => 198_400_000, 'status' => 'tagihan', 'jatuhTempo' => '20 Mei 2026'],
            ['id' => 'PO-2026-0091', 'tanggal' => '05 Mei 2026', 'vendor' => 'PT Bogasari Flour Mills',   'gudang' => 'Gudang Bekasi',   'total' => 124_800_000, 'status' => 'lunas',   'jatuhTempo' => '19 Mei 2026'],
            ['id' => 'PO-2026-0090', 'tanggal' => '05 Mei 2026', 'vendor' => 'UD Beras Sumber Tani',       'gudang' => 'Gudang Surabaya', 'total' => 87_200_000, 'status' => 'dikirim', 'jatuhTempo' => '19 Mei 2026'],
            ['id' => 'PO-2026-0089', 'tanggal' => '04 Mei 2026', 'vendor' => 'CV Garam Madura Sejahtera',  'gudang' => 'Gudang Bekasi',   'total' => 62_400_000, 'status' => 'lunas',   'jatuhTempo' => '18 Mei 2026'],
            ['id' => 'PO-2026-0088', 'tanggal' => '03 Mei 2026', 'vendor' => 'PT Salim Ivomas',            'gudang' => 'Gudang Tangerang', 'total' => 142_600_000, 'status' => 'lunas',   'jatuhTempo' => '17 Mei 2026'],
        ];
    }

    public static function poDetailItems(): array
    {
        return [
            ['kode' => 'TPG-001', 'nama' => 'Tepung Terigu Cakra Kembar',  'qty' => 200, 'qtyDiterima' => 198, 'susut' => 2, 'satuan' => 'Kg', 'harga' => 188_000],
            ['kode' => 'TPG-002', 'nama' => 'Tepung Terigu Segitiga Biru', 'qty' => 120, 'qtyDiterima' => 120, 'susut' => 0, 'satuan' => 'Kg', 'harga' => 172_000],
        ];
    }

    public static function penyusutanPembelian(): array
    {
        return [
            ['id' => 'SUT-2026-0018', 'tanggal' => '07 Mei 2026', 'poRef' => 'PO-2026-0090', 'vendor' => 'UD Beras Sumber Tani',      'produk' => 'Beras Premium IR-64',         'qtySusut' => 3,  'satuan' => 'Kg', 'hargaSatuan' => 312_000, 'nilaiSusut' => 936_000,   'keterangan' => 'Kerusakan kemasan',         'status' => 'selesai'],
            ['id' => 'SUT-2026-0017', 'tanggal' => '06 Mei 2026', 'poRef' => 'PO-2026-0092', 'vendor' => 'PT Salim Ivomas',            'produk' => 'Minyak Goreng Kelapa Sawit',  'qtySusut' => 8,  'satuan' => 'Kg', 'hargaSatuan' => 248_000, 'nilaiSusut' => 1_984_000, 'keterangan' => 'Tumpah saat bongkar muat', 'status' => 'pending'],
            ['id' => 'SUT-2026-0016', 'tanggal' => '04 Mei 2026', 'poRef' => 'PO-2026-0089', 'vendor' => 'CV Garam Madura Sejahtera',  'produk' => 'Garam Halus Konsumsi',        'qtySusut' => 15, 'satuan' => 'Kg', 'hargaSatuan' => 62_000,  'nilaiSusut' => 930_000,   'keterangan' => 'Kemasan bocor',             'status' => 'selesai'],
            ['id' => 'SUT-2026-0015', 'tanggal' => '03 Mei 2026', 'poRef' => 'PO-2026-0088', 'vendor' => 'PT Salim Ivomas',            'produk' => 'Minyak Goreng Kelapa Sawit',  'qtySusut' => 5,  'satuan' => 'Kg', 'hargaSatuan' => 248_000, 'nilaiSusut' => 1_240_000, 'keterangan' => 'Kondisi tidak layak',       'status' => 'selesai'],
            ['id' => 'SUT-2026-0014', 'tanggal' => '30 Apr 2026', 'poRef' => 'PO-2026-0085', 'vendor' => 'PT Bogasari Flour Mills',    'produk' => 'Tepung Terigu Cakra Kembar',  'qtySusut' => 2,  'satuan' => 'Kg', 'hargaSatuan' => 188_000, 'nilaiSusut' => 376_000,   'keterangan' => 'Sobek kemasan',             'status' => 'selesai'],
            ['id' => 'SUT-2026-0013', 'tanggal' => '28 Apr 2026', 'poRef' => 'PO-2026-0083', 'vendor' => 'CV Gula Manis Lestari',      'produk' => 'Gula Pasir Kemasan Premium',  'qtySusut' => 6,  'satuan' => 'Kg', 'hargaSatuan' => 612_000, 'nilaiSusut' => 3_672_000, 'keterangan' => 'Terkena air hujan',         'status' => 'selesai'],
            ['id' => 'SUT-2026-0012', 'tanggal' => '25 Apr 2026', 'poRef' => 'PO-2026-0081', 'vendor' => 'UD Beras Sumber Tani',      'produk' => 'Beras Premium IR-64',         'qtySusut' => 10, 'satuan' => 'Kg', 'hargaSatuan' => 312_000, 'nilaiSusut' => 3_120_000, 'keterangan' => 'Tumpah saat bongkar muat', 'status' => 'pending'],
            ['id' => 'SUT-2026-0011', 'tanggal' => '22 Apr 2026', 'poRef' => 'PO-2026-0079', 'vendor' => 'PT Bogasari Flour Mills',    'produk' => 'Tepung Terigu Segitiga Biru', 'qtySusut' => 4,  'satuan' => 'Kg', 'hargaSatuan' => 172_000, 'nilaiSusut' => 688_000,   'keterangan' => 'Kemasan basah',             'status' => 'selesai'],
        ];
    }

    public static function tagihanPembelian(): array
    {
        return [
            ['id' => 'BILL-2026-0024', 'tanggal' => '06 Mei 2026', 'poRef' => 'PO-2026-0092', 'vendor' => 'PT Salim Ivomas',           'jatuhTempo' => '20 Mei 2026', 'total' => 198_400_000, 'status' => 'tagihan'],
            ['id' => 'BILL-2026-0023', 'tanggal' => '05 Mei 2026', 'poRef' => 'PO-2026-0094', 'vendor' => 'PT Bogasari Flour Mills',   'jatuhTempo' => '21 Mei 2026', 'total' => 376_000_000, 'status' => 'tagihan'],
            ['id' => 'BILL-2026-0022', 'tanggal' => '04 Mei 2026', 'poRef' => 'PO-2026-0093', 'vendor' => 'CV Gula Manis Lestari',     'jatuhTempo' => '21 Mei 2026', 'total' => 244_800_000, 'status' => 'tagihan'],
            ['id' => 'BILL-2026-0021', 'tanggal' => '05 Mei 2026', 'poRef' => 'PO-2026-0091', 'vendor' => 'PT Bogasari Flour Mills',   'jatuhTempo' => '19 Mei 2026', 'total' => 124_800_000, 'status' => 'lunas'],
            ['id' => 'BILL-2026-0020', 'tanggal' => '04 Mei 2026', 'poRef' => 'PO-2026-0089', 'vendor' => 'CV Garam Madura Sejahtera', 'jatuhTempo' => '18 Mei 2026', 'total' => 62_400_000,  'status' => 'lunas'],
            ['id' => 'BILL-2026-0019', 'tanggal' => '03 Mei 2026', 'poRef' => 'PO-2026-0088', 'vendor' => 'PT Salim Ivomas',           'jatuhTempo' => '17 Mei 2026', 'total' => 142_600_000, 'status' => 'lunas'],
            ['id' => 'BILL-2026-0018', 'tanggal' => '05 Mei 2026', 'poRef' => 'PO-2026-0090', 'vendor' => 'UD Beras Sumber Tani',     'jatuhTempo' => '19 Mei 2026', 'total' => 87_200_000,  'status' => 'lunas'],
            ['id' => 'BILL-2026-0017', 'tanggal' => '01 Mei 2026', 'poRef' => 'PO-2026-0086', 'vendor' => 'CV Gula Manis Lestari',     'jatuhTempo' => '15 Mei 2026', 'total' => 118_600_000, 'status' => 'tagihan'],
            ['id' => 'BILL-2026-0016', 'tanggal' => '30 Apr 2026', 'poRef' => 'PO-2026-0084', 'vendor' => 'PT Bogasari Flour Mills',   'jatuhTempo' => '14 Mei 2026', 'total' => 204_800_000, 'status' => 'lunas'],
        ];
    }

    public static function pengirimanPenjualan(): array
    {
        return [
            ['id' => 'DO-2026-0089', 'tanggal' => '06 Mei 2026', 'soRef' => 'SO-2026-0141', 'customer' => 'CV Mie Mas Joko',              'gudang' => 'Gudang Bekasi',    'ekspedisi' => 'Internal – Truk Box L300', 'driver' => 'Sutrisno Hadi',  'status' => 'selesai', 'biaya' => 1_800_000],
            ['id' => 'DO-2026-0088', 'tanggal' => '05 Mei 2026', 'soRef' => 'SO-2026-0136', 'customer' => 'CV Mie Mas Joko',              'gudang' => 'Gudang Semarang',  'ekspedisi' => 'JNE Cargo',               'driver' => 'Ahmad Fauzi',    'status' => 'dikirim', 'biaya' => 2_400_000],
            ['id' => 'DO-2026-0087', 'tanggal' => '04 Mei 2026', 'soRef' => 'SO-2026-0134', 'customer' => 'PT Bogasari Flour Mills',      'gudang' => 'Gudang Bekasi',    'ekspedisi' => 'Internal – Truk Box L300', 'driver' => 'Budi Prasetyo', 'status' => 'dikirim', 'biaya' => 3_500_000],
            ['id' => 'DO-2026-0086', 'tanggal' => '03 Mei 2026', 'soRef' => 'SO-2026-0128', 'customer' => 'PT Catering Selera Nusantara', 'gudang' => 'Gudang Tangerang', 'ekspedisi' => 'SiCepat Cargo',           'driver' => 'Rudi Hartono',  'status' => 'selesai', 'biaya' => 950_000],
            ['id' => 'DO-2026-0085', 'tanggal' => '02 Mei 2026', 'soRef' => 'SO-2026-0125', 'customer' => 'PT Salim Ivomas',              'gudang' => 'Gudang Tangerang', 'ekspedisi' => 'Internal – Truk Box L300', 'driver' => 'Sutrisno Hadi', 'status' => 'dikirim', 'biaya' => 4_200_000],
            ['id' => 'DO-2026-0084', 'tanggal' => '01 Mei 2026', 'soRef' => 'SO-2026-0120', 'customer' => 'CV Mie Mas Joko',              'gudang' => 'Gudang Semarang',  'ekspedisi' => 'JNE Cargo',               'driver' => 'Ahmad Fauzi',   'status' => 'selesai', 'biaya' => 1_600_000],
            ['id' => 'DO-2026-0083', 'tanggal' => '30 Apr 2026', 'soRef' => 'SO-2026-0117', 'customer' => 'Toko Bahan Kue Anggrek',       'gudang' => 'Gudang Surabaya',  'ekspedisi' => 'Lion Parcel',             'driver' => 'Eko Susanto',   'status' => 'selesai', 'biaya' => 780_000],
            ['id' => 'DO-2026-0082', 'tanggal' => '29 Apr 2026', 'soRef' => 'SO-2026-0113', 'customer' => 'PT Bogasari Flour Mills',      'gudang' => 'Gudang Bekasi',    'ekspedisi' => 'Internal – Truk Box L300', 'driver' => 'Budi Prasetyo', 'status' => 'selesai', 'biaya' => 3_800_000],
            ['id' => 'DO-2026-0081', 'tanggal' => '28 Apr 2026', 'soRef' => 'SO-2026-0110', 'customer' => 'CV Mie Mas Joko',              'gudang' => 'Gudang Semarang',  'ekspedisi' => 'JNE Cargo',               'driver' => 'Ahmad Fauzi',   'status' => 'pending', 'biaya' => 1_200_000],
            ['id' => 'DO-2026-0080', 'tanggal' => '27 Apr 2026', 'soRef' => 'SO-2026-0108', 'customer' => 'PT Roti Sumber Rejeki',        'gudang' => 'Gudang Bekasi',    'ekspedisi' => 'Internal – Truk Box L300', 'driver' => 'Sutrisno Hadi', 'status' => 'selesai', 'biaya' => 2_100_000],
        ];
    }

    public static function tagihanPenjualan(): array
    {
        return [
            ['id' => 'INV-2026-0042', 'tanggal' => '05 Mei 2026', 'soRef' => 'SO-2026-0140', 'customer' => 'PT Catering Selera Nusantara', 'jatuhTempo' => '19 Mei 2026', 'total' => 38_200_000,  'status' => 'tagihan'],
            ['id' => 'INV-2026-0041', 'tanggal' => '02 Mei 2026', 'soRef' => 'SO-2026-0131', 'customer' => 'PT Roti Sumber Rejeki',        'jatuhTempo' => '18 Mei 2026', 'total' => 56_400_000,  'status' => 'tagihan'],
            ['id' => 'INV-2026-0040', 'tanggal' => '01 Mei 2026', 'soRef' => 'SO-2026-0126', 'customer' => 'CV Gula Manis Lestari',        'jatuhTempo' => '18 Mei 2026', 'total' => 64_200_000,  'status' => 'tagihan'],
            ['id' => 'INV-2026-0039', 'tanggal' => '29 Apr 2026', 'soRef' => 'SO-2026-0121', 'customer' => 'PT Roti Sumber Rejeki',        'jatuhTempo' => '15 Mei 2026', 'total' => 72_900_000,  'status' => 'tagihan'],
            ['id' => 'INV-2026-0038', 'tanggal' => '26 Apr 2026', 'soRef' => 'SO-2026-0116', 'customer' => 'CV Gula Manis Lestari',        'jatuhTempo' => '11 Mei 2026', 'total' => 72_000_000,  'status' => 'lunas'],
            ['id' => 'INV-2026-0037', 'tanggal' => '25 Apr 2026', 'soRef' => 'SO-2026-0110', 'customer' => 'CV Mie Mas Joko',              'jatuhTempo' => '07 Mei 2026', 'total' => 21_500_000,  'status' => 'tagihan'],
            ['id' => 'INV-2026-0036', 'tanggal' => '06 Mei 2026', 'soRef' => 'SO-2026-0139', 'customer' => 'Toko Bahan Kue Anggrek',       'jatuhTempo' => '18 Mei 2026', 'total' => 22_750_000,  'status' => 'lunas'],
            ['id' => 'INV-2026-0035', 'tanggal' => '06 Mei 2026', 'soRef' => 'SO-2026-0137', 'customer' => 'PT Roti Sumber Rejeki',        'jatuhTempo' => '17 Mei 2026', 'total' => 96_300_000,  'status' => 'lunas'],
            ['id' => 'INV-2026-0034', 'tanggal' => '05 Mei 2026', 'soRef' => 'SO-2026-0135', 'customer' => 'Bakery Sahabat Sehat',         'jatuhTempo' => '16 Mei 2026', 'total' => 48_650_000,  'status' => 'lunas'],
            ['id' => 'INV-2026-0033', 'tanggal' => '03 Mei 2026', 'soRef' => 'SO-2026-0132', 'customer' => 'Warung Padang Sederhana',      'jatuhTempo' => '15 Mei 2026', 'total' => 7_900_000,   'status' => 'lunas'],
            ['id' => 'INV-2026-0032', 'tanggal' => '02 Mei 2026', 'soRef' => 'SO-2026-0129', 'customer' => 'Bakery Sahabat Sehat',         'jatuhTempo' => '13 Mei 2026', 'total' => 12_650_000,  'status' => 'lunas'],
            ['id' => 'INV-2026-0031', 'tanggal' => '01 Mei 2026', 'soRef' => 'SO-2026-0124', 'customer' => 'UD Beras Sumber Tani',         'jatuhTempo' => '17 Mei 2026', 'total' => 87_200_000,  'status' => 'lunas'],
        ];
    }
}
