<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appraisals', function (Blueprint $table) {
            $table->unsignedSmallInteger('year')->nullable()->after('reviewer_id');
        });

        DB::statement("
            UPDATE appraisals a
            LEFT JOIN appraisal_cycles c ON c.id = a.cycle_id
            SET a.year = YEAR(COALESCE(a.appraisal_date, c.end_date, c.start_date, a.created_at))
        ");

        Schema::table('appraisals', function (Blueprint $table) {
            $table->dropForeign(['cycle_id']);
        });

        // Add the new (user_id, year) unique index before dropping the old
        // (user_id, cycle_id) one — the old one is currently also the only
        // index backing the user_id -> users foreign key, so MySQL refuses
        // to drop it until another index covers that column.
        Schema::table('appraisals', function (Blueprint $table) {
            $table->unsignedSmallInteger('year')->nullable(false)->change();
            $table->unique(['user_id', 'year'], 'uq_user_year');
        });

        Schema::table('appraisals', function (Blueprint $table) {
            $table->dropUnique('uq_user_cycle');
            $table->dropIndex('idx_appraisals_cycle');
            $table->dropColumn('cycle_id');
        });
    }

    public function down(): void
    {
        // Best-effort only: the cycle a given appraisal originally belonged to is
        // no longer known, so this restores the nullable column/FK shape without
        // trying to (re)populate or re-enforce not-null/unique constraints on it.
        Schema::table('appraisals', function (Blueprint $table) {
            $table->foreignId('cycle_id')->nullable()->after('reviewer_id')->constrained('appraisal_cycles')->restrictOnDelete();
            $table->index('cycle_id', 'idx_appraisals_cycle');
            // Also gives the user_id -> users foreign key a supporting index to
            // fall back on once uq_user_year is dropped below (see up()'s comment).
            $table->index('user_id', 'idx_appraisals_user_id');
        });

        Schema::table('appraisals', function (Blueprint $table) {
            $table->dropUnique('uq_user_year');
            $table->dropColumn('year');
        });
    }
};
