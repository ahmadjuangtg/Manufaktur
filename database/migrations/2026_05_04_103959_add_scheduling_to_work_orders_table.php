<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->foreignId('priority_id')->nullable()->constrained('priorities')->onDelete('set null');
            $table->dateTime('scheduled_start')->nullable();
            $table->dateTime('scheduled_end')->nullable();
        });
    }

    public function down()
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropForeign(['priority_id']);
            $table->dropColumn(['priority_id', 'scheduled_start', 'scheduled_end']);
        });
    }
};
