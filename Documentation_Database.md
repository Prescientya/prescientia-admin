                                             Table "public.users"
      Column       |              Type              | Collation | Nullable |              Default              
-------------------+--------------------------------+-----------+----------+-----------------------------------
 id                | bigint                         |           | not null | nextval('users_id_seq'::regclass)
 email             | character varying(100)         |           | not null | 
 email_verified_at | timestamp(0) without time zone |           |          | 
 password          | character varying(255)         |           | not null | 
 role              | character varying(255)         |           | not null | 
 device_id         | character varying(255)         |           |          | 
 is_active         | boolean                        |           | not null | true
 last_login_at     | timestamp(0) without time zone |           |          | 
 remember_token    | character varying(100)         |           |          | 
 created_at        | timestamp(0) without time zone |           |          | 
 updated_at        | timestamp(0) without time zone |           |          | 
Indexes:
    "users_pkey" PRIMARY KEY, btree (id)
    "users_device_id_index" btree (device_id)
    "users_email_unique" UNIQUE CONSTRAINT, btree (email)
    "users_is_active_index" btree (is_active)
    "users_role_index" btree (role)
Check constraints:
    "users_role_check" CHECK (role::text = ANY (ARRAY['admin'::character varying, 'teacher'::character varying, 'student'::character varying]::text[]))
Referenced by:
    TABLE "admins" CONSTRAINT "admins_user_id_foreign" FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    TABLE "device_change_requests" CONSTRAINT "device_change_requests_user_id_foreign" FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    TABLE "history_login" CONSTRAINT "history_login_user_id_foreign" FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    TABLE "students" CONSTRAINT "students_user_id_foreign" FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    TABLE "teachers" CONSTRAINT "teachers_user_id_foreign" FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    TABLE "wifi_presence_logs" CONSTRAINT "wifi_presence_logs_user_id_foreign" FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE

                                          Table "public.wifi_networks"
   Column   |              Type              | Collation | Nullable |                  Default                  
------------+--------------------------------+-----------+----------+-------------------------------------------
 id         | bigint                         |           | not null | nextval('wifi_networks_id_seq'::regclass)
 ssid       | character varying(100)         |           | not null | 
 bssid      | character varying(50)          |           | not null | 
 ip_address | character varying(45)          |           |          | 
 created_at | timestamp(0) without time zone |           |          | 
 updated_at | timestamp(0) without time zone |           |          | 
Indexes:
    "wifi_networks_pkey" PRIMARY KEY, btree (id)
    "wifi_networks_bssid_unique" UNIQUE CONSTRAINT, btree (bssid)
    "wifi_networks_ssid_index" btree (ssid)
Referenced by:
    TABLE "wifi_presence_logs" CONSTRAINT "wifi_presence_logs_wifi_id_foreign" FOREIGN KEY (wifi_id) REFERENCES wifi_networks(id) ON DELETE CASCADE

                                          Table "public.school_calendar"
   Column   |              Type              | Collation | Nullable |                   Default                   
------------+--------------------------------+-----------+----------+---------------------------------------------
 id         | bigint                         |           | not null | nextval('school_calendar_id_seq'::regclass)
 date       | date                           |           | not null | 
 year       | integer                        |           | not null | 
 month      | integer                        |           | not null | 
 day        | integer                        |           | not null | 
 status     | character varying(255)         |           | not null | 'aktif'::character varying
 created_at | timestamp(0) without time zone |           |          | 
 updated_at | timestamp(0) without time zone |           |          | 
 notes      | character varying(255)         |           |          | 
Indexes:
    "school_calendar_pkey" PRIMARY KEY, btree (id)
    "school_calendar_date_unique" UNIQUE CONSTRAINT, btree (date)
    "school_calendar_status_index" btree (status)
    "school_calendar_year_month_index" btree (year, month)
Check constraints:
    "school_calendar_status_check" CHECK (status::text = ANY (ARRAY['aktif'::character varying, 'libur'::character varying]::text[]))
Referenced by:
    TABLE "student_attendances" CONSTRAINT "student_attendances_calendar_id_foreign" FOREIGN KEY (calendar_id) REFERENCES school_calendar(id) ON DELETE CASCADE
    TABLE "teacher_attendances" CONSTRAINT "teacher_attendances_calendar_id_foreign" FOREIGN KEY (calendar_id) REFERENCES school_calendar(id) ON DELETE CASCADE

                                           Table "public.teachers"
    Column     |              Type              | Collation | Nullable |               Default                
---------------+--------------------------------+-----------+----------+--------------------------------------
 id            | bigint                         |           | not null | nextval('teachers_id_seq'::regclass)
 user_id       | bigint                         |           | not null | 
 nip           | character varying(20)          |           | not null | 
 name          | character varying(100)         |           | not null | 
 gender        | character varying(255)         |           | not null | 
 date_of_birth | date                           |           | not null | 
 phone_number  | character varying(20)          |           |          | 
 address       | text                           |           |          | 
 department    | json                           |           |          | 
 photo_profile | character varying(255)         |           |          | 
 created_at    | timestamp(0) without time zone |           |          | 
 updated_at    | timestamp(0) without time zone |           |          | 
Indexes:
    "teachers_pkey" PRIMARY KEY, btree (id)
    "teachers_name_index" btree (name)
    "teachers_nip_unique" UNIQUE CONSTRAINT, btree (nip)
    "teachers_user_id_unique" UNIQUE CONSTRAINT, btree (user_id)
