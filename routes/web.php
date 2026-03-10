<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
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
use App\Http\Controllers\EventController;
use App\Http\Controllers\PeriodAttendanceController;
use App\Http\Controllers\AbsenceLetterController;

// Login
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'Login'])->name('login.post')->middleware('throttle:5,1');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

<<<<<<< HEAD
// Admin Routes
Route::prefix('admin')->middleware('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/classes/{grade}', [DashboardController::class, 'getClassesByGrade'])->name('dashboard.classes');
    Route::get('/dashboard/teachers', [DashboardController::class, 'getTeacherStats'])->name('dashboard.teachers');
    Route::get('/dashboard/class/{classId}/students', [DashboardController::class, 'getClassStudents'])->name('dashboard.class.students');
    
    // Students
    // Bulk import template and upload - register before resource to avoid route parameter collision
    Route::get('students/import', [StudentController::class, 'importForm'])->name('students.import.form');
    Route::get('students/download-template', [StudentController::class, 'downloadTemplate'])->name('students.download-template');
    Route::post('students/import', [StudentController::class, 'import'])->name('students.import');
    Route::post('students/import/create-missing-and-import', [StudentController::class, 'createMissingAndImport'])->name('students.import.create-missing-and-import');
    Route::post('students/import/confirm-dependencies', [StudentController::class, 'confirmAndCreateDependencies'])->name('students.import.confirm-dependencies');
    Route::post('students/import/process', [StudentController::class, 'importProcess'])->name('students.import.process');
    Route::delete('students/delete-graduates', [StudentController::class, 'destroyGraduates'])->name('students.delete-graduates');
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
    
    // Attendances (separate pages for students and teachers)
    Route::get('attendances/students', [AttendanceController::class, 'students'])->name('attendances.students');
    Route::get('attendances/students/export', [AttendanceController::class, 'exportStudents'])->name('attendances.students.export');
    Route::get('attendances/students/search-unattended', [AttendanceController::class, 'searchUnattendedStudents'])->name('attendances.students.search-unattended');
    Route::post('attendances/students/store', [AttendanceController::class, 'storeStudentAttendance'])->name('attendances.students.store');
    Route::delete('attendances/students/delete-range', [AttendanceController::class, 'deleteStudentAttendanceRange'])->name('attendances.students.delete-range');
    Route::get('attendances/teachers', [AttendanceController::class, 'teachers'])->name('attendances.teachers');
    Route::get('attendances/teachers/export', [AttendanceController::class, 'exportTeachers'])->name('attendances.teachers.export');
    Route::get('attendances/teachers/search-unattended', [AttendanceController::class, 'searchUnattendedTeachers'])->name('attendances.teachers.search-unattended');
    Route::post('attendances/teachers/store', [AttendanceController::class, 'storeTeacherAttendance'])->name('attendances.teachers.store');
    Route::delete('attendances/teachers/delete-range', [AttendanceController::class, 'deleteTeacherAttendanceRange'])->name('attendances.teachers.delete-range');
    Route::post('attendances/record', [AttendanceController::class, 'record'])->name('attendances.record');
    Route::get('attendances/{role}/{id}', [AttendanceController::class, 'show'])->name('attendances.show');
    Route::put('attendances/{role}/{id}', [AttendanceController::class, 'update'])->name('attendances.update');
    
    // Class Periods (Jam Pelajaran) - CRUD Management
    Route::resource('class-periods', \App\Http\Controllers\Admin\AdminClassPeriodController::class);
    
    // Class Periods - Import & Export Routes
    Route::get('class-periods-download-template', [ClassPeriodController::class, 'downloadTemplate'])->name('class-periods.download-template');
    Route::post('class-periods-import', [ClassPeriodController::class, 'import'])->name('class-periods.import');
    Route::post('class-periods-seed-data', [ClassPeriodController::class, 'seedData'])->name('class-periods.seed-data');
    Route::patch('class-periods-update-note', [ClassPeriodController::class, 'updateNote'])->name('class-periods.update-note');
    Route::delete('class-periods-delete-all', [ClassPeriodController::class, 'deleteAll'])->name('class-periods.delete-all');
    Route::get('class-periods-check-status', [ClassPeriodController::class, 'checkStatus'])->name('class-periods.check-status');
    
    // Teacher Schedules (Bulk Assignment & Import)
    Route::get('teacher-schedules', [\App\Http\Controllers\Admin\AdminTeacherScheduleController::class, 'index'])->name('teacher-schedules.index');
    Route::get('teacher-schedules/periods', [\App\Http\Controllers\Admin\AdminTeacherScheduleController::class, 'getPeriodsByDay'])->name('teacher-schedules.periods');
    Route::post('teacher-schedules/bulk', [\App\Http\Controllers\Admin\AdminTeacherScheduleController::class, 'storeBulk'])->name('teacher-schedules.bulk');
    Route::post('teacher-schedules/import', [\App\Http\Controllers\Admin\AdminTeacherScheduleController::class, 'importExcel'])->name('teacher-schedules.import');
    Route::get('teacher-schedules/download-template', [\App\Http\Controllers\Admin\AdminTeacherScheduleController::class, 'downloadTemplate'])->name('teacher-schedules.download-template');
    Route::delete('teacher-schedules/{schedule}', [\App\Http\Controllers\Admin\AdminTeacherScheduleController::class, 'destroy'])->name('teacher-schedules.destroy');
    
    // Attendance Recap Per Day
    Route::get('attendance/recap', [\App\Http\Controllers\Admin\AdminTeacherAttendanceRecapController::class, 'index'])->name('attendance.recap');
    
    // Calendar
    Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::post('calendar', [CalendarController::class, 'store'])->name('calendar.store');
    Route::get('calendar/check-exists', [CalendarController::class, 'checkCalendarExists'])->name('calendar.check-exists');
    Route::post('calendar/seed', [CalendarController::class, 'seed'])->name('calendar.seed');
    Route::get('calendar/get-date-status', [CalendarController::class, 'getDateStatus'])->name('calendar.get-date-status');
    Route::post('calendar/{id}/toggle-status', [CalendarController::class, 'toggleDateStatus'])->name('calendar.toggle-status');
    Route::put('calendar/{id}', [CalendarController::class, 'update'])->name('calendar.update');
    
    // Login History
    Route::get('login-history', [LoginHistoryController::class, 'index'])->name('login-history.index');
    
    // Profile
    Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
