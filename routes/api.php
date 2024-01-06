<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\SocialAuth\SocialAuthController;
use App\Http\Controllers\Auth\EmailVerificationController;

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


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::post('/verify-email', [EmailVerificationController::class, 'emailVerification']);
Route::post('/resend-verify-email', [EmailVerificationController::class, 'resendEmailVerification']);

Route::post('/forget-password', [ResetPasswordController::class, 'forgetPassword']);
Route::post('/verify-code', [ResetPasswordController::class, 'verifyCode']);
Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword']);

Route::group(['middleware' => 'web'], function () {
    Route::get('/register/{provider}', [SocialAuthController::class, 'redirect']);
    Route::get('/login/{provider}', [SocialAuthController::class, 'redirect']);
});
Route::get('/register/{provider}/callback', [SocialAuthController::class, 'callback']);

Route::post('/social-auth', [SocialAuthController::class, 'socialAuth']);

require_once __dir__.'/profile.php';
require_once __dir__.'/course.php';
require_once __dir__.'/student.php';
