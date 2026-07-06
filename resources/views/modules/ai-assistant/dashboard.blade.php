@extends('layouts.admin')

@section('title', 'AI Executive Copilot')
@section('page-title', 'AI Executive Copilot')

@section('content')
<div class="exec-dashboard">
    {{-- Top Hero Section --}}
    <div class="exec-hero" id="execHero">
        <div class="exec-hero-content">
            <div class="exec-hero-left">
                <div class="exec-hero-badge">
                    <i class="ti ti-sparkles"></i>
                    AI Executive Copilot
                </div>
                <h1 class="exec-hero-greeting" id="heroGreeting">Good Morning</h1>
                <p class="exec-hero-subtitle">Your AI-powered school operations center</p>
            </div>
            <div class="exec-hero-right">
                <div class="exec-health-card" id="healthCard">
                    <div class="exec-health-ring">
                        <svg viewBox="0 0 120 120" class="exec-health-svg">
                            <circle cx="60" cy="60" r="54" class="exec-health-bg"/>
                            <circle cx="60" cy="60" r="54" class="exec-health-fill" id="healthRing"/>
                        </svg>
                        <div class="exec-health-value" id="healthValue">--</div>
                    </div>
                    <div class="exec-health-info">
                        <span class="exec-health-label">School Health Score</span>
                        <span class="exec-health-status" id="healthStatus">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="exec-hero-particles"></div>
    </div>

    {{-- Today's Snapshot --}}
    <div class="exec-section">
        <div class="exec-section-header">
            <h2 class="exec-section-title">
                <i class="ti ti-chart-grid"></i>
                Today's Snapshot
            </h2>
            <span class="exec-section-badge" id="snapshotTime">--</span>
        </div>
        <div class="exec-kpi-grid" id="kpiGrid">
            {{-- KPI Cards will be inserted here --}}
            <div class="exec-kpi-skeleton">
                <div class="exec-skeleton-icon"></div>
                <div class="exec-skeleton-value"></div>
                <div class="exec-skeleton-label"></div>
            </div>
            <div class="exec-kpi-skeleton">
                <div class="exec-skeleton-icon"></div>
                <div class="exec-skeleton-value"></div>
                <div class="exec-skeleton-label"></div>
            </div>
            <div class="exec-kpi-skeleton">
                <div class="exec-skeleton-icon"></div>
                <div class="exec-skeleton-value"></div>
                <div class="exec-skeleton-label"></div>
            </div>
            <div class="exec-kpi-skeleton">
                <div class="exec-skeleton-icon"></div>
                <div class="exec-skeleton-value"></div>
                <div class="exec-skeleton-label"></div>
            </div>
            <div class="exec-kpi-skeleton">
                <div class="exec-skeleton-icon"></div>
                <div class="exec-skeleton-value"></div>
                <div class="exec-skeleton-label"></div>
            </div>
            <div class="exec-kpi-skeleton">
                <div class="exec-skeleton-icon"></div>
                <div class="exec-skeleton-value"></div>
                <div class="exec-skeleton-label"></div>
            </div>
            <div class="exec-kpi-skeleton">
                <div class="exec-skeleton-icon"></div>
                <div class="exec-skeleton-value"></div>
                <div class="exec-skeleton-label"></div>
            </div>
        </div>
    </div>

    {{-- Suggested Questions --}}
    <div class="exec-section">
        <div class="exec-section-header">
            <h2 class="exec-section-title">
                <i class="ti ti-lightbulb"></i>
                Suggested Questions
            </h2>
        </div>
        <div class="exec-suggestions" id="suggestionsGrid">
            <button type="button" class="exec-suggestion-chip" data-question="How is my school today?">
                <i class="ti ti-dashboard"></i>
                <span>How is my school today?</span>
            </button>
            <button type="button" class="exec-suggestion-chip" data-question="Today's attendance">
                <i class="ti ti-users"></i>
                <span>Today's attendance</span>
            </button>
            <button type="button" class="exec-suggestion-chip" data-question="Outstanding fees">
                <i class="ti ti-cash"></i>
                <span>Outstanding fees</span>
            </button>
            <button type="button" class="exec-suggestion-chip" data-question="Transport status">
                <i class="ti ti-bus"></i>
                <span>Transport status</span>
            </button>
            <button type="button" class="exec-suggestion-chip" data-question="Exam summary">
                <i class="ti ti-file-text"></i>
                <span>Exam summary</span>
            </button>
            <button type="button" class="exec-suggestion-chip" data-question="Homework pending">
                <i class="ti ti-notebook"></i>
                <span>Homework pending</span>
            </button>
            <button type="button" class="exec-suggestion-chip" data-question="Payroll summary">
                <i class="ti ti-wallet"></i>
                <span>Payroll summary</span>
            </button>
        </div>
    </div>

    {{-- Chat Input --}}
    <div class="exec-section">
        <div class="exec-chat-input-wrapper">
            <div class="exec-chat-input" id="chatInputContainer">
                <div class="exec-chat-input-inner">
                    <textarea 
                        id="execQuestion" 
                        class="exec-chat-textarea" 
                        placeholder="Ask anything about your school..."
                        rows="1"
                        maxlength="500"
                    ></textarea>
                    <div class="exec-chat-actions">
                        <span class="exec-char-count" id="charCount">0/500</span>
                        <button type="button" class="exec-mic-btn" id="micBtn" title="Voice input (coming soon)">
                            <i class="ti ti-microphone"></i>
                        </button>
                        <button type="button" class="exec-send-btn" id="sendBtn" disabled>
                            <i class="ti ti-send"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Conversation History --}}
    <div class="exec-section" id="conversationSection" style="display: none;">
        <div class="exec-section-header">
            <h2 class="exec-section-title">
                <i class="ti ti-message"></i>
                Conversation
            </h2>
            <button type="button" class="exec-clear-btn" id="clearChat">
                <i class="ti ti-trash"></i>
                Clear
            </button>
        </div>
        <div class="exec-conversation" id="conversation">
            {{-- Chat messages will be inserted here --}}
        </div>
    </div>

    {{-- Typing Indicator --}}
    <div class="exec-typing" id="typingIndicator" style="display: none;">
        <div class="exec-typing-avatar">
            <i class="ti ti-sparkles"></i>
        </div>
        <div class="exec-typing-content">
            <div class="exec-typing-dots">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <span class="exec-typing-label">Analyzing your request...</span>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* ============================================================
   AI EXECUTIVE DASHBOARD — Premium Ops Center
   ============================================================ */

