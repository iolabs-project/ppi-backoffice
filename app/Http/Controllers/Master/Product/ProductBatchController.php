<?php

namespace App\Http\Controllers\Master\Product;

use App\Http\Controllers\Controller;
use App\Services\Master\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductBatchController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function show(int $id, int $batchID)
    {
        $product = $this->productService->fetchProductByID($id);
        $batch = $this->productService->fetchProductBatchByID($batchID);
        $data = [
            'currentPage'      => 'master',
            'breadcrumb'       => [
                ['label' => 'Master Data', 'url' => route('master.index')],
                ['label' => 'Produk', 'url' => route('master.products.show', $id)],
                ['label' => 'Batch',],
            ],
            'product'          => $product,
            'batch'            => $batch,
        ];

        return view('master.product.batch.show', $data);
    }
}
