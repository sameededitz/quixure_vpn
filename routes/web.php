<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('admin-home');
    }
    return redirect()->route('login');
})->name('home');


require __DIR__ . '/auth.php';

require __DIR__ . '/admin.php';

Route::get('/send-test-email', function () {
    \Illuminate\Support\Facades\Mail::raw('This is a test email', function ($message) {
        $message->to('sameedhassan22@gmail.com')
            ->subject('Test Email');
    });

    return 'Test email sent';
});

Route::get('/optimize-clear', function () {
    Artisan::call('optimize:clear');
    return 'Optimize Cleared';
});

Route::get('/optimize', function () {
    Artisan::call('optimize');
    return 'Optimized';
});

Route::get('/storage-link', function () {
    Artisan::call('storage:link');
    return 'Linked';
});

Route::get('/migrate-fresh', function () {
    Artisan::call('migrate:fresh --seed');
    return 'Migrated Freshed and Seeded';
});
Route::get('/migrate', function () {
    Artisan::call('migrate');
    return 'Migrated';
});

Route::get('/log-smtp', function () {
    Log::info('Current SMTP Configuration', [
        'MAIL_HOST' => env('MAIL_HOST'),
        'MAIL_PORT' => env('MAIL_PORT'),
        'MAIL_USERNAME' => env('MAIL_USERNAME'),
        'MAIL_PASSWORD' => env('MAIL_PASSWORD'),
        'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION'),
    ]);
    return 'SMTP config logged!';
});