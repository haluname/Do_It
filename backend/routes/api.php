<?php

use App\Http\Controllers\Auth\OtpController as AuthOtpController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ThreadController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\Auth\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\FileController;



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');

Route::post('/password/otp', [OtpController::class, 'sendOtp']);
Route::post('/password/reset-with-otp', [OtpController::class, 'resetWithOtp']);
Route::post('/verify-otp', [OtpController::class, 'verifyRegistrationOtp']);


Route::middleware('auth:sanctum')->group(function () {  

    // CRUD Goals
    Route::get('/goals', [GoalController::class, 'index']);
    Route::post('/goals', [GoalController::class, 'store']);
    Route::get('/goals/today', [GoalController::class, 'today']);
    Route::get('/goals/{id}', [GoalController::class, 'show']);
    Route::put('/goals/{id}', [GoalController::class, 'update']);
    Route::delete('/goals/{id}', [GoalController::class, 'destroy']);


    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);

    Route::get('files/{file}/preview', [FileController::class, 'show']);
    Route::get('files/{file}/download', [FileController::class, 'download']);
    Route::apiResource('files', FileController::class)->except(['update']);
    Route::put("/apply-penalties", [GoalController::class, 'penalty']);

    Route::post('/threads', [ThreadController::class, 'store']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/threads', [ThreadController::class, 'index']);

});

