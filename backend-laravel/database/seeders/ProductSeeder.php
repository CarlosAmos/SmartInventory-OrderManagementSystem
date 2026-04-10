<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();

        // Create products with normal stock for each category
        $categories->each(function ($category) {
            $products = Product::factory()
                ->count(2)
                ->create(['category_id' => $category->id]);

            $products->each(function ($product) {
                ProductStock::factory()->create([
                    'product_id' => $product->id,
                    'quantity' => fake()->numberBetween(0, 10),
                ]);
            });
        });

        // Create some special stock scenarios
        $outOfStockProducts = Product::factory()->count(5)->create();
        $outOfStockProducts->each(function ($product) {
            ProductStock::factory()->outOfStock()->create(['product_id' => $product->id]);
        });

        $lowStockProducts = Product::factory()->count(5)->create();
        $lowStockProducts->each(function ($product) {
            ProductStock::factory()->lowStock()->create(['product_id' => $product->id]);
        });
    }
}
