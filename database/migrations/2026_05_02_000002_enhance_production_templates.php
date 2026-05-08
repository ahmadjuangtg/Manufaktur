<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add fields to production_templates
        Schema::table('production_templates', function (Blueprint $table) {
            $table->integer('production_line')->nullable()->after('product_id');
            $table->string('marketing')->nullable()->after('production_line');
            $table->string('stage_code')->nullable()->after('marketing');
            $table->string('composition_code')->nullable()->after('stage_code');
            $table->text('notes')->nullable()->after('composition_code');
            
            // Make product_id nullable since we might use multiple products
            $table->unsignedBigInteger('product_id')->nullable()->change();
        });

        // 2. Create production_template_products table
        Schema::create('production_template_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('production_templates')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items');
            $table->decimal('quantity', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_template_products');
        
        Schema::table('production_templates', function (Blueprint $table) {
            $table->dropColumn(['production_line', 'marketing', 'stage_code', 'composition_code', 'notes']);
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
        });
    }
};
