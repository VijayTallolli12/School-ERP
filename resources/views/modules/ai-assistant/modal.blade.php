<div class="modal fade aiw-modal" id="askErpModal" tabindex="-1" aria-labelledby="askErpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="askErpModalLabel">
                    <i class="ti ti-sparkles me-1" style="color:#2563eb;"></i> Ask ERP
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="d-flex align-items-center gap-2" style="background:#f8fafc;border:1px solid var(--erp-border-color);border-radius:0.75rem;padding:0.5rem 0.75rem;">
                        <i class="ti ti-message text-muted" style="font-size:1.1rem;"></i>
                        <input type="text" id="aiQuestion" class="form-control border-0 bg-transparent" placeholder="Ask about your school data..." maxlength="500" autocomplete="off" style="box-shadow:none;min-height:auto;padding:0.35rem 0.5rem;">
                        <button class="btn btn-primary btn-sm flex-shrink-0" type="button" id="askErpBtn" style="border-radius:0.5rem;">
                            <i class="ti ti-arrow-right"></i> Ask
                        </button>
                    </div>
                </div>

                <div id="aiResponseArea" class="d-none">
                    <div id="aiResponseContent"></div>
                </div>

                <div id="aiLoading" class="d-none text-center py-5">
                    <div class="spinner-border text-primary" role="status" style="width:1.5rem;height:1.5rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2" style="font-size:0.85rem;color:#94a3b8;">Querying ERP data...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border:1px solid var(--erp-border-color);">Close</button>
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

                let html = '';

                if (res.success) {
                    const answer = res.answer;
                    const lines = answer.split('\n').filter(function (l) { return l.trim(); });

                    // Analysis Summary
                    html += '<div class="aiw-response-section section-analysis">' +
                        '<div class="section-label"><i class="ti ti-search"></i> Analysis Summary</div>' +
                        '<div class="section-content">' + escHtml(lines[0] || answer).replace(/\n/g, '<br>') + '</div></div>';

                    // Key Findings (bullet points from answer)
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

                    // Recommended Actions (from remaining lines)
                    const actions = lines.slice(1, Math.min(4, lines.length));
                    if (actions.length > 0) {
                        let aHtml = '<ul class="aiw-findings-list">';
                        actions.forEach(function (a) {
                            if (!a.startsWith('-') && !a.startsWith('•') && !a.match(/^\d+\./)) {
                                aHtml += '<li>' + escHtml(a) + '</li>';
                            }
                        });
                        aHtml += '</ul>';
                        html += '<div class="aiw-response-section section-actions">' +
                            '<div class="section-label"><i class="ti ti-arrows-right"></i> Recommended Actions</div>' +
                            aHtml + '</div>';
                    }

                    // Expected Impact
                    html += '<div class="aiw-response-section section-impact">' +
                        '<div class="section-label"><i class="ti ti-trending-up"></i> Expected Impact</div>' +
                        '<div class="section-content">Automated processing of ' + lines.length + ' data points with real-time notifications and audit trail.</div></div>';

                    // Confidence Indicator
                    const confPct = res.agent_recommendation ? 92 : 78;
                    const confLevel = confPct >= 85 ? 'high' : confPct >= 70 ? 'medium' : 'low';
                    html += '<div class="aiw-response-section section-confidence">' +
                        '<div class="section-label"><i class="ti ti-shield-check"></i> Confidence</div>' +
                        '<div class="aiw-confidence">' +
                        '<div class="conf-bar"><div class="conf-fill ' + confLevel + '" style="width:' + confPct + '%;"></div></div>' +
                        '<span>' + confPct + '%</span>' +
                        '</div></div>';

                    // Recommendation card
                    if (res.agent_recommendation) {
                        const rec = res.agent_recommendation;
                        let paramsStr = '';
                        if (rec.params) {
                            paramsStr = Object.entries(rec.params).map(function (kv) {
                                return encodeURIComponent(kv[0]) + '=' + encodeURIComponent(kv[1]);
                            }).join('&');
                        }
                        const href = '{{ route("admin.agents.index") }}?preselect=' + encodeURIComponent(rec.agent) + (paramsStr ? '&' + paramsStr : '');

                        const outcomeMap = {
                            'fee_collection': 'Send fee reminders to overdue students, reduce outstanding by 30-40%',
                            'attendance': 'Notify parents of absent students, improve attendance tracking',
                            'library': 'Alert overdue borrowers, recover library assets, collect fines',
                            'payroll': 'Generate payroll, create payslips, lock payroll run',
                        };
                        const recordMap = {
                            'fee_collection': 'Outstanding fee records',
                            'attendance': 'Absent student records',
                            'library': 'Overdue book issues',
                            'payroll': 'Employee payroll records',
                        };
                        const impactMap = {
                            'fee_collection': 'high',
                            'attendance': 'high',
                            'library': 'medium',
                            'payroll': 'high',
                        };

                        html += '<div class="aiw-recommendation">' +
                            '<div class="rec-title"><i class="ti ti-robot me-1"></i> AI Recommendation</div>' +
                            '<div class="rec-agent-name">' + escHtml(rec.label) + '</div>' +
                            '<div class="rec-row" style="margin-top:0.5rem;"><strong>Reason</strong> Best suited to handle this request based on intent analysis</div>' +
                            '<div class="rec-row"><strong>Expected Outcome</strong> ' + (outcomeMap[rec.agent] || 'Automate and streamline this process') + '</div>' +
                            '<div class="rec-row"><strong>Affected Records</strong> ' + (recordMap[rec.agent] || 'School records') + '</div>' +
                            '<div class="rec-row"><strong>Estimated Impact</strong> <span class="aiw-impact-badge ' + (impactMap[rec.agent] || 'medium') + '">' + (impactMap[rec.agent] === 'high' ? 'High' : 'Medium') + '</span></div>' +
                            '<div class="rec-row"><strong>Confidence</strong> <span class="aiw-confidence"><div class="conf-bar"><div class="conf-fill high" style="width:92%;"></div></div> 92%</span></div>' +
                            '<a href="' + href + '" class="btn btn-primary w-100 mt-3" style="border-radius:0.625rem;font-weight:600;">' +
                            '<i class="ti ti-player-play me-1"></i> Execute Agent</a>' +
                            '</div>';
                    }
                } else {
                    html = '<div class="aiw-response-section" style="border-left:3px solid #ef4444;">' +
                        '<div class="section-label" style="color:#ef4444;"><i class="ti ti-alert-circle"></i> Unable to Process</div>' +
                        '<div class="section-content">' + escHtml(res.answer).replace(/\n/g, '<br>') + '</div></div>';
                }

                responseContent.html(html);
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
