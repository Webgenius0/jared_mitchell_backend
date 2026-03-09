@extends('layout.master-layout')
@section('title', 'IMAP Settings')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">IMAP Settings</h4>
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">IMAP</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="row">
                @include('pages.admin.settings._settings-nav')

                <div class="col-lg-9 col-xxl-10">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ri-inbox-line me-2 text-primary"></i> IMAP Incoming Mail Configuration
                            </h5>
                        </div>
                        <div class="card-body">

                            <div class="alert alert-info alert-borderless d-flex gap-2 mb-4">
                                <i class="ri-information-line fs-16 mt-1 flex-shrink-0"></i>
                                <span>
                                    For Gmail, use an <strong>App Password</strong> — not your regular Gmail password.
                                    Generate one at <a href="https://myaccount.google.com/apppasswords"
                                        target="_blank">myaccount.google.com/apppasswords</a>.
                                </span>
                            </div>

                            <form id="settingsForm">
                                <div class="row g-3">

                                    <div class="col-12">
                                        <p class="text-muted text-uppercase fw-semibold fs-12 mb-0">Server Configuration</p>
                                    </div>

                                    <div class="col-lg-6">
                                        <label for="imap_host" class="form-label">IMAP Host <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="imap_host" name="imap_host"
                                            value="{{ env('IMAP_HOST') }}" placeholder="imap.gmail.com">
                                        <div class="text-danger small mt-1 field-error" id="error-imap_host"></div>
                                    </div>

                                    <div class="col-lg-2">
                                        <label for="imap_port" class="form-label">Port <span
                                                class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="imap_port" name="imap_port"
                                            value="{{ env('IMAP_PORT') }}" placeholder="993">
                                        <div class="text-danger small mt-1 field-error" id="error-imap_port"></div>
                                    </div>

                                    <div class="col-lg-2">
                                        <label for="imap_protocol" class="form-label">Protocol <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="imap_protocol" name="imap_protocol">
                                            <option value="imap" {{ env('IMAP_PROTOCOL') === 'imap' ? 'selected' : '' }}>
                                                IMAP</option>
                                            <option value="pop3" {{ env('IMAP_PROTOCOL') === 'pop3' ? 'selected' : '' }}>
                                                POP3</option>
                                            <option value="nntp" {{ env('IMAP_PROTOCOL') === 'nntp' ? 'selected' : '' }}>
                                                NNTP</option>
                                        </select>
                                        <div class="text-danger small mt-1 field-error" id="error-imap_protocol"></div>
                                    </div>

                                    <div class="col-lg-2">
                                        <label for="imap_encryption" class="form-label">Encryption <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="imap_encryption" name="imap_encryption">
                                            <option value="ssl" {{ env('IMAP_ENCRYPTION') === 'ssl' ? 'selected' : '' }}>
                                                SSL</option>
                                            <option value="tls" {{ env('IMAP_ENCRYPTION') === 'tls' ? 'selected' : '' }}>
                                                TLS</option>
                                            <option value="notls"
                                                {{ env('IMAP_ENCRYPTION') === 'notls' ? 'selected' : '' }}>None</option>
                                        </select>
                                        <div class="text-danger small mt-1 field-error" id="error-imap_encryption"></div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="imap_validate_cert"
                                                {{ env('IMAP_VALIDATE_CERT') === 'true' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="imap_validate_cert">
                                                Validate SSL Certificate
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-12 pt-2">
                                        <p class="text-muted text-uppercase fw-semibold fs-12 mb-0">Credentials</p>
                                    </div>

                                    <div class="col-lg-6">
                                        <label for="imap_username" class="form-label">Username (Email) <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-mail-line text-muted"></i></span>
                                            <input type="email" class="form-control" id="imap_username"
                                                name="imap_username" value="{{ env('IMAP_USERNAME') }}"
                                                placeholder="you@gmail.com">
                                        </div>
                                        <div class="text-danger small mt-1 field-error" id="error-imap_username"></div>
                                    </div>

                                    <div class="col-lg-6">
                                        <label for="imap_password" class="form-label">Password / App Password</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control font-monospace" id="imap_password"
                                                name="imap_password" value="{{ env('IMAP_PASSWORD') }}"
                                                placeholder="••••••••" autocomplete="new-password">
                                            <button type="button" class="btn btn-outline-secondary toggle-secret"
                                                data-target="imap_password">
                                                <i class="ri-eye-fill"></i>
                                            </button>
                                        </div>
                                        <div class="text-danger small mt-1 field-error" id="error-imap_password"></div>
                                    </div>

                                    <div class="col-lg-6">
                                        <label for="imap_default_account" class="form-label">Default Account Name</label>
                                        <input type="text" class="form-control" id="imap_default_account"
                                            name="imap_default_account"
                                            value="{{ env('IMAP_DEFAULT_ACCOUNT', 'default') }}" placeholder="default">
                                        <div class="form-text">Used as the key in webklex/php-imap config.</div>
                                        <div class="text-danger small mt-1 field-error" id="error-imap_default_account">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="hstack gap-2 justify-content-end">
                                            <button type="submit" class="btn btn-primary" id="saveBtn">
                                                <span class="btn-text"><i class="ri-save-line me-1"></i> Save
                                                    Changes</span>
                                                <span class="btn-spinner d-none"><span
                                                        class="spinner-border spinner-border-sm me-1"></span>
                                                    Saving…</span>
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
            document.querySelectorAll('.toggle-secret').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const input = document.getElementById(this.dataset.target);
                    const icon = this.querySelector('i');
                    const show = input.type === 'password';
                    input.type = show ? 'text' : 'password';
                    icon.className = show ? 'ri-eye-off-fill' : 'ri-eye-fill';
                });
            });

            document.getElementById('settingsForm').addEventListener('submit', function(e) {
                e.preventDefault();
                document.querySelectorAll('.field-error').forEach(el => el.textContent = '');

                const btn = document.getElementById('saveBtn');
                btn.disabled = true;
                btn.querySelector('.btn-text').classList.add('d-none');
                btn.querySelector('.btn-spinner').classList.remove('d-none');

                axios.patch('{{ route('admin.settings.imap.update') }}', {
                        imap_host: document.getElementById('imap_host').value,
                        imap_port: document.getElementById('imap_port').value,
                        imap_protocol: document.getElementById('imap_protocol').value,
                        imap_encryption: document.getElementById('imap_encryption').value,
                        imap_validate_cert: document.getElementById('imap_validate_cert').checked ? 1 :
                            0,
                        imap_username: document.getElementById('imap_username').value,
                        imap_password: document.getElementById('imap_password').value,
                        imap_default_account: document.getElementById('imap_default_account').value,
                    })
                    .then(res => Toast.success(res.data.message))
                    .catch(function(err) {
                        const data = err.response?.data;
                        if (data?.errors) {
                            Object.entries(data.errors).forEach(([field, messages]) => {
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
