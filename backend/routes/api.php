<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;




Route::get('/user', function (Request $request) {
    return response()->json(['user' => Auth::user()]);
})->middleware('auth');

Route::get('/users', [UserController::class, 'index']);

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout']);

Route::get("/shosh", [UserController::class, 'shosh'])->middleware('auth:sanctum');



Route::get('/prova', function (Request $request) {
    return response()->json(['user' => Auth::user()]);
});









