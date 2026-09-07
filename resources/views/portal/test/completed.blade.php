@extends('layouts.portal')

@section('title', 'Selesai — ' . $trainingModule->name)

@section('content')
<a href="{{ route('portal.index') }}" class="text-decoration-none small">&larr; Kembali ke daftar training</a>

<div class="content-card mt-3">
    <div class="content-card-header">{{ $trainingModule->name }} — Selesai ✅</div>
    <div class="content-card-body">
        <div class="alert alert-success">
            Anda sudah menyelesaikan training ini dan tercatat otomatis di riwayat training Anda.
        </div>

        <div class="row">
            <div class="col-md-6">
                <p class="mb-1 text-muted small">Skor Pre-Test</p>
                <p class="fs-4 fw-bold">{{ $progress->pretest_score }}</p>
            </div>
            <div class="col-md-6">
                <p class="mb-1 text-muted small">Skor Post-Test</p>
                <p class="fs-4 fw-bold text-success">{{ $progress->posttest_score }}</p>
            </div>
        </div>

        @if($progress->pretest_score !== null && $progress->posttest_score !== null)
            <p class="text-muted small">
                Peningkatan skor: {{ $progress->posttest_score - $progress->pretest_score >= 0 ? '+' : '' }}{{ $progress->posttest_score - $progress->pretest_score }} poin.
            </p>
        @endif

        @if($trainingModule->validity_months)
            <p class="text-muted small mb-0">
                Training ini perlu diulang lagi dalam {{ $trainingModule->validity_months }} bulan.
            </p>
        @endif
    </div>
</div>
@endsection
