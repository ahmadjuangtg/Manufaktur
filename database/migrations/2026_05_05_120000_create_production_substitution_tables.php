<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Machine Substitutions
        Schema::create('machine_substitutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->onDelete('cascade');
            $table->foreignId('substitute_machine_id')->constrained('machines')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['machine_id', 'substitute_machine_id']);
        });

        // Item Substitutions (Materials)
        Schema::create('item_substitutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->foreignId('substitute_item_id')->constrained('items')->onDelete('cascade');
            $table->decimal('conversion_ratio', 12, 4)->default(1.0000);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['item_id', 'substitute_item_id']);
        });

        // Machine Capabilities (What product can be made in which machine)
        Schema::create('machine_capabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->boolean('is_default')->default(false);
            $table->decimal('production_rate', 12, 4)->nullable(); // e.g. units per hour if different from machine default
            $table->timestamps();

            $table->unique(['machine_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machine_capabilities');
        Schema::dropIfExists('item_substitutions');
        Schema::dropIfExists('machine_substitutions');
    }
};
