# Graph Report - ppi-backoffice  (2026-11-09)

## Corpus Check
- 373 files · ~154,723 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 52 file(s) not represented in the graph (top: .scss 30, (none) 17, .graphify-bak 1)

## Summary
- 2194 nodes · 4211 edges · 266 communities (90 shown, 176 thin omitted)
- Extraction: 97% EXTRACTED · 3% INFERRED · 0% AMBIGUOUS · INFERRED: 134 edges (avg confidence: 0.92)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `a6b1240e`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- package.json
- PurchaseInvoice
- web.php
- PurchaseInvoiceService
- ProductService
- Controller
- Illuminate\Foundation\Http\FormRequest
- LimitsAmounts
- helpers.php
- CascadeCancelTest.php
- InventoryService
- PurchaseOrderService.php
- UserService
- ReportService
- ReportService.php
- ProductOptionController.php
- Illuminate\Database\Schema\Blueprint
- Sales Module Context
- UserService.php
- ExpenseService.php
- SalesInvoiceService
- Illuminate\Support\Facades\DB
- AccountReceivableController
- StockAdjustmentService.php
- ProductController
- PurchaseOrderService
- CashService
- Illuminate\Support\Collection
- DeliveryOrder
- InventoryService.php
- GoodsReceipt
- WarehouseTransferController.php
- GoodsReceiptService.php
- ProductService.php
- CashController
- SalesInvoiceService.php
- FormRequestRulesTest.php
- AccountPayableService.php
- JournalService
- PaymentTerm
- SalesOrderService.php
- ProductBatch
- ExpenseController
- CashSendFormRequest
- SalesInvoice
- QaFixesTest.php
- DeliveryOrderService.php
- ExpenseStatus
- devDependencies
- AccountPayableController.php
- bootstrap/app.php
- AccountSetting
- PurchaseInvoiceService.php
- ActivityLogTest.php
- Illuminate\Database\Migrations\Migration
- ProductStock
- scripts
- Expense Module Context
- GoodsReceiptItem
- AccountSettingEnum
- form-validation.js
- Illuminate\Support\Facades\Schema
- SalesOrderFormRequest
- User
- PurchaseOrderController
- CashTransferFormRequest
- ActivityLogEventEnum
- Illuminate\Validation\ValidationException
- DeliveryOrderStatus
- GoodsReceiptStatus
- Purchasing Module Context
- UserPasswordChangeTest.php
- CashTransactionStatusEnum
- BilledBy
- README.md
- CashFormRequest
- InventoryTransaction
- WarehouseTransfer
- composer.json
- require-dev
- scripts
- SalesOrderController
- TotalsAndJournalTest.php
- Illuminate\Http\Request
- GoodsReceiptFormRequestTest.php
- Illuminate\Database\Eloquent\Model
- AccountService
- require
- LogsModelActivity
- ContactService
- Cost & Charge Handling
- Illuminate\Support\Facades\Auth
- PurchaseOrderFormRequest
- dependencies
- LimitsAmounts.php
- CashReceiveFormRequest
- config
- 2026_07_06_065924_create_sales_orders_table.php
- 2026_07_06_114637_create_delivery_orders_table.php
- StockPageTest.php
- export.js
- PurchaseInvoiceFormRequest
- 2026_07_27_125404_create_expenses_table.php
- 2026_06_23_073413_create_product_batches_table.php
- WarehouseController
- JournalEntryStatusEnum
- console.php
- app.js
- 2026_06_22_121951_create_companies_table.php
- 2026_06_23_073840_create_inventory_transactions_table.php
- 2026_07_09_033233_create_account_mappings_table.php
- psr-4
- 0001_01_01_000000_create_users_table.php
- 2026_06_23_090155_create_purchase_orders_table.php
- 2026_06_27_082234_create_goods_receipts_table.php
- 2026_06_29_124854_create_purchase_invoices_table.php
- CLAUDE.md
- GoodsReceiptService
- JournalEntry
- logging.php
- sanctum.php
- 0001_01_01_000001_create_cache_table.php
- 2026_06_27_201437_add_goods_receipt_item_id_column_to_product_batches_table.php
- 2026_07_08_064724_add_contact_id_column_to_users_table.php
- 2026_07_08_085520_create_journal_entries_table.php
- 2026_08_29_201935_create_stock_adjustments_table.php
- 2026_10_22_000000_change_payment_terms_to_string.php
- 2026_06_22_123311_create_user_companies_table.php
- 2026_06_22_123430_create_chart_of_accounts_table.php
- contact.js
- 2026_06_23_072233_create_units_table.php
- 2026_06_23_072523_create_product_categories_table.php
- 2026_06_23_073116_create_products_table.php
- 2026_06_23_074424_create_contacts_table.php
- 2026_07_28_112509_create_account_settings_table.php
- 2026_08_09_011515_create_cash_transactions_table.php
- 2026_08_04_143524_create_receivable_payments_table.php
- warehouse/show.blade.php
- purchase-order/create.blade.php
- expense/create.blade.php
- expense/edit.blade.php
- delivery-order/edit.blade.php
- sales-order/create.blade.php
- sales-order/edit.blade.php
- abort_unless_can_become
- artisan
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
- master.partials.modals.account-modal
- master.partials.modals.contact-modal
- master.partials.modals.permit-modal
- master.partials.modals.product-modal
- master.partials.modals.user-modal
- master.partials.modals.warehouse-modal
- 2026_08_04_143434_create_payable_payments_table.php

