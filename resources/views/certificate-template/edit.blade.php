@extends('layouts.app')

@section('title', 'Template Sertifikat')
@section('page-title', 'Template Sertifikat')
@section('page-subtitle', 'Sertifikat ini otomatis diberikan ke karyawan yang lulus post-test.')

@section('page-actions')
    @if($template)
        <a href="{{ route('certificate-template.preview') }}" target="_blank" class="btn btn-outline-primary">
            <i class="bi bi-eye"></i> Preview PDF
        </a>
    @endif
@endsection

@section('content')
<div class="content-card">
    <div class="content-card-body">
        @if($template)
            <div class="alert alert-info">
                Template saat ini: <strong>{{ $template->original_filename }}</strong>
                ({{ $template->page_width_mm }}mm × {{ $template->page_height_mm }}mm).
                Upload file baru di bawah kalau mau ganti gambar background — kosongkan
                kalau cuma mau ubah posisi teks.
            </div>
        @else
            <div class="alert alert-warning">
                Belum ada template sertifikat. Upload gambar background sertifikat
                (JPG/PNG, disarankan ukuran A4 landscape) untuk mengaktifkan fitur ini.
            </div>
        @endif

        <form action="{{ route('certificate-template.update') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                    <label class="form-label">Gambar Background Sertifikat {{ $template ? '' : '*' }}</label>
                    <input type="file" name="background_image" class="form-control @error('background_image') is-invalid @enderror" accept="image/*" {{ $template ? '' : 'required' }}>
                    @error('background_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label">Lebar Halaman (mm)</label>
                    <input type="number" name="page_width_mm" class="form-control" value="{{ old('page_width_mm', $template->page_width_mm ?? 297) }}" required>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label">Tinggi Halaman (mm)</label>
                    <input type="number" name="page_height_mm" class="form-control" value="{{ old('page_height_mm', $template->page_height_mm ?? 210) }}" required>
                    <small class="text-muted">Default A4 landscape (297 × 210mm)</small>
                </div>
            </div>

            <h6 class="text-primary border-bottom pb-2 mb-3">Posisi Teks Otomatis</h6>
            <p class="text-muted small">
                Koordinat dalam milimeter (mm), dihitung dari <strong>pojok kiri-atas</strong> halaman.
                Pakai tombol Preview di kanan atas untuk cek hasilnya, ulangi sampai pas — tidak apa-apa
                coba-coba beberapa kali.
            </p>

            @php
                $fieldLabels = [
                    'name' => 'Nama Karyawan',
                    'module' => 'Nama Training',
                    'date' => 'Tanggal Lulus',
                    'score' => 'Skor Post-Test',
                ];
            @endphp

            @foreach($fieldLabels as $key => $label)
                <div class="border rounded p-3 mb-3">
                    <div class="form-check mb-2">
                        <input type="hidden" name="fields[{{ $key }}][enabled]" value="0">
                        <input type="checkbox" name="fields[{{ $key }}][enabled]" value="1" class="form-check-input"
                               id="enabled_{{ $key }}" {{ $fields[$key]['enabled'] ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="enabled_{{ $key }}">{{ $label }}</label>
                    </div>
                    <div class="row g-2">
                        <div class="col-6 col-md-2">
                            <label class="form-label small">X (mm)</label>
                            <input type="number" step="0.5" name="fields[{{ $key }}][x]" class="form-control form-control-sm" value="{{ $fields[$key]['x'] }}" required>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small">Y (mm)</label>
                            <input type="number" step="0.5" name="fields[{{ $key }}][y]" class="form-control form-control-sm" value="{{ $fields[$key]['y'] }}" required>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small">Ukuran Font (pt)</label>
                            <input type="number" name="fields[{{ $key }}][font_size]" class="form-control form-control-sm" value="{{ $fields[$key]['font_size'] }}" required>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small">Warna</label>
                            <input type="color" name="fields[{{ $key }}][color]" class="form-control form-control-sm form-control-color" value="{{ $fields[$key]['color'] }}" required>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small">Rata</label>
                            <select name="fields[{{ $key }}][align]" class="form-select form-select-sm" required>
                                <option value="left" {{ $fields[$key]['align'] === 'left' ? 'selected' : '' }}>Kiri</option>
                                <option value="center" {{ $fields[$key]['align'] === 'center' ? 'selected' : '' }}>Tengah</option>
                                <option value="right" {{ $fields[$key]['align'] === 'right' ? 'selected' : '' }}>Kanan</option>
                            </select>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        @if($key === 'name') Diisi otomatis dari nama karyawan yang lulus.
                        @elseif($key === 'module') Diisi otomatis dari nama modul training.
                        @elseif($key === 'date') Diisi otomatis dari tanggal post-test dinyatakan lulus.
                        @else Diisi otomatis dari skor post-test karyawan (0-100).
                        @endif
                    </small>
                </div>
            @endforeach

            <button type="submit" class="btn btn-primary">Simpan Template</button>
        </form>
    </div>
</div>
@endsection
