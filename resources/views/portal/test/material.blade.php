@extends('layouts.portal')

@section('title', 'Materi — ' . $trainingModule->name)

@section('content')
<a href="{{ route('portal.index') }}" class="text-decoration-none small">&larr; Kembali ke daftar training</a>

<div class="content-card mt-3">
    <div class="content-card-header">
        <span class="badge bg-primary">Tahap 2 dari 3</span> Materi — {{ $trainingModule->name }}
    </div>
    <div class="content-card-body">
        <div class="alert alert-success py-2">
            Pre-test selesai (skor: {{ $progress->pretest_score }}). Silakan baca/download materi
            di bawah ini sebelum lanjut ke post-test.
        </div>

        @if($trainingModule->materials->isEmpty())
            <p class="text-muted">Belum ada materi diupload untuk modul ini. Hubungi HRD.</p>
        @else
            <ul class="list-unstyled mb-4">
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

        <form action="{{ route('portal.modules.material-confirm', $trainingModule) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary">Saya Sudah Membaca Materi, Lanjut ke Post-Test</button>
        </form>
    </div>
</div>
@endsection
