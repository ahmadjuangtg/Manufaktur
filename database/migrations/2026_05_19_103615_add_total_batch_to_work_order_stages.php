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
        Schema::table('work_order_stages', function (Blueprint $table) {
            $table->decimal('total_batch', 8, 2)->default(1.00)->after('machine_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_order_stages', function (Blueprint $table) {
            $table->dropColumn('total_batch');
        });
    }
};
