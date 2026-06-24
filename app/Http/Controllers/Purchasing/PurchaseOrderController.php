<?php

namespace App\Http\Controllers\Purchasing;

use App\Enums\PaymentTerm;
use App\Http\Controllers\Controller;
use App\Services\PurchasingService;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $data = [
            'currentPage'    => 'pembelian',
            'breadcrumb'     => [['label' => 'Pembelian', 'url' => route('pembelian.index')]],
            'purchaseOrders' => [],
        ];
        return view('purchasing.purchase-order.index', $data);
    }

    public function create(PurchasingService $purchasingService)
    {
        $data = [
            'currentPage' => 'pembelian',
            'breadcrumb'  => [
                ['label' => 'Pembelian', 'url' => route('pembelian.index')],
                ['label' => 'Tambah PO'],
            ],
            'number' => $purchasingService->generatePONumber(),
            'produk' => [],
            'kontak' => [],
            'gudang' => [],
            'paymentTerms' => PaymentTerm::dropdownOptions()
        ];
        return view('purchasing.purchase-order.create', $data);
    }
}
