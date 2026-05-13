<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Services\ErpDataService;

class DashboardController extends Controller
{
    public function index()
    {
        $data = ErpDataService::getData();

        return view('pages.dashboard', array_merge($data, [
            'currentPage' => 'dashboard',
            'breadcrumb'  => [['label' => 'Dashboard']],
        ]));
    }
}
