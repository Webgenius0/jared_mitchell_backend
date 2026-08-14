@extends('layout.master-layout')
@section('title', 'General Settings')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            {{-- Page Title --}}
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">General Settings</h4>
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">General Settings</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="row">

                {{-- Settings Sidebar --}}
                @include('web.admin.settings._settings-nav')

                {{-- Content --}}
                <div class="col-lg-9 col-xxl-10">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ri-settings-3-line me-2 text-primary"></i> Application Identity
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="settingsForm">

                                <div class="row g-3">

                                    <div class="col-lg-6">
                                        <label for="app_name" class="form-label">App Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="app_name" name="app_name"
                                            value="{{ $s->app_name }}" placeholder="My Application">
                                        <div class="text-danger small mt-1 field-error" id="error-app_name"></div>
                                    </div>

                                    <div class="col-lg-6">
                                        <label for="app_tagline" class="form-label">Tagline</label>
                                        <input type="text" class="form-control" id="app_tagline" name="app_tagline"
                                            value="{{ $s->app_tagline }}" placeholder="Build something great.">
                                        <div class="text-danger small mt-1 field-error" id="error-app_tagline"></div>
                                    </div>

                                    <div class="col-lg-4">
                                        <label for="app_version" class="form-label">Version</label>
                                        <input type="text" class="form-control" id="app_version" name="app_version"
                                            value="{{ $s->app_version }}" placeholder="1.0.0">
                                        <div class="text-danger small mt-1 field-error" id="error-app_version"></div>
                                    </div>

                                    <div class="col-lg-4">
                                        <label for="author_name" class="form-label">Author Name</label>
                                        <input type="text" class="form-control" id="author_name" name="author_name"
                                            value="{{ $s->author_name }}" placeholder="John Doe">
                                        <div class="text-danger small mt-1 field-error" id="error-author_name"></div>
                                    </div>

                                    <div class="col-lg-4">
                                        <label for="author_url" class="form-label">Author URL</label>
                                        <input type="url" class="form-control" id="author_url" name="author_url"
                                            value="{{ $s->author_url }}" placeholder="https://example.com">
                                        <div class="text-danger small mt-1 field-error" id="error-author_url"></div>
                                    </div>

                                    <div class="col-12">
                                        <label for="copyright" class="form-label">Copyright Text</label>
                                        <input type="text" class="form-control" id="copyright" name="copyright"
                                            value="{{ $s->copyright }}" placeholder="© 2026 My App. All rights reserved.">
                                        <div class="text-danger small mt-1 field-error" id="error-copyright"></div>
                                    </div>

                                    <div class="col-lg-6">
                                        <label for="boss_beginnings_start_date" class="form-label">Boss Beginnings Initial Start Date</label>
                                        <input type="datetime-local" class="form-control" id="boss_beginnings_start_date" name="boss_beginnings_start_date"
                                            value="{{ $s->boss_beginnings_start_date ? $s->boss_beginnings_start_date->format('Y-m-d\TH:i') : '' }}">
                                        <div class="text-danger small mt-1 field-error" id="error-boss_beginnings_start_date"></div>
                                    </div>

                                    <div class="col-lg-6">
                                        <label for="spotlight_start_date" class="form-label">Spotlight Initial Start Date</label>
                                        <input type="datetime-local" class="form-control" id="spotlight_start_date" name="spotlight_start_date"
                                            value="{{ $s->spotlight_start_date ? $s->spotlight_start_date->format('Y-m-d\TH:i') : '' }}">
                                        <div class="text-danger small mt-1 field-error" id="error-spotlight_start_date"></div>
                                    </div>

                                    {{-- Quill: About field --}}
                                    {{-- snow-editor class = theme loads Quill JS automatically --}}
                                    <div class="col-12">
                                        <label class="form-label">About</label>
                                        <div id="aboutEditor" class="snow-editor" style="height: 220px;"></div>
                                        <input type="hidden" id="about" name="about" value="{{ $s->about }}">
                                        <div class="text-danger small mt-1 field-error" id="error-about"></div>
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

            // ── Quill editor ─────────────────────────────────────────────
            // Theme initializes Quill on .snow-editor elements automatically.
            // Quill.find() is the official API to get the instance from a DOM element.
            const editorEl = document.getElementById('aboutEditor');
            const aboutInput = document.getElementById('about');
            const aboutEditor = Quill.find(editorEl);

            // Load existing content
            if (aboutEditor && aboutInput.value) {
                aboutEditor.clipboard.dangerouslyPasteHTML(aboutInput.value);
            }

            // Keep hidden input in sync
            if (aboutEditor) {
                aboutEditor.on('text-change', function() {
                    aboutInput.value = aboutEditor.getSemanticHTML();
                });
            }

            // ── Form submit ──────────────────────────────────────────────
            document.getElementById('settingsForm').addEventListener('submit', function(e) {
                e.preventDefault();

                document.querySelectorAll('.field-error').forEach(el => el.textContent = '');
                document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

                const btn = document.getElementById('saveBtn');
                btn.disabled = true;
                btn.querySelector('.btn-text').classList.add('d-none');
                btn.querySelector('.btn-spinner').classList.remove('d-none');

                axios.patch('{{ route('admin.settings.general.update') }}', {
                        app_name: document.getElementById('app_name').value,
                        app_tagline: document.getElementById('app_tagline').value,
                        app_version: document.getElementById('app_version').value,
                        author_name: document.getElementById('author_name').value,
                        author_url: document.getElementById('author_url').value,
                        copyright: document.getElementById('copyright').value,
                        about: document.getElementById('about').value,
                        boss_beginnings_start_date: document.getElementById('boss_beginnings_start_date').value,
                        spotlight_start_date: document.getElementById('spotlight_start_date').value,
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
