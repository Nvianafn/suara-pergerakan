<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:10,1');
    Route::get('forgot-password', [PasswordController::class, 'forgot'])->name('password.request');
    Route::post('forgot-password', [PasswordController::class, 'email'])->middleware('throttle:5,1')->name('password.email');
    Route::get('reset-password/{token}', [PasswordController::class, 'reset'])->name('password.reset');
    Route::post('reset-password', [PasswordController::class, 'store'])->middleware('throttle:5,1')->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('admin/ganti-password', [PasswordController::class, 'edit'])->name('password.change');
    Route::put('admin/ganti-password', [PasswordController::class, 'update'])->middleware('throttle:5,1')->name('password.update');
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
