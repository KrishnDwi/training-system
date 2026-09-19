@extends('layouts.portal')

@section('title', 'Post-Test — ' . $trainingModule->name)

@section('content')
<a href="{{ route('portal.index') }}" class="text-decoration-none small">&larr; Kembali ke daftar training</a>

<div class="content-card mt-3">
    <div class="content-card-header">
        <span class="badge bg-primary">Tahap 3 dari 3</span> Post-Test — {{ $trainingModule->name }}
    </div>
    <div class="content-card-body">
        @if($posttestAttempts->isNotEmpty())
            <div class="alert alert-warning">
                <p class="mb-2">
                    Anda sudah melakukan <strong>{{ $posttestAttempts->count() }} percobaan</strong>,
                    belum ada yang mencapai nilai minimum <strong>{{ $trainingModule->passing_score }}</strong>.
                    Silakan coba lagi di bawah ini.
                </p>
                <div class="table-responsive-wrapper">
<table class="table table-sm mb-0">
                    <thead>
                        <tr><th>Percobaan</th><th>Skor</th><th>Durasi</th><th>Waktu</th></tr>
                    </thead>
                    <tbody>
                        @foreach($posttestAttempts as $attempt)
                            <tr>
                                <td>Ke-{{ $attempt->attempt_number }}</td>
                                <td class="fw-semibold">{{ $attempt->score }}</td>
                                <td>{{ $attempt->duration ?? '-' }}</td>
                                <td>{{ $attempt->completed_at->format('d M Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
</div>
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

                    @if($question->question_image_path)
                        <img src="{{ route('questions.image', [$question, 'question']) }}"
                             class="img-fluid rounded mb-3" style="max-height: 300px;">
                    @endif

                    @foreach($question->optionsList() as $key => $data)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio"
                                   name="answers[{{ $question->id }}]" value="{{ $key }}"
                                   id="pq{{ $question->id }}_{{ $key }}" required>
                            <label class="form-check-label" for="pq{{ $question->id }}_{{ $key }}">
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

            <button type="submit" class="btn btn-primary">Submit Post-Test</button>
        </form>
    </div>
</div>

@if($deadlineAtMs)
    @include('portal.test._countdown-script', ['deadlineAtMs' => $deadlineAtMs])
@endif
@endsection
