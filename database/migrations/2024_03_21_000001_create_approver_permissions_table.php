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
        Schema::create('approver_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('accnt_id');
            $table->foreign('accnt_id')->references('accnt_id')->on('tb_account')->onDelete('cascade');
            $table->boolean('can_approve_pending')->default(true);
            $table->boolean('can_approve_in_progress')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approver_permissions');
    }
}; 