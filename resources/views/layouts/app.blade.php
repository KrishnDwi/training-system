<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#111827">
    <title>@yield('title', 'ETMS') — Employee Training Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.11/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-bg: #111827;
            --sidebar-width: 260px;
            --sidebar-text: #9ca3af;
            --sidebar-active-bg: #1f2937;
            --content-bg: #f3f4f6;
            --accent-blue: #2563eb;
            --accent-green: #16a34a;
            --accent-red: #dc2626;
            --bottom-nav-height: 64px;
        }

        * { font-family: 'Inter', sans-serif; }

        body {
            background-color: var(--content-bg);
            margin: 0;
            /* Ruang untuk bottom nav di HP + safe area iPhone */
            padding-bottom: calc(var(--bottom-nav-height) + env(safe-area-inset-bottom, 0px));
            -webkit-text-size-adjust: 100%;
        }

        /* ============================================================
           MOBILE FIRST — gaya dasar di bawah ini berlaku untuk HP.
           Media query di bagian bawah baru meng-upgrade ke desktop.
           ============================================================ */

        /* ---- Topbar mobile (sticky) ---- */
        .mobile-topbar {
            position: sticky;
            top: 0;
            z-index: 1030;
            background: var(--sidebar-bg);
            color: #fff;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            padding-top: calc(14px + env(safe-area-inset-top, 0px));
        }
        .mobile-topbar .brand {
            font-weight: 800;
            font-size: 1rem;
            line-height: 1.2;
        }
        .mobile-topbar .brand small {
            display: block;
            font-weight: 500;
            font-size: 0.7rem;
            color: #9ca3af;
        }
        .mobile-menu-btn {
            background: transparent;
            border: 0;
            color: #fff;
            font-size: 1.4rem;
            line-height: 1;
            padding: 4px 6px;
        }

        /* ---- Sidebar: jadi off-canvas drawer di HP ---- */
        .app-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            max-width: 82vw;
            background-color: var(--sidebar-bg);
            padding: 24px 16px;
            padding-top: calc(24px + env(safe-area-inset-top, 0px));
            z-index: 1045;
            transform: translateX(-100%);
            transition: transform .25s ease;
            overflow-y: auto;
        }
        .app-sidebar.open { transform: translateX(0); }

        .sidebar-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.5);
            z-index: 1040;
            display: none;
        }
        .sidebar-backdrop.show { display: block; }

        .sidebar-brand {
            color: #fff;
            font-weight: 800;
            font-size: 1.15rem;
            margin-bottom: 28px;
            padding: 0 10px;
            line-height: 1.3;
        }
        .sidebar-section-label {
            color: #6b7280;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin: 22px 10px 10px;
        }
        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--sidebar-text);
            font-weight: 500;
            font-size: 1rem;
            padding: 14px;             /* target sentuh lega di HP */
            border-radius: 10px;
            margin-bottom: 4px;
        }
        .sidebar-nav .nav-link:hover { background-color: var(--sidebar-active-bg); color: #fff; }
        .sidebar-nav .nav-link.active { background-color: #f3f4f6; color: #111827; font-weight: 600; }

        /* ---- Konten ---- */
        .app-main { min-width: 0; }
        .app-topbar { padding: 20px 16px 4px; }
        .app-topbar h1 { font-size: 1.35rem; font-weight: 800; color: #111827; margin-bottom: 4px; }
        .app-topbar p { color: #6b7280; margin-bottom: 0; font-size: .9rem; }

        /* Tombol aksi: full-width & ditumpuk di HP */
        .topbar-actions { display: flex; flex-direction: column; gap: 8px; margin-top: 12px; }
        .topbar-actions .btn { border-radius: 10px; font-weight: 600; padding: 12px 16px; width: 100%; }

        .app-content { padding: 8px 16px 32px; }

        /* ---- Kartu ---- */
        .stat-card, .content-card {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #eef0f2;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }
        .stat-card { padding: 18px; height: 100%; }
        .stat-card .stat-title { font-weight: 700; color: #111827; font-size: .9rem; margin-bottom: 10px; }
        .stat-card .stat-value { font-size: 1.8rem; font-weight: 800; color: #111827; line-height: 1; margin-bottom: 8px; }
        .stat-card .stat-caption { color: #9ca3af; font-size: .8rem; }
        .stat-card.accent-red .stat-value { color: var(--accent-red); }
        .stat-card.accent-blue .stat-value { color: var(--accent-blue); }
        .stat-card.accent-green .stat-value { color: var(--accent-green); }

        .content-card .content-card-header {
            padding: 16px 18px;
            border-bottom: 1px solid #f1f2f4;
            font-weight: 700;
            color: #111827;
            font-size: .95rem;
        }
        .content-card .content-card-body { padding: 18px; }

        /* ---- Form: cegah browser HP auto-zoom saat fokus input ---- */
        .form-control, .form-select { font-size: 16px; padding: 12px 14px; border-radius: 10px; }
        .form-control-sm, .form-select-sm { font-size: 16px; padding: 10px 12px; }
        .form-label { font-weight: 500; margin-bottom: 6px; }
        .btn { border-radius: 10px; }

        /* ---- Tabel: bisa digeser horizontal di HP ---- */
        .table-responsive-wrapper { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table { white-space: nowrap; }
        .table td, .table th { vertical-align: middle; }

        /* Tombol aksi di dalam tabel jangan dempet */
        .table .btn-sm { padding: 6px 10px; margin-bottom: 2px; }

        /* DataTables: rapikan kontrol bawaannya di layar sempit */
        .dataTables_wrapper .row > div { margin-bottom: 8px; }
        .dataTables_filter input, .dataTables_length select { font-size: 16px; }

        .btn-primary { background-color: var(--accent-blue); border-color: var(--accent-blue); }
        .btn-success { background-color: var(--accent-green); border-color: var(--accent-green); }

        /* ---- Bottom nav (HP saja) ---- */
        .bottom-nav {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            height: calc(var(--bottom-nav-height) + env(safe-area-inset-bottom, 0px));
            padding-bottom: env(safe-area-inset-bottom, 0px);
            background: #fff;
            border-top: 1px solid #e5e7eb;
            display: flex;
            z-index: 1035;
        }
        .bottom-nav a {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 3px;
            color: #9ca3af;
            text-decoration: none;
            font-size: .68rem;
            font-weight: 500;
        }
        .bottom-nav a i { font-size: 1.25rem; }
        .bottom-nav a.active { color: var(--accent-blue); }

        /* ============================================================
           DESKTOP (>= 992px) — sidebar permanen, bottom nav disembunyikan
           ============================================================ */
        @media (min-width: 992px) {
            body { padding-bottom: 0; }

            .mobile-topbar, .bottom-nav, .sidebar-backdrop { display: none !important; }

            .app-wrapper { display: flex; min-height: 100vh; }

            .app-sidebar {
                position: sticky;
                top: 0;
                height: 100vh;
                max-width: none;
                transform: none;
                padding: 28px 18px;
                flex-shrink: 0;
                transition: none;
            }

            .app-main { flex: 1; }

            .app-topbar {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                flex-wrap: wrap;
                gap: 16px;
                padding: 36px 40px 8px;
            }
            .app-topbar h1 { font-size: 1.9rem; margin-bottom: 6px; }
            .app-topbar p { font-size: 1rem; }

            .topbar-actions { flex-direction: row; margin-top: 0; }
            .topbar-actions .btn { width: auto; padding: 10px 18px; }

            .app-content { padding: 8px 40px 48px; }

            .stat-card { padding: 22px 24px; border-radius: 16px; }
            .stat-card .stat-title { font-size: 1rem; }
            .stat-card .stat-value { font-size: 2.1rem; }
            .content-card { border-radius: 16px; }
            .content-card .content-card-header { padding: 18px 24px; font-size: 1rem; }
            .content-card .content-card-body { padding: 24px; }

            .form-control, .form-select { font-size: 1rem; padding: .5rem .75rem; }
            .form-control-sm, .form-select-sm { font-size: .875rem; padding: .25rem .5rem; }

            .table { white-space: normal; }
        }
    </style>

    @stack('styles')
</head>
<body>

{{-- Topbar mobile: tombol hamburger + brand --}}
<div class="mobile-topbar">
    <button class="mobile-menu-btn" id="sidebarToggle" aria-label="Buka menu">
        <i class="bi bi-list"></i>
    </button>
    <div class="brand">Harris Hotel Seminyak<small>ETMS</small></div>
</div>

<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<div class="app-wrapper">
    <aside class="app-sidebar" id="appSidebar">
        <div class="sidebar-brand">Harris Hotel Seminyak<br><small style="font-weight:500; color:#9ca3af; font-size:0.75rem;">ETMS</small></div>

        {{-- Route::has() dipakai supaya nav tidak error sebelum semua modul selesai dibuat --}}
        <nav class="sidebar-nav">
            @if(Route::has('dashboard'))
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-grid-1x2"></i> Dashboard
                </a>
            @endif
            <a class="nav-link {{ request()->routeIs('training-modules.*') ? 'active' : '' }}" href="{{ route('training-modules.index') }}">
                <i class="bi bi-mortarboard"></i> Master Training
            </a>
            @if(Route::has('employees.index'))
                <a class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}" href="{{ route('employees.index') }}">
                    <i class="bi bi-people"></i> Data Karyawan
                </a>
            @endif
            <a class="nav-link {{ request()->routeIs('training-sessions.*') ? 'active' : '' }}" href="{{ route('training-sessions.index') }}">
                <i class="bi bi-calendar-check"></i> Training Session
            </a>
            @if(Route::has('reports.index'))
                <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                    <i class="bi bi-file-earmark-bar-graph"></i> Report
                </a>
            @endif

            @if(Route::has('certificate-template.edit'))
                <div class="sidebar-section-label">Settings</div>
                <a class="nav-link {{ request()->routeIs('certificate-template.*') ? 'active' : '' }}" href="{{ route('certificate-template.edit') }}">
                    <i class="bi bi-patch-check"></i> Template Sertifikat
                </a>
            @endif
        </nav>
    </aside>

    <div class="app-main">
        <header class="app-topbar">
            <div>
                <h1>@yield('page-title', 'ETMS')</h1>
                <p>@yield('page-subtitle')</p>
            </div>
            <div class="topbar-actions">
                @yield('page-actions')
            </div>
        </header>

        <main class="app-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show">
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

{{-- Bottom nav: 4 menu tersering, khusus HP --}}
<nav class="bottom-nav">
    @if(Route::has('dashboard'))
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2"></i> Dashboard
        </a>
    @endif
    @if(Route::has('employees.index'))
        <a href="{{ route('employees.index') }}" class="{{ request()->routeIs('employees.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Karyawan
        </a>
    @endif
    <a href="{{ route('training-sessions.index') }}" class="{{ request()->routeIs('training-sessions.*') ? 'active' : '' }}">
        <i class="bi bi-calendar-check"></i> Session
    </a>
    @if(Route::has('reports.index'))
        <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-bar-graph"></i> Report
        </a>
    @endif
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.11/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.11/js/dataTables.bootstrap5.min.js"></script>
<script>
(function () {
    var sidebar = document.getElementById('appSidebar');
    var backdrop = document.getElementById('sidebarBackdrop');
    var toggle = document.getElementById('sidebarToggle');

    function closeSidebar() {
        sidebar.classList.remove('open');
        backdrop.classList.remove('show');
    }

    toggle.addEventListener('click', function () {
        sidebar.classList.toggle('open');
        backdrop.classList.toggle('show');
    });
    backdrop.addEventListener('click', closeSidebar);

    // Tutup drawer otomatis kalau layar dilebarkan ke ukuran desktop
    window.addEventListener('resize', function () {
        if (window.innerWidth >= 992) closeSidebar();
    });
})();
</script>
@stack('scripts')
</body>
</html>
