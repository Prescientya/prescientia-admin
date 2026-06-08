@php
    // Static page — NO DB queries, content is hardcoded (Google Play requires the
    // privacy policy to be "not editable"). $type only switches which app variant
    // (student/teacher) the technical details describe.
    $isSiswa   = $type === 'siswa';
    $appName   = $isSiswa ? 'Prescientia Student' : 'Prescientia Teacher';
    $appNameId = $isSiswa ? 'Prescientia Siswa' : 'Prescientia Guru';
    $role      = $isSiswa ? 'student' : 'teacher';
    $effective = '8 June 2026';
    // CHANGE if needed: official school contact email for privacy inquiries.
    $contactEmail = 'humas@smkn1ciamis.id';
    $schoolName   = 'SMK Negeri 1 Ciamis';

    // Android runtime permissions actually declared by this app variant, with the
    // exact technical reason each one is requested.
    $permissions = $isSiswa ? [
        ['INTERNET', 'Communicate with the school backend API over HTTPS (login, fetching the registered WiFi list, submitting attendance).'],
        ['ACCESS_WIFI_STATE', 'Read the current WiFi adapter state and read scan results (via the wifi_scan plugin).'],
        ['CHANGE_WIFI_STATE', 'Trigger an active WiFi scan to detect the school access points nearby.'],
        ['NEARBY_WIFI_DEVICES', 'On Android 13+ (API 33), scan for nearby WiFi access points without exposing physical location.'],
        ['ACCESS_FINE_LOCATION', 'Required by Android as a precondition for WiFi scanning. In the Student app it is ALSO used to read GPS coordinates (latitude/longitude/accuracy) once, at the moment you submit attendance, as a secondary location check.'],
        ['ACCESS_COARSE_LOCATION', 'Fallback coarse location, also required by Android for WiFi scanning.'],
        ['POST_NOTIFICATIONS', 'Display local attendance reminder notifications (Android 13+ runtime notification permission).'],
        ['SCHEDULE_EXACT_ALARM / USE_EXACT_ALARM', 'Schedule attendance reminders to fire at precise times.'],
        ['RECEIVE_BOOT_COMPLETED', 'Re-register scheduled reminders after the device restarts.'],
        ['FOREGROUND_SERVICE', 'Run scheduled background work (WorkManager) reliably.'],
        ['WAKE_LOCK', 'Keep the CPU briefly awake to deliver a scheduled reminder.'],
        ['VIBRATE', 'Vibrate the device for reminder notifications.'],
    ] : [
        ['INTERNET', 'Communicate with the school backend API over HTTPS (login, fetching the registered WiFi list, submitting attendance and teaching records).'],
        ['ACCESS_NETWORK_STATE', 'Check whether the device currently has network connectivity.'],
        ['ACCESS_WIFI_STATE', 'Read the current WiFi adapter state and read scan results (via the wifi_scan plugin).'],
        ['CHANGE_WIFI_STATE', 'Trigger an active WiFi scan to detect the school access points nearby.'],
        ['CHANGE_NETWORK_STATE', 'Allow the WiFi scanning subsystem to operate.'],
        ['ACCESS_FINE_LOCATION', 'Required by Android as a precondition for WiFi scanning. The Teacher app does NOT read or transmit GPS coordinates — location permission is used solely to satisfy the Android requirement for scanning WiFi and to verify that the device location service is switched on.'],
        ['ACCESS_COARSE_LOCATION', 'Fallback coarse location, also required by Android for WiFi scanning.'],
        ['POST_NOTIFICATIONS', 'Display local class-period and attendance reminder notifications (Android 13+ runtime notification permission).'],
        ['SCHEDULE_EXACT_ALARM / USE_EXACT_ALARM', 'Schedule class-period and attendance reminders to fire at precise times.'],
        ['RECEIVE_BOOT_COMPLETED', 'Re-register scheduled reminders after the device restarts.'],
        ['FOREGROUND_SERVICE', 'Run scheduled background work (WorkManager) reliably.'],
        ['WAKE_LOCK', 'Keep the CPU briefly awake to deliver a scheduled reminder.'],
        ['VIBRATE', 'Vibrate the device for reminder notifications.'],
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <title>Privacy Policy — {{ $appName }}</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/prescientia-logo-square.png') }}">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --cream:#FFFBEB; --cream-dark:#FEF3C7; --gold:#F59E0B; --gold-dark:#D97706;
            --gold-light:#FEF9EE; --gold-soft:#FDE68A; --ink:#1F2937; --ink-soft:#374151;
            --muted:#6B7280; --border:#FDE68A; --border-soft:#E5E7EB; --white:#FFFFFF;
            --shadow:rgba(245,158,11,0.12); --shadow-lg:rgba(0,0,0,0.08);
        }
        html { scroll-behavior: smooth; }
        body {
            font-family:"Segoe UI", system-ui, -apple-system, sans-serif;
            background:var(--cream); color:var(--ink); line-height:1.65;
            min-height:100vh; display:flex; flex-direction:column;
        }
        .pp-nav {
            position:sticky; top:0; z-index:90; background:rgba(255,251,235,0.92);
            backdrop-filter:blur(12px); border-bottom:1px solid var(--border); padding:0 24px;
        }
        .pp-nav__inner {
            max-width:880px; margin:0 auto; display:flex; align-items:center;
            justify-content:space-between; height:72px; gap:14px;
        }
        .pp-nav__brand { display:flex; align-items:center; gap:12px; text-decoration:none; }
        .pp-nav__brand img { width:42px; height:42px; border-radius:9px; }
        .pp-nav__brand-name { font-weight:700; font-size:1.05rem; color:var(--ink); line-height:1.2; }
        .pp-nav__brand-label {
            font-size:.6rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase;
            color:var(--gold-dark);
        }
        .pp-hero {
            background:linear-gradient(135deg,#FFFBEB 0%,#FEF3C7 60%,#FDE68A22 100%);
            border-bottom:1px solid var(--border); padding:48px 24px 40px; text-align:center;
        }
        .pp-hero__inner { max-width:760px; margin:0 auto; }
        .pp-hero__badge {
            display:inline-flex; align-items:center; gap:7px; background:var(--white);
            border:1.5px solid var(--border); border-radius:20px; padding:5px 14px;
            font-size:.78rem; font-weight:600; color:var(--gold-dark); margin-bottom:18px;
            box-shadow:0 2px 8px var(--shadow);
        }
        .pp-hero__title {
            font-size:clamp(1.5rem,4vw,2.2rem); font-weight:800; color:var(--ink);
            line-height:1.2; margin-bottom:12px;
        }
        .pp-hero__title span { color:var(--gold-dark); }
        .pp-hero__meta { font-size:.82rem; color:var(--muted); }
        .pp-wrap { flex:1; max-width:840px; width:100%; margin:0 auto; padding:40px 24px 72px; }
        .pp-card {
            background:var(--white); border:1px solid var(--border-soft); border-radius:16px;
            box-shadow:0 2px 16px var(--shadow-lg); padding:8px 28px 28px;
        }
        .pp-section { padding-top:28px; scroll-margin-top:88px; }
        .pp-section h2 {
            font-size:1.12rem; font-weight:800; color:var(--ink);
            display:flex; align-items:center; gap:12px; margin-bottom:6px;
        }
        .pp-section h2 .pp-num {
            width:30px; height:30px; flex-shrink:0; border-radius:9px; color:#fff;
            background:linear-gradient(135deg,var(--gold),var(--gold-dark));
            display:flex; align-items:center; justify-content:center;
            font-size:.82rem; font-weight:800; box-shadow:0 3px 10px rgba(245,158,11,.35);
        }
        .pp-divider {
            height:2px; margin:10px 0 14px;
            background:linear-gradient(90deg,var(--gold) 0%,var(--gold-soft) 30%,transparent 100%);
            border-radius:2px;
        }
        .pp-section p { color:var(--ink-soft); font-size:.92rem; margin-bottom:12px; }
        .pp-section ul { list-style:none; display:flex; flex-direction:column; gap:10px; margin:6px 0 14px; }
        .pp-section li { position:relative; padding-left:24px; color:var(--ink-soft); font-size:.92rem; }
        .pp-section li::before {
            content:''; position:absolute; left:6px; top:.55em; width:7px; height:7px;
            border-radius:50%; background:var(--gold);
        }
        .pp-section li strong { color:var(--ink); }
        .pp-callout {
            background:var(--gold-light); border:1px solid var(--gold-soft); border-left:3px solid var(--gold);
            border-radius:10px; padding:14px 16px; font-size:.88rem; color:var(--ink-soft); margin:6px 0 4px;
        }
        .pp-steps { counter-reset:step; list-style:none; display:flex; flex-direction:column; gap:12px; margin:6px 0 4px; }
        .pp-steps li {
            position:relative; padding-left:42px; color:var(--ink-soft); font-size:.92rem; min-height:30px;
        }
        .pp-steps li::before {
            counter-increment:step; content:counter(step);
            position:absolute; left:0; top:0; width:28px; height:28px; border-radius:50%;
            background:var(--cream-dark); color:var(--gold-dark); font-weight:800; font-size:.8rem;
            display:flex; align-items:center; justify-content:center;
        }
        .pp-perm { width:100%; border-collapse:collapse; margin:8px 0 4px; font-size:.86rem; }
        .pp-perm th, .pp-perm td { text-align:left; padding:10px 12px; border-bottom:1px solid var(--border-soft); vertical-align:top; }
        .pp-perm th { color:var(--gold-dark); font-size:.72rem; text-transform:uppercase; letter-spacing:.05em; }
        .pp-perm td:first-child { font-family:ui-monospace, "Cascadia Code", Consolas, monospace; font-size:.8rem; color:var(--ink); white-space:nowrap; }
        .pp-perm tr:last-child td { border-bottom:none; }
        code { font-family:ui-monospace, Consolas, monospace; background:var(--cream-dark); color:var(--gold-dark); padding:1px 6px; border-radius:5px; font-size:.85em; }
        .pp-contact a { color:var(--gold-dark); font-weight:600; text-decoration:none; }
        .pp-contact a:hover { text-decoration:underline; }
        .pp-footer {
            background:var(--ink); color:rgba(255,255,255,.6); text-align:center;
            padding:26px 24px; font-size:.8rem; margin-top:auto;
        }
        .pp-footer a { color:var(--gold-soft); text-decoration:none; }
        .pp-footer a:hover { color:var(--gold); }
        @media (max-width:600px) {
            .pp-hero { padding:36px 18px 30px; }
            .pp-wrap { padding:28px 16px 56px; }
            .pp-card { padding:6px 16px 20px; }
            .pp-perm td:first-child { white-space:normal; }
        }
    </style>
</head>
<body>

<nav class="pp-nav">
    <div class="pp-nav__inner">
        <a href="{{ url('/') }}" class="pp-nav__brand">
            <img src="{{ asset('storage/prescientia-logo-square.png') }}" alt="Prescientia" onerror="this.style.display='none'">
            <div>
                <div class="pp-nav__brand-name">Prescientia</div>
                <div class="pp-nav__brand-label">Privacy Policy</div>
            </div>
        </a>
    </div>
</nav>

<section class="pp-hero">
    <div class="pp-hero__inner">
        <div class="pp-hero__badge">🔒 Official Document</div>
        <h1 class="pp-hero__title">Privacy Policy — <span>{{ $appName }}</span></h1>
        <p class="pp-hero__meta">Effective {{ $effective }} · Operated by {{ $schoolName }}</p>
    </div>
</section>

<div class="pp-wrap">
    <div class="pp-card">

        <section class="pp-section" id="introduction">
            <h2><span class="pp-num">1</span> Introduction</h2>
            <div class="pp-divider"></div>
            <p>
                This Privacy Policy explains how the <strong>{{ $appName }}</strong> mobile application
                (locally titled "{{ $appNameId }}", hereinafter the "App"), operated by
                <strong>{{ $schoolName }}</strong>, collects, uses, stores, and protects your data as a
                {{ $role }}. The App is a WiFi-based attendance (presence) system used within the school
                premises. By using the App, you agree to the practices described in this policy.
            </p>
            <p>
                The App is built with Flutter (Dart) for Android. All data processing happens directly
                between your device and the school's own backend server over an encrypted (HTTPS)
                connection. The App contains <strong>no third-party advertising or analytics SDKs</strong>.
            </p>
        </section>

        <section class="pp-section" id="data">
            <h2><span class="pp-num">2</span> Data We Collect</h2>
            <div class="pp-divider"></div>
            <p>The App collects the following data, solely for school attendance purposes:</p>
            <ul>
                <li><strong>Account &amp; identity data:</strong> name,
                    {{ $isSiswa ? 'Student ID Number (NIS)' : 'employee/teacher identifier (NIP)' }},
                    email, phone number, address, profile photo, and class assignment. This data is
                    registered by the school; the App reads it after you log in.</li>

                <li><strong>WiFi scan results:</strong> the hardware identifier of nearby WiFi access
                    points (<strong>BSSID</strong> / MAC address) and the network name (<strong>SSID</strong>),
                    obtained through an on-demand WiFi scan (the <code>wifi_scan</code> plugin). Only the
                    BSSID is used to validate that you are physically present at school; the SSID is for
                    display only. <strong>Validation is BSSID-only</strong> because an SSID (network name)
                    can be trivially cloned, whereas an access point's MAC address cannot.</li>

                @if($isSiswa)
                <li><strong>GPS coordinates (attendance moment only):</strong> when you submit
                    attendance, the App reads your device's current latitude, longitude, and accuracy
                    (via the <code>geolocator</code> plugin) <strong>one time</strong> and sends them to
                    the backend as a secondary location signal. The App does <strong>not</strong> track
                    your location continuously and does not record your location at any other time.</li>
                @else
                <li><strong>Location:</strong> the Teacher App does <strong>not</strong> read or transmit
                    GPS coordinates. Location permission is requested only because Android requires it to
                    perform WiFi scans, and the App merely checks that the device's location service is
                    turned on.</li>
                @endif

                <li><strong>Device information &amp; device binding:</strong> device model, manufacturer,
                    operating-system version, and a stable device identifier. The App derives a
                    <strong>device fingerprint</strong> by hashing these values with <strong>SHA-256</strong>
                    (via <code>device_info_plus</code> + <code>crypto</code>) and binds your account to a
                    single device to prevent attendance fraud (e.g. asking a friend to check in for you).</li>

                <li><strong>Attendance &amp; activity records:</strong> check-in and check-out times and
                    attendance status@if(!$isSiswa), plus teaching/class-period records@endif.</li>
            </ul>
            <div class="pp-callout">
                The App does <strong>not</strong> collect your contacts, photo gallery, messages, call
                logs, browsing history,
                @if($isSiswa) camera, @endif
                or any sensitive data beyond what is listed above.
            </div>
        </section>

        <section class="pp-section" id="how-it-works">
            <h2><span class="pp-num">3</span> How the App Works (Technical Flow)</h2>
            <div class="pp-divider"></div>
            <p>Understanding the flow makes it clear when and why each piece of data is used:</p>
            <ol class="pp-steps">
                <li><strong>Login.</strong> You sign in with credentials issued by the school. The
                    backend returns a signed session token (JWT), which the App stores
                    <strong>encrypted</strong> on the device (Android Keystore-backed
                    <code>EncryptedSharedPreferences</code> via <code>flutter_secure_storage</code>).</li>
                <li><strong>Loading school networks.</strong> The App fetches the list of registered
                    school WiFi access points (their BSSIDs) from the backend over HTTPS.</li>
                <li><strong>Attendance.</strong> When you check in or out, the App performs an on-demand
                    WiFi scan and compares the detected BSSIDs against the registered list. Attendance is
                    accepted only if a registered BSSID is detected.
                    @if($isSiswa) The Student App additionally reads your GPS coordinates once at this
                    moment and includes them in the submission. @endif</li>
                <li><strong>Submission.</strong> The attendance record (and the device fingerprint) is
                    sent to the backend over HTTPS, authenticated with your session token.</li>
                <li><strong>Reminders.</strong> The App schedules local notifications
                    (<code>flutter_local_notifications</code> + <code>WorkManager</code>) to remind you
                    of @if($isSiswa) attendance times @else class periods and attendance times @endif.
                    These run on-device and are re-registered after a reboot.</li>
            </ol>
        </section>

        <section class="pp-section" id="permissions">
            <h2><span class="pp-num">4</span> Android Permissions and Why They Are Needed</h2>
            <div class="pp-divider"></div>
            <p>The {{ $appName }} App declares the following Android permissions:</p>
            <table class="pp-perm">
                <thead><tr><th>Permission</th><th>Reason</th></tr></thead>
                <tbody>
                @foreach($permissions as $perm)
                    <tr><td>{{ $perm[0] }}</td><td>{{ $perm[1] }}</td></tr>
                @endforeach
                </tbody>
            </table>
            <div class="pp-callout">
                <strong>About location:</strong> Android treats WiFi scanning as location-sensitive, so
                an app cannot scan WiFi without location permission. That is the primary reason this App
                requests location.
                @if($isSiswa) The Student App also captures GPS coordinates, but only once at the instant
                you submit attendance — never as continuous tracking. @else The Teacher App does not
                capture GPS coordinates at all. @endif
            </div>
        </section>

        <section class="pp-section" id="purpose">
            <h2><span class="pp-num">5</span> How We Use Your Data</h2>
            <div class="pp-divider"></div>
            <ul>
                <li>Authenticate (verify) your identity when you sign in.</li>
                <li>Validate attendance based on the school's WiFi access points
                    @if($isSiswa) and, as a secondary check, GPS coordinates @endif.</li>
                <li>Bind your account to one device to prevent attendance fraud.</li>
                <li>Send on-device reminder notifications.</li>
                <li>Produce attendance summaries and reports for the school.</li>
            </ul>
        </section>

        <section class="pp-section" id="storage">
            <h2><span class="pp-num">6</span> Data Storage and Security</h2>
            <div class="pp-divider"></div>
            <ul>
                <li>The session token (JWT) is stored <strong>encrypted</strong> on your device using
                    the Android Keystore-backed secure storage; it is never stored in plain text.</li>
                <li>Identity and attendance data are stored on the school's official backend server.</li>
                <li>All communication between the App and the server uses an encrypted
                    <strong>HTTPS</strong> connection.</li>
                <li>Logging out clears your session data from the device.</li>
            </ul>
        </section>

        <section class="pp-section" id="third-party">
            <h2><span class="pp-num">7</span> Third-Party Libraries</h2>
            <div class="pp-divider"></div>
            <p>
                The App uses open-source Flutter plugins to access device capabilities locally:
                <code>wifi_scan</code> (WiFi scanning), <code>geolocator</code> (location services),
                <code>device_info_plus</code> (device metadata), <code>flutter_secure_storage</code>
                (encrypted storage), <code>flutter_local_notifications</code> and <code>workmanager</code>
                (reminders). These libraries run on your device and do not send your data to any party
                other than the school's backend. The App integrates <strong>no advertising networks and
                no third-party analytics or tracking SDKs</strong>.
            </p>
        </section>

        <section class="pp-section" id="sharing">
            <h2><span class="pp-num">8</span> Data Sharing</h2>
            <div class="pp-divider"></div>
            <p>
                We do <strong>not</strong> sell and do <strong>not</strong> share your data with third
                parties for commercial or advertising purposes. Data is accessible only to authorized
                school staff (e.g. homeroom teachers, teachers, and administrators) for attendance
                administration.
            </p>
        </section>

        <section class="pp-section" id="retention">
            <h2><span class="pp-num">9</span> Data Retention</h2>
            <div class="pp-divider"></div>
            <p>
                Data is retained while you are an active {{ $role }} at the school. It is deleted or
                deactivated when your account is removed by the school or after you are
                {{ $isSiswa ? 'no longer enrolled (e.g. graduation)' : 'no longer employed' }}, in line
                with the school's administrative policy.
            </p>
        </section>

        <section class="pp-section" id="rights">
            <h2><span class="pp-num">10</span> Your Rights</h2>
            <div class="pp-divider"></div>
            <p>
                You have the right to access and request correction of your personal data. Because the
                data is managed by the school, requests to access, correct, or delete your data should
                be submitted through the school administration.
            </p>
        </section>

        @if($isSiswa)
        <section class="pp-section" id="minors">
            <h2><span class="pp-num">11</span> Minors</h2>
            <div class="pp-divider"></div>
            <p>
                The App is intended for an educational environment and may be used by students under 18
                years old under the school's supervision. Accounts are created by the school, and data
                use is strictly limited to educational attendance purposes.
            </p>
        </section>
        @endif

        <section class="pp-section" id="changes">
            <h2><span class="pp-num">{{ $isSiswa ? 12 : 11 }}</span> Changes to This Policy</h2>
            <div class="pp-divider"></div>
            <p>
                This Privacy Policy may be updated from time to time. Changes will be published on this
                page with an updated effective date at the top.
            </p>
        </section>

        <section class="pp-section pp-contact" id="contact">
            <h2><span class="pp-num">{{ $isSiswa ? 13 : 12 }}</span> Contact</h2>
            <div class="pp-divider"></div>
            <p>
                For questions about this Privacy Policy, please contact {{ $schoolName }} by email at
                <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>.
            </p>
        </section>

    </div>
</div>

<footer class="pp-footer">
    &copy; {{ date('Y') }} {{ $schoolName }} · Prescientia ·
    <a href="{{ route('privacy-policy.show', $isSiswa ? 'guru' : 'siswa') }}">
        {{ $isSiswa ? 'Teacher' : 'Student' }} Privacy Policy
    </a>
</footer>

</body>
</html>
