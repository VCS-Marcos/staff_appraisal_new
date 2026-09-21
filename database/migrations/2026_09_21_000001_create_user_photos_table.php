<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Staff photos live in the database rather than on disk: the Wasmer host has a
     * read-only filesystem, and the processed images are small (~20 KB). They sit in
     * their own table so ordinary user queries never drag image bytes along.
     */
    public function up(): void
    {
        Schema::create('user_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->binary('data')->comment('Re-encoded JPEG bytes');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('photo_updated_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('photo_updated_at');
        });

        Schema::dropIfExists('user_photos');
    }
};
