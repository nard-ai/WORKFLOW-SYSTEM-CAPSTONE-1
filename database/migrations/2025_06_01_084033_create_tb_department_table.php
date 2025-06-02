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
        Schema::create('tb_department', function (Blueprint $table) {
            $table->id('department_id');
            $table->string('dept_name');
            $table->enum('category', ['Non-teaching', 'Teaching']);
            $table->string('dept_code');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_department');
    }
};
