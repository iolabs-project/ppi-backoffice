# Graph Report - ppi-backoffice  (2026-10-29)

## Corpus Check
- 368 files · ~145,165 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 51 file(s) not represented in the graph (top: .scss 29, (none) 17, .graphify-bak 1)

## Summary
- 2314 nodes · 4309 edges · 281 communities (97 shown, 184 thin omitted)
- Extraction: 91% EXTRACTED · 9% INFERRED · 0% AMBIGUOUS · INFERRED: 388 edges (avg confidence: 0.87)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `36b6e8de`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- package.json
- Illuminate\Http\Request
- PurchaseInvoiceService
- ProductService
- AccountCategoryEnum
- Illuminate\Foundation\Http\FormRequest
- AccountSettingEnum
- helpers.php
- CashController
- InventoryService
- JournalService
- UserService
- SalesOrderService.php
- ReportService.php
- Controller
- Illuminate\Database\Schema\Blueprint
- Sales Module Context
- Illuminate\Database\Eloquent\Model
- LogsModelActivity
- SalesInvoiceService
- Illuminate\Support\Facades\DB
- AccountReceivableService.php
- StockAdjustmentService.php
- .fetchSalesOrderByID
- PurchaseOrderService
- CashService.php
- Illuminate\Support\Collection
- DeliveryOrderService
- InventoryService.php
- GoodsReceiptService
- ExpenseService.php
- GoodsReceiptService.php
- AccountPayableService
- .finalizeGoodsReceipt
- SalesOrderStatus
- Models (`app/Models/`)
- PurchaseInvoiceService.php
- Spatie\Permission\Models\Permission
- PaymentTerm
- AccountService
- DeliveryOrder
- ExpenseService
- PurchaseOrder
- SalesInvoice
- QaFixesTest.php
- DeliveryOrderService.php
- ExpenseStatus
- AccountReceivableService
- AccountPayableController.php
- bootstrap/app.php
- User
- PurchaseOrderService.php
- UserFactory.php
- Illuminate\Support\Facades\Schema
- AccountPayableStatusEnum
- AccountReceivableStatusEnum
- Expense Module Context
- ExpenseFormRequest
- ProductOptionController.php
- .index
- JournalService.php
- ReportTypeEnum
- 2026_10_27_000000_add_voided_at_to_payment_tables.php
- OrderStatusGuardTest.php
- SalesOrder
- PurchaseOrderStatus
- Payment Module Context
- SalesInvoiceFormRequest
- AccountPayableService.php
- PurchaseInvoiceStatus
- ActivityLogEventEnum
- CashTransactionTypeEnum
- DeliveryOrderStatus
- GoodsReceiptStatus
- Purchasing Module Context
- GoodsReceiptItem
- UserPasswordChangeTest.php
- PurchaseOrderController.php
- CashTransactionStatusEnum
- abort_unless_can_become
- README.md
- CashFormRequest
- InventoryTransaction
- WarehouseTransfer
- composer.json
- require-dev
- scripts
- SalesInvoiceStatus
- SalesOrderController
- TotalsAndJournalTest.php
- GoodsReceiptFormRequestTest.php
- SalesInvoiceService.php
- AppServiceProvider.php
- require
- GoodsReceiptFormRequest
- ContactController
- CashReceiveFormRequest
- CashSendFormRequest
- 2026_08_29_201804_create_warehouse_transfers_table.php
- PurchaseOrderFormRequest
- AccountReceivableController.php
- LimitsAmounts.php
- InventoryTransactionTypeEnum
- config
- 2026_07_06_065924_create_sales_orders_table.php
- 2026_07_06_114637_create_delivery_orders_table.php
- CashTransferFormRequest
- SalesInvoiceItem
- PurchaseInvoiceFormRequest
- 2026_07_27_125404_create_expenses_table.php
- FormRequestRulesTest.php
- 2026_06_23_073413_create_product_batches_table.php
- .fetchGRItemsForPurchaseInvoice
- JournalEntryStatusEnum
- AccountSettingFormRequest
- StockAdjustmentFormRequest
- DeliveryOrderFormRequest
- Illuminate\Database\Migrations\Migration
- 2026_06_23_073840_create_inventory_transactions_table.php
- 2026_07_09_033233_create_account_mappings_table.php
- DeliveryOrderItem
- WarehouseTransferFormRequest
- psr-4
- 0001_01_01_000000_create_users_table.php
- 2026_06_23_090155_create_purchase_orders_table.php
- 2026_06_27_082234_create_goods_receipts_table.php
- 2026_06_29_124854_create_purchase_invoices_table.php
- CLAUDE.md
- Module Structure
- Enums (`app/Enums/`)
- ReceivablePaymentReferenceTypeEnum
- Validation Rules
- DashboardDummySeeder.php
- 0001_01_01_000001_create_cache_table.php
- 2026_06_27_201437_add_goods_receipt_item_id_column_to_product_batches_table.php
- 2026_07_08_064724_add_contact_id_column_to_users_table.php
- 2026_07_08_085520_create_journal_entries_table.php
- Routes (`routes/web.php`)
- 2026_08_29_201935_create_stock_adjustments_table.php
- 2026_10_22_000000_change_payment_terms_to_string.php
- InvoiceCancelAndPaymentTest.php
- 2026_06_22_123311_create_user_companies_table.php
- 2026_06_22_123430_create_chart_of_accounts_table.php
- 2026_06_23_072233_create_units_table.php
- 2026_06_23_072523_create_product_categories_table.php
- 2026_06_23_073116_create_products_table.php
- 2026_06_23_074424_create_contacts_table.php
- 2026_07_28_112509_create_account_settings_table.php
- 2026_08_04_143524_create_receivable_payments_table.php
- warehouse/show.blade.php
- purchase-order/create.blade.php
- expense/create.blade.php
- expense/edit.blade.php
- delivery-order/edit.blade.php
- sales-order/create.blade.php
- sales-order/edit.blade.php
- autoload-dev
- extra
- finance.account-payable.partials.payment-modal
- finance.account-receivable.partials.payment-modal
- finance.cash.partials.account-modal
- Illuminate\Foundation\Testing\TestCase
- receive/create.blade.php
- receive/edit.blade.php
- send/create.blade.php
- send/edit.blade.php
- transfer/create.blade.php
- transfer/edit.blade.php
- goods-receipt/edit.blade.php
- purchase-invoice/edit.blade.php
- purchase-order/edit.blade.php
- sales-invoice/edit.blade.php
- layouts.auth
- master.partials.modals.account-modal
- master.partials.modals.contact-modal
- master.partials.modals.permit-modal
- master.partials.modals.product-modal
- master.partials.modals.user-modal
- master.partials.modals.warehouse-modal
- dashboard/index.blade.php
- coming-soon.blade.php
- account-payable/index.blade.php
- account-receivable/index.blade.php
- cash/show.blade.php
- expense/index.blade.php
- contact/show.blade.php
- master/index.blade.php
- batch/show.blade.php
- product/show.blade.php
- stock-adjustment/create.blade.php
- warehouse-transfer/create.blade.php
- delivery-order.blade.php
- expense.blade.php
- goods-receipt.blade.php
- purchase-invoice.blade.php
- purchase-order.blade.php
- sales-invoice.blade.php
- sales-order.blade.php
- goods-receipt/index.blade.php
- goods-receipt/show.blade.php
- purchase-invoice/index.blade.php
- purchase-invoice/show.blade.php
- purchase-order/index.blade.php
- purchase-order/show.blade.php
- report/index.blade.php
- report/show.blade.php
- delivery-order/index.blade.php
- delivery-order/show.blade.php
- sales-invoice/index.blade.php
- sales-invoice/show.blade.php
- sales-order/index.blade.php
- sales-order/show.blade.php
- expense/show.blade.php

