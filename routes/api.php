<?php

use App\Http\Controllers\BmiController;
use App\Http\Controllers\DailyIntakeController;
use App\Http\Controllers\FatsecretController;
use App\Http\Controllers\FoodController;
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
    Route::get('/profiles/bmr', [UserController::class, 'getBmr']);
    Route::put('/change-password', [UserController::class, 'changePassword']);
    Route::get('/profiles', [UserController::class, 'get']);
    Route::put('/profiles', [UserController::class, 'updateProfile']);
    Route::get('/myprofile', [UserController::class, 'myprofile']);
    Route::get('/user-status', [UserController::class, 'userStatus']);
    Route::post('/fill-assestment', [UserController::class, 'updateAssesment']);

    Route::get('/bmis', [BmiController::class, 'index']);

    Route::get('/foods', [FoodController::class, 'index']);
    Route::post('/foods', [FoodController::class, 'store']);

    Route::get('/daily-intake', [DailyIntakeController::class, 'get']);

    Route::get('/check-auth', [UserController::class, 'checkAuth']);
    Route::post('/logout', [UserController::class, 'logout']);
});

Route::get('/tes', function () {
    return response()->json('hai');
});

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

// APi proxy fatsecret
Route::get('/fatsecret/token', [FatsecretController::class, 'getToken']);
