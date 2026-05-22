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
        // Adding indexes for performance
        Schema::table('suppliers', function (Blueprint $table) { $table->index('name'); });
        Schema::table('categories', function (Blueprint $table) { $table->index('name'); $table->index('prefix'); });
        Schema::table('types', function (Blueprint $table) { $table->index('name'); $table->index('prefix'); });
        Schema::table('manufacturers', function (Blueprint $table) { $table->index('name'); $table->index('code'); });
        Schema::table('units', function (Blueprint $table) { $table->index('name'); $table->index('code'); });
        Schema::table('items', function (Blueprint $table) { $table->index('name'); $table->index('code'); });
        Schema::table('warehouses', function (Blueprint $table) { $table->index('name'); });
        Schema::table('machines', function (Blueprint $table) { $table->index('name'); $table->index('code'); });
        Schema::table('machine_categories', function (Blueprint $table) { $table->index('name'); });
        Schema::table('customers', function (Blueprint $table) { $table->index('name'); });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) { $table->dropIndex(['name']); });
        Schema::table('categories', function (Blueprint $table) { $table->dropIndex(['name']); $table->dropIndex(['prefix']); });
        Schema::table('types', function (Blueprint $table) { $table->dropIndex(['name']); $table->dropIndex(['prefix']); });
        Schema::table('manufacturers', function (Blueprint $table) { $table->dropIndex(['name']); $table->dropIndex(['code']); });
        Schema::table('units', function (Blueprint $table) { $table->dropIndex(['name']); $table->dropIndex(['code']); });
        Schema::table('items', function (Blueprint $table) { $table->dropIndex(['name']); $table->dropIndex(['code']); });
        Schema::table('warehouses', function (Blueprint $table) { $table->dropIndex(['name']); });
        Schema::table('machines', function (Blueprint $table) { $table->dropIndex(['name']); $table->dropIndex(['code']); });
        Schema::table('machine_categories', function (Blueprint $table) { $table->dropIndex(['name']); });
        Schema::table('customers', function (Blueprint $table) { $table->dropIndex(['name']); });
    }
};
