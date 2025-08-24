<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


// Authentication

Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Cart
    Route::post('add-cart',[App\Http\Controllers\CartController::class,'addToCart']);
    Route::post('update-qty/{id}',[App\Http\Controllers\CartController::class,'updateItemQuantity']);
    Route::delete('remove-item/{id}',[App\Http\Controllers\CartController::class,'removeItem']);
    Route::delete('clear-cart/{id}',[App\Http\Controllers\CartController::class,'clearCart']);
    Route::post('confirm-cart',[App\Http\Controllers\CartController::class,'confirmCart']);

    // Favorite
    Route::post('save-favorite', [App\Http\Controllers\HomeController::class, 'saveFavorite']);
    Route::get('favorite-list', [App\Http\Controllers\HomeController::class, 'favoritesList']);
    Route::delete('remove-favorite-item', [App\Http\Controllers\HomeController::class, 'removeFromFavorite']);
    Route::delete('clear-favorite', [App\Http\Controllers\HomeController::class, 'clearFavorite']);

    // Items
    Route::get('items',[App\Http\Controllers\HomeController::class,'standardItem']);
    Route::get('item-details/{id}',[App\Http\Controllers\HomeController::class,'itemDetails']);


    Route::get('/logout', [\App\Http\Controllers\AuthController::class, 'logout']);



});








