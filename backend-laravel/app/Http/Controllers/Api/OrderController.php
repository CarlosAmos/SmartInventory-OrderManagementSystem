<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Requests\CreateOrderRequest;
use App\Services\OrderService;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index()
    {
        $order = 1;
        $order = Order::with(['products'])->where('user_id', auth()->id())->get();
        return response()->json($order);
    }

    public function show($id)
    {
        $order = 1;
        $order = Order::with(['products'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        return response()->json($order);
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
