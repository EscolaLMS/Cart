<?php

use EscolaSoft\Cart\Http\CartApiController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'api/cart', 'middleware' => ['auth:api']], function () {
    Route::get('/', [CartApiController::class, 'index']);
    Route::group(['middleware' => [\Illuminate\Routing\Middleware\SubstituteBindings::class]], function () {
        Route::post('/course/{course}', [CartApiController::class, 'addCourse']);
        Route::delete('/course/{course}', [CartApiController::class, 'deleteCourse']);
    });
});