## God Nodes (most connected - your core abstractions)
1. `LogsModelActivity` - 87 edges
2. `Controller` - 53 edges
3. `AccountService` - 45 edges
4. `InventoryService` - 44 edges
5. `AccountSettingEnum` - 40 edges
6. `JournalService` - 40 edges
7. `ProductService` - 39 edges
8. `GoodsReceiptService` - 35 edges
9. `LimitsAmounts` - 34 edges
10. `PaymentTerm` - 31 edges

## Surprising Connections (you probably didn't know these)
- `Controller (`app/Http/Controllers/`)` --references--> `ExpenseController`  [INFERRED]
  context/expense.md → app/Http/Controllers/Finance/ExpenseController.php
- `Service (`app/Services/`)` --references--> `ExpenseService`  [INFERRED]
  context/expense.md → app/Services/ExpenseService.php
- `Enums (`app/Enums/`)` --references--> `DeliveryOrderStatus`  [INFERRED]
  context/sales.md → app/Enums/DeliveryOrderStatus.php
- `Enum (`app/Enums/`)` --references--> `ExpenseStatus`  [INFERRED]
  context/expense.md → app/Enums/ExpenseStatus.php
- `Enums (`app/Enums/`)` --references--> `GoodsReceiptStatus`  [INFERRED]
  context/purchasing.md → app/Enums/GoodsReceiptStatus.php

