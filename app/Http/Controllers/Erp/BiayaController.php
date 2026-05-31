<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Services\ErpDataService;

class BiayaController extends Controller
{
    public function index()
    {
        $biaya = ErpDataService::biaya();
        $total = array_sum(array_column($biaya, 'jumlah'));

        return view('pages.biaya.index', [
            'currentPage' => 'biaya',
            'breadcrumb'  => [['label' => 'Biaya', 'url' => route('biaya.index')]],
            'biaya'       => $biaya,
            'totalBiaya'  => $total,
        ]);
    }

    public function show(string $id)
    {
        $biaya = ErpDataService::biayaDetail($id);

        if (!$biaya) {
            abort(404);
        }

        $subtotal = array_sum(array_column($biaya['items'], 'jumlah'));

        return view('pages.biaya.show', [
            'currentPage' => 'biaya',
            'breadcrumb'  => [
                ['label' => 'Biaya', 'url' => route('biaya.index')],
                ['label' => $biaya['nomor']],
            ],
            'biaya'    => $biaya,
            'subtotal' => $subtotal,
        ]);
    }

    public function create()
    {
        return view('pages.biaya.create', [
            'currentPage' => 'biaya',
            'breadcrumb'  => [
                ['label' => 'Biaya', 'url' => route('biaya.index')],
                ['label' => 'Tambah Biaya'],
            ],
            'akunKas' => ErpDataService::akunKas(),
            'kontak'  => ErpDataService::kontak(),
            'coa'     => collect(ErpDataService::chartOfAccounts())
                            ->filter(fn($a) => str_starts_with($a['kode'], '6'))
                            ->values()
                            ->toArray(),
        ]);
    }
}
