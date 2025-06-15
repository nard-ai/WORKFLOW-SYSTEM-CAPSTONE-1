<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('form_approvals', function (Blueprint $table) {
            $table->foreignId('signature_style_id')->nullable()->constrained('signature_styles')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_approvals', function (Blueprint $table) {
            $table->dropForeignId('form_approvals_signature_style_id_foreign');
            $table->dropColumn('signature_style_id');
        });
    }
};
