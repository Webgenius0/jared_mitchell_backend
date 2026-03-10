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
                @include('web.admin.settings._settings-nav')

                <div class="col-lg-9 col-xxl-10">

                    {{-- ── Maintenance Mode ──────────────────────────────────── --}}
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
                            <form id="maintenanceForm">
                                <div class="row g-3">

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
                                                    {{ $s->maintenance_mode ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label for="maintenance_message" class="form-label">
                                            Maintenance Message <span class="text-muted fs-11">(shown to visitors)</span>
                                        </label>
                                        <textarea class="form-control" id="maintenance_message" rows="3" maxlength="500"
                                            placeholder="We're performing scheduled maintenance. We'll be back shortly!">{{ $s->maintenance_message }}</textarea>
                                        <div class="d-flex justify-content-between mt-1">
                                            <div class="text-danger small field-error" id="error-maintenance_message"></div>
                                            <small class="text-muted"><span
                                                    id="charCount">{{ strlen($s->maintenance_message ?? '') }}</span>/500</small>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label for="maintenance_allowed_ips" class="form-label">
                                            Allowed IP Addresses
                                            <span class="text-muted fs-11">(comma-separated — bypass maintenance)</span>
                                        </label>
                                        <textarea class="form-control font-monospace" id="maintenance_allowed_ips" rows="2"
                                            placeholder="192.168.1.1, 203.0.113.10">{{ $s->maintenance_allowed_ips }}</textarea>
                                        <div class="form-text">
                                            Your current IP: <code id="myIp">detecting…</code>
                                            <button type="button" class="btn btn-link btn-sm p-0 ms-1" id="addMyIpBtn">Add
                                                my IP</button>
                                        </div>
                                        <div class="text-danger small mt-1 field-error" id="error-maintenance_allowed_ips">
                                        </div>
                                    </div>

                                    <div class="col-12" id="activeWarning"
                                        style="{{ $s->maintenance_mode ? '' : 'display:none;' }}">
                                        <div class="alert alert-warning alert-borderless d-flex gap-2 mb-0">
                                            <i class="ri-error-warning-line fs-16 mt-1 flex-shrink-0"></i>
                                            <span><strong>Maintenance is ON.</strong> Regular users cannot access the site.
                                                Make sure your IP is whitelisted.</span>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="hstack gap-2 justify-content-end">
                                            <button type="submit" class="btn btn-primary" id="maintenanceSaveBtn">
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

                    {{-- ── Artisan Commands ──────────────────────────────────── --}}
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ri-terminal-box-line me-2 text-primary"></i> Artisan Commands
                            </h5>
                        </div>
                        <div class="card-body">

                            {{-- Output console --}}
                            <div id="artisanOutput" class="d-none mb-3">
                                <label class="form-label fw-medium">Output</label>
                                <pre class="rounded p-3 mb-0 small" id="artisanOutputText"
                                    style="background:#1e1e2e; color:#cdd6f4; min-height:60px; max-height:200px; overflow-y:auto;"></pre>
                            </div>

                            @php
                                $commands = [
                                    'safe' => [
                                        [
                                            'cmd' => 'cache:clear',
                                            'label' => 'Cache Clear',
                                            'icon' => 'ri-delete-bin-line',
                                            'desc' => 'Clears the application cache',
                                        ],
                                        [
                                            'cmd' => 'config:clear',
                                            'label' => 'Config Clear',
                                            'icon' => 'ri-settings-line',
                                            'desc' => 'Clears the config cache',
                                        ],
                                        [
                                            'cmd' => 'config:cache',
                                            'label' => 'Config Cache',
                                            'icon' => 'ri-settings-fill',
                                            'desc' => 'Creates a config cache file',
                                        ],
                                        [
                                            'cmd' => 'route:clear',
                                            'label' => 'Route Clear',
                                            'icon' => 'ri-road-map-line',
                                            'desc' => 'Clears the route cache',
                                        ],
                                        [
                                            'cmd' => 'route:cache',
                                            'label' => 'Route Cache',
                                            'icon' => 'ri-road-map-fill',
                                            'desc' => 'Creates a route cache file',
                                        ],
                                        [
                                            'cmd' => 'view:clear',
                                            'label' => 'View Clear',
                                            'icon' => 'ri-eye-off-line',
                                            'desc' => 'Clears all compiled view files',
                                        ],
                                        [
                                            'cmd' => 'view:cache',
                                            'label' => 'View Cache',
                                            'icon' => 'ri-eye-line',
                                            'desc' => 'Compiles all blade templates',
                                        ],
                                        [
                                            'cmd' => 'optimize',
                                            'label' => 'Optimize',
                                            'icon' => 'ri-rocket-line',
                                            'desc' => 'Caches config, routes and views',
                                        ],
                                        [
                                            'cmd' => 'optimize:clear',
                                            'label' => 'Optimize Clear',
                                            'icon' => 'ri-refresh-line',
                                            'desc' => 'Clears all cached files',
                                        ],
                                        [
                                            'cmd' => 'event:clear',
                                            'label' => 'Event Clear',
                                            'icon' => 'ri-calendar-close-line',
                                            'desc' => 'Clears all cached events',
                                        ],
                                        [
                                            'cmd' => 'queue:restart',
                                            'label' => 'Queue Restart',
                                            'icon' => 'ri-loop-right-line',
                                            'desc' => 'Restarts queue worker after job',
                                        ],
                                        [
                                            'cmd' => 'storage:link',
                                            'label' => 'Storage Link',
                                            'icon' => 'ri-links-line',
                                            'desc' => 'Creates the public storage symlink',
                                        ],
                                    ],
                                    'danger' => [
                                        [
                                            'cmd' => 'migrate',
                                            'label' => 'Migrate',
                                            'icon' => 'ri-database-2-line',
                                            'desc' => 'Run pending database migrations',
                                        ],
                                        [
                                            'cmd' => 'migrate:rollback',
                                            'label' => 'Migrate Rollback',
                                            'icon' => 'ri-arrow-go-back-line',
                                            'desc' => 'Rollback the last migration batch',
                                        ],
                                        [
                                            'cmd' => 'migrate:fresh',
                                            'label' => 'Migrate Fresh',
                                            'icon' => 'ri-delete-bin-2-line',
                                            'desc' => '⚠ Drops ALL tables and re-runs migrations',
                                        ],
                                        [
                                            'cmd' => 'db:seed',
                                            'label' => 'DB Seed',
                                            'icon' => 'ri-database-fill',
                                            'desc' => 'Run the database seeders',
                                        ],
                                    ],
                                ];
                            @endphp

                            {{-- Safe Commands --}}
                            <p class="text-muted text-uppercase fw-semibold fs-12 mb-3">Safe Commands</p>
                            <div class="row g-2 mb-4">
                                @foreach ($commands['safe'] as $c)
                                    <div class="col-lg-3 col-md-4 col-sm-6">
                                        <button type="button"
                                            class="btn btn-soft-primary w-100 artisan-btn text-start px-3 py-2"
                                            data-command="{{ $c['cmd'] }}" title="{{ $c['desc'] }}">
                                            <i class="{{ $c['icon'] }} me-2"></i>
                                            <span class="fw-medium small">{{ $c['label'] }}</span>
                                        </button>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Danger Zone --}}
                            {{-- <div class="alert alert-danger alert-borderless d-flex gap-2 mb-3">
                                <i class="ri-error-warning-fill fs-16 mt-1 flex-shrink-0"></i>
                                <span>
                                    <strong>Danger Zone.</strong>
                                    Commands below can cause <strong>data loss</strong> or break your application.
                                    Only run these if you know exactly what you're doing.
                                    <code>migrate:fresh</code> will <strong>wipe your entire database</strong>.
                                </span>
                            </div> --}}

                            {{-- <p class="text-muted text-uppercase fw-semibold fs-12 mb-3">Dangerous Commands</p>
                            <div class="row g-2">
                                @foreach ($commands['danger'] as $c)
                                    <div class="col-lg-3 col-md-4 col-sm-6">
                                        <button type="button"
                                            class="btn btn-soft-danger w-100 artisan-btn text-start px-3 py-2"
                                            data-command="{{ $c['cmd'] }}" data-danger="true"
                                            title="{{ $c['desc'] }}">
                                            <i class="{{ $c['icon'] }} me-2"></i>
                                            <span class="fw-medium small">{{ $c['label'] }}</span>
                                        </button>
                                    </div>
                                @endforeach
                            </div> --}}

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

            // ── Maintenance toggle UI ────────────────────────────────────
            document.getElementById('maintenance_mode').addEventListener('change', function() {
                const isOn = this.checked;
                document.getElementById('toggleBox').style.background = isOn ? 'rgba(240,101,72,.06)' :
                    'rgba(10,179,156,.06)';
                document.getElementById('toggleLabel').textContent = isOn ? 'Site is in Maintenance Mode' :
                    'Site is Live';
                document.getElementById('toggleSub').textContent = isOn ?
                    'Only whitelisted IPs can access the site.' :
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
                        if (existing.includes(data.ip)) return;
                        field.value = existing ? existing + ', ' + data.ip : data.ip;
                    });
                })
                .catch(() => document.getElementById('myIp').textContent = 'unavailable');

            // ── Maintenance form submit ───────────────────────────────────
            document.getElementById('maintenanceForm').addEventListener('submit', function(e) {
                e.preventDefault();
                document.querySelectorAll('.field-error').forEach(el => el.textContent = '');

                const isOn = document.getElementById('maintenance_mode').checked;

                const doSave = function() {
                    const btn = document.getElementById('maintenanceSaveBtn');
                    btn.disabled = true;
                    btn.querySelector('.btn-text').classList.add('d-none');
                    btn.querySelector('.btn-spinner').classList.remove('d-none');

                    axios.patch('{{ route('admin.settings.maintenance.update') }}', {
                            maintenance_mode: isOn ? 1 : 0,
                            maintenance_message: document.getElementById('maintenance_message')
                                .value,
                            maintenance_allowed_ips: document.getElementById(
                                'maintenance_allowed_ips').value,
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
                            const btn = document.getElementById('maintenanceSaveBtn');
                            btn.disabled = false;
                            btn.querySelector('.btn-text').classList.remove('d-none');
                            btn.querySelector('.btn-spinner').classList.add('d-none');
                        });
                };

                if (isOn) {
                    Alert.confirm(
                        'Enabling this will block all regular visitors. Make sure your IP is in the whitelist.', {
                            title: 'Enable Maintenance Mode?',
                            type: 'danger',
                            confirmText: 'Yes, enable it'
                        }
                    ).then(confirmed => {
                        if (confirmed) doSave();
                    });
                } else {
                    doSave();
                }
            });

            // ── Artisan command runner ────────────────────────────────────
            const outputBox = document.getElementById('artisanOutput');
            const outputText = document.getElementById('artisanOutputText');

            document.querySelectorAll('.artisan-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const command = this.dataset.command;
                    const isDanger = this.dataset.danger === 'true';
                    const label = this.querySelector('span').textContent.trim();

                    const runCommand = function() {
                        // Disable all buttons during run
                        document.querySelectorAll('.artisan-btn').forEach(b => b.disabled =
                            true);
                        outputBox.classList.remove('d-none');
                        outputText.textContent = '$ php artisan ' + command + '\n\nRunning…';

                        axios.post('{{ route('admin.settings.artisan.run') }}', {
                                command
                            })
                            .then(function(res) {
                                outputText.textContent = '$ php artisan ' + command +
                                    '\n\n' + res.data.data.output;
                                Toast.success(res.data.message);
                            })
                            .catch(function(err) {
                                const msg = err.response?.data?.message ||
                                'Command failed.';
                                outputText.textContent = '$ php artisan ' + command +
                                    '\n\nERROR: ' + msg;
                                Toast.error(msg);
                            })
                            .finally(function() {
                                document.querySelectorAll('.artisan-btn').forEach(b => b
                                    .disabled = false);
                                // Scroll output to bottom
                                outputText.parentElement.scrollTop = outputText
                                    .parentElement.scrollHeight;
                            });
                    };

                    if (isDanger) {
                        Alert.confirm(
                            '<code>' + command +
                            '</code> can cause irreversible changes. Are you sure?', {
                                title: 'Run ' + label + '?',
                                type: 'danger',
                                confirmText: 'Yes, run it'
                            }
                        ).then(confirmed => {
                            if (confirmed) runCommand();
                        });
                    } else {
                        runCommand();
                    }
                });
            });

        });
    </script>
@endpush
