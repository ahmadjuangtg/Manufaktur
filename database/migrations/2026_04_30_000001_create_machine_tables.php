<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machine_categories', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('code')->unique();
            $blueprint->string('name');
            $blueprint->timestamps();
        });

        Schema::create('machines', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('code')->unique();
            $blueprint->string('name');
            $blueprint->foreignId('machine_category_id')->constrained('machine_categories');
            $blueprint->decimal('capacity', 15, 2);
            $blueprint->string('capacity_unit');
            $blueprint->string('outlet')->nullable();
            $blueprint->boolean('is_active')->default(true);
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machines');
        Schema::dropIfExists('machine_categories');
    }
};
