<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#111827">
    <title>@yield('title', 'Portal Karyawan') — Harris Hotel Seminyak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }

        body {
            background-color: #f3f4f6;
            -webkit-text-size-adjust: 100%;
        }

        /* ============ MOBILE FIRST ============ */

        .navbar-portal {
            background-color: #111827;
            position: sticky;
            top: 0;
            z-index: 1030;
            padding-top: calc(.5rem + env(safe-area-inset-top, 0px));
        }
        .navbar-portal .navbar-brand {
            font-size: .95rem;
            font-weight: 700;
            white-space: normal;
            line-height: 1.25;
        }

        .page-container {
            padding: 16px;
            padding-bottom: calc(24px + env(safe-area-inset-bottom, 0px));
        }

        .content-card {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #eef0f2;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }
        .content-card .content-card-header {
            padding: 16px 18px;
            border-bottom: 1px solid #f1f2f4;
            font-weight: 700;
            color: #111827;
            font-size: .95rem;
        }
        .content-card .content-card-body { padding: 18px; }

        /* Cegah auto-zoom iOS saat fokus ke input */
        .form-control, .form-select { font-size: 16px; padding: 12px 14px; border-radius: 10px; }
        .btn { border-radius: 10px; padding: 12px 16px; font-weight: 600; }
        .btn-sm { padding: 8px 12px; font-weight: 500; }

        /* Target sentuh radio jawaban dibuat lega & mudah ditekan */
        .form-check {
            padding: 12px 12px 12px 42px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            margin-bottom: 8px;
        }
        .form-check .form-check-input {
            width: 20px;
            height: 20px;
            margin-left: -30px;
            margin-top: 2px;
        }
        .form-check .form-check-label { width: 100%; cursor: pointer; }
        .form-check:has(.form-check-input:checked) {
            border-color: #2563eb;
            background-color: #eff6ff;
        }

        /* Timer countdown menempel di atas saat scroll — biar selalu kelihatan */
        #timer-box {
            position: sticky;
            top: 56px;
            z-index: 1020;
            border-radius: 10px;
        }

        /* Tabel bisa digeser horizontal, tidak merusak layout */
        .table-responsive-wrapper { overflow-x: auto; -webkit-overflow-scrolling: touch; }

        img { max-width: 100%; height: auto; }

        /* ============ DESKTOP (>= 992px) ============ */
        @media (min-width: 992px) {
            .navbar-portal .navbar-brand { font-size: 1.15rem; }
            .page-container { padding: 24px 0; }
            .content-card { border-radius: 16px; }
            .content-card .content-card-header { padding: 18px 24px; font-size: 1rem; }
            .content-card .content-card-body { padding: 24px; }
            .form-control, .form-select { font-size: 1rem; padding: .5rem .75rem; }
            .btn { padding: .5rem 1rem; }
            #timer-box { top: 72px; }
        }
    </style>
    @stack('styles')
</head>
<body>

<nav class="navbar navbar-dark navbar-portal">
    <div class="container-fluid container-lg d-flex justify-content-between align-items-center gap-2">
        <span class="navbar-brand mb-0">Portal Karyawan<br class="d-lg-none"><span class="d-none d-lg-inline"> — </span><small class="fw-normal">Harris Hotel Seminyak</small></span>
        @auth('employee')
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <span class="text-light small d-none d-sm-inline">{{ auth('employee')->user()->name }}</span>
                <form action="{{ route('portal.logout') }}" method="POST">
                    @csrf
                    <button class="btn btn-sm btn-outline-light">Keluar</button>
                </form>
            </div>
        @endauth
    </div>
</nav>

<div class="container-fluid container-lg page-container">
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
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
