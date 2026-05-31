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
            ['id' => 'SO-2026-0145', 'tanggal' => '08 Mei 2026', 'customer' => 'PT Bogasari Flour Mills',      'gudang' => 'Gudang Bekasi',    'total' => 218_400_000, 'status' => 'draft',   'jatuhTempo' => '22 Mei 2026'],
            ['id' => 'SO-2026-0144', 'tanggal' => '07 Mei 2026', 'customer' => 'CV Gula Manis Lestari',        'gudang' => 'Gudang Tangerang', 'total' => 57_600_000,  'status' => 'draft',   'jatuhTempo' => '21 Mei 2026'],
            ['id' => 'SO-2026-0143', 'tanggal' => '07 Mei 2026', 'customer' => 'Bakery Sahabat Sehat',         'gudang' => 'Gudang Bekasi',    'total' => 31_250_000,  'status' => 'draft',   'jatuhTempo' => '21 Mei 2026'],
            ['id' => 'SO-2026-0142', 'tanggal' => '06 Mei 2026', 'customer' => 'PT Roti Sumber Rejeki',        'gudang' => 'Gudang Bekasi',    'total' => 84_500_000,  'status' => 'selesai', 'jatuhTempo' => '20 Mei 2026'],
            ['id' => 'SO-2026-0141', 'tanggal' => '06 Mei 2026', 'customer' => 'CV Mie Mas Joko',              'gudang' => 'Gudang Bekasi',    'total' => 142_800_000, 'status' => 'selesai', 'jatuhTempo' => '21 Mei 2026'],
            ['id' => 'SO-2026-0140', 'tanggal' => '05 Mei 2026', 'customer' => 'PT Catering Selera Nusantara', 'gudang' => 'Gudang Tangerang', 'total' => 38_200_000,  'status' => 'selesai', 'jatuhTempo' => '19 Mei 2026'],
            ['id' => 'SO-2026-0139', 'tanggal' => '05 Mei 2026', 'customer' => 'Toko Bahan Kue Anggrek',       'gudang' => 'Gudang Surabaya',  'total' => 22_750_000,  'status' => 'selesai', 'jatuhTempo' => '18 Mei 2026'],
            ['id' => 'SO-2026-0138', 'tanggal' => '04 Mei 2026', 'customer' => 'Warung Padang Sederhana',      'gudang' => 'Gudang Bekasi',    'total' => 14_900_000,  'status' => 'selesai', 'jatuhTempo' => '18 Mei 2026'],
            ['id' => 'SO-2026-0137', 'tanggal' => '04 Mei 2026', 'customer' => 'PT Roti Sumber Rejeki',        'gudang' => 'Gudang Bekasi',    'total' => 96_300_000,  'status' => 'selesai', 'jatuhTempo' => '17 Mei 2026'],
            ['id' => 'SO-2026-0136', 'tanggal' => '03 Mei 2026', 'customer' => 'CV Mie Mas Joko',              'gudang' => 'Gudang Semarang',  'total' => 71_200_000,  'status' => 'selesai', 'jatuhTempo' => '17 Mei 2026'],
            ['id' => 'SO-2026-0135', 'tanggal' => '03 Mei 2026', 'customer' => 'Bakery Sahabat Sehat',         'gudang' => 'Gudang Bekasi',    'total' => 48_650_000,  'status' => 'selesai', 'jatuhTempo' => '16 Mei 2026'],
            ['id' => 'SO-2026-0134', 'tanggal' => '03 Mei 2026', 'customer' => 'PT Bogasari Flour Mills',      'gudang' => 'Gudang Bekasi',    'total' => 312_000_000, 'status' => 'selesai', 'jatuhTempo' => '20 Mei 2026'],
            ['id' => 'SO-2026-0133', 'tanggal' => '02 Mei 2026', 'customer' => 'Toko Bahan Kue Anggrek',       'gudang' => 'Gudang Surabaya',  'total' => 18_250_000,  'status' => 'selesai', 'jatuhTempo' => '16 Mei 2026'],
            ['id' => 'SO-2026-0132', 'tanggal' => '02 Mei 2026', 'customer' => 'Warung Padang Sederhana',      'gudang' => 'Gudang Padang',    'total' => 7_900_000,   'status' => 'selesai', 'jatuhTempo' => '15 Mei 2026'],
            ['id' => 'SO-2026-0131', 'tanggal' => '02 Mei 2026', 'customer' => 'PT Roti Sumber Rejeki',        'gudang' => 'Gudang Bekasi',    'total' => 56_400_000,  'status' => 'selesai', 'jatuhTempo' => '18 Mei 2026'],
            ['id' => 'SO-2026-0130', 'tanggal' => '01 Mei 2026', 'customer' => 'CV Mie Mas Joko',              'gudang' => 'Gudang Semarang',  'total' => 23_700_000,  'status' => 'selesai', 'jatuhTempo' => '14 Mei 2026'],
            ['id' => 'SO-2026-0129', 'tanggal' => '01 Mei 2026', 'customer' => 'Bakery Sahabat Sehat',         'gudang' => 'Gudang Bekasi',    'total' => 12_650_000,  'status' => 'selesai', 'jatuhTempo' => '13 Mei 2026'],
            ['id' => 'SO-2026-0128', 'tanggal' => '01 Mei 2026', 'customer' => 'PT Catering Selera Nusantara', 'gudang' => 'Gudang Tangerang', 'total' => 44_800_000,  'status' => 'selesai', 'jatuhTempo' => '19 Mei 2026'],
            ['id' => 'SO-2026-0127', 'tanggal' => '30 Apr 2026', 'customer' => 'Toko Bahan Kue Anggrek',       'gudang' => 'Gudang Surabaya',  'total' => 9_350_000,   'status' => 'selesai', 'jatuhTempo' => '14 Mei 2026'],
            ['id' => 'SO-2026-0126', 'tanggal' => '30 Apr 2026', 'customer' => 'CV Gula Manis Lestari',        'gudang' => 'Gudang Bekasi',    'total' => 64_200_000,  'status' => 'selesai', 'jatuhTempo' => '18 Mei 2026'],
            ['id' => 'SO-2026-0125', 'tanggal' => '30 Apr 2026', 'customer' => 'PT Salim Ivomas',              'gudang' => 'Gudang Tangerang', 'total' => 198_400_000, 'status' => 'selesai', 'jatuhTempo' => '21 Mei 2026'],
            ['id' => 'SO-2026-0124', 'tanggal' => '29 Apr 2026', 'customer' => 'UD Beras Sumber Tani',         'gudang' => 'Gudang Surabaya',  'total' => 87_200_000,  'status' => 'selesai', 'jatuhTempo' => '17 Mei 2026'],
            ['id' => 'SO-2026-0123', 'tanggal' => '29 Apr 2026', 'customer' => 'PT Bogasari Flour Mills',      'gudang' => 'Gudang Bekasi',    'total' => 124_800_000, 'status' => 'selesai', 'jatuhTempo' => '16 Mei 2026'],
            ['id' => 'SO-2026-0122', 'tanggal' => '29 Apr 2026', 'customer' => 'Warung Padang Sederhana',      'gudang' => 'Gudang Padang',    'total' => 6_450_000,   'status' => 'selesai', 'jatuhTempo' => '12 Mei 2026'],
            ['id' => 'SO-2026-0121', 'tanggal' => '28 Apr 2026', 'customer' => 'PT Roti Sumber Rejeki',        'gudang' => 'Gudang Bekasi',    'total' => 72_900_000,  'status' => 'selesai', 'jatuhTempo' => '15 Mei 2026'],
            ['id' => 'SO-2026-0120', 'tanggal' => '28 Apr 2026', 'customer' => 'CV Mie Mas Joko',              'gudang' => 'Gudang Semarang',  'total' => 33_200_000,  'status' => 'selesai', 'jatuhTempo' => '18 Mei 2026'],
            ['id' => 'SO-2026-0119', 'tanggal' => '28 Apr 2026', 'customer' => 'Bakery Sahabat Sehat',         'gudang' => 'Gudang Bekasi',    'total' => 28_450_000,  'status' => 'selesai', 'jatuhTempo' => '14 Mei 2026'],
            ['id' => 'SO-2026-0118', 'tanggal' => '27 Apr 2026', 'customer' => 'PT Catering Selera Nusantara', 'gudang' => 'Gudang Tangerang', 'total' => 51_300_000,  'status' => 'selesai', 'jatuhTempo' => '13 Mei 2026'],
            ['id' => 'SO-2026-0117', 'tanggal' => '27 Apr 2026', 'customer' => 'Toko Bahan Kue Anggrek',       'gudang' => 'Gudang Surabaya',  'total' => 16_900_000,  'status' => 'selesai', 'jatuhTempo' => '12 Mei 2026'],
            ['id' => 'SO-2026-0116', 'tanggal' => '26 Apr 2026', 'customer' => 'CV Gula Manis Lestari',        'gudang' => 'Gudang Bekasi',    'total' => 72_000_000,  'status' => 'selesai', 'jatuhTempo' => '11 Mei 2026'],
            ['id' => 'SO-2026-0115', 'tanggal' => '26 Apr 2026', 'customer' => 'PT Salim Ivomas',              'gudang' => 'Gudang Tangerang', 'total' => 142_600_000, 'status' => 'selesai', 'jatuhTempo' => '10 Mei 2026'],
            ['id' => 'SO-2026-0114', 'tanggal' => '25 Apr 2026', 'customer' => 'UD Beras Sumber Tani',         'gudang' => 'Gudang Surabaya',  'total' => 39_800_000,  'status' => 'selesai', 'jatuhTempo' => '09 Mei 2026'],
            ['id' => 'SO-2026-0113', 'tanggal' => '25 Apr 2026', 'customer' => 'PT Bogasari Flour Mills',      'gudang' => 'Gudang Bekasi',    'total' => 256_400_000, 'status' => 'selesai', 'jatuhTempo' => '14 Mei 2026'],
            ['id' => 'SO-2026-0112', 'tanggal' => '24 Apr 2026', 'customer' => 'Warung Padang Sederhana',      'gudang' => 'Gudang Padang',    'total' => 5_200_000,   'status' => 'selesai', 'jatuhTempo' => '08 Mei 2026'],
            ['id' => 'SO-2026-0111', 'tanggal' => '24 Apr 2026', 'customer' => 'PT Roti Sumber Rejeki',        'gudang' => 'Gudang Bekasi',    'total' => 66_700_000,  'status' => 'selesai', 'jatuhTempo' => '12 Mei 2026'],
            ['id' => 'SO-2026-0110', 'tanggal' => '23 Apr 2026', 'customer' => 'CV Mie Mas Joko',              'gudang' => 'Gudang Semarang',  'total' => 21_500_000,  'status' => 'selesai', 'jatuhTempo' => '07 Mei 2026'],
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
            ['id' => '1-10001', 'nama' => 'Kas',           'bank' => 'Kas',  'norek' => '', 'jenis' => 'Kas',  'saldo' => 3_450_000,  'tipe' => 'IDR', 'transaksi' => 2],
            ['id' => '1-10002', 'nama' => 'Rekening Bank', 'bank' => 'Bank', 'norek' => '', 'jenis' => 'Bank', 'saldo' => 96_300_000, 'tipe' => 'IDR', 'transaksi' => 3],
            ['id' => '1-10003', 'nama' => 'Giro',          'bank' => 'Giro', 'norek' => '', 'jenis' => 'Bank', 'saldo' => 48_650_000, 'tipe' => 'IDR', 'transaksi' => 2],
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
            ['kode' => '6-1400', 'nama' => 'Beban Transport',          'kategori' => 'Beban',    'parent' => '6-1000', 'saldo' => 9_350_000],
            ['kode' => '6-1500', 'nama' => 'Beban Operasional',        'kategori' => 'Beban',    'parent' => '6-1000', 'saldo' => 2_450_000],
            ['kode' => '6-1600', 'nama' => 'Beban Pemeliharaan',       'kategori' => 'Beban',    'parent' => '6-1000', 'saldo' => 4_200_000],
            ['kode' => '6-1700', 'nama' => 'Beban Marketing',          'kategori' => 'Beban',    'parent' => '6-1000', 'saldo' => 8_500_000],
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
            ['tanggal' => '06 Mei 2026', 'akun' => 'Rekening Bank', 'keterangan' => 'Pelunasan SO-2026-0137 – PT Roti Sumber Rejeki',    'masuk' => 96_300_000, 'keluar' => 0,          'saldo' => 96_300_000,  'ref' => 'SO-0137'],
            ['tanggal' => '06 Mei 2026', 'akun' => 'Rekening Bank', 'keterangan' => 'Pembayaran PO-2026-0091 – PT Bogasari Flour Mills', 'masuk' => 0,          'keluar' => 45_000_000, 'saldo' => 51_300_000,  'ref' => 'PO-0091'],
            ['tanggal' => '05 Mei 2026', 'akun' => 'Giro',          'keterangan' => 'Terima pembayaran giro SO-2026-0135',              'masuk' => 48_650_000, 'keluar' => 0,          'saldo' => 48_650_000,  'ref' => 'SO-0135'],
            ['tanggal' => '05 Mei 2026', 'akun' => 'Rekening Bank', 'keterangan' => 'Transfer ke Giro – pencairan dana operasional',    'masuk' => 0,          'keluar' => 20_000_000, 'saldo' => 31_300_000,  'ref' => 'TRF-0021'],
            ['tanggal' => '04 Mei 2026', 'akun' => 'Kas',           'keterangan' => 'Penerimaan tunai penjualan harian',                'masuk' => 5_250_000,  'keluar' => 0,          'saldo' => 5_250_000,   'ref' => 'SO-0132'],
            ['tanggal' => '04 Mei 2026', 'akun' => 'Kas',           'keterangan' => 'Bayar biaya operasional kantor',                   'masuk' => 0,          'keluar' => 1_800_000,  'saldo' => 3_450_000,   'ref' => 'EXP-0042'],
            ['tanggal' => '03 Mei 2026', 'akun' => 'Giro',          'keterangan' => 'Pencairan giro – pembayaran vendor',               'masuk' => 0,          'keluar' => 12_500_000, 'saldo' => 36_150_000,  'ref' => 'PO-0088'],
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
            ['id' => 'PO-2026-0097', 'tanggal' => '09 Mei 2026', 'vendor' => 'PT Bogasari Flour Mills',   'gudang' => 'Gudang Bekasi',    'total' => 188_000_000, 'status' => 'draft',   'jatuhTempo' => '23 Mei 2026'],
            ['id' => 'PO-2026-0096', 'tanggal' => '08 Mei 2026', 'vendor' => 'CV Gula Manis Lestari',     'gudang' => 'Gudang Bekasi',    'total' => 122_400_000, 'status' => 'draft',   'jatuhTempo' => '22 Mei 2026'],
            ['id' => 'PO-2026-0095', 'tanggal' => '08 Mei 2026', 'vendor' => 'UD Beras Sumber Tani',      'gudang' => 'Gudang Surabaya',  'total' => 62_400_000,  'status' => 'draft',   'jatuhTempo' => '22 Mei 2026'],
            ['id' => 'PO-2026-0094', 'tanggal' => '07 Mei 2026', 'vendor' => 'PT Bogasari Flour Mills',   'gudang' => 'Gudang Bekasi',   'total' => 376_000_000, 'status' => 'disetujui', 'jatuhTempo' => '21 Mei 2026'],
            ['id' => 'PO-2026-0093', 'tanggal' => '07 Mei 2026', 'vendor' => 'CV Gula Manis Lestari',     'gudang' => 'Gudang Bekasi',   'total' => 244_800_000, 'status' => 'disetujui', 'jatuhTempo' => '21 Mei 2026'],
            ['id' => 'PO-2026-0092', 'tanggal' => '06 Mei 2026', 'vendor' => 'PT Salim Ivomas',            'gudang' => 'Gudang Tangerang', 'total' => 198_400_000, 'status' => 'selesai',   'jatuhTempo' => '20 Mei 2026'],
            ['id' => 'PO-2026-0091', 'tanggal' => '05 Mei 2026', 'vendor' => 'PT Bogasari Flour Mills',   'gudang' => 'Gudang Bekasi',   'total' => 124_800_000, 'status' => 'selesai',   'jatuhTempo' => '19 Mei 2026'],
            ['id' => 'PO-2026-0090', 'tanggal' => '05 Mei 2026', 'vendor' => 'UD Beras Sumber Tani',       'gudang' => 'Gudang Surabaya', 'total' => 87_200_000,  'status' => 'disetujui', 'jatuhTempo' => '19 Mei 2026'],
            ['id' => 'PO-2026-0089', 'tanggal' => '04 Mei 2026', 'vendor' => 'CV Garam Madura Sejahtera',  'gudang' => 'Gudang Bekasi',   'total' => 62_400_000,  'status' => 'selesai',   'jatuhTempo' => '18 Mei 2026'],
            ['id' => 'PO-2026-0088', 'tanggal' => '03 Mei 2026', 'vendor' => 'PT Salim Ivomas',            'gudang' => 'Gudang Tangerang', 'total' => 142_600_000, 'status' => 'selesai',   'jatuhTempo' => '17 Mei 2026'],
        ];
    }

    public static function poDetailItems(): array
    {
        return [
            ['kode' => 'TPG-001', 'nama' => 'Tepung Terigu Cakra Kembar',  'qty' => 200, 'qtyDiterima' => 198, 'susut' => 2, 'satuan' => 'Kg', 'harga' => 188_000],
            ['kode' => 'TPG-002', 'nama' => 'Tepung Terigu Segitiga Biru', 'qty' => 120, 'qtyDiterima' => 120, 'susut' => 0, 'satuan' => 'Kg', 'harga' => 172_000],
        ];
    }

    public static function penerimaanPembelian(): array
    {
        return [
            ['id' => 'SUT-2026-0018', 'tanggal' => '07 Mei 2026', 'poRef' => 'PO-2026-0090', 'vendor' => 'UD Beras Sumber Tani',      'produk' => 'Beras Premium IR-64',         'qtySusut' => 3,  'satuan' => 'Kg', 'hargaSatuan' => 312_000, 'nilaiSusut' => 936_000,   'keterangan' => 'Kerusakan kemasan',         'status' => 'selesai'],
            ['id' => 'SUT-2026-0017', 'tanggal' => '06 Mei 2026', 'poRef' => 'PO-2026-0092', 'vendor' => 'PT Salim Ivomas',            'produk' => 'Minyak Goreng Kelapa Sawit',  'qtySusut' => 8,  'satuan' => 'Kg', 'hargaSatuan' => 248_000, 'nilaiSusut' => 1_984_000, 'keterangan' => 'Tumpah saat bongkar muat', 'status' => 'draft'],
            ['id' => 'SUT-2026-0016', 'tanggal' => '04 Mei 2026', 'poRef' => 'PO-2026-0089', 'vendor' => 'CV Garam Madura Sejahtera',  'produk' => 'Garam Halus Konsumsi',        'qtySusut' => 15, 'satuan' => 'Kg', 'hargaSatuan' => 62_000,  'nilaiSusut' => 930_000,   'keterangan' => 'Kemasan bocor',             'status' => 'selesai'],
            ['id' => 'SUT-2026-0015', 'tanggal' => '03 Mei 2026', 'poRef' => 'PO-2026-0088', 'vendor' => 'PT Salim Ivomas',            'produk' => 'Minyak Goreng Kelapa Sawit',  'qtySusut' => 5,  'satuan' => 'Kg', 'hargaSatuan' => 248_000, 'nilaiSusut' => 1_240_000, 'keterangan' => 'Kondisi tidak layak',       'status' => 'selesai'],
            ['id' => 'SUT-2026-0014', 'tanggal' => '30 Apr 2026', 'poRef' => 'PO-2026-0085', 'vendor' => 'PT Bogasari Flour Mills',    'produk' => 'Tepung Terigu Cakra Kembar',  'qtySusut' => 2,  'satuan' => 'Kg', 'hargaSatuan' => 188_000, 'nilaiSusut' => 376_000,   'keterangan' => 'Sobek kemasan',             'status' => 'selesai'],
            ['id' => 'SUT-2026-0013', 'tanggal' => '28 Apr 2026', 'poRef' => 'PO-2026-0083', 'vendor' => 'CV Gula Manis Lestari',      'produk' => 'Gula Pasir Kemasan Premium',  'qtySusut' => 6,  'satuan' => 'Kg', 'hargaSatuan' => 612_000, 'nilaiSusut' => 3_672_000, 'keterangan' => 'Terkena air hujan',         'status' => 'selesai'],
            ['id' => 'SUT-2026-0012', 'tanggal' => '25 Apr 2026', 'poRef' => 'PO-2026-0081', 'vendor' => 'UD Beras Sumber Tani',      'produk' => 'Beras Premium IR-64',         'qtySusut' => 10, 'satuan' => 'Kg', 'hargaSatuan' => 312_000, 'nilaiSusut' => 3_120_000, 'keterangan' => 'Tumpah saat bongkar muat', 'status' => 'draft'],
            ['id' => 'SUT-2026-0011', 'tanggal' => '22 Apr 2026', 'poRef' => 'PO-2026-0079', 'vendor' => 'PT Bogasari Flour Mills',    'produk' => 'Tepung Terigu Segitiga Biru', 'qtySusut' => 4,  'satuan' => 'Kg', 'hargaSatuan' => 172_000, 'nilaiSusut' => 688_000,   'keterangan' => 'Kemasan basah',             'status' => 'selesai'],
        ];
    }

    public static function tagihanPembelian(): array
    {
        return [
            ['id' => 'BILL-2026-0024', 'tanggal' => '06 Mei 2026', 'poRef' => 'PO-2026-0092', 'vendor' => 'PT Salim Ivomas',           'jatuhTempo' => '20 Mei 2026', 'total' => 198_400_000, 'status' => 'belum-dibayar'],
            ['id' => 'BILL-2026-0023', 'tanggal' => '05 Mei 2026', 'poRef' => 'PO-2026-0094', 'vendor' => 'PT Bogasari Flour Mills',   'jatuhTempo' => '21 Mei 2026', 'total' => 376_000_000, 'status' => 'belum-dibayar'],
            ['id' => 'BILL-2026-0022', 'tanggal' => '04 Mei 2026', 'poRef' => 'PO-2026-0093', 'vendor' => 'CV Gula Manis Lestari',     'jatuhTempo' => '21 Mei 2026', 'total' => 244_800_000, 'status' => 'dibayar-sebagian'],
            ['id' => 'BILL-2026-0021', 'tanggal' => '05 Mei 2026', 'poRef' => 'PO-2026-0091', 'vendor' => 'PT Bogasari Flour Mills',   'jatuhTempo' => '19 Mei 2026', 'total' => 124_800_000, 'status' => 'lunas'],
            ['id' => 'BILL-2026-0020', 'tanggal' => '04 Mei 2026', 'poRef' => 'PO-2026-0089', 'vendor' => 'CV Garam Madura Sejahtera', 'jatuhTempo' => '18 Mei 2026', 'total' => 62_400_000,  'status' => 'lunas'],
            ['id' => 'BILL-2026-0019', 'tanggal' => '03 Mei 2026', 'poRef' => 'PO-2026-0088', 'vendor' => 'PT Salim Ivomas',           'jatuhTempo' => '17 Mei 2026', 'total' => 142_600_000, 'status' => 'dibayar-sebagian'],
            ['id' => 'BILL-2026-0018', 'tanggal' => '05 Mei 2026', 'poRef' => 'PO-2026-0090', 'vendor' => 'UD Beras Sumber Tani',     'jatuhTempo' => '19 Mei 2026', 'total' => 87_200_000,  'status' => 'lunas'],
            ['id' => 'BILL-2026-0017', 'tanggal' => '01 Mei 2026', 'poRef' => 'PO-2026-0086', 'vendor' => 'CV Gula Manis Lestari',     'jatuhTempo' => '15 Mei 2026', 'total' => 118_600_000, 'status' => 'belum-dibayar'],
            ['id' => 'BILL-2026-0016', 'tanggal' => '30 Apr 2026', 'poRef' => 'PO-2026-0084', 'vendor' => 'PT Bogasari Flour Mills',   'jatuhTempo' => '14 Mei 2026', 'total' => 204_800_000, 'status' => 'lunas'],
        ];
    }

    public static function pengirimanPenjualan(): array
    {
        return [
            ['id' => 'DO-2026-0089', 'tanggal' => '06 Mei 2026', 'soRef' => 'SO-2026-0141', 'customer' => 'CV Mie Mas Joko',              'gudang' => 'Gudang Bekasi',    'ekspedisi' => 'Internal – Truk Box L300', 'driver' => 'Sutrisno Hadi',  'status' => 'terkirim', 'biaya' => 1_800_000],
            ['id' => 'DO-2026-0088', 'tanggal' => '05 Mei 2026', 'soRef' => 'SO-2026-0136', 'customer' => 'CV Mie Mas Joko',              'gudang' => 'Gudang Semarang',  'ekspedisi' => 'JNE Cargo',               'driver' => 'Ahmad Fauzi',    'status' => 'open',     'biaya' => 2_400_000],
            ['id' => 'DO-2026-0087', 'tanggal' => '04 Mei 2026', 'soRef' => 'SO-2026-0134', 'customer' => 'PT Bogasari Flour Mills',      'gudang' => 'Gudang Bekasi',    'ekspedisi' => 'Internal – Truk Box L300', 'driver' => 'Budi Prasetyo', 'status' => 'open',     'biaya' => 3_500_000],
            ['id' => 'DO-2026-0086', 'tanggal' => '03 Mei 2026', 'soRef' => 'SO-2026-0128', 'customer' => 'PT Catering Selera Nusantara', 'gudang' => 'Gudang Tangerang', 'ekspedisi' => 'SiCepat Cargo',           'driver' => 'Rudi Hartono',  'status' => 'terkirim', 'biaya' => 950_000],
            ['id' => 'DO-2026-0085', 'tanggal' => '02 Mei 2026', 'soRef' => 'SO-2026-0125', 'customer' => 'PT Salim Ivomas',              'gudang' => 'Gudang Tangerang', 'ekspedisi' => 'Internal – Truk Box L300', 'driver' => 'Sutrisno Hadi', 'status' => 'open',     'biaya' => 4_200_000],
            ['id' => 'DO-2026-0084', 'tanggal' => '01 Mei 2026', 'soRef' => 'SO-2026-0120', 'customer' => 'CV Mie Mas Joko',              'gudang' => 'Gudang Semarang',  'ekspedisi' => 'JNE Cargo',               'driver' => 'Ahmad Fauzi',   'status' => 'terkirim', 'biaya' => 1_600_000],
            ['id' => 'DO-2026-0083', 'tanggal' => '30 Apr 2026', 'soRef' => 'SO-2026-0117', 'customer' => 'Toko Bahan Kue Anggrek',       'gudang' => 'Gudang Surabaya',  'ekspedisi' => 'Lion Parcel',             'driver' => 'Eko Susanto',   'status' => 'terkirim', 'biaya' => 780_000],
            ['id' => 'DO-2026-0082', 'tanggal' => '29 Apr 2026', 'soRef' => 'SO-2026-0113', 'customer' => 'PT Bogasari Flour Mills',      'gudang' => 'Gudang Bekasi',    'ekspedisi' => 'Internal – Truk Box L300', 'driver' => 'Budi Prasetyo', 'status' => 'terkirim', 'biaya' => 3_800_000],
            ['id' => 'DO-2026-0081', 'tanggal' => '28 Apr 2026', 'soRef' => 'SO-2026-0110', 'customer' => 'CV Mie Mas Joko',              'gudang' => 'Gudang Semarang',  'ekspedisi' => 'JNE Cargo',               'driver' => 'Ahmad Fauzi',   'status' => 'open',     'biaya' => 1_200_000],
            ['id' => 'DO-2026-0080', 'tanggal' => '27 Apr 2026', 'soRef' => 'SO-2026-0108', 'customer' => 'PT Roti Sumber Rejeki',        'gudang' => 'Gudang Bekasi',    'ekspedisi' => 'Internal – Truk Box L300', 'driver' => 'Sutrisno Hadi', 'status' => 'terkirim', 'biaya' => 2_100_000],
        ];
    }

    public static function biayaDetail(string $id): ?array
    {
        $records = [
            'EXP-2026-0051' => [
                'id' => 'EXP-2026-0051', 'nomor' => 'EXP/00051',
                'tanggal' => '09 Mei 2026', 'tgl_raw' => '2026-05-09', 'status' => 'disetujui',
                'bayarNanti' => false,
                'dibayarDari' => ['id' => '1-10002', 'nama' => 'Rekening Bank'],
                'penerima' => ['nama' => 'Nadia Rahmawati', 'perusahaan' => 'PPI Internal', 'telepon' => '0812-3456-7890', 'alamat' => 'Kantor Pusat, Jakarta Selatan'],
                'items' => [
                    ['deskripsi' => 'Gaji Karyawan Tetap – Mei 2026',        'akun_kode' => '6-1100', 'akun_nama' => 'Beban Gaji & Tunjangan', 'jumlah' => 120_000_000],
                    ['deskripsi' => 'Tunjangan Transport – Mei 2026',        'akun_kode' => '6-1100', 'akun_nama' => 'Beban Gaji & Tunjangan', 'jumlah' => 22_800_000],
                ],
                'catatan' => 'Transfer dilakukan setiap tanggal 9 bulan berjalan.',
            ],
            'EXP-2026-0050' => [
                'id' => 'EXP-2026-0050', 'nomor' => 'EXP/00050',
                'tanggal' => '08 Mei 2026', 'tgl_raw' => '2026-05-08', 'status' => 'disetujui',
                'bayarNanti' => false,
                'dibayarDari' => ['id' => '1-10002', 'nama' => 'Rekening Bank'],
                'penerima' => ['nama' => 'PT Properti Nusantara', 'perusahaan' => '', 'telepon' => '(021) 5508-9001', 'alamat' => 'Jl. Cibitung Raya No. 88, Bekasi'],
                'items' => [
                    ['deskripsi' => 'Sewa Gudang Bekasi – Mei 2026',         'akun_kode' => '6-1200', 'akun_nama' => 'Beban Sewa Gudang', 'jumlah' => 18_000_000],
                ],
                'catatan' => '',
            ],
            'EXP-2026-0049' => [
                'id' => 'EXP-2026-0049', 'nomor' => 'EXP/00049',
                'tanggal' => '08 Mei 2026', 'tgl_raw' => '2026-05-08', 'status' => 'disetujui',
                'bayarNanti' => false,
                'dibayarDari' => ['id' => '1-10002', 'nama' => 'Rekening Bank'],
                'penerima' => ['nama' => 'PT Properti Nusantara', 'perusahaan' => '', 'telepon' => '(021) 5508-9001', 'alamat' => 'Kawasan Industri Cikupa Blok F12, Tangerang'],
                'items' => [
                    ['deskripsi' => 'Sewa Gudang Tangerang – Mei 2026',      'akun_kode' => '6-1200', 'akun_nama' => 'Beban Sewa Gudang', 'jumlah' => 15_000_000],
                ],
                'catatan' => '',
            ],
            'EXP-2026-0048' => [
                'id' => 'EXP-2026-0048', 'nomor' => 'EXP/00048',
                'tanggal' => '06 Mei 2026', 'tgl_raw' => '2026-05-06', 'status' => 'disetujui',
                'bayarNanti' => false,
                'dibayarDari' => ['id' => '1-10001', 'nama' => 'Kas'],
                'penerima' => ['nama' => 'PLN & PDAM', 'perusahaan' => '', 'telepon' => '', 'alamat' => ''],
                'items' => [
                    ['deskripsi' => 'Listrik Gudang Bekasi – Apr 2026',       'akun_kode' => '6-1300', 'akun_nama' => 'Beban Listrik & Air', 'jumlah' => 3_200_000],
                    ['deskripsi' => 'Air / PDAM Gudang Bekasi – Apr 2026',    'akun_kode' => '6-1300', 'akun_nama' => 'Beban Listrik & Air', 'jumlah' => 1_080_000],
                ],
                'catatan' => '',
            ],
            'EXP-2026-0046' => [
                'id' => 'EXP-2026-0046', 'nomor' => 'EXP/00046',
                'tanggal' => '04 Mei 2026', 'tgl_raw' => '2026-05-04', 'status' => 'menunggu',
                'bayarNanti' => false,
                'dibayarDari' => ['id' => '1-10001', 'nama' => 'Kas'],
                'penerima' => ['nama' => '—', 'perusahaan' => '', 'telepon' => '', 'alamat' => ''],
                'items' => [
                    ['deskripsi' => 'Biaya Operasional Kantor Pusat – Apr',   'akun_kode' => '6-1500', 'akun_nama' => 'Beban Operasional', 'jumlah' => 1_800_000],
                ],
                'catatan' => 'Menunggu persetujuan manager keuangan.',
            ],
        ];

        if (isset($records[$id])) {
            return $records[$id];
        }

        $list = static::biaya();
        $item = collect($list)->firstWhere('id', $id);
        if (!$item) return null;

        $nomor = 'EXP/' . str_pad(ltrim(substr($id, strrpos($id, '-') + 1), '0') ?: '0', 5, '0', STR_PAD_LEFT);
        return [
            'id'         => $item['id'],
            'nomor'      => $nomor,
            'tanggal'    => $item['tanggal'],
            'tgl_raw'    => '2026-04-01',
            'status'     => $item['status'],
            'bayarNanti' => false,
            'dibayarDari'=> ['id' => '1-10002', 'nama' => 'Rekening Bank'],
            'penerima'   => ['nama' => '—', 'perusahaan' => '', 'telepon' => '', 'alamat' => ''],
            'items'      => [
                ['deskripsi' => $item['keterangan'], 'akun_kode' => '', 'akun_nama' => $item['akun'], 'jumlah' => $item['jumlah']],
            ],
            'catatan'    => '',
        ];
    }

    public static function biaya(): array
    {
        return [
            ['id' => 'EXP-2026-0051', 'tanggal' => '09 Mei 2026', 'keterangan' => 'Gaji & Tunjangan Karyawan – Mei 2026',    'kategori' => 'Gaji',         'akun' => 'Beban Gaji & Tunjangan',  'jumlah' => 142_800_000, 'status' => 'disetujui'],
            ['id' => 'EXP-2026-0050', 'tanggal' => '08 Mei 2026', 'keterangan' => 'Sewa Gudang Bekasi – Mei 2026',            'kategori' => 'Sewa',         'akun' => 'Beban Sewa Gudang',       'jumlah' => 18_000_000,  'status' => 'disetujui'],
            ['id' => 'EXP-2026-0049', 'tanggal' => '08 Mei 2026', 'keterangan' => 'Sewa Gudang Tangerang – Mei 2026',         'kategori' => 'Sewa',         'akun' => 'Beban Sewa Gudang',       'jumlah' => 15_000_000,  'status' => 'disetujui'],
            ['id' => 'EXP-2026-0048', 'tanggal' => '06 Mei 2026', 'keterangan' => 'Listrik & Air Gudang Bekasi – Apr 2026',   'kategori' => 'Utilitas',     'akun' => 'Beban Listrik & Air',     'jumlah' => 4_280_000,   'status' => 'disetujui'],
            ['id' => 'EXP-2026-0047', 'tanggal' => '05 Mei 2026', 'keterangan' => 'Transport & BBM Truk Box Bekasi',          'kategori' => 'Transport',    'akun' => 'Beban Transport',         'jumlah' => 3_750_000,   'status' => 'disetujui'],
            ['id' => 'EXP-2026-0046', 'tanggal' => '04 Mei 2026', 'keterangan' => 'Biaya Operasional Kantor Pusat – Apr',     'kategori' => 'Operasional',  'akun' => 'Beban Operasional',       'jumlah' => 1_800_000,   'status' => 'menunggu'],
            ['id' => 'EXP-2026-0045', 'tanggal' => '03 Mei 2026', 'keterangan' => 'Perawatan Forklift Gudang Bekasi',         'kategori' => 'Pemeliharaan', 'akun' => 'Beban Pemeliharaan',      'jumlah' => 2_400_000,   'status' => 'menunggu'],
            ['id' => 'EXP-2026-0044', 'tanggal' => '02 Mei 2026', 'keterangan' => 'Biaya ATK & Perlengkapan Kantor',          'kategori' => 'Operasional',  'akun' => 'Beban Operasional',       'jumlah' => 650_000,     'status' => 'ditolak'],
            ['id' => 'EXP-2026-0043', 'tanggal' => '01 Mei 2026', 'keterangan' => 'Internet & Telepon Kantor Pusat',          'kategori' => 'Utilitas',     'akun' => 'Beban Listrik & Air',     'jumlah' => 1_200_000,   'status' => 'disetujui'],
            ['id' => 'EXP-2026-0042', 'tanggal' => '30 Apr 2026', 'keterangan' => 'Biaya Pengiriman Internal – Apr 2026',     'kategori' => 'Transport',    'akun' => 'Beban Transport',         'jumlah' => 5_600_000,   'status' => 'disetujui'],
            ['id' => 'EXP-2026-0041', 'tanggal' => '29 Apr 2026', 'keterangan' => 'Listrik & Air Gudang Tangerang – Mar',     'kategori' => 'Utilitas',     'akun' => 'Beban Listrik & Air',     'jumlah' => 3_120_000,   'status' => 'disetujui'],
            ['id' => 'EXP-2026-0040', 'tanggal' => '28 Apr 2026', 'keterangan' => 'Perawatan AC Kantor & Gudang Bekasi',      'kategori' => 'Pemeliharaan', 'akun' => 'Beban Pemeliharaan',      'jumlah' => 1_800_000,   'status' => 'disetujui'],
            ['id' => 'EXP-2026-0039', 'tanggal' => '27 Apr 2026', 'keterangan' => 'Sewa Gudang Surabaya – Apr 2026',          'kategori' => 'Sewa',         'akun' => 'Beban Sewa Gudang',       'jumlah' => 9_000_000,   'status' => 'disetujui'],
            ['id' => 'EXP-2026-0038', 'tanggal' => '25 Apr 2026', 'keterangan' => 'Biaya Marketing & Promosi Q2',             'kategori' => 'Marketing',    'akun' => 'Beban Marketing',         'jumlah' => 8_500_000,   'status' => 'menunggu'],
        ];
    }

    public static function tagihanPenjualan(): array
    {
        return [
            ['id' => 'INV-2026-0042', 'tanggal' => '05 Mei 2026', 'soRef' => 'SO-2026-0140', 'customer' => 'PT Catering Selera Nusantara', 'jatuhTempo' => '19 Mei 2026', 'total' => 38_200_000,  'status' => 'belum-dibayar'],
            ['id' => 'INV-2026-0041', 'tanggal' => '02 Mei 2026', 'soRef' => 'SO-2026-0131', 'customer' => 'PT Roti Sumber Rejeki',        'jatuhTempo' => '18 Mei 2026', 'total' => 56_400_000,  'status' => 'belum-dibayar'],
            ['id' => 'INV-2026-0040', 'tanggal' => '01 Mei 2026', 'soRef' => 'SO-2026-0126', 'customer' => 'CV Gula Manis Lestari',        'jatuhTempo' => '18 Mei 2026', 'total' => 64_200_000,  'status' => 'dibayar-sebagian'],
            ['id' => 'INV-2026-0039', 'tanggal' => '29 Apr 2026', 'soRef' => 'SO-2026-0121', 'customer' => 'PT Roti Sumber Rejeki',        'jatuhTempo' => '15 Mei 2026', 'total' => 72_900_000,  'status' => 'belum-dibayar'],
            ['id' => 'INV-2026-0038', 'tanggal' => '26 Apr 2026', 'soRef' => 'SO-2026-0116', 'customer' => 'CV Gula Manis Lestari',        'jatuhTempo' => '11 Mei 2026', 'total' => 72_000_000,  'status' => 'lunas'],
            ['id' => 'INV-2026-0037', 'tanggal' => '25 Apr 2026', 'soRef' => 'SO-2026-0110', 'customer' => 'CV Mie Mas Joko',              'jatuhTempo' => '07 Mei 2026', 'total' => 21_500_000,  'status' => 'dibayar-sebagian'],
            ['id' => 'INV-2026-0036', 'tanggal' => '06 Mei 2026', 'soRef' => 'SO-2026-0139', 'customer' => 'Toko Bahan Kue Anggrek',       'jatuhTempo' => '18 Mei 2026', 'total' => 22_750_000,  'status' => 'lunas'],
            ['id' => 'INV-2026-0035', 'tanggal' => '06 Mei 2026', 'soRef' => 'SO-2026-0137', 'customer' => 'PT Roti Sumber Rejeki',        'jatuhTempo' => '17 Mei 2026', 'total' => 96_300_000,  'status' => 'lunas'],
            ['id' => 'INV-2026-0034', 'tanggal' => '05 Mei 2026', 'soRef' => 'SO-2026-0135', 'customer' => 'Bakery Sahabat Sehat',         'jatuhTempo' => '16 Mei 2026', 'total' => 48_650_000,  'status' => 'lunas'],
            ['id' => 'INV-2026-0033', 'tanggal' => '03 Mei 2026', 'soRef' => 'SO-2026-0132', 'customer' => 'Warung Padang Sederhana',      'jatuhTempo' => '15 Mei 2026', 'total' => 7_900_000,   'status' => 'lunas'],
            ['id' => 'INV-2026-0032', 'tanggal' => '02 Mei 2026', 'soRef' => 'SO-2026-0129', 'customer' => 'Bakery Sahabat Sehat',         'jatuhTempo' => '13 Mei 2026', 'total' => 12_650_000,  'status' => 'lunas'],
            ['id' => 'INV-2026-0031', 'tanggal' => '01 Mei 2026', 'soRef' => 'SO-2026-0124', 'customer' => 'UD Beras Sumber Tani',         'jatuhTempo' => '17 Mei 2026', 'total' => 87_200_000,  'status' => 'lunas'],
        ];
    }
}
