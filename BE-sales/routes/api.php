<?php

use App\Http\Controllers\AddressController;
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
    Route::post('/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');
});

Route::prefix('/address')->group(function () {
    Route::post('/tambah',[AddressController::class,'store'])->middleware('auth:sanctum');
    Route::get('/get',[AddressController::class,'getAlamat'])->middleware('auth:sanctum');
    Route::delete('/delete/{id}',[AddressController::class,'destroy'])->middleware('auth:sanctum');
    Route::get('/edit/{id}',[AddressController::class,'edit'])->middleware('auth:sanctum');
    Route::put('/update/{id}',[AddressController::class,'update'])->middleware('auth:sanctum');
});