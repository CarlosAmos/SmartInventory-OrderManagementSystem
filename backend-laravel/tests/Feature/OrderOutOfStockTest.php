<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;

class OrderOutOfStockTest extends TestCase
{
    use RefreshDatabase;

    // ! Pass
    public function test_user_cannot_order_out_of_stock_items(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Test Category']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock' => 0, // Out of stock
            'price' => 99.99,
        ]);
        $response = $this->actingAs($user)
            ->postJson('/api/orders', [
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 1
                    ]
                ]
            ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['stock']);
        $this->assertStringContainsString('Insufficient stock', $response->json('errors.stock.0'));
        
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_user_cannot_order_more_than_available_stock(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Test Category']);
        $randomStockNumber = rand(1,9);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 49.99,
            'stock' => $randomStockNumber, // Out of stock
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/orders', [
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 10
                    ]
                ]
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['stock']);

        $this->assertStringContainsString('Only '.$randomStockNumber.' available', $response->json('errors.stock.0'));
        
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => $randomStockNumber
        ]);
    }
}