/* --- Dashboard Container --- */
.exec-dashboard {
    max-width: 1200px;
    margin: 0 auto;
    padding: 1.5rem;
}

/* --- Hero Section --- */
.exec-hero {
    position: relative;
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
    border-radius: 1.5rem;
    padding: 2.5rem;
    margin-bottom: 2rem;
    overflow: hidden;
    isolation: isolate;
}

.exec-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at 20% 30%, rgba(37,99,235,.25) 0%, transparent 50%),
        radial-gradient(circle at 80% 70%, rgba(14,165,233,.15) 0%, transparent 50%),
        radial-gradient(circle at 50% 50%, rgba(99,102,241,.1) 0%, transparent 60%);
    z-index: 0;
    pointer-events: none;
}

.exec-hero-content {
    position: relative;
    z-index: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 2rem;
}

.exec-hero-left {
    flex: 1;
}

.exec-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.4rem 1rem;
    border-radius: 999px;
    background: rgba(255,255,255,.08);
    color: rgba(255,255,255,.8);
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 0.02em;
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,.08);
    margin-bottom: 1rem;
}

.exec-hero-badge i {
    font-size: 1rem;
    color: #60a5fa;
}

.exec-hero-greeting {
    font-size: 2rem;
    font-weight: 700;
    letter-spacing: -0.03em;
    color: #fff;
    margin: 0 0 0.5rem;
    animation: fadeInUp 0.6s ease;
}

