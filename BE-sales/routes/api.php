<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\VerificationsController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/user', [AuthController::class, 'edit'])->middleware('auth:sanctum');
    Route::get('/notif', [TransactionController::class, 'getAdminNotif'])->middleware('auth:sanctum');
    Route::post('/update', [AuthController::class, 'update'])->middleware('auth:sanctum', 'role:User');
    Route::post('/update/admin', [AuthController::class, 'updateAdmin'])->middleware('auth:sanctum', 'role:Admin');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/toogle', [AuthController::class, 'toogleBukaTutup'])->middleware('auth:sanctum', 'role:Admin');
    Route::get('/toogle/admin', [AuthController::class, 'toogleAdmin'])->middleware('auth:sanctum');
});

Route::get('/email/verify/{id}/{hash}', [VerificationsController::class, 'verify'])->name('verification.verify');
Route::post('/email/resend', [VerificationsController::class, 'resend'])->name('verification.resend');

Route::prefix('/address')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/add', [AddressController::class, 'store']);
    Route::get('/get', [AddressController::class, 'getAlamat']);
    Route::delete('/delete/{id}', [AddressController::class, 'destroy']);
    Route::get('/edit/{id}', [AddressController::class, 'edit']);
    Route::put('/update/{id}', [AddressController::class, 'update']);
});

Route::prefix('/menu')->middleware(['auth:sanctum', 'role:Admin'])->group(function () {
    Route::post('/add', [MenuController::class, 'store']);
    Route::get('/get', [MenuController::class, 'getMenuAdmin']);
    Route::get('/get/{id}', [MenuController::class, 'getOne']);
    Route::post('/update/{id}', [MenuController::class, 'update']);
    Route::delete('/delete/{id}', [MenuController::class, 'destroy']);
});

Route::prefix('/ingredient')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/get/{menuId}', [IngredientController::class, 'index']);
    Route::get('/getCart/{cartItemId}', [IngredientController::class, 'indexCart']);

    Route::middleware('role:Admin')->group(function () {
        Route::post('/add', [IngredientController::class, 'store']);
        Route::put('/update/{id}', [IngredientController::class, 'update']);
        Route::delete('/delete/{id}', [IngredientController::class, 'destroy']);
        Route::get('/get/{id}', [IngredientController::class, 'getOne']);

    });
});

Route::prefix('/cart')->middleware(['auth:sanctum', 'role:User'])->group(function () {
    Route::post('/add', [CartController::class, 'store']);
    Route::get('/get', [CartController::class, 'index']);
    Route::get('/item/{cartItemId}', [CartController::class, 'getCartItem']);
    Route::get('/select/{id}', [CartController::class, 'selectCartItem']);
    Route::get('/unselect/{id}', [CartController::class, 'unselectCartItem']);
    Route::post('/update/{id}', [CartController::class, 'update']);
    Route::delete('/delete/{id}', [CartController::class, 'destroy']);
});

Route::prefix('/customize')->middleware(['auth:sanctum', 'role:User'])->group(function () {
    Route::post('/cart/{cartItemId}', [CartController::class, 'addCustomizationCart']);
    Route::post('/menu/{menuId}', [CartController::class, 'addCustomizationMenu']);
    Route::get('/get/{cartItemId}', [CartController::class, 'getCustomization']);
    Route::delete('/delete/cartItem/{cartItemId}', [CartController::class, 'deleteCustomizationCart']);
    Route::delete('/delete/menu/{cartItemId}', [CartController::class, 'deleteCustomizationMenu']);
});

Route::get('/getMenu', [MenuController::class, 'index']);
Route::get('menu/get/{id}', [MenuController::class, 'getOne']);
Route::post('/midtrans/webhook', [TransactionController::class, 'midtransWebhook']);


Route::prefix('/biteship')->group(function () {
    Route::get('/areas', [AddressController::class, 'getAreas']);
    Route::post('/kurir', [CartController::class, 'getShippingRates']);
    Route::post('/webhook', [TransactionController::class, 'biteshipWebhook']);
});

Route::prefix('/order')->middleware(['auth:sanctum'])->group(function () {

    Route::get('/detail/{orderId}', [TransactionController::class, 'detailOrder']);
    Route::get('/tracking/{orderId}', [TransactionController::class, 'retrieveTracking']);

    Route::middleware('role:User')->group(function () {
        Route::post('/checkout', [TransactionController::class, 'createOrder']);
        Route::get('/getOrder', [TransactionController::class, 'getUserOrders']);
        Route::post('/update', [TransactionController::class, 'updateOrderStatus']);
    });

    Route::middleware('role:Admin')->group(function () {
        Route::get('/adminOrder', [TransactionController::class, 'getAdminOrders']);
        Route::get('/create/{orderId}', [TransactionController::class, 'adminCreateOrder']);
    });
});