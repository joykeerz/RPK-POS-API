<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PosInventoryController;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('/check/connection', function () {
    return response()->json([
        'message' => "Connected",
        "data" => now(),
    ], 200);
});

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('account')->group(function () {
        Route::get('/', [AuthController::class, 'getLoginUser']);
        Route::get('/profile', [AuthController::class, 'getProfileUser']);
        Route::get('/check/first-login', [AuthController::class, 'isFirstTimeLogin']);
        Route::get('/update/pin', [AuthController::class, 'updateUserPin']);
    });

    Route::prefix('inventory')->group(function () {
        Route::get('/', [PosInventoryController::class, 'getUserInventory']);
        Route::get('/products', [PosInventoryController::class, 'getUserProducts']);
        Route::get('/product/{id}', [PosInventoryController::class, 'getSingleProduct']);
        Route::post('/product/create', [PosInventoryController::class, 'createSingleProduct']);
        Route::post('/product/update/{id}', [PosInventoryController::class, 'updateSingleProduct']);
        Route::get('/product/delete/{id}', [PosInventoryController::class, 'deleteSingelProduct']);
    });

    Route::prefix('category')->group(function () {
        Route::get('/', [PosInventoryController::class, 'getUserCategory']);
        Route::post('/create', [PosInventoryController::class, 'createCategory']);
    });
});
