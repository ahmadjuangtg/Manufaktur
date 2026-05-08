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
        Schema::table('work_orders', function (Blueprint $table) {
            $table->string('duration')->nullable()->after('total_batch');
        });

        Schema::table('work_order_stages', function (Blueprint $table) {
            $table->dateTime('planned_start')->nullable()->after('machine_id');
        });

        Schema::table('production_templates', function (Blueprint $table) {
            $table->string('duration')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn('duration');
        });

        Schema::table('work_order_stages', function (Blueprint $table) {
            $table->dropColumn('planned_start');
        });

        Schema::table('production_templates', function (Blueprint $table) {
            $table->dropColumn('duration');
        });
    }
};
