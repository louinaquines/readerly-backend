<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SchoolClassController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\ReadingSessionController;
use App\Http\Controllers\Api\StudentLookupController;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::get('me',       [AuthController::class, 'me']);
        Route::post('logout',  [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });
});

// Auth only (teachers AND students)
Route::middleware('auth:api')->group(function () {
    Route::get('students/by-user/{userId}', [StudentLookupController::class, 'byUser']);
    Route::get('students/{student}/sessions', [ReadingSessionController::class, 'index']);
    Route::get('students/{student}/sessions/{session}', [ReadingSessionController::class, 'show']);
    Route::post('students/{student}/sessions/{session}/submit', [ReadingSessionController::class, 'submit']);
    Route::get('students/{student}/stories', [ReadingSessionController::class, 'stories']);

    // Enroll — students call this to join a class
    Route::post('classes/enroll', [SchoolClassController::class, 'enrollStudent']);
    Route::post('classes/enroll-student', [SchoolClassController::class, 'enrollByStudentId']);

    Route::put('profile/update', [AuthController::class, 'updateProfile']);
    Route::put('profile/password', [AuthController::class, 'updatePassword']);
    Route::delete('profile/delete', [AuthController::class, 'deleteAccount']);
});

// Teacher only
Route::middleware(['auth:api', 'teacher'])->group(function () {
    Route::apiResource('classes', SchoolClassController::class)
        ->parameters(['classes' => 'schoolClass']);
    Route::apiResource('classes.students', StudentController::class)
        ->parameters(['classes' => 'schoolClass']);
    Route::post('students/{student}/sessions', [ReadingSessionController::class, 'store']);
    Route::post('students/{student}/sessions/{session}/approve', [ReadingSessionController::class, 'approve']);
});

