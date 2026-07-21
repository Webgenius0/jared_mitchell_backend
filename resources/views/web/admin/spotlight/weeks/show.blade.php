@extends('layout.master-layout')

@section('title', 'Spotlight Week Details')

@section('content')
@include('components.admin.flash-message')
<div class="page-content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Spotlight Week: Week {{ $week->week_number }} ({{ $week->year }})</h4>
                    <div class="page-title-right">
                        <a href="{{ route('admin.spotlight.weeks.index') }}?edit={{ $week->id }}" class="btn btn-soft-primary btn-sm">
                            <i class="ri-pencil-line me-1"></i> Edit
                        </a>
                        <a href="{{ route('admin.spotlight.weeks.applications', $week->id) }}" class="btn btn-soft-info">
                            <i class="ri-file-list-3-line me-1"></i> View Applications
                        </a>
                        <a href="{{ route('admin.spotlight.weeks.index') }}" class="btn btn-light">
                            <i class="ri-arrow-left-line me-1"></i> Back to Weeks
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Week Info Cards --}}
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">Week</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-calendar-line fs-24 text-primary"></i>
                            </div>
                        </div>
                        <h5 class="mt-2 mb-0">Week {{ $week->week_number }} ({{ $week->year }})</h5>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">Status</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-information-line fs-24 text-info"></i>
                            </div>
                        </div>
                        @php
                            $statusMap = [
                                'pending' => 'bg-warning-subtle text-warning',
                                'nominating' => 'bg-info-subtle text-info',
                                'voting' => 'bg-success-subtle text-success',
                                'voting_closed' => 'bg-secondary-subtle text-secondary',
                                'completed' => 'bg-primary-subtle text-primary',
                                'cancelled' => 'bg-danger-subtle text-danger',
                            ];
                            $statusClass = $statusMap[$week->status] ?? 'bg-secondary-subtle text-secondary';
                        @endphp
                        <h5 class="mt-2 mb-0"><span class="badge {{ $statusClass }} fs-6">{{ str_replace('_', ' ', ucfirst($week->status)) }}</span></h5>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">Voting Start</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-play-circle-line fs-24 text-success"></i>
                            </div>
                        </div>
                        <h5 class="mt-2 mb-0">{{ $week->voting_starts_at?->format('M d, Y h:i A') ?? '—' }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">Voting End</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-stop-circle-line fs-24 text-danger"></i>
                            </div>
                        </div>
                        <h5 class="mt-2 mb-0">{{ $week->voting_ends_at?->format('M d, Y h:i A') ?? '—' }}</h5>
                    </div>
                </div>
            </div>
        </div>

        {{-- Winner Info (if completed) --}}
        @if($week->winner_spotlightable_id)
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-success">
                    <div class="card-body d-flex align-items-center gap-3">
                        <i class="ri-trophy-fill fs-48 text-success"></i>
                        <div>
                            <h5 class="mb-1 text-success">Winner</h5>
                            @php
                                $winner = $week->nominees->firstWhere('is_winner', true);
                                $winnerName = $winner?->spotlightable?->business_name
                                    ?? $winner?->spotlightable?->artist_stage_name
                                    ?? $winner?->spotlightable?->brand_name
                                    ?? '—';
                            @endphp
                            <p class="mb-0 fs-5 fw-semibold">{{ $winnerName }}</p>
                            <small class="text-muted">
                                Total Votes: {{ number_format($winner?->total_vote_count ?? 0) }}
                                @if($week->announced_at)
                                    | Announced: {{ $week->announced_at->format('M d, Y h:i A') }}
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Week Actions — Status Transitions --}}
        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex flex-wrap gap-2 align-items-center">
                        <h6 class="mb-0 me-3 fw-semibold">Status Actions:</h6>

                        {{-- pending → nominating --}}
                        @if($week->status === 'pending')
                            <form action="{{ route('admin.spotlight.weeks.update-status', $week->id) }}" method="POST" class="d-inline" data-confirm="Move this week to <strong>Nominating</strong>? Applications will start being accepted." data-confirm-type="confirm">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="nominating">
                                <button type="submit" class="btn btn-soft-info btn-sm">
                                    <i class="ri-arrow-right-line me-1"></i> Start Nominating
                                </button>
                            </form>
                        @endif

                        {{-- pending → cancelled --}}
                        @if($week->status === 'pending')
                            <form action="{{ route('admin.spotlight.weeks.cancel', $week->id) }}" method="POST" class="d-inline" data-confirm="Cancel this week? This cannot be undone." data-confirm-type="danger">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-soft-danger btn-sm">
                                    <i class="ri-close-circle-line me-1"></i> Cancel Week
                                </button>
                            </form>
                        @endif

                        {{-- nominating → pending (revert) --}}
                        @if($week->status === 'nominating')
                            <form action="{{ route('admin.spotlight.weeks.update-status', $week->id) }}" method="POST" class="d-inline" data-confirm="Revert this week back to <strong>Pending</strong>? It will stop accepting applications." data-confirm-type="warning">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="pending">
                                <button type="submit" class="btn btn-soft-warning btn-sm">
                                    <i class="ri-arrow-go-back-line me-1"></i> Revert to Pending
                                </button>
                            </form>
                        @endif

                        {{-- nominating → cancelled --}}
                        @if($week->status === 'nominating')
                            <form action="{{ route('admin.spotlight.weeks.cancel', $week->id) }}" method="POST" class="d-inline" data-confirm="Cancel this week? This cannot be undone." data-confirm-type="danger">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-soft-danger btn-sm">
                                    <i class="ri-close-circle-line me-1"></i> Cancel Week
                                </button>
                            </form>
                        @endif

                        {{-- voting → close voting --}}
                        @if($week->status === 'voting')
                            <form action="{{ route('admin.spotlight.weeks.close-voting', $week->id) }}" method="POST" class="d-inline" data-confirm="Close voting for this week? This will stop accepting new votes." data-confirm-type="warning">
                                @csrf
                                <button type="submit" class="btn btn-soft-warning btn-sm">
                                    <i class="ri-stop-circle-line me-1"></i> Close Voting
                                </button>
                            </form>
                        @endif

                        {{-- voting → cancelled --}}
                        @if($week->status === 'voting')
                            <form action="{{ route('admin.spotlight.weeks.cancel', $week->id) }}" method="POST" class="d-inline" data-confirm="Cancel this week? All votes will be lost." data-confirm-type="danger">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-soft-danger btn-sm">
                                    <i class="ri-close-circle-line me-1"></i> Cancel Week
                                </button>
                            </form>
                        @endif

                        {{-- voting_closed → reopen voting --}}
                        @if($week->status === 'voting_closed')
                            <form action="{{ route('admin.spotlight.weeks.update-status', $week->id) }}" method="POST" class="d-inline" data-confirm="Reopen voting for this week?" data-confirm-type="confirm">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="voting">
                                <button type="submit" class="btn btn-soft-success btn-sm">
                                    <i class="ri-play-circle-line me-1"></i> Reopen Voting
                                </button>
                            </form>
                        @endif

                        {{-- voting_closed → announce winner button --}}
                        @if(in_array($week->status, ['voting', 'voting_closed']) && $nominations->count() > 0)
                            <button type="button" class="btn btn-soft-success btn-sm" data-bs-toggle="modal" data-bs-target="#announceWinnerModal">
                                <i class="ri-trophy-line me-1"></i> Announce Winner
                            </button>
                        @endif

                        {{-- voting_closed → cancelled --}}
                        @if($week->status === 'voting_closed')
                            <form action="{{ route('admin.spotlight.weeks.cancel', $week->id) }}" method="POST" class="d-inline" data-confirm="Cancel this week? This cannot be undone." data-confirm-type="danger">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-soft-danger btn-sm">
                                    <i class="ri-close-circle-line me-1"></i> Cancel Week
                                </button>
                            </form>
                        @endif

                        {{-- cancelled → pending (recover from cancel) --}}
                        @if($week->status === 'cancelled')
                            <form action="{{ route('admin.spotlight.weeks.update-status', $week->id) }}" method="POST" class="d-inline" data-confirm="Recover this cancelled week back to <strong>Pending</strong>?" data-confirm-type="warning">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="pending">
                                <button type="submit" class="btn btn-soft-warning btn-sm">
                                    <i class="ri-arrow-go-back-line me-1"></i> Recover to Pending
                                </button>
                            </form>
                        @endif

                        {{-- Delete (not for voting/completed) --}}
                        @if(!in_array($week->status, ['voting', 'completed']))
                            <form action="{{ route('admin.spotlight.weeks.destroy', $week->id) }}" method="POST" class="d-inline" data-confirm="Permanently delete this week? This cannot be undone." data-confirm-type="danger">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-soft-danger btn-sm">
                                    <i class="ri-delete-bin-line me-1"></i> Delete
                                </button>
                            </form>
                        @endif

                        {{-- If no status actions available --}}
                        @if($week->status === 'completed')
                            <span class="text-muted small"><i class="ri-information-line me-1"></i>No further actions available for completed weeks.</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Nominees Table --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Nominees ({{ $nominations->count() }})</h5>
                    </div>
                    <div class="card-body">
                        @if($nominations->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle table-nowrap mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th>Nominee</th>
                                            <th>Type</th>
                                            <th>Owner</th>
                                            <th class="text-center">Free Votes</th>
                                            <th class="text-center">Paid Votes</th>
                                            <th class="text-center">Total Votes</th>
                                            <th class="text-center">Rank</th>
                                            <th class="text-center">Winner</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($nominations as $nominee)
                                        <tr class="{{ $nominee->is_winner ? 'table-success' : '' }}">
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <strong>
                                                    @php
                                                        $isArtist = $nominee->spotlightable_type === 'App\Models\ArtistSpotlight';
                                                        $name = $isArtist
                                                            ? ($nominee->spotlightable?->artist_stage_name ?? $nominee->spotlightable?->full_legal_name ?? '#' . $nominee->id)
                                                            : ($nominee->spotlightable?->business_name ?? $nominee->spotlightable?->brand_name ?? '#' . $nominee->id);
                                                    @endphp
                                                    {{ $name }}
                                                </strong>
                                            </td>
                                            <td>
                                                @if($isArtist)
                                                    <span class="badge bg-light text-dark"><i class="ri-user-star-line me-1"></i>Artist</span>
                                                @else
                                                    <span class="badge bg-light text-dark"><i class="ri-store-2-line me-1"></i>Business</span>
                                                @endif
                                            </td>
                                            <td>{{ $nominee->user?->profile?->name ?? $nominee->user?->email ?? '—' }}</td>
                                            <td class="text-center">{{ number_format($nominee->free_vote_count) }}</td>
                                            <td class="text-center">{{ number_format($nominee->paid_vote_count) }}</td>
                                            <td class="text-center">
                                                <strong>{{ number_format($nominee->total_vote_count) }}</strong>
                                            </td>
                                            <td class="text-center">
                                                @if($nominee->rank)
                                                    <span class="badge bg-dark">#{{ $nominee->rank }}</span>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($nominee->is_winner)
                                                    <span class="badge bg-success"><i class="ri-trophy-fill me-1"></i>Winner</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="ri-user-unfollow-line fs-48 text-muted"></i>
                                <p class="mt-2 text-muted">No nominees selected yet for this week.</p>
                                @if(in_array($week->status, ['pending', 'nominating']))
                                    <a href="{{ route('admin.spotlight.weeks.applications', $week->id) }}" class="btn btn-primary btn-sm">
                                        <i class="ri-file-list-3-line me-1"></i> Review Applications
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Announce Winner Modal --}}
<div class="modal fade" id="announceWinnerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.spotlight.weeks.announce-winner', $week->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="ri-trophy-line me-2 text-warning"></i>Announce Winner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">Select the nominee to announce as the winner for this week. This will mark the week as completed.</p>
                    <div class="mb-3">
                        <label class="form-label">Select Winner <span class="text-danger">*</span></label>
                        <select name="nominee_id" class="form-select" required>
                            <option value="">-- Choose Nominee --</option>
                            @foreach($nominations as $nominee)
                                @php
                                    $isArtist = $nominee->spotlightable_type === 'App\Models\ArtistSpotlight';
                                    $name = $isArtist
                                        ? ($nominee->spotlightable?->artist_stage_name ?? $nominee->spotlightable?->full_legal_name ?? '#' . $nominee->id)
                                        : ($nominee->spotlightable?->business_name ?? $nominee->spotlightable?->brand_name ?? '#' . $nominee->id);
                                @endphp
                                <option value="{{ $nominee->id }}">
                                    {{ $name }} ({{ number_format($nominee->total_vote_count) }} votes)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="ri-trophy-fill me-1"></i> Announce Winner
                    </button>
                </div>
            </form>
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

        @if(session('info'))
            Toast.info(@json(session('info')));
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
                    confirmText: type === 'danger' ? 'Yes, confirm' : 'Yes, proceed',
                }).then(function (confirmed) {
                    if (confirmed) {
                        form.submit();
                    }
                });
            });
        });

        // ── Edit button — redirect to index and open edit modal ───────
        // The edit opens the index page with the edit modal; use inline edit for now
        // via a simple redirect to index with a query param
        // For now, clicking edit on show page goes to index
        // (Edit modal is on the index page via DataTable)
    })();
</script>
@endpush
