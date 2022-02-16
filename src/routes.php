<?php

use EscolaLms\Cart\Http\Controllers\Admin\OrderAdminApiController;
use EscolaLms\Cart\Http\Controllers\Admin\ProductAdminApiController;
use EscolaLms\Cart\Http\Controllers\CartApiController;
use EscolaLms\Cart\Http\Controllers\OrderApiController;
use EscolaLms\Cart\Http\Controllers\ProductsApiController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'api/admin', 'middleware' => ['auth:api']], function () {
    Route::get('/orders', [OrderAdminApiController::class, 'index']);
    Route::get('/orders/{id}', [OrderAdminApiController::class, 'show']);
    Route::post('/products/attach', [ProductAdminApiController::class, 'attach']);
    Route::post('/products/detach', [ProductAdminApiController::class, 'detach']);
});

Route::group(['prefix' => 'api/cart', 'middleware' => ['auth:api']], function () {
    Route::get('/', [CartApiController::class, 'index']);
    Route::post('/add', [CartApiController::class, 'add']);
    Route::post('/remove', [CartApiController::class, 'removeProduct']);
    Route::delete('/{id}', [CartApiController::class, 'removeCartItem']);
    Route::post('/pay', [CartApiController::class, 'pay']);
});

Route::group(['prefix' => 'api/products', 'middleware' => ['auth:api']], function () {
    Route::get('/', [ProductsApiController::class, 'index']);
});

Route::group(['prefix' => 'api/orders', 'middleware' => ['auth:api']], function () {
    Route::get('/', [OrderApiController::class, 'index']);
});
