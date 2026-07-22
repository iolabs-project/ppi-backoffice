<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Erp\DashboardController;
use App\Http\Controllers\Erp\PenjualanController;
use App\Http\Controllers\Erp\PembelianController;
use App\Http\Controllers\Erp\KasBankController;
use App\Http\Controllers\Erp\LaporanController;
use App\Http\Controllers\Erp\MasterController;
use App\Http\Controllers\Erp\BiayaController;
use App\Http\Controllers\Finance\AccountPayableController;
use App\Http\Controllers\Finance\AccountReceivableController;
use App\Http\Controllers\Master\AccountController;
use App\Http\Controllers\Master\ContactController;
use App\Http\Controllers\Master\ProductCategoryController;
use App\Http\Controllers\Master\ProductController;
use App\Http\Controllers\Master\UserController;
use App\Http\Controllers\Master\WarehouseController;
use App\Http\Controllers\Purchasing\GoodsReceiptController;
use App\Http\Controllers\Purchasing\PurchaseInvoiceController;
use App\Http\Controllers\Purchasing\PurchaseOrderController;
use App\Http\Controllers\Sale\SaleOrderController;
use App\Http\Controllers\Sales\DeliveryOrderController;
use App\Http\Controllers\Sales\SalesInvoiceController;
use App\Http\Controllers\Sales\SalesOrderController;

// ── Auth ─────────────────────────────────────────────────────────────────────

Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

