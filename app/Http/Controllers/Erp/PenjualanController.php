<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Services\ErpDataService;

class PenjualanController extends Controller
{
    public function index()
    {
        return view('erp.penjualan.index', [
            'currentPage'  => 'penjualan',
            'breadcrumb'   => [['label' => 'Penjualan', 'url' => route('erp.penjualan.index')]],
            'salesOrders'  => ErpDataService::salesOrders(),
        ]);
    }

    public function show(string $id)
    {
        $so    = collect(ErpDataService::salesOrders())->firstWhere('id', $id)
                 ?? ErpDataService::salesOrders()[0];

        return view('erp.penjualan.show', [
            'currentPage'   => 'penjualan',
            'breadcrumb'    => [
                ['label' => 'Penjualan', 'url' => route('erp.penjualan.index')],
                ['label' => $so['id']],
            ],
            'so'            => $so,
            'soDetailItems' => ErpDataService::soDetailItems(),
        ]);
    }

    public function create()
    {
        return view('erp.penjualan.create', [
            'currentPage' => 'penjualan',
            'breadcrumb'  => [
                ['label' => 'Penjualan', 'url' => route('erp.penjualan.index')],
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

        return view('erp.penjualan.pengiriman', [
            'currentPage'   => 'penjualan',
            'breadcrumb'    => [
                ['label' => 'Penjualan', 'url' => route('erp.penjualan.index')],
                ['label' => $so['id'], 'url' => route('erp.penjualan.show', $so['id'])],
                ['label' => 'Buat Pengiriman'],
            ],
            'so'            => $so,
            'soDetailItems' => ErpDataService::soDetailItems(),
        ]);
    }
}
