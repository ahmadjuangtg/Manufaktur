<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->index('wo_number');
            $table->index('status');
            $table->index('production_date');
            $table->index('customer_id');
        });

        Schema::table('production_templates', function (Blueprint $table) {
            $table->index('code');
            $table->index('product_id');
        });

        Schema::table('item_requests', function (Blueprint $table) {
            $table->index('reference_no');
            $table->index('status');
            $table->index('warehouse_id');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->index('po_no');
            $table->index('status');
            $table->index('supplier_id');
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropIndex(['wo_number', 'status', 'production_date', 'customer_id']);
        });

        Schema::table('production_templates', function (Blueprint $table) {
            $table->dropIndex(['code', 'product_id']);
        });

        Schema::table('item_requests', function (Blueprint $table) {
            $table->dropIndex(['reference_no', 'status', 'warehouse_id']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndex(['po_no', 'status', 'supplier_id']);
        });
    }
};
