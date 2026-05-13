<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Services\ErpDataService;

class LaporanController extends Controller
{
    public function index()
    {
        return view('laporan.index', [
            'currentPage'      => 'laporan',
            'breadcrumb'       => [['label' => 'Laporan Keuangan']],
            'chartOfAccounts'  => ErpDataService::chartOfAccounts(),
            'jurnal'           => ErpDataService::jurnal(),
            'kontak'           => ErpDataService::kontak(),
            'monthly'          => ErpDataService::monthly(),
            'kpis'             => ErpDataService::kpis(),
        ]);
    }
}
