@extends('layouts.app')

@section('title', 'Report')
@section('page-title', 'Report Training')
@section('page-subtitle', 'Filter dan export laporan riwayat training.')

@push('styles')
<style>
    /* Filter: collapsible di HP, selalu terbuka di desktop */
    @media (min-width: 768px) {
        #filterCollapse {
            display: block !important;
            height: auto !important;
            visibility: visible !important;
        }
    }
    .report-card {
        background: #fff;
        border: 1px solid #eef0f2;
        border-radius: 12px;
        padding: 14px;
        margin-bottom: 10px;
    }
    .report-card .rc-name { font-weight: 700; color: #111827; }
    .report-card .rc-row {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        padding: 6px 0;
        border-top: 1px solid #f3f4f6;
        font-size: .85rem;
    }
    .report-card .rc-row .rc-label { color: #9ca3af; }
    .report-card .rc-row .rc-value { text-align: right; color: #111827; }
</style>
@endpush

@section('content')

{{-- ===== Filter (collapsible di HP) ===== --}}
<div class="content-card mb-3">
    <div class="content-card-body">
        <button class="btn btn-outline-secondary w-100 d-flex justify-content-between align-items-center d-md-none mb-3"
                type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false">
            <span><i class="bi bi-funnel"></i> Filter & Export</span>
            <i class="bi bi-chevron-down"></i>
        </button>

        <div class="collapse" id="filterCollapse">
            <form action="{{ route('reports.index') }}" method="GET" id="filter-form">
                <div class="row g-3">
                    <div class="col-6 col-md-2">
                        <label class="form-label">Periode Dari</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label">Periode Sampai</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label">Nama Karyawan</label>
                        <input type="text" name="employee_name" class="form-control" value="{{ request('employee_name') }}" placeholder="Cari nama...">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label">Departemen</label>
                        <select name="department_id" class="form-select">
                            <option value="">Semua</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label">Jabatan</label>
                        <input type="text" name="position" class="form-control" value="{{ request('position') }}" placeholder="Cari jabatan...">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label">Modul Training</label>
                        <select name="training_module_id" class="form-select">
                            <option value="">Semua</option>
                            @foreach($modules as $module)
                                <option value="{{ $module->id }}" @selected(request('training_module_id') == $module->id)>{{ $module->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Mandatory</label>
                        <select name="mandatory" class="form-select">
                            <option value="all" @selected(request('mandatory', 'all') === 'all')>Semua</option>
                            <option value="mandatory" @selected(request('mandatory') === 'mandatory')>Mandatory</option>
                            <option value="non_mandatory" @selected(request('mandatory') === 'non_mandatory')>Non Mandatory</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-9 d-flex flex-column flex-md-row align-items-stretch align-items-md-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Terapkan Filter
                        </button>
                        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">Reset</a>
                        <a href="{{ route('reports.export.excel', request()->query()) }}" class="btn btn-success ms-md-auto">
                            <i class="bi bi-file-earmark-excel"></i> Export Excel
                        </a>
                        <a href="{{ route('reports.export.pdf', request()->query()) }}" class="btn btn-danger">
                            <i class="bi bi-file-earmark-pdf"></i> Export PDF
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== Kartu Ringkasan ===== --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-title">Total Riwayat</div>
            <div class="stat-value">{{ $summary['total'] }}</div>
            <div class="stat-caption">Sesuai filter aktif</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card accent-red">
            <div class="stat-title">Mandatory</div>
            <div class="stat-value">{{ $summary['mandatory'] }}</div>
            <div class="stat-caption">Riwayat training mandatory</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card" style="border-color:#fde68a;">
            <div class="stat-title">Segera Perlu Diulang</div>
            <div class="stat-value" style="color:#d97706;">{{ $summary['expiring_soon'] }}</div>
            <div class="stat-caption">≤ 30 hari lagi</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card accent-red">
            <div class="stat-title">Sudah Waktunya Diulang</div>
            <div class="stat-value">{{ $summary['expired'] }}</div>
            <div class="stat-caption">Sudah lewat jadwal pengulangan</div>
        </div>
    </div>
</div>

@php
    $statusBadge = [
        'valid' => 'bg-success',
        'expiring_soon' => 'bg-warning',
        'expired' => 'bg-danger',
        'no_expiry' => 'bg-secondary',
    ];
    $statusLabel = [
        'valid' => 'Belum Waktunya',
        'expiring_soon' => 'Segera Waktunya',
        'expired' => 'Sudah Waktunya Diulang',
        'no_expiry' => 'Sekali Saja',
    ];
@endphp

{{-- ===== Tampilan HP: kartu bertumpuk (bukan tabel lebar) ===== --}}
<div class="d-md-none">
    @forelse($histories as $history)
        <div class="report-card">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <div>
                    <div class="rc-name">{{ $history->employee->name }}</div>
                    <small class="text-muted">{{ $history->employee->employee_number }} · {{ $history->employee->department->name }}</small>
                </div>
                <span class="badge {{ $statusBadge[$history->status] }}">{{ $statusLabel[$history->status] }}</span>
            </div>
            <div class="rc-row"><span class="rc-label">Training</span><span class="rc-value">{{ $history->training_name_snapshot }}</span></div>
            <div class="rc-row">
                <span class="rc-label">Mandatory</span>
                <span class="rc-value">
                    @if($history->is_mandatory_snapshot)
                        <span class="badge bg-danger">Mandatory</span>
                    @else
                        <span class="badge bg-secondary">Non Mandatory</span>
                    @endif
                </span>
            </div>
            <div class="rc-row"><span class="rc-label">Tanggal</span><span class="rc-value">{{ $history->training_date->format('d M Y') }}</span></div>
            <div class="rc-row"><span class="rc-label">Trainer</span><span class="rc-value">{{ $history->trainer_name_snapshot }}</span></div>
            <div class="rc-row"><span class="rc-label">Jadwal Ulang</span><span class="rc-value">{{ $history->expired_at?->format('d M Y') ?? '-' }}</span></div>
        </div>
    @empty
        <p class="text-muted text-center py-4">Tidak ada data sesuai filter</p>
    @endforelse
</div>

{{-- ===== Tampilan Desktop: tabel penuh ===== --}}
<div class="content-card d-none d-md-block">
    <div class="content-card-body">
        <div class="table-responsive-wrapper">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Departemen</th>
                        <th>Training</th>
                        <th>Mandatory</th>
                        <th>Tanggal</th>
                        <th>Trainer</th>
                        <th>Jadwal Ulang</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($histories as $history)
                        <tr>
                            <td>{{ $history->employee->name }}<br><small class="text-muted">{{ $history->employee->employee_number }}</small></td>
                            <td>{{ $history->employee->department->name }}</td>
                            <td>{{ $history->training_name_snapshot }}</td>
                            <td>
                                @if($history->is_mandatory_snapshot)
                                    <span class="badge bg-danger">Mandatory</span>
                                @else
                                    <span class="badge bg-secondary">Non Mandatory</span>
                                @endif
                            </td>
                            <td>{{ $history->training_date->format('d M Y') }}</td>
                            <td>{{ $history->trainer_name_snapshot }}</td>
                            <td>{{ $history->expired_at?->format('d M Y') ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $statusBadge[$history->status] }}">{{ $statusLabel[$history->status] }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">Tidak ada data sesuai filter</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $histories->links() }}
</div>
@endsection
