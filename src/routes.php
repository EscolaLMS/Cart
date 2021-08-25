<?php

use EscolaLms\Cart\Http\Controllers\Admin\OrderAdminApiController;
use EscolaLms\Cart\Http\Controllers\CartApiController;
use EscolaLms\Cart\Http\Controllers\OrderApiController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'api/admin', 'middleware' => ['auth:api']], function () {
    Route::get('/orders', [OrderAdminApiController::class, 'index']);
});

Route::group(['prefix' => 'api/cart', 'middleware' => ['auth:api']], function () {
    Route::get('/', [CartApiController::class, 'index']);
    //Route::group(['middleware' => [\Illuminate\Routing\Middleware\SubstituteBindings::class]], function () {
    Route::post('/course/{course}', [CartApiController::class, 'addCourse']);
    Route::delete('/course/{course}', [CartApiController::class, 'deleteCourse']);
    Route::post('/pay', [CartApiController::class, 'pay']);
    //});
});

Route::group(['prefix' => 'api/orders', 'middleware' => ['auth:api']], function () {
    Route::get('/', [OrderApiController::class, 'index']);
});
