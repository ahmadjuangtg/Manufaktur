<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Suppliers Table
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('sub_district')->nullable();
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->timestamps();
        });

        // 2. Item Requests (Permintaan Barang)
        Schema::create('item_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->foreignId('user_id')->constrained(); // Requester
            $table->foreignId('warehouse_id')->constrained();
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED', 'COMPLETED'])->default('PENDING');
            $table->text('note')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_note')->nullable();
            $table->timestamps();
        });

        Schema::create('item_request_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained();
            $table->decimal('quantity', 15, 2);
            $table->timestamps();
        });

        // 3. Purchase Orders
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_no')->unique();
            $table->foreignId('item_request_id')->nullable()->constrained(); // Link to original request
            $table->foreignId('supplier_id')->constrained();
            $table->foreignId('user_id')->constrained(); // Buyer/Purchasing
            $table->enum('status', ['DRAFT', 'OPEN', 'PARTIAL', 'CLOSED', 'CANCELLED'])->default('OPEN');
            $table->date('order_date');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('purchase_order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained();
            $table->decimal('quantity', 15, 2);
            $table->decimal('received_quantity', 15, 2)->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_details');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('item_request_details');
        Schema::dropIfExists('item_requests');
        Schema::dropIfExists('suppliers');
    }
};
