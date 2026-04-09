<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\Category;
use App\Models\Product;
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
            'stock' => 7, 
            'price' => 100.00
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

        $product->refresh();
        $this->assertEquals(0, $product->stock);

        $this->assertDatabaseCount('orders', 1);
    }

    // ! Pass
    public function test_database_trigger_prevents_negative_stock(): void
    {

        $category = Category::create(['name' => 'Test Category']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock' => 5,
            'price' => 50.00
        ]);

        try {
            DB::table('products')
                ->where('id', $product->id)
                ->update(['stock' => -5]);
            
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Stock cannot be negative', $e->getMessage());
        }

        $product->refresh();
        $this->assertEquals(5, $product->stock);
    }
}