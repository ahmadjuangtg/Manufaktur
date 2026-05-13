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
            // Composite index for fast scheduling lookups and sorting
            $table->index(['production_line', 'status', 'scheduled_start'], 'idx_wo_scheduling_lookup');
            $table->index(['priority_id', 'sort_order'], 'idx_wo_priority_sort');
        });

        Schema::table('work_order_stages', function (Blueprint $table) {
            // Index for fast resource/machine availability lookups
            $table->index(['machine_id', 'work_order_id'], 'idx_stage_machine_wo');
        });
        
        Schema::table('stock_transactions', function (Blueprint $table) {
            // Index for fast stock balance calculations
            $table->index(['item_id', 'warehouse_id', 'type'], 'idx_stock_balance_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropIndex('idx_wo_scheduling_lookup');
            $table->dropIndex('idx_wo_priority_sort');
        });

        Schema::table('work_order_stages', function (Blueprint $table) {
            $table->dropIndex('idx_stage_machine_wo');
        });
        
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_stock_balance_lookup');
        });
    }
};
