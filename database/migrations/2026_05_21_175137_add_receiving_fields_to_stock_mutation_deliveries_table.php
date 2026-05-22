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
        Schema::table('stock_mutation_deliveries', function (Blueprint $table) {
            $table->string('shipment_no')->nullable()->after('stock_mutation_id');
            $table->decimal('received_quantity', 15, 2)->nullable()->after('quantity');
            $table->foreignId('received_by')->nullable()->after('delivered_by')->constrained('users')->nullOnDelete();
            $table->timestamp('received_at')->nullable()->after('delivered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_mutation_deliveries', function (Blueprint $table) {
            $table->dropForeign(['received_by']);
            $table->dropColumn(['shipment_no', 'received_quantity', 'received_by', 'received_at']);
        });
    }
};
