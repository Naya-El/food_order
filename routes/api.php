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

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [\App\Http\Controllers\CustomerController\AuthController::class, 'logout']);

});
Route::post('/register', [\App\Http\Controllers\CustomerController\AuthController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\CustomerController\AuthController::class, 'login']);

Route::get('/test', function () {
    return response()->json(['message' => 'Laravel is alive!']);
});




Route::post('store-order', [\App\Http\Controllers\CustomerController\OrderController::class, 'storeOrder']);
