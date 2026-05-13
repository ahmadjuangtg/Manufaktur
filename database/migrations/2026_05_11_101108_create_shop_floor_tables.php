<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Machine Steps (Master Data)
        Schema::create('machine_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained('machines')->onDelete('cascade');
            $table->string('step_name');
            $table->integer('sequence');
            $table->timestamps();
        });

        // 2. Machine Status Logs (Downtime tracking)
        Schema::create('machine_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained('machines');
            $table->foreignId('work_order_id')->nullable()->constrained('work_orders');
            $table->string('status'); // RUNNING, DOWN, MAINTENANCE, IDLE
            $table->string('reason')->nullable();
            $table->timestamp('start_at');
            $table->timestamp('end_at')->nullable();
            $table->timestamps();
        });

        // 3. Production Outputs (LHP - Laporan Hasil Produksi)
        Schema::create('production_outputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders');
            $table->foreignId('work_order_stage_id')->constrained('work_order_stages');
            $table->decimal('quantity_good', 15, 2);
            $table->decimal('quantity_reject', 15, 2)->default(0);
            $table->foreignId('operator_id')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 4. Production Transfers (NPB/PHP - Nota Penyerahan Barang / Penyerahan Hasil Produksi)
        Schema::create('production_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->foreignId('work_order_id')->constrained('work_orders');
            $table->enum('type', ['NPB', 'PHP']); // NPB = Internal/Next Stage, PHP = to FG Warehouse
            $table->decimal('quantity', 15, 2);
            $table->foreignId('from_warehouse_id')->constrained('warehouses');
            $table->foreignId('to_warehouse_id')->constrained('warehouses');
            $table->enum('status', ['PENDING', 'VERIFIED', 'REJECTED'])->default('PENDING');
            $table->foreignId('user_id')->constrained('users'); // Requester
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_transfers');
        Schema::dropIfExists('production_outputs');
        Schema::dropIfExists('machine_status_logs');
        Schema::dropIfExists('machine_steps');
    }
};
