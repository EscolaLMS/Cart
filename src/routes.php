<?php

use EscolaLms\Cart\Http\Controllers\Admin\OrderAdminApiController;
use EscolaLms\Cart\Http\Controllers\Admin\ProductableAdminApiController;
use EscolaLms\Cart\Http\Controllers\Admin\ProductAdminApiController;
use EscolaLms\Cart\Http\Controllers\CartApiController;
use EscolaLms\Cart\Http\Controllers\OrderApiController;
use EscolaLms\Cart\Http\Controllers\ProductApiController;
use Illuminate\Routing\Route;

Route::group(['prefix' => 'api/admin', 'middleware' => ['auth:api']], function () {
    Route::get('/orders', [OrderAdminApiController::class, 'index']);
    Route::get('/orders/{id}', [OrderAdminApiController::class, 'read']);

    Route::get('/products', [ProductAdminApiController::class, 'index']);
    Route::post('/products', [ProductAdminApiController::class, 'create']);
    Route::get('/products/{id}', [ProductAdminApiController::class, 'read'])->whereNumber('id');
    Route::put('/products/{id}', [ProductAdminApiController::class, 'update'])->whereNumber('id');
    Route::delete('/products/{id}', [ProductAdminApiController::class, 'delete'])->whereNumber('id');

    Route::post('/products/{id}/trigger-event-manually/{idTemplate}', [ProductAdminApiController::class, 'triggerEventManuallyForUsers'])->whereNumber(['id', 'idTemplate']);

    Route::post('/products/{id}/attach', [ProductAdminApiController::class, 'attach'])->whereNumber('id');
    Route::post('/products/{id}/detach', [ProductAdminApiController::class, 'detach'])->whereNumber('id');

    Route::get('/productables', [ProductableAdminApiController::class, 'index']);
    Route::get('/productables/registered', [ProductableAdminApiController::class, 'registered']);
    Route::get('/productables/product', [ProductableAdminApiController::class, 'product']);
    Route::post('/productables/attach', [ProductableAdminApiController::class, 'attach']);
    Route::post('/productables/detach', [ProductableAdminApiController::class, 'detach']);
});

Route::group(['prefix' => 'api/cart', 'middleware' => ['auth:api']], function () {
    Route::get('/', [CartApiController::class, 'index']);
    Route::post('/products', [CartApiController::class, 'setProductQuantity']);
    Route::post('/missing', [CartApiController::class, 'addMissingProducts']);
    Route::delete('/products/{id}', [CartApiController::class, 'remove']);
    Route::post('/add', [CartApiController::class, 'addProductable']);
    Route::post('/pay', [CartApiController::class, 'pay']);
});

Route::group(['prefix' => 'api/products'], function () {
    Route::get('/{id}', [ProductApiController::class, 'read']);
    Route::get('/', [ProductApiController::class, 'index']);
});

Route::group(['prefix' => 'api/orders', 'middleware' => ['auth:api']], function () {
    Route::get('/', [OrderApiController::class, 'index']);
    Route::get('/{id}', [OrderApiController::class, 'read']);
});
