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

        <div class="table-responsive-wrapper">
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
</div>

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
                    <strong>{{ $loop->iteration }}. {{ $question->question_text ?: '(gambar saja)' }}</strong>
                    <form action="{{ route('training-modules.questions.destroy', [$trainingModule, $question]) }}" method="POST" onsubmit="return confirm('Hapus soal ini? Gambar yang terlampir juga akan dihapus.')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Hapus</button>
                    </form>
                </div>

                @if($question->question_image_path)
                    <img src="{{ route('questions.image', [$question, 'question']) }}"
                         class="img-fluid rounded mt-2" style="max-height: 180px;">
                @endif

                <ul class="list-unstyled mt-2 mb-0 small">
                    @foreach($question->optionsList() as $opt => $data)
                        <li class="mb-1 {{ $question->correct_option === $opt ? 'text-success fw-semibold' : '' }}">
                            {{ strtoupper($opt) }}. {{ $data['text'] }}
                            @if($question->correct_option === $opt) <i class="bi bi-check-circle-fill"></i> @endif
                            @if($data['image'])
                                <br>
                                <img src="{{ route('questions.image', [$question, 'option_'.$opt]) }}"
                                     class="img-fluid rounded mt-1" style="max-height: 100px;">
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @empty
            <p class="text-muted small">Belum ada soal.</p>
        @endforelse

        <hr>
        <h6 class="small text-primary">Tambah Soal Baru</h6>
        <p class="text-muted small">
            Pertanyaan dan tiap opsi bisa diisi <strong>teks saja, gambar saja, atau keduanya</strong> —
            minimal salah satu harus diisi. Format gambar: JPG/PNG, maksimal 5MB per gambar.
        </p>

        <form action="{{ route('training-modules.questions.store', $trainingModule) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label small">Pertanyaan</label>
                <textarea name="question_text" rows="2" class="form-control form-control-sm @error('question_text') is-invalid @enderror">{{ old('question_text') }}</textarea>
                @error('question_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <input type="file" name="question_image" accept="image/*" class="form-control form-control-sm mt-1">
                <small class="text-muted">Gambar pertanyaan (opsional)</small>
            </div>

            <div class="row g-3">
                @foreach(['a','b','c','d'] as $opt)
                    <div class="col-md-6">
                        <label class="form-label small">Opsi {{ strtoupper($opt) }}</label>
                        <input type="text" name="option_{{ $opt }}" value="{{ old('option_'.$opt) }}"
                               class="form-control form-control-sm @error('option_'.$opt) is-invalid @enderror">
                        @error('option_'.$opt) <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <input type="file" name="option_{{ $opt }}_image" accept="image/*" class="form-control form-control-sm mt-1">
                        <small class="text-muted">Gambar opsi {{ strtoupper($opt) }} (opsional)</small>
                    </div>
                @endforeach
            </div>

            <div class="mt-3" style="max-width: 220px;">
                <label class="form-label small">Jawaban Benar</label>
                <select name="correct_option" class="form-select form-select-sm" required>
                    @foreach(['a','b','c','d'] as $opt)
                        <option value="{{ $opt }}" @selected(old('correct_option') === $opt)>{{ strtoupper($opt) }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-sm btn-primary mt-3">Tambah Soal</button>
        </form>
    </div>
</div>
@endsection
