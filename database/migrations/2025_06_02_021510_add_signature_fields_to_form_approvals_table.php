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
        Schema::table('form_approvals', function (Blueprint $table) {
            $table->string('signature_name')->nullable()->after('comments');
            $table->text('signature_data')->nullable()->after('signature_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_approvals', function (Blueprint $table) {
            $table->dropColumn(['signature_name', 'signature_data']);
        });
    }
};
