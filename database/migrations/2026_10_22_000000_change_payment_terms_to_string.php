<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = ['purchase_orders', 'purchase_invoices', 'sales_orders', 'sales_invoices', 'expenses'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Values are validated against App\Enums\PaymentTerm (cash, cod, net_*, custom)
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('payment_terms', 20)->nullable()->default('net_14')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->enum('payment_terms', ['net_7', 'net_14', 'net_30', 'net_45'])->nullable()->default('net_14')->change();
            });
        }
    }
};
