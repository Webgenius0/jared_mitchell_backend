@extends('layout.master-layout')
@section('title', 'AWS S3 Settings')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">AWS S3 Settings</h4>
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">AWS S3</li>
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
                                <i class="ri-cloud-line me-2 text-primary"></i> AWS S3 Storage Configuration
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="settingsForm">
                                <div class="row g-3">

                                    <div class="col-12">
                                        <label for="aws_access_key_id" class="form-label">Access Key ID</label>
                                        <input type="text" class="form-control font-monospace" id="aws_access_key_id"
                                            name="aws_access_key_id" value="{{ env('AWS_ACCESS_KEY_ID') }}"
                                            placeholder="AKIAIOSFODNN7EXAMPLE">
                                        <div class="text-danger small mt-1 field-error" id="error-aws_access_key_id"></div>
                                    </div>

                                    <div class="col-12">
                                        <label for="aws_secret_access_key" class="form-label">Secret Access Key</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control font-monospace"
                                                id="aws_secret_access_key" name="aws_secret_access_key"
                                                value="{{ env('AWS_SECRET_ACCESS_KEY') }}" placeholder="••••••••"
                                                autocomplete="new-password">
                                            <button type="button" class="btn btn-outline-secondary toggle-secret"
                                                data-target="aws_secret_access_key">
                                                <i class="ri-eye-fill"></i>
                                            </button>
                                        </div>
                                        <div class="text-danger small mt-1 field-error" id="error-aws_secret_access_key">
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <label for="aws_default_region" class="form-label">Default Region <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="aws_default_region" name="aws_default_region">
                                            @php
                                                $regions = [
                                                    'us-east-1',
                                                    'us-east-2',
                                                    'us-west-1',
                                                    'us-west-2',
                                                    'ap-south-1',
                                                    'ap-southeast-1',
                                                    'ap-southeast-2',
                                                    'ap-northeast-1',
                                                    'eu-west-1',
                                                    'eu-west-2',
                                                    'eu-central-1',
                                                    'sa-east-1',
                                                ];
                                            @endphp
                                            @foreach ($regions as $region)
                                                <option value="{{ $region }}"
                                                    {{ env('AWS_DEFAULT_REGION') === $region ? 'selected' : '' }}>
                                                    {{ $region }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="text-danger small mt-1 field-error" id="error-aws_default_region"></div>
                                    </div>

                                    <div class="col-lg-6">
                                        <label for="aws_bucket" class="form-label">Bucket Name</label>
                                        <input type="text" class="form-control" id="aws_bucket" name="aws_bucket"
                                            value="{{ env('AWS_BUCKET') }}" placeholder="my-app-bucket">
                                        <div class="text-danger small mt-1 field-error" id="error-aws_bucket"></div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="aws_use_path_style_endpoint"
                                                name="aws_use_path_style_endpoint"
                                                {{ env('AWS_USE_PATH_STYLE_ENDPOINT') === 'true' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="aws_use_path_style_endpoint">
                                                Use Path Style Endpoint
                                                <span class="text-muted fs-11">(required for MinIO and some S3-compatible
                                                    services)</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="hstack gap-2 justify-content-end">
                                            <button type="submit" class="btn btn-primary" id="saveBtn">
                                                <span class="btn-text"><i class="ri-save-line me-1"></i> Save Changes</span>
                                                <span class="btn-spinner d-none"><span
                                                        class="spinner-border spinner-border-sm me-1"></span> Saving…</span>
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

                axios.patch('{{ route('admin.settings.aws.update') }}', {
                        aws_access_key_id: document.getElementById('aws_access_key_id').value,
                        aws_secret_access_key: document.getElementById('aws_secret_access_key').value,
                        aws_default_region: document.getElementById('aws_default_region').value,
                        aws_bucket: document.getElementById('aws_bucket').value,
                        aws_use_path_style_endpoint: document.getElementById(
                            'aws_use_path_style_endpoint').checked ? 1 : 0,
                    })
                    .then(res => Toast.success(res.data.message))
                    .catch(function(err) {
                        const data = err.response?.data;
                        if (data?.errors) {
                            Object.entries(data.errors).forEach(([field, messages]) => {
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
