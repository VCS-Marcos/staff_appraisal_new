<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appraisals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('cycle_id')->constrained('appraisal_cycles')->restrictOnDelete();
            $table->date('appraisal_date')->nullable();
            $table->text('self_reflection')->nullable();
            $table->text('reviewer_comments')->nullable();
            $table->enum('overall_rating', [
                'Did Not Meet All Targets',
                'Met All Targets',
                'Exceeded All Targets',
            ])->nullable();
            $table->date('next_review_date')->nullable();
            $table->enum('status', [
                'draft',
                'pending_employee',
                'pending_reviewer',
                'pending_signoff',
                'completed',
            ])->default('draft');
            $table->timestamp('employee_signed_at')->nullable();
            $table->timestamp('reviewer_signed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'cycle_id'], 'uq_user_cycle');
            $table->index('status', 'idx_appraisals_status');
            $table->index('cycle_id', 'idx_appraisals_cycle');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appraisals');
    }
};
