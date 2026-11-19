<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.store');
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::patch('products/{product}/restore', [ProductController::class, 'restore'])->name('products.restore');
    Route::resource('products', ProductController::class);
});

/*
 * Malformed URLs such as /products//////.......////// should not dead-end at a
 * 404. Send them back to the page the user was on instead. External referrers,
 * another malformed URL, and the fallback target itself are rejected to avoid
 * redirect loops.
 */
Route::fallback(function () {
    $safeTarget = route('dashboard');
    $previous = url()->previous();
    $base = rtrim(url('/'), '/');

    if (! is_string($previous) || $previous === '' || $previous === url()->full()) {
        return redirect()->to($safeTarget);
    }

    if ($previous !== $base && ! str_starts_with($previous, $base.'/')) {
        return redirect()->to($safeTarget);
    }

    $path = parse_url($previous, PHP_URL_PATH) ?: '';

    if (preg_match('#/{2,}|\.{2,}#', $path)) {
        return redirect()->to($safeTarget);
    }

    return redirect()->to($previous);
});
