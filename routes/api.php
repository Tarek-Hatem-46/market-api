<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function(){
Route::apiResource('/products',ProductController::class)->only(['index','show']);
Route::apiResource('/categories',CategoryController::class)->only(['index','show']);
Route::apiResource('/orders',OrderController::class)->only(['index','show','store']);
Route::post('/orders/{order}/cancel',[OrderController::class,'cancel']);
Route::post('/orders/{order}/complete',[OrderController::class,'complete']);

Route::middleware('role:admin')->group(function(){
Route::apiResource('/products',ProductController::class)->only(['store','update','destroy']);
Route::apiResource('/categories',CategoryController::class)->only(['store','update','destroy']);
});
});

Route::controller(AuthController::class)->group(function(){
    Route::post('/register','register');
    Route::post('/login','login');
    Route::post('/logout','logout')->middleware('auth:sanctum');
});