Check constraints:
    "teachers_gender_check" CHECK (gender::text = ANY (ARRAY['L'::character varying, 'P'::character varying]::text[]))
Foreign-key constraints:
    "teachers_user_id_foreign" FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
Referenced by:
    TABLE "classes" CONSTRAINT "classes_homeroom_teacher_id_foreign" FOREIGN KEY (homeroom_teacher_id) REFERENCES teachers(id) ON DELETE SET NULL
    TABLE "mbg_teacher_excess" CONSTRAINT "mbg_teacher_excess_teacher_id_foreign" FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
    TABLE "student_attendance_details" CONSTRAINT "student_attendance_details_approved_by_foreign" FOREIGN KEY (approved_by) REFERENCES teachers(id) ON DELETE SET NULL
    TABLE "submit_teacher_periods" CONSTRAINT "submit_teacher_periods_teacher_id_foreign" FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
    TABLE "teached_classes" CONSTRAINT "teached_classes_teacher_id_foreign" FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
    TABLE "teacher_attendances" CONSTRAINT "teacher_attendances_teacher_id_foreign" FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
    TABLE "teacher_class_roles" CONSTRAINT "teacher_class_roles_teacher_id_foreign" FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
    TABLE "teacher_class_schedules" CONSTRAINT "teacher_class_schedules_teacher_id_foreign" FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
    TABLE "teacher_schedules" CONSTRAINT "teacher_schedules_teacher_id_foreign" FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
    TABLE "teacher_subject" CONSTRAINT "teacher_subject_teacher_id_foreign" FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE

                                           Table "public.admins"
    Column     |              Type              | Collation | Nullable |              Default               
---------------+--------------------------------+-----------+----------+------------------------------------
 id            | bigint                         |           | not null | nextval('admins_id_seq'::regclass)
 user_id       | bigint                         |           | not null | 
 name          | character varying(100)         |           | not null | 
 photo_profile | character varying(255)         |           |          | 
 created_at    | timestamp(0) without time zone |           |          | 
 updated_at    | timestamp(0) without time zone |           |          | 
Indexes:
    "admins_pkey" PRIMARY KEY, btree (id)
    "admins_user_id_unique" UNIQUE CONSTRAINT, btree (user_id)
Foreign-key constraints:
    "admins_user_id_foreign" FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE

                                              Table "public.classes"
       Column        |              Type              | Collation | Nullable |               Default               
---------------------+--------------------------------+-----------+----------+-------------------------------------
 id                  | bigint                         |           | not null | nextval('classes_id_seq'::regclass)
 class               | integer                        |           | not null | 
 major               | character varying(100)         |           |          | 
 homeroom_teacher_id | bigint                         |           |          | 
 created_at          | timestamp(0) without time zone |           |          | 
 updated_at          | timestamp(0) without time zone |           |          | 
Indexes:
    "classes_pkey" PRIMARY KEY, btree (id)
    "classes_class_index" btree (class)
    "classes_major_index" btree (major)
Foreign-key constraints:
    "classes_homeroom_teacher_id_foreign" FOREIGN KEY (homeroom_teacher_id) REFERENCES teachers(id) ON DELETE SET NULL
Referenced by:
    TABLE "mbg_class_daily" CONSTRAINT "mbg_class_daily_class_id_foreign" FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
    TABLE "student_attendances" CONSTRAINT "student_attendances_class_id_foreign" FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
    TABLE "student_class_roles" CONSTRAINT "student_class_roles_class_id_foreign" FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
    TABLE "students" CONSTRAINT "students_class_id_foreign" FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL
    TABLE "subject_classes" CONSTRAINT "subject_classes_class_id_foreign" FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
    TABLE "submit_teacher_periods" CONSTRAINT "submit_teacher_periods_class_id_foreign" FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
    TABLE "teached_classes" CONSTRAINT "teached_classes_class_id_foreign" FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
    TABLE "teacher_class_roles" CONSTRAINT "teacher_class_roles_class_id_foreign" FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
    TABLE "teacher_class_schedules" CONSTRAINT "teacher_class_schedules_class_id_foreign" FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
    TABLE "teacher_schedules" CONSTRAINT "teacher_schedules_class_id_foreign" FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE

                                           Table "public.students"
    Column     |              Type              | Collation | Nullable |               Default                
---------------+--------------------------------+-----------+----------+--------------------------------------
 id            | bigint                         |           | not null | nextval('students_id_seq'::regclass)
 user_id       | bigint                         |           | not null | 
 nis           | character varying(20)          |           | not null | 
 name          | character varying(100)         |           | not null | 
 gender        | character varying(255)         |           | not null | 
 date_of_birth | date                           |           | not null | 
 phone_number  | character varying(20)          |           |          | 
 address       | text                           |           |          | 
 class_id      | bigint                         |           |          | 
 photo_profile | character varying(255)         |           |          | 
 created_at    | timestamp(0) without time zone |           |          | 
 updated_at    | timestamp(0) without time zone |           |          | 
Indexes:
    "students_pkey" PRIMARY KEY, btree (id)
    "students_class_id_index" btree (class_id)
    "students_name_index" btree (name)
    "students_nis_unique" UNIQUE CONSTRAINT, btree (nis)
    "students_user_id_unique" UNIQUE CONSTRAINT, btree (user_id)
