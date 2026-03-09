@extends('layout.master-layout')
@section('title', 'Mail Settings')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Mail Settings</h4>
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Mail Settings</li>
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
                                <i class="ri-mail-settings-line me-2 text-primary"></i> Mail Configuration
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="settingsForm">

                                <div class="row g-3">

                                    {{-- SMTP --}}
                                    <div class="col-12">
                                        <p class="text-muted text-uppercase fw-semibold fs-12 mb-0">SMTP Configuration</p>
                                    </div>

                                    <div class="col-lg-6">
                                        <label for="mail_mailer" class="form-label">Mailer</label>
                                        <select class="form-select" id="mail_mailer" name="mail_mailer">
                                            @foreach (['smtp', 'sendmail', 'mailgun', 'ses', 'postmark', 'log'] as $mailer)
                                                <option value="{{ $mailer }}"
                                                    {{ env('MAIL_MAILER') === $mailer ? 'selected' : '' }}>
                                                    {{ strtoupper($mailer) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="text-danger small mt-1 field-error" id="error-mail_mailer"></div>
                                    </div>

                                    <div class="col-lg-6">
                                        <label for="mail_encryption" class="form-label">Encryption</label>
                                        <select class="form-select" id="mail_encryption" name="mail_encryption">
                                            <option value="tls" {{ env('MAIL_ENCRYPTION') === 'tls' ? 'selected' : '' }}>
                                                TLS</option>
                                            <option value="ssl" {{ env('MAIL_ENCRYPTION') === 'ssl' ? 'selected' : '' }}>
                                                SSL</option>
                                            <option value="" {{ !env('MAIL_ENCRYPTION') ? 'selected' : '' }}>None
                                            </option>
                                        </select>
                                        <div class="text-danger small mt-1 field-error" id="error-mail_encryption"></div>
                                    </div>

                                    <div class="col-lg-8">
                                        <label for="mail_host" class="form-label">SMTP Host <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="mail_host" name="mail_host"
                                            value="{{ env('MAIL_HOST') }}" placeholder="smtp.mailtrap.io">
                                        <div class="text-danger small mt-1 field-error" id="error-mail_host"></div>
                                    </div>

                                    <div class="col-lg-4">
                                        <label for="mail_port" class="form-label">SMTP Port <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="mail_port" name="mail_port">
                                            <option value="587"
                                                {{ (string) env('MAIL_PORT') === '587' ? 'selected' : '' }}>587 (TLS)
                                            </option>
                                            <option value="465"
                                                {{ (string) env('MAIL_PORT') === '465' ? 'selected' : '' }}>465 (SSL)
                                            </option>
                                            <option value="25"
                                                {{ (string) env('MAIL_PORT') === '25' ? 'selected' : '' }}>25</option>
                                            <option value="2525"
                                                {{ (string) env('MAIL_PORT') === '2525' ? 'selected' : '' }}>2525</option>
                                        </select>
                                        <div class="text-danger small mt-1 field-error" id="error-mail_port"></div>
                                    </div>

                                    <div class="col-lg-6">
                                        <label for="mail_username" class="form-label">SMTP Username</label>
                                        <input type="text" class="form-control" id="mail_username" name="mail_username"
                                            value="{{ env('MAIL_USERNAME') }}" placeholder="your@email.com"
                                            autocomplete="off">
                                        <div class="text-danger small mt-1 field-error" id="error-mail_username"></div>
                                    </div>

                                    <div class="col-lg-6">
                                        <label for="mail_password" class="form-label">SMTP Password</label>
                                        <div class="position-relative">
                                            <input type="password" class="form-control pe-5" id="mail_password"
                                                name="mail_password" value="{{ env('MAIL_PASSWORD') }}"
                                                placeholder="••••••••" autocomplete="new-password">
                                            <button type="button"
                                                class="btn btn-link position-absolute end-0 top-0 text-muted text-decoration-none"
                                                id="togglePassword">
                                                <i class="ri-eye-fill align-middle"></i>
                                            </button>
                                        </div>
                                        <div class="text-danger small mt-1 field-error" id="error-mail_password"></div>
                                    </div>

                                    {{-- Sender Identity --}}
                                    <div class="col-12 pt-2">
                                        <p class="text-muted text-uppercase fw-semibold fs-12 mb-0">Sender Identity</p>
                                    </div>

                                    <div class="col-lg-6">
                                        <label for="mail_from_address" class="form-label">From Address <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-mail-line text-muted"></i></span>
                                            <input type="email" class="form-control" id="mail_from_address"
                                                name="mail_from_address" value="{{ env('MAIL_FROM_ADDRESS') }}"
                                                placeholder="noreply@example.com">
                                        </div>
                                        <div class="text-danger small mt-1 field-error" id="error-mail_from_address">
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <label for="mail_from_name" class="form-label">From Name <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-user-line text-muted"></i></span>
                                            <input type="text" class="form-control" id="mail_from_name"
                                                name="mail_from_name" value="{{ env('MAIL_FROM_NAME') }}"
                                                placeholder="My Application">
                                        </div>
                                        <div class="text-danger small mt-1 field-error" id="error-mail_from_name"></div>
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

            // ── Password visibility toggle ───────────────────────────────
            document.getElementById('togglePassword').addEventListener('click', function() {
                const input = document.getElementById('mail_password');
                const icon = this.querySelector('i');
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                icon.className = show ? 'ri-eye-off-fill align-middle' : 'ri-eye-fill align-middle';
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

                axios.patch('{{ route('admin.settings.mail.update') }}', {
                        mail_mailer: document.getElementById('mail_mailer').value,
                        mail_host: document.getElementById('mail_host').value,
                        mail_port: document.getElementById('mail_port').value,
                        mail_encryption: document.getElementById('mail_encryption').value,
                        mail_username: document.getElementById('mail_username').value,
                        mail_password: document.getElementById('mail_password').value,
                        mail_from_address: document.getElementById('mail_from_address').value,
                        mail_from_name: document.getElementById('mail_from_name').value,
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
