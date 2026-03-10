@extends('layout.master-layout')
@section('title', 'Reverb Settings')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Reverb Settings</h4>
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Reverb Settings</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="row">
                @include('web.admin.settings._settings-nav')

                <div class="col-lg-9 col-xxl-10">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ri-broadcast-line me-2 text-primary"></i> Laravel Reverb (WebSocket) Configuration
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="settingsForm">
                                <div class="row g-3">

                                    <div class="col-12">
                                        <p class="text-muted text-uppercase fw-semibold fs-12 mb-0">App Credentials</p>
                                    </div>

                                    <div class="col-lg-4">
                                        <label for="reverb_app_id" class="form-label">App ID <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control font-monospace" id="reverb_app_id"
                                            name="reverb_app_id" value="{{ env('REVERB_APP_ID') }}" placeholder="802151">
                                        <div class="text-danger small mt-1 field-error" id="error-reverb_app_id"></div>
                                    </div>

                                    <div class="col-lg-4">
                                        <label for="reverb_app_key" class="form-label">App Key <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control font-monospace" id="reverb_app_key"
                                            name="reverb_app_key" value="{{ env('REVERB_APP_KEY') }}"
                                            placeholder="abc123...">
                                        <div class="text-danger small mt-1 field-error" id="error-reverb_app_key"></div>
                                    </div>

                                    <div class="col-lg-4">
                                        <label for="reverb_app_secret" class="form-label">App Secret <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="password" class="form-control font-monospace"
                                                id="reverb_app_secret" name="reverb_app_secret"
                                                value="{{ env('REVERB_APP_SECRET') }}" placeholder="••••••••">
                                            <button type="button" class="btn btn-outline-secondary toggle-secret"
                                                data-target="reverb_app_secret">
                                                <i class="ri-eye-fill"></i>
                                            </button>
                                        </div>
                                        <div class="text-danger small mt-1 field-error" id="error-reverb_app_secret"></div>
                                    </div>

                                    <div class="col-12 pt-2">
                                        <p class="text-muted text-uppercase fw-semibold fs-12 mb-0">Server Configuration</p>
                                    </div>

                                    <div class="col-lg-6">
                                        <label for="reverb_host" class="form-label">Host <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="reverb_host" name="reverb_host"
                                            value="{{ env('REVERB_HOST') }}" placeholder="yoursite.com">
                                        <div class="text-danger small mt-1 field-error" id="error-reverb_host"></div>
                                    </div>

                                    <div class="col-lg-2">
                                        <label for="reverb_scheme" class="form-label">Scheme <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="reverb_scheme" name="reverb_scheme">
                                            <option value="https" {{ env('REVERB_SCHEME') === 'https' ? 'selected' : '' }}>
                                                HTTPS</option>
                                            <option value="http" {{ env('REVERB_SCHEME') === 'http' ? 'selected' : '' }}>
                                                HTTP</option>
                                        </select>
                                        <div class="text-danger small mt-1 field-error" id="error-reverb_scheme"></div>
                                    </div>

                                    <div class="col-lg-2">
                                        <label for="reverb_port" class="form-label">Client Port <span
                                                class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="reverb_port" name="reverb_port"
                                            value="{{ env('REVERB_PORT') }}" placeholder="8081">
                                        <div class="form-text">Frontend connects here.</div>
                                        <div class="text-danger small mt-1 field-error" id="error-reverb_port"></div>
                                    </div>

                                    <div class="col-lg-2">
                                        <label for="reverb_server_port" class="form-label">Server Port <span
                                                class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="reverb_server_port"
                                            name="reverb_server_port" value="{{ env('REVERB_SERVER_PORT') }}"
                                            placeholder="8083">
                                        <div class="form-text">Reverb listens here.</div>
                                        <div class="text-danger small mt-1 field-error" id="error-reverb_server_port">
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
                document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

                const btn = document.getElementById('saveBtn');
                btn.disabled = true;
                btn.querySelector('.btn-text').classList.add('d-none');
                btn.querySelector('.btn-spinner').classList.remove('d-none');

                axios.patch('{{ route('admin.settings.reverb.update') }}', {
                        reverb_app_id: document.getElementById('reverb_app_id').value,
                        reverb_app_key: document.getElementById('reverb_app_key').value,
                        reverb_app_secret: document.getElementById('reverb_app_secret').value,
                        reverb_host: document.getElementById('reverb_host').value,
                        reverb_port: document.getElementById('reverb_port').value,
                        reverb_server_port: document.getElementById('reverb_server_port').value,
                        reverb_scheme: document.getElementById('reverb_scheme').value,
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
