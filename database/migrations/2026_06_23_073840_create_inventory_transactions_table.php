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
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('no action');
            $table->foreignId('product_id')->constrained()->onDelete('no action');
            $table->foreignId('warehouse_id')->constrained()->onDelete('no action');
            $table->enum('type', ['opening', 'purchase', 'sale', 'transfer_in', 'transfer_out', 'adjustment_plus', 'adjustment_minus']);
            $table->tinyInteger('direction')->comment('1 for incoming, -1 for outgoing');
            $table->decimal('quantity', 18, 4);
            $table->decimal('unit_cost', 18, 4)->nullable();
            $table->decimal('total_cost', 18, 4)->nullable();
            $table->decimal('stock_before', 18, 4)->nullable();
            $table->decimal('stock_after', 18, 4)->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->dateTime('transaction_date');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index([
                'company_id',
                'product_id',
                'warehouse_id'
            ]);

            $table->index([
                'company_id',
                'transaction_date'
            ]);

            $table->index([
                'reference_type',
                'reference_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
