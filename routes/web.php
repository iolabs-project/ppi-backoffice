<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Finance\ExpenseController;
use App\Http\Controllers\Finance\AccountPayableController;
use App\Http\Controllers\Finance\AccountReceivableController;
use App\Http\Controllers\Finance\CashController;
use App\Http\Controllers\Master\AccountController;
use App\Http\Controllers\Master\AccountSettingController;
use App\Http\Controllers\Master\ContactController;
use App\Http\Controllers\Master\MasterController;
use App\Http\Controllers\Master\Product\ProductBatchController;
use App\Http\Controllers\Master\Product\ProductCategoryController;
use App\Http\Controllers\Master\Product\ProductController;
use App\Http\Controllers\Master\Product\ProductOptionController;
use App\Http\Controllers\Master\RoleController;
use App\Http\Controllers\Master\UserController;
use App\Http\Controllers\Master\Warehouse\StockAdjustmentController;
use App\Http\Controllers\Master\Warehouse\WarehouseController;
use App\Http\Controllers\Master\Warehouse\WarehouseTransferController;
use App\Http\Controllers\Purchasing\GoodsReceiptController;
use App\Http\Controllers\Purchasing\PurchaseInvoiceController;
use App\Http\Controllers\Purchasing\PurchaseOrderController;
use App\Http\Controllers\ReportController;
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

    Route::prefix('purchasings')->name('purchasings.')->group(function () {
        Route::prefix('purchase-orders')->name('purchase_orders.')->controller(PurchaseOrderController::class)->group(function () {
            Route::get('/', 'index')->middleware('permission:purchasing.purchase-orders.view')->name('index');
            Route::get('/datatable', 'datatable')->middleware('permission:purchasing.purchase-orders.view')->name('datatable');
            Route::get('/create', 'create')->middleware('permission:purchasing.purchase-orders.create')->name('create');
            Route::post('/', 'store')->middleware('permission:purchasing.purchase-orders.create')->name('store');

            Route::prefix('/{id}')->group(function () {
                Route::get('/', 'show')->middleware('permission:purchasing.purchase-orders.view')->name('show');
                Route::get('/edit', 'edit')->middleware('permission:purchasing.purchase-orders.edit')->name('edit');
                Route::put('/', 'update')->middleware('permission:purchasing.purchase-orders.edit')->name('update');
                Route::post('/open', 'open')->middleware('permission:purchasing.purchase-orders.edit')->name('open');
                Route::post('/close', 'close')->middleware('permission:purchasing.purchase-orders.edit')->name('close');
                Route::post('/cancel', 'cancel')->middleware('permission:purchasing.purchase-orders.delete')->name('cancel');
            });
        });

        Route::prefix('goods-receipts')->name('goods_receipts.')->controller(GoodsReceiptController::class)->group(function () {
            Route::get('/', 'index')->middleware('permission:purchasing.goods-receipts.view')->name('index');
            Route::get('/datatable', 'datatable')->middleware('permission:purchasing.goods-receipts.view')->name('datatable');
            Route::post('/', 'store')->middleware('permission:purchasing.goods-receipts.create')->name('store');

            Route::prefix('/{id}')->group(function () {
                Route::get('/', 'show')->middleware('permission:purchasing.goods-receipts.view')->name('show');
                Route::get('/edit', 'edit')->middleware('permission:purchasing.goods-receipts.edit')->name('edit');
                Route::put('/', 'update')->middleware('permission:purchasing.goods-receipts.edit')->name('update');
                Route::post('/cancel', 'cancel')->middleware('permission:purchasing.goods-receipts.delete')->name('cancel');
            });
        });

        Route::prefix('purchase-invoices')->name('purchase_invoices.')->controller(PurchaseInvoiceController::class)->group(function () {
            Route::get('/', 'index')->middleware('permission:purchasing.invoices.view')->name('index');
            Route::get('/datatable', 'datatable')->middleware('permission:purchasing.invoices.view')->name('datatable');
            Route::post('/', 'store')->middleware('permission:purchasing.invoices.create')->name('store');

            Route::prefix('/{id}')->group(function () {
                Route::get('/', 'show')->middleware('permission:purchasing.invoices.view')->name('show');
                Route::get('/edit', 'edit')->middleware('permission:purchasing.invoices.edit')->name('edit');
                Route::put('/', 'update')->middleware('permission:purchasing.invoices.edit')->name('update');
                Route::post('/cancel', 'cancel')->middleware('permission:purchasing.invoices.delete')->name('cancel');
            });
        });
    });

    Route::prefix('sales')->name('sales.')->group(function () {
        Route::prefix('sales-orders')->name('sales_orders.')->controller(SalesOrderController::class)->group(function () {
            Route::get('/', 'index')->middleware('permission:sales.sales-orders.view')->name('index');
            Route::get('/datatable', 'datatable')->middleware('permission:sales.sales-orders.view')->name('datatable');
            Route::get('/create', 'create')->middleware('permission:sales.sales-orders.create')->name('create');
            Route::post('/', 'store')->middleware('permission:sales.sales-orders.create')->name('store');

            Route::prefix('/{id}')->group(function () {
                Route::get('/', 'show')->middleware('permission:sales.sales-orders.view')->name('show');
                Route::get('/edit', 'edit')->middleware('permission:sales.sales-orders.edit')->name('edit');
                Route::put('/', 'update')->middleware('permission:sales.sales-orders.edit')->name('update');
                Route::post('/open', 'open')->middleware('permission:sales.sales-orders.edit')->name('open');
                Route::post('/close', 'close')->middleware('permission:sales.sales-orders.edit')->name('close');
                Route::post('/cancel', 'cancel')->middleware('permission:sales.sales-orders.delete')->name('cancel');
            });
        });

        Route::prefix('delivery-orders')->name('delivery_orders.')->controller(DeliveryOrderController::class)->group(function () {
            Route::get('/', 'index')->middleware('permission:sales.delivery-orders.view')->name('index');
            Route::get('/datatable', 'datatable')->middleware('permission:sales.delivery-orders.view')->name('datatable');
            Route::post('/', 'store')->middleware('permission:sales.delivery-orders.create')->name('store');

            Route::prefix('/{id}')->group(function () {
                Route::get('/', 'show')->middleware('permission:sales.delivery-orders.view')->name('show');
                Route::get('/edit', 'edit')->middleware('permission:sales.delivery-orders.edit')->name('edit');
                Route::put('/', 'update')->middleware('permission:sales.delivery-orders.edit')->name('update');
                Route::post('/cancel', 'cancel')->middleware('permission:sales.delivery-orders.delete')->name('cancel');
            });
        });

        Route::prefix('sales-invoices')->name('sales_invoices.')->controller(SalesInvoiceController::class)->group(function () {
            Route::get('/', 'index')->middleware('permission:sales.invoices.view')->name('index');
            Route::get('/datatable', 'datatable')->middleware('permission:sales.invoices.view')->name('datatable');
            Route::post('/', 'store')->middleware('permission:sales.invoices.create')->name('store');

            Route::prefix('/{id}')->group(function () {
                Route::get('/', 'show')->middleware('permission:sales.invoices.view')->name('show');
                Route::get('/edit', 'edit')->middleware('permission:sales.invoices.edit')->name('edit');
                Route::put('/', 'update')->middleware('permission:sales.invoices.edit')->name('update');
                Route::post('/cancel', 'cancel')->middleware('permission:sales.invoices.delete')->name('cancel');
            });
        });
    });

    Route::prefix('finances')->name('finances.')->group(function () {
        Route::prefix('account-payables')->name('account_payables.')->controller(AccountPayableController::class)->group(function () {
            Route::get('/', 'index')->middleware('permission:finances.payables.view')->name('index');
            Route::get('/datatable', 'datatable')->middleware('permission:finances.payables.view')->name('datatable');
            Route::get('/payment-datatable', 'paymentDatatable')->middleware('permission:finances.payables.view')->name('payment_datatable');
            Route::get('/{id}', 'show')->middleware('permission:finances.payables.view')->name('show');
            Route::post('/{id}', 'store')->middleware('permission:finances.payables.create')->name('store');
            Route::get('/{id}/edit', 'edit')->middleware('permission:finances.payables.edit')->name('edit');
        });
        Route::prefix('account-receivables')->name('account_receivables.')->controller(AccountReceivableController::class)->group(function () {
            Route::get('/', 'index')->middleware('permission:finances.receivables.view')->name('index');
            Route::get('/datatable', 'datatable')->middleware('permission:finances.receivables.view')->name('datatable');
            Route::get('/payment-datatable', 'paymentDatatable')->middleware('permission:finances.receivables.view')->name('payment_datatable');
            Route::get('/{id}', 'show')->middleware('permission:finances.receivables.view')->name('show');
            Route::post('/{id}', 'store')->middleware('permission:finances.receivables.create')->name('store');
            Route::get('/{id}/edit', 'edit')->middleware('permission:finances.receivables.edit')->name('edit');
        });
        Route::prefix('cash')->name('cash.')->controller(CashController::class)->group(function () {
            Route::get('/', 'index')->middleware('permission:finances.cash-bank.view')->name('index');
            Route::get('/datatable', 'datatable')->middleware('permission:finances.cash-bank.view')->name('datatable');
            Route::get('/create', 'create')->middleware('permission:finances.cash-bank.create')->name('create');
            Route::post('/', 'store')->middleware('permission:finances.cash-bank.create')->name('store');

            Route::prefix('/{id}')->group(function () {
                Route::get('/', 'show')->middleware('permission:finances.cash-bank.view')->name('show');

                Route::prefix('/transfer')->name('transfer.')->group(function () {
                    Route::get('/create', 'createTransfer')->middleware('permission:finances.cash-bank.create')->name('create');
                    Route::post('/', 'storeTransfer')->middleware('permission:finances.cash-bank.create')->name('store');
                    Route::prefix('/{transfer}')->group(function () {
                        Route::get('/', 'showTransfer')->middleware('permission:finances.cash-bank.view')->name('show');
                        Route::get('/edit', 'editTransfer')->middleware('permission:finances.cash-bank.edit')->name('edit');
                        Route::put('/', 'updateTransfer')->middleware('permission:finances.cash-bank.edit')->name('update');
                        Route::post('/cancel', 'cancelTransfer')->middleware('permission:finances.cash-bank.delete')->name('cancel');
                    });
                });

                Route::prefix('/send')->name('send.')->group(function () {
                    Route::get('/create', 'createSend')->middleware('permission:finances.cash-bank.create')->name('create');
                    Route::post('/', 'storeSend')->middleware('permission:finances.cash-bank.create')->name('store');
                    Route::prefix('/{send}')->group(function () {
                        Route::get('/', 'showSend')->middleware('permission:finances.cash-bank.view')->name('show');
                        Route::get('/edit', 'editSend')->middleware('permission:finances.cash-bank.edit')->name('edit');
                        Route::put('/', 'updateSend')->middleware('permission:finances.cash-bank.edit')->name('update');
                        Route::post('/cancel', 'cancelSend')->middleware('permission:finances.cash-bank.delete')->name('cancel');
                    });
                });

                Route::prefix('/receive')->name('receive.')->group(function () {
                    Route::get('/create', 'createReceive')->middleware('permission:finances.cash-bank.create')->name('create');
                    Route::post('/', 'storeReceive')->middleware('permission:finances.cash-bank.create')->name('store');
                    Route::prefix('/{receive}')->group(function () {
                        Route::get('/', 'showReceive')->middleware('permission:finances.cash-bank.view')->name('show');
                        Route::get('/edit', 'editReceive')->middleware('permission:finances.cash-bank.edit')->name('edit');
                        Route::put('/', 'updateReceive')->middleware('permission:finances.cash-bank.edit')->name('update');
                        Route::post('/cancel', 'cancelReceive')->middleware('permission:finances.cash-bank.delete')->name('cancel');
                    });
                });
            });
        });
    });

    Route::prefix('expenses')->name('expenses.')->controller(ExpenseController::class)->group(function () {
        Route::get('/', 'index')->middleware('permission:finances.expenses.view')->name('index');
        Route::get('/datatable', 'datatable')->middleware('permission:finances.expenses.view')->name('datatable');
        Route::get('/create', 'create')->middleware('permission:finances.expenses.create')->name('create');
        Route::post('/', 'store')->middleware('permission:finances.expenses.create')->name('store');

        Route::prefix('/{id}')->group(function () {
            Route::get('/', 'show')->middleware('permission:finances.expenses.view')->name('show');
            Route::get('/edit', 'edit')->middleware('permission:finances.expenses.edit')->name('edit');
            Route::put('/', 'update')->middleware('permission:finances.expenses.edit')->name('update');
            Route::post('/cancel', 'cancel')->middleware('permission:finances.expenses.delete')->name('cancel');
        });
    });

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
        Route::get('/', [MasterController::class, 'index'])
            ->middleware('permission:master.products.view|master.contacts.view|master.warehouses.view|master.accounts.view|master.users.view|master.roles.view')
            ->name('index');

        Route::prefix('contacts')->name('contacts.')->controller(ContactController::class)->group(function () {
            Route::get('/datatable', 'datatable')->middleware('permission:master.contacts.view')->name('datatable');
            Route::post('/', 'store')->middleware('permission:master.contacts.create')->name('store');
            Route::get('/options', 'options')->middleware('permission:master.contacts.view')->name('options');
            Route::get('/{id}', 'show')->middleware('permission:master.contacts.view')->name('show');
            Route::put('/{id}', 'update')->middleware('permission:master.contacts.edit')->name('update');
            Route::post('/{id}/status', 'status')->middleware('permission:master.contacts.edit')->name('status');
        });

        Route::prefix('warehouses')->name('warehouses.')->controller(WarehouseController::class)->group(function () {
            Route::get('/datatable', 'datatable')->middleware('permission:master.warehouses.view')->name('datatable');
            Route::get('/stock-datatable', 'datatableStock')->middleware('permission:master.warehouses.view')->name('stock_datatable');
            Route::get('/batch-datatable', 'datatableBatch')->middleware('permission:master.warehouses.view')->name('batch_datatable');
            Route::post('/', 'store')->middleware('permission:master.warehouses.create')->name('store');
            Route::get('/options', 'options')->middleware('permission:master.warehouses.view')->name('options');

            Route::prefix('{id}')->group(function () {
                Route::get('/', 'show')->middleware('permission:master.warehouses.view')->name('show');
                Route::put('/', 'update')->middleware('permission:master.warehouses.edit')->name('update');
                Route::post('/status', 'status')->middleware('permission:master.warehouses.edit')->name('status');

                Route::prefix('warehouse-transfers')->name('warehouse_transfers.')->controller(WarehouseTransferController::class)->group(function () {
                    Route::get('/create', 'create')->middleware('permission:master.warehouses.create')->name('create');
                    Route::post('/', 'store')->middleware('permission:master.warehouses.create')->name('store');
                });

                Route::prefix('stock-adjustments')->name('stock_adjustments.')->controller(StockAdjustmentController::class)->group(function () {
                    Route::get('/create', 'create')->middleware('permission:master.warehouses.create')->name('create');
                    Route::post('/', 'store')->middleware('permission:master.warehouses.create')->name('store');
                });
            });
        });

        Route::prefix('products')->name('products.')->controller(ProductController::class)->group(function () {
            Route::prefix('categories')->name('categories.')->controller(ProductCategoryController::class)->group(function () {
                Route::post('/', 'store')->middleware('permission:master.products.create')->name('store');
                Route::put('/{id}', 'update')->middleware('permission:master.products.edit')->name('update');
            });

            Route::prefix('/options')->name('options.')->controller(ProductOptionController::class)->group(function () {
                Route::get('/batches', 'batches')->middleware('permission:master.products.view')->name('batches');
            });


            Route::get('/datatable', 'datatable')->middleware('permission:master.products.view')->name('datatable');
            Route::get('/transaction-datatable', 'transactionDatatable')->middleware('permission:master.products.view')->name('transaction_datatable');
            Route::post('/', 'store')->middleware('permission:master.products.create')->name('store');
            Route::get('/options', 'options')->middleware('permission:master.products.view')->name('options');
            Route::get('/{id}', 'show')->middleware('permission:master.products.view')->name('show');
            Route::put('/{id}', 'update')->middleware('permission:master.products.edit')->name('update');
            Route::post('/{id}/status', 'status')->middleware('permission:master.products.edit')->name('status');

            Route::prefix('{id}/batches')->name('batches.')->controller(ProductBatchController::class)->group(function () {
                Route::get('/{batch_id}', 'show')->middleware('permission:master.products.view')->name('show');
            });
        });


        Route::prefix('users')->name('users.')->controller(UserController::class)->group(function () {
            Route::get('/datatable', 'datatable')->middleware('permission:master.users.view')->name('datatable');
            Route::post('/', 'store')->middleware('permission:master.users.create')->name('store');
            Route::get('/options', 'options')->middleware('permission:master.users.view')->name('options');
            Route::put('/{id}', 'update')->middleware('permission:master.users.edit')->name('update');
            Route::post('/{id}/status', 'status')->middleware('permission:master.users.edit')->name('status');
        });

        Route::prefix('accounts')->name('accounts.')->controller(AccountController::class)->group(function () {
            Route::get('/datatable', 'datatable')->middleware('permission:master.accounts.view')->name('datatable');
            Route::post('/', 'store')->middleware('permission:master.accounts.create')->name('store');
            Route::put('/{id}', 'update')->middleware('permission:master.accounts.edit')->name('update');
            Route::post('/{id}/status', 'status')->middleware('permission:master.accounts.edit')->name('status');
        });

        Route::prefix('account-settings')->name('account_settings.')->controller(AccountSettingController::class)->group(function () {
            Route::put('/', 'update')->middleware('permission:master.accounts.edit')->name('update');
        });

        Route::prefix('roles')->name('roles.')->controller(RoleController::class)->group(function () {
            Route::get('/', 'index')->middleware('permission:master.roles.view')->name('index');
            Route::post('/', 'store')->middleware('permission:master.roles.create')->name('store');
            Route::put('/{id}', 'update')->middleware('permission:master.roles.edit')->name('update');
            Route::put('/{id}/permissions', 'updatePermissions')->middleware('permission:master.roles.edit')->name('update_permissions');
        });
    });

    Route::prefix('reports')->name('reports.')->controller(ReportController::class)->group(function () {
        Route::get('/', 'index')
            ->middleware('permission:reports.balance-sheet.view|reports.cash-flow.view|reports.profit-loss.view|reports.executive.view|reports.receivable.view|reports.payable.view|reports.journal.view')
            ->name('index');
        Route::get('/profit-loss/datatable', 'profitLossDatatable')->middleware('permission:reports.profit-loss.view')->name('profit_loss.datatable');
        Route::get('/balance-sheet/datatable', 'balanceSheetDatatable')->middleware('permission:reports.balance-sheet.view')->name('balance_sheet.datatable');
        Route::get('/cash-flow/datatable', 'cashFlowDatatable')->middleware('permission:reports.cash-flow.view')->name('cash_flow.datatable');
        Route::get('/executive/datatable', 'executiveDatatable')->middleware('permission:reports.executive.view')->name('executive.datatable');
        Route::get('/receivable/datatable', 'receivableDatatable')->middleware('permission:reports.receivable.view')->name('receivable.datatable');
        Route::get('/payable/datatable', 'payableDatatable')->middleware('permission:reports.payable.view')->name('payable.datatable');
        Route::get('/journal/datatable', 'journalDatatable')->middleware('permission:reports.journal.view')->name('journal.datatable');
        Route::get('/general-ledger/datatable', 'generalLedgerDatatable')->middleware('permission:reports.journal.view')->name('general_ledger.datatable');
        Route::get('/activity-log/datatable', 'activityLogDatatable')->middleware('permission:reports.activity-log.view')->name('activity_log.datatable');
        Route::get('/{id}', 'show')->middleware('report.permission')->name('show');
    });
});
