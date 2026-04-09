<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Requests\CreateOrderRequest;
use App\Services\OrderService;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\OrderResource;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index()
    {
        $orders = Order::with(['products'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();
        return OrderResource::collection($orders);
    }

    public function show($id)
    {
        $orders = Order::with(['products'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->findOrFail($id);
        return OrderResource::collection($orders);
    }

    public function store(CreateOrderRequest $request)
    {
        try {
            $order = $this->orderService->createOrder(
                auth()->id(),
                $request->input('items')
            );

            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Order creation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
