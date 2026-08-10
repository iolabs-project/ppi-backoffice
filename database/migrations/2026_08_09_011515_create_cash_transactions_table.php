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
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('restrict');
            $table->foreignId('from_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('restrict');
            $table->foreignId('to_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('restrict');
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->onDelete('restrict');
            $table->string('reference_type', 100)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('number', 50)->unique();
            $table->string('reference_number', 50)->nullable();
            $table->dateTime('transaction_date');
            $table->enum('type', ['send', 'receive', 'transfer'])->default('send');
            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('draft');
            $table->decimal('subtotal', 18, 4)->default(0);
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->decimal('tax_amount', 18, 4)->default(0);
            $table->decimal('total_amount', 18, 4)->default(0);
            $table->text('note')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });

        Schema::create('cash_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_transaction_id')->constrained('cash_transactions')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('chart_of_accounts')->onDelete('restrict');
            $table->text('note')->nullable();
            $table->decimal('amount', 18, 4)->default(0);
            $table->timestamps();
        });

        Schema::create('cash_transaction_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_transaction_id')->constrained('cash_transactions')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('chart_of_accounts')->onDelete('restrict');
            $table->text('note')->nullable();
            $table->decimal('amount', 18, 4)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_transaction_costs');
        Schema::dropIfExists('cash_transaction_items');
        Schema::dropIfExists('cash_transactions');
    }
};
