<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->string('province')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->string('district')->nullable()->change();
            $table->string('village')->nullable()->change();
            $table->string('postal_code')->nullable()->change();
            $table->string('region')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->string('province')->change();
            $table->string('city')->change();
            $table->string('district')->change();
            $table->string('village')->change();
            $table->string('postal_code')->change();
            $table->string('region')->change();
        });
    }
};
