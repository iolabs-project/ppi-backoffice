<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Services\ErpDataService;

class PembelianController extends Controller
{
    public function index()
    {
        return view('erp.pembelian.index', [
            'currentPage'    => 'pembelian',
            'breadcrumb'     => [['label' => 'Pembelian', 'url' => route('erp.pembelian.index')]],
            'purchaseOrders' => ErpDataService::purchaseOrders(),
        ]);
    }

    public function show(string $id)
    {
        $po = collect(ErpDataService::purchaseOrders())->firstWhere('id', $id)
              ?? ErpDataService::purchaseOrders()[0];

        return view('erp.pembelian.show', [
            'currentPage'    => 'pembelian',
            'breadcrumb'     => [
                ['label' => 'Pembelian', 'url' => route('erp.pembelian.index')],
                ['label' => $po['id']],
            ],
            'po'            => $po,
            'poDetailItems' => ErpDataService::poDetailItems(),
        ]);
    }

    public function create()
    {
        return view('erp.pembelian.create', [
            'currentPage' => 'pembelian',
            'breadcrumb'  => [
                ['label' => 'Pembelian', 'url' => route('erp.pembelian.index')],
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

        return view('erp.pembelian.pengiriman', [
            'currentPage'   => 'pembelian',
            'breadcrumb'    => [
                ['label' => 'Pembelian', 'url' => route('erp.pembelian.index')],
                ['label' => $po['id'], 'url' => route('erp.pembelian.show', $po['id'])],
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

        return view('erp.pembelian.tagihan', [
            'currentPage'   => 'pembelian',
            'breadcrumb'    => [
                ['label' => 'Pembelian', 'url' => route('erp.pembelian.index')],
                ['label' => $po['id'], 'url' => route('erp.pembelian.show', $po['id'])],
                ['label' => 'Buat Tagihan'],
            ],
            'po'            => $po,
            'poDetailItems' => ErpDataService::poDetailItems(),
        ]);
    }
}
