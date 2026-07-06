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
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('restrict');
            $table->foreignId('customer_id')->constrained('contacts')->onDelete('restrict');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('restrict');
            $table->foreignId('sales_person_id')->nullable()->constrained('contacts')->onDelete('restrict');
            $table->string('number', 50)->unique();
            $table->string('reference_number', 50)->nullable();
            $table->dateTime('order_date');
            $table->dateTime('due_date')->nullable();
            $table->enum('payment_terms', ['net_7', 'net_14', 'net_30', 'net_45'])->nullable()->default('net_14');
            $table->enum('status', ['draft', 'open', 'closed', 'cancelled'])->default('draft');
            $table->decimal('subtotal', 18, 4)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('discount_amount', 18, 4)->default(0);
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->decimal('tax_amount', 18, 4)->default(0);
            $table->decimal('shipping_charge', 18, 4)->default(0);
            $table->decimal('other_charge', 18, 4)->default(0);
            $table->decimal('down_payment_amount', 18, 4)->default(0);
            $table->decimal('down_payment_remaining_amount', 18, 4)->default(0);
            $table->foreignId('down_payment_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('restrict');
            $table->decimal('total_amount', 18, 4)->default(0);
            $table->text('note')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });

        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->decimal('quantity', 18, 4)->default(0);
            $table->decimal('shipped_quantity', 18, 4)->default(0);
            $table->decimal('invoiced_quantity', 18, 4)->default(0);
            $table->decimal('unit_price', 18, 4)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('discount_amount', 18, 4)->default(0);
            $table->decimal('total_amount', 18, 4)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_order_items');
        Schema::dropIfExists('sale_orders');
    }
};
