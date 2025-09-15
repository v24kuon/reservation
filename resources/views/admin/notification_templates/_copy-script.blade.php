@push('scripts')
<script>
(function() {
    function legacyCopy(text){
        const ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
    }
    function setFeedback(feedbackEl, text) {
        if (!feedbackEl) return;
        feedbackEl.textContent = 'クリップボードにコピーしました: ' + text;
        clearTimeout(window.__copy_feedback_timer);
        window.__copy_feedback_timer = setTimeout(() => { if (feedbackEl) feedbackEl.textContent = ''; }, 3000);
    }
    function copy(text){
        const feedback = document.getElementById('copy-feedback');
        if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
            navigator.clipboard.writeText(text)
                .then(() => setFeedback(feedback, text))
                .catch(() => { legacyCopy(text); setFeedback(feedback, text); });
            return;
        }
        legacyCopy(text);
        setFeedback(feedback, text);
    }
    function moustacheFor(ph){
        const open = String.fromCharCode(123,123); // "{{"
        const close = String.fromCharCode(125,125); // "}}"
        return open + ph + close;
    }
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.copy-chip');
        if (!btn) return;
        const ph = btn.dataset.ph;
        if (!ph) return;
        copy(moustacheFor(ph));
    });
})();
</script>
@endpush