.exec-hero-subtitle {
    color: rgba(255,255,255,.6);
    font-size: 1rem;
    margin: 0;
    animation: fadeInUp 0.6s ease 0.1s both;
}

/* --- Health Score Card --- */
.exec-hero-right {
    flex-shrink: 0;
}

.exec-health-card {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 1.25rem;
    padding: 1.25rem 1.5rem;
    backdrop-filter: blur(12px);
}

.exec-health-ring {
    position: relative;
    width: 80px;
    height: 80px;
}

.exec-health-svg {
    transform: rotate(-90deg);
    width: 100%;
    height: 100%;
}

.exec-health-bg {
    fill: none;
    stroke: rgba(255,255,255,.1);
    stroke-width: 8;
}

.exec-health-fill {
    fill: none;
    stroke: #22c55e;
    stroke-width: 8;
    stroke-linecap: round;
    stroke-dasharray: 339.292;
    stroke-dashoffset: 339.292;
    transition: stroke-dashoffset 1.5s ease, stroke 0.3s ease;
}

.exec-health-value {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
    color: #fff;
}

.exec-health-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.exec-health-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: rgba(255,255,255,.9);
}

.exec-health-status {
    font-size: 0.75rem;
    font-weight: 500;
    color: #22c55e;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* --- Section Styles --- */
.exec-section {
    margin-bottom: 2rem;
}

.exec-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.exec-section-title {
    font-size: 1rem;
    font-weight: 650;
    color: #0f172a;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.exec-section-title i {
    font-size: 1.1rem;
    color: var(--erp-primary, #2563eb);
}

.exec-section-badge {
    font-size: 0.72rem;
    font-weight: 600;
    color: #64748b;
    background: #f1f5f9;
    padding: 0.25rem 0.75rem;
    border-radius: 999px;
}

/* --- KPI Grid --- */
.exec-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 1rem;
}

.exec-kpi-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    padding: 1.25rem;
    transition: all 0.25s ease;
    position: relative;
    overflow: hidden;
    animation: fadeInUp 0.5s ease both;
}

.exec-kpi-card:hover {
    box-shadow: 0 8px 24px rgba(0,0,0,.06);
    transform: translateY(-2px);
}

.exec-kpi-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--kpi-color, #2563eb);
    opacity: 0;
    transition: opacity 0.25s ease;
}

.exec-kpi-card:hover::before {
    opacity: 1;
}

