<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductStockController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CategoryController;

Route::post('/login', [AuthController::class, 'login']);
Route::get('/products', [ProductController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::get('/products/{id}/stock', [ProductStockController::class, 'show']);
    Route::put('/products/{id}/stock', [ProductStockController::class, 'update']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']); 
    Route::get('/orders/{id}', [OrderController::class, 'show']);

    Route::get('/categories', [CategoryController::class, 'index']);
});