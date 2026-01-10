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
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\MbgOfficerController;
use App\Http\Controllers\Admin\TeachedClassController;
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
Route::prefix('admin')->middleware('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Students
    Route::resource('students', StudentController::class);
    
    // Teachers
    Route::resource('teachers', TeacherController::class);
    
    // Classes
    Route::resource('classes', ClassController::class);
    
    // Subjects (Mata Pelajaran)
    Route::resource('subjects', \App\Http\Controllers\Admin\SubjectsController::class);
    
    // Teached Classes (Guru Mengajar)
    Route::get('teached-classes', [TeachedClassController::class, 'index'])->name('teached-classes.index');
    Route::get('teached-classes/{class}/edit', [TeachedClassController::class, 'edit'])->name('teached-classes.edit');
    Route::post('teached-classes/{class}', [TeachedClassController::class, 'store'])->name('teached-classes.store');
    Route::put('teached-classes/{teachedClass}', [TeachedClassController::class, 'update'])->name('teached-classes.update');
    Route::delete('teached-classes/{teachedClass}', [TeachedClassController::class, 'destroy'])->name('teached-classes.destroy');
    
    // WiFi Networks
    Route::resource('wifi', WifiController::class)->except(['show']);
    Route::post('wifi/convert', [WifiController::class, 'convert'])->name('wifi.convert');
    
    // Attendances
    Route::get('attendances', [AttendanceController::class, 'index'])->name('attendances.index');
    Route::get('attendances/students', [AttendanceController::class, 'students'])->name('attendances.students');
    Route::get('attendances/teachers', [AttendanceController::class, 'teachers'])->name('attendances.teachers');
    Route::post('attendances/record', [AttendanceController::class, 'record'])->name('attendances.record');
    Route::get('attendances/{role}/{id}', [AttendanceController::class, 'show'])->name('attendances.show');
    Route::put('attendances/{role}/{id}', [AttendanceController::class, 'update'])->name('attendances.update');
    
    // Calendar
    Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::post('calendar', [CalendarController::class, 'store'])->name('calendar.store');
    Route::post('calendar/seed', [CalendarController::class, 'seed'])->name('calendar.seed');
    Route::put('calendar/{id}', [CalendarController::class, 'update'])->name('calendar.update');
    
    // Login History
    Route::get('login-history', [LoginHistoryController::class, 'index'])->name('login-history.index');
    
    // Profile
    Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    
    // Petugas MBG
    Route::resource('mbg-officers', MbgOfficerController::class);
});
