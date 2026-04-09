<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * Create a new class instance.
     */
    public function createOrder(int $userId, array $items): Order
    {
        // Use sql transaction to prevent other orders from adjusting the stock while this order is processing
        return DB::transaction(function () use ($userId, $items) {
            $total = 0;
            $orderItems = [];
            
            foreach ($items as $item) {
                
                $product = Product::lockForUpdate()->find($item['product_id']);

                if (!$product) {
                    throw ValidationException::withMessages([
                        'product' => ["Product with ID {$item['product_id']} not found."]
                    ]);
                }

                // Check if product is in stock
                if (!$product->inStock($item['quantity'])) {
                    
                    throw ValidationException::withMessages([
                        'stock' => ["Insufficient stock for {$product->name}. Only {$product->stock} available."]
                    ]);
                }
                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price, 
                ];
                $total += $product->price * $item['quantity'];

                $product->decrement('stock', $item['quantity']);
            }

            $subtotal = $total;
            $gst = $total * 0.10;
            $total = $subtotal + $gst;
            
            $order = Order::create([
                'user_id' => $userId,
                'total' => $total,
                'status' => 'pending',
            ]);

            foreach ($orderItems as $orderItem) {
                $order->products()->attach($orderItem['product_id'], [
                    'quantity' => $orderItem['quantity'],
                    'price' => $orderItem['price'],
                ]);
            }

            return $order->load('products');
        });
    }
}
