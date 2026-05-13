<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Services\ErpDataService;

class PenjualanController extends Controller
{
    public function index()
    {
        return view('pages.penjualan.index', [
            'currentPage'  => 'penjualan',
            'breadcrumb'   => [['label' => 'Penjualan', 'url' => route('penjualan.index')]],
            'salesOrders'  => ErpDataService::salesOrders(),
        ]);
    }

    public function show(string $id)
    {
        $so    = collect(ErpDataService::salesOrders())->firstWhere('id', $id)
                 ?? ErpDataService::salesOrders()[0];

        return view('penjualan.show', [
            'currentPage'   => 'penjualan',
            'breadcrumb'    => [
                ['label' => 'Penjualan', 'url' => route('penjualan.index')],
                ['label' => $so['id']],
            ],
            'so'            => $so,
            'soDetailItems' => ErpDataService::soDetailItems(),
        ]);
    }

    public function create()
    {
        return view('penjualan.create', [
            'currentPage' => 'penjualan',
            'breadcrumb'  => [
                ['label' => 'Penjualan', 'url' => route('penjualan.index')],
                ['label' => 'Tambah SO'],
            ],
            'produk'  => ErpDataService::produk(),
            'kontak'  => ErpDataService::kontak(),
            'gudang'  => ErpDataService::gudang(),
        ]);
    }

    public function pengiriman(string $id)
    {
        $so = collect(ErpDataService::salesOrders())->firstWhere('id', $id)
              ?? ErpDataService::salesOrders()[0];

        return view('penjualan.pengiriman', [
            'currentPage'   => 'penjualan',
            'breadcrumb'    => [
                ['label' => 'Penjualan', 'url' => route('penjualan.index')],
                ['label' => $so['id'], 'url' => route('penjualan.show', $so['id'])],
                ['label' => 'Buat Pengiriman'],
            ],
            'so'            => $so,
            'soDetailItems' => ErpDataService::soDetailItems(),
        ]);
    }
}
