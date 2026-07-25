@extends('layout.master-layout')
@section('title', 'Sponsors')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            {{-- Page Header --}}
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Sponsors</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Sponsors</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <x-admin.flash-message />

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h5 class="card-title mb-0 flex-grow-1">Manage Sponsors</h5>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#sponsorModal" onclick="resetForm()">
                                <i class="ri-add-line align-bottom me-1"></i> Add Sponsor
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="sponsorsTable" class="table table-bordered align-middle table-nowrap">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th style="width: 90px;">Logo</th>
                                            <th>Sponsor Name</th>
                                            <th>Website URL</th>
                                            <th>Status</th>
                                            <th class="text-center" style="width: 150px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Sponsor Modal --}}
    <div class="modal fade" id="sponsorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow-lg overflow-hidden">

                {{-- Modal Header --}}
                <div class="modal-header bg-primary px-4 py-3 border-0">
                    <div class="d-flex align-items-center">
                        <div>
                            <h5 class="modal-title text-white fw-semibold mb-0" id="modalTitle">
                                Add Sponsor
                            </h5>
                        </div>
                    </div>

                    <button type="button" class="btn-close btn-close-white opacity-100 shadow-none" data-bs-dismiss="modal"
                        aria-label="Close">
                    </button>
                </div>

                <form id="sponsorForm" enctype="multipart/form-data">
                    <div class="modal-body px-4 py-4">

                        <input type="hidden" id="sponsorId">

                        <div class="row g-4">
                            {{-- Left Column --}}
                            <div class="col-md-7">

                                {{-- Sponsor Name --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        Sponsor Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="sponsorName" required
                                        placeholder="e.g. Acme Corporation">
                                </div>

                                {{-- Website URL --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        Website URL <span class="text-muted fw-normal">(Optional)</span>
                                    </label>
                                    <input type="url" class="form-control" id="sponsorWebsite"
                                        placeholder="https://example.com">
                                </div>

                                {{-- Description --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        Description <span class="text-muted fw-normal">(Optional)</span>
                                    </label>
                                    <textarea class="form-control" id="sponsorDescription" rows="4"
                                        placeholder="Write a short description about the sponsor..."></textarea>
                                </div>
                            </div>

                            {{-- Right Column --}}
                            <div class="col-md-5">

                                {{-- Logo Upload --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        Logo <span class="text-danger">*</span>
                                    </label>

                                    <div class="text-center">
                                        {{-- Logo Preview --}}
                                        <div class="logo-preview-container mb-3">
                                            <img id="logoPreview"
                                                src="{{ asset('admin/assets/images/default/no-image.png') }}"
                                                alt="Logo Preview"
                                                class="rounded border p-2"
                                                style="width: 160px; height: 160px; object-fit: contain; background: #f8f9fa;">
                                        </div>

                                        <div class="d-flex gap-2 justify-content-center">
                                            <label class="btn btn-outline-primary btn-sm">
                                                <i class="ri-upload-2-line align-bottom me-1"></i> Choose Logo
                                                <input type="file" class="d-none" id="sponsorLogo" accept="image/*">
                                            </label>
                                            <button type="button" class="btn btn-outline-danger btn-sm" id="removeLogoBtn"
                                                style="display: none;">
                                                <i class="ri-close-line"></i>
                                            </button>
                                        </div>

                                        <small class="text-muted d-block mt-2">
                                            Allowed: JPEG, PNG, WebP, SVG. Max 5MB
                                        </small>
                                    </div>
                                </div>

                                {{-- Status --}}
                                <div class="mt-2">
                                    <label class="form-label fw-semibold d-block mb-2">
                                        Status
                                    </label>
                                    <div class="form-check form-switch form-switch-md mt-1">
                                        <input class="form-check-input" type="checkbox" id="sponsorStatus" checked>
                                        <label class="form-check-label ms-2" for="sponsorStatus">
                                            Active
                                        </label>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="modal-footer border-0 bg-light px-4 py-3">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button type="submit" class="btn btn-primary px-4" id="saveBtn">
                            <i class="ri-save-line align-bottom me-1"></i>
                            Save Sponsor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            'use strict';

            // ── DataTable Initialisation ──────────────────────────────────────────
            const table = $('#sponsorsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.sponsors.data') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'logo',
                        name: 'logo',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'website_url',
                        name: 'website_url'
                    },
                    {
                        data: 'is_active',
                        name: 'is_active',
                        className: 'text-center'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                ],
                language: {
                    processing: '<div class="spinner-border spinner-border-sm text-primary"></div>',
                }
            });

            const sponsorForm = document.getElementById('sponsorForm');
            const sponsorModal = new bootstrap.Modal(document.getElementById('sponsorModal'));
            const logoInput = document.getElementById('sponsorLogo');
            const logoPreview = document.getElementById('logoPreview');
            const removeLogoBtn = document.getElementById('removeLogoBtn');

            let existingLogo = null; // Track existing logo for edit mode

            // ── Logo Preview ────────────────────────────────────────────────────
            logoInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        logoPreview.src = e.target.result;
                        removeLogoBtn.style.display = 'inline-block';
                    };
                    reader.readAsDataURL(file);
                }
            });

            removeLogoBtn.addEventListener('click', function() {
                logoInput.value = '';
                logoPreview.src = '{{ asset('admin/assets/images/default/no-image.png') }}';
                removeLogoBtn.style.display = 'none';
            });

            window.resetForm = function() {
                sponsorForm.reset();
                document.getElementById('sponsorId').value = '';
                document.getElementById('modalTitle').innerText = 'Add Sponsor';
                document.getElementById('saveBtn').innerHTML = '<i class="ri-save-line align-bottom me-1"></i> Save Sponsor';
                logoPreview.src = '{{ asset('admin/assets/images/default/no-image.png') }}';
                removeLogoBtn.style.display = 'none';
                existingLogo = null;
                document.getElementById('sponsorStatus').checked = true;
            };

            // ── Edit Sponsor ─────────────────────────────────────────────────────
            $(document).on('click', '.edit-btn', function() {
                const sponsor = $(this).data('sponsor');
                resetForm();

                document.getElementById('sponsorId').value = sponsor.id;
                document.getElementById('sponsorName').value = sponsor.name;
                document.getElementById('sponsorWebsite').value = sponsor.website_url || '';
                document.getElementById('sponsorDescription').value = sponsor.description || '';
                document.getElementById('sponsorStatus').checked = !!sponsor.is_active;
                document.getElementById('modalTitle').innerText = 'Edit Sponsor';
                document.getElementById('saveBtn').innerHTML = '<i class="ri-save-line align-bottom me-1"></i> Update Sponsor';

                if (sponsor.logo) {
                    const logoUrl = '/' + sponsor.logo;
                    logoPreview.src = logoUrl;
                    removeLogoBtn.style.display = 'inline-block';
                }

                existingLogo = sponsor.logo;
                sponsorModal.show();
            });

            // ── Submit Form ─────────────────────────────────────────────────────
            sponsorForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const id = document.getElementById('sponsorId').value;
                const url = id ?
                    `{{ url('/sponsors') }}/${id}` :
                    `{{ route('admin.sponsors.store') }}`;
                const method = id ? 'post' : 'post';

                const formData = new FormData();
                formData.append('name', document.getElementById('sponsorName').value);
                formData.append('website_url', document.getElementById('sponsorWebsite').value);
                formData.append('description', document.getElementById('sponsorDescription').value);
                formData.append('is_active', document.getElementById('sponsorStatus').checked ? '1' : '0');

                if (logoInput.files[0]) {
                    formData.append('logo', logoInput.files[0]);
                }

                if (id) {
                    formData.append('_method', 'PUT');
                }

                const config = {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                };

                axios[method](url, formData, config)
                    .then(res => {
                        Toast.success(res.data.message);
                        sponsorModal.hide();
                        table.draw(false);
                    })
                    .catch(err => {
                        const msg = err.response?.data?.message || 'Something went wrong.';
                        Toast.error(msg);
                    });
            });

            // ── Status Toggle ───────────────────────────────────────────────────
            $(document).on('click', '.toggle-status-btn', function() {
                const id = $(this).data('id');
                const isActive = $(this).data('active');
                const label = isActive ? 'deactivate' : 'activate';

                Alert.confirm(`Are you sure you want to ${label} this sponsor?`, {
                    type: 'warning',
                    confirmText: `Yes, ${label}`
                }).then(confirmed => {
                    if (!confirmed) return;

                    axios.patch(`{{ url('/sponsors') }}/${id}/toggle-status`)
                        .then(res => {
                            Toast.success(res.data.message);
                            table.draw(false);
                        })
                        .catch(err => {
                            Toast.error(err.response?.data?.message || 'Toggle failed.');
                        });
                });
            });

            // ── Delete Sponsor ──────────────────────────────────────────────────
            $(document).on('click', '.delete-btn', function() {
                const id = $(this).data('id');

                Alert.confirm('Are you sure you want to delete this sponsor? This action cannot be undone.', {
                    type: 'danger',
                    confirmText: 'Yes, delete it'
                }).then(confirmed => {
                    if (!confirmed) return;

                    axios.delete(`{{ url('/sponsors') }}/${id}`)
                        .then(res => {
                            Toast.success(res.data.message);
                            table.draw(false);
                        })
                        .catch(err => {
                            Toast.error(err.response?.data?.message || 'Delete failed.');
                        });
                });
            });

        })();
    </script>
@endpush

@push('styles')
    <style>
        #sponsorModal .modal-content {
            border-radius: 14px;
        }

        #sponsorModal .form-control {
            border-radius: 8px;
            min-height: 42px;
        }

        #sponsorModal textarea.form-control {
            min-height: 100px;
        }

        #sponsorModal .modal-footer .btn {
            min-width: 120px;
            border-radius: 8px;
            font-weight: 500;
        }

        #sponsorModal .btn-close:focus {
            box-shadow: none;
        }

        .logo-preview-container {
            transition: transform 0.2s ease;
        }

        .logo-preview-container:hover {
            transform: scale(1.02);
        }

        #sponsorModal .form-switch .form-check-input:checked {
            background-color: #0ab39c;
            border-color: #0ab39c;
        }

        #sponsorModal .form-switch .form-check-input:focus {
            border-color: #0ab39c;
            box-shadow: 0 0 0 0.15rem rgba(10, 179, 156, 0.25);
        }
    </style>
@endpush
