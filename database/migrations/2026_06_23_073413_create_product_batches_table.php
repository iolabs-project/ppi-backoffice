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
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('restrict');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->string('batch_number', 50);
            $table->string('supplier_batch_number', 50)->nullable();
            $table->decimal('initial_quantity', 18, 4)->default(0);
            $table->decimal('unit_cost', 18, 4)->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'product_id', 'batch_number'], 'unique_product_batch');
        });

        Schema::create('product_batch_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_batch_id')->constrained('product_batches')->onDelete('restrict');
            $table->foreignId('warehouse_id')->constrained()->onDelete('restrict');
            $table->decimal('quantity', 18, 4)->default(0);
            $table->decimal('reserved_quantity', 18, 4)->default(0);
            $table->timestamps();

            $table->unique([
                'product_batch_id',
                'warehouse_id',
            ], 'unique_product_batch_stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_batch_stocks');
        Schema::dropIfExists('product_batches');
    }
};
