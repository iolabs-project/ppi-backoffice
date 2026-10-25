<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Typo from COASeeder in databases seeded before it was fixed
        DB::table('account_categories')
            ->where('name', 'Depresiasi & Armortisasi')
            ->update(['name' => 'Depresiasi & Amortisasi']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to undo: the old name was a typo
    }
};
