<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('package_qty', 10, 2)->nullable()->after('unit_id');
            $table->string('package_type')->nullable()->after('package_qty');
        });
    }
    public function down(): void {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['package_qty', 'package_type']);
        });
    }
};
