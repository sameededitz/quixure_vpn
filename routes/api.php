<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\ServerController;
use App\Http\Controllers\Api\SocialController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VerifyController;
use App\Http\Controllers\OptionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('api.login');

    Route::post('/signup', [AuthController::class, 'signup'])->name('api.signup');

    Route::post('/login/google', [SocialController::class, 'handleGoogleCallback'])->name('api.login.google');

    Route::post('/login/apple', [SocialController::class, 'handleAppleCallback'])->name('api.login.apple');

    Route::post('/reset-password', [VerifyController::class, 'sendResetLink'])->name('api.reset.password');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

    Route::post('/purchase', [PurchaseController::class, 'addPurchase'])->name('api.add.purchase');

    Route::post('/purchase/status', [PurchaseController::class, 'Status'])->name('api.purchase');

    Route::get('/user', [UserController::class, 'user'])->name('api.user');

    Route::put('/user/update', [UserController::class, 'update'])->name('api.user.update');

    Route::delete('/user/delete', [UserController::class, 'deleteAccount'])->name('api.user.delete');
});

Route::post('/email/resend-verification', [VerifyController::class, 'resendVerify'])->name('api.verify.resend');

Route::get('/servers', [ServerController::class, 'index'])->name('api.all.servers');

Route::get('/plans', [ServerController::class, 'plans'])->name('api.all.plans');

Route::get('/options', [OptionController::class, 'getOptions'])->name('api.options');
