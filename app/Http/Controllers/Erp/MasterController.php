<?php

namespace App\Http\Controllers\Erp;

use App\Enums\AccountCategoryEnum;
use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Services\Master\AccountService;
use App\Services\Master\ContactService;
use App\Services\ErpDataService;
use App\Services\Master\ProductService;
use App\Services\Master\RoleService;

class MasterController extends Controller
{
    public function index(ProductService $productService, ContactService $contactService, AccountService $accountService, RoleService $roleService)
    {
        $companyID = config('context.selected_company_id');
        return view('pages.master.index', [
            'currentPage'        => 'master',
            'breadcrumb'         => [['label' => 'Master Data']],
            'accountCategories'  => $accountService->fetchAccountCategoryData(),
            'userRoles'          => $roleService->fetchRoleData(),
            'roles'              => ErpDataService::roles(),
            'units'              => Unit::whereNull('deleted_at')->select('id', 'name', 'symbol')->get(),
            'productCategories'  => $productService->fetchProductCategoryData(),
            'contactOptions'     => $contactService->fetchContactData('employee'),
            'inventoryAccounts'  => $accountService->fetchAccountData(companyID: $companyID, categoryID: AccountCategoryEnum::INVENTORY->value),
            'salesAccounts'      => $accountService->fetchAccountData(companyID: $companyID, categoryID: AccountCategoryEnum::REVENUE->value),
            'cogsAccounts'       => $accountService->fetchAccountData(companyID: $companyID, categoryID: AccountCategoryEnum::COST_OF_GOODS_SOLD->value),
            'receivableAccounts' => $accountService->fetchAccountData(companyID: $companyID, categoryID: AccountCategoryEnum::ACCOUNT_RECEIVABLE->value),
            'payableAccounts'    => $accountService->fetchAccountData(companyID: $companyID, categoryID: AccountCategoryEnum::ACCOUNT_PAYABLE->value),
        ]);
    }

    public function showProduk(string $kode)
    {
        $produkList = ErpDataService::produk();
        $produk = collect($produkList)->firstWhere('kode', $kode);

        if (!$produk) abort(404);

        $stok = $produk['stok'] ?? 0;

        $stokPerGudang = [
            ['nama' => 'Unassigned',       'stok' => (int)round($stok * 0.34)],
            ['nama' => 'Gudang ' . ($produk['gudang'] ?? 'Bekasi'), 'stok' => (int)round($stok * 0.33)],
            ['nama' => 'Gudang Lain',      'stok' => $stok - (int)round($stok * 0.34) - (int)round($stok * 0.33)],
        ];

        $transaksiTerkini = [
            ['tanggal' => '26/05/2026', 'deskripsi' => 'Pengiriman Penjualan (SD/00001)', 'ref' => '',         'qty' => -1,  'harga' => $produk['hargaJual'], 'total' => $produk['hargaJual']],
            ['tanggal' => '22/05/2026', 'deskripsi' => 'Tagihan Penjualan (INV/00048)',   'ref' => 'INV/00048', 'qty' => -2,  'harga' => $produk['hargaJual'], 'total' => $produk['hargaJual'] * 2],
            ['tanggal' => '20/05/2026', 'deskripsi' => 'Pemesanan Pembelian (PI/00055)',  'ref' => 'PI/00055',  'qty' => 200, 'harga' => $produk['hargaBeli'], 'total' => $produk['hargaBeli'] * 200],
        ];

        $transferGudang = [
            ['tanggal' => '15/04/2026', 'nomor' => 'WT/00001', 'dari' => 'Unassigned',       'ke' => 'Gudang ' . ($produk['gudang'] ?? 'Bekasi'), 'qty' => 10],
            ['tanggal' => '16/04/2026', 'nomor' => 'WT/00002', 'dari' => 'Gudang ' . ($produk['gudang'] ?? 'Bekasi'), 'ke' => 'Gudang Lain',     'qty' => 5],
            ['tanggal' => '17/04/2026', 'nomor' => 'WT/00003', 'dari' => 'Gudang Lain',      'ke' => 'Unassigned',                                'qty' => 2],
        ];

        return view('pages.master.produk-show', [
            'currentPage'      => 'master',
            'breadcrumb'       => [
                ['label' => 'Master Data', 'url' => route('master.index')],
                ['label' => $produk['nama']],
            ],
            'produk'           => $produk,
            'stokPerGudang'    => $stokPerGudang,
            'transaksiTerkini' => $transaksiTerkini,
            'transferGudang'   => $transferGudang,
            'akunPembelian'    => '5-1000 HPP',
            'akunPenjualan'    => '4-1000 Pendapatan Penjualan',
            'akunInventori'    => '1-1300 Persediaan Barang',
        ]);
    }

