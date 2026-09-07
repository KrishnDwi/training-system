@extends('layouts.app')

@section('title', 'Edit Modul Training')
@section('page-title', 'Edit Modul Training')
@section('page-subtitle', $trainingModule->name)

@section('content')
<div class="alert alert-info">
    <i class="bi bi-info-circle"></i>
    Perubahan di sini (misalnya status mandatory atau masa berlaku) <strong>tidak akan mengubah
    riwayat training yang sudah tercatat sebelumnya</strong> — riwayat lama tersimpan sebagai
    snapshot terpisah agar laporan historis tetap akurat.
</div>

<div class="content-card mb-3">
    <div class="content-card-body">
        <form action="{{ route('training-modules.update', $trainingModule) }}" method="POST">
            @method('PUT')
            @include('training-modules._form')
        </form>
    </div>
</div>

{{-- ===== Materi Training (diakses & didownload karyawan lewat Portal) ===== --}}
<div class="content-card">
    <div class="content-card-header d-flex justify-content-between align-items-center">
        <span>Materi Training</span>
        <span class="badge bg-secondary">{{ $trainingModule->materials->count() }} file</span>
    </div>
    <div class="content-card-body">
        <p class="text-muted small">
            File di sini bisa diakses & didownload karyawan lewat Portal Karyawan
            (perlu login). Mendukung semua jenis file (PDF, PPT, Word, video, dll), maks 50MB.
        </p>

        <table class="table table-sm mb-3">
            <thead><tr><th>Judul</th><th>Nama File</th><th>Ukuran</th><th>Diupload</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($trainingModule->materials as $material)
                    <tr>
                        <td>{{ $material->title }}</td>
                        <td>{{ $material->original_filename }}</td>
                        <td>{{ $material->formatted_size }}</td>
                        <td>{{ $material->created_at->format('d M Y') }}</td>
                        <td>
                            <form action="{{ route('training-modules.materials.destroy', [$trainingModule, $material]) }}"
                                  method="POST" onsubmit="return confirm('Hapus materi ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">Belum ada materi diupload</td></tr>
                @endforelse
            </tbody>
        </table>

        <form action="{{ route('training-modules.materials.store', $trainingModule) }}" method="POST" enctype="multipart/form-data" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-4">
                <label class="form-label small">Judul Materi</label>
                <input type="text" name="title" class="form-control form-control-sm" required placeholder="mis. Slide Presentasi Fire Safety">
            </div>
            <div class="col-md-5">
                <label class="form-label small">File</label>
                <input type="file" name="file" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-primary w-100">Upload Materi</button>
            </div>
        </form>
    </div>
</div>

{{-- ===== Bank Soal Pre-Test / Post-Test ===== --}}
<div class="content-card mt-3">
    <div class="content-card-header d-flex justify-content-between align-items-center">
        <span>Bank Soal Pre-Test / Post-Test</span>
        <span class="badge bg-secondary">{{ $trainingModule->questions->count() }} soal</span>
    </div>
    <div class="content-card-body">
        <p class="text-muted small">
            Soal yang sama dipakai untuk pre-test DAN post-test (supaya bisa mengukur
            peningkatan skor). Kalau modul ini punya minimal 1 soal, karyawan di Portal
            akan diarahkan lewat alur <strong>Pre-Test → Baca Materi → Post-Test</strong>
            sebelum training ini tercatat selesai. Kalau tidak ada soal sama sekali,
            karyawan cukup langsung bisa download materi seperti biasa (tanpa test).
        </p>

        @forelse($trainingModule->questions as $question)
            <div class="border rounded p-3 mb-2">
                <div class="d-flex justify-content-between align-items-start">
                    <strong>{{ $loop->iteration }}. {{ $question->question_text }}</strong>
                    <form action="{{ route('training-modules.questions.destroy', [$trainingModule, $question]) }}" method="POST" onsubmit="return confirm('Hapus soal ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Hapus</button>
                    </form>
                </div>
                <ul class="list-unstyled mt-2 mb-0 small">
                    @foreach(['a','b','c','d'] as $opt)
                        <li class="{{ $question->correct_option === $opt ? 'text-success fw-semibold' : '' }}">
                            {{ strtoupper($opt) }}. {{ $question->{'option_'.$opt} }}
                            @if($question->correct_option === $opt) <i class="bi bi-check-circle-fill"></i> @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @empty
            <p class="text-muted small">Belum ada soal.</p>
        @endforelse

        <hr>
        <h6 class="small text-primary">Tambah Soal Baru</h6>
        <form action="{{ route('training-modules.questions.store', $trainingModule) }}" method="POST">
            @csrf
            <div class="mb-2">
                <label class="form-label small">Pertanyaan</label>
                <textarea name="question_text" rows="2" class="form-control form-control-sm" required></textarea>
            </div>
            <div class="row g-2">
                @foreach(['a','b','c','d'] as $opt)
                    <div class="col-md-6">
                        <label class="form-label small">Opsi {{ strtoupper($opt) }}</label>
                        <input type="text" name="option_{{ $opt }}" class="form-control form-control-sm" required>
                    </div>
                @endforeach
            </div>
            <div class="mt-2" style="max-width: 220px;">
                <label class="form-label small">Jawaban Benar</label>
                <select name="correct_option" class="form-select form-select-sm" required>
                    <option value="a">A</option>
                    <option value="b">B</option>
                    <option value="c">C</option>
                    <option value="d">D</option>
                </select>
            </div>
            <button type="submit" class="btn btn-sm btn-primary mt-3">Tambah Soal</button>
        </form>
    </div>
</div>
@endsection
