<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('restrict');
            $table->foreignId('sales_order_id')->constrained('sales_orders')->onDelete('restrict');
            $table->foreignId('customer_id')->constrained('contacts')->onDelete('restrict');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('restrict');
            $table->string('number', 50)->unique();
            $table->string('reference_number', 50)->nullable();
            $table->dateTime('delivery_date');
            $table->enum('status', ['draft', 'finished', 'cancelled'])->default('draft');
            $table->decimal('subtotal', 18, 4)->default(0);
            $table->decimal('total_amount', 18, 4)->default(0);
            $table->text('note')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });

        Schema::create('delivery_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_order_id')->constrained('delivery_orders')->onDelete('cascade');
            $table->foreignId('sales_order_item_id')->constrained('sales_order_items')->onDelete('restrict');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->decimal('quantity', 18, 4);
            $table->timestamps();
        });

        Schema::create('delivery_order_item_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_order_item_id')->constrained('delivery_order_items')->onDelete('cascade');
            $table->foreignId('product_batch_id')->constrained('product_batches')->onDelete('restrict');
            $table->decimal('quantity', 18, 4);
            $table->decimal('unit_cost', 18, 4)->default(0);
            $table->timestamps();
        });

        Schema::create('delivery_order_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_order_id')->constrained('delivery_orders')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('chart_of_accounts')->onDelete('restrict');
            $table->text('description')->nullable();
            $table->decimal('amount', 18, 4)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_order_costs');
        Schema::dropIfExists('delivery_order_item_batches');
        Schema::dropIfExists('delivery_order_items');
        Schema::dropIfExists('delivery_orders');
    }
};
