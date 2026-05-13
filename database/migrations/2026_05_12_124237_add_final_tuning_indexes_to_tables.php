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
            // High-performance composite index for the main scheduling query
            // Covers: status filtering + production_line + priority + sort_order
            $table->index(['status', 'production_line', 'priority_id', 'sort_order'], 'idx_wo_perf_tuning');
            
            // Index for faster latest() queries
            $table->index(['created_at', 'status']);
        });

        Schema::table('work_order_products', function (Blueprint $table) {
            // Faster product lookups per WO
            $table->index(['work_order_id', 'item_id']);
        });

        Schema::table('work_order_stages', function (Blueprint $table) {
            // Faster stage sequencing
            $table->index(['work_order_id', 'sequence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropIndex('idx_wo_perf_tuning');
            $table->dropIndex(['created_at', 'status']);
        });

        Schema::table('work_order_products', function (Blueprint $table) {
            $table->dropIndex(['work_order_id', 'item_id']);
        });

        Schema::table('work_order_stages', function (Blueprint $table) {
            $table->dropIndex(['work_order_id', 'sequence']);
        });
    }
};
