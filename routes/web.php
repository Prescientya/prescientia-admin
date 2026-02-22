<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\Logincontroller;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\StudentAttendanceController;
use App\Http\Controllers\DeviceChangeRequestController;

// Login
Route::get('/', [Logincontroller::class, 'showLoginForm'])->name('login');
Route::post('/login', [Logincontroller::class, 'Login'])->name('login.post');
Route::post('/logout', [Logincontroller::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('auth:admin')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('Dashboard');

    /* ── Data Kelas ──────────────────────────────────── */
    Route::resource('/kelas', KelasController::class)->only(['index', 'store', 'update', 'destroy']);
    /* ── Data Guru ───────────────────────────────────────── */
    Route::get('/guru/template', [TeacherController::class, 'downloadTemplate'])->name('guru.template');
    Route::post('/guru/import', [TeacherController::class, 'importExcel'])->name('guru.import');
    Route::resource('/guru', TeacherController::class)->except(['create']);
    /* ── Data Siswa ──────────────────────────────────── */
    Route::get('/siswa/template', [StudentController::class, 'downloadTemplate'])->name('siswa.template');
    Route::post('/siswa/import', [StudentController::class, 'importExcel'])->name('siswa.import');
    Route::resource('/siswa', StudentController::class)->except(['create']);

    /* ── Device Change Requests ──────────────────────── */
    Route::prefix('device-requests')->name('device-requests.')->group(function () {
        Route::get('/',              [DeviceChangeRequestController::class, 'index'])->name('index');
        Route::patch('{id}/approve', [DeviceChangeRequestController::class, 'approve'])->name('approve');
        Route::patch('{id}/reject',  [DeviceChangeRequestController::class, 'reject'])->name('reject');
    });

    /* ── Kehadiran Siswa ─────────────────────────────── */
    Route::prefix('attendance/siswa')->name('attendance.student.')->group(function () {
        Route::get('/',             [StudentAttendanceController::class, 'index'])->name('index');
        Route::post('/',            [StudentAttendanceController::class, 'store'])->name('store');
        Route::patch('{id}',        [StudentAttendanceController::class, 'update'])->name('update');
        Route::delete('{id}',       [StudentAttendanceController::class, 'destroy'])->name('destroy');
        Route::patch('{id}/status', [StudentAttendanceController::class, 'updateStatus'])->name('updateStatus');
        Route::patch('{id}/time',   [StudentAttendanceController::class, 'updateTime'])->name('updateTime');
        Route::get('export',        [StudentAttendanceController::class, 'export'])->name('export');
        Route::get('search',        [StudentAttendanceController::class, 'searchStudents'])->name('search');
    });

});
