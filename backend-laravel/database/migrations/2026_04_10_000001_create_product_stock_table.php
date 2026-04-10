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
        Schema::create('product_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->integer('low_stock_threshold')->default(5);
            $table->timestamps();

            $table->unique('product_id');
        });

        DB::statement('CREATE TRIGGER prevent_negative_product_stock
            BEFORE UPDATE ON product_stock
            FOR EACH ROW
            WHEN NEW.quantity < 0
            BEGIN
                SELECT RAISE(ABORT, "Stock quantity cannot be negative");
            END;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS prevent_negative_product_stock');
        Schema::dropIfExists('product_stock');
    }
};
