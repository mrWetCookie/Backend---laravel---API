<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
//use App\Http\Controllers\Api\Auth\AuthController;
//use App\Http\Controllers\Api\Auth\RegisterController;
//use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;

// Ruta protegida con Sanctum
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rutas de usuarios (API Resource)
Route::apiResource('users', UserController::class);
