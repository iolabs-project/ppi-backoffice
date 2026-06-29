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
        Schema::create('cash_bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('restrict');
            $table->foreignId('cash_bank_account_id')->constrained('chart_of_accounts')->onDelete('restrict');
            $table->foreignId('pair_transaction_id')->nullable()->constrained('cash_bank_transactions')->onDelete('restrict');
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->onDelete('restrict');
            $table->enum('direction', ['in', 'out']);
            $table->string('number', 50);
            $table->dateTime('transaction_date');
            $table->decimal('amount', 18, 4);
            $table->text('note')->nullable();
            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();

            $table->index([
                'company_id',
                'transaction_date',
            ]);

            $table->unique([
                'company_id',
                'cash_bank_account_id',
                'number',
            ]);

            $table->index([
                'company_id',
                'cash_bank_account_id',
                'transaction_date',
            ]);
        });

        Schema::create('cash_bank_transaction_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_bank_transaction_id')->constrained('cash_bank_transactions')->onDelete('cascade');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_type')->nullable();
            $table->foreignId('account_id')->nullable()->constrained('chart_of_accounts')->onDelete('restrict');
            $table->string('description', 255)->nullable();
            $table->decimal('amount', 18, 4);
            $table->timestamps();

            $table->index([
                'cash_bank_transaction_id',
                'reference_id',
                'reference_type',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_bank_transaction_lines');
        Schema::dropIfExists('cash_bank_transactions');
    }
};