## God Nodes (most connected - your core abstractions)
1. `LogsModelActivity` - 87 edges
2. `Controller` - 55 edges
3. `User` - 53 edges
4. `InventoryService` - 52 edges
5. `AccountService` - 45 edges
6. `JournalService` - 42 edges
7. `SalesInvoice` - 41 edges
8. `ProductService` - 39 edges
9. `PaymentTerm` - 37 edges
10. `JournalEntry` - 37 edges

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

## Communities (266 total, 176 thin omitted)

### Community 0 - "package.json"
Cohesion: 0.15
Nodes (12): private, $schema, type, axios, concurrently, laravel-vite-plugin, sass-embedded, sweetalert2 (+4 more)

### Community 1 - "PurchaseInvoice"
Cohesion: 0.11
Nodes (3): PurchaseInvoice, PurchaseInvoiceItem, {closure#5}()

### Community 3 - "PurchaseInvoiceService"
Cohesion: 0.11
Nodes (8): {closure#1}(), PurchaseInvoiceStatus, PurchaseInvoiceController, PurchaseInvoiceService, Business Logic, Invoice Status Affected, Payment Number Generation, Store Payment Flow

### Community 4 - "ProductService"
Cohesion: 0.14
Nodes (3): ProductBatchController, Product, ProductService

### Community 5 - "Controller"
Cohesion: 0.16
Nodes (5): LoginController, Controller, MasterController, ProductCategoryController, Illuminate\Validation\Rules\Unique

### Community 6 - "Illuminate\Foundation\Http\FormRequest"
Cohesion: 0.24
Nodes (4): Illuminate\Contracts\Validation\ValidationRule, Illuminate\Foundation\Http\FormRequest, Illuminate\Validation\Rule, Override

### Community 7 - "LimitsAmounts"
Cohesion: 0.15
Nodes (4): LimitsAmounts, AccountReceivableFormRequest, StockAdjustmentFormRequest, Illuminate\Validation\Rules\Enum

### Community 8 - "helpers.php"
Cohesion: 0.19
Nodes (4): fmt_doc(), fmt_doc_percent(), fmt_doc_qty(), {closure#1}()

### Community 9 - "CascadeCancelTest.php"
Cohesion: 0.23
Nodes (9): cascadeBasics(), {closure#2}(), {closure#3}(), {closure#5}(), {closure#7}(), {closure#8}(), {closure#9}(), finishedDeliveryFixture() (+1 more)

### Community 11 - "PurchaseOrderService.php"
Cohesion: 0.09
Nodes (4): PurchaseOrder, {closure#12}(), {closure#6}(), {closure#9}()

### Community 12 - "UserService"
Cohesion: 0.19
Nodes (3): UserController, UserFormRequest, UserService

### Community 16 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.12
Nodes (14): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}() (+6 more)

### Community 17 - "Sales Module Context"
Cohesion: 0.07
Nodes (26): 1. Sales Order (SO), 2. Delivery Order (DO), 3. Sales Invoice (SI), Business Flow, Configuration, Context Config (`config/context.php`), Database Schema (Migrations), Delivery Order Update (non-draft) (+18 more)

### Community 18 - "UserService.php"
Cohesion: 0.32
Nodes (3): UserCompany, {closure#4}(), {closure#5}()

### Community 19 - "ExpenseService.php"
Cohesion: 0.20
Nodes (6): ExpenseCost, ExpenseItem, {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}()

### Community 20 - "SalesInvoiceService"
Cohesion: 0.08
Nodes (9): {closure#1}(), SalesInvoiceStatus, SalesInvoiceController, SalesInvoiceFormRequest, SalesInvoiceService, Controllers (`app/Http/Controllers/Sales/`), Enums (`app/Enums/`), Module Structure (+1 more)

### Community 21 - "Illuminate\Support\Facades\DB"
Cohesion: 0.05
Nodes (18): RoleEnum, Carbon\Carbon, AccountSettingSeeder, COASeeder, ContactSeeder, DashboardDummySeeder, DatabaseSeeder, InventorySeeder (+10 more)

### Community 22 - "AccountReceivableController"
Cohesion: 0.09
Nodes (15): AccountReceivableStatusEnum, {closure#1}(), AccountReceivableController, Controllers (`app/Http/Controllers/Finance/`), Database Schema, Key Conventions, Models (`app/Models/`), Module Structure (+7 more)

### Community 23 - "StockAdjustmentService.php"
Cohesion: 0.20
Nodes (4): {closure#10}(), ProductBatchStock, {closure#1}(), StockAdjustmentService

### Community 25 - "PurchaseOrderService"
Cohesion: 0.12
Nodes (8): PurchaseOrderItem, PurchaseOrderService, Controllers (`app/Http/Controllers/Purchasing/`), Goods Receipt, Models (`app/Models/`), Module Structure, Purchase Order (`PurchaseOrderService`), Services (`app/Services/Purchasing/`)

### Community 26 - "CashService"
Cohesion: 0.06
Nodes (6): CashTransaction, CashTransactionCost, CashTransactionItem, CashService, {closure#6}(), {closure#7}()

### Community 27 - "Illuminate\Support\Collection"
Cohesion: 0.17
Nodes (5): PrintDocument, Sales Invoice, Sales Order, Views (`resources/views/sales/`), Illuminate\Support\Collection

### Community 28 - "DeliveryOrder"
Cohesion: 0.09
Nodes (6): DeliveryOrderController, DeliveryOrderFormRequest, DeliveryOrder, DeliveryOrderService, From Delivery Order (`storeExpenseFromDeliveryOrder`), Delivery Order (`DeliveryOrderService`)

### Community 29 - "InventoryService.php"
Cohesion: 0.07
Nodes (3): InventoryTransactionTypeEnum, {closure#15}(), {closure#18}()

### Community 31 - "WarehouseTransferController.php"
Cohesion: 0.19
Nodes (3): WarehouseTransferController, WarehouseTransferFormRequest, WarehouseTransferService

### Community 32 - "GoodsReceiptService.php"
Cohesion: 0.08
Nodes (8): DocumentTotals, {closure#10}(), {closure#13}(), {closure#8}(), {closure#10}(), {closure#7}(), {closure#6}(), {closure#7}()

### Community 35 - "SalesInvoiceService.php"
Cohesion: 0.11
Nodes (7): {closure#1}(), SalesOrderStatus, {closure#5}(), {closure#6}(), {closure#9}(), Key Service Methods, Sales Invoice (`SalesInvoiceService`)

### Community 36 - "FormRequestRulesTest.php"
Cohesion: 0.22
Nodes (4): AccountPayableFormRequest, {closure#2}(), {closure#3}(), rulesForRoute()

### Community 37 - "AccountPayableService.php"
Cohesion: 0.08
Nodes (6): PayablePaymentReferenceTypeEnum, Expense, PayablePayment, AccountPayableService, {closure#6}(), {closure#7}()

### Community 39 - "PaymentTerm"
Cohesion: 0.17
Nodes (4): {closure#1}(), {closure#2}(), PaymentTerm, {closure#4}()

### Community 40 - "SalesOrderService.php"
Cohesion: 0.06
Nodes (9): SalesOrder, SalesOrderItem, {closure#10}(), {closure#6}(), {closure#8}(), {closure#9}(), SalesOrderService, Delivery Order (+1 more)

### Community 44 - "SalesInvoice"
Cohesion: 0.06
Nodes (15): ReceivablePaymentReferenceTypeEnum, DashboardController, ReceivablePayment, SalesInvoice, DashboardService, AccountReceivableService, {closure#5}(), {closure#6}() (+7 more)

### Community 45 - "QaFixesTest.php"
Cohesion: 0.13
Nodes (8): Closure, run_once(), cashBasics(), {closure#13}(), {closure#2}(), {closure#4}(), {closure#6}(), {closure#8}()

### Community 46 - "DeliveryOrderService.php"
Cohesion: 0.08
Nodes (4): DeliveryOrderItem, DeliveryOrderItemBatch, {closure#11}(), {closure#5}()

### Community 47 - "ExpenseStatus"
Cohesion: 0.18
Nodes (7): {closure#1}(), ExpenseStatus, Controller (`app/Http/Controllers/`), Enum (`app/Enums/`), Models (`app/Models/`), Module Structure, Service (`app/Services/`)

### Community 48 - "devDependencies"
Cohesion: 0.25
Nodes (8): devDependencies, axios, concurrently, laravel-vite-plugin, sass-embedded, tailwindcss, @tailwindcss/vite, vite

### Community 49 - "AccountPayableController.php"
Cohesion: 0.18
Nodes (3): AccountPayableStatusEnum, {closure#1}(), AccountPayableController

### Community 50 - "bootstrap/app.php"
Cohesion: 0.18
Nodes (10): EnsureUserIsActive, ReportPermissionMiddleware, {closure#1}(), {closure#2}(), Closure, Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware (+2 more)

### Community 52 - "PurchaseInvoiceService.php"
Cohesion: 0.12
Nodes (3): NumberingService, {closure#11}(), {closure#6}()

### Community 53 - "ActivityLogTest.php"
Cohesion: 0.12
Nodes (9): Company, UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Facades\Hash, Illuminate\Support\Str, Pdo\Mysql, static, {closure#1}() (+1 more)

### Community 54 - "Illuminate\Database\Migrations\Migration"
Cohesion: 0.12
Nodes (4): {closure#1}(), {closure#1}(), {closure#1}(), Illuminate\Database\Migrations\Migration

### Community 56 - "scripts"
Cohesion: 0.67
Nodes (3): scripts, build, dev

### Community 57 - "Expense Module Context"
Cohesion: 0.12
Nodes (14): Amount Calculation, Auto-Creation from Other Modules, Database Schema, Draft status (`store` / `update`), Expense Module Context, From Goods Receipt (`storeExpenseFromGoodsReceipt`), Key Dependencies, Number Generation (+6 more)

### Community 59 - "GoodsReceiptItem"
Cohesion: 0.13
Nodes (8): GoodsReceiptCost, GoodsReceiptItem, {closure#7}(), {closure#9}(), Goods Receipt (`GoodsReceiptService`), Inventory Integration (Goods Receipt Finalize), Key Service Methods, Purchase Invoice (`PurchasingService`)

### Community 60 - "AccountSettingEnum"
Cohesion: 0.17
Nodes (4): AccountSettingEnum, AccountSettingController, AccountSettingFormRequest, AccountSettingService

### Community 61 - "form-validation.js"
Cohesion: 0.42
Nodes (10): clearErrors(), escapeHtml(), isEmpty(), isVisible(), markField(), recheckRequired(), showServerErrors(), unmarkField() (+2 more)

### Community 63 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.15
Nodes (5): {closure#1}(), {closure#1}(), {closure#1}(), {closure#2}(), Illuminate\Support\Facades\Schema

### Community 65 - "User"
Cohesion: 0.11
Nodes (12): User, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Spatie\Permission\Traits\HasRoles, {closure#10}(), {closure#12}(), {closure#13}() (+4 more)

### Community 66 - "PurchaseOrderController"
Cohesion: 0.15
Nodes (3): {closure#1}(), PurchaseOrderStatus, PurchaseOrderController

### Community 73 - "Illuminate\Validation\ValidationException"
Cohesion: 0.09
Nodes (10): AccountCategoryEnum, CashTransactionTypeEnum, {closure#1}(), StockController, StockAdjustmentController, Warehouse, WarehouseService, Illuminate\Support\Facades\Log (+2 more)

### Community 76 - "Purchasing Module Context"
Cohesion: 0.06
Nodes (31): 1. Purchase Order (PO), 2. Goods Receipt (GR), 3. Purchase Invoice (PI), Business Flow, Configuration, Context Config (`config/context.php`), Cost Fields, Cost Flow (+23 more)

### Community 78 - "UserPasswordChangeTest.php"
Cohesion: 0.27
Nodes (10): put, Spatie\Permission\Models\Role, actingUserWithEditPermission(), {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}() (+2 more)

### Community 81 - "BilledBy"
Cohesion: 0.27
Nodes (3): BilledBy, {closure#1}(), {closure#2}()

### Community 82 - "README.md"
Cohesion: 0.22
Nodes (8): About Laravel, Code of Conduct, Contributing, Laravel Sponsors, Learning Laravel, License, Premium Partners, Security Vulnerabilities

### Community 85 - "WarehouseTransfer"
Cohesion: 0.16
Nodes (3): WarehouseTransfer, WarehouseTransferItem, {closure#1}()

### Community 86 - "composer.json"
Cohesion: 0.22
Nodes (8): description, keywords, license, minimum-stability, name, prefer-stable, $schema, type

### Community 87 - "require-dev"
Cohesion: 0.22
Nodes (9): require-dev, fakerphp/faker, laravel/pail, laravel/pint, laravel/sail, mockery/mockery, nunomaduro/collision, pestphp/pest (+1 more)

### Community 88 - "scripts"
Cohesion: 0.22
Nodes (9): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+1 more)

### Community 92 - "TotalsAndJournalTest.php"
Cohesion: 0.25
Nodes (6): {closure#2}(), {closure#3}(), {closure#4}(), {closure#6}(), {closure#8}(), journalBasics()

### Community 93 - "Illuminate\Http\Request"
Cohesion: 0.10
Nodes (7): ReportTypeEnum, RoleController, ReportController, ActivityLogService, RoleService, Illuminate\Http\Request, Spatie\Activitylog\Models\Activity

### Community 94 - "GoodsReceiptFormRequestTest.php"
Cohesion: 0.13
Nodes (12): App\Http\Controllers\API\Master\ContactController, AppServiceProvider, Illuminate\Support\Facades\Gate, Illuminate\Support\Facades\Route, Illuminate\Support\Facades\Validator, Illuminate\Support\ServiceProvider, GET /user(), {closure#2}() (+4 more)

### Community 95 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.09
Nodes (10): AccountMapping, DeliveryOrderCost, SalesInvoiceCharge, SalesInvoiceItem, SalesOrderCharge, SalesOrderCost, Unit, {closure#7}() (+2 more)

### Community 96 - "AccountService"
Cohesion: 0.11
Nodes (6): AccountController, AccountFormRequest, AccountCategory, ChartOfAccount, AccountService, Enums (`app/Enums/`)

### Community 97 - "require"
Cohesion: 0.25
Nodes (8): require, laravel/framework, laravel/sanctum, laravel/tinker, php, spatie/laravel-activitylog, spatie/laravel-permission, tightenco/ziggy

### Community 98 - "LogsModelActivity"
Cohesion: 0.09
Nodes (8): LogsModelActivity, ProductTransaction, PurchaseInvoiceCost, PurchaseOrderCost, StockAdjustment, StockAdjustmentItem, Spatie\Activitylog\Models\Concerns\LogsActivity, Spatie\Activitylog\Support\LogOptions

### Community 99 - "ContactService"
Cohesion: 0.10
Nodes (4): ContactController, ContactFormRequest, Contact, ContactService

### Community 102 - "Cost & Charge Handling"
Cohesion: 0.33
Nodes (5): Charge Fields, Cost/Charge Flow, Cost & Charge Handling, Cost Fields, Key Behaviors

### Community 103 - "Illuminate\Support\Facades\Auth"
Cohesion: 0.15
Nodes (11): get, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Support\Facades\Auth, Illuminate\Support\Facades\Blade, Spatie\Permission\DefaultTeamResolver, Spatie\Permission\Models\Permission, {closure#1}(), {closure#1}() (+3 more)

### Community 105 - "dependencies"
Cohesion: 0.67
Nodes (3): dependencies, sweetalert2, xlsx

### Community 108 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 109 - "2026_07_06_065924_create_sales_orders_table.php"
Cohesion: 0.29
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}()

### Community 110 - "2026_07_06_114637_create_delivery_orders_table.php"
Cohesion: 0.29
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}()

### Community 111 - "StockPageTest.php"
Cohesion: 0.53
Nodes (4): {closure#2}(), {closure#3}(), {closure#4}(), stockBasics()

### Community 112 - "export.js"
Cohesion: 0.28
Nodes (4): xlsx, exportXlsx(), today(), toNumber()

### Community 114 - "2026_07_27_125404_create_expenses_table.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 120 - "app.js"
Cohesion: 0.22
Nodes (4): matchPeriod(), periodRange(), PERIODS, toYmd()

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

### Community 134 - "GoodsReceiptService"
Cohesion: 0.20
Nodes (3): GoodsReceiptController, GoodsReceiptFormRequest, GoodsReceiptService

### Community 135 - "JournalEntry"
Cohesion: 0.18
Nodes (4): JournalEntry, {closure#6}(), {closure#1}(), {closure#2}()

### Community 138 - "logging.php"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 139 - "sanctum.php"
Cohesion: 0.40
Nodes (4): Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Foundation\Http\Middleware\ValidateCsrfToken, Laravel\Sanctum\Http\Middleware\AuthenticateSession, Laravel\Sanctum\Sanctum

### Community 152 - "contact.js"
Cohesion: 0.60
Nodes (4): quickAdd(), quickAddWithType(), storeContact(), TYPE_LABELS

### Community 159 - "2026_08_09_011515_create_cash_transactions_table.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 168 - "abort_unless_can_become"
Cohesion: 0.22
Nodes (3): abort_unless_can_become(), Symfony\Component\HttpKernel\Exception\HttpException, {closure#2}()

### Community 170 - "autoload-dev"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

### Community 171 - "extra"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

## Knowledge Gaps
- **161 isolated node(s):** `$schema`, `name`, `type`, `description`, `keywords` (+156 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 972 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **176 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `InventoryService` connect `InventoryService` to `GoodsReceiptService.php`, `PurchaseInvoiceService`, `JournalService`, `GoodsReceiptService`, `Illuminate\Validation\ValidationException`, `ProductBatch`, `DeliveryOrderService.php`, `StockAdjustmentService.php`, `WarehouseController`, `WarehouseTransfer`, `ProductStock`, `ProductController`, `PurchaseInvoiceService.php`, `DeliveryOrder`, `InventoryService.php`, `WarehouseTransferController.php`?**
  _High betweenness centrality (0.043) - this node is a cross-community bridge._
- **Why does `LogsModelActivity` connect `LogsModelActivity` to `PurchaseInvoice`, `ProductService`, `JournalEntry`, `PurchaseOrderService.php`, `ReportService`, `ExpenseService.php`, `Illuminate\Support\Facades\DB`, `PurchaseOrderService`, `CashService`, `DeliveryOrder`, `GoodsReceipt`, `AccountPayableService.php`, `SalesOrderService.php`, `SalesInvoice`, `DeliveryOrderService.php`, `AccountSetting`, `ProductStock`, `GoodsReceiptItem`, `User`, `Illuminate\Validation\ValidationException`, `DeliveryOrderStatus`, `WarehouseTransfer`, `Illuminate\Database\Eloquent\Model`, `AccountService`, `ContactService`?**
  _High betweenness centrality (0.039) - this node is a cross-community bridge._
- **Why does `ProductService` connect `ProductService` to `GoodsReceiptService.php`, `ProductService.php`, `PurchaseOrderController`, `Controller`, `GoodsReceiptService`, `JournalService`, `PaymentTerm`, `Illuminate\Validation\ValidationException`, `ProductBatch`, `InventoryTransaction`, `ProductController`, `SalesOrderController`?**
  _High betweenness centrality (0.027) - this node is a cross-community bridge._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _161 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `PurchaseInvoice` be split into smaller, more focused modules?**
  _Cohesion score 0.10952380952380952 - nodes in this community are weakly interconnected._
- **Should `web.php` be split into smaller, more focused modules?**
  _Cohesion score 0.043478260869565216 - nodes in this community are weakly interconnected._
- **Should `PurchaseInvoiceService` be split into smaller, more focused modules?**
  _Cohesion score 0.11462450592885376 - nodes in this community are weakly interconnected._