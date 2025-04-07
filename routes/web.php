<?php

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