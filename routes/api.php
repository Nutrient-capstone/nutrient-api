<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

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

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/tes-login', function () {
        return response()->json('yu sudah login');
    });
    Route::get('/profiles/bmi', [UserController::class, 'getBmi']);
    Route::put('/change-password', [UserController::class, 'changePassword']);
    Route::get('/profiles', [UserController::class, 'get']);
    Route::put('/profiles', [UserController::class, 'updateProfile']);
    Route::post('/logout', [UserController::class, 'logout']);
});

Route::get('/tes', function () {
    return response()->json('hai');
});

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
