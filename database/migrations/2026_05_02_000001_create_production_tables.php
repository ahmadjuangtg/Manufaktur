<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Production Templates
        Schema::create('production_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->foreignId('product_id')->constrained('items');
            $table->timestamps();
        });

        Schema::create('production_template_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('production_templates')->onDelete('cascade');
            $table->string('name');
            $table->integer('sequence');
            $table->foreignId('machine_id')->nullable()->constrained('machines');
            $table->timestamps();
        });

        Schema::create('production_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stage_id')->constrained('production_template_stages')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items');
            $table->decimal('quantity_per_batch', 15, 2)->default(0);
            $table->enum('type', ['input', 'output']);
            $table->timestamps();
        });

        // 2. Work Orders
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('wo_number')->unique();
            $table->integer('production_line');
            $table->date('production_date');
            $table->foreignId('customer_id')->constrained('customers');
            $table->string('marketing')->nullable();
            $table->string('status')->default('draft');
            $table->integer('total_batch')->default(1);
            $table->string('stage_code')->nullable();
            $table->string('composition_code')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('work_order_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items');
            $table->decimal('quantity', 15, 2);
            $table->timestamps();
        });

        Schema::create('work_order_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->onDelete('cascade');
            $table->string('name');
            $table->integer('sequence');
            $table->foreignId('machine_id')->nullable()->constrained('machines');
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('work_order_stage_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_stage_id')->constrained('work_order_stages')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items');
            $table->decimal('quantity_per_batch', 15, 2)->default(0);
            $table->decimal('quantity_total', 15, 2)->default(0);
            $table->enum('type', ['input', 'output']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_stage_items');
        Schema::dropIfExists('work_order_stages');
        Schema::dropIfExists('work_order_products');
        Schema::dropIfExists('work_orders');
        Schema::dropIfExists('production_template_items');
        Schema::dropIfExists('production_template_stages');
        Schema::dropIfExists('production_templates');
    }
};
