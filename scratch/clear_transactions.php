<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// List of tables to clear
$tables = [
    'delivery_batches',
    'packing_list_details',
    'packing_lists',
    'stock_opnames',
    'purchase_order_details',
    'purchase_orders',
    'item_request_details',
    'item_requests',
    'machine_status_logs',
    'stock_mutation_details',
    'stock_mutations',
    'production_transfers',
    'production_outputs',
    'inventory_stocks',
    'stock_transactions',
    'work_order_stage_items',
    'work_order_products',
    'work_order_stages',
    'work_orders',
];

echo "Starting data cleanup...\n";

Schema::disableForeignKeyConstraints();

foreach ($tables as $table) {
    if (Schema::hasTable($table)) {
        echo "Truncating $table...\n";
        DB::table($table)->truncate();
    }
}

Schema::enableForeignKeyConstraints();

echo "Cleanup complete!\n";
