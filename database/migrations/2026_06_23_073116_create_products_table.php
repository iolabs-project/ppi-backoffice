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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('restrict');
            $table->foreignId('category_id')->constrained('product_categories')->onDelete('restrict');
            $table->foreignId('unit_id')->constrained('units')->onDelete('restrict');
            $table->string('name', 255);
            $table->string('code', 50)->nullable();
            $table->text('description')->nullable();
            $table->decimal('minimum_stock', 18, 4)->default(0);
            $table->foreignId('inventory_account_id')->constrained('chart_of_accounts')->onDelete('restrict');
            $table->foreignId('sales_account_id')->constrained('chart_of_accounts')->onDelete('restrict');
            $table->foreignId('cogs_account_id')->constrained('chart_of_accounts')->onDelete('restrict');
            $table->softDeletes();
            $table->timestamps();

            // index
            $table->index(['company_id', 'category_id']);
            $table->index(['company_id', 'unit_id']);
            $table->index(['company_id', 'code']);
            $table->index(['company_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
