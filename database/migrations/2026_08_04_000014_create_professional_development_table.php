<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professional_development', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('appraisal_id')->constrained('appraisals')->cascadeOnDelete();
            $table->string('activity_name', 255);
            $table->enum('nature', ['Online', 'In-Person']);
            $table->string('provider', 255)->nullable();
            $table->text('impact_on_practice')->nullable();
            $table->decimal('hours', 5, 2)->default(0);
            $table->date('activity_date')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'activity_date'], 'idx_pd_user_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professional_development');
    }
};
