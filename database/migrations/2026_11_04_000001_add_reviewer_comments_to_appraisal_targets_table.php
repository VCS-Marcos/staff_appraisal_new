<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appraisal_targets', function (Blueprint $table) {
            // The reviewer's optional comment on a Section 1 target. (The existing `comments`
            // column is the staff member's comment — only the labels were renamed.)
            $table->text('reviewer_comments')->nullable()->after('comments');
        });
    }

    public function down(): void
    {
        Schema::table('appraisal_targets', function (Blueprint $table) {
            $table->dropColumn('reviewer_comments');
        });
    }
};
