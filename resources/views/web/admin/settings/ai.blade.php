@extends('layout.master-layout')
@section('title', 'AI Platform Settings')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">AI Platform Settings</h4>
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">AI Platform Settings</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="row">

                @include('web.admin.settings._settings-nav')

                <div class="col-lg-9 col-xxl-10">
                    <div class="card">
                        <div class="card-header d-flex align-items-center gap-2">
                            <i class="ri-robot-line fs-20 text-primary"></i>
                            <h5 class="card-title mb-0">AI Platform Configuration</h5>
                        </div>
                        <div class="card-body">

                            <div class="alert alert-info alert-borderless d-flex gap-2 mb-4">
                                <i class="ri-information-line fs-16 mt-1 flex-shrink-0"></i>
                                <span>
                                    Select the <strong>active AI provider</strong> and enter its API key.
                                    Only the enabled provider will be used by the application.
                                    Keys are stored securely in your <code>.env</code> file.
                                </span>
                            </div>

                            <form id="settingsForm">

                                {{-- Active Provider --}}
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">
                                        Active AI Provider <span class="text-danger">*</span>
                                    </label>
                                    <div class="row g-3" id="providerCards">

                                        {{-- OpenAI Card --}}
                                        <div class="col-lg-4">
                                            <label class="provider-card d-block border rounded-3 p-3 cursor-pointer
                                                {{ env('AI_PROVIDER', 'openai') === 'openai' ? 'border-primary bg-primary bg-opacity-10' : '' }}"
                                                for="provider_openai">
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <input class="form-check-input provider-radio mt-0" type="radio"
                                                        name="ai_provider" id="provider_openai" value="openai"
                                                        {{ env('AI_PROVIDER', 'openai') === 'openai' ? 'checked' : '' }}>
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M22.28 9.84a5.83 5.83 0 0 0-.5-4.79 5.9 5.9 0 0 0-6.36-2.83 5.83 5.83 0 0 0-4.39-1.96 5.9 5.9 0 0 0-5.62 4.08 5.83 5.83 0 0 0-3.9 2.83 5.9 5.9 0 0 0 .73 6.92 5.83 5.83 0 0 0 .5 4.79 5.9 5.9 0 0 0 6.36 2.83 5.83 5.83 0 0 0 4.39 1.96 5.9 5.9 0 0 0 5.63-4.09 5.83 5.83 0 0 0 3.9-2.83 5.9 5.9 0 0 0-.74-6.91z" fill="currentColor"/>
                                                    </svg>
                                                    <span class="fw-semibold">OpenAI</span>
                                                </div>
                                                <small class="text-muted">GPT-4, GPT-3.5 Turbo & more</small>
                                            </label>
                                        </div>

                                        {{-- Anthropic Card --}}
                                        <div class="col-lg-4">
                                            <label class="provider-card d-block border rounded-3 p-3 cursor-pointer
                                                {{ env('AI_PROVIDER') === 'anthropic' ? 'border-primary bg-primary bg-opacity-10' : '' }}"
                                                for="provider_anthropic">
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <input class="form-check-input provider-radio mt-0" type="radio"
                                                        name="ai_provider" id="provider_anthropic" value="anthropic"
                                                        {{ env('AI_PROVIDER') === 'anthropic' ? 'checked' : '' }}>
                                                    <i class="ri-sparkling-2-line fs-16"></i>
                                                    <span class="fw-semibold">Anthropic</span>
                                                </div>
                                                <small class="text-muted">Claude 3.5 Sonnet & more</small>
                                            </label>
                                        </div>

                                        {{-- Gemini Card --}}
                                        <div class="col-lg-4">
                                            <label class="provider-card d-block border rounded-3 p-3 cursor-pointer
                                                {{ env('AI_PROVIDER') === 'gemini' ? 'border-primary bg-primary bg-opacity-10' : '' }}"
                                                for="provider_gemini">
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <input class="form-check-input provider-radio mt-0" type="radio"
                                                        name="ai_provider" id="provider_gemini" value="gemini"
                                                        {{ env('AI_PROVIDER') === 'gemini' ? 'checked' : '' }}>
                                                    <i class="ri-google-line fs-16"></i>
                                                    <span class="fw-semibold">Google Gemini</span>
                                                </div>
                                                <small class="text-muted">Gemini 1.5 Pro & more</small>
                                            </label>
                                        </div>

                                    </div>
                                    <div class="text-danger small mt-1 field-error" id="error-ai_provider"></div>
                                </div>

                                <hr>

                                {{-- OpenAI Keys --}}
                                <div id="panel-openai" class="provider-panel {{ env('AI_PROVIDER', 'openai') !== 'openai' ? 'd-none' : '' }}">
                                    <p class="text-muted text-uppercase fw-semibold fs-12 mb-3">
                                        <i class="ri-key-line me-1"></i> OpenAI API Keys
                                    </p>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label for="openai_api_key" class="form-label">
                                                API Key <span class="text-muted fs-11">(starts with sk-)</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="ri-lock-line text-muted"></i></span>
                                                <input type="password" class="form-control font-monospace"
                                                    id="openai_api_key" name="openai_api_key"
                                                    value="{{ env('OPENAI_API_KEY') }}"
                                                    placeholder="sk-..." autocomplete="new-password">
                                                <button type="button" class="btn btn-outline-secondary toggle-secret"
                                                    data-target="openai_api_key">
                                                    <i class="ri-eye-fill"></i>
                                                </button>
                                            </div>
                                            <div class="text-danger small mt-1 field-error" id="error-openai_api_key"></div>
                                        </div>
                                        <div class="col-12">
                                            <label for="openai_organization" class="form-label">
                                                Organization ID <span class="text-muted fs-11">(optional, starts with org-)</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="ri-building-line text-muted"></i></span>
                                                <input type="text" class="form-control font-monospace"
                                                    id="openai_organization" name="openai_organization"
                                                    value="{{ env('OPENAI_ORGANIZATION') }}"
                                                    placeholder="org-...">
                                            </div>
                                            <div class="text-danger small mt-1 field-error" id="error-openai_organization"></div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Anthropic Keys --}}
                                <div id="panel-anthropic" class="provider-panel {{ env('AI_PROVIDER') !== 'anthropic' ? 'd-none' : '' }}">
                                    <p class="text-muted text-uppercase fw-semibold fs-12 mb-3">
                                        <i class="ri-key-line me-1"></i> Anthropic API Keys
                                    </p>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label for="anthropic_api_key" class="form-label">
                                                API Key <span class="text-muted fs-11">(starts with sk-ant-)</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="ri-lock-line text-muted"></i></span>
                                                <input type="password" class="form-control font-monospace"
                                                    id="anthropic_api_key" name="anthropic_api_key"
                                                    value="{{ env('ANTHROPIC_API_KEY') }}"
                                                    placeholder="sk-ant-..." autocomplete="new-password">
                                                <button type="button" class="btn btn-outline-secondary toggle-secret"
                                                    data-target="anthropic_api_key">
                                                    <i class="ri-eye-fill"></i>
                                                </button>
                                            </div>
                                            <div class="text-danger small mt-1 field-error" id="error-anthropic_api_key"></div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Gemini Keys --}}
                                <div id="panel-gemini" class="provider-panel {{ env('AI_PROVIDER') !== 'gemini' ? 'd-none' : '' }}">
                                    <p class="text-muted text-uppercase fw-semibold fs-12 mb-3">
                                        <i class="ri-key-line me-1"></i> Google Gemini API Keys
                                    </p>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label for="gemini_api_key" class="form-label">
                                                API Key
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="ri-lock-line text-muted"></i></span>
                                                <input type="password" class="form-control font-monospace"
                                                    id="gemini_api_key" name="gemini_api_key"
                                                    value="{{ env('GEMINI_API_KEY') }}"
                                                    placeholder="AIza..." autocomplete="new-password">
                                                <button type="button" class="btn btn-outline-secondary toggle-secret"
                                                    data-target="gemini_api_key">
                                                    <i class="ri-eye-fill"></i>
                                                </button>
                                            </div>
                                            <div class="text-danger small mt-1 field-error" id="error-gemini_api_key"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="hstack gap-2 justify-content-end mt-4">
                                    <button type="submit" class="btn btn-primary" id="saveBtn">
                                        <span class="btn-text"><i class="ri-save-line me-1"></i> Save Changes</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-1"></span> Saving…
                                        </span>
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // ── Provider card highlight & panel toggle ───────────────────
            document.querySelectorAll('.provider-radio').forEach(function (radio) {
                radio.addEventListener('change', function () {
                    // Reset all card highlights
                    document.querySelectorAll('.provider-card').forEach(function (card) {
                        card.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
                    });
                    // Highlight selected card
                    this.closest('.provider-card').classList.add('border-primary', 'bg-primary', 'bg-opacity-10');

                    // Show/hide panels
                    document.querySelectorAll('.provider-panel').forEach(function (panel) {
                        panel.classList.add('d-none');
                    });
                    const panel = document.getElementById('panel-' + radio.value);
                    if (panel) panel.classList.remove('d-none');
                });
            });

            // ── Toggle secret visibility ─────────────────────────────────
            document.querySelectorAll('.toggle-secret').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const input = document.getElementById(this.dataset.target);
                    const icon  = this.querySelector('i');
                    const show  = input.type === 'password';
                    input.type  = show ? 'text' : 'password';
                    icon.className = show ? 'ri-eye-off-fill' : 'ri-eye-fill';
                });
            });

            // ── Form submit ──────────────────────────────────────────────
            document.getElementById('settingsForm').addEventListener('submit', function (e) {
                e.preventDefault();

                document.querySelectorAll('.field-error').forEach(el => el.textContent = '');
                document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

                const btn = document.getElementById('saveBtn');
                btn.disabled = true;
                btn.querySelector('.btn-text').classList.add('d-none');
                btn.querySelector('.btn-spinner').classList.remove('d-none');

                axios.patch('{{ route('admin.settings.ai.update') }}', {
                    ai_provider:         document.querySelector('input[name="ai_provider"]:checked')?.value,
                    openai_api_key:      document.getElementById('openai_api_key').value,
                    openai_organization: document.getElementById('openai_organization').value,
                    anthropic_api_key:   document.getElementById('anthropic_api_key').value,
                    gemini_api_key:      document.getElementById('gemini_api_key').value,
                })
                .then(res => Toast.success(res.data.message))
                .catch(function (err) {
                    const data = err.response?.data;
                    if (data?.errors) {
                        Object.entries(data.errors).forEach(function ([field, messages]) {
                            const errorEl = document.getElementById('error-' + field);
                            const inputEl = document.getElementById(field);
                            if (errorEl) errorEl.textContent = messages[0];
                            if (inputEl) inputEl.classList.add('is-invalid');
                        });
                        Toast.error(data.message || 'Please fix the errors below.');
                    } else {
                        Toast.fromResponse(data);
                    }
                })
                .finally(function () {
                    btn.disabled = false;
                    btn.querySelector('.btn-text').classList.remove('d-none');
                    btn.querySelector('.btn-spinner').classList.add('d-none');
                });
            });

        });
    </script>
@endpush