.exec-kpi-icon {
    width: 44px;
    height: 44px;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    margin-bottom: 1rem;
    background: var(--kpi-bg, rgba(37,99,235,.08));
    color: var(--kpi-color, #2563eb);
}

.exec-kpi-value {
    font-size: 1.75rem;
    font-weight: 700;
    line-height: 1;
    letter-spacing: -0.02em;
    color: #0f172a;
    margin-bottom: 0.25rem;
}

.exec-kpi-label {
    font-size: 0.8rem;
    font-weight: 500;
    color: #64748b;
}

.exec-kpi-sub {
    font-size: 0.7rem;
    color: #94a3b8;
    margin-top: 0.25rem;
}

/* --- KPI Skeleton --- */
.exec-kpi-skeleton {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    padding: 1.25rem;
}

.exec-skeleton-icon {
    width: 44px;
    height: 44px;
    border-radius: 0.75rem;
    background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    margin-bottom: 1rem;
}

.exec-skeleton-value {
    width: 60%;
    height: 24px;
    border-radius: 4px;
    background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    margin-bottom: 0.5rem;
}

.exec-skeleton-label {
    width: 40%;
    height: 14px;
    border-radius: 4px;
    background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

/* --- Suggestions --- */
.exec-suggestions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.exec-suggestion-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1rem;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 999px;
    font-size: 0.85rem;
    font-weight: 500;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.exec-suggestion-chip:hover {
    background: var(--erp-primary, #2563eb);
    border-color: var(--erp-primary, #2563eb);
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(37,99,235,.2);
}

.exec-suggestion-chip i {
    font-size: 1rem;
    opacity: 0.7;
}

.exec-suggestion-chip:hover i {
    opacity: 1;
}

/* --- Chat Input --- */
.exec-chat-input-wrapper {
    max-width: 800px;
    margin: 0 auto;
}

.exec-chat-input {
    background: #fff;
    border: 2px solid #e2e8f0;
    border-radius: 1.25rem;
    padding: 0.5rem;
    transition: all 0.25s ease;
}

.exec-chat-input:focus-within {
    border-color: var(--erp-primary, #2563eb);
    box-shadow: 0 0 0 4px rgba(37,99,235,.1);
}

.exec-chat-input-inner {
    display: flex;
    align-items: flex-end;
    gap: 0.5rem;
}

.exec-chat-textarea {
    flex: 1;
    border: none;
    background: transparent;
    outline: none;
    font-size: 0.95rem;
    color: #0f172a;
    resize: none;
    min-height: 24px;
    max-height: 150px;
    padding: 0.5rem 0.75rem;
    line-height: 1.5;
    font-family: inherit;
}

.exec-chat-textarea::placeholder {
    color: #94a3b8;
}

.exec-chat-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem;
}

.exec-char-count {
    font-size: 0.7rem;
    color: #94a3b8;
    white-space: nowrap;
}

.exec-mic-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: transparent;
    border-radius: 0.5rem;
    color: #94a3b8;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.exec-mic-btn:hover {
    background: #f1f5f9;
    color: #64748b;
}

.exec-send-btn {
    width: 40px;
    height: 40px;
    border: none;
    background: var(--erp-primary, #2563eb);
    border-radius: 0.625rem;
    color: #fff;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.exec-send-btn:hover:not(:disabled) {
    background: #1d4ed8;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(37,99,235,.3);
}

.exec-send-btn:disabled {
    background: #cbd5e1;
    cursor: not-allowed;
}

/* --- Conversation --- */
.exec-conversation {
    max-width: 800px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.exec-message {
    display: flex;
    gap: 0.75rem;
    animation: fadeInUp 0.3s ease both;
}

.exec-message.user {
    flex-direction: row-reverse;
}

.exec-message-avatar {
    width: 36px;
    height: 36px;
    border-radius: 0.625rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.exec-message.user .exec-message-avatar {
    background: #f1f5f9;
    color: #64748b;
}

.exec-message.ai .exec-message-avatar {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #fff;
}

.exec-message-content {
    max-width: 70%;
    padding: 1rem 1.25rem;
    border-radius: 1rem;
    font-size: 0.9rem;
    line-height: 1.6;
}

.exec-message.user .exec-message-content {
    background: var(--erp-primary, #2563eb);
    color: #fff;
    border-bottom-right-radius: 0.25rem;
}

.exec-message.ai .exec-message-content {
    background: #fff;
    border: 1px solid #e2e8f0;
    color: #0f172a;
    border-bottom-left-radius: 0.25rem;
}

/* --- AI Response Cards --- */
.exec-response-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    padding: 1rem;
    margin-top: 0.75rem;
}

.exec-response-card-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    margin-bottom: 0.5rem;
}

.exec-response-card-header i {
    color: var(--erp-primary, #2563eb);
}

.exec-response-kpi-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
}

.exec-response-kpi {
    text-align: center;
    padding: 0.75rem;
    background: #fff;
    border-radius: 0.5rem;
}

.exec-response-kpi-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #0f172a;
}

.exec-response-kpi-label {
    font-size: 0.7rem;
    color: #64748b;
    margin-top: 0.15rem;
}

/* --- Typing Indicator --- */
.exec-typing {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 0;
    animation: fadeIn 0.3s ease;
}

.exec-typing-avatar {
    width: 36px;
    height: 36px;
    border-radius: 0.625rem;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}

.exec-typing-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    padding: 0.75rem 1rem;
}

.exec-typing-dots {
    display: flex;
    gap: 4px;
}

.exec-typing-dots span {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #94a3b8;
    animation: typingBounce 1.4s infinite ease-in-out;
}

.exec-typing-dots span:nth-child(1) { animation-delay: 0s; }
.exec-typing-dots span:nth-child(2) { animation-delay: 0.2s; }
.exec-typing-dots span:nth-child(3) { animation-delay: 0.4s; }

@keyframes typingBounce {
    0%, 80%, 100% { transform: scale(0.8); opacity: 0.5; }
    40% { transform: scale(1); opacity: 1; }
}

.exec-typing-label {
    font-size: 0.8rem;
    color: #94a3b8;
}

/* --- Clear Button --- */
.exec-clear-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.35rem 0.75rem;
    background: transparent;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    font-size: 0.75rem;
    font-weight: 500;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s ease;
}

.exec-clear-btn:hover {
    background: #fef2f2;
    border-color: #fecaca;
    color: #dc2626;
}

/* --- Animations --- */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* --- Responsive --- */
@media (max-width: 768px) {
    .exec-dashboard {
        padding: 1rem;
    }

    .exec-hero {
        padding: 1.5rem;
    }

    .exec-hero-content {
        flex-direction: column;
        align-items: flex-start;
    }

    .exec-hero-greeting {
        font-size: 1.5rem;
    }

    .exec-health-card {
        width: 100%;
    }

    .exec-kpi-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .exec-message-content {
        max-width: 85%;
    }

    .exec-response-kpi-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .exec-kpi-grid {
        grid-template-columns: 1fr;
    }

    .exec-suggestions {
        gap: 0.5rem;
    }

    .exec-suggestion-chip {
        font-size: 0.8rem;
        padding: 0.5rem 0.85rem;
    }
}

/* --- Dark Mode Ready --- */
[data-bs-theme="dark"] .exec-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
}

[data-bs-theme="dark"] .exec-kpi-card,
[data-bs-theme="dark"] .exec-chat-input,
[data-bs-theme="dark"] .exec-message.ai .exec-message-content {
    background: #1e293b;
    border-color: #334155;
}

[data-bs-theme="dark"] .exec-kpi-value,
[data-bs-theme="dark"] .exec-message.ai .exec-message-content {
    color: #f1f5f9;
}

[data-bs-theme="dark"] .exec-suggestion-chip {
    background: #1e293b;
    border-color: #334155;
    color: #cbd5e1;
}

[data-bs-theme="dark"] .exec-suggestion-chip:hover {
    background: var(--erp-primary, #2563eb);
    border-color: var(--erp-primary, #2563eb);
    color: #fff;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Elements
    const questionInput = $('#execQuestion');
    const sendBtn = $('#sendBtn');
    const charCount = $('#charCount');
    const conversation = $('#conversation');
    const conversationSection = $('#conversationSection');
    const typingIndicator = $('#typingIndicator');
    const healthValue = $('#healthValue');
    const healthRing = $('#healthRing');
    const healthStatus = $('#healthStatus');
    const kpiGrid = $('#kpiGrid');
    const snapshotTime = $('#snapshotTime');
    const clearChat = $('#clearChat');
    const suggestionChips = $('.exec-suggestion-chip');

    // State
    let conversationHistory = [];
    let isProcessing = false;

    // Initialize
    init();

    function init() {
        setGreeting();
        loadDashboardData();
        setupEventListeners();
    }

    // Set greeting based on time of day
    function setGreeting() {
        const hour = new Date().getHours();
        let greeting = 'Good Morning';
        if (hour >= 12 && hour < 17) greeting = 'Good Afternoon';
        else if (hour >= 17) greeting = 'Good Evening';
        $('#heroGreeting').text(greeting);
    }

    // Load dashboard data
    function loadDashboardData() {
        // Simulate loading KPI data
        setTimeout(function() {
            updateKPIs();
            updateHealthScore(96);
            snapshotTime.text('Updated: ' + new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}));
        }, 500);
    }

    // Update KPIs
    function updateKPIs() {
        const kpis = [
            { icon: 'ti ti-users', label: 'Attendance', value: '432', sub: '96% present', color: '#22c55e', bg: 'rgba(34,197,94,.08)' },
            { icon: 'ti ti-chalkboard', label: 'Teachers', value: '28', sub: 'All active', color: '#8b5cf6', bg: 'rgba(139,92,246,.08)' },
            { icon: 'ti ti-cash', label: 'Fee Collection', value: '₹2.4L', sub: '78% collected', color: '#2563eb', bg: 'rgba(37,99,235,.08)' },
            { icon: 'ti ti-bus', label: 'Transport', value: '12', sub: 'Routes active', color: '#f59e0b', bg: 'rgba(245,158,11,.08)' },
            { icon: 'ti ti-notebook', label: 'Homework', value: '45', sub: 'Pending review', color: '#06b6d4', bg: 'rgba(6,182,212,.08)' },
            { icon: 'ti ti-file-text', label: 'Exams', value: '3', sub: 'Upcoming', color: '#ec4899', bg: 'rgba(236,72,153,.08)' },
            { icon: 'ti ti-alert-triangle', label: 'Alerts', value: '2', sub: 'Requires attention', color: '#ef4444', bg: 'rgba(239,68,68,.08)' }
        ];

        let html = '';
        kpis.forEach(function(kpi, index) {
            html += '<div class="exec-kpi-card" style="--kpi-color: ' + kpi.color + '; --kpi-bg: ' + kpi.bg + '; animation-delay: ' + (index * 0.05) + 's;">' +
                '<div class="exec-kpi-icon" style="background: ' + kpi.bg + '; color: ' + kpi.color + ';">' +
                    '<i class="' + kpi.icon + '"></i>' +
                '</div>' +
                '<div class="exec-kpi-value">' + kpi.value + '</div>' +
                '<div class="exec-kpi-label">' + kpi.label + '</div>' +
                '<div class="exec-kpi-sub">' + kpi.sub + '</div>' +
            '</div>';
        });

        kpiGrid.html(html);
    }

    // Update health score
    function updateHealthScore(score) {
        const circumference = 339.292;
        const offset = circumference - (score / 100) * circumference;

        healthRing.css('stroke-dashoffset', offset);
        healthValue.text(score);

        let color = '#22c55e';
        let status = 'Excellent';
        if (score < 60) { color = '#ef4444'; status = 'Critical'; }
        else if (score < 75) { color = '#f59e0b'; status = 'Warning'; }
        else if (score < 90) { color = '#2563eb'; status = 'Good'; }

        healthRing.css('stroke', color);
        healthStatus.text(status);
        healthStatus.css('color', color);
    }

    // Setup event listeners
    function setupEventListeners() {
        // Auto-grow textarea
        questionInput.on('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 150) + 'px';

            const len = $(this).val().length;
            charCount.text(len + '/500');
            sendBtn.prop('disabled', len === 0 || isProcessing);
        });

        // Send on Enter (Shift+Enter for newline)
        questionInput.on('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (!sendBtn.prop('disabled')) {
                    sendMessage();
                }
            }
        });

        // Send button click
        sendBtn.on('click', sendMessage);

        // Suggestion chips
        suggestionChips.on('click', function() {
            const question = $(this).data('question');
            questionInput.val(question).trigger('input');
            sendMessage();
        });

        // Clear chat
        clearChat.on('click', function() {
            conversationHistory = [];
            conversation.html('');
            conversationSection.hide();
        });

        // Mic button (placeholder)
        $('#micBtn').on('click', function() {
            App.toast?.('info', 'Voice input coming soon!');
        });
    }

    // Send message
    function sendMessage() {
        const question = questionInput.val().trim();
        if (!question || isProcessing) return;

        isProcessing = true;
        sendBtn.prop('disabled', true);

        // Add user message to conversation
        addMessage(question, 'user');

        // Clear input
        questionInput.val('').trigger('input');

        // Show typing indicator
        typingIndicator.show();
        conversationSection.show();

        // Scroll to bottom
        scrollToBottom();

        // Send request
        $.ajax({
            url: '{{ route("admin.ai.ask") }}',
            method: 'POST',
            data: {
                question: question,
                confirmed: 0,
                _token: '{{ csrf_token() }}'
            },
            success: function(res) {
                typingIndicator.hide();

                if (res.success) {
                    addMessage(res.answer, 'ai', res);
                } else {
                    addMessage(res.answer || 'Sorry, I could not process your request.', 'ai');
                }
            },
            error: function() {
                typingIndicator.hide();
                addMessage('An error occurred while processing your request. Please try again.', 'ai');
            },
            complete: function() {
                isProcessing = false;
                sendBtn.prop('disabled', questionInput.val().trim().length === 0);
                scrollToBottom();
            }
        });
    }

    // Add message to conversation
    function addMessage(content, type, data) {
        const avatar = type === 'user' 
            ? '<i class="ti ti-user"></i>' 
            : '<i class="ti ti-sparkles"></i>';

        let messageContent = formatMessage(content);

        // Add response cards for AI messages
        if (type === 'ai' && data) {
            messageContent += buildResponseCards(data);
        }

        const html = '<div class="exec-message ' + type + '">' +
            '<div class="exec-message-avatar">' + avatar + '</div>' +
            '<div class="exec-message-content">' + messageContent + '</div>' +
        '</div>';

        conversation.append(html);
        conversationHistory.push({ content, type, data });
    }

    // Format message with markdown-like support
    function formatMessage(text) {
        if (!text) return '';

        // Escape HTML
        let formatted = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        // Bold
        formatted = formatted.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

        // Line breaks
        formatted = formatted.replace(/\n/g, '<br>');

        return formatted;
    }

    // Build response cards
    function buildResponseCards(data) {
        let cards = '';

        // Summary card for school summary
        if (data.intent === 'school.summary' && data.summary_data) {
            cards += '<div class="exec-response-card">' +
                '<div class="exec-response-card-header">' +
                    '<i class="ti ti-chart-bar"></i> Key Metrics' +
                '</div>' +
                '<div class="exec-response-kpi-grid">' +
                    '<div class="exec-response-kpi">' +
                        '<div class="exec-response-kpi-value">' + (data.summary_data.attendance?.present_today || '--') + '</div>' +
                        '<div class="exec-response-kpi-label">Students Present</div>' +
                    '</div>' +
                    '<div class="exec-response-kpi">' +
                        '<div class="exec-response-kpi-value">₹' + formatNumber(data.summary_data.fees?.collected_today || 0) + '</div>' +
                        '<div class="exec-response-kpi-label">Fees Collected</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
        }

        // Confidence indicator
        if (data.confidence) {
            const confPct = Math.round(data.confidence * 100);
            const confLevel = confPct >= 85 ? 'high' : confPct >= 70 ? 'medium' : 'low';
            cards += '<div class="exec-response-card">' +
                '<div class="exec-response-card-header">' +
                    '<i class="ti ti-shield-check"></i> Confidence' +
                '</div>' +
                '<div class="exec-confidence">' +
                    '<div class="exec-conf-bar"><div class="exec-conf-fill ' + confLevel + '" style="width:' + confPct + '%;"></div></div>' +
                    '<span>' + confPct + '%</span>' +
                '</div>' +
            '</div>';
        }

        return cards;
    }

    // Format number with commas
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    // Scroll to bottom
    function scrollToBottom() {
        $('html, body').animate({
            scrollTop: $(document).height()
        }, 300);
    }
});
</script>
@endpush
