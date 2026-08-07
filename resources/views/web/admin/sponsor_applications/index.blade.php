@extends('layout.master-layout')
@section('title', 'Sponsor Applications')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            {{-- Page Header --}}
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Sponsor Applications</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Sponsor Applications</li>
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
                            <h5 class="card-title mb-0 flex-grow-1">Manage Sponsor Applications</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="sponsorApplicationsTable" class="table table-bordered align-middle table-nowrap">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th style="width: 90px;">Image</th>
                                            <th>Full Name</th>
                                            <th>Email</th>
                                            <th>Phone Number</th>
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

    {{-- Application Details Modal --}}
    <div class="modal fade" id="applicationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg overflow-hidden">

                {{-- Modal Header --}}
                <div class="modal-header bg-primary px-4 py-3 border-0">
                    <div class="d-flex align-items-center">
                        <div>
                            <h5 class="modal-title text-white fw-semibold mb-0">
                                Sponsor Application Details
                            </h5>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white opacity-100 shadow-none" data-bs-dismiss="modal"
                        aria-label="Close">
                    </button>
                </div>

                <div class="modal-body px-4 py-4">
                    <div class="row g-4">
                        {{-- Image Column --}}
                        <div class="col-md-4 text-center">
                            <img id="detailImage" src="{{ asset('admin/assets/images/default/no-image.png') }}"
                                alt="Sponsor Image" class="rounded border p-2 img-fluid"
                                style="max-height: 200px; object-fit: contain; background: #f8f9fa;">
                        </div>

                        {{-- Details Column --}}
                        <div class="col-md-8">
                            <table class="table table-sm table-borderless">
                                <tbody>
                                    <tr>
                                        <th style="width: 150px;">Full Name:</th>
                                        <td id="detailName"></td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td id="detailEmail"></td>
                                    </tr>
                                    <tr>
                                        <th>Phone Number:</th>
                                        <td id="detailPhone"></td>
                                    </tr>
                                    <tr>
                                        <th>Sponsor Title:</th>
                                        <td id="detailTitle"></td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="mt-3">
                                <h6>Why Sponsor:</h6>
                                <div id="detailWhy" class="p-3 bg-light rounded text-muted" style="min-height: 80px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="modal-footer border-0 bg-light px-4 py-3">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            'use strict';

            // ── DataTable Initialisation ──────────────────────────────────────────
            const table = $('#sponsorApplicationsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.sponsor-applications.data') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'sponsor_image',
                        name: 'sponsor_image',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'full_name',
                        name: 'full_name'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'phone_number',
                        name: 'phone_number'
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

            const applicationModal = new bootstrap.Modal(document.getElementById('applicationModal'));

            // ── View Application ─────────────────────────────────────────────────────
            $(document).on('click', '.view-btn', function() {
                const application = $(this).data('application');
                
                document.getElementById('detailName').innerText = application.full_name || '—';
                document.getElementById('detailEmail').innerText = application.email || '—';
                document.getElementById('detailPhone').innerText = application.phone_number || '—';
                document.getElementById('detailTitle').innerText = application.sponsor_title || '—';
                document.getElementById('detailWhy').innerText = application.why_sponsor || '—';

                const imgEl = document.getElementById('detailImage');
                if (application.sponsor_image) {
                    imgEl.src = '/' + application.sponsor_image;
                } else {
                    imgEl.src = '{{ asset('admin/assets/images/default/no-image.png') }}';
                }

                applicationModal.show();
            });

            // ── Delete Application ──────────────────────────────────────────────────
            $(document).on('click', '.delete-btn', function() {
                const id = $(this).data('id');

                Alert.confirm('Are you sure you want to delete this application? This action cannot be undone.', {
                    type: 'danger',
                    confirmText: 'Yes, delete it'
                }).then(confirmed => {
                    if (!confirmed) return;

                    axios.delete(`{{ url('/sponsor-applications') }}/${id}`)
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
        #applicationModal .modal-content {
            border-radius: 14px;
        }

        #applicationModal .btn-close:focus {
            box-shadow: none;
        }
    </style>
@endpush
