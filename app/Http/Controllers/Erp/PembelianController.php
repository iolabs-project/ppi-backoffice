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
}
