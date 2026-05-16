<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Clear all transaction data as requested by user
        $tablesToTruncate = [
            'purchase_order_details',
            'purchase_orders',
            'work_order_stage_items',
            'work_order_stages',
            'work_order_products',
            'production_outputs',
            'production_transfers',
            'work_orders',
            'stock_mutation_details',
            'stock_mutations',
            'stock_opnames',
            'stock_transactions',
            'item_requests',
            'item_request_details'
        ];

        // For SQLite, TRUNCATE is not supported directly, DELETE FROM is used
        foreach ($tablesToTruncate as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }

        // 2. Modify 'type' column in stock_transactions to string
        if (Schema::hasTable('stock_transactions')) {
            Schema::table('stock_transactions', function (Blueprint $table) {
                $table->string('type')->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('stock_transactions')) {
            Schema::table('stock_transactions', function (Blueprint $table) {
                $table->enum('type', ['IN', 'OUT'])->change();
            });
        }
    }
};
