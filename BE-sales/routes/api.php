<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\MenuController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('/auth')->group(function () {
    Route::post('/login',[AuthController::class,'login']);
    Route::post('/register',[AuthController::class,'register']);
    Route::get('/user',[AuthController::class,'edit'])->middleware('auth:sanctum');
    Route::post('/update',[AuthController::class,'update'])->middleware('auth:sanctum');
    Route::post('/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');
});

Route::prefix('/address')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/add',[AddressController::class,'store']);
    Route::get('/get',[AddressController::class,'getAlamat']);
    Route::delete('/delete/{id}',[AddressController::class,'destroy']);
    Route::get('/edit/{id}',[AddressController::class,'edit']);
    Route::put('/update/{id}',[AddressController::class,'update']);
});

Route::prefix('/menu')->middleware(['auth:sanctum', 'role:Admin'])->group(function () {
    Route::post('/add', [MenuController::class, 'store']);
    Route::get('/get', [MenuController::class, 'getMenuAdmin']);
    Route::get('/get/{id}', [MenuController::class, 'getOne']);
    Route::post('/update/{id}', [MenuController::class, 'update']);
    Route::delete('/delete/{id}', [MenuController::class, 'destroy']);
});


Route::prefix('/ingredient')->middleware(['auth:sanctum', 'role:Admin'])->group(function () {
    Route::post('/add',[IngredientController::class,'store']);
    Route::get('/get',[IngredientController::class,'index']);
    Route::get('/get/{id}',[IngredientController::class,'getOne']);
    Route::put('/update/{id}',[IngredientController::class,'update']);
    Route::delete('/delete/{id}',[IngredientController::class,'destroy']);
});

Route::get('/getMenu', [MenuController::class, 'index']);
Route::get('menu/get/{id}', [MenuController::class, 'getOne']);