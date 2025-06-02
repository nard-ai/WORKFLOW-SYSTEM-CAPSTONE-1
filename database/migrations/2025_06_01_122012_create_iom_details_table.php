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
        Schema::create('iom_details', function (Blueprint $table) {
            $table->unsignedBigInteger('form_id')->primary();
            $table->date('date_needed')->nullable();
            $table->enum('priority', ['Urgent', 'Routine', 'Rush'])->nullable();
            $table->string('purpose', 100)->nullable();
            $table->text('body')->nullable();

            $table->foreign('form_id')->references('form_id')->on('form_requests')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iom_details');
    }
};
