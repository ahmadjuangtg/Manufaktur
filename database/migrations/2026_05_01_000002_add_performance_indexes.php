<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->index(['item_id', 'warehouse_id']);
            $table->index('type');
            $table->index('reference_no');
            $table->index('created_at');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->index('category_id');
            $table->index('type_id');
            $table->index('manufacturer_id');
            $table->index('unit_id');
        });

        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->index(['item_id', 'warehouse_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->dropIndex(['item_id', 'warehouse_id']);
            $table->dropIndex(['type']);
            $table->dropIndex(['reference_no']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
            $table->dropIndex(['type_id']);
            $table->dropIndex(['manufacturer_id']);
            $table->dropIndex(['unit_id']);
        });

        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->dropIndex(['item_id', 'warehouse_id']);
            $table->dropIndex(['created_at']);
        });
    }
};
