@extends('layout.master-layout')
@section('title', 'Stripe Settings')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Stripe Settings</h4>
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Stripe Settings</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="row">

                @include('pages.admin.settings._settings-nav')

                <div class="col-lg-9 col-xxl-10">
                    <div class="card">
                        <div class="card-header d-flex align-items-center gap-2">
                            <svg width="20" height="20" viewBox="0 0 32 32" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect width="32" height="32" rx="6" fill="#635BFF" />
                                <path
                                    d="M13.4 12.8c0-.9.7-1.2 1.9-1.2 1.7 0 3.8.5 5.5 1.4V8.5C19 7.6 17.1 7 14.8 7 10.2 7 7 9.4 7 13.1c0 5.8 8 4.9 8 7.4 0 1-.9 1.4-2.1 1.4-1.8 0-4.2-.8-6-1.8v4.6C8.7 25.5 11 26 13.4 26c4.7 0 8-2.3 8-6.1-.1-6.2-8-5.1-8-7.1z"
                                    fill="white" />
                            </svg>
                            <h5 class="card-title mb-0">Stripe Payment Configuration</h5>
                        </div>
                        <div class="card-body">

                            <div class="alert alert-warning alert-borderless d-flex gap-2 mb-4">
                                <i class="ri-shield-keyhole-line fs-16 mt-1 flex-shrink-0"></i>
                                <span>
                                    These keys are stored in your <code>.env</code> file.
                                    Use <strong>test keys</strong> during development and switch to live keys in production.
                                    Never share your secret key.
                                </span>
                            </div>

                            <form id="settingsForm">
                                <div class="row g-3">

                                    <div class="col-12">
                                        <p class="text-muted text-uppercase fw-semibold fs-12 mb-0">API Keys</p>
                                    </div>

                                    <div class="col-12">
                                        <label for="stripe_key" class="form-label">
                                            Publishable Key <span class="text-danger">*</span>
                                            <span class="text-muted fs-11">(starts with pk_)</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-key-line text-muted"></i></span>
                                            <input type="text" class="form-control font-monospace" id="stripe_key"
                                                name="stripe_key" value="{{ env('STRIPE_KEY') }}" placeholder="pk_test_...">
                                        </div>
                                        <div class="text-danger small mt-1 field-error" id="error-stripe_key"></div>
                                    </div>

                                    <div class="col-12">
                                        <label for="stripe_secret" class="form-label">
                                            Secret Key <span class="text-danger">*</span>
                                            <span class="text-muted fs-11">(starts with sk_)</span>
                                        </label>
                                        <div class="position-relative">
                                            <div class="input-group">
                                                <span class="input-group-text"><i
                                                        class="ri-lock-line text-muted"></i></span>
                                                <input type="password" class="form-control font-monospace pe-5"
                                                    id="stripe_secret" name="stripe_secret"
                                                    value="{{ env('STRIPE_SECRET') }}" placeholder="sk_test_..."
                                                    autocomplete="new-password">
                                                <button type="button" class="btn btn-outline-secondary toggle-secret"
                                                    data-target="stripe_secret">
                                                    <i class="ri-eye-fill"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="text-danger small mt-1 field-error" id="error-stripe_secret"></div>
                                    </div>

                                    <div class="col-12">
                                        <label for="stripe_webhook_secret" class="form-label">
                                            Webhook Secret
                                            <span class="text-muted fs-11">(starts with whsec_)</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-webhook-line text-muted"></i></span>
                                            <input type="password" class="form-control font-monospace"
                                                id="stripe_webhook_secret" name="stripe_webhook_secret"
                                                value="{{ env('STRIPE_WEBHOOK_SECRET') }}" placeholder="whsec_...">
                                            <button type="button" class="btn btn-outline-secondary toggle-secret"
                                                data-target="stripe_webhook_secret">
                                                <i class="ri-eye-fill"></i>
                                            </button>
                                        </div>
                                        <div class="text-danger small mt-1 field-error" id="error-stripe_webhook_secret">
                                        </div>
                                    </div>

                                    <div class="col-12 pt-2">
                                        <p class="text-muted text-uppercase fw-semibold fs-12 mb-0">Redirect URLs</p>
                                    </div>

                                    <div class="col-lg-6">
                                        <label for="stripe_success_url" class="form-label">Success URL</label>
                                        <div class="input-group">
                                            <span class="input-group-text text-success"><i
                                                    class="ri-checkbox-circle-line"></i></span>
                                            <input type="url" class="form-control" id="stripe_success_url"
                                                name="stripe_success_url" value="{{ env('STRIPE_SUCCESS_URL') }}"
                                                placeholder="https://yoursite.com/payment/success">
                                        </div>
                                        <div class="text-danger small mt-1 field-error" id="error-stripe_success_url">
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <label for="stripe_cancel_url" class="form-label">Cancel URL</label>
                                        <div class="input-group">
                                            <span class="input-group-text text-danger"><i
                                                    class="ri-close-circle-line"></i></span>
                                            <input type="url" class="form-control" id="stripe_cancel_url"
                                                name="stripe_cancel_url" value="{{ env('STRIPE_CANCEL_URL') }}"
                                                placeholder="https://yoursite.com/payment/cancel">
                                        </div>
                                        <div class="text-danger small mt-1 field-error" id="error-stripe_cancel_url">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="hstack gap-2 justify-content-end">
                                            <button type="submit" class="btn btn-primary" id="saveBtn">
                                                <span class="btn-text"><i class="ri-save-line me-1"></i> Save
                                                    Changes</span>
                                                <span class="btn-spinner d-none">
                                                    <span class="spinner-border spinner-border-sm me-1"></span> Saving…
                                                </span>
                                            </button>
                                        </div>
                                    </div>

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
        document.addEventListener('DOMContentLoaded', function() {

            // ── Toggle secret visibility ─────────────────────────────────
            document.querySelectorAll('.toggle-secret').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const input = document.getElementById(this.dataset.target);
                    const icon = this.querySelector('i');
                    const show = input.type === 'password';
                    input.type = show ? 'text' : 'password';
                    icon.className = show ? 'ri-eye-off-fill' : 'ri-eye-fill';
                });
            });

            // ── Form submit ──────────────────────────────────────────────
            document.getElementById('settingsForm').addEventListener('submit', function(e) {
                e.preventDefault();

                document.querySelectorAll('.field-error').forEach(el => el.textContent = '');
                document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

                const btn = document.getElementById('saveBtn');
                btn.disabled = true;
                btn.querySelector('.btn-text').classList.add('d-none');
                btn.querySelector('.btn-spinner').classList.remove('d-none');

                axios.patch('{{ route('admin.settings.stripe.update') }}', {
                        stripe_key: document.getElementById('stripe_key').value,
                        stripe_secret: document.getElementById('stripe_secret').value,
                        stripe_webhook_secret: document.getElementById('stripe_webhook_secret').value,
                        stripe_success_url: document.getElementById('stripe_success_url').value,
                        stripe_cancel_url: document.getElementById('stripe_cancel_url').value,
                    })
                    .then(res => Toast.success(res.data.message))
                    .catch(function(err) {
                        const data = err.response?.data;
                        if (data?.errors) {
                            Object.entries(data.errors).forEach(function([field, messages]) {
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
                    .finally(function() {
                        btn.disabled = false;
                        btn.querySelector('.btn-text').classList.remove('d-none');
                        btn.querySelector('.btn-spinner').classList.add('d-none');
                    });
            });

        });
    </script>
@endpush
