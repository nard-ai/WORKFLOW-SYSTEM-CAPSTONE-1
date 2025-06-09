<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tb_account', function (Blueprint $table) {
            // Add 'VPAA' to the enum. IMPORTANT: Check existing values in your DB.
            // The safest way to modify an ENUM is to use a raw DB statement.
            // Ensure you have 'Head', 'Staff', 'Admin' already.
            DB::statement("ALTER TABLE tb_account MODIFY COLUMN position ENUM('Head', 'Staff', 'Admin', 'VPAA') NOT NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_account', function (Blueprint $table) {
            // Revert to the original ENUM definition if VPAA was added.
            // This assumes 'VPAA' was the last one added.
            DB::statement("ALTER TABLE tb_account MODIFY COLUMN position ENUM('Head', 'Staff', 'Admin') NOT NULL");
        });
    }
};
