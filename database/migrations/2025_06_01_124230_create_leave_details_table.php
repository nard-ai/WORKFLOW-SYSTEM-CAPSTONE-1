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
        Schema::create('leave_details', function (Blueprint $table) {
            $table->unsignedBigInteger('form_id')->primary(); // Foreign key to form_requests
            $table->enum('leave_type', ['sick', 'vacation', 'emergency']);
            $table->date('date_of_leave');
            $table->text('description')->nullable(); // The reason for leave
            // No timestamps needed here if they are tied to the main form_request

            $table->foreign('form_id')->references('form_id')->on('form_requests')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_details');
    }
};
