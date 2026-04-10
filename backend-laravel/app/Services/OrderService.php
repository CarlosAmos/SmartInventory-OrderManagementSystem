<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProductStock;
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

            // STEP 1: Validate ALL stock first (before any modifications)
            foreach ($items as $item) {
                $productStock = ProductStock::where('product_id', $item['product_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$productStock) {
                    throw ValidationException::withMessages([
                        'product' => ["Product with ID {$item['product_id']} not found."]
                    ]);
                }

                if ($productStock->quantity < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'stock' => [
                            "Insufficient stock for {$productStock->product->name}. " .
                            "Only {$productStock->quantity} available."
                        ]
                    ]);
                }
            }

            // STEP 2: All validations passed - now process the order
            foreach ($items as $item) {
                $productStock = ProductStock::where('product_id', $item['product_id'])
                    ->lockForUpdate()
                    ->first();

                $product = $productStock->product;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ];

                $total += $product->price * $item['quantity'];

                $productStock->decrement('quantity', $item['quantity']);
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
