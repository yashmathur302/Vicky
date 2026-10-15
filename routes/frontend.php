<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\AuthController as FrontendAuthController;
use App\Http\Controllers\Frontend\HomeController;

// Route::get('/clear-my-cache', function() {
//     \Illuminate\Support\Facades\Artisan::call('route:clear');
//     \Illuminate\Support\Facades\Artisan::call('view:clear');
//     \Illuminate\Support\Facades\Artisan::call('config:clear');
//     return 'All Cache Cleared Successfully!';
// });

// Guest routes
// Route::middleware('frontend.guest')->controller(FrontendAuthController::class)->group(function () {
Route::middleware('guest:web')->controller(FrontendAuthController::class)->group(function () {
    Route::get('/login', 'getLogin')->name('login');
    Route::post('/login-submit', 'postLogin')->name('login.submit');

    Route::get('/register', 'getRegister')->name('register');
    Route::post('/register-submit', 'postRegister')->name('register.submit');

    Route::get('/forgot-password', 'getForgotPassword')->name('frontend.forgot.password.index');
    Route::post('/forgot-password', 'postForgotPassword')->name('frontend.forgot.password');
    
    Route::get('/reset-password/{token}', 'getResetPassword')->name('frontend.password.reset.index');
    Route::post('/reset-password', 'postResetPassword')->name('frontend.password.reset');
});

// Email verification must remain available to a user arriving from their email.
// Route::get('/email/verify/{id}/{hash}', [FrontendAuthController::class, 'verifyEmail'])
//     ->name('verification.verify');

Route::controller(FrontendAuthController::class)->group(function () {
    Route::get('/account/report/{user}/{hash}', 'getReport')
        ->name('account.report');
        // ->middleware('signed');

    Route::post('/account/report', 'postReport')
        ->name('account.report.submit');

    // Email verification must remain available to a user arriving from their email.
    Route::get('/email/verify/{id}/{hash}', 'verifyEmail')
        ->name('verification.verify');
});

// Authenticated routes
// Route::middleware(['auth', 'check.user', 'check.banned'])->group(function () {
Route::middleware(['auth:web', 'check.user', 'check.banned'])->group(function () {

    Route::get('/keep-active', function() { return response()->json(['status' => 'active']); })->name('keep.active');

    Route::post('/logout', [FrontendAuthController::class, 'postLogout'])->name('logout');
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/feed', [HomeController::class, 'feed'])->middleware('throttle:60,1')->name('feed.load');

    Route::post('/email/verification-notification', [FrontendAuthController::class, 'resendVerificationEmail'])
        ->middleware(['throttle:1,1'])
        ->name('verification.send');
});