Check constraints:
    "students_gender_check" CHECK (gender::text = ANY (ARRAY['L'::character varying, 'P'::character varying]::text[]))
Foreign-key constraints:
    "students_class_id_foreign" FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL
    "students_user_id_foreign" FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
Referenced by:
    TABLE "student_attendance_summary" CONSTRAINT "student_attendance_summary_student_id_foreign" FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
    TABLE "student_attendances" CONSTRAINT "student_attendances_student_id_foreign" FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
    TABLE "student_class_roles" CONSTRAINT "student_class_roles_student_id_foreign" FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE

                                          Table "public.student_class_roles"
   Column   |              Type              | Collation | Nullable |                     Default                     
------------+--------------------------------+-----------+----------+-------------------------------------------------
 id         | bigint                         |           | not null | nextval('student_class_roles_id_seq'::regclass)
 class_id   | bigint                         |           | not null | 
 student_id | bigint                         |           | not null | 
 role       | character varying(50)          |           | not null | 'pelajar'::character varying
 created_at | timestamp(0) without time zone |           |          | 
 updated_at | timestamp(0) without time zone |           |          | 
Indexes:
    "student_class_roles_pkey" PRIMARY KEY, btree (id)
    "student_class_roles_class_id_student_id_unique" UNIQUE CONSTRAINT, btree (class_id, student_id)
    "student_class_roles_role_index" btree (role)
Foreign-key constraints:
    "student_class_roles_class_id_foreign" FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
    "student_class_roles_student_id_foreign" FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE

                                          Table "public.teacher_class_roles"
   Column   |              Type              | Collation | Nullable |                     Default                     
------------+--------------------------------+-----------+----------+-------------------------------------------------
 id         | bigint                         |           | not null | nextval('teacher_class_roles_id_seq'::regclass)
 teacher_id | bigint                         |           | not null | 
 class_id   | bigint                         |           |          | 
 role       | character varying(255)         |           | not null | 
 created_at | timestamp(0) without time zone |           |          | 
 updated_at | timestamp(0) without time zone |           |          | 
Indexes:
    "teacher_class_roles_pkey" PRIMARY KEY, btree (id)
    "teacher_class_roles_role_index" btree (role)
    "teacher_class_roles_teacher_id_class_id_index" btree (teacher_id, class_id)
Check constraints:
    "teacher_class_roles_role_check" CHECK (role::text = ANY (ARRAY['pengajar'::character varying, 'wali_kelas'::character varying]::text[]))
Foreign-key constraints:
    "teacher_class_roles_class_id_foreign" FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
    "teacher_class_roles_teacher_id_foreign" FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE

                                             Table "public.history_login"
      Column      |              Type              | Collation | Nullable |                  Default                  
------------------+--------------------------------+-----------+----------+-------------------------------------------
 id               | bigint                         |           | not null | nextval('history_login_id_seq'::regclass)
 user_id          | bigint                         |           | not null | 
 device_id        | character varying(255)         |           |          | 
 wifi_mac         | character varying(50)          |           |          | 
 ip_address       | character varying(45)          |           |          | 
 login_at         | timestamp(0) without time zone |           |          | 
 logout_at        | timestamp(0) without time zone |           |          | 
 duration_minutes | integer                        |           |          | 
 location         | character varying(255)         |           |          | 
 status           | character varying(255)         |           | not null | 
 created_at       | timestamp(0) without time zone |           |          | 
 updated_at       | timestamp(0) without time zone |           |          | 
Indexes:
    "history_login_pkey" PRIMARY KEY, btree (id)
    "history_login_login_at_index" btree (login_at)
    "history_login_status_index" btree (status)
    "history_login_user_id_index" btree (user_id)
Check constraints:
    "history_login_status_check" CHECK (status::text = ANY (ARRAY['success'::character varying, 'failed'::character varying]::text[]))
Foreign-key constraints:
    "history_login_user_id_foreign" FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE

                                          Table "public.wifi_presence_logs"
   Column    |              Type              | Collation | Nullable |                    Default                     
-------------+--------------------------------+-----------+----------+------------------------------------------------
 id          | bigint                         |           | not null | nextval('wifi_presence_logs_id_seq'::regclass)
 user_id     | bigint                         |           | not null | 
 wifi_id     | bigint                         |           | not null | 
 detected_at | timestamp(0) without time zone |           | not null | 
 created_at  | timestamp(0) without time zone |           |          | 
 updated_at  | timestamp(0) without time zone |           |          | 
Indexes:
    "wifi_presence_logs_pkey" PRIMARY KEY, btree (id)
    "wifi_presence_logs_user_id_detected_at_index" btree (user_id, detected_at)
    "wifi_presence_logs_wifi_id_index" btree (wifi_id)
Foreign-key constraints:
    "wifi_presence_logs_user_id_foreign" FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    "wifi_presence_logs_wifi_id_foreign" FOREIGN KEY (wifi_id) REFERENCES wifi_networks(id) ON DELETE CASCADE

                                            Table "public.student_attendances"
     Column     |              Type              | Collation | Nullable |                     Default                     
