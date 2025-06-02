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
        Schema::create('form_approvals', function (Blueprint $table) {
            $table->id('approval_id');
            $table->unsignedBigInteger('form_id');
            $table->unsignedBigInteger('approver_id');
            $table->enum('action', ['Approved', 'Rejected', 'Noted', 'Submitted']);
            $table->text('comments')->nullable();
            $table->timestamp('action_date')->useCurrent();
            $table->timestamps(); // For created_at and updated_at

            $table->foreign('form_id')->references('form_id')->on('form_requests')->onDelete('cascade');
            // Assuming tb_account is the name of your users table and accnt_id is its PK
            $table->foreign('approver_id')->references('accnt_id')->on('tb_account')->onDelete('cascade'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_approvals');
    }
};
