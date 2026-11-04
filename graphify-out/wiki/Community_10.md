# Community 10

> 23 nodes · cohesion 0.17

## Key Concepts

- **InventoryService** (46 connections) — `app/Services/InventoryService.php`
- **WarehouseTransferService.php** (11 connections) — `app/Services/Master/WarehouseTransferService.php`
- **WarehouseTransferService** (8 connections) — `app/Services/Master/WarehouseTransferService.php`
- **.insertInventoryTransaction()** (8 connections) — `app/Services/InventoryService.php`
- **.create()** (7 connections) — `app/Http/Controllers/Master/Warehouse/WarehouseTransferController.php`
- **.receiveInventoryFromGR()** (7 connections) — `app/Services/InventoryService.php`
- **.adjustInventoryFromStockAdjustment()** (6 connections) — `app/Services/InventoryService.php`
- **.fetchInventoryBatches()** (6 connections) — `app/Services/InventoryService.php`
- **.recalculateMovingAverageCost()** (6 connections) — `app/Services/InventoryService.php`
- **.removeInventoryFromGR()** (6 connections) — `app/Services/InventoryService.php`
- **.issueInventoryFromDO()** (5 connections) — `app/Services/InventoryService.php`
- **.transferInventoryBetweenWarehouses()** (5 connections) — `app/Services/InventoryService.php`
- **.updateUnitCostFromPI()** (5 connections) — `app/Services/InventoryService.php`
- **.show()** (4 connections) — `app/Http/Controllers/Master/Warehouse/WarehouseController.php`
- **.store()** (4 connections) — `app/Http/Controllers/Master/Warehouse/WarehouseTransferController.php`
- **.fetchInventoryStock()** (4 connections) — `app/Services/InventoryService.php`
- **.returnInventoryFromDO()** (4 connections) — `app/Services/InventoryService.php`
- **.fetchWarehouseByID()** (4 connections) — `app/Services/Master/WarehouseService.php`
- **.buildInventoryBatchStockQuery()** (3 connections) — `app/Services/InventoryService.php`
- **{closure#1}()** (3 connections) — `app/Services/Master/WarehouseTransferService.php`
- **.generateNumber()** (3 connections) — `app/Services/Master/WarehouseTransferService.php`
- **.storeWarehouseTransfer()** (3 connections) — `app/Services/Master/WarehouseTransferService.php`
- **.__construct()** (2 connections) — `app/Services/Master/WarehouseTransferService.php`

## Relationships

- [Community 4](Community_4.md) (6 shared connections)
- [GoodsReceiptService](GoodsReceiptService.md) (6 shared connections)
- [Community 117](Community_117.md) (5 shared connections)
- [Community 15](Community_15.md) (5 shared connections)
- [DeliveryOrderService](DeliveryOrderService.md) (4 shared connections)
- [Community 23](Community_23.md) (4 shared connections)
- [Community 84](Community_84.md) (4 shared connections)
- [Community 34](Community_34.md) (4 shared connections)
- [Community 98](Community_98.md) (3 shared connections)
- [Community 69](Community_69.md) (3 shared connections)
- [Community 27](Community_27.md) (3 shared connections)
- [DeliveryOrder](DeliveryOrder.md) (3 shared connections)

## Source Files

- `app/Http/Controllers/Master/Warehouse/WarehouseController.php`
- `app/Http/Controllers/Master/Warehouse/WarehouseTransferController.php`
- `app/Services/InventoryService.php`
- `app/Services/Master/WarehouseService.php`
- `app/Services/Master/WarehouseTransferService.php`

## Audit Trail

- EXTRACTED: 97 (84%)
- INFERRED: 19 (16%)
- AMBIGUOUS: 0 (0%)

---

*Part of the graphify knowledge wiki. See [index](index.md) to navigate.*