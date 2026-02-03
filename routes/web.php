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
use App\Http\Controllers\ClassPeriodController;
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
    // Bulk import template and upload - register before resource to avoid route parameter collision
    Route::get('students/import', [StudentController::class, 'importForm'])->name('students.import.form');
    Route::get('students/download-template', [StudentController::class, 'downloadTemplate'])->name('students.download-template');
    Route::post('students/import', [StudentController::class, 'import'])->name('students.import');
    Route::post('students/import/create-missing-and-import', [StudentController::class, 'createMissingAndImport'])->name('students.import.create-missing-and-import');
    Route::post('students/import/confirm-dependencies', [StudentController::class, 'confirmAndCreateDependencies'])->name('students.import.confirm-dependencies');
    Route::post('students/import/process', [StudentController::class, 'importProcess'])->name('students.import.process');
    Route::resource('students', StudentController::class);
    
    // Teachers
    // Bulk import template and upload - register before resource to avoid route parameter collision
    Route::get('teachers/import', [\App\Http\Controllers\Admin\TeacherController::class, 'importForm'])->name('teachers.import.form');
    Route::get('teachers/download-template', [\App\Http\Controllers\Admin\TeacherController::class, 'downloadTemplate'])->name('teachers.download-template');
    Route::post('teachers/import', [\App\Http\Controllers\Admin\TeacherController::class, 'import'])->name('teachers.import');
    Route::post('teachers/import/confirm-dependencies', [\App\Http\Controllers\Admin\TeacherController::class, 'confirmAndCreateDependencies'])->name('teachers.import.confirm-dependencies');
    Route::resource('teachers', TeacherController::class);
    
    // Classes
    Route::resource('classes', ClassController::class);
    
    // Subjects (Mata Pelajaran)
    Route::resource('subjects', \App\Http\Controllers\Admin\SubjectsController::class);
    
    // Teached Classes (Guru Mengajar)
    Route::get('teached-classes', [TeachedClassController::class, 'index'])->name('teached-classes.index');
    Route::get('teached-classes/download-assignment-template', [TeachedClassController::class, 'downloadAssignmentTemplate'])->name('teached-classes.download-assignment-template');
    Route::post('teached-classes/import-assignments', [TeachedClassController::class, 'importAssignments'])->name('teached-classes.import-assignments');
    Route::get('teached-classes/suggestions', [TeachedClassController::class, 'suggestions'])->name('teached-classes.suggestions');
    Route::get('teached-classes/{class}/subjects', [TeachedClassController::class, 'getTeacherSubjects'])->name('teached-classes.get-subjects');
    Route::get('teached-classes/{class}/edit', [TeachedClassController::class, 'edit'])->name('teached-classes.edit');
    
    // NEW: Subject-first assignment routes
    Route::post('teached-classes/{class}/subjects/{subject}/assign', [TeachedClassController::class, 'assignTeacherToSubject'])->name('teached-classes.assign-subject');
    Route::put('teached-classes/{teachedClass}/update-teacher', [TeachedClassController::class, 'updateTeacherForSubject'])->name('teached-classes.update-teacher');
    Route::delete('teached-classes/{teachedClass}/remove', [TeachedClassController::class, 'removeTeacherFromSubject'])->name('teached-classes.remove-subject');
    
    // DEPRECATED: Old multi-subject assignment routes (kept for backwards compatibility)
    Route::post('teached-classes/{class}', [TeachedClassController::class, 'store'])->name('teached-classes.store');
    Route::put('teached-classes/{teachedClass}', [TeachedClassController::class, 'update'])->name('teached-classes.update');
    Route::delete('teached-classes/{teachedClass}', [TeachedClassController::class, 'destroy'])->name('teached-classes.destroy');
    
    // WiFi Networks
    Route::resource('wifi', WifiController::class)->except(['show']);
    Route::post('wifi/convert', [WifiController::class, 'convert'])->name('wifi.convert');
    
    // Attendances (separate pages for students and teachers)
    Route::get('attendances/students', [AttendanceController::class, 'students'])->name('attendances.students');
    Route::get('attendances/students/export', [AttendanceController::class, 'exportStudents'])->name('attendances.students.export');
    Route::get('attendances/students/search-unattended', [AttendanceController::class, 'searchUnattendedStudents'])->name('attendances.students.search-unattended');
    Route::post('attendances/students/store', [AttendanceController::class, 'storeStudentAttendance'])->name('attendances.students.store');
    Route::get('attendances/teachers', [AttendanceController::class, 'teachers'])->name('attendances.teachers');
    Route::get('attendances/teachers/export', [AttendanceController::class, 'exportTeachers'])->name('attendances.teachers.export');
    Route::get('attendances/teachers/search-unattended', [AttendanceController::class, 'searchUnattendedTeachers'])->name('attendances.teachers.search-unattended');
    Route::post('attendances/teachers/store', [AttendanceController::class, 'storeTeacherAttendance'])->name('attendances.teachers.store');
    Route::post('attendances/record', [AttendanceController::class, 'record'])->name('attendances.record');
    Route::get('attendances/{role}/{id}', [AttendanceController::class, 'show'])->name('attendances.show');
    Route::put('attendances/{role}/{id}', [AttendanceController::class, 'update'])->name('attendances.update');
    
    // Class Periods (Jam Pelajaran)
    Route::get('class-periods', [ClassPeriodController::class, 'index'])->name('class-periods.index');
    Route::get('class-periods/download-template', [ClassPeriodController::class, 'downloadTemplate'])->name('class-periods.download-template');
    Route::post('class-periods/import', [ClassPeriodController::class, 'import'])->name('class-periods.import');
    Route::patch('class-periods/update-note', [ClassPeriodController::class, 'updateNote'])->name('class-periods.update-note');
    Route::delete('class-periods/delete-all', [ClassPeriodController::class, 'deleteAll'])->name('class-periods.delete-all');
    Route::get('class-periods/check-status', [ClassPeriodController::class, 'checkStatus'])->name('class-periods.check-status');
    Route::get('class-periods/{classPeriod}', [ClassPeriodController::class, 'show'])->name('class-periods.show');
    
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
