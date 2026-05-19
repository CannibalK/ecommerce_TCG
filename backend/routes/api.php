<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SetController;
use Illuminate\Support\Facades\Route;

// Rutas públicas — cualquiera puede consultarlas sin estar logueado
Route::prefix('v1')->group(function () {

    // Catálogo
    Route::get('games',       [GameController::class, 'index']);
    Route::get('games/{game}', [GameController::class, 'show']);

    Route::get('sets',        [SetController::class, 'index']);
    Route::get('sets/{set}',  [SetController::class, 'show']);

    Route::get('cards',        [CardController::class, 'index']);
    Route::get('cards/{card}', [CardController::class, 'show']);

    Route::get('products',           [ProductController::class, 'index']);
    Route::get('products/{product}', [ProductController::class, 'show']);

    // Autenticación
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login',    [AuthController::class, 'login']);

    // Carrito sin login (guest checkout)
    Route::get('cart',                       [CartController::class, 'show']);
    Route::post('cart/items',                [CartController::class, 'addItem']);
    Route::patch('cart/items/{item}',        [CartController::class, 'updateItem']);
    Route::delete('cart/items/{item}',       [CartController::class, 'removeItem']);
    Route::delete('cart',                    [CartController::class, 'clear']);

    // Rutas protegidas — requieren token de Sanctum
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me',      [AuthController::class, 'me']);

        // Listados de productos (solo sellers/admin)
        Route::post('products',              [ProductController::class, 'store']);
        Route::patch('products/{product}',   [ProductController::class, 'update']);
        Route::delete('products/{product}',  [ProductController::class, 'destroy']);

        // Órdenes
        Route::get('orders',         [OrderController::class, 'index']);
        Route::post('orders',        [OrderController::class, 'store']);
        Route::get('orders/{order}', [OrderController::class, 'show']);
    });
});
