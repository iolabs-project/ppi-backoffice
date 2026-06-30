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
            $table->unsignedBigInteger('purchase_order_id');
            $table->unsignedBigInteger('cash_bank_transaction_id');
            $table->decimal('remaining_amount', 18, 4)->default(0);
            $table->enum('status', ['draft', 'open', 'closed', 'cancelled'])->default('draft');
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('purchase_order_id', 'dp_po_fk')
                ->references('id')
                ->on('purchase_orders')
                ->restrictOnDelete();

            $table->foreign('cash_bank_transaction_id', 'dp_cbt_fk')
                ->references('id')
                ->on('cash_bank_transactions')
                ->restrictOnDelete();

            $table->foreign('created_by', 'dp_created_fk')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();

            $table->unique(['purchase_order_id', 'cash_bank_transaction_id'], 'unique_purchase_order_down_payment');
        });

        Schema::create('purchase_order_down_payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_down_payment_id');
            $table->unsignedBigInteger('purchase_invoice_id');
            $table->decimal('allocated_amount', 18, 4);
            $table->timestamps();

            $table->foreign('purchase_order_down_payment_id', 'alloc_dp_fk')
                ->references('id')
                ->on('purchase_order_down_payments')
                ->cascadeOnDelete();

            $table->foreign('purchase_invoice_id', 'alloc_inv_fk')
                ->references('id')
                ->on('purchase_invoices')
                ->restrictOnDelete();

            $table->unique(['purchase_order_down_payment_id', 'purchase_invoice_id'], 'unique_po_dp_allocation');
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
