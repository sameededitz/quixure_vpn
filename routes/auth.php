<?php

use App\Livewire\ResetPassword;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VerifyController;
use App\Livewire\Actions\Logout;
use App\Livewire\Actions\VerifyEmail;

Route::get('email/verify/view/{id}/{hash}', [VerifyController::class, 'viewEmail'])->name('email.verification.view');
Route::get('password/reset/view/{email}/{token}', [VerifyController::class, 'viewInBrowser'])->name('password.reset.view');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'LoginForm'])->name('login');

    Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('throttle:3,1');

    Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Route::get('/email/verify/{id}/{hash}', VerifyEmail::class)->middleware(['signed', 'throttle:6,1'])->withoutMiddleware(['auth'])->name('verification.verify');

    Route::post('/logout', Logout::class)->name('logout');
});