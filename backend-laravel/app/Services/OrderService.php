<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * Create a new order with stock validation and concurrency protection.
     */
    public function createOrder(int $userId, array $items): Order
    {
        return DB::transaction(function () use ($userId, $items) {
            $total = 0;
            $orderItems = [];
            
            // STEP 1: Validate ALL products first (before any modifications)
            foreach ($items as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);

                if (!$product) {
                    throw ValidationException::withMessages([
                        'product' => ["Product with ID {$item['product_id']} not found."]
                    ]);
                }

                // Validate stock availability
                if ($product->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'stock' => ["Insufficient stock for {$product->name}. Only {$product->stock} available."]
                    ]);
                }
            }

            // STEP 2: All validations passed - now process the order
            foreach ($items as $item) {
                // Re-fetch with lock (already validated above)
                $product = Product::lockForUpdate()->find($item['product_id']);

                // Store order item data (snapshot price)
                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ];

                // Calculate running total
                $total += $product->price * $item['quantity'];

                // Decrement stock (safe because validation passed)
                $product->decrement('stock', $item['quantity']);
            }

            // Calculate totals with GST
            $subtotal = $total;
            $gst = $total * 0.10;
            $totalWithGst = $subtotal + $gst;
            
            // Create the order
            $order = Order::create([
                'user_id' => $userId,
                'total' => $totalWithGst,
                'status' => 'pending',
            ]);

            // Attach products to order
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