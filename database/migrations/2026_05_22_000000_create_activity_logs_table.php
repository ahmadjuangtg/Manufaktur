<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->string('action'); // e.g. CREATED, UPDATED, DELETED, LOGIN, LOGOUT
            $table->string('subject_type')->nullable(); // e.g. App\Models\Item
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->text('description');
            $table->jsonb('properties')->nullable(); // JSONB for PostgreSQL support
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->nullable()->index(); // Index for performance
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
