<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appraisals', function (Blueprint $table) {
            // Optional logistics for the appraisal meeting; set when the appraisal is created/edited.
            $table->time('appraisal_time')->nullable()->after('appraisal_date');
            $table->string('venue', 255)->nullable()->after('appraisal_time');
        });
    }

    public function down(): void
    {
        Schema::table('appraisals', function (Blueprint $table) {
            $table->dropColumn(['appraisal_time', 'venue']);
        });
    }
};
