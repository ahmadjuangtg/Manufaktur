<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Delivery Batches (Group of Packing Lists)
        Schema::create('delivery_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_no')->unique();
            $table->string('destination');
            $table->string('driver_name')->nullable();
            $table->string('vehicle_no')->nullable();
            $table->string('status')->default('PENDING'); // PENDING, ON_DELIVERY, COMPLETED
            $table->dateTime('departure_at')->nullable();
            $table->dateTime('arrival_at')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });

        // 2. Packing Lists
        Schema::create('packing_lists', function (Blueprint $table) {
            $table->id();
            $table->string('packing_no')->unique();
            $table->foreignId('delivery_batch_id')->nullable()->constrained('delivery_batches')->onDelete('set null');
            $table->string('status')->default('DRAFT'); // DRAFT, READY, SHIPPED
            $table->text('note')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });

        // 3. Packing List Details
        Schema::create('packing_list_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packing_list_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained();
            $table->decimal('quantity', 15, 2);
            $table->string('package_type')->nullable(); // Box, Pallet, Bag, etc.
            $table->string('package_number')->nullable(); // E.g. Box #1
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packing_list_details');
        Schema::dropIfExists('packing_lists');
        Schema::dropIfExists('delivery_batches');
    }
};
