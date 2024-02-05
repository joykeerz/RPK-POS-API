<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PosCategoryController;
use App\Http\Controllers\Api\PosDiscountController;
use App\Http\Controllers\Api\PosInventoryController;
use App\Http\Controllers\Api\PosProductController;
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
        Route::get('/get/{id}', [PosInventoryController::class, 'getSingleInventory']);
        Route::post('/create', [PosInventoryController::class, 'createInventory']);
        Route::put('/update/{id}', [PosInventoryController::class, 'updateInventory']);
        Route::delete('/delete/{id}', [PosInventoryController::class, 'deleteInventory']);
        Route::put('/update/quantity/{id}', [PosInventoryController::class, 'updateInventoryQuantity']);
    });

    Route::prefix('product')->group(function () {
        Route::get('/', [PosProductController::class, 'getUserProducts']);
        Route::get('/get/{id}', [PosProductController::class, 'getSingleProduct']);
        Route::post('/create', [PosProductController::class, 'createSingleProduct']);
        Route::post('/create/complete', [PosProductController::class, 'createCompleteProduct']);
        Route::put('/update/{id}', [PosProductController::class, 'updateSingleProduct']);
        Route::delete('/delete/{id}', [PosProductController::class, 'deleteSingelProduct']);
    });

    Route::prefix('category')->group(function () {
        Route::get('/', [PosCategoryController::class, 'getUserCategory']);
        Route::get('/get/{id}', [PosCategoryController::class, 'getSingleCategory']);
        Route::put('/update/{id}', [PosCategoryController::class, 'updateCategory']);
        Route::post('/create', [PosCategoryController::class, 'createCategory']);
    });

    Route::prefix('discount')->group(function () {
        Route::get('/', [PosDiscountController::class, 'index']);
        Route::post('/create', [PosDiscountController::class, 'store']);
    });
});
