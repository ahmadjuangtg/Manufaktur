<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('customer_id')->unique();
            $blueprint->string('username')->nullable();
            $blueprint->string('name');
            $blueprint->string('country')->default('Indonesia');
            $blueprint->text('address');
            $blueprint->string('phone');
            $blueprint->string('email')->unique();
            $blueprint->date('dob')->nullable();
            $blueprint->string('gender')->nullable();
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
