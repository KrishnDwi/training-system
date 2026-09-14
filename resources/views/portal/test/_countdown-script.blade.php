@push('scripts')
<script>
(function () {
    var deadline = {{ $deadlineAtMs }};
    var display = document.getElementById('countdown-display');
    var timerBox = document.getElementById('timer-box');
    var timerLabel = document.getElementById('timer-label');
    var form = document.querySelector('form[data-test-form]');
    var submitted = false;

    function pad(n) {
        return String(n).padStart(2, '0');
    }

    function tick() {
        var remaining = deadline - Date.now();

        if (remaining <= 0) {
            display.textContent = '00:00';
            clearInterval(timer);

            if (!submitted) {
                submitted = true;
                timerBox.classList.remove('alert-warning');
                timerBox.classList.add('alert-danger');
                timerLabel.textContent = 'Waktu habis — jawaban Anda sedang dikirim otomatis...';
                // form.submit() sengaja dipakai (bukan klik tombol) karena TIDAK
                // menjalankan validasi HTML "required" — supaya jawaban yang
                // sudah diisi tetap terkirim meski belum semua pertanyaan dijawab.
                form.submit();
            }
            return;
        }

        var totalSeconds = Math.floor(remaining / 1000);
        var minutes = Math.floor(totalSeconds / 60);
        var seconds = totalSeconds % 60;
        display.textContent = pad(minutes) + ':' + pad(seconds);

        if (totalSeconds <= 60) {
            timerBox.classList.remove('alert-warning');
            timerBox.classList.add('alert-danger');
        }
    }

    tick();
    var timer = setInterval(tick, 1000);
})();
</script>
@endpush
