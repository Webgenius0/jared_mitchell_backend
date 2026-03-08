@extends('layout.master-layout')
@section('title', 'Maintenance')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Maintenance</h4>
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Maintenance</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="row">

                @include('pages.admin.settings._settings-nav')

                <div class="col-lg-9 col-xxl-10">
                    <div class="card" id="maintenanceCard">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0">
                                <i class="ri-tools-line me-2 text-primary"></i> Maintenance Mode
                            </h5>
                            <span id="statusBadge"
                                class="badge fs-12 {{ $s->maintenance_mode ? 'bg-danger' : 'bg-success' }}">
                                {{ $s->maintenance_mode ? '🔴 Active' : '🟢 Inactive' }}
                            </span>
                        </div>
                        <div class="card-body">
                            <form id="settingsForm">

                                <div class="row g-3">

                                    {{-- Toggle --}}
                                    <div class="col-12">
                                        <div class="d-flex align-items-center justify-content-between p-3 rounded border"
                                            id="toggleBox"
                                            style="background: {{ $s->maintenance_mode ? 'rgba(240,101,72,.06)' : 'rgba(10,179,156,.06)' }}">
                                            <div>
                                                <h6 class="mb-1" id="toggleLabel">
                                                    {{ $s->maintenance_mode ? 'Site is in Maintenance Mode' : 'Site is Live' }}
                                                </h6>
                                                <p class="text-muted mb-0 small" id="toggleSub">
                                                    {{ $s->maintenance_mode ? 'Only whitelisted IPs can access the site.' : 'Toggle to put the site into maintenance mode.' }}
                                                </p>
                                            </div>
                                            <div class="form-check form-switch form-switch-lg mb-0 ms-3">
                                                <input class="form-check-input" type="checkbox" id="maintenance_mode"
                                                    name="maintenance_mode" {{ $s->maintenance_mode ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Message --}}
                                    <div class="col-12">
                                        <label for="maintenance_message" class="form-label">
                                            Maintenance Message
                                            <span class="text-muted fs-11">(shown to visitors)</span>
                                        </label>
                                        <textarea class="form-control" id="maintenance_message" name="maintenance_message" rows="3" maxlength="500"
                                            placeholder="We're performing scheduled maintenance. We'll be back shortly!">{{ $s->maintenance_message }}</textarea>
                                        <div class="d-flex justify-content-between mt-1">
                                            <div class="text-danger small field-error" id="error-maintenance_message"></div>
                                            <small class="text-muted">
                                                <span id="charCount">{{ strlen($s->maintenance_message ?? '') }}</span>/500
                                            </small>
                                        </div>
                                    </div>

                                    {{-- Allowed IPs --}}
                                    <div class="col-12">
                                        <label for="maintenance_allowed_ips" class="form-label">
                                            Allowed IP Addresses
                                            <span class="text-muted fs-11">(comma-separated — these IPs bypass
                                                maintenance)</span>
                                        </label>
                                        <textarea class="form-control font-monospace" id="maintenance_allowed_ips" name="maintenance_allowed_ips" rows="2"
                                            placeholder="192.168.1.1, 203.0.113.10">{{ $s->maintenance_allowed_ips }}</textarea>
                                        <div class="form-text">
                                            Your current IP: <code id="myIp">detecting…</code>
                                            <button type="button" class="btn btn-link btn-sm p-0 ms-1" id="addMyIpBtn">
                                                Add my IP
                                            </button>
                                        </div>
                                        <div class="text-danger small mt-1 field-error" id="error-maintenance_allowed_ips">
                                        </div>
                                    </div>

                                    {{-- Warning when active --}}
                                    <div class="col-12" id="activeWarning"
                                        style="{{ $s->maintenance_mode ? '' : 'display: none;' }}">
                                        <div class="alert alert-warning alert-borderless d-flex gap-2 mb-0">
                                            <i class="ri-error-warning-line fs-16 mt-1 flex-shrink-0"></i>
                                            <span>
                                                <strong>Maintenance is ON.</strong>
                                                Regular users cannot access the site. Make sure your IP is whitelisted.
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

            // ── Toggle UI update ─────────────────────────────────────────
            document.getElementById('maintenance_mode').addEventListener('change', function() {
                const isOn = this.checked;

                document.getElementById('toggleBox').style.background =
                    isOn ? 'rgba(240,101,72,.06)' : 'rgba(10,179,156,.06)';

                document.getElementById('toggleLabel').textContent =
                    isOn ? 'Site is in Maintenance Mode' : 'Site is Live';

                document.getElementById('toggleSub').textContent =
                    isOn ? 'Only whitelisted IPs can access the site.' :
                    'Toggle to put the site into maintenance mode.';

                document.getElementById('statusBadge').textContent = isOn ? '🔴 Active' : '🟢 Inactive';
                document.getElementById('statusBadge').className = 'badge fs-12 ' + (isOn ? 'bg-danger' :
                    'bg-success');

                document.getElementById('activeWarning').style.display = isOn ? '' : 'none';
            });

            // ── Char counter ─────────────────────────────────────────────
            document.getElementById('maintenance_message').addEventListener('input', function() {
                document.getElementById('charCount').textContent = this.value.length;
            });

            // ── Detect current IP ─────────────────────────────────────────
            fetch('https://api.ipify.org?format=json')
                .then(r => r.json())
                .then(function(data) {
                    document.getElementById('myIp').textContent = data.ip;

                    document.getElementById('addMyIpBtn').addEventListener('click', function() {
                        const field = document.getElementById('maintenance_allowed_ips');
                        const existing = field.value.trim();
                        if (existing.includes(data.ip)) return; // already in list
                        field.value = existing ? existing + ', ' + data.ip : data.ip;
                    });
                })
                .catch(function() {
                    document.getElementById('myIp').textContent = 'unavailable';
                });

            // ── Form submit ──────────────────────────────────────────────
            document.getElementById('settingsForm').addEventListener('submit', function(e) {
                e.preventDefault();

                document.querySelectorAll('.field-error').forEach(el => el.textContent = '');

                const isOn = document.getElementById('maintenance_mode').checked;

                // Confirm before enabling maintenance
                if (isOn) {
                    Alert.confirm(
                        'Enabling this will block all regular visitors. Make sure your IP is in the whitelist.', {
                            title: 'Enable Maintenance Mode?',
                            type: 'danger',
                            confirmText: 'Yes, enable it'
                        }
                    ).then(function(confirmed) {
                        if (confirmed) submitForm();
                    });
                } else {
                    submitForm();
                }
            });

            function submitForm() {
                const btn = document.getElementById('saveBtn');
                btn.disabled = true;
                btn.querySelector('.btn-text').classList.add('d-none');
                btn.querySelector('.btn-spinner').classList.remove('d-none');

                axios.patch('{{ route('admin.settings.maintenance.update') }}', {
                        maintenance_mode: document.getElementById('maintenance_mode').checked ? 1 : 0,
                        maintenance_message: document.getElementById('maintenance_message').value,
                        maintenance_allowed_ips: document.getElementById('maintenance_allowed_ips').value,
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
            }

        });
    </script>
@endpush
