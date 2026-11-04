# GoodsReceiptService

> 26 nodes · cohesion 0.15

## Key Concepts

- **JournalService** (42 connections) — `app/Services/JournalService.php`
- **GoodsReceiptService** (36 connections) — `app/Services/Purchasing/GoodsReceiptService.php`
- **PurchaseOrderService** (31 connections) — `app/Services/Purchasing/PurchaseOrderService.php`
- **GoodsReceiptController** (14 connections) — `app/Http/Controllers/Purchasing/GoodsReceiptController.php`
- **GoodsReceiptController.php** (12 connections) — `app/Http/Controllers/Purchasing/GoodsReceiptController.php`
- **.edit()** (7 connections) — `app/Http/Controllers/Purchasing/GoodsReceiptController.php`
- **.__construct()** (6 connections) — `app/Services/Purchasing/GoodsReceiptService.php`
- **.storeGoodsReceipt()** (6 connections) — `app/Services/Purchasing/GoodsReceiptService.php`
- **.fetchGoodsReceiptByID()** (5 connections) — `app/Services/Purchasing/GoodsReceiptService.php`
- **.__construct()** (5 connections) — `app/Services/Purchasing/PurchaseInvoiceService.php`
- **.fetchPOItemsForGoodsReceipt()** (5 connections) — `app/Services/Purchasing/PurchaseOrderService.php`
- **.__construct()** (5 connections) — `app/Services/Sales/DeliveryOrderService.php`
- **.cancel()** (4 connections) — `app/Http/Controllers/Purchasing/GoodsReceiptController.php`
- **.datatable()** (4 connections) — `app/Http/Controllers/Purchasing/GoodsReceiptController.php`
- **.store()** (4 connections) — `app/Http/Controllers/Purchasing/GoodsReceiptController.php`
- **.update()** (4 connections) — `app/Http/Controllers/Purchasing/GoodsReceiptController.php`
- **.changeGoodsReceiptStatus()** (4 connections) — `app/Services/Purchasing/GoodsReceiptService.php`
- **.updateGoodsReceipt()** (4 connections) — `app/Services/Purchasing/GoodsReceiptService.php`
- **.generatePONumber()** (4 connections) — `app/Services/Purchasing/PurchaseOrderService.php`
- **.print()** (3 connections) — `app/Http/Controllers/Purchasing/GoodsReceiptController.php`
- **.show()** (3 connections) — `app/Http/Controllers/Purchasing/GoodsReceiptController.php`
- **.fetchGoodsReceiptTableData()** (3 connections) — `app/Services/Purchasing/GoodsReceiptService.php`
- **.__construct()** (3 connections) — `app/Services/Purchasing/PurchaseOrderService.php`
- **Purchase Order (`PurchaseOrderService`)** (3 connections) — `context/purchasing.md`
- **.__construct()** (2 connections) — `app/Services/Finance/CashService.php`
- *... and 1 more nodes in this community*

## Relationships

- [Community 24](Community_24.md) (10 shared connections)
- [Community 34](Community_34.md) (9 shared connections)
- [PurchaseInvoiceService](PurchaseInvoiceService.md) (7 shared connections)
- [Community 93](Community_93.md) (7 shared connections)
- [Community 10](Community_10.md) (6 shared connections)
- [Community 15](Community_15.md) (5 shared connections)
- [GoodsReceiptStatus](GoodsReceiptStatus.md) (5 shared connections)
- [Community 42](Community_42.md) (5 shared connections)
- [Community 96](Community_96.md) (4 shared connections)
- [Community 49](Community_49.md) (4 shared connections)
- [GoodsReceiptService.php](GoodsReceiptService.php.md) (4 shared connections)
- [Community 37](Community_37.md) (4 shared connections)

## Source Files

- `app/Http/Controllers/Purchasing/GoodsReceiptController.php`
- `app/Services/Finance/CashService.php`
- `app/Services/InventoryService.php`
- `app/Services/JournalService.php`
- `app/Services/Purchasing/GoodsReceiptService.php`
- `app/Services/Purchasing/PurchaseInvoiceService.php`
- `app/Services/Purchasing/PurchaseOrderService.php`
- `app/Services/Sales/DeliveryOrderService.php`
- `context/purchasing.md`

## Audit Trail

- EXTRACTED: 148 (87%)
- INFERRED: 23 (13%)
- AMBIGUOUS: 0 (0%)

---

*Part of the graphify knowledge wiki. See [index](index.md) to navigate.*