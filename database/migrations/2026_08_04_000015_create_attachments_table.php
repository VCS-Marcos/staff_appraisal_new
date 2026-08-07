<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appraisal_id')->nullable()->constrained('appraisals')->cascadeOnDelete();
            $table->foreignId('pd_record_id')->nullable()->constrained('professional_development')->cascadeOnDelete();
            $table->string('file_path', 500);
            $table->string('original_name')->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('uploaded_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
