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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('code', 50)->nullable();
            $table->string('name', 255);
            $table->string('email', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->text('note')->nullable();
            $table->foreignId('receivable_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
            $table->foreignId('payable_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
            $table->decimal('transportation_cost', 18, 4)->default(0);
            $table->boolean('is_customer')->default(false);
            $table->boolean('is_supplier')->default(false);
            $table->boolean('is_employee')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->index('company_id');
            $table->index('code');
            $table->index('name');
            $table->index('is_customer');
            $table->index('is_supplier');
            $table->index('is_employee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
