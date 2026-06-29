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
        Schema::create('purchase_order_down_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->onDelete('restrict');
            $table->foreignId('cash_bank_transaction_id')->constrained('cash_bank_transactions')->onDelete('restrict');
            $table->decimal('remaining_amount', 18, 4)->default(0);
            $table->enum('status', ['draft', 'open', 'closed', 'cancelled'])->default('draft');
            $table->text('note')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();

            $table->unique(['purchase_order_id', 'cash_bank_transaction_id'], 'unique_purchase_order_down_payment');
        });

        Schema::create('purchase_order_down_payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_down_payment_id')->constrained('purchase_order_down_payments')->onDelete('cascade');
            $table->foreignId('purchase_invoice_id')->constrained('purchase_invoices')->onDelete('restrict');
            $table->decimal('allocated_amount', 18, 4);
            $table->timestamps();

            $table->unique(['purchase_order_down_payment_id', 'purchase_invoice_id'], 'unique_purchase_order_down_payment_allocation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_down_payment_allocations');
        Schema::dropIfExists('purchase_order_down_payments');
    }
};
