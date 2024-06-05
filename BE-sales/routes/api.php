<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\IngredientController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/user', [AuthController::class, 'edit'])->middleware('auth:sanctum');
    Route::post('/update', [AuthController::class, 'update'])->middleware('auth:sanctum', 'role:User');
    Route::post('/update/admin', [AuthController::class, 'updateAdmin'])->middleware('auth:sanctum', 'role:Admin');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

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
    Route::post('/get', [IngredientController::class, 'index']);
    Route::get('/getCart/{cartItemId}', [IngredientController::class, 'indexCart']);

    Route::middleware('role:admin')->group(function () {
        Route::post('/add', [IngredientController::class, 'store']);
        Route::post('/update/{id}', [IngredientController::class, 'update']);
        Route::delete('/delete/{id}', [IngredientController::class, 'destroy']);
        Route::get('/get/{id}', [IngredientController::class, 'getOne']);

    });
});

Route::prefix('/cart')->middleware(['auth:sanctum', 'role:User'])->group(function () {
    Route::post('/add', [CartController::class, 'store']);
    Route::get('/get', [CartController::class, 'index']);
    Route::post('/customize/cart/{cartItemId}', [CartController::class, 'addCustomizationCart']);
    Route::post('/customize/menu/{menuId}', [CartController::class, 'addCustomizationMenu']);
    Route::get('/customize/get/{cartItemId}', [CartController::class, 'getCustomization']);
    Route::get('/select/{id}', [CartController::class, 'selectCartItem']);
    Route::get('/unselect/{id}', [CartController::class, 'unselectCartItem']);
    Route::post('/update/{id}', [CartController::class, 'update']);
    Route::delete('/delete/{id}', [CartController::class, 'destroy']);
});

Route::get('/getMenu', [MenuController::class, 'index']);
Route::get('menu/get/{id}', [MenuController::class, 'getOne']);

Route::prefix('/biteship')->group(function () {
    Route::get('/areas', [AddressController::class, 'getAreas']);

});