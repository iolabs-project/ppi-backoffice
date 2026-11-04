# InventoryService

> God node · 46 connections · `app/Services/InventoryService.php`

**Community:** [Community 10](Community_10.md)

## Connections by Relation

### contains
- [InventoryService.php](InventoryService.php.md) `EXTRACTED`

### imports
- [GoodsReceiptService.php](GoodsReceiptService.php.md) `EXTRACTED`
- DeliveryOrderService.php `EXTRACTED`
- PurchaseInvoiceService.php `EXTRACTED`
- StockAdjustmentService.php `EXTRACTED`
- DeliveryOrderController.php `EXTRACTED`
- WarehouseTransferService.php `EXTRACTED`
- ProductController.php `EXTRACTED`
- WarehouseController.php `EXTRACTED`
- WarehouseTransferController.php `EXTRACTED`

### method
- .insertInventoryTransaction() `EXTRACTED`
- .receiveInventoryFromGR() `EXTRACTED`
- .fetchInventoryBatches() `EXTRACTED`
- .removeInventoryFromGR() `EXTRACTED`
- .adjustInventoryFromStockAdjustment() `EXTRACTED`
- .recalculateMovingAverageCost() `EXTRACTED`
- .issueInventoryFromDO() `EXTRACTED`
- .transferInventoryBetweenWarehouses() `EXTRACTED`
- .updateUnitCostFromPI() `EXTRACTED`
- .fetchInventoryBatchTableData() `EXTRACTED`
- .transformBatchStock() `EXTRACTED`
- .returnInventoryFromDO() `EXTRACTED`
- .fetchInventoryStock() `EXTRACTED`
- .buildInventoryBatchStockQuery() `EXTRACTED`
- .fetchInventoryStockTableData() `EXTRACTED`
- .__construct() `EXTRACTED`
- .fetchQuantitySoldByProductThisMonth() `EXTRACTED`
- .fetchQuantityReceivedByProductThisMonth() `EXTRACTED`

### references
- [JournalService](JournalService.md) `EXTRACTED`
- [GoodsReceiptService](GoodsReceiptService.md) `EXTRACTED`
- [DeliveryOrderService](DeliveryOrderService.md) `EXTRACTED`
- [PurchaseInvoiceService](PurchaseInvoiceService.md) `EXTRACTED`
- DeliveryOrderController `EXTRACTED`
- WarehouseController `EXTRACTED`
- ProductController `EXTRACTED`
- StockAdjustmentService `EXTRACTED`
- WarehouseTransferService `EXTRACTED`
- .create() `EXTRACTED`
- .__construct() `EXTRACTED`
- .__construct() `EXTRACTED`
- .__construct() `EXTRACTED`
- .__construct() `EXTRACTED`
- .__construct() `EXTRACTED`
- .__construct() `EXTRACTED`
- .__construct() `EXTRACTED`
- .__construct() `EXTRACTED`

---

*Part of the graphify knowledge wiki. See [index](index.md) to navigate.*