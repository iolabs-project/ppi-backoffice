<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Services\ErpDataService;

class MasterController extends Controller
{
    public function index()
    {
        return view('erp.master.index', [
            'currentPage'      => 'master',
            'breadcrumb'       => [['label' => 'Master Data']],
            'produk'           => ErpDataService::produk(),
            'kontak'           => ErpDataService::kontak(),
            'chartOfAccounts'  => ErpDataService::chartOfAccounts(),
            'gudang'           => ErpDataService::gudang(),
        ]);
    }
}
