<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();

        // Create products for each category
        $categories->each(function ($category) {
            Product::factory()
                ->count(2) 
                ->create(['category_id' => $category->id]);
        });

        // Create some special stock scenarios
        Product::factory()->count(5)->outOfStock()->create();
        Product::factory()->count(5)->lowStock()->create();
    }
}
