@extends('layout.master-layout')
@section('title', 'HubSpot Settings')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">HubSpot Settings</h4>
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">HubSpot Settings</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="row">

                @include('web.admin.settings._settings-nav')

                <div class="col-lg-9 col-xxl-10">
                    <div class="card">
                        <div class="card-header d-flex align-items-center gap-2">
                            {{-- HubSpot sprocket icon --}}
                            <svg width="22" height="22" viewBox="0 0 512 512" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M402.9 209.1c-15.3-7-31.8-10.6-48.7-10.6-7.5 0-14.9.7-22.1 2V151c20.4-8.5 33.8-28.3 33.8-51.1C366 71.5 344.4 50 318 50c-26.4 0-48 21.5-48 48 0 22.8 13.4 42.6 33.8 51.1v49.5a103.6 103.6 0 0 0-23.3 10.5L137.2 93.3a56.7 56.7 0 0 0 1.5-13C138.7 53.8 116.5 32 89 32S39.3 53.8 39.3 80.3c0 26.4 22.2 48 49.7 48 12.4 0 23.7-4.5 32.4-11.9l141 114.2a103.4 103.4 0 0 0-14.3 52.5c0 22.5 7.2 44.3 20.6 62.3l-39.1 39.1a40.2 40.2 0 0 0-11.9-1.8c-22.5 0-40.8 18.3-40.8 40.8s18.3 40.8 40.8 40.8 40.8-18.3 40.8-40.8c0-4.2-.6-8.2-1.8-12l38.7-38.7a102.5 102.5 0 0 0 63.7 22c57.1 0 103.5-46.4 103.5-103.5.1-37.8-20.4-72.5-52.9-90.2zm-50.4 145.5c-29.3 0-53.1-23.8-53.1-53.1s23.8-53.1 53.1-53.1 53.1 23.8 53.1 53.1-23.8 53.1-53.1 53.1z" fill="#FF7A59"/>
                            </svg>
                            <h5 class="card-title mb-0">HubSpot CRM Configuration</h5>
                        </div>
                        <div class="card-body">

                            <div class="alert alert-warning alert-borderless d-flex gap-2 mb-4">
                                <i class="ri-shield-keyhole-line fs-16 mt-1 flex-shrink-0"></i>
                                <span>
                                    These credentials are stored in your <code>.env</code> file.
                                    Generate a <strong>Private App</strong> access token in HubSpot under
                                    <em>Settings → Integrations → Private Apps</em>. Never share your token.
                                </span>
                            </div>

                            <form id="settingsForm">
                                <div class="row g-3">

                                    {{-- Section: Authentication --}}
                                    <div class="col-12">
                                        <p class="text-muted text-uppercase fw-semibold fs-12 mb-0">Authentication</p>
                                    </div>

                                    <div class="col-12">
                                        <label for="hubspot_access_token" class="form-label">
                                            Private App Access Token
                                            <span class="text-muted fs-11">(starts with pat-)</span>
                                        </label>
                                        <div class="position-relative">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="ri-lock-line text-muted"></i></span>
                                                <input type="password" class="form-control font-monospace pe-5"
                                                    id="hubspot_access_token" name="hubspot_access_token"
                                                    value="{{ env('HUBSPOT_ACCESS_TOKEN') }}"
                                                    placeholder="pat-na1-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
                                                    autocomplete="new-password">
                                                <button type="button" class="btn btn-outline-secondary toggle-secret"
                                                    data-target="hubspot_access_token">
                                                    <i class="ri-eye-fill"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="text-danger small mt-1 field-error" id="error-hubspot_access_token"></div>
                                    </div>

                                    <div class="col-12 col-lg-6">
                                        <label for="hubspot_portal_id" class="form-label">
                                            Portal (Hub) ID
                                            <span class="text-muted fs-11">(numeric, e.g. 12345678)</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-building-line text-muted"></i></span>
                                            <input type="text" class="form-control font-monospace"
                                                id="hubspot_portal_id" name="hubspot_portal_id"
                                                value="{{ env('HUBSPOT_PORTAL_ID') }}"
                                                placeholder="12345678">
                                        </div>
                                        <div class="text-muted fs-11 mt-1">
                                            Found in HubSpot → Settings → Account Setup → Account Information.
                                        </div>
                                        <div class="text-danger small mt-1 field-error" id="error-hubspot_portal_id"></div>
                                    </div>

                                    {{-- Section: Forms & Newsletter --}}
                                    <div class="col-12 pt-2">
                                        <p class="text-muted text-uppercase fw-semibold fs-12 mb-0">Forms &amp; Newsletter</p>
                                    </div>

                                    <div class="col-12">
                                        <label for="hubspot_newsletter_form_guid" class="form-label">
                                            Newsletter Form GUID
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-mail-add-line text-muted"></i></span>
                                            <input type="text" class="form-control font-monospace"
                                                id="hubspot_newsletter_form_guid" name="hubspot_newsletter_form_guid"
                                                value="{{ env('HUBSPOT_NEWSLETTER_FORM_GUID') }}"
                                                placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx">
                                        </div>
                                        <div class="text-muted fs-11 mt-1">
                                            The GUID of the HubSpot form used for newsletter sign-ups.
                                            Find it on the form's detail page in HubSpot → Marketing → Forms.
                                        </div>
                                        <div class="text-danger small mt-1 field-error" id="error-hubspot_newsletter_form_guid"></div>
                                    </div>

                                    {{-- API Endpoints reference --}}
                                    <div class="col-12 pt-2">
                                        <p class="text-muted text-uppercase fw-semibold fs-12 mb-1">Available API Endpoints</p>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered table-nowrap mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Method</th>
                                                        <th>Endpoint</th>
                                                        <th>Description</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><span class="badge bg-success">POST</span></td>
                                                        <td><code>/api/v1/hubspot/contact</code></td>
                                                        <td>Create / update a CRM contact</td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="badge bg-primary">GET</span></td>
                                                        <td><code>/api/v1/hubspot/contact?email=</code></td>
                                                        <td>Look up a contact by email</td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="badge bg-success">POST</span></td>
                                                        <td><code>/api/v1/hubspot/form/{formGuid}</code></td>
                                                        <td>Submit any HubSpot form</td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="badge bg-success">POST</span></td>
                                                        <td><code>/api/v1/hubspot/newsletter/subscribe</code></td>
                                                        <td>Subscribe email to newsletter</td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="badge bg-danger">POST</span></td>
                                                        <td><code>/api/v1/hubspot/newsletter/unsubscribe</code></td>
                                                        <td>Opt out email from newsletter</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="hstack gap-2 justify-content-end">
                                            <button type="submit" class="btn btn-primary" id="saveBtn">
                                                <span class="btn-text"><i class="ri-save-line me-1"></i> Save Changes</span>
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
        document.addEventListener('DOMContentLoaded', function () {

            // ── Toggle secret visibility ──────────────────────────────────
            document.querySelectorAll('.toggle-secret').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const input = document.getElementById(this.dataset.target);
                    const icon  = this.querySelector('i');
                    const show  = input.type === 'password';
                    input.type  = show ? 'text' : 'password';
                    icon.className = show ? 'ri-eye-off-fill' : 'ri-eye-fill';
                });
            });

            // ── Form submit ───────────────────────────────────────────────
            document.getElementById('settingsForm').addEventListener('submit', function (e) {
                e.preventDefault();

                document.querySelectorAll('.field-error').forEach(el => el.textContent = '');
                document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

                const btn = document.getElementById('saveBtn');
                btn.disabled = true;
                btn.querySelector('.btn-text').classList.add('d-none');
                btn.querySelector('.btn-spinner').classList.remove('d-none');

                axios.patch('{{ route('admin.settings.hubspot.update') }}', {
                    hubspot_access_token:         document.getElementById('hubspot_access_token').value,
                    hubspot_portal_id:            document.getElementById('hubspot_portal_id').value,
                    hubspot_newsletter_form_guid: document.getElementById('hubspot_newsletter_form_guid').value,
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
                    } else {
                        Toast.error(data?.message ?? 'Something went wrong. Please try again.');
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
