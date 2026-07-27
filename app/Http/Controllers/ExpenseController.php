<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\ExpenseStatus;

class ExpenseController extends Controller
{
    public function index()
    {
        $data = [
            'currentPage'    => 'biaya',
            'breadcrumb'     => [['label' => 'Biaya']],
            'status' => ExpenseStatus::dropdownOptions(),
        ];
        return view('expense.index', $data);
    }

    public function datatable(Request $request)
    {
       

        return response()->json();
    }
}
