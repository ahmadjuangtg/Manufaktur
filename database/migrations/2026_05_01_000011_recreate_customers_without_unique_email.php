<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite doesn't handle dropping unique constraints well on existing columns.
        // We recreate the table to be safe.
        
        Schema::create('customers_new', function (Blueprint $table) {
            $table->id();
            $table->string('customer_id')->unique();
            $table->string('username')->nullable();
            $table->string('name');
            $table->string('country')->default('Indonesia');
            $table->text('address');
            $table->string('phone');
            $table->string('email')->nullable(); // Removed unique
            $table->date('dob')->nullable();
            $table->string('gender')->nullable();
            $table->timestamps();
        });

        $oldCustomers = DB::table('customers')->get();
        foreach ($oldCustomers as $customer) {
            DB::table('customers_new')->insert((array)$customer);
        }

        Schema::drop('customers');
        Schema::rename('customers_new', 'customers');
    }

    public function down(): void
    {
        // No easy way to go back if data already has duplicates
    }
};
