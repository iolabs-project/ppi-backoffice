<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function index()
    {
        $companyId = config('context.selected_company_id');
        $data = DashboardService::getData($companyId);

        return view('pages.dashboard', array_merge($data, [
            'currentPage' => 'dashboard',
            'breadcrumb'  => [['label' => 'Dashboard']],
        ]));
    }
}