----------------+--------------------------------+-----------+----------+-------------------------------------------------
 id             | bigint                         |           | not null | nextval('student_attendances_id_seq'::regclass)
 student_id     | bigint                         |           | not null | 
 class_id       | bigint                         |           | not null | 
 calendar_id    | bigint                         |           | not null | 
 check_in_time  | timestamp(0) with time zone    |           |          | 
 check_out_time | timestamp(0) with time zone    |           |          | 
 status         | character varying(255)         |           | not null | 
 source         | character varying(255)         |           |          | 
 created_at     | timestamp(0) without time zone |           |          | 
 updated_at     | timestamp(0) without time zone |           |          | 
Indexes:
    "student_attendances_pkey" PRIMARY KEY, btree (id)
    "student_attendances_check_in_time_index" btree (check_in_time)
    "student_attendances_class_id_calendar_id_index" btree (class_id, calendar_id)
    "student_attendances_status_index" btree (status)
    "student_attendances_student_id_calendar_id_unique" UNIQUE CONSTRAINT, btree (student_id, calendar_id)
Check constraints:
    "student_attendances_source_check" CHECK (source::text = ANY (ARRAY['digital_wifi'::character varying, 'guru_pengajar'::character varying, 'wali_kelas'::character varying, 'manual'::character varying, 'self_report'::character varying]::text[]))
    "student_attendances_status_check" CHECK (status::text = ANY (ARRAY['hadir'::character varying, 'sakit'::character varying, 'izin'::character varying, 'alpa'::character varying, 'terlambat'::character varying]::text[]))
Foreign-key constraints:
    "student_attendances_calendar_id_foreign" FOREIGN KEY (calendar_id) REFERENCES school_calendar(id) ON DELETE CASCADE
    "student_attendances_class_id_foreign" FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
    "student_attendances_student_id_foreign" FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
Referenced by:
    TABLE "student_attendance_details" CONSTRAINT "student_attendance_details_attendance_id_foreign" FOREIGN KEY (attendance_id) REFERENCES student_attendances(id) ON DELETE CASCADE

                                            Table "public.teacher_attendances"
     Column     |              Type              | Collation | Nullable |                     Default                     
----------------+--------------------------------+-----------+----------+-------------------------------------------------
 id             | bigint                         |           | not null | nextval('teacher_attendances_id_seq'::regclass)
 teacher_id     | bigint                         |           | not null | 
 calendar_id    | bigint                         |           | not null | 
 period_id      | bigint                         |           |          | 
 check_in_time  | timestamp(0) with time zone    |           |          | 
 check_out_time | timestamp(0) with time zone    |           |          | 
 status         | character varying(255)         |           | not null | 
 source         | character varying(255)         |           |          | 
 created_at     | timestamp(0) without time zone |           |          | 
 updated_at     | timestamp(0) without time zone |           |          | 
Indexes:
    "teacher_attendances_pkey" PRIMARY KEY, btree (id)
    "teacher_attendances_check_in_time_index" btree (check_in_time)
    "teacher_attendances_status_index" btree (status)
    "teacher_attendances_teacher_id_calendar_id_unique" UNIQUE CONSTRAINT, btree (teacher_id, calendar_id)
Check constraints:
    "teacher_attendances_source_check" CHECK (source::text = ANY (ARRAY['digital_wifi'::character varying, 'manual'::character varying, 'self_report'::character varying]::text[]))
    "teacher_attendances_status_check" CHECK (status::text = ANY (ARRAY['hadir'::character varying, 'sakit'::character varying, 'izin'::character varying, 'dinas'::character varying, 'alpa'::character varying, 'terlambat'::character varying]::text[]))
Foreign-key constraints:
    "teacher_attendances_calendar_id_foreign" FOREIGN KEY (calendar_id) REFERENCES school_calendar(id) ON DELETE CASCADE
    "teacher_attendances_period_id_foreign" FOREIGN KEY (period_id) REFERENCES class_periods(id) ON DELETE CASCADE
    "teacher_attendances_teacher_id_foreign" FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
Referenced by:
    TABLE "teacher_attendance_details" CONSTRAINT "teacher_attendance_details_attendance_id_foreign" FOREIGN KEY (attendance_id) REFERENCES teacher_attendances(id) ON DELETE CASCADE

                                            Table "public.student_attendance_details"
     Column      |              Type              | Collation | Nullable |                        Default                         
-----------------+--------------------------------+-----------+----------+--------------------------------------------------------
 id              | bigint                         |           | not null | nextval('student_attendance_details_id_seq'::regclass)
 attendance_id   | bigint                         |           | not null | 
 status          | character varying(255)         |           | not null | 
 approval_status | character varying(50)          |           | not null | 'pending'::character varying
 approved_by     | bigint                         |           |          | 
 approved_at     | timestamp(0) with time zone    |           |          | 
 description     | text                           |           |          | 
 evidence_url    | character varying(255)         |           |          | 
 created_at      | timestamp(0) without time zone |           |          | 
 updated_at      | timestamp(0) without time zone |           |          | 
Indexes:
    "student_attendance_details_pkey" PRIMARY KEY, btree (id)
    "idx_student_attendance_details_approval_status" btree (approval_status)
    "student_attendance_details_attendance_id_unique" UNIQUE CONSTRAINT, btree (attendance_id)
Check constraints:
    "student_attendance_details_status_check" CHECK (status::text = ANY (ARRAY['sakit'::character varying, 'izin'::character varying, 'alpa'::character varying, 'terlambat'::character varying]::text[]))
