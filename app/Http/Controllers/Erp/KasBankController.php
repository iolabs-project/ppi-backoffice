<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Services\ErpDataService;

class KasBankController extends Controller
{
    public function index()
    {
        $akunKas = ErpDataService::akunKas();
        $total   = array_sum(array_column($akunKas, 'saldo'));
        $transaksiKas = ErpDataService::transaksiKas();

        return view('pages.kasbank.index', [
            'currentPage' => 'kasbank',
            'breadcrumb'  => [['label' => 'Kas & Bank', 'url' => route('kasbank.index')]],
            'akunKas'     => $akunKas,
            'totalSaldo'  => $total,
            'transaksiKas'=> $transaksiKas,
        ]);
    }

    public function show(string $id)
    {
        $akun = collect(ErpDataService::akunKas())->firstWhere('id', $id)
                ?? ErpDataService::akunKas()[0];

        return view('pages.kasbank.show', [
            'currentPage'  => 'kasbank',
            'breadcrumb'   => [
                ['label' => 'Kas & Bank', 'url' => route('kasbank.index')],
                ['label' => $akun['nama']],
            ],
            'akun'         => $akun,
            'transaksiKas' => ErpDataService::transaksiKas(),
        ]);
    }
}
