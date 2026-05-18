<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_mutation_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_mutation_id')->constrained('stock_mutations')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->decimal('quantity', 15, 2);
            $table->foreignId('delivered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('delivered_at')->useCurrent();
            $table->timestamps();

            // Indexing for rapid queries
            $table->index(['stock_mutation_id', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_mutation_deliveries');
    }
};
