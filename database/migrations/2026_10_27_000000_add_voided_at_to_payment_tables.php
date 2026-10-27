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
        foreach (['payable_payments', 'receivable_payments'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->timestamp('voided_at')->nullable()->after('note');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['payable_payments', 'receivable_payments'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('voided_at');
            });
        }
    }
};
