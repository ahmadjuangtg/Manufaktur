<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); $table->string('code')->unique(); $table->string('prefix')->unique(); $table->string('name'); $table->timestamps();
        });
        Schema::create('types', function (Blueprint $table) {
            $table->id(); $table->string('code')->unique(); $table->string('prefix')->unique(); $table->string('name'); $table->timestamps();
        });
        Schema::create('manufacturers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('address');
            $table->string('postal_code')->nullable();
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('sub_district')->nullable();
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('contact_name');
            $table->string('contact_phone');
            $table->string('contact_email')->nullable();
            $table->timestamps();
        });
        Schema::create('units', function (Blueprint $table) {
            $table->id(); $table->string('code')->unique(); $table->string('name'); $table->timestamps();
        });
        Schema::create('items', function (Blueprint $table) {
            $table->id(); $table->string('barcode')->unique(); $table->string('code')->unique(); $table->string('name'); $table->string('display_name');
            $table->foreignId('category_id')->constrained(); $table->foreignId('type_id')->constrained();
            $table->foreignId('manufacturer_id')->constrained(); $table->foreignId('unit_id')->constrained();
            $table->string('package_contain')->nullable(); $table->decimal('length', 8, 2)->nullable(); $table->decimal('width', 8, 2)->nullable(); $table->decimal('height', 8, 2)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('items'); Schema::dropIfExists('units'); Schema::dropIfExists('manufacturers'); Schema::dropIfExists('types'); Schema::dropIfExists('categories');
    }
};
