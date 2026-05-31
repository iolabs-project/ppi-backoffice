<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Services\ErpDataService;

class PembelianController extends Controller
{
    public function index()
    {
        return view('pages.pembelian.index', [
            'currentPage'    => 'pembelian',
            'breadcrumb'     => [['label' => 'Pembelian', 'url' => route('pembelian.index')]],
            'purchaseOrders' => ErpDataService::purchaseOrders(),
        ]);
    }

    public function show(string $id)
    {
        $po = collect(ErpDataService::purchaseOrders())->firstWhere('id', $id)
              ?? ErpDataService::purchaseOrders()[0];

        return view('pages.pembelian.show', [
            'currentPage'    => 'pembelian',
            'breadcrumb'     => [
                ['label' => 'Pembelian', 'url' => route('pembelian.index')],
                ['label' => $po['id']],
            ],
            'po'            => $po,
            'poDetailItems' => ErpDataService::poDetailItems(),
        ]);
    }

    public function create()
    {
        return view('pages.pembelian.create', [
            'currentPage' => 'pembelian',
            'breadcrumb'  => [
                ['label' => 'Pembelian', 'url' => route('pembelian.index')],
                ['label' => 'Tambah PO'],
            ],
            'produk' => ErpDataService::produk(),
            'kontak' => ErpDataService::kontak(),
            'gudang' => ErpDataService::gudang(),
        ]);
    }

    public function edit(string $id)
    {
        $po = collect(ErpDataService::purchaseOrders())->firstWhere('id', $id)
              ?? ErpDataService::purchaseOrders()[0];

        return view('pages.pembelian.edit', [
            'currentPage'   => 'pembelian',
            'breadcrumb'    => [
                ['label' => 'Pembelian', 'url' => route('pembelian.index')],
                ['label' => $po['id'], 'url' => route('pembelian.show', $po['id'])],
                ['label' => 'Edit Draft'],
            ],
            'po'            => $po,
            'produk'        => ErpDataService::produk(),
            'kontak'        => ErpDataService::kontak(),
            'gudang'        => ErpDataService::gudang(),
            'poDetailItems' => ErpDataService::poDetailItems(),
        ]);
    }

    public function pengiriman(string $id)
    {
        $po = collect(ErpDataService::purchaseOrders())->firstWhere('id', $id)
              ?? ErpDataService::purchaseOrders()[0];

        return view('pages.pembelian.pengiriman', [
            'currentPage'   => 'pembelian',
            'breadcrumb'    => [
                ['label' => 'Pembelian', 'url' => route('pembelian.index')],
                ['label' => $po['id'], 'url' => route('pembelian.show', $po['id'])],
                ['label' => 'Buat Pengiriman'],
            ],
            'po'            => $po,
            'poDetailItems' => ErpDataService::poDetailItems(),
        ]);
    }

    public function penerimaan(string $id)
    {
        $po = collect(ErpDataService::purchaseOrders())->firstWhere('id', $id)
              ?? ErpDataService::purchaseOrders()[0];

        return view('pages.pembelian.penerimaan', [
            'currentPage'   => 'pembelian',
            'breadcrumb'    => [
                ['label' => 'Pembelian', 'url' => route('pembelian.index')],
                ['label' => $po['id'], 'url' => route('pembelian.show', $po['id'])],
                ['label' => 'Buat Penerimaan'],
            ],
            'po'            => $po,
            'poDetailItems' => ErpDataService::poDetailItems(),
        ]);
    }

    public function tagihan(string $id)
    {
        $po = collect(ErpDataService::purchaseOrders())->firstWhere('id', $id)
              ?? ErpDataService::purchaseOrders()[0];

        return view('pages.pembelian.tagihan', [
            'currentPage'   => 'pembelian',
            'breadcrumb'    => [
                ['label' => 'Pembelian', 'url' => route('pembelian.index')],
                ['label' => $po['id'], 'url' => route('pembelian.show', $po['id'])],
                ['label' => 'Buat Tagihan'],
            ],
            'po'            => $po,
            'poDetailItems' => ErpDataService::poDetailItems(),
        ]);
    }

    public function penerimaanList()
    {
        return view('pages.pembelian.penerimaan-list', [
            'currentPage' => 'pembelian.penerimaan',
            'breadcrumb'  => [
                ['label' => 'Pembelian', 'url' => route('pembelian.index')],
                ['label' => 'Penerimaan'],
            ],
            'penerimaan' => ErpDataService::penerimaanPembelian(),
        ]);
    }

    public function tagihanList()
    {
        return view('pages.pembelian.tagihan-list', [
            'currentPage' => 'pembelian.tagihan_list',
            'breadcrumb'  => [
                ['label' => 'Pembelian', 'url' => route('pembelian.index')],
                ['label' => 'Tagihan'],
            ],
            'tagihan' => ErpDataService::tagihanPembelian(),
        ]);
    }

    public function tagihanCreate()
    {
        return view('pages.pembelian.tagihan-create', [
            'currentPage' => 'pembelian.tagihan_list',
            'breadcrumb'  => [
                ['label' => 'Pembelian', 'url' => route('pembelian.index')],
                ['label' => 'Tagihan', 'url' => route('pembelian.tagihan_list')],
                ['label' => 'Buat Tagihan'],
            ],
            'purchaseOrders' => ErpDataService::purchaseOrders(),
            'kontak'         => ErpDataService::kontak(),
            'produk'         => ErpDataService::produk(),
            'gudang'         => ErpDataService::gudang(),
        ]);
    }

    public function tagihanShow(string $id)
    {
        $bill = collect(ErpDataService::tagihanPembelian())->firstWhere('id', $id)
                ?? ErpDataService::tagihanPembelian()[0];

        return view('pages.pembelian.tagihan-show', [
            'currentPage' => 'pembelian.tagihan_list',
            'breadcrumb'  => [
                ['label' => 'Pembelian', 'url' => route('pembelian.index')],
                ['label' => 'Tagihan', 'url' => route('pembelian.tagihan_list')],
                ['label' => $bill['id']],
            ],
            'bill'          => $bill,
            'poDetailItems' => ErpDataService::poDetailItems(),
        ]);
    }
}
