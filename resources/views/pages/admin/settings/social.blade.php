@extends('layout.master-layout')
@section('title', 'Social Media Settings')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Social Media</h4>
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Social Media</li>
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
                                <i class="ri-share-line me-2 text-primary"></i> Social Media Links
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="settingsForm">

                                @php
                                    $platforms = [
                                        [
                                            'name' => 'facebook',
                                            'label' => 'Facebook',
                                            'icon' => 'ri-facebook-fill',
                                            'color' => '#1877f2',
                                        ],
                                        [
                                            'name' => 'twitter',
                                            'label' => 'X (Twitter)',
                                            'icon' => 'ri-twitter-x-fill',
                                            'color' => '#000000',
                                        ],
                                        [
                                            'name' => 'instagram',
                                            'label' => 'Instagram',
                                            'icon' => 'ri-instagram-fill',
                                            'color' => '#e1306c',
                                        ],
                                        [
                                            'name' => 'linkedin',
                                            'label' => 'LinkedIn',
                                            'icon' => 'ri-linkedin-fill',
                                            'color' => '#0a66c2',
                                        ],
                                        [
                                            'name' => 'youtube',
                                            'label' => 'YouTube',
                                            'icon' => 'ri-youtube-fill',
                                            'color' => '#ff0000',
                                        ],
                                        [
                                            'name' => 'tiktok',
                                            'label' => 'TikTok',
                                            'icon' => 'ri-tiktok-fill',
                                            'color' => '#010101',
                                        ],
                                        [
                                            'name' => 'pinterest',
                                            'label' => 'Pinterest',
                                            'icon' => 'ri-pinterest-fill',
                                            'color' => '#e60023',
                                        ],
                                        [
                                            'name' => 'github',
                                            'label' => 'GitHub',
                                            'icon' => 'ri-github-fill',
                                            'color' => '#24292f',
                                        ],
                                        [
                                            'name' => 'telegram',
                                            'label' => 'Telegram',
                                            'icon' => 'ri-telegram-fill',
                                            'color' => '#229ed9',
                                        ],
                                        [
                                            'name' => 'discord',
                                            'label' => 'Discord',
                                            'icon' => 'ri-discord-fill',
                                            'color' => '#5865f2',
                                        ],
                                    ];
                                @endphp

                                <div class="row g-3">
                                    @foreach ($platforms as $p)
                                        <div class="col-lg-6">
                                            <label for="{{ $p['name'] }}" class="form-label">{{ $p['label'] }}</label>
                                            <div class="input-group">
                                                <span class="input-group-text"
                                                    style="background: {{ $p['color'] }}18; border-color: {{ $p['color'] }}40;">
                                                    <i class="{{ $p['icon'] }}" style="color: {{ $p['color'] }};"></i>
                                                </span>
                                                <input type="url" class="form-control" id="{{ $p['name'] }}"
                                                    name="{{ $p['name'] }}" value="{{ $s->{$p['name']} }}"
                                                    placeholder="https://{{ $p['name'] }}.com/yourpage">
                                            </div>
                                            <div class="text-danger small mt-1 field-error" id="error-{{ $p['name'] }}">
                                            </div>
                                        </div>
                                    @endforeach

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

            document.getElementById('settingsForm').addEventListener('submit', function(e) {
                e.preventDefault();

                document.querySelectorAll('.field-error').forEach(el => el.textContent = '');
                document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

                const btn = document.getElementById('saveBtn');
                btn.disabled = true;
                btn.querySelector('.btn-text').classList.add('d-none');
                btn.querySelector('.btn-spinner').classList.remove('d-none');

                axios.patch('{{ route('admin.settings.social.update') }}', {
                        facebook: document.getElementById('facebook').value,
                        twitter: document.getElementById('twitter').value,
                        instagram: document.getElementById('instagram').value,
                        linkedin: document.getElementById('linkedin').value,
                        youtube: document.getElementById('youtube').value,
                        tiktok: document.getElementById('tiktok').value,
                        pinterest: document.getElementById('pinterest').value,
                        github: document.getElementById('github').value,
                        telegram: document.getElementById('telegram').value,
                        discord: document.getElementById('discord').value,
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
