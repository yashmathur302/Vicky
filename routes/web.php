<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AuthController;
// use \App\Http\Middleware\AdminAutoLogout;
use \App\Http\Middleware\CheckAdminStatus;

//FASTER REDIRECTS
Route::redirect('/', '/login');
Route::redirect('/admin', '/admin/login');

// Route::redirect('/', '/admin/dashboard');
// Route::redirect('/login', '/admin/login')->name('login');

Route::prefix('admin')->name('admin.')->group(function () {

    // 1. UNSECURED ROUTES (Guest Accessible)
    // Route::middleware('guest')->controller(AuthController::class)->group(function () {
    Route::middleware('guest:admin')->controller(AuthController::class)->group(function () {
        
        Route::get('/login', 'getLogin')->name('auth.login');
        Route::post('/login', 'postLogin')->name('login');
        
        Route::get('/forgot/password', 'getForgotPassword')->name('forgot.password.index');
        Route::post('/forgot/password', 'postForgotPassword')->name('forgot.password');

        Route::get('/reset-password/{token}', 'getResetPassword')->name('password.reset.index');
        Route::post('/reset-password', 'postResetPassword')->name('password.reset');
        
    });

    // 2. SECURED ROUTES (Requires Login)
    // Route::middleware(['auth', CheckAdminStatus::class, AdminAutoLogout::class])->group(function () {
    // Route::middleware(['auth', CheckAdminStatus::class])->group(function () {
    Route::middleware(['auth:admin', 'check.admin'])->group(function () {
        
        // --- Ping ke liye ---
        Route::get('/keep-active', function() { return response()->json(['status' => 'active']); })->name('keep.active');
        
        // Single Routes
        Route::post('/logout', [AuthController::class, 'postLogout'])->name('logout');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // User Management
        Route::prefix('users')->name('users.')->controller(UserController::class)->group(function () {
            Route::get('list', 'getList')->name('list');
            Route::get('create', 'getCreate')->name('create.index');
            Route::post('create', 'postCreate')->name('create');
            Route::get('update/{id}', 'getUpdate')->name('update.index');
            Route::post('update/{id}', 'postUpdate')->name('update');
            Route::delete('delete/{id}', 'destroy')->name('delete'); 
            Route::post('change/status/{id}', 'getChangeStatus')->name('change.status');
            Route::get('banned-users', 'getBannedList')->name('banned_user');
        });

        Route::prefix('events')->name('events.')->controller(EventController::class)->group(function () {
            Route::get('list', 'getList')->name('list');
            Route::get('update/{id}', 'getUpdate')->name('update.index');
            Route::post('update/{id}', 'postUpdate')->name('update');
            Route::delete('delete/{id}', 'destroy')->name('delete');
        });

        Route::prefix('categories')->name('categories.')->controller(CategoryController::class)->group(function () {
            Route::get('list', 'getList')->name('list');
            Route::post('create', 'postCreate')->name('create');
            Route::post('update/{id}', 'postUpdate')->name('update');
            Route::delete('delete/{id}', 'destroy')->name('delete');
        });
        
        // Future Modules
    });
});

// Route::get('/run-clear-9f3k2x7q', function () {
//     abort_unless(request('key') === 'PUT_A_LONG_RANDOM_STRING_HERE', 404);
//     Artisan::call('optimize:clear');
//     return nl2br(Artisan::output());
// });