Foreign-key constraints:
    "student_attendance_details_approved_by_foreign" FOREIGN KEY (approved_by) REFERENCES teachers(id) ON DELETE SET NULL
    "student_attendance_details_attendance_id_foreign" FOREIGN KEY (attendance_id) REFERENCES student_attendances(id) ON DELETE CASCADE

                                           Table "public.teacher_attendance_details"
    Column     |              Type              | Collation | Nullable |                        Default                         
---------------+--------------------------------+-----------+----------+--------------------------------------------------------
 id            | bigint                         |           | not null | nextval('teacher_attendance_details_id_seq'::regclass)
 attendance_id | bigint                         |           | not null | 
 period_id     | bigint                         |           |          | 
 reason        | character varying(255)         |           | not null | 
 description   | text                           |           |          | 
 evidence_url  | character varying(255)         |           |          | 
 created_at    | timestamp(0) without time zone |           |          | 
 updated_at    | timestamp(0) without time zone |           |          | 
Indexes:
    "teacher_attendance_details_pkey" PRIMARY KEY, btree (id)
    "teacher_attendance_details_attendance_id_unique" UNIQUE CONSTRAINT, btree (attendance_id)
Check constraints:
    "teacher_attendance_details_reason_check" CHECK (reason::text = ANY (ARRAY['sakit'::character varying, 'izin'::character varying, 'dinas'::character varying, 'alpa'::character varying, 'terlambat'::character varying]::text[]))
Foreign-key constraints:
    "teacher_attendance_details_attendance_id_foreign" FOREIGN KEY (attendance_id) REFERENCES teacher_attendances(id) ON DELETE CASCADE
    "teacher_attendance_details_period_id_foreign" FOREIGN KEY (period_id) REFERENCES class_periods(id) ON DELETE CASCADE

                                          Table "public.student_attendance_summary"
   Column    |              Type              | Collation | Nullable |                        Default                         
-------------+--------------------------------+-----------+----------+--------------------------------------------------------
 id          | bigint                         |           | not null | nextval('student_attendance_summary_id_seq'::regclass)
 student_id  | bigint                         |           | not null | 
 total_hadir | integer                        |           | not null | 0
 total_izin  | integer                        |           | not null | 0
 total_sakit | integer                        |           | not null | 0
 total_alpha | integer                        |           | not null | 0
 created_at  | timestamp(0) without time zone |           |          | 
 updated_at  | timestamp(0) without time zone |           |          | 
Indexes:
    "student_attendance_summary_pkey" PRIMARY KEY, btree (id)
    "student_attendance_summary_student_id_unique" UNIQUE CONSTRAINT, btree (student_id)
Foreign-key constraints:
    "student_attendance_summary_student_id_foreign" FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE

                                          Table "public.petugas_mbg"
   Column   |              Type              | Collation | Nullable |                 Default                 
------------+--------------------------------+-----------+----------+-----------------------------------------
 id         | bigint                         |           | not null | nextval('petugas_mbg_id_seq'::regclass)
 username   | character varying(255)         |           | not null | 
 password   | character varying(255)         |           | not null | 
 created_at | timestamp(0) without time zone |           |          | 
 updated_at | timestamp(0) without time zone |           |          | 
Indexes:
    "petugas_mbg_pkey" PRIMARY KEY, btree (id)
    "petugas_mbg_username_unique" UNIQUE CONSTRAINT, btree (username)

                                              Table "public.piring_mbg"
       Column       |              Type              | Collation | Nullable |                Default                 
--------------------+--------------------------------+-----------+----------+----------------------------------------
 id                 | bigint                         |           | not null | nextval('piring_mbg_id_seq'::regclass)
 stok               | integer                        |           | not null | 0
 tanggal_distribusi | date                           |           |          | 
 created_at         | timestamp(0) without time zone |           |          | 
 updated_at         | timestamp(0) without time zone |           |          | 
Indexes:
    "piring_mbg_pkey" PRIMARY KEY, btree (id)
Referenced by:
    TABLE "mbg_class_daily" CONSTRAINT "mbg_class_daily_piring_mbg_id_foreign" FOREIGN KEY (piring_mbg_id) REFERENCES piring_mbg(id) ON DELETE CASCADE
    TABLE "mbg_teacher_excess" CONSTRAINT "mbg_teacher_excess_piring_mbg_id_foreign" FOREIGN KEY (piring_mbg_id) REFERENCES piring_mbg(id) ON DELETE CASCADE

                                                Table "public.mbg_class_daily"
         Column         |              Type              | Collation | Nullable |                   Default                   
------------------------+--------------------------------+-----------+----------+---------------------------------------------
 id                     | bigint                         |           | not null | nextval('mbg_class_daily_id_seq'::regclass)
 piring_mbg_id          | bigint                         |           | not null | 
 class_id               | bigint                         |           | not null | 
 total_students         | integer                        |           | not null | 0
 attended_students      | integer                        |           | not null | 0
 returned_plates        | integer                        |           | not null | 0
 given_plates           | integer                        |           |          | 
 student_representative | character varying(255)         |           |          | 
 created_at             | timestamp(0) without time zone |           |          | 
 updated_at             | timestamp(0) without time zone |           |          | 
Indexes:
    "mbg_class_daily_pkey" PRIMARY KEY, btree (id)
    "mbg_class_daily_piring_mbg_id_class_id_unique" UNIQUE CONSTRAINT, btree (piring_mbg_id, class_id)
