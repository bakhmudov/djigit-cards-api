<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\CompanyBusinessCardController;
use App\Http\Controllers\EmployeeBusinessCardController;
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
Route::group(['middleware' => 'jwt'], function () {
    Route::post('card-creation', [PersonalBusinessCardController::class, 'store']);
    Route::put('card-edit/{id}', [PersonalBusinessCardController::class, 'update']);
    Route::get('cards', [PersonalBusinessCardController::class, 'index']);
});

Route::get('card/{id}', [PersonalBusinessCardController::class, 'show']);
Route::get('company-card/{id}', [CompanyBusinessCardController::class, 'show']);
Route::get('employee-card/{id}', [EmployeeBusinessCardController::class, 'show']);

// Маршруты для создания визитки компании
Route::middleware('auth:api')->group(function () {
    Route::prefix('company-cards')->group(function () {
        Route::post('/', [CompanyBusinessCardController::class, 'store']);
        Route::put('{id}', [CompanyBusinessCardController::class, 'update']);
        Route::delete('{id}', [CompanyBusinessCardController::class, 'destroy']);
    });

    Route::prefix('employee-cards')->group(function () {
        Route::post('/', [EmployeeBusinessCardController::class, 'store']);
        Route::put('{id}', [EmployeeBusinessCardController::class, 'update']);
        Route::delete('{id}', [EmployeeBusinessCardController::class, 'destroy']);
    });
});
