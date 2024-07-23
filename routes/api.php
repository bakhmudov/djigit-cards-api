<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\PersonalBusinessCardController;
use Illuminate\Support\Facades\Route;

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

Route::post('register', [RegisterController::class, 'register']);
Route::post('verify-email', [VerificationController::class, 'verify']);

// Маршруты для восстановления пароля
Route::post('password-reset', [PasswordResetController::class, 'sendResetCode']);
Route::post('password-confirmation', [PasswordResetController::class, 'confirmResetCode']);
Route::post('password-new', [PasswordResetController::class, 'saveNewPassword']);

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
});

// Маршруты для создания персональной визитки
Route::group(['middleware' => 'auth:api'], function () {
    Route::post('/card-creation', [PersonalBusinessCardController::class, 'store']);
    Route::put('/card-edit/{id}', [PersonalBusinessCardController::class, 'update']);
    Route::get('/card-edit/{id}', [PersonalBusinessCardController::class, 'show']);
});
