# DeliveryOrder

> 16 nodes · cohesion 0.15

## Key Concepts

- **DeliveryOrder** (27 connections) — `app/Models/DeliveryOrder.php`
- **.finalizeDeliveryOrder()** (11 connections) — `app/Services/Sales/DeliveryOrderService.php`
- **.draftDeliveryOrder()** (4 connections) — `app/Services/Sales/DeliveryOrderService.php`
- **.postDOJournal()** (4 connections) — `app/Services/Sales/DeliveryOrderService.php`
- **.storeExpenseFromDeliveryOrder()** (3 connections) — `app/Services/ExpenseService.php`
- **{closure#5}()** (3 connections) — `app/Services/Sales/DeliveryOrderService.php`
- **From Delivery Order (`storeExpenseFromDeliveryOrder`)** (3 connections) — `context/expense.md`
- **.costs()** (2 connections) — `app/Models/DeliveryOrder.php`
- **.items()** (2 connections) — `app/Models/DeliveryOrder.php`
- **.updateShippedQuantity()** (2 connections) — `app/Services/Sales/SalesOrderService.php`
- **Inventory Integration (Delivery Order Finalize)** (2 connections) — `context/sales.md`
- **.creator()** (1 connections) — `app/Models/DeliveryOrder.php`
- **.customer()** (1 connections) — `app/Models/DeliveryOrder.php`
- **.getIsCancellableAttribute()** (1 connections) — `app/Models/DeliveryOrder.php`
- **.salesOrder()** (1 connections) — `app/Models/DeliveryOrder.php`
- **.warehouse()** (1 connections) — `app/Models/DeliveryOrder.php`

## Relationships

- [DeliveryOrderService](DeliveryOrderService.md) (8 shared connections)
- [Community 46](Community_46.md) (3 shared connections)
- [Community 10](Community_10.md) (3 shared connections)
- [Community 31](Community_31.md) (2 shared connections)
- [Community 27](Community_27.md) (2 shared connections)
- [Community 17](Community_17.md) (2 shared connections)
- [DashboardService.php](DashboardService.php.md) (1 shared connections)
- [InventoryService.php](InventoryService.php.md) (1 shared connections)
- [Community 95](Community_95.md) (1 shared connections)
- [Community 40](Community_40.md) (1 shared connections)
- [Community 9](Community_9.md) (1 shared connections)
- [DeliveryOrderStatus](DeliveryOrderStatus.md) (1 shared connections)

## Source Files

- `app/Models/DeliveryOrder.php`
- `app/Services/ExpenseService.php`
- `app/Services/Sales/DeliveryOrderService.php`
- `app/Services/Sales/SalesOrderService.php`
- `context/expense.md`
- `context/sales.md`

## Audit Trail

- EXTRACTED: 39 (78%)
- INFERRED: 11 (22%)
- AMBIGUOUS: 0 (0%)

---

*Part of the graphify knowledge wiki. See [index](index.md) to navigate.*