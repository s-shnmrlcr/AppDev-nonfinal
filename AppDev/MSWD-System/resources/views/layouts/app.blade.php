<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MSWDO Kabacan')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/branding/municipality-kabacan.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>

<body class="app-body">
    <div class="app-shell">
        @include('components.sidebar')

        <div class="main-shell">
            @include('components.navbar')

            <main class="page-content">
                @yield('content')
            </main>
        </div>
    </div>

    <div class="mobile-overlay" id="mobileOverlay"></div>
    @stack('scripts')
</body>

</html>
