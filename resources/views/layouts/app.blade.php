<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SekolahKu Admin')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-layout.css') }}">
    @yield('css')
</head>
<body>
    <div class="admin-layout">
        @include('components.sidebar')

        <div class="admin-main-wrapper">
            @include('components.header')

            <main class="admin-content">
                @if ($message = Session::get('success'))
                    <div class="alert alert-success">
                        <strong>Berhasil!</strong> {{ $message }}
                    </div>
                @endif

                @if ($message = Session::get('error'))
                    <div class="alert alert-danger">
                        <strong>Gagal!</strong> {{ $message }}
                    </div>
                @endif

                @if ($message = Session::get('warning'))
                    <div class="alert alert-warning">
                        <strong>Perhatian!</strong> {{ $message }}
                    </div>
                @endif

                @if ($message = Session::get('info'))
                    <div class="alert alert-info">
                        <strong>Informasi!</strong> {{ $message }}
                    </div>
                @endif

                @yield('content')
            </main>

            @include('components.footer')
        </div>
    </div>

    @yield('scripts')
</body>
</html>
