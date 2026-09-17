@extends('layouts.portal')

@section('title', 'Pre-Test — ' . $trainingModule->name)

@section('content')
<a href="{{ route('portal.index') }}" class="text-decoration-none small">&larr; Kembali ke daftar training</a>

<div class="content-card mt-3">
    <div class="content-card-header">
        <span class="badge bg-primary">Tahap 1 dari 3</span> Pre-Test — {{ $trainingModule->name }}
    </div>
    <div class="content-card-body">
        <p class="text-muted">
            Jawab pertanyaan berikut SEBELUM membaca materi — ini untuk mengukur
            pemahaman awal Anda, bukan syarat lulus. Setelah ini Anda akan
            diarahkan ke materi training.
        </p>

        @if($errors->any())
            <div class="alert alert-danger">Mohon jawab semua pertanyaan sebelum submit.</div>
        @endif

        @if($deadlineAtMs)
            <div class="alert alert-warning d-flex justify-content-between align-items-center" id="timer-box">
                <span id="timer-label">Sisa waktu mengerjakan:</span>
                <strong id="countdown-display" class="fs-5">--:--</strong>
            </div>
        @endif

        <form action="{{ route('portal.modules.pretest', $trainingModule) }}" method="POST" data-test-form>
            @csrf
            @foreach($trainingModule->questions as $question)
                <div class="mb-4 pb-3 border-bottom">
                    <p class="fw-semibold">{{ $loop->iteration }}. {{ $question->question_text }}</p>

                    @if($question->question_image_path)
                        <img src="{{ route('questions.image', [$question, 'question']) }}"
                             class="img-fluid rounded mb-3" style="max-height: 300px;">
                    @endif

                    @foreach($question->optionsList() as $key => $data)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio"
                                   name="answers[{{ $question->id }}]" value="{{ $key }}"
                                   id="q{{ $question->id }}_{{ $key }}" required>
                            <label class="form-check-label" for="q{{ $question->id }}_{{ $key }}">
                                {{ strtoupper($key) }}. {{ $data['text'] }}
                                @if($data['image'])
                                    <br>
                                    <img src="{{ route('questions.image', [$question, 'option_'.$key]) }}"
                                         class="img-fluid rounded mt-1" style="max-height: 160px;">
                                @endif
                            </label>
                        </div>
                    @endforeach
                </div>
            @endforeach

            <button type="submit" class="btn btn-primary">Submit Pre-Test</button>
        </form>
    </div>
</div>

@if($deadlineAtMs)
    @include('portal.test._countdown-script', ['deadlineAtMs' => $deadlineAtMs])
@endif
@endsection
