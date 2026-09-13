<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appraisals', function (Blueprint $table) {
            $table->enum('completion_mode', ['self_service', 'assisted'])
                ->default('self_service')
                ->after('status');
            $table->index('completion_mode', 'idx_appraisals_completion_mode');
        });
    }

    public function down(): void
    {
        Schema::table('appraisals', function (Blueprint $table) {
            $table->dropIndex('idx_appraisals_completion_mode');
            $table->dropColumn('completion_mode');
        });
    }
};
