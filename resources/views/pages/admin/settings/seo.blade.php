@extends('layout.master-layout')
@section('title', 'SEO & Scripts')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">SEO & Scripts</h4>
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">SEO & Scripts</li>
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
                                <i class="ri-search-eye-line me-2 text-primary"></i> SEO & Tracking
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="settingsForm">
                                <div class="row g-3">

                                    {{-- Meta --}}
                                    <div class="col-12">
                                        <p class="text-muted text-uppercase fw-semibold fs-12 mb-0">Meta Tags</p>
                                    </div>

                                    <div class="col-12">
                                        <label for="meta_title" class="form-label d-flex justify-content-between">
                                            <span>Meta Title</span>
                                            <small class="text-muted"><span
                                                    id="metaTitleCount">{{ strlen($s->meta_title ?? '') }}</span>/160</small>
                                        </label>
                                        <input type="text" class="form-control" id="meta_title" name="meta_title"
                                            value="{{ $s->meta_title }}" maxlength="160"
                                            placeholder="My App — Build something great">
                                        <div class="text-danger small mt-1 field-error" id="error-meta_title"></div>
                                    </div>

                                    <div class="col-12">
                                        <label for="meta_description" class="form-label d-flex justify-content-between">
                                            <span>Meta Description</span>
                                            <small class="text-muted"><span
                                                    id="metaDescCount">{{ strlen($s->meta_description ?? '') }}</span>/320</small>
                                        </label>
                                        <textarea class="form-control" id="meta_description" name="meta_description" rows="3" maxlength="320"
                                            placeholder="A short description of your application for search engines…">{{ $s->meta_description }}</textarea>
                                        <div class="text-danger small mt-1 field-error" id="error-meta_description"></div>
                                    </div>

                                    <div class="col-12">
                                        <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                        <input type="text" class="form-control" id="meta_keywords" name="meta_keywords"
                                            value="{{ $s->meta_keywords }}" placeholder="laravel, saas, admin, dashboard">
                                        <div class="form-text">Comma-separated keywords.</div>
                                        <div class="text-danger small mt-1 field-error" id="error-meta_keywords"></div>
                                    </div>

                                    {{-- Tracking --}}
                                    <div class="col-12 pt-2">
                                        <p class="text-muted text-uppercase fw-semibold fs-12 mb-0">Analytics & Tracking</p>
                                    </div>

                                    <div class="col-lg-4">
                                        <label for="google_analytics_id" class="form-label">Google Analytics 4 ID</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i
                                                    class="ri-bar-chart-line text-warning"></i></span>
                                            <input type="text" class="form-control font-monospace"
                                                id="google_analytics_id" name="google_analytics_id"
                                                value="{{ $s->google_analytics_id }}" placeholder="G-XXXXXXXXXX">
                                        </div>
                                        <div class="text-danger small mt-1 field-error" id="error-google_analytics_id">
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <label for="google_tag_manager_id" class="form-label">Google Tag Manager ID</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i
                                                    class="ri-price-tag-3-line text-primary"></i></span>
                                            <input type="text" class="form-control font-monospace"
                                                id="google_tag_manager_id" name="google_tag_manager_id"
                                                value="{{ $s->google_tag_manager_id }}" placeholder="GTM-XXXXXXX">
                                        </div>
                                        <div class="text-danger small mt-1 field-error" id="error-google_tag_manager_id">
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <label for="facebook_pixel_id" class="form-label">Facebook Pixel ID</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-facebook-fill"
                                                    style="color:#1877f2"></i></span>
                                            <input type="text" class="form-control font-monospace" id="facebook_pixel_id"
                                                name="facebook_pixel_id" value="{{ $s->facebook_pixel_id }}"
                                                placeholder="XXXXXXXXXXXXXXXXXX">
                                        </div>
                                        <div class="text-danger small mt-1 field-error" id="error-facebook_pixel_id">
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <label for="google_site_verification" class="form-label">Google Site
                                            Verification</label>
                                        <input type="text" class="form-control font-monospace"
                                            id="google_site_verification" name="google_site_verification"
                                            value="{{ $s->google_site_verification }}"
                                            placeholder="Paste verification meta content value">
                                        <div class="text-danger small mt-1 field-error"
                                            id="error-google_site_verification"></div>
                                    </div>

                                    {{-- Custom Scripts --}}
                                    <div class="col-12 pt-2">
                                        <p class="text-muted text-uppercase fw-semibold fs-12 mb-0">Custom Scripts</p>
                                    </div>

                                    <div class="col-12">
                                        <label for="header_scripts" class="form-label">
                                            Header Scripts
                                            <span class="text-muted fs-11">(injected inside &lt;head&gt;)</span>
                                        </label>
                                        <textarea class="form-control font-monospace" id="header_scripts" name="header_scripts" rows="4"
                                            placeholder="<!-- custom head scripts -->">{{ $s->header_scripts }}</textarea>
                                        <div class="text-danger small mt-1 field-error" id="error-header_scripts"></div>
                                    </div>

                                    <div class="col-12">
                                        <label for="footer_scripts" class="form-label">
                                            Footer Scripts
                                            <span class="text-muted fs-11">(injected before &lt;/body&gt;)</span>
                                        </label>
                                        <textarea class="form-control font-monospace" id="footer_scripts" name="footer_scripts" rows="4"
                                            placeholder="<!-- custom footer scripts -->">{{ $s->footer_scripts }}</textarea>
                                        <div class="text-danger small mt-1 field-error" id="error-footer_scripts"></div>
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

            // ── Char counters ────────────────────────────────────────────
            document.getElementById('meta_title').addEventListener('input', function() {
                document.getElementById('metaTitleCount').textContent = this.value.length;
            });
            document.getElementById('meta_description').addEventListener('input', function() {
                document.getElementById('metaDescCount').textContent = this.value.length;
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

                axios.patch('{{ route('admin.settings.seo.update') }}', {
                        meta_title: document.getElementById('meta_title').value,
                        meta_description: document.getElementById('meta_description').value,
                        meta_keywords: document.getElementById('meta_keywords').value,
                        google_analytics_id: document.getElementById('google_analytics_id').value,
                        google_tag_manager_id: document.getElementById('google_tag_manager_id').value,
                        facebook_pixel_id: document.getElementById('facebook_pixel_id').value,
                        google_site_verification: document.getElementById('google_site_verification')
                            .value,
                        header_scripts: document.getElementById('header_scripts').value,
                        footer_scripts: document.getElementById('footer_scripts').value,
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