Foreign-key constraints:
    "mbg_class_daily_class_id_foreign" FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
    "mbg_class_daily_piring_mbg_id_foreign" FOREIGN KEY (piring_mbg_id) REFERENCES piring_mbg(id) ON DELETE CASCADE

                                           Table "public.mbg_teacher_excess"
    Column     |              Type              | Collation | Nullable |                    Default                     
---------------+--------------------------------+-----------+----------+------------------------------------------------
 id            | bigint                         |           | not null | nextval('mbg_teacher_excess_id_seq'::regclass)
 piring_mbg_id | bigint                         |           | not null | 
 teacher_id    | bigint                         |           | not null | 
 quantity      | integer                        |           | not null | 0
 location      | character varying(255)         |           |          | 
 notes         | text                           |           |          | 
 created_at    | timestamp(0) without time zone |           |          | 
 updated_at    | timestamp(0) without time zone |           |          | 
Indexes:
    "mbg_teacher_excess_pkey" PRIMARY KEY, btree (id)
Foreign-key constraints:
    "mbg_teacher_excess_piring_mbg_id_foreign" FOREIGN KEY (piring_mbg_id) REFERENCES piring_mbg(id) ON DELETE CASCADE
    "mbg_teacher_excess_teacher_id_foreign" FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE

                                          Table "public.teached_classes"
   Column    |              Type              | Collation | Nullable |                   Default                   
-------------+--------------------------------+-----------+----------+---------------------------------------------
 id          | bigint                         |           | not null | nextval('teached_classes_id_seq'::regclass)
 teacher_id  | bigint                         |           | not null | 
 class_id    | bigint                         |           | not null | 
 subject_id  | bigint                         |           |          | 
 semester    | integer                        |           | not null | 
 departments | json                           |           | not null | 
 created_at  | timestamp(0) without time zone |           |          | 
 updated_at  | timestamp(0) without time zone |           |          | 
Indexes:
    "teached_classes_pkey" PRIMARY KEY, btree (id)
    "idx_teacher_class_subject_semester" btree (teacher_id, class_id, subject_id, semester)
    "teached_classes_class_id_semester_index" btree (class_id, semester)
    "teached_classes_semester_index" btree (semester)
    "teached_classes_teacher_id_semester_index" btree (teacher_id, semester)
Foreign-key constraints:
    "teached_classes_class_id_foreign" FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
    "teached_classes_subject_id_foreign" FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
    "teached_classes_teacher_id_foreign" FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
Referenced by:
    TABLE "subject_teached_class" CONSTRAINT "subject_teached_class_teached_class_id_foreign" FOREIGN KEY (teached_class_id) REFERENCES teached_classes(id) ON DELETE CASCADE

                                          Table "public.subjects"
   Column    |              Type              | Collation | Nullable |               Default                
-------------+--------------------------------+-----------+----------+--------------------------------------
 id          | bigint                         |           | not null | nextval('subjects_id_seq'::regclass)
 name        | character varying(100)         |           | not null | 
 description | text                           |           |          | 
 is_active   | boolean                        |           | not null | true
 created_at  | timestamp(0) without time zone |           |          | 
 updated_at  | timestamp(0) without time zone |           |          | 
Indexes:
    "subjects_pkey" PRIMARY KEY, btree (id)
    "subjects_name_index" btree (name)
    "subjects_name_unique" UNIQUE CONSTRAINT, btree (name)
Referenced by:
    TABLE "subject_classes" CONSTRAINT "subject_classes_subject_id_foreign" FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
    TABLE "subject_teached_class" CONSTRAINT "subject_teached_class_subject_id_foreign" FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
    TABLE "submit_teacher_periods" CONSTRAINT "submit_teacher_periods_subject_id_foreign" FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
    TABLE "teached_classes" CONSTRAINT "teached_classes_subject_id_foreign" FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
    TABLE "teacher_class_schedules" CONSTRAINT "teacher_class_schedules_subject_id_foreign" FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
    TABLE "teacher_schedules" CONSTRAINT "teacher_schedules_subject_id_foreign" FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
    TABLE "teacher_subject" CONSTRAINT "teacher_subject_subject_id_foreign" FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE

                                          Table "public.teacher_subject"
   Column   |              Type              | Collation | Nullable |                   Default                   
------------+--------------------------------+-----------+----------+---------------------------------------------
 id         | bigint                         |           | not null | nextval('teacher_subject_id_seq'::regclass)
 teacher_id | bigint                         |           | not null | 
 subject_id | bigint                         |           | not null | 
 created_at | timestamp(0) without time zone |           |          | 
 updated_at | timestamp(0) without time zone |           |          | 
Indexes:
    "teacher_subject_pkey" PRIMARY KEY, btree (id)
    "teacher_subject_subject_id_index" btree (subject_id)
    "teacher_subject_teacher_id_index" btree (teacher_id)
    "teacher_subject_teacher_id_subject_id_unique" UNIQUE CONSTRAINT, btree (teacher_id, subject_id)
Foreign-key constraints:
    "teacher_subject_subject_id_foreign" FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
    "teacher_subject_teacher_id_foreign" FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE

                                             Table "public.subject_teached_class"
      Column      |              Type              | Collation | Nullable |                      Default                      
------------------+--------------------------------+-----------+----------+---------------------------------------------------
 id               | bigint                         |           | not null | nextval('subject_teached_class_id_seq'::regclass)
 subject_id       | bigint                         |           | not null | 
 teached_class_id | bigint                         |           | not null | 
 created_at       | timestamp(0) without time zone |           |          | 
 updated_at       | timestamp(0) without time zone |           |          | 
