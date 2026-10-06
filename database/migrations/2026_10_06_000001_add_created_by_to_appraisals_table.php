<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Records who created each appraisal, so a reviewer can be limited to editing the
     * ones they created themselves. Existing rows are back-filled from the audit log
     * ("appraisal.created" entries); any without an entry are left null (admin-only).
     */
    public function up(): void
    {
        Schema::table('appraisals', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('reviewer_id')
                ->constrained('users')->nullOnDelete();
        });

        DB::statement("
            UPDATE appraisals a
            JOIN (
                SELECT entity_id, MIN(id) AS first_id
                FROM audit_log
                WHERE action = 'appraisal.created' AND entity_type = 'Appraisal'
                GROUP BY entity_id
            ) f ON f.entity_id = a.id
            JOIN audit_log l ON l.id = f.first_id
            SET a.created_by = l.user_id
        ");
    }

    public function down(): void
    {
        Schema::table('appraisals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
        });
    }
};
