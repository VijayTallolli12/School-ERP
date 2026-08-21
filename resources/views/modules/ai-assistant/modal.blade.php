<style>
.aiw-modal .btn-close:focus, .aiw-modal .btn-close:active {
    box-shadow: none;
    outline: none;
}
.ai-chat-wrapper {
    background: #ffffff;
    border: 1px solid var(--erp-gray-200, #e5e7eb);
    border-radius: var(--erp-input-radius, 8px);
    padding: 6px 6px 6px 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    transition: all 0.2s ease;
}
.ai-chat-wrapper:focus-within {
    border-color: var(--erp-primary);
    box-shadow: 0 4px 12px rgba(119, 85, 204, 0.15), 0 0 0 4px var(--erp-primary-light);
}
.ai-chat-input {
    border: none !important;
    box-shadow: none !important;
    background: transparent !important;
    padding: 0 !important;
    font-size: 15px;
    color: var(--erp-gray-900);
}
.ai-chat-input:focus {
    box-shadow: none !important;
}
</style>
<div class="modal fade aiw-modal" id="askErpModal" tabindex="-1" aria-labelledby="askErpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content" style="border-radius: var(--erp-card-radius); border: none; box-shadow: var(--erp-card-shadow-hover);">
            <div class="modal-header border-bottom border-light p-4">
                <h5 class="modal-title fw-bold" id="askErpModalLabel">
                    <i class="ti ti-sparkles me-2" style="color:var(--erp-primary);"></i>Ask ERP
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="aiModalBody">
                <div id="aiEmptyState" class="text-center py-5 my-4">
                    <i class="ti ti-sparkles mb-3" style="font-size: 48px; color: var(--erp-primary); opacity: 0.15;"></i>
                    <h5 class="fw-semibold text-dark mb-2">How can I help you today?</h5>
                    <p class="text-muted mb-0" style="font-size: 15px;">Ask me to analyze attendance, fee collection, or summarize student data.</p>
                </div>

                <div id="aiResponseArea" class="d-none">
                    <div id="aiResponseContent"></div>
                </div>

                <div id="aiLoading" class="d-none text-center py-5">
                    <div class="spinner-border text-primary" role="status" style="width:1.5rem;height:1.5rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted" style="font-size:14px;">Querying ERP data...</p>
                </div>
            </div>
            <div class="modal-footer p-4 pt-2 border-0 bg-white">
                <div class="w-100">
                    <div class="d-flex align-items-center gap-2 ai-chat-wrapper">
                        <i class="ti ti-message text-muted" style="font-size:1.2rem;"></i>
                        <input type="text" id="aiQuestion" class="form-control ai-chat-input" placeholder="Ask about your school data..." maxlength="500" autocomplete="off">
                        <button class="btn-primary-action px-4 flex-shrink-0 m-0" type="button" id="askErpBtn" style="height: 38px;">Ask</button>
                    </div>
                </div>
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

    function escHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function sendRequest(question, confirmed) {
        $('#aiEmptyState').addClass('d-none');
        responseArea.addClass('d-none');
        loading.removeClass('d-none');
        askBtn.prop('disabled', true);

        $.ajax({
            url: '{{ route("admin.ai.ask") }}',
            method: 'POST',
            data: {
                question: question,
                confirmed: confirmed ? 1 : 0,
                _token: '{{ csrf_token() }}'
            },
            success: function (res) {
                loading.addClass('d-none');
                responseArea.removeClass('d-none');

                let html = '';

                if (res.success) {
                    const answer = res.answer || '';
                    const lines = answer.split('\n').filter(function (l) { return l.trim(); });

                    if (res.confirmation_required) {
                        // The action is persisted server-side. The Confirm/Cancel
                        // buttons send a natural reply ("Yes"/"No") which the
                        // backend resolves against the pending action — the user
                        // can also type "Sure", "No", etc. in the input.
                        html += '<div class="aiw-response-section section-analysis">' +
                            '<div class="section-label"><i class="ti ti-alert-triangle text-warning"></i> Confirmation Required</div>' +
                            '<div class="section-content">' + escHtml(answer).replace(/\n/g, '<br>') + '</div></div>';

                        html += '<div class="aiw-response-section" style="border-left:3px solid var(--erp-primary);">' +
                            '<div class="section-label text-primary"><i class="ti ti-info-circle"></i> Action Preview</div>' +
                            '<div class="section-content">' +
                            '<div class="d-flex gap-2 mt-2">' +
                            '<button type="button" class="btn btn-success btn-sm fw-semibold" id="aiConfirmBtn">' +
                            ' Confirm &amp; Send</button>' +
                            '<button type="button" class="btn btn-outline-secondary btn-sm" id="aiCancelBtn">' +
                            ' Cancel</button>' +
                            '</div></div></div>';

                    } else {
                        html += '<div class="aiw-response-section section-analysis">' +
                            '<div class="section-label"><i class="ti ti-search"></i> Analysis Summary</div>' +
                            '<div class="section-content">' + escHtml(answer).replace(/\n/g, '<br>') + '</div></div>';
                    }

                    if (lines.length > 1 && !res.confirmation_required) {
                        const findings = lines.filter(function (l) { return l.trim().startsWith('-') || l.trim().startsWith('•') || l.trim().match(/^\d+\./) || l.includes(':'); });
                        if (findings.length > 0) {
                            let fHtml = '<ul class="aiw-findings-list">';
                            findings.slice(0, 5).forEach(function (f) {
                                fHtml += '<li>' + escHtml(f.replace(/^[-•]\s*/, '').replace(/^\d+\.\s*/, '')) + '</li>';
                            });
                            fHtml += '</ul>';
                            html += '<div class="aiw-response-section section-findings">' +
                                '<div class="section-label"><i class="ti ti-list-details"></i> Key Findings</div>' +
                                fHtml + '</div>';
                        }
                    }

                    if (res.agent_recommendation && !res.confirmation_required) {
                        const rec = res.agent_recommendation;
                        let paramsStr = '';
                        if (rec.params) {
                            paramsStr = Object.entries(rec.params).map(function (kv) {
                                return encodeURIComponent(kv[0]) + '=' + encodeURIComponent(kv[1]);
                            }).join('&');
                        }
                        const href = '{{ route("admin.agents.index") }}?preselect=' + encodeURIComponent(rec.agent) + (paramsStr ? '&' + paramsStr : '');

                        html += '<div class="aiw-recommendation">' +
                            '<div class="rec-title"><i class="ti ti-robot me-1"></i> AI Recommendation</div>' +
                            '<div class="rec-agent-name">' + escHtml(rec.label) + '</div>' +
                            '<a href="' + href + '" class="btn btn-primary w-100 mt-3 fw-semibold">' +
                            '<i class="ti ti-player-play me-1"></i> Execute Agent</a>' +
                            '</div>';
                    }
                } else {
                    html = '<div class="aiw-response-section" style="border-left:3px solid var(--erp-danger);">' +
                        '<div class="section-label text-danger"><i class="ti ti-alert-circle"></i> Unable to Process</div>' +
                        '<div class="section-content">' + escHtml(res.answer).replace(/\n/g, '<br>') + '</div></div>';
                }

                responseContent.html(html);

                if (res.confirmation_required) {
                    $('#aiConfirmBtn').on('click', function () {
                        sendRequest('Yes', false);
                    });
                    $('#aiCancelBtn').on('click', function () {
                        sendRequest('No', false);
                    });
                }
                
                setTimeout(function() {
                    const modalBody = document.getElementById('aiModalBody');
                    if (modalBody) {
                        modalBody.scrollTop = modalBody.scrollHeight;
                    }
                }, 50);
            },
            error: function () {
                loading.addClass('d-none');
                responseArea.removeClass('d-none');
                responseContent.html('<div class="aiw-response-section" style="border-left:3px solid #ef4444;">' +
                    '<div class="section-label" style="color:#ef4444;"><i class="ti ti-alert-circle"></i> Error</div>' +
                    '<div class="section-content">An error occurred while processing your request.</div></div>');
            },
            complete: function () {
                askBtn.prop('disabled', false);
            }
        });
    }

    function askQuestion() {
        const question = questionInput.val().trim();
        if (!question) {
            App.toast?.('warning', 'Please enter a question.');
            return;
        }
        sendRequest(question, false);
    }

    askBtn.on('click', askQuestion);
    questionInput.on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            askQuestion();
        }
    });
});
</script>
@endpush
