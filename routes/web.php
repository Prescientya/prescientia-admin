<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\Logincontroller;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;

// Login
Route::get('/', [Logincontroller::class, 'showLoginForm'])->name('login');
Route::post('/login', [Logincontroller::class, 'Login'])->name('login.post');
Route::post('/logout', [Logincontroller::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('auth:admin')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('Dashboard');

    /* ── Data Siswa ──────────────────────────────────── */
    Route::post('/siswa/import', [StudentController::class, 'importExcel'])->name('siswa.import');
    Route::resource('/siswa', StudentController::class)->except(['create']);

});
