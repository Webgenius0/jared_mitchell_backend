@extends('layout.master-layout')

@section('title', 'Application Details')

@section('content')
@include('components.admin.flash-message')
<div class="page-content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Application #{{ $application->id }}</h4>
                    <div class="page-title-right">
                        <a href="{{ route('admin.spotlight.applications.index') }}" class="btn btn-light">
                            <i class="ri-arrow-left-line me-1"></i> Back to Applications
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Main Details --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">Application Information</h5>
                        @php
                            $statusMap = [
                                'pending'   => 'bg-warning-subtle text-warning',
                                'selected'  => 'bg-success-subtle text-success',
                                'rejected'  => 'bg-danger-subtle text-danger',
                                'withdrawn' => 'bg-secondary-subtle text-secondary',
                            ];
                            $statusClass = $statusMap[$application->status] ?? 'bg-secondary-subtle text-secondary';
                        @endphp
                        <span class="badge {{ $statusClass }} fs-6">{{ ucfirst($application->status) }}</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-nowrap mb-0">
                                <tbody>
                                    <tr>
                                        <th style="width: 200px;" class="table-light">Application ID</th>
                                        <td>#{{ $application->id }}</td>
                                    </tr>
                                    <tr>
                                        <th class="table-light">Week</th>
                                        <td>
                                            @if($application->week)
                                                <a href="{{ route('admin.spotlight.weeks.show', $application->week->id) }}" class="fw-medium">
                                                    Week {{ $application->week->week_number }} ({{ $application->week->year }})
                                                </a>
                                                @php
                                                    $wStatusMap = [
                                                        'pending'   => 'bg-warning-subtle text-warning',
                                                        'nominating'=> 'bg-info-subtle text-info',
                                                        'voting'    => 'bg-success-subtle text-success',
                                                        'completed' => 'bg-primary-subtle text-primary',
                                                        'cancelled' => 'bg-danger-subtle text-danger',
                                                    ];
                                                    $wClass = $wStatusMap[$application->week->status] ?? 'bg-secondary-subtle text-secondary';
                                                @endphp
                                                <span class="badge {{ $wClass }} ms-2">{{ ucfirst($application->week->status) }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="table-light">Spotlight Type</th>
                                        <td>
                                            @php $isArtist = $application->spotlightable_type === 'App\Models\ArtistSpotlight'; @endphp
                                            <span class="badge bg-light text-dark fs-6">
                                                <i class="{{ $isArtist ? 'ri-user-star-line' : 'ri-store-2-line' }} me-1"></i>
                                                {{ $isArtist ? 'Artist Spotlight' : 'Business Spotlight' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="table-light">Spotlight Name</th>
                                        <td>
                                            @php
                                                $s = $application->spotlightable;
                                                $name = $s?->business_name ?? $s?->brand_name ?? $s?->artist_stage_name ?? '—';
                                            @endphp
                                            <strong>{{ $name }}</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="table-light">Applied At</th>
                                        <td>{{ $application->applied_at?->format('M d, Y h:i A') ?? $application->created_at->format('M d, Y h:i A') }}</td>
                                    </tr>
                                    @if($application->reviewed_at)
                                    <tr>
                                        <th class="table-light">Reviewed At</th>
                                        <td>{{ $application->reviewed_at->format('M d, Y h:i A') }}</td>
                                    </tr>
                                    @endif
                                    @if($application->reviewer_notes)
                                    <tr>
                                        <th class="table-light">Reviewer Notes</th>
                                        <td>{{ $application->reviewer_notes }}</td>
                                    </tr>
                                    @endif
                                    @if($application->reviewer)
                                    <tr>
                                        <th class="table-light">Reviewed By</th>
                                        <td>{{ $application->reviewer?->profile?->name ?? $application->reviewer?->email ?? '—' }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <th class="table-light">Created At</th>
                                        <td>{{ $application->created_at->format('M d, Y h:i A') }}</td>
                                    </tr>
                                    <tr>
                                        <th class="table-light">Updated At</th>
                                        <td>{{ $application->updated_at->format('M d, Y h:i A') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Spotlight Details Card --}}
                @if($application->spotlightable)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="{{ $isArtist ? 'ri-user-star-line' : 'ri-store-2-line' }} me-1"></i>
                            Spotlight Details
                        </h5>
                    </div>
                    <div class="card-body">
                        @php $s = $application->spotlightable; @endphp
                        <div class="table-responsive">
                            <table class="table table-bordered table-nowrap mb-0">
                                <tbody>
                                    @if($isArtist)
                                        <tr>
                                            <th style="width: 200px;" class="table-light">Stage Name</th>
                                            <td>{{ $s->artist_stage_name ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="table-light">Full Legal Name</th>
                                            <td>{{ $s->full_legal_name ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="table-light">Email</th>
                                            <td>{{ $s->email ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="table-light">City / State</th>
                                            <td>{{ $s->city ?? '—' }}, {{ $s->state ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="table-light">Status</th>
                                            <td>
                                                @php
                                                    $asMap = ['draft'=>'secondary','submitted'=>'warning','under_review'=>'info','approved'=>'success','rejected'=>'danger','featured'=>'primary'];
                                                    $asClass = $asMap[$s->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $asClass }}-subtle text-{{ $asClass }}">{{ ucfirst($s->status) }}</span>
                                            </td>
                                        </tr>
                                    @else
                                        <tr>
                                            <th style="width: 200px;" class="table-light">Business Name</th>
                                            <td>{{ $s->business_name ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="table-light">Owner / Founder</th>
                                            <td>{{ $s->owner_founder_name ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="table-light">Email</th>
                                            <td>{{ $s->email ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="table-light">City / State</th>
                                            <td>{{ $s->city ?? '—' }}, {{ $s->state ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="table-light">Category</th>
                                            <td>{{ $s->business_category ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="table-light">Status</th>
                                            <td>
                                                @php
                                                    $bsMap = ['draft'=>'secondary','submitted'=>'warning','under_review'=>'info','approved'=>'success','rejected'=>'danger'];
                                                    $bsClass = $bsMap[$s->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $bsClass }}-subtle text-{{ $bsClass }}">{{ ucfirst($s->status) }}</span>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                {{-- Applicant Info --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Applicant</h5>
                    </div>
                    <div class="card-body text-center">
                        @php
                            // Resolve applicant name: profile → spotlight → email
                            $s = $application->spotlightable;
                            $spotlightName = $s?->business_name
                                ?? $s?->brand_name
                                ?? $s?->artist_stage_name
                                ?? $s?->full_legal_name
                                ?? null;
                            $applicantName = $application->user?->profile?->name
                                ?? $spotlightName
                                ?? $application->user?->email
                                ?? '—';
                        @endphp
                        <img src="{{ $application->user?->profile?->avatar_url ?? asset('admin/default/user.jpg') }}"
                            class="rounded-circle avatar-lg mb-3 object-fit-cover"
                            alt="User Avatar"
                            style="width: 80px; height: 80px;">
                        <h6>{{ $applicantName }}</h6>
                        <p class="text-muted mb-1">
                            <i class="ri-mail-line me-1"></i>{{ $application->user?->email ?? '—' }}
                        </p>
                        @if($application->user?->phone)
                            <p class="text-muted mb-0">
                                <i class="ri-phone-line me-1"></i>{{ $application->user->phone }}
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Actions</h5>
                    </div>
                    <div class="card-body">
                        @if($application->isPending())
                            @php
                                $weekCanAccept = $application->week && in_array($application->week->status, ['pending', 'nominating']);
                            @endphp

                            @if(!$weekCanAccept)
                                <div class="alert alert-warning mb-3 py-2 px-3">
                                    <i class="ri-alert-line me-1"></i>
                                    <small>The associated week is not accepting nominees (status: <strong>{{ $application->week?->status ?? 'N/A' }}</strong>).</small>
                                </div>
                            @endif

                            {{-- Approve Button — creates nominee record --}}
                            @if($weekCanAccept)
                                <form action="{{ route('admin.spotlight.applications.approve', $application->id) }}" method="POST" class="mb-3"
                                    data-confirm="Approve this application and create a nominee record? This will add <strong>{{ e($name) }}</strong> to the week's nominees.">
                                    @csrf
                                    <div class="mb-2">
                                        <label class="form-label">Approval Notes (optional)</label>
                                        <textarea name="reviewer_notes" class="form-control" rows="2" placeholder="Notes for approving this application..."></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="ri-check-double-line me-1"></i> Approve & Add as Nominee
                                    </button>
                                </form>
                            @endif

                            <hr class="my-2">

                            {{-- Reject Button --}}
                            <form action="{{ route('admin.spotlight.applications.update-status', $application->id) }}" method="POST"
                                data-confirm="Reject this application? <strong>{{ e($name) }}</strong> will not be added to this week's vote. The applicant can re-apply to future weeks."
                                data-confirm-type="danger">
                                @csrf
                                <input type="hidden" name="status" value="rejected">
                                <div class="mb-2">
                                    <label class="form-label">Rejection Reason (optional)</label>
                                    <textarea name="reviewer_notes" class="form-control" rows="2" placeholder="Why is this application being rejected?"></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="ri-close-circle-line me-1"></i> Reject Application
                                </button>
                            </form>

                        @elseif(in_array($application->status, ['selected', 'rejected']))
                            {{-- Revert to Pending --}}
                            <div class="alert alert-info mb-3 py-2 px-3">
                                <i class="ri-information-line me-1"></i>
                                <small>
                                    @if($application->isSelected())
                                        This application was <strong>approved</strong> and added as a nominee.
                                        @if($existingNominee)
                                            <a href="{{ route('admin.spotlight.weeks.show', $application->spotlight_week_id) }}" class="alert-link">View nominee in week</a>.
                                        @endif
                                    @else
                                        This application was <strong>rejected</strong>.
                                    @endif
                                </small>
                            </div>

                            @if($application->isSelected() && $existingNominee && $application->week?->status === 'voting')
                                <div class="alert alert-warning py-2 px-3 mb-3">
                                    <i class="ri-alert-line me-1"></i>
                                    <small>Voting is already open for this week. You must close voting and remove the nominee before reverting.</small>
                                </div>
                            @else
                                <form action="{{ route('admin.spotlight.applications.update-status', $application->id) }}" method="POST"
                                    data-confirm="Revert this application back to pending? It will be reviewable again and can be approved or rejected again."
                                    data-confirm-type="warning">
                                    @csrf
                                    <input type="hidden" name="status" value="pending">
                                    <button type="submit" class="btn btn-soft-warning w-100">
                                        <i class="ri-arrow-go-back-line me-1"></i> Revert to Pending
                                    </button>
                                </form>
                            @endif

                        @elseif($application->status === 'withdrawn')
                            <div class="alert alert-secondary mb-0 py-2 px-3">
                                <i class="ri-information-line me-1"></i>
                                <small>This application was withdrawn by the applicant. No actions available.</small>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Week Info --}}
                @if($application->week)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Week Info</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-1">
                            <span class="text-muted">Week:</span>
                            <strong>{{ $application->week->week_number }} ({{ $application->week->year }})</strong>
                        </p>
                        <p class="mb-1">
                            <span class="text-muted">Status:</span>
                            <span class="badge {{ $wClass ?? 'bg-secondary' }}">{{ ucfirst($application->week->status) }}</span>
                        </p>
                        @if($application->week->voting_starts_at)
                        <p class="mb-1">
                            <span class="text-muted">Voting Start:</span>
                            <strong>{{ $application->week->voting_starts_at->format('M d, Y') }}</strong>
                        </p>
                        @endif
                        @if($application->week->voting_ends_at)
                        <p class="mb-0">
                            <span class="text-muted">Voting End:</span>
                            <strong>{{ $application->week->voting_ends_at->format('M d, Y') }}</strong>
                        </p>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Nominee Info (if approved) --}}
                @if($application->isSelected() && $existingNominee)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Nominee Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-around text-center">
                            <div>
                                <p class="text-muted small mb-0">Free Votes</p>
                                <h4 class="mb-0">{{ number_format($existingNominee->free_vote_count) }}</h4>
                            </div>
                            <div>
                                <p class="text-muted small mb-0">Paid Votes</p>
                                <h4 class="mb-0">{{ number_format($existingNominee->paid_vote_count) }}</h4>
                            </div>
                            <div>
                                <p class="text-muted small mb-0">Total</p>
                                <h4 class="mb-0">{{ number_format($existingNominee->total_vote_count) }}</h4>
                            </div>
                        </div>
                        @if($existingNominee->rank)
                        <p class="text-center mt-2 mb-0">
                            <span class="badge bg-dark">Rank #{{ $existingNominee->rank }}</span>
                            @if($existingNominee->is_winner)
                                <span class="badge bg-success"><i class="ri-trophy-fill me-1"></i> Winner</span>
                            @endif
                        </p>
                        @endif
                    </div>
                </div>
                @endif
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

        @if ($errors->any())
            @foreach ($errors->all() as $error)
                Toast.error('{{ $error }}');
            @endforeach
        @endif

        // ── SweetAlert for any form with data-confirm ──────────────────
        document.querySelectorAll('form[data-confirm]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                var message = form.getAttribute('data-confirm');
                var type = form.getAttribute('data-confirm-type') || 'confirm';

                Alert.confirm(message, {
                    type: type,
                    confirmText: type === 'danger' ? 'Yes, reject' : type === 'warning' ? 'Yes, revert' : 'Yes, approve',
                }).then(function (confirmed) {
                    if (confirmed) {
                        form.submit();
                    }
                });
            });
        });

    })();
</script>
@endpush
