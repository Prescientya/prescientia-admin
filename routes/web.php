<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\WifiController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\LoginHistoryController;
use App\Http\Controllers\Admin\MbgOfficerController;
use App\Http\Controllers\Admin\ProfileController;
use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin Routes
use App\Http\Middleware\AdminMiddleware;

Route::prefix('admin')->middleware(AdminMiddleware::class)->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Students
    Route::resource('students', StudentController::class);
    
    // Teachers
    Route::resource('teachers', TeacherController::class);
    
    // Classes
    Route::resource('classes', ClassController::class);
    
    // WiFi Networks
    Route::resource('wifi', WifiController::class)->except(['show']);
    
    // Attendances
    Route::get('attendances', [AttendanceController::class, 'index'])->name('attendances.index');
    Route::get('attendances/students', [AttendanceController::class, 'students'])->name('attendances.students');
    Route::get('attendances/teachers', [AttendanceController::class, 'teachers'])->name('attendances.teachers');
    Route::post('attendances/record', [AttendanceController::class, 'record'])->name('attendances.record');
    
    // Calendar
    Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::post('calendar', [CalendarController::class, 'store'])->name('calendar.store');
    Route::put('calendar/{id}', [CalendarController::class, 'update'])->name('calendar.update');
    
    // Login History
    Route::get('login-history', [LoginHistoryController::class, 'index'])->name('login-history.index');
    
    // MBG Officers
    Route::post('mbg-officers/verify-password', [MbgOfficerController::class, 'verifyPassword'])->name('mbg-officers.verify-password');
    Route::resource('mbg-officers', MbgOfficerController::class);
    
    // Profile
    Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});