## Import Cycles
- None detected.

## Communities (281 total, 184 thin omitted)

### Community 0 - "package.json"
Cohesion: 0.05
Nodes (40): dependencies, sweetalert2@^11.26.25, xlsx@https://cdn.sheetjs.com/xlsx-0.20.3/x..., devDependencies, axios@^1.18.0, concurrently@^9.0.1, laravel-vite-plugin@^2.0.0, sass-embedded@^1.99.0 (+32 more)

### Community 1 - "Illuminate\Http\Request"
Cohesion: 0.08
Nodes (5): ReportController, ActivityLogService, ReportService, GET /user(), POST /logout()

### Community 4 - "ProductService"
Cohesion: 0.09
Nodes (5): ProductBatchController, ProductCategoryController, ProductController, ProductFormRequest, ProductService

### Community 5 - "AccountCategoryEnum"
Cohesion: 0.11
Nodes (18): AccountCategoryEnum, ACCOUNT_PAYABLE, ACCOUNT_RECEIVABLE, ACCUMULATED_DEPRECIATION_AMORTIZATION, CASH_BANK, COST_OF_GOODS_SOLD, CREDIT_CARD, EQUITY (+10 more)

### Community 7 - "AccountSettingEnum"
Cohesion: 0.07
Nodes (23): AccountSettingEnum, ACCOUNT_PAYABLE, ACCOUNT_RECEIVABLE, BANK, CASH, COGS, GRNI, INPUT_TAX (+15 more)

