<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('name');
            $blueprint->json('permissions')->nullable();
            $blueprint->timestamps();
        });

        Schema::create('warehouses', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('name');
            $blueprint->boolean('is_24_hours')->default(false);
            $blueprint->string('operational_hours')->nullable();
            $blueprint->enum('server_state', ['WIT', 'WITA', 'WIB']);
            $blueprint->text('address');
            $blueprint->string('postal_code');
            $blueprint->string('province');
            $blueprint->string('city');
            $blueprint->string('district');
            $blueprint->string('village');
            $blueprint->string('region');
            $blueprint->string('phone');
            $blueprint->string('warehouse_type');
            $blueprint->decimal('area', 10, 2);
            $blueprint->boolean('is_active')->default(true);
            $blueprint->timestamps();
        });

        Schema::table('users', function (Blueprint $blueprint) {
            $blueprint->foreignId('role_id')->nullable()->constrained('roles');
        });

        Schema::create('user_warehouse', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('user_id')->constrained()->onDelete('cascade');
            $blueprint->foreignId('warehouse_id')->constrained()->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_warehouse');
        Schema::table('users', function (Blueprint $blueprint) {
            $blueprint->dropConstrainedForeignId('role_id');
        });
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('roles');
    }
};