Indexes:
    "subject_teached_class_pkey" PRIMARY KEY, btree (id)
    "subject_teached_class_subject_id_index" btree (subject_id)
    "subject_teached_class_subject_id_teached_class_id_unique" UNIQUE CONSTRAINT, btree (subject_id, teached_class_id)
    "subject_teached_class_teached_class_id_index" btree (teached_class_id)
Foreign-key constraints:
    "subject_teached_class_subject_id_foreign" FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
    "subject_teached_class_teached_class_id_foreign" FOREIGN KEY (teached_class_id) REFERENCES teached_classes(id) ON DELETE CASCADE

                                             Table "public.class_periods"
      Column      |              Type              | Collation | Nullable |                  Default                  
------------------+--------------------------------+-----------+----------+-------------------------------------------
 id               | bigint                         |           | not null | nextval('class_periods_id_seq'::regclass)
 day              | character varying(255)         |           | not null | 
 sequence         | integer                        |           | not null | 
 start_time       | time(0) without time zone      |           | not null | 
 end_time         | time(0) without time zone      |           | not null | 
 duration_minutes | integer                        |           | not null | 
 activity_type    | character varying(255)         |           | not null | 'lesson'::character varying
 note             | character varying(255)         |           |          | 
 created_at       | timestamp(0) without time zone |           |          | 
 updated_at       | timestamp(0) without time zone |           |          | 
Indexes:
    "class_periods_pkey" PRIMARY KEY, btree (id)
    "class_periods_activity_type_index" btree (activity_type)
    "class_periods_day_index" btree (day)
    "class_periods_day_sequence_unique" UNIQUE CONSTRAINT, btree (day, sequence)
    "class_periods_day_start_time_unique" UNIQUE CONSTRAINT, btree (day, start_time)
Check constraints:
    "class_periods_activity_type_check" CHECK (activity_type::text = ANY (ARRAY['lesson'::character varying, 'break'::character varying, 'ceremony'::character varying, 'prayer'::character varying, 'cleaning'::character varying, 'other'::character varying]::text[]))
    "class_periods_day_check" CHECK (day::text = ANY (ARRAY['senin'::character varying, 'selasa'::character varying, 'rabu'::character varying, 'kamis'::character varying, 'jumat'::character varying]::text[]))
Referenced by:
    TABLE "submit_teacher_periods" CONSTRAINT "submit_teacher_periods_period_id_foreign" FOREIGN KEY (period_id) REFERENCES class_periods(id) ON DELETE CASCADE
    TABLE "teacher_attendance_details" CONSTRAINT "teacher_attendance_details_period_id_foreign" FOREIGN KEY (period_id) REFERENCES class_periods(id) ON DELETE CASCADE
    TABLE "teacher_attendances" CONSTRAINT "teacher_attendances_period_id_foreign" FOREIGN KEY (period_id) REFERENCES class_periods(id) ON DELETE CASCADE
    TABLE "teacher_class_schedules" CONSTRAINT "teacher_class_schedules_period_id_foreign" FOREIGN KEY (period_id) REFERENCES class_periods(id) ON DELETE CASCADE
    TABLE "teacher_schedules" CONSTRAINT "teacher_schedules_class_period_id_foreign" FOREIGN KEY (class_period_id) REFERENCES class_periods(id) ON DELETE CASCADE

                                          Table "public.teacher_class_schedules"
   Column   |              Type              | Collation | Nullable |                       Default                       
------------+--------------------------------+-----------+----------+-----------------------------------------------------
 id         | bigint                         |           | not null | nextval('teacher_class_schedules_id_seq'::regclass)
 teacher_id | bigint                         |           | not null | 
 class_id   | bigint                         |           | not null | 
 subject_id | bigint                         |           | not null | 
 period_id  | bigint                         |           | not null | 
 day        | character varying(255)         |           | not null | 
 semester   | integer                        |           | not null | 
 created_at | timestamp(0) without time zone |           |          | 
 updated_at | timestamp(0) without time zone |           |          | 
Indexes:
    "teacher_class_schedules_pkey" PRIMARY KEY, btree (id)
    "unique_teacher_class_schedule" UNIQUE CONSTRAINT, btree (teacher_id, class_id, subject_id, period_id, day, semester)
Foreign-key constraints:
    "teacher_class_schedules_class_id_foreign" FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
    "teacher_class_schedules_period_id_foreign" FOREIGN KEY (period_id) REFERENCES class_periods(id) ON DELETE CASCADE
    "teacher_class_schedules_subject_id_foreign" FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
    "teacher_class_schedules_teacher_id_foreign" FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE

                                           Table "public.submit_teacher_periods"
    Column    |              Type              | Collation | Nullable |                      Default                       
--------------+--------------------------------+-----------+----------+----------------------------------------------------
 id           | bigint                         |           | not null | nextval('submit_teacher_periods_id_seq'::regclass)
 teacher_id   | bigint                         |           | not null | 
 class_id     | bigint                         |           | not null | 
 subject_id   | bigint                         |           | not null | 
 period_id    | bigint                         |           | not null | 
 day          | character varying(255)         |           | not null | 
 photo_url    | character varying(255)         |           | not null | 
 is_present   | boolean                        |           | not null | true
 submitted_at | timestamp(0) with time zone    |           | not null | CURRENT_TIMESTAMP
 created_at   | timestamp(0) without time zone |           |          | 
 updated_at   | timestamp(0) without time zone |           |          | 