### Community 8 - "helpers.php"
Cohesion: 0.19
Nodes (4): fmt_doc(), fmt_doc_percent(), fmt_doc_qty(), {closure#1}()

### Community 10 - "InventoryService"
Cohesion: 0.15
Nodes (3): InventoryService, {closure#1}(), WarehouseTransferService

### Community 11 - "JournalService"
Cohesion: 0.16
Nodes (6): {closure#7}(), CashService, JournalService, {closure#7}(), SalesOrderService, Sales Order (`SalesOrderService`)

### Community 12 - "UserService"
Cohesion: 0.17
Nodes (3): UserController, UserFormRequest, UserService

### Community 15 - "Controller"
Cohesion: 0.10
Nodes (8): LoginController, Controller, DashboardController, StockAdjustmentController, WarehouseController, WarehouseTransferController, WarehouseFormRequest, WarehouseService

### Community 16 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.11
Nodes (14): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}() (+6 more)

### Community 17 - "Sales Module Context"
Cohesion: 0.12
Nodes (15): Configuration, Context Config (`config/context.php`), Database Schema (Migrations), Delivery Orders, Dependencies, External Services Used, Key Business Rules, Key Service Methods (+7 more)

### Community 18 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.07
Nodes (12): AccountCategory, AccountMapping, ChartOfAccount, Company, ExpenseItem, ProductCategory, ProductTransaction, Unit (+4 more)

### Community 19 - "LogsModelActivity"
Cohesion: 0.07
Nodes (8): CashTransactionCost, CashTransactionItem, LogsModelActivity, Contact, ExpenseCost, ProductStock, PurchaseOrderCost, WarehouseTransferItem

### Community 21 - "Illuminate\Support\Facades\DB"
Cohesion: 0.16
Nodes (6): AccountSettingSeeder, COASeeder, ContactSeeder, DatabaseSeeder, InventorySeeder, UserCompanySeeder

### Community 23 - "StockAdjustmentService.php"
Cohesion: 0.10
Nodes (5): AccountSetting, StockAdjustment, StockAdjustmentItem, {closure#1}(), StockAdjustmentService

### Community 24 - ".fetchSalesOrderByID"
Cohesion: 0.29
Nodes (5): Charge Fields, Cost/Charge Flow, Cost & Charge Handling, Cost Fields, Key Behaviors

### Community 25 - "PurchaseOrderService"
Cohesion: 0.12
Nodes (6): PurchaseOrderController, PurchaseOrderService, Controllers (`app/Http/Controllers/Purchasing/`), Module Structure, Purchase Order (`PurchaseOrderService`), Services (`app/Services/Purchasing/`)

### Community 26 - "CashService.php"
Cohesion: 0.11
Nodes (3): CashTransaction, {closure#6}(), {closure#7}()

### Community 27 - "Illuminate\Support\Collection"
Cohesion: 0.13
Nodes (5): PrintDocument, Delivery Order, Sales Invoice, Sales Order, Views (`resources/views/sales/`)

### Community 28 - "DeliveryOrderService"
Cohesion: 0.25
Nodes (3): DeliveryOrderController, DeliveryOrderService, Delivery Order (`DeliveryOrderService`)

### Community 29 - "InventoryService.php"
Cohesion: 0.08
Nodes (4): {closure#10}(), ProductBatchStock, {closure#12}(), {closure#9}()

### Community 31 - "ExpenseService.php"
Cohesion: 0.12
Nodes (9): PayablePaymentReferenceTypeEnum, EXPENSE, PURCHASE_INVOICE, Expense, {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}() (+1 more)

### Community 33 - "AccountPayableService"
Cohesion: 0.20
Nodes (3): AccountPayableController, AccountPayableService, {closure#6}()

### Community 34 - ".finalizeGoodsReceipt"
Cohesion: 0.11
Nodes (9): GoodsReceipt, {closure#15}(), {closure#7}(), {closure#9}(), Cost Fields, Cost Flow, Cost Handling, Goods Receipt (`GoodsReceiptService`) (+1 more)

### Community 35 - "SalesOrderStatus"
Cohesion: 0.22
Nodes (6): {closure#1}(), SalesOrderStatus, CANCELLED, CLOSED, DRAFT, OPEN

### Community 36 - "Models (`app/Models/`)"
Cohesion: 0.12
Nodes (5): SalesInvoiceCharge, SalesOrderCharge, SalesOrderCost, SalesOrderItem, Models (`app/Models/`)

### Community 37 - "PurchaseInvoiceService.php"
Cohesion: 0.10
Nodes (3): PurchaseInvoice, {closure#11}(), {closure#6}()

### Community 38 - "Spatie\Permission\Models\Permission"
Cohesion: 0.12
Nodes (6): RoleEnum, ACCOUNTING, ADMIN, SUPER_ADMIN, PermissionSeeder, RoleSeeder

### Community 39 - "PaymentTerm"
Cohesion: 0.12
Nodes (11): {closure#1}(), {closure#2}(), PaymentTerm, CASH, COD, CUSTOM, NET_14, NET_30 (+3 more)

### Community 40 - "AccountService"
Cohesion: 0.19
Nodes (4): AccountController, GoodsReceiptController, AccountFormRequest, AccountService

### Community 41 - "DeliveryOrder"
Cohesion: 0.13
Nodes (5): DeliveryOrder, {closure#10}(), {closure#5}(), From Delivery Order (`storeExpenseFromDeliveryOrder`), Inventory Integration (Delivery Order Finalize)

### Community 45 - "QaFixesTest.php"
Cohesion: 0.14
Nodes (6): run_once(), cashBasics(), {closure#2}(), {closure#4}(), {closure#6}(), {closure#8}()

### Community 46 - "DeliveryOrderService.php"
Cohesion: 0.08
Nodes (6): DeliveryOrderCost, DeliveryOrderItemBatch, 1. Sales Order (SO), 2. Delivery Order (DO), 3. Sales Invoice (SI), Business Flow

### Community 47 - "ExpenseStatus"
Cohesion: 0.14
Nodes (12): {closure#1}(), ExpenseStatus, CANCELLED, DRAFT, OPEN, PAID, PARTIAL, Controller (`app/Http/Controllers/`) (+4 more)

### Community 48 - "AccountReceivableService"
Cohesion: 0.20
Nodes (3): AccountReceivableController, AccountReceivableService, {closure#5}()

### Community 50 - "bootstrap/app.php"
Cohesion: 0.18
Nodes (4): EnsureUserIsActive, ReportPermissionMiddleware, {closure#1}(), {closure#2}()

### Community 52 - "PurchaseOrderService.php"
Cohesion: 0.12
Nodes (16): DocumentTotals, {closure#10}(), {closure#8}(), {closure#5}(), {closure#10}(), {closure#12}(), {closure#6}(), {closure#7}() (+8 more)

### Community 54 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.12
Nodes (4): {closure#1}(), {closure#1}(), {closure#1}(), {closure#1}()

### Community 55 - "AccountPayableStatusEnum"
Cohesion: 0.20
Nodes (7): AccountPayableStatusEnum, CANCELLED, DRAFT, OPEN, PAID, PARTIAL, {closure#1}()

### Community 56 - "AccountReceivableStatusEnum"
Cohesion: 0.20
Nodes (7): AccountReceivableStatusEnum, CANCELLED, DRAFT, OPEN, PAID, PARTIAL, {closure#1}()

### Community 57 - "Expense Module Context"
Cohesion: 0.12
Nodes (14): Amount Calculation, Auto-Creation from Other Modules, Database Schema, Draft status (`store` / `update`), Expense Module Context, From Goods Receipt (`storeExpenseFromGoodsReceipt`), Key Dependencies, Number Generation (+6 more)

### Community 60 - ".index"
Cohesion: 0.16
Nodes (5): AccountSettingController, MasterController, RoleController, AccountSettingService, RoleService

### Community 61 - "JournalService.php"
Cohesion: 0.29
Nodes (3): JournalEntryItem, {closure#1}(), {closure#2}()

### Community 62 - "ReportTypeEnum"
Cohesion: 0.15
Nodes (9): ReportTypeEnum, ACTIVITY_LOG, BALANCE_SHEET, CASH_FLOW, EXECUTIVE, JOURNAL, PAYABLE, PROFIT_LOSS (+1 more)

### Community 64 - "OrderStatusGuardTest.php"
Cohesion: 0.21
Nodes (4): {closure#5}(), {closure#7}(), {closure#9}(), orderBasics()

### Community 66 - "PurchaseOrderStatus"
Cohesion: 0.22
Nodes (6): {closure#1}(), PurchaseOrderStatus, CANCELLED, CLOSED, DRAFT, OPEN

### Community 67 - "Payment Module Context"
Cohesion: 0.17
Nodes (11): Business Logic, Database Schema, Invoice Status Affected, Overview, Payment Module Context, Payment Number Generation, Purchase Payments, Routes (+3 more)

### Community 70 - "PurchaseInvoiceStatus"
Cohesion: 0.20
Nodes (7): {closure#1}(), PurchaseInvoiceStatus, CANCELLED, DRAFT, OPEN, PAID, PARTIAL

### Community 72 - "ActivityLogEventEnum"
Cohesion: 0.20
Nodes (7): ActivityLogEventEnum, CANCELLED, CLOSED, DRAFTED, FINISHED, OPENED, POSTED

### Community 73 - "CashTransactionTypeEnum"
Cohesion: 0.24
Nodes (5): CashTransactionTypeEnum, RECEIVE, SEND, TRANSFER, {closure#1}()

### Community 74 - "DeliveryOrderStatus"
Cohesion: 0.22
Nodes (5): {closure#1}(), DeliveryOrderStatus, CANCELLED, DRAFT, FINISHED

### Community 75 - "GoodsReceiptStatus"
Cohesion: 0.24
Nodes (5): {closure#1}(), GoodsReceiptStatus, CANCELLED, DRAFT, FINISHED

### Community 76 - "Purchasing Module Context"
Cohesion: 0.08
Nodes (23): Configuration, Context Config (`config/context.php`), Database Schema (Migrations), Dependencies, Goods Receipt Update (non-draft), Goods Receipts, Goods Receipts (`purchasings.goods_receipts.*`), Inventory Integration (Goods Receipt Finalize) (+15 more)

### Community 77 - "GoodsReceiptItem"
Cohesion: 0.10
Nodes (5): GoodsReceiptItem, PurchaseInvoiceCost, PurchaseInvoiceItem, PurchaseOrderItem, Models (`app/Models/`)

### Community 78 - "UserPasswordChangeTest.php"
Cohesion: 0.36
Nodes (8): actingUserWithEditPermission(), {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}()

### Community 79 - "PurchaseOrderController.php"
Cohesion: 0.24
Nodes (6): BilledBy, INTERNAL, SUPPLIER, THIRD_PARTY, {closure#1}(), {closure#2}()

### Community 80 - "CashTransactionStatusEnum"
Cohesion: 0.24
Nodes (5): CashTransactionStatusEnum, CANCELLED, DRAFT, POSTED, {closure#1}()

### Community 82 - "README.md"
Cohesion: 0.22
Nodes (8): About Laravel, Code of Conduct, Contributing, Laravel Sponsors, Learning Laravel, License, Premium Partners, Security Vulnerabilities

### Community 84 - "InventoryTransaction"
Cohesion: 0.07
Nodes (8): GoodsReceiptCost, InventoryTransaction, Product, ProductBatch, 1. Purchase Order (PO), 2. Goods Receipt (GR), 3. Purchase Invoice (PI), Business Flow

### Community 86 - "composer.json"
Cohesion: 0.22
Nodes (8): description, keywords, license, minimum-stability, name, prefer-stable, $schema, type

### Community 87 - "require-dev"
Cohesion: 0.22
Nodes (9): require-dev, fakerphp/faker, laravel/pail, laravel/pint, laravel/sail, mockery/mockery, nunomaduro/collision, pestphp/pest (+1 more)

### Community 88 - "scripts"
Cohesion: 0.22
Nodes (9): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+1 more)

### Community 89 - "SalesInvoiceStatus"
Cohesion: 0.20
Nodes (7): {closure#1}(), SalesInvoiceStatus, CANCELLED, DRAFT, OPEN, PAID, PARTIAL

### Community 92 - "TotalsAndJournalTest.php"
Cohesion: 0.29
Nodes (5): {closure#3}(), {closure#4}(), {closure#6}(), {closure#8}(), journalBasics()

### Community 94 - "GoodsReceiptFormRequestTest.php"
Cohesion: 0.39
Nodes (5): {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), goodsReceiptErrors()

### Community 95 - "SalesInvoiceService.php"
Cohesion: 0.08
Nodes (4): JournalEntry, SalesInvoice, {closure#5}(), {closure#9}()

### Community 97 - "require"
Cohesion: 0.25
Nodes (8): require, laravel/framework, laravel/sanctum, laravel/tinker, php, spatie/laravel-activitylog, spatie/laravel-permission, tightenco/ziggy

### Community 99 - "ContactController"
Cohesion: 0.13
Nodes (3): ContactController, ContactFormRequest, ContactService

### Community 107 - "InventoryTransactionTypeEnum"
Cohesion: 0.20
Nodes (9): InventoryTransactionTypeEnum, ADJUSTMENT_MINUS, ADJUSTMENT_PLUS, COST_ADJUSTMENT, OPENING, PURCHASE, SALE, TRANSFER_IN (+1 more)

### Community 108 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 109 - "2026_07_06_065924_create_sales_orders_table.php"
Cohesion: 0.29
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}()

### Community 110 - "2026_07_06_114637_create_delivery_orders_table.php"
Cohesion: 0.29
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}()

### Community 114 - "2026_07_27_125404_create_expenses_table.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 115 - "FormRequestRulesTest.php"
Cohesion: 0.47
Nodes (3): {closure#2}(), {closure#3}(), rulesForRoute()

### Community 117 - ".fetchGRItemsForPurchaseInvoice"
Cohesion: 0.40
Nodes (4): Goods Receipt, Purchase Invoice, Purchase Order, Views (`resources/views/purchasing/`)

### Community 118 - "JournalEntryStatusEnum"
Cohesion: 0.28
Nodes (5): {closure#1}(), JournalEntryStatusEnum, CANCELLED, DRAFT, POSTED

### Community 122 - "Illuminate\Database\Migrations\Migration"
Cohesion: 0.15
Nodes (3): {closure#1}(), {closure#1}(), {closure#1}()

### Community 127 - "psr-4"
Cohesion: 0.33
Nodes (6): autoload, files, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 128 - "0001_01_01_000000_create_users_table.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 129 - "2026_06_23_090155_create_purchase_orders_table.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 130 - "2026_06_27_082234_create_goods_receipts_table.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 131 - "2026_06_29_124854_create_purchase_invoices_table.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 133 - "Module Structure"
Cohesion: 0.50
Nodes (4): Controllers (`app/Http/Controllers/Finance/`), Models (`app/Models/`), Module Structure, Services (`app/Services/Finance/`)

### Community 134 - "Enums (`app/Enums/`)"
Cohesion: 0.50
Nodes (4): Controllers (`app/Http/Controllers/Sales/`), Enums (`app/Enums/`), Module Structure, Services (`app/Services/Sales/`)

### Community 136 - "Validation Rules"
Cohesion: 0.50
Nodes (4): Delivery Order Update (non-draft), Sales Invoice Update (non-draft), Sales Order Store/Update, Validation Rules

### Community 144 - "Routes (`routes/web.php`)"
Cohesion: 0.50
Nodes (4): Delivery Orders (`sales.delivery_orders.*`), Routes (`routes/web.php`), Sales Invoices (`sales.sales_invoices.*`), Sales Orders (`sales.sales_orders.*`)

### Community 148 - "InvoiceCancelAndPaymentTest.php"
Cohesion: 0.31
Nodes (7): {closure#1}(), {closure#2}(), {closure#5}(), {closure#7}(), paymentAccounts(), paymentRequest(), salesInvoiceFixture()

### Community 161 - "warehouse/show.blade.php"
Cohesion: 0.50
Nodes (3): master.warehouse.partials.batch-table, master.warehouse.partials.stock-table, layouts.app

### Community 162 - "purchase-order/create.blade.php"
Cohesion: 0.50
Nodes (3): purchasing.purchase-order.partials.create.item-table, layouts.app, purchasing.partials.additional-cost-table

### Community 163 - "expense/create.blade.php"
Cohesion: 0.50
Nodes (3): finance.expense.partials.cost-table, finance.expense.partials.item-table, layouts.app

### Community 164 - "expense/edit.blade.php"
Cohesion: 0.50
Nodes (3): finance.expense.partials.cost-table, finance.expense.partials.item-table, layouts.app

### Community 165 - "delivery-order/edit.blade.php"
Cohesion: 0.50
Nodes (3): layouts.app, sales.partials.additional-cost-table, sales.delivery-order.partials.edit.item-table

### Community 166 - "sales-order/create.blade.php"
Cohesion: 0.50
Nodes (3): layouts.app, sales.partials.additional-charge-table, sales.partials.additional-cost-table

### Community 167 - "sales-order/edit.blade.php"
Cohesion: 0.50
Nodes (3): layouts.app, sales.partials.additional-charge-table, sales.partials.additional-cost-table

### Community 170 - "autoload-dev"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

### Community 171 - "extra"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

## Knowledge Gaps
- **213 isolated node(s):** `CASH_BANK`, `ACCOUNT_RECEIVABLE`, `INVENTORY`, `OTHER_CURRENT_ASSET`, `FIXED_ASSET` (+208 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1054 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **184 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `LogsModelActivity` connect `LogsModelActivity` to `Illuminate\Database\Eloquent\Model`, `AccountReceivableService.php`, `StockAdjustmentService.php`, `CashService.php`, `ExpenseService.php`, `.finalizeGoodsReceipt`, `Models (`app/Models/`)`, `PurchaseInvoiceService.php`, `DeliveryOrder`, `PurchaseOrder`, `DeliveryOrderService.php`, `User`, `JournalService.php`, `SalesOrder`, `AccountPayableService.php`, `DeliveryOrderStatus`, `GoodsReceiptItem`, `InventoryTransaction`, `WarehouseTransfer`, `SalesInvoiceService.php`, `SalesInvoiceItem`, `DeliveryOrderItem`?**
  _High betweenness centrality (0.036) - this node is a cross-community bridge._
- **What connects `CASH_BANK`, `ACCOUNT_RECEIVABLE`, `INVENTORY` to the rest of the system?**
  _213 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `package.json` be split into smaller, more focused modules?**
  _Cohesion score 0.050816696914700546 - nodes in this community are weakly interconnected._
- **Why does `GoodsReceiptStatus` connect `GoodsReceiptStatus` to `GoodsReceiptService.php`, `Illuminate\Foundation\Http\FormRequest`, `AccountService`, `Illuminate\Database\Eloquent\Model`, `PurchaseOrderService.php`, `InventoryService.php`?**
  _High betweenness centrality (0.028) - this node is a cross-community bridge._
- **Should `Illuminate\Http\Request` be split into smaller, more focused modules?**
  _Cohesion score 0.08163265306122448 - nodes in this community are weakly interconnected._
- **Why does `User` connect `User` to `OrderStatusGuardTest.php`, `Illuminate\Support\Facades\Auth`, `QaFixesTest.php`, `UserPasswordChangeTest.php`, `LogsModelActivity`, `InvoiceCancelAndPaymentTest.php`, `UserFactory.php`, `Illuminate\Support\Facades\DB`, `TotalsAndJournalTest.php`, `Illuminate\Support\Facades\DB`?**
  _High betweenness centrality (0.024) - this node is a cross-community bridge._
- **Should `web.php` be split into smaller, more focused modules?**
  _Cohesion score 0.045454545454545456 - nodes in this community are weakly interconnected._