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
            $table->string('thickness')->nullable()->after('item_id');
            $table->string('diameter')->nullable()->after('thickness');
            $table->integer('cavity')->nullable()->after('diameter');
            $table->decimal('cycle', 8, 2)->nullable()->after('cavity');
            $table->string('rate_unit')->nullable()->default('kg/jam')->after('production_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machine_capabilities', function (Blueprint $table) {
            $table->dropColumn(['thickness', 'diameter', 'cavity', 'cycle', 'rate_unit']);
        });
    }
};
