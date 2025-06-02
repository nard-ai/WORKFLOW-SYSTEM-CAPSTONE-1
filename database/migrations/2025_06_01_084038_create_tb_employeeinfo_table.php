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
        Schema::create('tb_employeeinfo', function (Blueprint $table) {
            $table->string('Emp_No')->primary();
            $table->string('LastName');
            $table->string('FirstName');
            $table->string('MiddleName')->nullable();
            $table->string('Email')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_employeeinfo');
    }
};
