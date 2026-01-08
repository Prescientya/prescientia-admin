# DATABASE SCHEMA - PRESCIENTIA ADMIN SYSTEM
**Database Type:** PostgreSQL  
**Generated:** December 27, 2025

---

## TABLE OF CONTENTS
1. [Core Tables](#core-tables)
2. [User Management](#user-management)
3. [Attendance System](#attendance-system)
4. [MBG System](#mbg-system)
5. [Relations Diagram](#relations-diagram)
6. [API Endpoints Suggestions](#api-endpoints-suggestions)

---

## CORE TABLES

### 1. users
**Purpose:** Main authentication table for all system users  
**Primary Key:** id (BIGSERIAL)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Unique identifier |
| email | VARCHAR(100) | UNIQUE, NOT NULL | User email for login |
| email_verified_at | TIMESTAMP | NULL | Email verification timestamp |
| password | VARCHAR(255) | NOT NULL | Hashed password |
| device_id | VARCHAR(255) | NULL | Mobile device identifier |
| wifi_mac | VARCHAR(50) | NULL | WiFi MAC address |
| is_active | BOOLEAN | DEFAULT true | Account active status |
| last_login_at | TIMESTAMP | NULL | Last login timestamp |
| remember_token | VARCHAR(100) | NULL | Remember me token |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Record update time |
| deleted_at | TIMESTAMP | NULL | Soft delete timestamp |

**Indexes:**
- `users_email_unique` (email)
- `users_device_id_index` (device_id)
- `users_wifi_mac_index` (wifi_mac)
- `users_is_active_index` (is_active)

**SQL DDL:**
```sql
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    device_id VARCHAR(255) NULL,
    wifi_mac VARCHAR(50) NULL,
    is_active BOOLEAN DEFAULT true,
    last_login_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

CREATE INDEX users_device_id_index ON users(device_id);
CREATE INDEX users_wifi_mac_index ON users(wifi_mac);
CREATE INDEX users_is_active_index ON users(is_active);
```

---

### 2. admins
**Purpose:** Admin profile details  
**Primary Key:** id (BIGSERIAL)  
**Foreign Keys:** user_id → users(id)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Unique identifier |
| user_id | BIGINT | UNIQUE, FK→users(id) CASCADE | Reference to user account |
| name | VARCHAR(100) | NOT NULL | Admin full name |
| nip | VARCHAR(20) | NULL | Employee ID number |
| phone_number | VARCHAR(20) | NULL | Contact phone |
| photo_profile | VARCHAR(255) | NULL | Profile photo path |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Record update time |
| deleted_at | TIMESTAMP | NULL | Soft delete timestamp |

**SQL DDL:**
```sql
CREATE TABLE admins (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT UNIQUE NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    nip VARCHAR(20) NULL,
    phone_number VARCHAR(20) NULL,
    photo_profile VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

---

### 3. teachers
**Purpose:** Teacher profile details  
**Primary Key:** id (BIGSERIAL)  
**Foreign Keys:** user_id → users(id)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Unique identifier |
| user_id | BIGINT | UNIQUE, FK→users(id) CASCADE | Reference to user account |
| nip | VARCHAR(20) | UNIQUE, NOT NULL | Teacher ID number |
| name | VARCHAR(100) | NOT NULL | Teacher full name |
| gender | ENUM('L','P') | NOT NULL | Gender (L=Male, P=Female) |
| date_of_birth | DATE | NOT NULL | Birth date |
| phone_number | VARCHAR(20) | NULL | Contact phone |
| address | TEXT | NULL | Home address |
| department | VARCHAR(100) | NULL | Subject/department |
| photo_profile | VARCHAR(255) | NULL | Profile photo path |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Record update time |
| deleted_at | TIMESTAMP | NULL | Soft delete timestamp |

**Indexes:**
- `teachers_name_index` (name)
- `teachers_department_index` (department)

**SQL DDL:**
```sql
CREATE TYPE gender_enum AS ENUM ('L', 'P');

CREATE TABLE teachers (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT UNIQUE NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    nip VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    gender gender_enum NOT NULL,
    date_of_birth DATE NOT NULL,
    phone_number VARCHAR(20) NULL,
    address TEXT NULL,
    department VARCHAR(100) NULL,
    photo_profile VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

CREATE INDEX teachers_name_index ON teachers(name);
CREATE INDEX teachers_department_index ON teachers(department);
```

---

### 4. classes
**Purpose:** Class/classroom data  
**Primary Key:** id (BIGSERIAL)  
**Foreign Keys:** homeroom_teacher_id → teachers(id)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Unique identifier |
| class | INTEGER | NOT NULL | Class number (e.g., 10, 11, 12) |
| major | VARCHAR(100) | NULL | Major/program (e.g., Kuliner 1) |
| homeroom_teacher_id | BIGINT | NULL, FK→teachers(id) SET NULL | Homeroom teacher |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Record update time |

**Indexes:**
- `classes_class_index` (class)
- `classes_major_index` (major)

**SQL DDL:**
```sql
CREATE TABLE classes (
    id BIGSERIAL PRIMARY KEY,
    class INTEGER NOT NULL,
    major VARCHAR(100) NULL,
    homeroom_teacher_id BIGINT NULL REFERENCES teachers(id) ON DELETE SET NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX classes_class_index ON classes(class);
CREATE INDEX classes_major_index ON classes(major);
```

---

### 5. students
**Purpose:** Student profile details  
**Primary Key:** id (BIGSERIAL)  
**Foreign Keys:** user_id → users(id), class_id → classes(id)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Unique identifier |
| user_id | BIGINT | UNIQUE, FK→users(id) CASCADE | Reference to user account |
| nis | VARCHAR(20) | UNIQUE, NOT NULL | Student ID number |
| name | VARCHAR(100) | NOT NULL | Student full name |
| gender | ENUM('L','P') | NOT NULL | Gender (L=Male, P=Female) |
| date_of_birth | DATE | NOT NULL | Birth date |
| phone_number | VARCHAR(20) | NULL | Contact phone |
| address | TEXT | NULL | Home address |
| class_id | BIGINT | NULL, FK→classes(id) SET NULL | Assigned class |
| photo_profile | VARCHAR(255) | NULL | Profile photo path |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Record update time |
| deleted_at | TIMESTAMP | NULL | Soft delete timestamp |

**Indexes:**
- `students_name_index` (name)
- `students_class_id_index` (class_id)

**SQL DDL:**
```sql
CREATE TABLE students (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT UNIQUE NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    nis VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    gender gender_enum NOT NULL,
    date_of_birth DATE NOT NULL,
    phone_number VARCHAR(20) NULL,
    address TEXT NULL,
    class_id BIGINT NULL REFERENCES classes(id) ON DELETE SET NULL,
    photo_profile VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

CREATE INDEX students_name_index ON students(name);
CREATE INDEX students_class_id_index ON students(class_id);
```

---

## USER MANAGEMENT

### 6. history_login
**Purpose:** Login/logout history tracking  
**Primary Key:** id (BIGSERIAL)  
**Foreign Keys:** user_id → users(id)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Unique identifier |
| user_id | BIGINT | FK→users(id) CASCADE | User who logged in |
| device_id | VARCHAR(255) | NULL | Device identifier |
| wifi_mac | VARCHAR(50) | NULL | WiFi MAC address |
| ip_address | VARCHAR(45) | NULL | IP address (IPv4/IPv6) |
| login_at | TIMESTAMP | NULL | Login timestamp |
| logout_at | TIMESTAMP | NULL | Logout timestamp |
| duration_minutes | INTEGER | NULL | Session duration in minutes |
| location | VARCHAR(255) | NULL | Login location |
| status | ENUM('success','failed') | NOT NULL | Login attempt status |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Record update time |

**Indexes:**
- `history_login_user_id_index` (user_id)
- `history_login_login_at_index` (login_at)
- `history_login_status_index` (status)

**SQL DDL:**
```sql
CREATE TYPE login_status_enum AS ENUM ('success', 'failed');

CREATE TABLE history_login (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    device_id VARCHAR(255) NULL,
    wifi_mac VARCHAR(50) NULL,
    ip_address VARCHAR(45) NULL,
    login_at TIMESTAMP NULL,
    logout_at TIMESTAMP NULL,
    duration_minutes INTEGER NULL,
    location VARCHAR(255) NULL,
    status login_status_enum NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX history_login_user_id_index ON history_login(user_id);
CREATE INDEX history_login_login_at_index ON history_login(login_at);
CREATE INDEX history_login_status_index ON history_login(status);
```

---

### 7. student_class_roles
**Purpose:** Student roles within class (class leader, vice, secretary)  
**Primary Key:** id (BIGSERIAL)  
**Foreign Keys:** class_id → classes(id), student_id → students(id)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Unique identifier |
| class_id | BIGINT | FK→classes(id) CASCADE | Class reference |
| student_id | BIGINT | FK→students(id) CASCADE | Student reference |
| role | ENUM('KM','WKM','Sekretaris') | NOT NULL | Role (KM=Leader, WKM=Vice, Sekretaris=Secretary) |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Record update time |

**Unique Constraint:** (class_id, student_id)

**Indexes:**
- `student_class_roles_role_index` (role)

**SQL DDL:**
```sql
CREATE TYPE student_role_enum AS ENUM ('KM', 'WKM', 'Sekretaris');

CREATE TABLE student_class_roles (
    id BIGSERIAL PRIMARY KEY,
    class_id BIGINT NOT NULL REFERENCES classes(id) ON DELETE CASCADE,
    student_id BIGINT NOT NULL REFERENCES students(id) ON DELETE CASCADE,
    role student_role_enum NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(class_id, student_id)
);

CREATE INDEX student_class_roles_role_index ON student_class_roles(role);
```

---

### 8. teacher_class_roles
**Purpose:** Teacher roles (teacher or homeroom teacher)  
**Primary Key:** id (BIGSERIAL)  
**Foreign Keys:** teacher_id → teachers(id), class_id → classes(id)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Unique identifier |
| teacher_id | BIGINT | FK→teachers(id) CASCADE | Teacher reference |
| class_id | BIGINT | NULL, FK→classes(id) CASCADE | Class reference (null if not assigned) |
| role | ENUM('pengajar','wali_kelas') | NOT NULL | Role (pengajar=Teacher, wali_kelas=Homeroom) |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Record update time |

**Indexes:**
- `teacher_class_roles_teacher_class_index` (teacher_id, class_id)
- `teacher_class_roles_role_index` (role)

**SQL DDL:**
```sql
CREATE TYPE teacher_role_enum AS ENUM ('pengajar', 'wali_kelas');

CREATE TABLE teacher_class_roles (
    id BIGSERIAL PRIMARY KEY,
    teacher_id BIGINT NOT NULL REFERENCES teachers(id) ON DELETE CASCADE,
    class_id BIGINT NULL REFERENCES classes(id) ON DELETE CASCADE,
    role teacher_role_enum NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX teacher_class_roles_teacher_class_index ON teacher_class_roles(teacher_id, class_id);
CREATE INDEX teacher_class_roles_role_index ON teacher_class_roles(role);
```

---

### 9. wifi_networks
**Purpose:** WiFi access point registry for presence detection  
**Primary Key:** id (BIGSERIAL)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Unique identifier |
| ssid | VARCHAR(100) | NOT NULL | Network name |
| bssid | VARCHAR(50) | UNIQUE, NOT NULL | MAC address (unique AP identifier) |
| ip_address | VARCHAR(45) | NULL | IP address of AP |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Record update time |

**Indexes:**
- `wifi_networks_ssid_index` (ssid)

**SQL DDL:**
```sql
CREATE TABLE wifi_networks (
    id BIGSERIAL PRIMARY KEY,
    ssid VARCHAR(100) NOT NULL,
    bssid VARCHAR(50) UNIQUE NOT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX wifi_networks_ssid_index ON wifi_networks(ssid);
```

---

### 10. wifi_presence_logs
**Purpose:** Log WiFi-based presence detection  
**Primary Key:** id (BIGSERIAL)  
**Foreign Keys:** user_id → users(id), wifi_id → wifi_networks(id)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Unique identifier |
| user_id | BIGINT | FK→users(id) CASCADE | User detected |
| wifi_id | BIGINT | FK→wifi_networks(id) CASCADE | WiFi network detected |
| detected_at | TIMESTAMP | NOT NULL | Detection timestamp |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Record update time |

**Indexes:**
- `wifi_presence_logs_user_detected_index` (user_id, detected_at)
- `wifi_presence_logs_wifi_id_index` (wifi_id)

**SQL DDL:**
```sql
CREATE TABLE wifi_presence_logs (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    wifi_id BIGINT NOT NULL REFERENCES wifi_networks(id) ON DELETE CASCADE,
    detected_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX wifi_presence_logs_user_detected_index ON wifi_presence_logs(user_id, detected_at);
CREATE INDEX wifi_presence_logs_wifi_id_index ON wifi_presence_logs(wifi_id);
```

---

### 11. school_calendar
**Purpose:** School calendar (active days & holidays)  
**Primary Key:** id (BIGSERIAL)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Unique identifier |
| date | DATE | UNIQUE, NOT NULL | Calendar date |
| year | INTEGER | NOT NULL | Year |
| month | INTEGER | NOT NULL | Month (1-12) |
| day | INTEGER | NOT NULL | Day of month |
| status | ENUM('aktif','libur') | DEFAULT 'aktif' | Day status (aktif=Active, libur=Holiday) |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Record update time |

**Indexes:**
- `school_calendar_year_month_index` (year, month)
- `school_calendar_status_index` (status)

**SQL DDL:**
```sql
CREATE TYPE calendar_status_enum AS ENUM ('aktif', 'libur');

CREATE TABLE school_calendar (
    id BIGSERIAL PRIMARY KEY,
    date DATE UNIQUE NOT NULL,
    year INTEGER NOT NULL,
    month INTEGER NOT NULL,
    day INTEGER NOT NULL,
    status calendar_status_enum DEFAULT 'aktif',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX school_calendar_year_month_index ON school_calendar(year, month);
CREATE INDEX school_calendar_status_index ON school_calendar(status);
```

---

## ATTENDANCE SYSTEM

### 12. student_attendances
**Purpose:** Daily student attendance records  
**Primary Key:** id (BIGSERIAL)  
**Foreign Keys:** student_id → students(id), class_id → classes(id), calendar_id → school_calendar(id)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Unique identifier |
| student_id | BIGINT | FK→students(id) CASCADE | Student reference |
| class_id | BIGINT | FK→classes(id) CASCADE | Class reference |
| calendar_id | BIGINT | FK→school_calendar(id) CASCADE | Date reference |
| check_in_time | TIMESTAMP | NULL | Check-in time |
| check_out_time | TIMESTAMP | NULL | Check-out time |
| status | ENUM('hadir','sakit','izin','alpa','terlambat') | NOT NULL | Attendance status |
| source | ENUM('digital_wifi','guru_pengajar','wali_kelas','self_report','manual') | NULL | Source of record |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Record update time |

**Unique Constraint:** (student_id, calendar_id)

**Indexes:**
- `student_attendances_class_calendar_index` (class_id, calendar_id)
- `student_attendances_status_index` (status)
- `student_attendances_check_in_time_index` (check_in_time)

**SQL DDL:**
```sql
CREATE TYPE student_attendance_status_enum AS ENUM ('hadir', 'sakit', 'izin', 'alpa', 'terlambat');
CREATE TYPE student_attendance_source_enum AS ENUM ('digital_wifi', 'guru_pengajar', 'wali_kelas', 'self_report', 'manual');

CREATE TABLE student_attendances (
    id BIGSERIAL PRIMARY KEY,
    student_id BIGINT NOT NULL REFERENCES students(id) ON DELETE CASCADE,
    class_id BIGINT NOT NULL REFERENCES classes(id) ON DELETE CASCADE,
    calendar_id BIGINT NOT NULL REFERENCES school_calendar(id) ON DELETE CASCADE,
    check_in_time TIMESTAMP NULL,
    check_out_time TIMESTAMP NULL,
    status student_attendance_status_enum NOT NULL,
    source student_attendance_source_enum NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(student_id, calendar_id)
);

CREATE INDEX student_attendances_class_calendar_index ON student_attendances(class_id, calendar_id);
CREATE INDEX student_attendances_status_index ON student_attendances(status);
CREATE INDEX student_attendances_check_in_time_index ON student_attendances(check_in_time);
```

---

### 13. student_attendance_details
**Purpose:** Additional details for non-present students  
**Primary Key:** id (BIGSERIAL)  
**Foreign Keys:** attendance_id → student_attendances(id)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Unique identifier |
| attendance_id | BIGINT | UNIQUE, FK→student_attendances(id) CASCADE | Attendance record |
| reason | ENUM('sakit','izin','alpa','terlambat') | NOT NULL | Absence reason |
| description | TEXT | NULL | Detailed description |
| evidence_url | VARCHAR(255) | NULL | Evidence file URL |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Record update time |

**SQL DDL:**
```sql
CREATE TYPE attendance_reason_enum AS ENUM ('sakit', 'izin', 'alpa', 'terlambat');

CREATE TABLE student_attendance_details (
    id BIGSERIAL PRIMARY KEY,
    attendance_id BIGINT UNIQUE NOT NULL REFERENCES student_attendances(id) ON DELETE CASCADE,
    reason attendance_reason_enum NOT NULL,
    description TEXT NULL,
    evidence_url VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

---

### 14. student_attendance_summary
**Purpose:** Aggregated attendance statistics per student  
**Primary Key:** id (BIGSERIAL)  
**Foreign Keys:** student_id → students(id)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Unique identifier |
| student_id | BIGINT | UNIQUE, FK→students(id) CASCADE | Student reference |
| total_hadir | INTEGER | DEFAULT 0 | Total present days |
| total_izin | INTEGER | DEFAULT 0 | Total excused absences |
| total_sakit | INTEGER | DEFAULT 0 | Total sick days |
| total_alpha | INTEGER | DEFAULT 0 | Total unexcused absences |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Record update time |

**SQL DDL:**
```sql
CREATE TABLE student_attendance_summary (
    id BIGSERIAL PRIMARY KEY,
    student_id BIGINT UNIQUE NOT NULL REFERENCES students(id) ON DELETE CASCADE,
    total_hadir INTEGER DEFAULT 0,
    total_izin INTEGER DEFAULT 0,
    total_sakit INTEGER DEFAULT 0,
    total_alpha INTEGER DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

---

### 15. teacher_attendances
**Purpose:** Daily teacher attendance records  
**Primary Key:** id (BIGSERIAL)  
**Foreign Keys:** teacher_id → teachers(id), calendar_id → school_calendar(id)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Unique identifier |
| teacher_id | BIGINT | FK→teachers(id) CASCADE | Teacher reference |
| calendar_id | BIGINT | FK→school_calendar(id) CASCADE | Date reference |
| check_in_time | TIMESTAMP | NULL | Check-in time |
| check_out_time | TIMESTAMP | NULL | Check-out time |
| status | ENUM('hadir','sakit','izin','dinas','alpa','terlambat') | NOT NULL | Attendance status |
| source | ENUM('digital_wifi','manual','self_report') | NOT NULL | Source of record |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Record update time |

**Unique Constraint:** (teacher_id, calendar_id)

**Indexes:**
- `teacher_attendances_status_index` (status)
- `teacher_attendances_check_in_time_index` (check_in_time)

**SQL DDL:**
```sql
CREATE TYPE teacher_attendance_status_enum AS ENUM ('hadir', 'sakit', 'izin', 'dinas', 'alpa', 'terlambat');
CREATE TYPE teacher_attendance_source_enum AS ENUM ('digital_wifi', 'manual', 'self_report');

CREATE TABLE teacher_attendances (
    id BIGSERIAL PRIMARY KEY,
    teacher_id BIGINT NOT NULL REFERENCES teachers(id) ON DELETE CASCADE,
    calendar_id BIGINT NOT NULL REFERENCES school_calendar(id) ON DELETE CASCADE,
    check_in_time TIMESTAMP NULL,
    check_out_time TIMESTAMP NULL,
    status teacher_attendance_status_enum NOT NULL,
    source teacher_attendance_source_enum NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(teacher_id, calendar_id)
);

CREATE INDEX teacher_attendances_status_index ON teacher_attendances(status);
CREATE INDEX teacher_attendances_check_in_time_index ON teacher_attendances(check_in_time);
```

---

### 16. teacher_attendance_details
**Purpose:** Additional details for teacher absences  
**Primary Key:** id (BIGSERIAL)  
**Foreign Keys:** attendance_id → teacher_attendances(id)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Unique identifier |
| attendance_id | BIGINT | UNIQUE, FK→teacher_attendances(id) CASCADE | Attendance record |
| reason | ENUM('sakit','izin','dinas','alpa','terlambat') | NOT NULL | Absence reason |
| description | TEXT | NULL | Detailed description |
| evidence_url | VARCHAR(255) | NULL | Evidence file URL |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Record update time |

**SQL DDL:**
```sql
CREATE TYPE teacher_attendance_reason_enum AS ENUM ('sakit', 'izin', 'dinas', 'alpa', 'terlambat');

CREATE TABLE teacher_attendance_details (
    id BIGSERIAL PRIMARY KEY,
    attendance_id BIGINT UNIQUE NOT NULL REFERENCES teacher_attendances(id) ON DELETE CASCADE,
    reason teacher_attendance_reason_enum NOT NULL,
    description TEXT NULL,
    evidence_url VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

---

## MBG SYSTEM (Meal Distribution)

### 17. petugas_mbg
**Purpose:** MBG officers authentication  
**Primary Key:** id (BIGSERIAL)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Unique identifier |
| username | VARCHAR(255) | UNIQUE, NOT NULL | Officer username |
| password | VARCHAR(255) | NOT NULL | Hashed password |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Record update time |
| deleted_at | TIMESTAMP | NULL | Soft delete timestamp |

**SQL DDL:**
```sql
CREATE TABLE petugas_mbg (
    id BIGSERIAL PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

---

### 18. piring_mbg
**Purpose:** Daily meal plate stock tracking  
**Primary Key:** id (BIGSERIAL)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Unique identifier |
| stok | INTEGER | DEFAULT 0 | Total stock from all attended students |
| tanggal_distribusi | DATE | NULL | Distribution date |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Record update time |

**SQL DDL:**
```sql
CREATE TABLE piring_mbg (
    id BIGSERIAL PRIMARY KEY,
    stok INTEGER DEFAULT 0,
    tanggal_distribusi DATE NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

---

### 19. mbg_class_daily
**Purpose:** Daily per-class meal distribution details  
**Primary Key:** id (BIGSERIAL)  
**Foreign Keys:** piring_mbg_id → piring_mbg(id), class_id → classes(id)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Unique identifier |
| piring_mbg_id | BIGINT | FK→piring_mbg(id) CASCADE | Meal stock reference |
| class_id | BIGINT | FK→classes(id) CASCADE | Class reference |
| total_students | INTEGER | DEFAULT 0 | Total students in class |
| attended_students | INTEGER | DEFAULT 0 | Students present |
| returned_plates | INTEGER | DEFAULT 0 | Plates returned |
| class_code | VARCHAR(255) | NULL | Class code (e.g., "Kuliner-1") |
| student_representative | VARCHAR(255) | NULL | Student rep name |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Record update time |

**Unique Constraint:** (piring_mbg_id, class_id)

**SQL DDL:**
```sql
CREATE TABLE mbg_class_daily (
    id BIGSERIAL PRIMARY KEY,
    piring_mbg_id BIGINT NOT NULL REFERENCES piring_mbg(id) ON DELETE CASCADE,
    class_id BIGINT NOT NULL REFERENCES classes(id) ON DELETE CASCADE,
    total_students INTEGER DEFAULT 0,
    attended_students INTEGER DEFAULT 0,
    returned_plates INTEGER DEFAULT 0,
    class_code VARCHAR(255) NULL,
    student_representative VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(piring_mbg_id, class_id)
);
```

---

### 20. mbg_teacher_excess
**Purpose:** Extra meal plates for teachers  
**Primary Key:** id (BIGSERIAL)  
**Foreign Keys:** piring_mbg_id → piring_mbg(id), teacher_id → teachers(id)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGSERIAL | PRIMARY KEY | Unique identifier |
| piring_mbg_id | BIGINT | FK→piring_mbg(id) CASCADE | Meal stock reference |
| teacher_id | BIGINT | FK→teachers(id) CASCADE | Teacher reference |
| quantity | INTEGER | DEFAULT 0 | Number of plates given |
| location | VARCHAR(255) | NULL | Room/location |
| notes | TEXT | NULL | Additional notes |
| created_at | TIMESTAMP | NOT NULL | Record creation time |
| updated_at | TIMESTAMP | NOT NULL | Record update time |

**SQL DDL:**
```sql
CREATE TABLE mbg_teacher_excess (
    id BIGSERIAL PRIMARY KEY,
    piring_mbg_id BIGINT NOT NULL REFERENCES piring_mbg(id) ON DELETE CASCADE,
    teacher_id BIGINT NOT NULL REFERENCES teachers(id) ON DELETE CASCADE,
    quantity INTEGER DEFAULT 0,
    location VARCHAR(255) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

---

## RELATIONS DIAGRAM

```
users (1) ----< (1) admins
users (1) ----< (1) teachers
users (1) ----< (1) students
users (1) ----< (*) history_login
users (1) ----< (*) wifi_presence_logs

teachers (1) ----< (*) classes (homeroom_teacher_id)
teachers (1) ----< (*) teacher_class_roles
teachers (1) ----< (*) teacher_attendances
teachers (1) ----< (*) mbg_teacher_excess

classes (1) ----< (*) students
classes (1) ----< (*) student_class_roles
classes (1) ----< (*) teacher_class_roles
classes (1) ----< (*) student_attendances
classes (1) ----< (*) mbg_class_daily

students (1) ----< (*) student_class_roles
students (1) ----< (*) student_attendances
students (1) ----< (1) student_attendance_summary

school_calendar (1) ----< (*) student_attendances
school_calendar (1) ----< (*) teacher_attendances

student_attendances (1) ----< (1) student_attendance_details
teacher_attendances (1) ----< (1) teacher_attendance_details

wifi_networks (1) ----< (*) wifi_presence_logs

piring_mbg (1) ----< (*) mbg_class_daily
piring_mbg (1) ----< (*) mbg_teacher_excess
```

---

## API ENDPOINTS SUGGESTIONS

### Authentication
```
POST   /api/auth/login              - Login (admin/teacher/student)
POST   /api/auth/logout             - Logout
POST   /api/auth/refresh            - Refresh token
GET    /api/auth/me                 - Get current user
POST   /api/auth/change-password    - Change password
```

### Users Management
```
GET    /api/users                   - List all users
GET    /api/users/{id}              - Get user detail
POST   /api/users                   - Create new user
PUT    /api/users/{id}              - Update user
DELETE /api/users/{id}              - Delete user
PATCH  /api/users/{id}/activate     - Activate user
PATCH  /api/users/{id}/deactivate   - Deactivate user
```

### Admins
```
GET    /api/admins                  - List admins
GET    /api/admins/{id}             - Get admin detail
POST   /api/admins                  - Create admin
PUT    /api/admins/{id}             - Update admin
DELETE /api/admins/{id}             - Delete admin
```

### Teachers
```
GET    /api/teachers                - List teachers
GET    /api/teachers/{id}           - Get teacher detail
POST   /api/teachers                - Create teacher
PUT    /api/teachers/{id}           - Update teacher
DELETE /api/teachers/{id}           - Delete teacher
GET    /api/teachers/{id}/classes   - Get teacher's classes
GET    /api/teachers/{id}/attendances - Get teacher's attendance history
```

### Students
```
GET    /api/students                - List students
GET    /api/students/{id}           - Get student detail
POST   /api/students                - Create student
PUT    /api/students/{id}           - Update student
DELETE /api/students/{id}           - Delete student
GET    /api/students/{id}/attendances - Get student's attendance history
GET    /api/students/{id}/summary   - Get attendance summary
```

### Classes
```
GET    /api/classes                 - List classes
GET    /api/classes/{id}            - Get class detail
POST   /api/classes                 - Create class
PUT    /api/classes/{id}            - Update class
DELETE /api/classes/{id}            - Delete class
GET    /api/classes/{id}/students   - Get students in class
GET    /api/classes/{id}/teachers   - Get teachers for class
POST   /api/classes/{id}/assign-teacher - Assign teacher to class
POST   /api/classes/{id}/assign-student - Assign student to class
```

### Student Attendances
```
GET    /api/attendances/students           - List student attendances (with filters)
GET    /api/attendances/students/{id}      - Get attendance detail
POST   /api/attendances/students           - Create attendance record
PUT    /api/attendances/students/{id}      - Update attendance
DELETE /api/attendances/students/{id}      - Delete attendance
POST   /api/attendances/students/bulk      - Bulk create attendances
GET    /api/attendances/students/report    - Generate attendance report
GET    /api/attendances/students/summary   - Get summary statistics
```

### Teacher Attendances
```
GET    /api/attendances/teachers           - List teacher attendances
GET    /api/attendances/teachers/{id}      - Get attendance detail
POST   /api/attendances/teachers           - Create attendance record
PUT    /api/attendances/teachers/{id}      - Update attendance
DELETE /api/attendances/teachers/{id}      - Delete attendance
GET    /api/attendances/teachers/report    - Generate attendance report
```

### School Calendar
```
GET    /api/calendar                - List calendar entries
GET    /api/calendar/{id}           - Get calendar detail
POST   /api/calendar                - Create calendar entry
PUT    /api/calendar/{id}           - Update calendar entry
DELETE /api/calendar/{id}           - Delete calendar entry
GET    /api/calendar/today          - Get today's calendar
GET    /api/calendar/range          - Get calendar by date range
POST   /api/calendar/bulk           - Bulk create calendar entries
```

### WiFi Networks
```
GET    /api/wifi                    - List WiFi networks
GET    /api/wifi/{id}               - Get WiFi detail
POST   /api/wifi                    - Create WiFi network
PUT    /api/wifi/{id}               - Update WiFi network
DELETE /api/wifi/{id}               - Delete WiFi network
```

### WiFi Presence Logs
```
GET    /api/wifi/presence           - List presence logs
POST   /api/wifi/presence           - Create presence log (device detection)
GET    /api/wifi/presence/user/{id} - Get user's presence logs
GET    /api/wifi/presence/today     - Get today's presence logs
```

### History Login
```
GET    /api/history-login           - List login history
GET    /api/history-login/{id}      - Get login detail
GET    /api/history-login/user/{id} - Get user's login history
GET    /api/history-login/active    - Get active sessions
```

### MBG Officers
```
GET    /api/mbg/officers            - List MBG officers
GET    /api/mbg/officers/{id}       - Get officer detail
POST   /api/mbg/officers            - Create officer
PUT    /api/mbg/officers/{id}       - Update officer
DELETE /api/mbg/officers/{id}       - Delete officer
POST   /api/mbg/officers/login      - MBG officer login
```

### MBG Plates (Piring MBG)
```
GET    /api/mbg/plates              - List plate records
GET    /api/mbg/plates/{id}         - Get plate detail
POST   /api/mbg/plates              - Create plate record
PUT    /api/mbg/plates/{id}         - Update plate record
DELETE /api/mbg/plates/{id}         - Delete plate record
GET    /api/mbg/plates/today        - Get today's plate record
```

### MBG Class Daily
```
GET    /api/mbg/class-daily         - List class daily records
GET    /api/mbg/class-daily/{id}    - Get detail
POST   /api/mbg/class-daily         - Create record
PUT    /api/mbg/class-daily/{id}    - Update record
DELETE /api/mbg/class-daily/{id}    - Delete record
GET    /api/mbg/class-daily/by-date - Get by date
GET    /api/mbg/class-daily/by-class/{classId} - Get by class
```

### MBG Teacher Excess
```
GET    /api/mbg/teacher-excess      - List teacher excess records
GET    /api/mbg/teacher-excess/{id} - Get detail
POST   /api/mbg/teacher-excess      - Create record
PUT    /api/mbg/teacher-excess/{id} - Update record
DELETE /api/mbg/teacher-excess/{id} - Delete record
```

---

## COMMON QUERY PARAMETERS

### Pagination
```
?page=1
?per_page=15
```

### Filtering
```
?status=hadir
?class_id=1
?date_from=2025-01-01
?date_to=2025-12-31
?gender=L
?role=wali_kelas
```

### Sorting
```
?sort_by=name
?sort_order=asc
```

### Search
```
?search=john
?q=matematika
```

### Relations (Include)
```
?include=user,class,teacher
?with=attendances,summary
```

---

## RESPONSE FORMAT SUGGESTIONS

### Success Response (Single Record)
```json
{
  "success": true,
  "message": "Data retrieved successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    ...
  }
}
```

### Success Response (List)
```json
{
  "success": true,
  "message": "Data retrieved successfully",
  "data": [...],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

---

## NOTES FOR BACKEND IMPLEMENTATION

1. **Authentication**: Implement JWT or Laravel Sanctum for API authentication
2. **Validation**: Use Laravel Form Request classes for validation
3. **Resources**: Use API Resources for consistent response formatting
4. **Middleware**: 
   - Auth middleware for protected routes
   - Role-based middleware (admin, teacher, student)
   - Rate limiting middleware
5. **Soft Deletes**: Most tables use soft deletes - handle with `withTrashed()`, `onlyTrashed()`
6. **Transactions**: Use DB transactions for operations affecting multiple tables
7. **Eager Loading**: Prevent N+1 queries with proper eager loading
8. **Caching**: Cache frequently accessed data (calendar, classes list, etc.)
9. **File Uploads**: Handle photo_profile and evidence_url uploads with proper validation
10. **Timestamps**: All tables have created_at, updated_at - use automatically
11. **Indexes**: Follow the indexes defined in migrations for query optimization
12. **Enum Values**: Ensure enum values in code match database enum definitions

---

**END OF SCHEMA DOCUMENTATION**
