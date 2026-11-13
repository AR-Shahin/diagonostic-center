<?php

use App\Http\Controllers\Portal\AccountController;
use App\Http\Controllers\Portal\AuthController;
use App\Http\Middleware\EnsurePortalPasswordChanged;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

// Patient account on the website: login with phone number, see own and family bills.
Route::middleware(SetLocale::class)->prefix('account')->group(function () {
    Route::middleware('guest:web')->group(function () {
        Route::get('login', [AuthController::class, 'create'])->name('login');
        Route::post('login', [AuthController::class, 'store'])->name('portal.login');
    });

    Route::middleware('auth:web')->name('portal.')->group(function () {
        Route::post('logout', [AuthController::class, 'destroy'])->name('logout');
        Route::get('password', [AccountController::class, 'editPassword'])->name('password.edit');
        Route::put('password', [AccountController::class, 'updatePassword'])->name('password.update');

        Route::middleware(EnsurePortalPasswordChanged::class)->group(function () {
            Route::get('/', [AccountController::class, 'dashboard'])->name('dashboard');
            Route::get('invoices/{invoice}', [AccountController::class, 'invoice'])->name('invoice');
        });
    });
});
