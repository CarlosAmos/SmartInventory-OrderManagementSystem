<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStock;
use App\Http\Resources\ProductStockResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductStockController extends Controller
{
    public function show(int $productId): ProductStockResource|JsonResponse
    {
        $product = Product::findOrFail($productId);
        $stock = ProductStock::where('product_id', $product->id)->firstOrFail();

        return new ProductStockResource($stock);
    }

    public function update(Request $request, int $productId): ProductStockResource|JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['sometimes', 'integer', 'min:0'],
            'low_stock_threshold' => ['sometimes', 'integer', 'min:0'],
        ]);

        $product = Product::findOrFail($productId);

        $stock = ProductStock::updateOrCreate(
            ['product_id' => $product->id],
            $validated
        );

        return new ProductStockResource($stock);
    }
}
