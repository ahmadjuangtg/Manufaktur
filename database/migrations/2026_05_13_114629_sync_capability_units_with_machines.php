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
        Schema::table('machine_capabilities', function (Blueprint $table) {
            // $table->dropColumn('rate_unit');
            $table->string('output_unit')->nullable()->after('production_rate');
            $table->string('capacity_unit')->nullable()->after('output_unit'); // interval (per jam, etc)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machine_capabilities', function (Blueprint $table) {
            $table->dropColumn(['output_unit', 'capacity_unit']);
            $table->string('rate_unit')->nullable()->after('production_rate');
        });
    }
};