    public function showKontak(string $id)
    {
        $kontakList = ErpDataService::kontak();
        $kontak = collect($kontakList)->firstWhere('id', $id);

        if (!$kontak) abort(404);

        $isCustomer = strtolower($kontak['tipe']) === 'customer';

        $transaksi = $isCustomer ? [
            ['tanggal' => '26/05/2026', 'nomor' => 'SO-2026-0145', 'tipe' => 'Sales Order',    'status' => 'draft',   'total' => 218_400_000],
            ['tanggal' => '06/05/2026', 'nomor' => 'SO-2026-0142', 'tipe' => 'Sales Order',    'status' => 'selesai', 'total' => 84_500_000],
            ['tanggal' => '02/05/2026', 'nomor' => 'INV-2026-0041','tipe' => 'Tagihan',         'status' => 'belum-dibayar', 'total' => 56_400_000],
            ['tanggal' => '28/04/2026', 'nomor' => 'SO-2026-0121', 'tipe' => 'Sales Order',    'status' => 'selesai', 'total' => 72_900_000],
            ['tanggal' => '24/04/2026', 'nomor' => 'INV-2026-0039','tipe' => 'Tagihan',         'status' => 'belum-dibayar', 'total' => 72_900_000],
            ['tanggal' => '20/04/2026', 'nomor' => 'SO-2026-0111', 'tipe' => 'Sales Order',    'status' => 'selesai', 'total' => 66_700_000],
        ] : [
            ['tanggal' => '09/05/2026', 'nomor' => 'PO-2026-0097', 'tipe' => 'Purchase Order', 'status' => 'draft',   'total' => 188_000_000],
            ['tanggal' => '07/05/2026', 'nomor' => 'PO-2026-0094', 'tipe' => 'Purchase Order', 'status' => 'disetujui', 'total' => 376_000_000],
            ['tanggal' => '05/05/2026', 'nomor' => 'BILL-2026-0023','tipe' => 'Tagihan Beli',   'status' => 'belum-dibayar', 'total' => 376_000_000],
            ['tanggal' => '05/05/2026', 'nomor' => 'PO-2026-0091', 'tipe' => 'Purchase Order', 'status' => 'selesai', 'total' => 124_800_000],
            ['tanggal' => '04/05/2026', 'nomor' => 'BILL-2026-0021','tipe' => 'Tagihan Beli',   'status' => 'lunas',   'total' => 124_800_000],
            ['tanggal' => '25/04/2026', 'nomor' => 'PO-2026-0083', 'tipe' => 'Purchase Order', 'status' => 'selesai', 'total' => 204_800_000],
        ];

        $monthly = [
            ['Des', 0], ['Jan', 0], ['Feb', 0],
            ['Mar', (int)round(($kontak['piutang'] + $kontak['hutang']) * 0.12)],
            ['Apr', (int)round(($kontak['piutang'] + $kontak['hutang']) * 0.35)],
            ['Mei', (int)round(($kontak['piutang'] + $kontak['hutang']) * 0.53)],
        ];

        return view('pages.master.kontak-show', [
            'currentPage' => 'master',
            'breadcrumb'  => [
                ['label' => 'Master Data', 'url' => route('master.index')],
                ['label' => $kontak['nama']],
            ],
            'kontak'    => $kontak,
            'transaksi' => $transaksi,
            'monthly'   => $monthly,
        ]);
    }

    public function showGudang(string $kode)
    {
        $gudangList = ErpDataService::gudang();
        $gudang = collect($gudangList)->firstWhere('kode', $kode);

        if (!$gudang) abort(404);

        $kotaGudang  = str_replace('Gudang ', '', $gudang['nama']);
        $allProduk   = ErpDataService::produk();
        $produkItems = collect($allProduk)
            ->filter(fn($p) => ($p['gudang'] ?? '') === $kotaGudang)
            ->values()->all();

        $totalStok  = array_sum(array_column($produkItems, 'stok'));
        $totalNilai = array_sum(array_map(fn($p) => $p['stok'] * $p['hargaBeli'], $produkItems));
        $rataHpp    = count($produkItems) > 0
            ? (int) round(array_sum(array_column($produkItems, 'hargaBeli')) / count($produkItems))
            : 0;

        $produkForJs = array_map(fn($p) => [
            'nama'     => $p['nama'],
            'kode'     => $p['kode'],
            'qty'      => $p['stok'],
            'qtyFmt'   => fmt_num($p['stok']),
            'satuan'   => $p['satuan'],
            'nilai'    => $p['stok'] * $p['hargaBeli'],
            'nilaiFmt' => fmt_rp_short($p['stok'] * $p['hargaBeli']),
        ], $produkItems);

        return view('pages.master.gudang-show', [
            'currentPage' => 'master',
            'breadcrumb'  => [
                ['label' => 'Master Data', 'url' => route('master.index')],
                ['label' => $gudang['nama']],
            ],
            'gudang'      => $gudang,
            'produkForJs' => $produkForJs,
            'totalStok'   => $totalStok,
            'totalNilai'  => $totalNilai,
            'rataHpp'     => $rataHpp,
        ]);
    }

    public function transferGudang(string $kode)
    {
        $gudangList = ErpDataService::gudang();
        $gudang     = collect($gudangList)->firstWhere('kode', $kode);

        if (!$gudang) abort(404);

        $kotaGudang  = str_replace('Gudang ', '', $gudang['nama']);
        $allProduk   = ErpDataService::produk();
        $produkGudang = collect($allProduk)
            ->filter(fn($p) => ($p['gudang'] ?? '') === $kotaGudang)
            ->map(fn($p) => ['kode' => $p['kode'], 'nama' => $p['nama'], 'satuan' => $p['satuan'], 'stok' => $p['stok']])
            ->values()->all();

        $otherGudang = collect($gudangList)
            ->filter(fn($g) => $g['kode'] !== $kode)
            ->values()->all();

        return view('pages.master.gudang-transfer', [
            'currentPage'  => 'master',
            'breadcrumb'   => [
                ['label' => 'Master Data', 'url' => route('master.index')],
                ['label' => $gudang['nama'], 'url' => route('master.gudang.show', $kode)],
                ['label' => 'Transfer Gudang'],
            ],
            'gudang'       => $gudang,
            'otherGudang'  => $otherGudang,
            'produkGudang' => $produkGudang,
            'nomorDefault' => 'WT/00005',
        ]);
    }
}
