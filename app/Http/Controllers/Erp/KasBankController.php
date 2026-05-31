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
        $akunKas = ErpDataService::akunKas();
        $akun    = collect($akunKas)->firstWhere('id', $id) ?? $akunKas[0];

        return view('pages.kasbank.show', [
            'currentPage'  => 'kasbank',
            'breadcrumb'   => [
                ['label' => 'Kas & Bank', 'url' => route('kasbank.index')],
                ['label' => $akun['nama']],
            ],
            'akun'         => $akun,
            'akunKas'      => $akunKas,
            'transaksiKas' => ErpDataService::transaksiKas(),
        ]);
    }

    public function kirimDana(string $id)
    {
        $akunKas = ErpDataService::akunKas();
        $akun    = collect($akunKas)->firstWhere('id', $id) ?? $akunKas[0];

        return view('pages.kasbank.kirim', [
            'currentPage' => 'kasbank',
            'breadcrumb'  => [
                ['label' => 'Kas & Bank', 'url' => route('kasbank.index')],
                ['label' => $akun['nama'], 'url' => route('kasbank.show', $id)],
                ['label' => 'Kirim Dana'],
            ],
            'akun'    => $akun,
            'akunKas' => $akunKas,
            'kontak'  => ErpDataService::kontak(),
            'coa'     => ErpDataService::chartOfAccounts(),
        ]);
    }

    public function terimaDana(string $id)
    {
        $akunKas = ErpDataService::akunKas();
        $akun    = collect($akunKas)->firstWhere('id', $id) ?? $akunKas[0];

        return view('pages.kasbank.terima', [
            'currentPage' => 'kasbank',
            'breadcrumb'  => [
                ['label' => 'Kas & Bank', 'url' => route('kasbank.index')],
                ['label' => $akun['nama'], 'url' => route('kasbank.show', $id)],
                ['label' => 'Terima Dana'],
            ],
            'akun'    => $akun,
            'akunKas' => $akunKas,
            'kontak'  => ErpDataService::kontak(),
            'coa'     => ErpDataService::chartOfAccounts(),
        ]);
    }
}
