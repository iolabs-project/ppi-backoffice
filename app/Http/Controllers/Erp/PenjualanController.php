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

        return view('pages.penjualan.show', [
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
        return view('pages.penjualan.create', [
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

    public function edit(string $id)
    {
        $so = collect(ErpDataService::salesOrders())->firstWhere('id', $id)
              ?? ErpDataService::salesOrders()[0];

        return view('pages.penjualan.edit', [
            'currentPage' => 'penjualan',
            'breadcrumb'  => [
                ['label' => 'Penjualan', 'url' => route('penjualan.index')],
                ['label' => $so['id'], 'url' => route('penjualan.show', $so['id'])],
                ['label' => 'Edit Draft'],
            ],
            'so'      => $so,
            'produk'  => ErpDataService::produk(),
            'kontak'  => ErpDataService::kontak(),
            'gudang'  => ErpDataService::gudang(),
            'soDetailItems' => ErpDataService::soDetailItems(),
        ]);
    }

    public function pengirimanList()
    {
        return view('pages.penjualan.pengiriman-list', [
            'currentPage' => 'penjualan.pengiriman',
            'breadcrumb'  => [
                ['label' => 'Penjualan', 'url' => route('penjualan.index')],
                ['label' => 'Pengiriman'],
            ],
            'pengiriman'  => ErpDataService::pengirimanPenjualan(),
        ]);
    }

    public function tagihanList()
    {
        return view('pages.penjualan.tagihan-list', [
            'currentPage' => 'penjualan.tagihan',
            'breadcrumb'  => [
                ['label' => 'Penjualan', 'url' => route('penjualan.index')],
                ['label' => 'Tagihan'],
            ],
            'tagihan'     => ErpDataService::tagihanPenjualan(),
        ]);
    }

    public function tagihanCreate()
    {
        return view('pages.penjualan.tagihan-create', [
            'currentPage' => 'penjualan.tagihan',
            'breadcrumb'  => [
                ['label' => 'Penjualan', 'url' => route('penjualan.index')],
                ['label' => 'Tagihan', 'url' => route('penjualan.tagihan_list')],
                ['label' => 'Buat Tagihan'],
            ],
            'salesOrders' => ErpDataService::salesOrders(),
            'kontak'      => ErpDataService::kontak(),
            'produk'      => ErpDataService::produk(),
            'gudang'      => ErpDataService::gudang(),
        ]);
    }

    public function tagihanShow(string $id)
    {
        $inv = collect(ErpDataService::tagihanPenjualan())->firstWhere('id', $id)
               ?? ErpDataService::tagihanPenjualan()[0];

        return view('pages.penjualan.tagihan-show', [
            'currentPage' => 'penjualan.tagihan',
            'breadcrumb'  => [
                ['label' => 'Penjualan', 'url' => route('penjualan.index')],
                ['label' => 'Tagihan', 'url' => route('penjualan.tagihan_list')],
                ['label' => $inv['id']],
            ],
            'inv'           => $inv,
            'soDetailItems' => ErpDataService::soDetailItems(),
        ]);
    }

    public function pengiriman(string $id)
    {
        $so = collect(ErpDataService::salesOrders())->firstWhere('id', $id)
              ?? ErpDataService::salesOrders()[0];

        return view('pages.penjualan.pengiriman', [
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
