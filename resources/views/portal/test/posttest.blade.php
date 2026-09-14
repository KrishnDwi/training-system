@extends('layouts.portal')

@section('title', 'Post-Test — ' . $trainingModule->name)

@section('content')
<a href="{{ route('portal.index') }}" class="text-decoration-none small">&larr; Kembali ke daftar training</a>

<div class="content-card mt-3">
    <div class="content-card-header">
        <span class="badge bg-primary">Tahap 3 dari 3</span> Post-Test — {{ $trainingModule->name }}
    </div>
    <div class="content-card-body">
        @if($progress->posttest_completed_at && !$progress->posttest_passed)
            <div class="alert alert-warning">
                Percobaan sebelumnya: skor <strong>{{ $progress->posttest_score }}</strong>,
                belum mencapai nilai minimum <strong>{{ $trainingModule->passing_score }}</strong>.
                Silakan coba lagi di bawah ini.
            </div>
        @else
            <p class="text-muted">
                Jawab pertanyaan berikut. Nilai minimum untuk lulus:
                <strong>{{ $trainingModule->passing_score }}</strong>.
            </p>
        @endif

        @if($deadlineAtMs)
            <div class="alert alert-warning d-flex justify-content-between align-items-center" id="timer-box">
                <span id="timer-label">Sisa waktu mengerjakan:</span>
                <strong id="countdown-display" class="fs-5">--:--</strong>
            </div>
        @endif

        <form action="{{ route('portal.modules.posttest', $trainingModule) }}" method="POST" data-test-form>
            @csrf
            @foreach($trainingModule->questions as $question)
                <div class="mb-4 pb-3 border-bottom">
                    <p class="fw-semibold">{{ $loop->iteration }}. {{ $question->question_text }}</p>
                    @foreach($question->optionsList() as $key => $text)
                        <div class="form-check">
                            <input class="form-check-input" type="radio"
                                   name="answers[{{ $question->id }}]" value="{{ $key }}"
                                   id="pq{{ $question->id }}_{{ $key }}" required>
                            <label class="form-check-label" for="pq{{ $question->id }}_{{ $key }}">
                                {{ strtoupper($key) }}. {{ $text }}
                            </label>
                        </div>
                    @endforeach
                </div>
            @endforeach

            <button type="submit" class="btn btn-primary">Submit Post-Test</button>
        </form>
    </div>
</div>

@if($deadlineAtMs)
    @include('portal.test._countdown-script', ['deadlineAtMs' => $deadlineAtMs])
@endif
@endsection
