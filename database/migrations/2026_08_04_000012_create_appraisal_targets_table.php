<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appraisal_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appraisal_id')->constrained('appraisals')->cascadeOnDelete();
            $table->enum('target_type', ['current', 'next_year']);
            $table->unsignedTinyInteger('target_number');
            $table->text('target_text')->nullable();
            $table->enum('target_met', ['yes', 'no', 'partially'])->nullable();
            $table->text('comments')->nullable();
            $table->text('action_text')->nullable();
            $table->text('success_criteria')->nullable();
            $table->timestamps();

            $table->unique(['appraisal_id', 'target_type', 'target_number'], 'uq_appraisal_target');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appraisal_targets');
    }
};
