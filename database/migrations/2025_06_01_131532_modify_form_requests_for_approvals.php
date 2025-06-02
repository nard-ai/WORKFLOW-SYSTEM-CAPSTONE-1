<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Required for DB::statement

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('form_requests', function (Blueprint $table) {
            // Add current_approver_id column
            $table->unsignedBigInteger('current_approver_id')->nullable()->after('requested_by');
            $table->foreign('current_approver_id')->references('accnt_id')->on('tb_account')->onDelete('set null');

            // Modify status ENUM with the new status
            $newStatuses = "'Pending', 'Noted', 'Approved', 'Rejected', 'Cancelled', 'In Progress'";
            DB::statement("ALTER TABLE form_requests MODIFY COLUMN status ENUM({$newStatuses}) NOT NULL DEFAULT 'Pending'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_requests', function (Blueprint $table) {
            $table->dropForeign(['current_approver_id']);
            $table->dropColumn('current_approver_id');

            // Revert status ENUM to previous state
            $oldStatuses = "'Pending', 'Approved', 'Rejected', 'Cancelled', 'In Progress'";
            DB::statement("ALTER TABLE form_requests MODIFY COLUMN status ENUM({$oldStatuses}) NOT NULL DEFAULT 'Pending'");
        });
    }
};
