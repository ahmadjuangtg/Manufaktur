<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->boolean('is_local')->default(true);
            $table->string('country')->default('Indonesia');
        });

        Schema::table('manufacturers', function (Blueprint $table) {
            $table->boolean('is_local')->default(true);
            $table->string('country')->default('Indonesia');
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['is_local', 'country']);
        });

        Schema::table('manufacturers', function (Blueprint $table) {
            $table->dropColumn(['is_local', 'country']);
        });
    }
};
