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
        Schema::table('production_transfers', function (Blueprint $table) {
            $table->foreignId('work_order_stage_id')->nullable()->after('work_order_id')->constrained('work_order_stages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_transfers', function (Blueprint $table) {
            //
        });
    }
};
