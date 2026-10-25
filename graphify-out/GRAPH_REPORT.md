# Graph Report - ppi-backoffice  (2026-10-25)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 2229 nodes · 4146 edges · 286 communities (77 shown, 209 thin omitted)
- Extraction: 93% EXTRACTED · 7% INFERRED · 0% AMBIGUOUS · INFERRED: 282 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `87cfc97c`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Community 0
- Community 1
- Community 3
- Community 4
- Community 5
- Community 6
- Community 7
- Community 8
- Community 9
- Community 10
- Community 11
- Community 12
- Community 13
- Community 15
- Community 16
- Community 17
- Community 18
- Community 19
- Community 20
- Community 21
- Community 22
- Community 23
- Community 24
- Community 25
- Community 26
- Community 27
- Community 28
- Community 30
- Community 31
- Community 32
- Community 33
- Community 34
- Community 35
- Community 36
- Community 37
- Community 38
- Community 39
- Community 40
- Community 41
- Community 42
- Community 43
- Community 44
- Community 45
- Community 47
- Community 48
- Community 49
- Community 50
- Community 51
- Community 53
- Community 54
- Community 55
- Community 56
- Community 57
- Community 58
- Community 59
- Community 60
- Community 61
- Community 62
- Community 63
- Community 64
- Community 65
- Community 66
- Community 67
- Community 68
- Community 69
- Community 72
- Community 73
- Community 74
- Community 75
- Community 76
- Community 77
- Community 78
- Community 79
- Community 80
- Community 81
- Community 82
- Community 83
- Community 84
- Community 85
- Community 86
- Community 87
- Community 88
- Community 90
- Community 91
- Community 92
- Community 93
- Community 94
- Community 95
- Community 96
- Community 97
- Community 98
- Community 99
- Community 100
- Community 101
- Community 102
- Community 103
- Community 104
- Community 105
- Community 106
- Community 107
- Community 108
- Community 109
- Community 110
- Community 111
- Community 112
- Community 113
- Community 114
- Community 115
- Community 116
- Community 117
- Community 118
- Community 119
- Community 120
- Community 121
- Community 122
- Community 123
- Community 124
- Community 125
- Community 127
- Community 128
- Community 129
- Community 130
- Community 131
- Community 132
- Community 133
- Community 134
- Community 135
- Community 137
- Community 140
- Community 141
- Community 142
- Community 143
- Community 144
- Community 145
- Community 146
- Community 148
- Community 149
- Community 150
- Community 151
- Community 152
- Community 153
- Community 154
- Community 155
- Community 156
- Community 157
- Community 158
- Community 159
- Community 160
- Community 161
- Community 162
- Community 163
- Community 164
- Community 165
- Community 166
- Community 167
- Community 168
- Community 170
- Community 171
- Community 172
- Community 173
- Community 174
- Community 175
- Community 176
- Community 177
- Community 178
- Community 179
- Community 180
- Community 181
- Community 182
- Community 183
- Community 184
- Community 185
- Community 187
- Community 188
- Community 189
- Community 190
- Community 191
- Community 192
- Community 193
- Community 194
- Community 195
- Community 196
- Community 197
- Community 198
- Community 199
- Community 200
- Community 201
- Community 202
- Community 203
- Community 204
- Community 205
- Community 206
- Community 207
- Community 208
- Community 209
- Community 210
- Community 211
- Community 212
- Community 213
- Community 214
- Community 215
- Community 216
- Community 217
- Community 218
- Community 219
- Community 220
- Community 221
- Community 222
- Community 223
- Community 224
- Community 225
- Community 226

## God Nodes (most connected - your core abstractions)
1. `Controller` - 52 edges
2. `InventoryService` - 44 edges
3. `AccountService` - 44 edges
4. `JournalService` - 40 edges
5. `ProductService` - 40 edges
6. `AccountSettingEnum` - 40 edges
7. `GoodsReceiptService` - 33 edges
8. `DeliveryOrderService` - 29 edges
9. `CashService` - 29 edges
10. `AccountCategoryEnum` - 28 edges

## Surprising Connections (you probably didn't know these)
- `{closure#2}()` --calls--> `abort_unless_can_become()`  [INFERRED]
  tests/Feature/StatusTransitionTest.php → app/helpers.php
- `actingUserWithEditPermission()` --references--> `User`  [EXTRACTED]
  tests/Feature/UserPasswordChangeTest.php → app/Models/User.php
- `{closure#5}()` --calls--> `abort_unless_draft()`  [INFERRED]
  app/Services/Purchasing/PurchaseInvoiceService.php → app/helpers.php
- `{closure#9}()` --calls--> `abort_unless_draft()`  [INFERRED]
  app/Services/Purchasing/PurchaseOrderService.php → app/helpers.php
