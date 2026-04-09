<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StudentLookupController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('students/by-user/{userId}', [StudentLookupController::class, 'byUser']);
