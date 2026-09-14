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

        <a href="{{ route('portal.modules.certificate', $trainingModule) }}" class="btn btn-success mb-3">
            <i class="bi bi-award"></i> Download Sertifikat
        </a>

        <div class="row">
            <div class="col-md-6">
                <p class="mb-1 text-muted small">Skor Pre-Test</p>
                <p class="fs-4 fw-bold mb-1">{{ $progress->pretest_score }}</p>
                @if($progress->pretest_duration)
                    <p class="text-muted small mb-0">Dikerjakan dalam {{ $progress->pretest_duration }}</p>
                @endif
            </div>
            <div class="col-md-6">
                <p class="mb-1 text-muted small">Skor Post-Test</p>
                <p class="fs-4 fw-bold text-success mb-1">{{ $progress->posttest_score }}</p>
                @if($progress->posttest_duration)
                    <p class="text-muted small mb-0">Dikerjakan dalam {{ $progress->posttest_duration }}</p>
                @endif
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

{{-- Materi tetap bisa diakses/didownload meski training sudah selesai —
     berguna untuk referensi ulang kapan saja. --}}
<div class="content-card mt-3">
    <div class="content-card-header">Materi Training</div>
    <div class="content-card-body">
        @if($trainingModule->materials->isEmpty())
            <p class="text-muted small mb-0">Belum ada materi diupload untuk modul ini.</p>
        @else
            <ul class="list-unstyled mb-0">
                @foreach($trainingModule->materials as $material)
                    <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <div class="fw-semibold">{{ $material->title }}</div>
                            <small class="text-muted">{{ $material->original_filename }} · {{ $material->formatted_size }}</small>
                        </div>
                        <a href="{{ route('portal.materials.download', $material) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-download"></i> Download
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
