<?php

use App\Http\Controllers\Api\AccountancyController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\PosCategoryController;
use App\Http\Controllers\Api\PosDiscountController;
use App\Http\Controllers\Api\PosInventoryController;
use App\Http\Controllers\Api\PosProductController;
use App\Http\Controllers\Api\PosPromoController;
use App\Http\Controllers\SessionController;
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
        Route::get('/{id}', [PosDiscountController::class, 'show']);
        Route::put('/update/{id}', [PosDiscountController::class, 'update']);
        Route::delete('/delete/{id}', [PosDiscountController::class, 'destroy']);
    });

    Route::prefix('promo')->group(function () {
        Route::get('/', [PosPromoController::class, 'index']);
        Route::post('/create', [PosPromoController::class, 'store']);
        Route::get('/{id}', [PosPromoController::class, 'show']);
        Route::put('/update/{id}', [PosPromoController::class, 'update']);
        Route::delete('/delete/{id}', [PosPromoController::class, 'destroy']);
    });

    Route::prefix('accountancy')->group(function () {
        Route::get('/', [AccountancyController::class, 'index']);
        Route::get('/today', [AccountancyController::class, 'getAccountancyToday']);
        Route::get('/this-week', [AccountancyController::class, 'getAccountancyThisWeek']);
        Route::post('/between', [AccountancyController::class, 'getAccountancyBetween']);
        Route::get('/detail/{id}', [AccountancyController::class, 'show']);
    });

    Route::prefix('session')->group(function () {
        Route::get('/all/open', [SessionController::class, 'getOpenSession']);
        Route::get('/all/close', [SessionController::class, 'getclosedSession']);
        Route::get('/select/{id}', [SessionController::class, 'getSingleSession']);
        Route::post('/open', [SessionController::class, 'openSession']);
        Route::put('/close/{id}', [SessionController::class, 'closeSession']);
    });

    Route::prefix('payment-method')->group(function () {
        Route::get('/', [PaymentMethodController::class, 'index']);
        Route::post('/create', [PaymentMethodController::class, 'store']);
        Route::get('/{id}', [PaymentMethodController::class, 'show']);
        Route::put('/update/{id}', [PaymentMethodController::class, 'update']);
        Route::delete('/delete/{id}', [PaymentMethodController::class, 'destroy']);
    });

    Route::prefix('sale')->group(function () {
        Route::post('/store/all', [AccountancyController::class, 'storeAllHistory']);
    });
});
