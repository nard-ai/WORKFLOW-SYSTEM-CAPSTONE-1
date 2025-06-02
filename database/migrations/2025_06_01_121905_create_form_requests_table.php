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
        Schema::create('form_requests', function (Blueprint $table) {
            $table->id('form_id');
            $table->enum('form_type', ['IOM', 'Leave', 'Budget Slip', 'Vehicle Reservation and Trip Ticket', 'Loan', 'Job Orders']); // Corrected ENUM
            $table->string('title')->nullable(); // Subject or title, nullable as not all forms might have it initially
            $table->unsignedBigInteger('from_department_id')->nullable();
            $table->unsignedBigInteger('to_department_id')->nullable(); // Optional
            $table->unsignedBigInteger('requested_by');
            $table->enum('status', ['Pending', 'Approved', 'Rejected', 'Cancelled', 'In Progress'])->default('Pending');
            $table->timestamp('date_submitted')->useCurrent();
            $table->timestamps(); // For created_at and updated_at, common in Laravel

            // Assuming tb_department is the name of your departments table and department_id is its PK
            $table->foreign('from_department_id')->references('department_id')->on('tb_department')->onDelete('set null');
            // Assuming tb_department is the name of your departments table and department_id is its PK
            $table->foreign('to_department_id')->references('department_id')->on('tb_department')->onDelete('set null');
            // Assuming tb_account is the name of your users table and accnt_id is its PK
            $table->foreign('requested_by')->references('accnt_id')->on('tb_account')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_requests');
    }
};
