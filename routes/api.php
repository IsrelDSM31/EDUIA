<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\StudentApiController;
use App\Http\Controllers\Api\GradeApiController;
use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\AlertApiController;

// Auth API
Route::post('/auth/login', [App\Http\Controllers\Api\AuthApiController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [App\Http\Controllers\Api\AuthApiController::class, 'logout']);
    Route::get('/auth/user', [App\Http\Controllers\Api\AuthApiController::class, 'user']);
});

// Users API
Route::get('/users', [UserApiController::class, 'index']);
Route::get('/users/{id}', [UserApiController::class, 'show']);

// Students API
Route::get('/students', [StudentApiController::class, 'index']);

// Grades API
Route::get('/grades', [GradeApiController::class, 'index']);

// Attendance API
Route::get('/attendance', [AttendanceApiController::class, 'index']);

// Alerts API
Route::get('/alerts', [AlertApiController::class, 'index']); 