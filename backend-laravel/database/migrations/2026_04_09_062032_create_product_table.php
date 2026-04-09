<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku');
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0);
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // Trigger to prevent stock_quantity value from going below 0
        DB::statement('CREATE TRIGGER prevent_negative_stock
            BEFORE UPDATE ON products
            FOR EACH ROW
            WHEN NEW.stock < 0
            BEGIN
                SELECT RAISE(ABORT, "Stock cannot be negative");
            END;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {        
        DB::statement('DROP TRIGGER IF EXISTS prevent_negative_stock');    
        Schema::dropIfExists('products');    
    }
};