Route::post('/logout', function (Request $request) {
    auth()->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

// ── ERP (protected) ──────────────────────────────────────────────────────────

Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Penjualan (Sales Orders)
    Route::prefix('penjualan')->name('penjualan.')->group(function () {
        Route::get('/',                  [PenjualanController::class, 'index'])->name('index');
        Route::get('/create',            [PenjualanController::class, 'create'])->name('create');
        Route::get('/pengiriman',        [PenjualanController::class, 'pengirimanList'])->name('pengiriman_list');
        Route::get('/tagihan',           [PenjualanController::class, 'tagihanList'])->name('tagihan_list');
        Route::get('/tagihan/create',    [PenjualanController::class, 'tagihanCreate'])->name('tagihan_create');
        Route::get('/tagihan/{id}',      [PenjualanController::class, 'tagihanShow'])->name('tagihan_show');
        Route::get('/{id}',              [PenjualanController::class, 'show'])->name('show');
        Route::get('/{id}/edit',         [PenjualanController::class, 'edit'])->name('edit');
        Route::get('/{id}/pengiriman',   [PenjualanController::class, 'pengiriman'])->name('pengiriman');
    });

    Route::prefix('purchasings')->name('purchasings.')->group(function () {
        Route::prefix('purchase-orders')->name('purchase_orders.')->controller(PurchaseOrderController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/datatable', 'datatable')->name('datatable');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');

            Route::prefix('/{id}')->group(function () {
                Route::get('/', 'show')->name('show');
                Route::get('/edit', 'edit')->name('edit');
                Route::put('/', 'update')->name('update');
                Route::post('/open', 'open')->name('open');
                Route::post('/close', 'close')->name('close');
                Route::post('/cancel', 'cancel')->name('cancel');
            });
        });

        Route::prefix('goods-receipts')->name('goods_receipts.')->controller(GoodsReceiptController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/datatable', 'datatable')->name('datatable');
            Route::post('/', 'store')->name('store');

            Route::prefix('/{id}')->group(function () {
                Route::get('/', 'show')->name('show');
                Route::get('/edit', 'edit')->name('edit');
                Route::put('/', 'update')->name('update');
                Route::post('/cancel', 'cancel')->name('cancel');
            });
        });

        Route::prefix('purchase-invoices')->name('purchase_invoices.')->controller(PurchaseInvoiceController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/datatable', 'datatable')->name('datatable');
            Route::post('/', 'store')->name('store');

            Route::prefix('/{id}')->group(function () {
                Route::get('/', 'show')->name('show');
                Route::get('/edit', 'edit')->name('edit');
                Route::put('/', 'update')->name('update');
                Route::post('/cancel', 'cancel')->name('cancel');
            });
        });
    });

    Route::prefix('sales')->name('sales.')->group(function () {
        Route::prefix('sales-orders')->name('sales_orders.')->controller(SalesOrderController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/datatable', 'datatable')->name('datatable');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');

            Route::prefix('/{id}')->group(function () {
                Route::get('/', 'show')->name('show');
                Route::get('/edit', 'edit')->name('edit');
                Route::put('/', 'update')->name('update');
                Route::post('/open', 'open')->name('open');
                Route::post('/close', 'close')->name('close');
                Route::post('/cancel', 'cancel')->name('cancel');
            });
        });

        Route::prefix('delivery-orders')->name('delivery_orders.')->controller(DeliveryOrderController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/datatable', 'datatable')->name('datatable');
            Route::post('/', 'store')->name('store');

            Route::prefix('/{id}')->group(function () {
                Route::get('/', 'show')->name('show');
                Route::get('/edit', 'edit')->name('edit');
                Route::put('/', 'update')->name('update');
                Route::post('/cancel', 'cancel')->name('cancel');
            });
        });

        Route::prefix('sales-invoices')->name('sales_invoices.')->controller(SalesInvoiceController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/datatable', 'datatable')->name('datatable');
            Route::post('/', 'store')->name('store');

            Route::prefix('/{id}')->group(function () {
                Route::get('/', 'show')->name('show');
                Route::get('/edit', 'edit')->name('edit');
                Route::put('/', 'update')->name('update');
                Route::post('/cancel', 'cancel')->name('cancel');
            });
        });
    });

    Route::prefix('finances')->name('finances.')->group(function () {
        Route::prefix('account-payables')->name('account_payables.')->controller(AccountPayableController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/datatable', 'datatable')->name('datatable');
            Route::get('/{id}', 'show')->name('show');
            Route::post('/{id}', 'store')->name('store');
        });
        Route::prefix('account-receivables')->name('account_receivables.')->controller(AccountReceivableController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/datatable', 'datatable')->name('datatable');
            Route::get('/{id}', 'show')->name('show');
            Route::post('/{id}', 'store')->name('store');
        });
    });

    // Pembelian (Purchase Orders)
    Route::prefix('pembelian')->name('pembelian.')->group(function () {
        Route::get('/',               [PembelianController::class, 'index'])->name('index');
        Route::get('/create',         [PembelianController::class, 'create'])->name('create');
        Route::get('/penerimaan',     [PembelianController::class, 'penerimaanList'])->name('penerimaan_list');
        Route::get('/tagihan-list',   [PembelianController::class, 'tagihanList'])->name('tagihan_list');
        Route::get('/tagihan/create', [PembelianController::class, 'tagihanCreate'])->name('tagihan_create');
        Route::get('/tagihan/{id}',   [PembelianController::class, 'tagihanShow'])->name('tagihan_show');
        Route::get('/{id}',           [PembelianController::class, 'show'])->name('show');
        Route::get('/{id}/edit',      [PembelianController::class, 'edit'])->name('edit');
        Route::get('/{id}/pengiriman', [PembelianController::class, 'pengiriman'])->name('pengiriman');
        Route::get('/{id}/penerimaan', [PembelianController::class, 'penerimaan'])->name('penerimaan');
        Route::get('/{id}/tagihan',   [PembelianController::class, 'tagihan'])->name('tagihan');
    });

    // Kas & Bank
    Route::prefix('kasbank')->name('kasbank.')->group(function () {
        Route::get('/',            [KasBankController::class, 'index'])->name('index');
        Route::get('/options',     [KasBankController::class, 'options'])->name('options');
        Route::get('/{id}/kirim',  [KasBankController::class, 'kirimDana'])->name('kirim');
        Route::get('/{id}/terima', [KasBankController::class, 'terimaDana'])->name('terima');
        Route::get('/{id}',        [KasBankController::class, 'show'])->name('show');
    });

    // Laporan Keuangan
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');

    // Master Data
    Route::get('/master',                         [MasterController::class, 'index'])->name('master.index');
    Route::get('/master/produk/{kode}',           [MasterController::class, 'showProduk'])->name('master.produk.show');
    Route::get('/master/kontak/{id}',             [MasterController::class, 'showKontak'])->name('master.kontak.show');
    Route::get('/master/gudang/{kode}',           [MasterController::class, 'showGudang'])->name('master.gudang.show');
    Route::get('/master/gudang/{kode}/transfer',  [MasterController::class, 'transferGudang'])->name('master.gudang.transfer');

    // Biaya
    Route::get('/biaya',        [BiayaController::class, 'index'])->name('biaya.index');
    Route::get('/biaya/create', [BiayaController::class, 'create'])->name('biaya.create');
    Route::get('/biaya/{id}',   [BiayaController::class, 'show'])->name('biaya.show');

    // Coming-soon placeholders
    Route::get('/pengaturan', function () {
        return view('coming-soon', [
            'currentPage' => 'pengaturan',
            'breadcrumb'  => [['label' => 'Pengaturan']],
            'title'       => 'Pengaturan',
            'description' => 'Konfigurasi sistem, pengguna, dan preferensi perusahaan.',
        ]);
    })->name('pengaturan.index');

    Route::get('/bantuan', function () {
        return view('coming-soon', [
            'currentPage' => 'bantuan',
            'breadcrumb'  => [['label' => 'Bantuan']],
            'title'       => 'Pusat Bantuan',
            'description' => 'Dokumentasi dan dukungan pengguna akan tersedia di sini.',
        ]);
    })->name('bantuan.index');

    Route::prefix('master')->name('master.')->group(function () {
        Route::prefix('contacts')->name('contacts.')->controller(ContactController::class)->group(function () {
            Route::get('/datatable', 'datatable')->name('datatable');
            Route::post('/', 'store')->name('store');
            Route::put('/{id}', 'update')->name('update');
            Route::post('/{id}/status', 'status')->name('status');
            Route::get('/options', 'options')->name('options');
        });

        Route::prefix('warehouses')->name('warehouses.')->controller(WarehouseController::class)->group(function () {
            Route::get('/datatable', 'datatable')->name('datatable');
            Route::post('/', 'store')->name('store');
            Route::put('/{id}', 'update')->name('update');
            Route::post('/{id}/status', 'status')->name('status');
            Route::get('/options', 'options')->name('options');
        });

        Route::prefix('products')->name('products.')->controller(ProductController::class)->group(function () {
            Route::prefix('categories')->name('categories.')->controller(ProductCategoryController::class)->group(function () {
                Route::post('/', 'store')->name('store');
                Route::put('/{id}', 'update')->name('update');
            });

            Route::get('/datatable', 'datatable')->name('datatable');
            Route::post('/', 'store')->name('store');
            Route::put('/{id}', 'update')->name('update');
            Route::post('/{id}/status', 'status')->name('status');
            Route::get('/options', 'options')->name('options');
        });

        Route::prefix('users')->name('users.')->controller(UserController::class)->group(function () {
            Route::get('/datatable', 'datatable')->name('datatable');
            Route::post('/', 'store')->name('store');
            Route::put('/{id}', 'update')->name('update');
            Route::post('/{id}/status', 'status')->name('status');
            Route::get('/options', 'options')->name('options');
        });

        Route::prefix('accounts')->name('accounts.')->controller(AccountController::class)->group(function () {
            Route::get('/datatable', 'datatable')->name('datatable');
            Route::post('/', 'store')->name('store');
            Route::put('/{id}', 'update')->name('update');
            Route::post('/{id}/status', 'status')->name('status');
        });
    });
});
