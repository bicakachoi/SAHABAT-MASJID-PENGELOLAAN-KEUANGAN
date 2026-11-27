<!DOCTYPE html>
<html lang="id" data-theme="light" data-contrast="normal">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Masjid Pintar — Transparansi Keuangan Masjid')</title>
    <link rel="stylesheet" href="{{ asset('css/masjid-pintar.css') }}">
</head>
<body>
    <header class="topbar">
        <div class="container topbar-inner">
            <a href="{{ route('home') }}" class="brand">
                <span class="brand-mark" aria-hidden="true">❖</span>
                <span>Masjid Pintar</span>
            </a>

            <nav class="nav-links" aria-label="Navigasi utama">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Beranda</a>
                <a href="{{ route('public.report') }}" class="nav-link {{ request()->routeIs('public.report') ? 'active' : '' }}">Laporan Publik</a>
                <a href="{{ route('public.donate') }}" class="nav-link {{ request()->routeIs('public.donate') ? 'active' : '' }}">Berdonasi</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
                    <a href="{{ route('transactions.index') }}" class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}">Transaksi</a>
                    <a href="{{ route('balance-sheet') }}" class="nav-link {{ request()->routeIs('balance-sheet') ? 'active' : '' }}">Neraca</a>
                @endauth
            </nav>

            <div class="nav-actions">
                <button type="button" id="toggle-contrast" class="btn-icon" title="Mode Kontras Tinggi" aria-label="Mode Kontras Tinggi">Aa</button>
                <button type="button" id="toggle-theme" class="btn-icon" title="Ganti Mode Terang/Gelap" aria-label="Ganti Mode Terang/Gelap">◐</button>
                @auth
                    <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                        @csrf
                        <button type="submit" class="btn btn-secondary">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">Masuk Pengurus</a>
                @endauth
            </div>
        </div>
    </header>

    <main class="container" style="padding-top: 24px; padding-bottom: 32px;">
        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-error" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @yield('content')
    </main>

    <nav class="bottom-nav" aria-label="Navigasi seluler">
        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Beranda</a>
        @auth
            <a href="{{ route('transactions.index') }}" class="{{ request()->routeIs('transactions.index') ? 'active' : '' }}">Transaksi</a>
            <a href="{{ route('transactions.create') }}" class="bottom-nav-fab">+ Catat</a>
            <a href="{{ route('balance-sheet') }}" class="{{ request()->routeIs('balance-sheet') ? 'active' : '' }}">Neraca</a>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
        @else
            <a href="{{ route('public.report') }}" class="{{ request()->routeIs('public.report') ? 'active' : '' }}">Laporan</a>
            <a href="{{ route('public.donate') }}" class="bottom-nav-fab">Donasi</a>
            <a href="{{ route('public.donate') }}">QRIS</a>
            <a href="{{ route('login') }}">Pengurus</a>
        @endauth
    </nav>

    <script>
        (function () {
            const root = document.documentElement;
            const savedTheme = localStorage.getItem('mp_theme') || 'light';
            const savedContrast = localStorage.getItem('mp_contrast') || 'normal';
            root.setAttribute('data-theme', savedTheme);
            root.setAttribute('data-contrast', savedContrast);

            document.getElementById('toggle-theme')?.addEventListener('click', () => {
                const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                root.setAttribute('data-theme', next);
                localStorage.setItem('mp_theme', next);
            });

            document.getElementById('toggle-contrast')?.addEventListener('click', () => {
                const next = root.getAttribute('data-contrast') === 'high' ? 'normal' : 'high';
                root.setAttribute('data-contrast', next);
                localStorage.setItem('mp_contrast', next);
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