=======
// Protected routes
Route::middleware('auth:admin')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('Dashboard');

    /* ── Data Kelas ──────────────────────────────────── */
    Route::resource('/kelas', KelasController::class)->only(['index', 'store', 'update', 'destroy']);
    /* ── Data Guru ───────────────────────────────────────── */
    Route::get('/guru/template', [TeacherController::class, 'downloadTemplate'])->name('guru.template');
    Route::post('/guru/import', [TeacherController::class, 'importExcel'])->name('guru.import');
    Route::post('/guru/check-subjects', [TeacherController::class, 'checkSubjects'])->name('guru.checksubjects');
    Route::resource('/guru', TeacherController::class)->except(['create']);
    /* ── Data Siswa ──────────────────────────────────── */
    Route::get('/siswa/template', [StudentController::class, 'downloadTemplate'])->name('siswa.template');
    Route::post('/siswa/import', [StudentController::class, 'importExcel'])->name('siswa.import');
    Route::post('/siswa/check-classes', [StudentController::class, 'checkClasses'])->name('siswa.checkclasses');
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
        Route::get('period',         [PeriodAttendanceController::class, 'studentPeriodIndex'])->name('period');
        Route::post('period/store',  [PeriodAttendanceController::class, 'storeStudentPeriod'])->name('period.store');
        Route::post('period/auto-fill', [PeriodAttendanceController::class, 'autoFillStudentPeriods'])->name('period.autofill');
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
        Route::get('/',              [ClassPeriodController::class, 'index'])->name('index');
        Route::post('/',             [ClassPeriodController::class, 'store'])->name('store');
        Route::post('/reset',        [ClassPeriodController::class, 'reset'])->name('reset');
        Route::get('/template',      [ClassPeriodController::class, 'downloadTemplate'])->name('template');
        Route::post('/import',       [ClassPeriodController::class, 'importExcel'])->name('import');
        Route::get('/{day}/table',   [ClassPeriodController::class, 'dayTable'])->name('dayTable');
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
        Route::get('period',         [PeriodAttendanceController::class, 'teacherPeriodIndex'])->name('period');
        Route::post('period/store',  [PeriodAttendanceController::class, 'storeTeacherPeriod'])->name('period.store');
        Route::post('period/auto-fill', [PeriodAttendanceController::class, 'autoFillTeacherPeriods'])->name('period.autofill');
    });

    /* ── Event / Acara ───────────────────────────────── */
    Route::resource('/events', EventController::class);

    /* ── Surat Izin / Sakit ───────────────────────────── */
    Route::prefix('absence-letters')->name('absence-letters.')->group(function () {
        Route::get('/',              [AbsenceLetterController::class, 'index'])->name('index');
        Route::patch('{id}/approve', [AbsenceLetterController::class, 'approve'])->name('approve');
        Route::patch('{id}/reject',  [AbsenceLetterController::class, 'reject'])->name('reject');
    });
>>>>>>> mysql_database

});
