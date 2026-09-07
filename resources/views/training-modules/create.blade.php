@extends('layouts.app')

@section('title', 'Tambah Modul Training')
@section('page-title', 'Tambah Modul Training Baru')
@section('page-subtitle', 'Buat modul training baru yang akan tampil di daftar Master Training.')

@section('content')
<div class="content-card mb-3">
    <div class="content-card-body">
        <form action="{{ route('training-modules.store') }}" method="POST" enctype="multipart/form-data">
            @include('training-modules._form', ['hideSubmit' => true])

            <hr class="my-4">
            <h6 class="text-primary mb-2">Materi Training (Opsional)</h6>
            <p class="text-muted small">
                Bisa langsung upload materi sekarang, atau nanti belakangan lewat halaman Edit.
                Bisa pilih beberapa file sekaligus — judul materi otomatis dari nama filenya
                (bisa diganti nanti di halaman Edit kalau perlu).
            </p>
            <input type="file" name="materials[]" class="form-control mb-4" multiple>

            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('training-modules.index') }}" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection
