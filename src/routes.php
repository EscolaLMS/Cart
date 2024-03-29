<?php

use EscolaLms\Cart\Http\Controllers\Admin\OrderAdminApiController;
use EscolaLms\Cart\Http\Controllers\Admin\ProductableAdminApiController;
use EscolaLms\Cart\Http\Controllers\Admin\ProductAdminApiController;
use EscolaLms\Cart\Http\Controllers\CartApiController;
use EscolaLms\Cart\Http\Controllers\OrderApiController;
use EscolaLms\Cart\Http\Controllers\PaymentApiController;
use EscolaLms\Cart\Http\Controllers\ProductablesApiController;
use EscolaLms\Cart\Http\Controllers\ProductApiController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'api/admin', 'middleware' => ['auth:api']], function () {
    Route::get('/orders', [OrderAdminApiController::class, 'index']);
    Route::get('/orders/export', [OrderAdminApiController::class, 'export']);
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
});

Route::group(['prefix' => 'api', 'middleware' => ['auth:api']], function () {
    Route::post('cart/pay', [PaymentApiController::class, 'pay']);
    Route::post('product/{id}/pay', [PaymentApiController::class, 'payProduct']);
});

Route::group(['prefix' => 'api/products'], function () {
    Route::group(['prefix' => 'my', 'middleware' => ['auth:api']], function () {
        Route::get('/', [ProductApiController::class, 'indexMy']);
    });

    Route::get('/{id}', [ProductApiController::class, 'read']);
    Route::get('/', [ProductApiController::class, 'index']);
    Route::post('/cancel/{id}', [ProductApiController::class, 'cancel'])->middleware(['auth:api']);
});

Route::group(['prefix' => 'api/productables', 'middleware' => ['auth:api']], function () {
    Route::post('attach', [ProductablesApiController::class, 'attach']);
});

Route::group(['prefix' => 'api/orders', 'middleware' => ['auth:api']], function () {
    Route::get('/', [OrderApiController::class, 'index']);
    Route::get('/{id}', [OrderApiController::class, 'read']);
});
