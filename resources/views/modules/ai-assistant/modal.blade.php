<div class="modal fade" id="askErpModal" tabindex="-1" aria-labelledby="askErpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="askErpModalLabel">
                    <i class="ti ti-robot me-1"></i> Ask ERP
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="aiQuestion" class="form-label fw-semibold">Ask a question about your school data</label>
                    <div class="input-group">
                        <input type="text" id="aiQuestion" class="form-control" placeholder="e.g. total students, absent today, pending fees..." maxlength="500" autocomplete="off">
                        <button class="btn btn-primary" type="button" id="askErpBtn">
                            <i class="ti ti-arrow-right"></i> Ask
                        </button>
                    </div>
                </div>
                <div id="aiResponseArea" class="d-none">
                    <hr>
                    <div class="d-flex align-items-center mb-2">
                        <i class="ti ti-message text-primary me-2"></i>
                        <span class="fw-semibold">Response</span>
                        <button class="btn btn-sm btn-outline-secondary ms-auto" type="button" id="copyResponseBtn" title="Copy response">
                            <i class="ti ti-copy"></i>
                        </button>
                    </div>
                    <div id="aiResponseContent" class="p-3 bg-light rounded border" style="white-space: pre-wrap; font-size: 0.9rem; max-height: 400px; overflow-y: auto;"></div>
                </div>
                <div id="aiLoading" class="d-none text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted small">Querying ERP data...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function () {
    const questionInput = $('#aiQuestion');
    const askBtn = $('#askErpBtn');
    const responseArea = $('#aiResponseArea');
    const responseContent = $('#aiResponseContent');
    const loading = $('#aiLoading');
    const copyBtn = $('#copyResponseBtn');

    function askQuestion() {
        const question = questionInput.val().trim();
        if (!question) {
            App.toast?.('warning', 'Please enter a question.');
            return;
        }

        responseArea.addClass('d-none');
        loading.removeClass('d-none');
        askBtn.prop('disabled', true);

        $.ajax({
            url: '{{ route("admin.ai.ask") }}',
            method: 'POST',
            data: {
                question: question,
                _token: '{{ csrf_token() }}'
            },
            success: function (res) {
                loading.addClass('d-none');
                responseArea.removeClass('d-none');

                const icon = res.success ? 'text-success ti ti-circle-check' : 'text-danger ti ti-alert-circle';
                const prefix = res.success ? '' : '⚠️ ';
                responseContent.html('<span class="d-flex align-items-start gap-2"><i class="ti ' + icon + ' mt-1"></i><span>' + prefix + escapeHtml(res.answer).replace(/\n/g, '<br>') + '</span></span>');
            },
            error: function () {
                loading.addClass('d-none');
                responseArea.removeClass('d-none');
                responseContent.html('<span class="text-danger"><i class="ti ti-alert-circle me-1"></i>An error occurred while processing your request.</span>');
            },
            complete: function () {
                askBtn.prop('disabled', false);
            }
        });
    }

    askBtn.on('click', askQuestion);
    questionInput.on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            askQuestion();
        }
    });

    copyBtn.on('click', function () {
        const text = responseContent.text();
        navigator.clipboard.writeText(text).then(function () {
            App.toast?.('success', 'Response copied to clipboard.');
        }).catch(function () {
            App.toast?.('error', 'Failed to copy.');
        });
    });

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
});
</script>
@endpush
