@extends('layout.master-layout')
@section('title', 'Logo & Branding')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Logo & Branding</h4>
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Logo & Branding</li>
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
                                <i class="ri-image-line me-2 text-primary"></i> Logo & Branding Assets
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="settingsForm" enctype="multipart/form-data">

                                <div class="row g-4">

                                    @php
                                        $logos = [
                                            [
                                                'field' => 'logo',
                                                'label' => 'Main Logo',
                                                'hint' => 'Used across the site',
                                            ],
                                            [
                                                'field' => 'logo_light',
                                                'label' => 'Light Logo',
                                                'hint' => 'For dark backgrounds',
                                            ],
                                            [
                                                'field' => 'logo_dark',
                                                'label' => 'Dark Logo',
                                                'hint' => 'For light backgrounds',
                                            ],
                                            [
                                                'field' => 'logo_sm',
                                                'label' => 'Small / Icon Logo',
                                                'hint' => 'Square icon version',
                                            ],
                                            [
                                                'field' => 'favicon',
                                                'label' => 'Favicon',
                                                'hint' => '.png or .ico, 32×32px',
                                            ],
                                            [
                                                'field' => 'og_image',
                                                'label' => 'OG / Share Image',
                                                'hint' => '1200×630px recommended',
                                            ],
                                        ];
                                    @endphp

                                    @foreach ($logos as $item)
                                        <div class="col-lg-4 col-md-6">
                                            <label class="form-label fw-medium">{{ $item['label'] }}</label>

                                            <div class="border rounded p-3 text-center">

                                                {{-- Current image preview --}}
                                                @if ($s->{$item['field']})
                                                    <img src="{{ asset('storage/' . $s->{$item['field']}) }}"
                                                        id="preview-{{ $item['field'] }}" class="img-fluid mb-2"
                                                        style="max-height: 60px;">
                                                @else
                                                    <img id="preview-{{ $item['field'] }}" class="img-fluid mb-2 d-none"
                                                        style="max-height: 60px;">
                                                    <div id="placeholder-{{ $item['field'] }}" class="text-muted py-2">
                                                        <i class="ri-image-add-line fs-24 d-block"></i>
                                                        <span class="fs-12">No image</span>
                                                    </div>
                                                @endif

                                                {{-- File input styled as button --}}
                                                <label for="input-{{ $item['field'] }}"
                                                    class="btn btn-sm btn-soft-primary w-100 mb-1 cursor-pointer">
                                                    <i class="ri-upload-2-line me-1"></i> Choose File
                                                </label>
                                                <input type="file" id="input-{{ $item['field'] }}"
                                                    name="{{ $item['field'] }}" accept="image/*" class="d-none">

                                                <p class="text-muted fs-11 mb-0">{{ $item['hint'] }}</p>
                                            </div>

                                            <div class="text-danger small mt-1 field-error"
                                                id="error-{{ $item['field'] }}"></div>
                                        </div>
                                    @endforeach

                                    {{-- Logo size --}}
                                    <div class="col-12 pt-2">
                                        <p class="text-muted text-uppercase fw-semibold fs-12 mb-0">Logo Display Size (px)
                                        </p>
                                    </div>

                                    <div class="col-lg-3 col-md-4">
                                        <label for="logo_width" class="form-label">Width</label>
                                        <input type="number" class="form-control" id="logo_width" name="logo_width"
                                            value="{{ $s->logo_width }}" placeholder="140" min="1" max="1000">
                                        <div class="text-danger small mt-1 field-error" id="error-logo_width"></div>
                                    </div>

                                    <div class="col-lg-3 col-md-4">
                                        <label for="logo_height" class="form-label">Height</label>
                                        <input type="number" class="form-control" id="logo_height" name="logo_height"
                                            value="{{ $s->logo_height }}" placeholder="50" min="1" max="500">
                                        <div class="text-danger small mt-1 field-error" id="error-logo_height"></div>
                                    </div>

                                    <div class="col-12">
                                        <div class="hstack gap-2 justify-content-end">
                                            <button type="submit" class="btn btn-primary" id="saveBtn">
                                                <span class="btn-text"><i class="ri-save-line me-1"></i> Save Changes</span>
                                                <span class="btn-spinner d-none">
                                                    <span class="spinner-border spinner-border-sm me-1"></span> Uploading…
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

            // Show a live preview when the user picks a file
            document.querySelectorAll('input[type="file"]').forEach(function(input) {
                input.addEventListener('change', function() {
                    const file = this.files[0];
                    if (!file) return;

                    const fieldName = this.name;
                    const previewImg = document.getElementById('preview-' + fieldName);
                    const placeholder = document.getElementById('placeholder-' + fieldName);

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        previewImg.classList.remove('d-none');
                        if (placeholder) placeholder.classList.add('d-none');
                    };
                    reader.readAsDataURL(file);
                });
            });

            // Submit — multipart/form-data because of file uploads
            document.getElementById('settingsForm').addEventListener('submit', function(e) {
                e.preventDefault();

                document.querySelectorAll('.field-error').forEach(el => el.textContent = '');

                const btn = document.getElementById('saveBtn');
                btn.disabled = true;
                btn.querySelector('.btn-text').classList.add('d-none');
                btn.querySelector('.btn-spinner').classList.remove('d-none');

                const formData = new FormData(this);

                axios.post('{{ route('admin.settings.logo.update') }}', formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        },
                    })
                    .then(res => Toast.success(res.data.message))
                    .catch(function(err) {
                        const data = err.response?.data;
                        if (data?.errors) {
                            Object.entries(data.errors).forEach(function([field, messages]) {
                                const el = document.getElementById('error-' + field);
                                if (el) el.textContent = messages[0];
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