- `{closure#8}()` --calls--> `abort_unless_draft()`  [INFERRED]
  app/Services/Sales/SalesOrderService.php → app/helpers.php

## Import Cycles
- None detected.

## Communities (286 total, 209 thin omitted)

### Community 0 - "Community 0"
Cohesion: 0.05
Nodes (40): dependencies, sweetalert2@^11.26.25, xlsx@https://cdn.sheetjs.com/xlsx-0.20.3/x..., devDependencies, axios@^1.18.0, concurrently@^9.0.1, laravel-vite-plugin@^2.0.0, sass-embedded@^1.99.0 (+32 more)

### Community 1 - "Community 1"
Cohesion: 0.07
Nodes (5): ReportController, ActivityLogService, ReportService, GET /user(), POST /logout()

### Community 3 - "Community 3"
Cohesion: 0.07
Nodes (11): {closure#1}(), PurchaseInvoiceStatus, CANCELLED, DRAFT, OPEN, PAID, PARTIAL, PurchaseInvoiceController (+3 more)

### Community 4 - "Community 4"
Cohesion: 0.09
Nodes (5): ProductBatchController, ProductCategoryController, ProductController, ProductFormRequest, ProductService

### Community 5 - "Community 5"
Cohesion: 0.12
Nodes (8): LoginController, Controller, DashboardController, AccountSettingController, ContactController, MasterController, WarehouseTransferController, AccountSettingService

### Community 6 - "Community 6"
Cohesion: 0.11
Nodes (4): AccountFormRequest, AccountSettingFormRequest, StockAdjustmentFormRequest, WarehouseTransferFormRequest

### Community 7 - "Community 7"
Cohesion: 0.07
Nodes (23): AccountSettingEnum, ACCOUNT_PAYABLE, ACCOUNT_RECEIVABLE, BANK, CASH, COGS, GRNI, INPUT_TAX (+15 more)

### Community 8 - "Community 8"
Cohesion: 0.09
Nodes (7): abort_unless_can_become(), fmt_doc(), fmt_doc_percent(), fmt_doc_qty(), {closure#1}(), PrintDocument, {closure#2}()

### Community 10 - "Community 10"
Cohesion: 0.12
Nodes (5): ExpenseService, InventoryService, JournalService, {closure#1}(), StockAdjustmentService

### Community 11 - "Community 11"
Cohesion: 0.11
Nodes (14): DocumentTotals, {closure#10}(), {closure#13}(), {closure#8}(), {closure#5}(), {closure#10}(), {closure#12}(), {closure#6}() (+6 more)

### Community 12 - "Community 12"
Cohesion: 0.12
Nodes (3): UserController, UserFormRequest, UserService

### Community 13 - "Community 13"
Cohesion: 0.09
Nodes (5): SalesOrder, {closure#10}(), {closure#6}(), {closure#7}(), {closure#8}()

### Community 15 - "Community 15"
Cohesion: 0.11
Nodes (4): StockAdjustmentController, WarehouseController, WarehouseFormRequest, WarehouseService

### Community 16 - "Community 16"
Cohesion: 0.12
Nodes (13): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}() (+5 more)

### Community 18 - "Community 18"
Cohesion: 0.11
Nodes (7): AccountCategory, AccountMapping, ChartOfAccount, Company, ProductCategory, Unit, UserCompany

### Community 19 - "Community 19"
Cohesion: 0.10
Nodes (4): Contact, JournalEntryItem, ProductTransaction, PurchaseOrderItem

### Community 20 - "Community 20"
Cohesion: 0.14
Nodes (3): SalesInvoiceController, SalesInvoiceFormRequest, SalesInvoiceService

### Community 21 - "Community 21"
Cohesion: 0.15
Nodes (6): AccountSettingSeeder, COASeeder, ContactSeeder, DatabaseSeeder, InventorySeeder, UserCompanySeeder

### Community 22 - "Community 22"
Cohesion: 0.13
Nodes (4): AccountReceivableController, ReceivablePayment, AccountReceivableService, {closure#4}()

### Community 23 - "Community 23"
Cohesion: 0.09
Nodes (3): AccountSetting, StockAdjustment, StockAdjustmentItem

### Community 26 - "Community 26"
Cohesion: 0.11
Nodes (3): CashTransaction, {closure#6}(), {closure#7}()

### Community 27 - "Community 27"
Cohesion: 0.11
Nodes (18): AccountCategoryEnum, ACCOUNT_PAYABLE, ACCOUNT_RECEIVABLE, ACCUMULATED_DEPRECIATION_AMORTIZATION, CASH_BANK, COST_OF_GOODS_SOLD, CREDIT_CARD, EQUITY (+10 more)

### Community 31 - "Community 31"
Cohesion: 0.16
Nodes (6): Expense, {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}()

### Community 33 - "Community 33"
Cohesion: 0.17
Nodes (3): AccountPayableController, AccountPayableFormRequest, AccountPayableService

### Community 38 - "Community 38"
Cohesion: 0.17
Nodes (3): PermissionService, PermissionSeeder, RoleSeeder

### Community 39 - "Community 39"
Cohesion: 0.17
Nodes (10): {closure#1}(), {closure#2}(), PaymentTerm, CASH, COD, CUSTOM, NET_14, NET_30 (+2 more)

### Community 45 - "Community 45"
Cohesion: 0.14
Nodes (3): SalesInvoice, {closure#5}(), {closure#9}()

### Community 47 - "Community 47"
Cohesion: 0.18
Nodes (7): {closure#1}(), ExpenseStatus, CANCELLED, DRAFT, OPEN, PAID, PARTIAL

### Community 48 - "Community 48"
Cohesion: 0.15
Nodes (9): ReportTypeEnum, ACTIVITY_LOG, BALANCE_SHEET, CASH_FLOW, EXECUTIVE, JOURNAL, PAYABLE, PROFIT_LOSS (+1 more)

### Community 50 - "Community 50"
Cohesion: 0.23
Nodes (3): ReportPermissionMiddleware, {closure#1}(), {closure#2}()

### Community 54 - "Community 54"
Cohesion: 0.15
Nodes (3): {closure#1}(), {closure#1}(), {closure#1}()

### Community 55 - "Community 55"
Cohesion: 0.20
Nodes (7): AccountPayableStatusEnum, CANCELLED, DRAFT, OPEN, PAID, PARTIAL, {closure#1}()

### Community 56 - "Community 56"
Cohesion: 0.20
Nodes (7): AccountReceivableStatusEnum, CANCELLED, DRAFT, OPEN, PAID, PARTIAL, {closure#1}()

### Community 57 - "Community 57"
Cohesion: 0.20
Nodes (7): {closure#1}(), SalesInvoiceStatus, CANCELLED, DRAFT, OPEN, PAID, PARTIAL

### Community 63 - "Community 63"
Cohesion: 0.17
Nodes (3): {closure#1}(), {closure#1}(), {closure#1}()

### Community 64 - "Community 64"
Cohesion: 0.21
Nodes (4): {closure#5}(), {closure#7}(), {closure#9}(), orderBasics()

### Community 65 - "Community 65"
Cohesion: 0.24
Nodes (6): BilledBy, INTERNAL, SUPPLIER, THIRD_PARTY, {closure#1}(), {closure#2}()

### Community 66 - "Community 66"
Cohesion: 0.22
Nodes (6): {closure#1}(), PurchaseOrderStatus, CANCELLED, CLOSED, DRAFT, OPEN

### Community 67 - "Community 67"
Cohesion: 0.22
Nodes (6): {closure#1}(), SalesOrderStatus, CANCELLED, CLOSED, DRAFT, OPEN

### Community 68 - "Community 68"
Cohesion: 0.22
Nodes (4): AccountReceivableFormRequest, {closure#2}(), {closure#3}(), rulesForRoute()

### Community 72 - "Community 72"
Cohesion: 0.20
Nodes (7): ActivityLogEventEnum, CANCELLED, CLOSED, DRAFTED, FINISHED, OPENED, POSTED

### Community 73 - "Community 73"
Cohesion: 0.24
Nodes (5): CashTransactionTypeEnum, RECEIVE, SEND, TRANSFER, {closure#1}()

### Community 74 - "Community 74"
Cohesion: 0.24
Nodes (5): {closure#1}(), DeliveryOrderStatus, CANCELLED, DRAFT, FINISHED

### Community 75 - "Community 75"
Cohesion: 0.24
Nodes (5): {closure#1}(), GoodsReceiptStatus, CANCELLED, DRAFT, FINISHED

### Community 76 - "Community 76"
Cohesion: 0.20
Nodes (9): InventoryTransactionTypeEnum, ADJUSTMENT_MINUS, ADJUSTMENT_PLUS, COST_ADJUSTMENT, OPENING, PURCHASE, SALE, TRANSFER_IN (+1 more)

### Community 77 - "Community 77"
Cohesion: 0.20
Nodes (3): PayablePaymentReferenceTypeEnum, EXPENSE, PURCHASE_INVOICE

### Community 78 - "Community 78"
Cohesion: 0.36
Nodes (8): actingUserWithEditPermission(), {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}()

### Community 79 - "Community 79"
Cohesion: 0.29
Nodes (5): {closure#3}(), {closure#4}(), {closure#6}(), {closure#8}(), journalBasics()

### Community 80 - "Community 80"
Cohesion: 0.28
Nodes (5): CashTransactionStatusEnum, CANCELLED, DRAFT, POSTED, {closure#1}()

### Community 81 - "Community 81"
Cohesion: 0.28
Nodes (5): {closure#1}(), JournalEntryStatusEnum, CANCELLED, DRAFT, POSTED

### Community 82 - "Community 82"
Cohesion: 0.22
Nodes (4): {closure#10}(), ProductBatchStock, {closure#12}(), {closure#9}()

### Community 86 - "Community 86"
Cohesion: 0.22
Nodes (8): description, keywords, license, minimum-stability, name, prefer-stable, $schema, type

### Community 87 - "Community 87"
Cohesion: 0.22
Nodes (9): require-dev, fakerphp/faker, laravel/pail, laravel/pint, laravel/sail, mockery/mockery, nunomaduro/collision, pestphp/pest (+1 more)

### Community 88 - "Community 88"
Cohesion: 0.22
Nodes (9): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+1 more)

### Community 97 - "Community 97"
Cohesion: 0.25
Nodes (8): require, laravel/framework, laravel/sanctum, laravel/tinker, php, spatie/laravel-activitylog, spatie/laravel-permission, tightenco/ziggy

### Community 98 - "Community 98"
Cohesion: 0.39
Nodes (5): {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), goodsReceiptErrors()

### Community 108 - "Community 108"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 109 - "Community 109"
Cohesion: 0.29
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}()

### Community 110 - "Community 110"
Cohesion: 0.29
Nodes (4): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}()

### Community 112 - "Community 112"
Cohesion: 0.33
Nodes (4): RoleEnum, ACCOUNTING, ADMIN, SUPER_ADMIN

### Community 127 - "Community 127"
Cohesion: 0.33
Nodes (6): autoload, files, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 128 - "Community 128"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 129 - "Community 129"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 130 - "Community 130"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 131 - "Community 131"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 132 - "Community 132"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 133 - "Community 133"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 148 - "Community 148"
Cohesion: 0.60
Nodes (3): {closure#1}(), {closure#2}(), salesInvoiceFixture()

### Community 161 - "Community 161"
Cohesion: 0.50
Nodes (3): master.warehouse.partials.batch-table, master.warehouse.partials.stock-table, layouts.app

### Community 162 - "Community 162"
Cohesion: 0.50
Nodes (3): purchasing.purchase-order.partials.create.item-table, layouts.app, purchasing.partials.additional-cost-table

### Community 163 - "Community 163"
Cohesion: 0.50
Nodes (3): finance.expense.partials.cost-table, finance.expense.partials.item-table, layouts.app

### Community 164 - "Community 164"
Cohesion: 0.50
Nodes (3): finance.expense.partials.cost-table, finance.expense.partials.item-table, layouts.app

### Community 165 - "Community 165"
Cohesion: 0.50
Nodes (3): layouts.app, sales.partials.additional-cost-table, sales.delivery-order.partials.edit.item-table

### Community 166 - "Community 166"
Cohesion: 0.50
Nodes (3): layouts.app, sales.partials.additional-charge-table, sales.partials.additional-cost-table

### Community 167 - "Community 167"
Cohesion: 0.50
Nodes (3): layouts.app, sales.partials.additional-charge-table, sales.partials.additional-cost-table

### Community 170 - "Community 170"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

### Community 171 - "Community 171"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

## Knowledge Gaps
- **212 isolated node(s):** `ACCOUNTING`, `ADMIN`, `SUPER_ADMIN`, `master.warehouse.partials.batch-table`, `master.warehouse.partials.stock-table` (+207 more)
  These have ≤1 connection - possible missing edges. (Counts symbols only; 1009 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **209 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Expense` connect `Community 31` to `Community 71`, `Community 77`, `Community 49`, `Community 18`, `Community 19`, `Community 24`?**
  _High betweenness centrality (0.027) - this node is a cross-community bridge._
- **What connects `ACCOUNTING`, `ADMIN`, `SUPER_ADMIN` to the rest of the system?**
  _212 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Community 0` be split into smaller, more focused modules?**
  _Cohesion score 0.053246753246753244 - nodes in this community are weakly interconnected._
- **Why does `ErpDataService` connect `Community 17` to `Community 40`, `Community 4`, `Community 5`?**
  _High betweenness centrality (0.023) - this node is a cross-community bridge._
- **Should `Community 1` be split into smaller, more focused modules?**
  _Cohesion score 0.07329462989840348 - nodes in this community are weakly interconnected._
- **Why does `PurchaseOrder` connect `Community 43` to `Community 37`, `Community 70`, `Community 71`, `Community 11`, `Community 18`, `Community 19`, `Community 52`, `Community 25`?**
  _High betweenness centrality (0.022) - this node is a cross-community bridge._
- **Should `Community 2` be split into smaller, more focused modules?**
  _Cohesion score 0.045454545454545456 - nodes in this community are weakly interconnected._