Indexes:
    "submit_teacher_periods_pkey" PRIMARY KEY, btree (id)
    "unique_teacher_period_submission" UNIQUE CONSTRAINT, btree (teacher_id, class_id, subject_id, period_id, day)
Foreign-key constraints:
    "submit_teacher_periods_class_id_foreign" FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
    "submit_teacher_periods_period_id_foreign" FOREIGN KEY (period_id) REFERENCES class_periods(id) ON DELETE CASCADE
    "submit_teacher_periods_subject_id_foreign" FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
    "submit_teacher_periods_teacher_id_foreign" FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE

                                           Table "public.device_change_requests"
    Column     |              Type              | Collation | Nullable |                      Default                       
---------------+--------------------------------+-----------+----------+----------------------------------------------------
 id            | bigint                         |           | not null | nextval('device_change_requests_id_seq'::regclass)
 user_id       | bigint                         |           | not null | 
 device_id_old | character varying(255)         |           | not null | 
 device_id_new | character varying(255)         |           | not null | 
 status        | character varying(255)         |           | not null | 'pending'::character varying
 submitted_by  | character varying(255)         |           |          | 
 created_at    | timestamp(0) without time zone |           |          | 
 updated_at    | timestamp(0) without time zone |           |          | 
Indexes:
    "device_change_requests_pkey" PRIMARY KEY, btree (id)
Check constraints:
    "device_change_requests_status_check" CHECK (status::text = ANY (ARRAY['pending'::character varying, 'denied'::character varying, 'confirm'::character varying]::text[]))
Foreign-key constraints:
    "device_change_requests_user_id_foreign" FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE

                         Table "public.sessions"
    Column     |          Type          | Collation | Nullable | Default 
---------------+------------------------+-----------+----------+---------
 id            | character varying(255) |           | not null | 
 user_id       | bigint                 |           |          | 
 ip_address    | character varying(45)  |           |          | 
 user_agent    | text                   |           |          | 
 payload       | text                   |           | not null | 
 last_activity | integer                |           | not null | 
Indexes:
    "sessions_pkey" PRIMARY KEY, btree (id)
    "sessions_last_activity_index" btree (last_activity)
    "sessions_user_id_index" btree (user_id)

                         Table "public.cache"
   Column   |          Type          | Collation | Nullable | Default 
------------+------------------------+-----------+----------+---------
 key        | character varying(255) |           | not null | 
 value      | text                   |           | not null | 
 expiration | integer                |           | not null | 
Indexes:
    "cache_pkey" PRIMARY KEY, btree (key)

                      Table "public.cache_locks"
   Column   |          Type          | Collation | Nullable | Default 
------------+------------------------+-----------+----------+---------
 key        | character varying(255) |           | not null | 
 owner      | character varying(255) |           | not null | 
 expiration | integer                |           | not null | 
Indexes:
    "cache_locks_pkey" PRIMARY KEY, btree (key)

                                          Table "public.subject_classes"
   Column   |              Type              | Collation | Nullable |                   Default                   
------------+--------------------------------+-----------+----------+---------------------------------------------
 id         | bigint                         |           | not null | nextval('subject_classes_id_seq'::regclass)
 subject_id | bigint                         |           | not null | 
 class_id   | bigint                         |           | not null | 
 created_at | timestamp(0) without time zone |           |          | 
 updated_at | timestamp(0) without time zone |           |          | 
Indexes:
    "subject_classes_pkey" PRIMARY KEY, btree (id)
    "subject_classes_class_id_index" btree (class_id)
    "subject_classes_subject_id_class_id_index" btree (subject_id, class_id)
    "subject_classes_subject_id_index" btree (subject_id)
    "uq_subject_class" UNIQUE CONSTRAINT, btree (subject_id, class_id)
Foreign-key constraints:
    "subject_classes_class_id_foreign" FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
    "subject_classes_subject_id_foreign" FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE

                                            Table "public.teacher_schedules"
     Column      |              Type              | Collation | Nullable |                    Default                    
-----------------+--------------------------------+-----------+----------+-----------------------------------------------
 id              | bigint                         |           | not null | nextval('teacher_schedules_id_seq'::regclass)
 teacher_id      | bigint                         |           | not null | 
 subject_id      | bigint                         |           | not null | 
 class_id        | bigint                         |           | not null | 
 class_period_id | bigint                         |           | not null | 
 created_at      | timestamp(0) without time zone |           |          | 
 updated_at      | timestamp(0) without time zone |           |          | 
Indexes:
    "teacher_schedules_pkey" PRIMARY KEY, btree (id)
    "uniq_class_period" UNIQUE CONSTRAINT, btree (class_id, class_period_id)
    "uniq_teacher_period" UNIQUE CONSTRAINT, btree (teacher_id, class_period_id)
Foreign-key constraints:
    "teacher_schedules_class_id_foreign" FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
    "teacher_schedules_class_period_id_foreign" FOREIGN KEY (class_period_id) REFERENCES class_periods(id) ON DELETE CASCADE
    "teacher_schedules_subject_id_foreign" FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
    "teacher_schedules_teacher_id_foreign" FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE


raw output \d tablename