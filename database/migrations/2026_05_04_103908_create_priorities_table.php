<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('priorities', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->integer('level'); // 1-5
            $table->string('name');
            $table->string('color')->default('#6366f1'); // Default indigo
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('priorities');
    }
};
