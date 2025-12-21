# 🎓 Prescientia Admin - School Attendance Management System

> Sistem manajemen absensi sekolah berbasis Laravel dengan WiFi detection dan real-time tracking.

## 📋 Tentang Project

Prescientia Admin adalah sistem komprehensif untuk mengelola absensi siswa dan guru menggunakan teknologi WiFi detection, dilengkapi dengan:

- 📱 Auto check-in via WiFi detection
- 👥 Manajemen siswa, guru, dan admin
- 📊 Dashboard & reporting lengkap
- 🔔 Notifikasi real-time
- 📈 Analisis kehadiran
- 🏫 Manajemen kelas dan jurusan

## ✨ Features

### Core Features
- ✅ **User Management**: Admin, Guru, Siswa
- ✅ **Class Management**: Kelas, jurusan, wali kelas
- ✅ **Attendance System**: Check-in/out otomatis & manual
- ✅ **WiFi Detection**: Auto-attendance dari WiFi sekolah
- ✅ **School Calendar**: Hari aktif/libur sekolah
- ✅ **Reporting**: Laporan harian, bulanan, semester

### Technical Features
- ✅ Laravel Best Practices implemented
- ✅ Eloquent ORM dengan relationships lengkap
- ✅ Service Layer architecture
- ✅ Observer pattern untuk auto-update
- ✅ Soft deletes untuk data penting
- ✅ Database indexes untuk performance
- ✅ Query scopes untuk reusability
- ✅ Type hints & documentation

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- MySQL 5.7+ / MariaDB 10.3+
- Composer
- Node.js & NPM (optional)

### Installation

```bash
# 1. Clone repository
git clone <repository-url>
cd prescientia-admin

# 2. Install dependencies
composer install

# 3. Environment setup
cp .env.example .env
php artisan key:generate

# 4. Configure database (.env)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=prescientia_db
DB_USERNAME=root
DB_PASSWORD=

# 5. Run migrations & seeders
php artisan migrate
php artisan db:seed --class=SchoolCalendarSeeder
php artisan db:seed --class=WifiNetworkSeeder
php artisan db:seed --class=AdminSeeder

# 6. Start development server
php artisan serve
```

### Default Login
```
Email: admin@sekolah.com
Password: password
```

## 📚 Documentation

- **[Quick Start Guide](QUICKSTART.md)** - Panduan cepat 5 menit
- **[Database Setup](DATABASE_SETUP.md)** - Setup database lengkap
- **[Best Practices](BEST_PRACTICES.md)** - Best practices yang diimplementasi
- **[Project Summary](PROJECT_SUMMARY.md)** - Overview lengkap project

## 🏗️ Project Structure

```
prescientia-admin/
├── app/
│   ├── Helpers/           # Helper functions
│   ├── Models/            # 15 Eloquent models
│   ├── Observers/         # Model observers
│   ├── Services/          # Business logic layer
│   └── Providers/         # Service providers
├── config/
│   └── prescientia.php    # Custom configuration
├── database/
│   ├── migrations/        # 16 migration files
│   └── seeders/           # Database seeders
└── docs/                  # Documentation
```

## 🗄️ Database Schema

### Core Tables
- **users** - Akun utama (admin, guru, siswa)
- **students** - Profil siswa
- **teachers** - Profil guru
- **admins** - Profil admin
- **classes** - Kelas & jurusan

### Attendance System
- **school_calendar** - Kalender sekolah
- **student_attendances** - Absensi siswa
- **teacher_attendances** - Absensi guru
- **student_attendance_details** - Detail ketidakhadiran
- **student_attendance_summary** - Rekapitulasi

### WiFi & Tracking
- **wifi_networks** - Daftar WiFi sekolah
- **wifi_presence_logs** - Log WiFi real-time
- **history_login** - Riwayat login

## 💡 Usage Examples

### Record Attendance
```php
use App\Services\AttendanceService;

$service = new AttendanceService();

// Manual check-in
$attendance = $service->recordStudentAttendance(
    studentId: 1,
    classId: 1,
    date: today()->format('Y-m-d'),
    status: 'hadir',
    source: 'digital_wifi'
);

// Auto check-in from WiFi
$attendance = $service->autoCheckInFromWifi(
    userId: 1,
    wifiId: 1
);
```

### Get Statistics
```php
use App\Helpers\AttendanceHelper;

// Today's class statistics
$stats = AttendanceHelper::getClassStatistics(1, today());

// Monthly summary
$summary = AttendanceHelper::getMonthlyAttendanceSummary(
    classId: 1,
    year: 2025,
    month: 12
);

// Student attendance percentage
$percentages = AttendanceHelper::getAttendancePercentageByStatus(1);
```

### Query with Relationships
```php
// Students with attendance summary
$students = Student::with(['class', 'attendanceSummary'])
    ->where('class_id', 1)
    ->get();

// Today's present students
$present = StudentAttendance::present()
    ->whereHas('calendar', fn($q) => $q->where('date', today()))
    ->get();
```

## 🛠️ Development

### Run Tests
```bash
php artisan test
```

### Code Style
```bash
./vendor/bin/pint
```

### Database Reset
```bash
php artisan migrate:fresh --seed
```

## 📦 Tech Stack

- **Framework**: Laravel 11.x
- **Database**: MySQL/MariaDB
- **PHP**: 8.2+
- **Authentication**: Laravel Sanctum (ready)
- **Architecture**: Service Layer Pattern
- **ORM**: Eloquent

## 🎯 Best Practices Implemented

✅ **Laravel Conventions**
- Standard naming (password, not password_hash)
- email_verified_at, remember_token
- timestamps() & softDeletes()

✅ **Database Optimization**
- Indexes on foreign keys & frequently queried columns
- Unique constraints to prevent duplicates
- Proper foreign key cascading

✅ **Code Quality**
- Type hints on all methods
- Comprehensive PHPDoc comments
- PSR-12 coding standards
- Separation of concerns (Service Layer)

✅ **Security**
- Auto password hashing
- Mass assignment protection
- Soft deletes for important data
- Hidden sensitive attributes

✅ **Performance**
- Eager loading support
- Query scopes for reusability
- Database indexes
- Computed attributes (accessors)

## 🔄 Roadmap

### Phase 1: Core System ✅
- [x] Database schema & migrations
- [x] Eloquent models with relationships
- [x] Service layer for business logic
- [x] Observers for auto-updates
- [x] Helper functions
- [x] Seeders & documentation

### Phase 2: API (In Progress)
- [ ] REST API endpoints
- [ ] API authentication (Sanctum)
- [ ] API resources & collections
- [ ] Form request validation

### Phase 3: Advanced Features
- [ ] Real-time notifications
- [ ] Export to Excel/PDF
- [ ] Email notifications
- [ ] Dashboard with charts
- [ ] Mobile app support

### Phase 4: Testing & Deployment
- [ ] Unit tests
- [ ] Feature tests
- [ ] API tests
- [ ] Docker setup
- [ ] CI/CD pipeline

## 🤝 Contributing

Contributions are welcome! Please read the contributing guidelines before submitting PRs.

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 👥 Team

- **Project Lead**: Prescientia Team
- **Backend**: Laravel Expert
- **Database**: MySQL DBA
- **Documentation**: Technical Writer

## 📞 Support

For issues, questions, or contributions:
- Create an issue on GitHub
- Contact: admin@prescientia.com
- Documentation: See `/docs` folder

## 🙏 Acknowledgments

- Laravel Framework
- Laravel Community
- All Contributors

---

<p align="center">
  <strong>Built with ❤️ using Laravel</strong>
</p>

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
