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
        Schema::create('receivable_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('restrict');
            $table->foreignId('account_id')->constrained('chart_of_accounts')->onDelete('restrict');
            $table->string('reference_type', 100)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('number', 50)->unique();
            $table->dateTime('payment_date');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'credit_card'])->default('cash');
            $table->string('reference_number', 50)->nullable();
            $table->decimal('amount', 18, 4)->default(0);
            $table->text('note')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receivable_payments');
    }
};
