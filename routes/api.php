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
| Here is where вы можете зарегистрировать маршруты для вашего приложения.
| Эти маршруты загружаются RouteServiceProvider и все они будут
| присвоены группе middleware "api". Постарайтесь сделать их удобными!
|
*/

// Маршруты для регистрации и авторизации
Route::post('register', [RegisterController::class, 'register']);
Route::post('resend-verification-code', [RegisterController::class, 'resendVerificationCode']);
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

// Маршруты для создания, редактирования и удаления персональных визиток (требуется авторизация)
Route::group(['middleware' => 'auth:api'], function () {
    Route::post('/card-creation', [PersonalBusinessCardController::class, 'store']);
    Route::post('/card-edit/{id}', [PersonalBusinessCardController::class, 'update']);
    Route::delete('/card-delete/{id}', [PersonalBusinessCardController::class, 'destroy']);
    Route::get('/cards', [PersonalBusinessCardController::class, 'index']);
});

// Маршруты для просмотра визиток (доступны всем пользователям, в том числе неавторизованным)
Route::get('/card/{id}', [PersonalBusinessCardController::class, 'show']);

// Маршруты для создания визитки компании
//Route::middleware('auth:api')->group(function () {
//    Route::prefix('company-cards')->group(function () {
//        Route::post('/', [CompanyBusinessCardController::class, 'store']);
//        Route::put('/{id}', [CompanyBusinessCardController::class, 'update']);
//        Route::get('/{id}', [CompanyBusinessCardController::class, 'show']);
//        Route::delete('/{id}', [CompanyBusinessCardController::class, 'destroy']);
//    });
//
//    Route::prefix('employee-cards')->group(function () {
//        Route::post('/', [EmployeeBusinessCardController::class, 'store']);
//        Route::put('/{id}', [EmployeeBusinessCardController::class, 'update']);
//        Route::get('/{id}', [EmployeeBusinessCardController::class, 'show']);
//        Route::delete('/{id}', [EmployeeBusinessCardController::class, 'destroy']);
//    });
//});
