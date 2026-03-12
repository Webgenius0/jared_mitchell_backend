@extends('layout.master-layout')
@section('title', 'App Settings')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">App Environment Settings</h4>
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">App Settings</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="row">

                @include('web.admin.settings._settings-nav')

                <div class="col-lg-9 col-xxl-10">
                    <div class="card">
                        <div class="card-header d-flex align-items-center gap-2">
                            <i class="ri-apps-fill fs-4"></i>
                            <h5 class="card-title mb-0">App Environment</h5>
                        </div>
                        <div class="card-body">

                            <div class="alert alert-success alert-borderless d-flex align-items-center gap-2 mb-4">
                                <i class="ri-shield-keyhole-line fs-5 flex-shrink-0"></i>
                                <div class="mb-0">
                                    These links and values are stored in your <code>.env</code> file.
                                </div>
                            </div>

                            <form id="settingsForm">
                                <div class="row g-3">

                                    <div class="col-12">
                                        <p class="text-muted text-uppercase fw-semibold fs-12 mb-0">App Settings</p>
                                    </div>

                                    <div class="col-lg-6">
                                        <label for="app_name" class="form-label">
                                            App Name <span class="text-danger">* <small class="text-red">Warning: After
                                                    change app name you will be logged out</small> </span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-apps-line text-muted"></i></span>
                                            <input type="text" class="form-control" id="app_name" name="app_name"
                                                value="{{ env('APP_NAME') }}" placeholder="Laravel">
                                        </div>
                                        <div class="text-danger small mt-1 field-error" id="error-app_name"></div>
                                    </div>

                                    <div class="col-lg-6">
                                        <label for="app_env" class="form-label">
                                            App Environment <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i
                                                    class="ri-terminal-box-line text-muted"></i></span>
                                            <input type="text" class="form-control font-monospace" id="app_env"
                                                name="app_env" value="{{ env('APP_ENV') }}"
                                                placeholder="local / production">
                                        </div>
                                        <div class="text-danger small mt-1 field-error" id="error-app_env"></div>
                                    </div>

                                    <div class="col-lg-6">
                                        <label for="app_debug" class="form-label">
                                            Debug Mode <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-bug-line text-muted"></i></span>
                                            <select class="form-select" id="app_debug" name="app_debug">
                                                <option value="true" {{ env('APP_DEBUG') ? 'selected' : '' }}>true
                                                </option>
                                                <option value="false" {{ !env('APP_DEBUG') ? 'selected' : '' }}>false
                                                </option>
                                            </select>
                                        </div>
                                        <div class="text-danger small mt-1 field-error" id="error-app_debug"></div>
                                    </div>

                                    <div class="col-lg-6">
                                        <label for="app_url" class="form-label">
                                            App URL <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-global-line text-muted"></i></span>
                                            <input type="url" class="form-control" id="app_url" name="app_url"
                                                value="{{ env('APP_URL') }}" placeholder="https://example.com">
                                        </div>
                                        <div class="text-danger small mt-1 field-error" id="error-app_url"></div>
                                    </div>

                                    <div class="col-lg-6">
                                        <label for="frontend_url" class="form-label">
                                            Frontend URL
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-links-line text-muted"></i></span>
                                            <input type="url" class="form-control" id="frontend_url" name="frontend_url"
                                                value="{{ env('FRONTEND_URL') }}"
                                                placeholder="https://frontend.example.com">
                                        </div>
                                        <div class="text-danger small mt-1 field-error" id="error-frontend_url"></div>
                                    </div>

                                    <div class="col-lg-6">
                                        <label for="app_timezone" class="form-label">
                                            App TimeZone
                                        </label>

                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="ri-time-line text-muted"></i>
                                            </span>

                                            <select class="form-control" id="app_timezone" name="app_timezone">
                                                <option value="">Select Timezone</option>

                                                @foreach (timezone_identifiers_list() as $timezone)
                                                    <option value="{{ $timezone }}"
                                                        {{ env('APP_TIMEZONE') == $timezone ? 'selected' : '' }}>
                                                        {{ $timezone }}
                                                    </option>
                                                @endforeach

                                            </select>
                                        </div>

                                        <div class="text-danger small mt-1 field-error" id="error-app_timezone"></div>
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
            // ── Form submit ──────────────────────────────────────────────
            document.getElementById('settingsForm').addEventListener('submit', function(e) {
                e.preventDefault();

                document.querySelectorAll('.field-error').forEach(el => el.textContent = '');
                document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

                const btn = document.getElementById('saveBtn');
                btn.disabled = true;
                btn.querySelector('.btn-text').classList.add('d-none');
                btn.querySelector('.btn-spinner').classList.remove('d-none');

                axios.patch('{{ route('admin.settings.app.update') }}', {
                        app_name: document.getElementById('app_name').value,
                        app_env: document.getElementById('app_env').value,
                        app_debug: document.getElementById('app_debug').value,
                        app_url: document.getElementById('app_url').value,
                        frontend_url: document.getElementById('frontend_url').value,
                        app_timezone: document.getElementById('app_timezone').value,
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

            // Fix - timezone dropdown
            setTimeout(function() {
                $('#app_timezone').select2({
                    placeholder: "Select Timezone",
                    minimumResultsForSearch: 0,
                    width: 'calc(100% - 38px)', // input-group icon (38px)
                });
            }, 0);
        });
    </script>
@endpush
