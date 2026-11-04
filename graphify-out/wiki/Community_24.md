# Community 24

> 80 nodes · cohesion 0.05

## Key Concepts

- **OrderStatusGuardTest.php** (36 connections) — `tests/Feature/OrderStatusGuardTest.php`
- **CashService** (33 connections) — `app/Services/Finance/CashService.php`
- **CashController** (25 connections) — `app/Http/Controllers/Finance/CashController.php`
- **abort_unless_draft()** (19 connections) — `app/helpers.php`
- **.fetchAccountData()** (18 connections) — `app/Services/Master/AccountService.php`
- **CashController.php** (15 connections) — `app/Http/Controllers/Finance/CashController.php`
- **CashReceiveFormRequest** (12 connections) — `app/Http/Requests/Finance/CashReceiveFormRequest.php`
- **CashSendFormRequest** (12 connections) — `app/Http/Requests/Finance/CashSendFormRequest.php`
- **CashTransferFormRequest** (12 connections) — `app/Http/Requests/Finance/CashTransferFormRequest.php`
- **.fetchContactData()** (12 connections) — `app/Services/Master/ContactService.php`
- **.dropdownOptions()** (9 connections) — `app/Enums/PaymentTerm.php`
- **.edit()** (9 connections) — `app/Http/Controllers/Purchasing/PurchaseOrderController.php`
- **.storeTransaction()** (9 connections) — `app/Services/Finance/CashService.php`
- **.create()** (8 connections) — `app/Http/Controllers/Purchasing/PurchaseOrderController.php`
- **.edit()** (8 connections) — `app/Http/Controllers/Sales/SalesOrderController.php`
- **.fetchActiveAccountDataByID()** (8 connections) — `app/Services/Finance/CashService.php`
- **.edit()** (7 connections) — `app/Http/Controllers/Sales/SalesInvoiceController.php`
- **.create()** (7 connections) — `app/Http/Controllers/Sales/SalesOrderController.php`
- **.fetchWarehouseData()** (7 connections) — `app/Services/Master/WarehouseService.php`
- **orderBasics()** (7 connections) — `tests/Feature/OrderStatusGuardTest.php`
- **.editReceive()** (6 connections) — `app/Http/Controllers/Finance/CashController.php`
- **.editSend()** (6 connections) — `app/Http/Controllers/Finance/CashController.php`
- **.edit()** (6 connections) — `app/Http/Controllers/Finance/ExpenseController.php`
- **.edit()** (6 connections) — `app/Http/Controllers/Purchasing/PurchaseInvoiceController.php`
- **.generateNumber()** (6 connections) — `app/Services/Finance/CashService.php`
- *... and 55 more nodes in this community*

## Relationships

- [GoodsReceiptService](GoodsReceiptService.md) (10 shared connections)
- [Community 91](Community_91.md) (7 shared connections)
- [Community 15](Community_15.md) (6 shared connections)
- [CashTransactionStatusEnum](CashTransactionStatusEnum.md) (6 shared connections)
- [DeliveryOrderService](DeliveryOrderService.md) (6 shared connections)
- [Community 93](Community_93.md) (6 shared connections)
- [Community 49](Community_49.md) (5 shared connections)
- [Community 96](Community_96.md) (4 shared connections)
- [Community 99](Community_99.md) (4 shared connections)
- [AccountReceivableService.php](AccountReceivableService.php.md) (4 shared connections)
- [Community 26](Community_26.md) (4 shared connections)
- [Community 52](Community_52.md) (4 shared connections)

## Source Files

- `app/Enums/BilledBy.php`
- `app/Enums/PaymentTerm.php`
- `app/Http/Controllers/Finance/CashController.php`
- `app/Http/Controllers/Finance/ExpenseController.php`
- `app/Http/Controllers/Purchasing/PurchaseInvoiceController.php`
- `app/Http/Controllers/Purchasing/PurchaseOrderController.php`
- `app/Http/Controllers/Sales/SalesInvoiceController.php`
- `app/Http/Controllers/Sales/SalesOrderController.php`
- `app/Http/Requests/Finance/CashReceiveFormRequest.php`
- `app/Http/Requests/Finance/CashSendFormRequest.php`
- `app/Http/Requests/Finance/CashTransferFormRequest.php`
- `app/Services/Finance/CashService.php`
- `app/Services/Master/AccountService.php`
- `app/Services/Master/ContactService.php`
- `app/Services/Master/ProductService.php`
- `app/Services/Master/WarehouseService.php`
- `app/helpers.php`
- `tests/Feature/OrderStatusGuardTest.php`

## Audit Trail

- EXTRACTED: 182 (65%)
- INFERRED: 96 (35%)
- AMBIGUOUS: 0 (0%)

---

*Part of the graphify knowledge wiki. See [index](index.md) to navigate.*