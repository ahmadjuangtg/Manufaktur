<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            if (!Schema::hasColumn('warehouses', 'server_state')) {
                $table->string('server_state')->nullable()->after('address'); // WIB, WITA, WIT
            }
            if (!Schema::hasColumn('warehouses', 'phone')) {
                $table->string('phone')->nullable()->after('server_state');
            }
            if (!Schema::hasColumn('warehouses', 'is_24_hours')) {
                $table->boolean('is_24_hours')->default(false)->after('phone');
            }
            if (!Schema::hasColumn('warehouses', 'operational_hours')) {
                $table->string('operational_hours')->nullable()->after('is_24_hours'); // S/d
            }
            if (!Schema::hasColumn('warehouses', 'postal_code')) {
                $table->string('postal_code')->nullable()->after('is_24_hours');
            }
            if (!Schema::hasColumn('warehouses', 'province')) {
                $table->string('province')->nullable()->after('postal_code');
            }
            if (!Schema::hasColumn('warehouses', 'city')) {
                $table->string('city')->nullable()->after('province');
            }
            if (!Schema::hasColumn('warehouses', 'district')) {
                $table->string('district')->nullable()->after('city'); // Kecamatan
            }
            if (!Schema::hasColumn('warehouses', 'village')) {
                $table->string('village')->nullable()->after('district'); // Kelurahan
            }
            if (!Schema::hasColumn('warehouses', 'region')) {
                $table->string('region')->nullable()->after('village');
            }
            if (!Schema::hasColumn('warehouses', 'warehouse_type')) {
                $table->string('warehouse_type')->nullable()->after('region');
            }
            if (!Schema::hasColumn('warehouses', 'surface_area')) {
                $table->decimal('surface_area', 10, 2)->default(0)->after('warehouse_type');
            }
            if (!Schema::hasColumn('warehouses', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('surface_area');
            }
        });
    }

    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn([
                'server_state', 'phone', 'is_24_hours', 'operational_hours', 
                'postal_code', 'province', 'city', 'district', 'village', 
                'region', 'warehouse_type', 'surface_area', 'is_active'
            ]);
        });
    }
};
