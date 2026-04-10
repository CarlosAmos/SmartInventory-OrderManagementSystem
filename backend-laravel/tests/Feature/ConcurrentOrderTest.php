<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;

class ConcurrentOrderTest extends TestCase
{
    use RefreshDatabase;

    // ! Pass
    public function test_concurrent_orders_prevent_negative_stock(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Test Category']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 100.00,
        ]);
        ProductStock::create([
            'product_id' => $product->id,
            'quantity' => 7,
        ]);

        $response1 = $this->actingAs($user)
            ->postJson('/api/orders', [
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 7
                    ]
                ]
            ]);

        $response2 = $this->actingAs($user)
            ->postJson('/api/orders', [
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 5
                    ]
                ]
            ]);

        $response1->assertStatus(201);

        $response2->assertStatus(422);
        $response2->assertJsonValidationErrors(['stock']);

        $this->assertDatabaseHas('product_stock', [
            'product_id' => $product->id,
            'quantity' => 0,
        ]);

        $this->assertDatabaseCount('orders', 1);
    }

    // ! Pass
    public function test_database_trigger_prevents_negative_stock(): void
    {
        $category = Category::create(['name' => 'Test Category']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 50.00,
        ]);
        ProductStock::create([
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        try {
            DB::table('product_stock')
                ->where('product_id', $product->id)
                ->update(['quantity' => -5]);

            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Stock quantity cannot be negative', $e->getMessage());
        }

        $this->assertDatabaseHas('product_stock', [
            'product_id' => $product->id,
            'quantity' => 5,
        ]);
    }
}
