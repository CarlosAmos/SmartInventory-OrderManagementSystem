<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $products = Product::all();

        $this->command->info('Creating orders...');

        // ? Create 2 completed orders
        for ($i = 0; $i < 2; $i++) {
            $order = Order::create([
                'user_id' => $user->id,
                'total' => 0, 
                'status' => 'completed',
                'created_at' => now()->subDays(rand(1, 30)),
            ]);

            $this->attachProductsToOrder($order, $products);
        }

        // ? Create 2 pending orders
        for ($i = 0; $i < 2; $i++) {
            $order = Order::create([
                'user_id' => $user->id,
                'total' => 0,
                'status' => 'pending',
                'created_at' => now()->subDays(rand(1, 7)),
            ]);

            $this->attachProductsToOrder($order, $products);
        }

        // ? Create 2 cancelled orders
        for ($i = 0; $i < 2; $i++) {
            $order = Order::create([
                'user_id' => $user->id,
                'total' => 0,
                'status' => 'cancelled',
                'created_at' => now()->subDays(rand(1, 60)),
            ]);

            $this->attachProductsToOrder($order, $products);
        }

        //$this->command->info('Orders created successfully!');
    }

    private function attachProductsToOrder(Order $order, $products)
    {
        // ? Pick 1-4 random products
        $selectedProducts = $products->random(rand(1, 5));
        $total = 0;

        foreach ($selectedProducts as $product) {
            $quantity = rand(1, 3);
            $price = $product->price;

            // ? Attach product to order
            $order->products()->attach($product->id, [
                'quantity' => $quantity,
                'price' => $price,
            ]);

            $total += $price * $quantity;
        }

        // ? Update order total
        $subtotal = $total;
        $gst = $subtotal * 0.10;
        $totalWithGst = $subtotal + $gst;

        $order->update(['total' => $totalWithGst]);
    }
}