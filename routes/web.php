<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\Logincontroller;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\StudentAttendanceController;
use App\Http\Controllers\DeviceChangeRequestController;
use App\Http\Controllers\WifiNetworkController;
use App\Http\Controllers\SchoolCalendarController;
use App\Http\Controllers\TeacherAttendanceController;
use App\Http\Controllers\ClassPeriodController;

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
    Route::get('/siswa/check-role', [StudentController::class, 'checkRole'])->name('siswa.checkRole');
    Route::resource('/siswa', StudentController::class)->except(['create']);

    /* ── Jaringan WiFi ───────────────────────────────── */
    Route::resource('/wifi-networks', WifiNetworkController::class)->only(['index', 'store', 'update', 'destroy']);

    /* ── Kalender Sekolah ────────────────────────────── */
    Route::prefix('school-calendar')->name('school-calendar.')->group(function () {
        Route::get('/',          [SchoolCalendarController::class, 'index'])->name('index');
        Route::post('/generate', [SchoolCalendarController::class, 'generate'])->name('generate');
        Route::put('/{id}',      [SchoolCalendarController::class, 'update'])->name('update');
        Route::delete('/year',   [SchoolCalendarController::class, 'destroyYear'])->name('destroy-year');
        Route::delete('/{id}',   [SchoolCalendarController::class, 'destroy'])->name('destroy');
    });

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

    /* ── Jadwal Mengajar ─────────────────────────────── */
    Route::prefix('jadwal-mengajar')->name('jadwal-mengajar.')->group(function () {
        Route::get('/',                  [\App\Http\Controllers\TeacherScheduleController::class, 'index'])->name('index');
        Route::get('/schedule',          [\App\Http\Controllers\TeacherScheduleController::class, 'fetchSchedule'])->name('schedule');
        Route::get('/available-periods', [\App\Http\Controllers\TeacherScheduleController::class, 'availablePeriods'])->name('available-periods');
        Route::post('/',                 [\App\Http\Controllers\TeacherScheduleController::class, 'store'])->name('store');
        Route::match(['put','patch'], '/{jadwalMengajar}', [\App\Http\Controllers\TeacherScheduleController::class, 'update'])->name('update');
        Route::delete('/{jadwalMengajar}', [\App\Http\Controllers\TeacherScheduleController::class, 'destroy'])->name('destroy');
        Route::post('/import',   [\App\Http\Controllers\TeacherScheduleController::class, 'importExcel'])->name('import');
        Route::get('/template',  [\App\Http\Controllers\TeacherScheduleController::class, 'downloadTemplate'])->name('template');
    });

    /* ── Mata Pelajaran ──────────────────────────────── */
    Route::prefix('mapel')->name('mapel.')->group(function () {
        Route::get('/',                           [\App\Http\Controllers\SubjectController::class, 'index'])->name('index');
        Route::post('/',                          [\App\Http\Controllers\SubjectController::class, 'store'])->name('store');
        Route::match(['put','patch'], '/{subject}', [\App\Http\Controllers\SubjectController::class, 'update'])->name('update');
        Route::delete('/{subject}',               [\App\Http\Controllers\SubjectController::class, 'destroy'])->name('destroy');
        Route::get('/class-options/{subject}',    [\App\Http\Controllers\SubjectController::class, 'classOptions'])->name('classOptions');
    });

    /* ── Jam Pelajaran ───────────────────────────────── */
    Route::prefix('jam-pelajaran')->name('jam-pelajaran.')->group(function () {
        Route::get('/',            [ClassPeriodController::class, 'index'])->name('index');
        Route::post('/',           [ClassPeriodController::class, 'store'])->name('store');
        Route::post('/reset',      [ClassPeriodController::class, 'reset'])->name('reset');
        Route::get('/{day}/table', [ClassPeriodController::class, 'dayTable'])->name('dayTable');
        Route::put('/{jamPelajaran}',    [ClassPeriodController::class, 'update'])->name('update');
        Route::delete('/{jamPelajaran}', [ClassPeriodController::class, 'destroy'])->name('destroy');
        Route::patch('/{jamPelajaran}',  [ClassPeriodController::class, 'update'])->name('patch');
    });

    /* ── Kehadiran Guru ──────────────────────────────── */
    Route::prefix('attendance/guru')->name('attendance.teacher.')->group(function () {
        Route::get('/',        [TeacherAttendanceController::class, 'index'])->name('index');
        Route::post('/',       [TeacherAttendanceController::class, 'store'])->name('store');
        Route::patch('{id}',   [TeacherAttendanceController::class, 'update'])->name('update');
        Route::delete('{id}',  [TeacherAttendanceController::class, 'destroy'])->name('destroy');
        Route::get('export',   [TeacherAttendanceController::class, 'export'])->name('export');
        Route::get('search',   [TeacherAttendanceController::class, 'searchTeachers'])->name('search');
    });

});
