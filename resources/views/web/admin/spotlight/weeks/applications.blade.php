@extends('layout.master-layout')

@section('title', 'Applications for Week')

@section('content')
@include('components.admin.flash-message')
<div class="page-content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Applications: Week {{ $week->week_number }} ({{ $week->year }})</h4>
                    <div class="page-title-right">
                        <a href="{{ route('admin.spotlight.weeks.show', $week->id) }}" class="btn btn-light">
                            <i class="ri-arrow-left-line me-1"></i> Back to Week
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Week Info --}}
        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex flex-wrap align-items-center gap-3">
                        <div>
                            <span class="text-muted">Type:</span>
                            <strong>{{ ucfirst($week->type) }}</strong>
                        </div>
                        <div>
                            <span class="text-muted">Status:</span>
                            @php
                                $statusMap = [
                                    'pending' => 'bg-warning-subtle text-warning',
                                    'nominating' => 'bg-info-subtle text-info',
                                    'voting' => 'bg-success-subtle text-success',
                                    'completed' => 'bg-primary-subtle text-primary',
                                ];
                                $statusClass = $statusMap[$week->status] ?? 'bg-secondary-subtle text-secondary';
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ ucfirst($week->status) }}</span>
                        </div>
                        <div>
                            <span class="text-muted">Total Applications:</span>
                            <strong>{{ $applications->total() }}</strong>
                        </div>

                        @if(in_array($week->status, ['pending', 'nominating']) && $applications->count() > 0)
                            <div class="ms-auto">
                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#selectNomineesModal">
                                    <i class="ri-check-double-line me-1"></i> Select Nominees
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Applications Table --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">All Applications</h5>
                    </div>
                    <div class="card-body">
                        @if($applications->count() > 0)
                            <form id="selectNomineesForm" action="{{ route('admin.spotlight.weeks.select-nominees', $week->id) }}" method="POST">
                                @csrf
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle table-nowrap mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                @if(in_array($week->status, ['pending', 'nominating']))
                                                <th style="width: 40px;">
                                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                                </th>
                                                @endif
                                                <th style="width: 50px;">#</th>
                                                <th>Applicant</th>
                                                <th>Spotlight</th>
                                                <th>Status</th>
                                                <th>Applied Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($applications as $application)
                                            <tr>
                                                @if(in_array($week->status, ['pending', 'nominating']))
                                                <td>
                                                    @if($application->isSelected())
                                                        <span class="badge bg-success-subtle text-success"><i class="ri-check-line"></i></span>
                                                    @else
                                                        <input type="checkbox" class="form-check-input row-checkbox" name="nominee_ids[]" value="{{ $application->id }}">
                                                    @endif
                                                </td>
                                                @endif
                                                <td>{{ $loop->iteration + ($applications->currentPage() - 1) * $applications->perPage() }}</td>
                                                <td>
                                                    <div>
                                                        <strong>{{ $application->user?->profile?->name ?? $application->user?->email ?? '—' }}</strong>
                                                        <br><small class="text-muted">{{ $application->user?->email ?? '' }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    @php
                                                        $spotlightable = $application->spotlightable;
                                                        $name = $spotlightable?->business_name ?? $spotlightable?->brand_name ?? '—';
                                                    @endphp
                                                    {{ $name }}
                                                </td>
                                                <td>
                                                    @if($application->isPending())
                                                        <span class="badge bg-warning-subtle text-warning">Pending</span>
                                                    @elseif($application->isSelected())
                                                        <span class="badge bg-success-subtle text-success">Selected</span>
                                                    @elseif($application->status === 'rejected')
                                                        <span class="badge bg-danger-subtle text-danger">Rejected</span>
                                                    @else
                                                        <span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($application->status) }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $application->applied_at?->format('M d, Y h:i A') ?? $application->created_at->format('M d, Y h:i A') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </form>

                            <div class="mt-3">
                                {{ $applications->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="ri-inbox-line fs-48 text-muted"></i>
                                <p class="mt-2 text-muted">No applications submitted for this week yet.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Select Nominees Modal --}}
<div class="modal fade" id="selectNomineesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="ri-check-double-line me-2 text-success"></i>Select Nominees</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-0">
                    You are about to select <strong id="selectedCount">0</strong> nominee(s) for this week.
                    Selected applications will be marked as "Selected" and converted to nominees.
                    All other pending applications will be rejected.
                </p>
                <p class="text-warning mt-2 mb-0">
                    <i class="ri-alert-line me-1"></i> This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success" form="selectNomineesForm">
                    <i class="ri-check-double-line me-1"></i> Confirm Selection
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        'use strict';

        @if(session('success'))
            Toast.success(@json(session('success')));
        @endif

        @if(session('error'))
            Toast.error(@json(session('error')));
        @endif

        @if(session('warning'))
            Toast.warning(@json(session('warning')));
        @endif

        // Select All checkbox
        const selectAll = document.getElementById('selectAll');
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.row-checkbox');
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateSelectedCount();
            });
        }

        // Individual checkboxes
        document.querySelectorAll('.row-checkbox').forEach(cb => {
            cb.addEventListener('change', updateSelectedCount);
        });

        function updateSelectedCount() {
            const count = document.querySelectorAll('.row-checkbox:checked').length;
            const el = document.getElementById('selectedCount');
            if (el) el.textContent = count;

            // Update select all state
            if (selectAll) {
                const total = document.querySelectorAll('.row-checkbox').length;
                const checked = document.querySelectorAll('.row-checkbox:checked').length;
                selectAll.checked = total > 0 && checked === total;
                selectAll.indeterminate = checked > 0 && checked < total;
            }
        }

        // Initial count
        updateSelectedCount();

        @if ($errors->any())
            @foreach ($errors->all() as $error)
                Toast.error('{{ $error }}');
            @endforeach
        @endif
    })();
</script>
@endpush
