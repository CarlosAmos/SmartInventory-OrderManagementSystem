<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with('category');
        
        // ? Filter by search term (name or SKU)
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('sku', 'like', '%' . $searchTerm . '%');
            });
        }
        
        // ? Filter by category
        if ($request->has('category') && $request->category !== 'all') {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('id', $request->category);
            });
        }

        $products = $query->get();

        return ProductResource::collection($products);
    }

    public function show($id)
    {
        //
        $product = Product::with('category')->findOrFail($id);
        return response()->json($product);
    }

    public function categories()
    {
        $categories = \App\Models\Category::orderBy('name')->get();
        
        return response()->json(
            $categories->pluck('name')
        );
    }
}
