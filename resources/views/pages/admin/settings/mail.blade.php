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
                                <i class="ri-mail-settings-line me-2 text-primary"></i> Outgoing Mail Identity
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="settingsForm">

                                <div class="row g-3">

                                    <div class="col-lg-6">
                                        <label for="mail_from_name" class="form-label">From Name</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-user-line text-muted"></i></span>
                                            <input type="text" class="form-control" id="mail_from_name"
                                                name="mail_from_name" value="{{ $s->mail_from_name }}"
                                                placeholder="My Application">
                                        </div>
                                        <div class="form-text">Name shown in the "From" field of emails.</div>
                                        <div class="text-danger small mt-1 field-error" id="error-mail_from_name"></div>
                                    </div>

                                    <div class="col-lg-6">
                                        <label for="mail_from_address" class="form-label">From Email Address</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-mail-line text-muted"></i></span>
                                            <input type="email" class="form-control" id="mail_from_address"
                                                name="mail_from_address" value="{{ $s->mail_from_address }}"
                                                placeholder="noreply@example.com">
                                        </div>
                                        <div class="form-text">Sender email address for all outgoing emails.</div>
                                        <div class="text-danger small mt-1 field-error" id="error-mail_from_address"></div>
                                    </div>

                                    {{-- Quill: Email Signature --}}
                                    <div class="col-12">
                                        <label class="form-label">
                                            Email Signature
                                            <span class="text-muted fs-11">— appended at the bottom of all outgoing
                                                emails</span>
                                        </label>
                                        <div id="signatureEditor" style="height: 220px;"></div>
                                        <input type="hidden" id="mail_signature" name="mail_signature"
                                            value="{{ $s->mail_signature }}">
                                        <div class="text-danger small mt-1 field-error" id="error-mail_signature"></div>
                                    </div>

                                    <div class="col-12">
                                        <div class="alert alert-info alert-borderless d-flex gap-2 mb-0">
                                            <i class="ri-information-line fs-16 mt-1 flex-shrink-0"></i>
                                            <span>
                                                SMTP credentials (host, port, username, password) are managed via
                                                <code>.env</code> file — not stored here for security reasons.
                                            </span>
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
        document.addEventListener('DOMContentLoaded', function() {

            // ── Quill: Email Signature ───────────────────────────────────
            const signatureEditor = new Quill('#signatureEditor', {
                theme: 'snow',
                placeholder: 'Write your email signature here…',
            });

            const signatureInput = document.getElementById('mail_signature');
            if (signatureInput.value) {
                signatureEditor.clipboard.dangerouslyPasteHTML(signatureInput.value);
            }
            signatureEditor.on('text-change', function() {
                signatureInput.value = signatureEditor.getSemanticHTML();
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
                        mail_from_name: document.getElementById('mail_from_name').value,
                        mail_from_address: document.getElementById('mail_from_address').value,
                        mail_signature: document.getElementById('mail_signature').value,
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
