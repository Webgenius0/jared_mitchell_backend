@extends('layout.master-layout')

@section('title', 'Winners')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Winners</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Winners</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        {{-- Info banner explaining the flow --}}
        <div class="row">
            <div class="col-12">
                <div class="alert alert-soft-info border-0 d-flex align-items-start gap-2 mb-3">
                    <i class="ri-information-line fs-18 mt-1"></i>
                    <div>
                        <strong>How winners work:</strong> The scheduler finalizes the top 3 businesses of the final
                        round — it does not decide the winner. Confirm (or change) the winner here among the top 3.
                        Only after confirmation does the winner appear in the public API.
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Final Round — Top 3 Finalists</h5>
                    </div>

                    {{-- Filters --}}
                    <div class="card-body border-bottom pb-2">
                        <div class="row g-2 align-items-end">
                            <div class="col-xl-3 col-md-4">
                                <label class="form-label text-muted small mb-1">Season</label>
                                <select class="form-select" id="seasonFilter">
                                    <option value="">All Seasons</option>
                                    @foreach($seasons as $season)
                                        <option value="{{ $season->id }}">{{ $season->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-3 col-md-4">
                                <label class="form-label text-muted small mb-1">Round</label>
                                <select class="form-select" id="roundFilter" disabled>
                                    <option value="">Final Rounds</option>
                                </select>
                            </div>
                            <div class="col-xl-3 col-md-4">
                                <label class="form-label text-muted small mb-1">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="button" id="resetFilters" class="btn btn-soft-danger">
                                        <i class="ri-refresh-line me-1"></i> Reset Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="winnersTable" class="table table-bordered align-middle table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Season</th>
                                        <th>Round</th>
                                        <th>Business</th>
                                        <th>Points</th>
                                        <th>Rank</th>
                                        <th>Status</th>
                                        <th class="text-center" style="width: 180px;">Action</th>
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

        const table = $('#winnersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('admin.winners.index') }}',
                data: function (d) {
                    d.season_id = $('#seasonFilter').val();
                    d.round_id = $('#roundFilter').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'season', name: 'season' },
                { data: 'round', name: 'round' },
                { data: 'business', name: 'business' },
                { data: 'points', name: 'points', className: 'text-center' },
                { data: 'rank', name: 'rank', className: 'text-center' },
                { data: 'status', name: 'status', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
            ],
            language: {
                processing: '<div class="spinner-border spinner-border-sm text-primary"></div>',
                emptyTable: 'No finalized rounds found. Run the scheduler or select a different filter.',
            }
        });

        // ── Season filter → loads rounds (final rounds only) ──
        $('#seasonFilter').on('change', function () {
            const seasonId = this.value;
            const roundSelect = $('#roundFilter');

            roundSelect.html('<option value="">Final Rounds</option>').prop('disabled', true);

            if (seasonId) {
                axios.get('{{ route('admin.winners.rounds') }}', { params: { season_id: seasonId } })
                    .then(function (res) {
                        const finalRounds = (res.data || []).filter(function (r) { return r.is_final; });
                        finalRounds.forEach(function (round) {
                            const opt = document.createElement('option');
                            opt.value = round.id;
                            opt.textContent = 'Final: Round ' + round.round_number + ' — ' + (round.title || '');
                            roundSelect.append(opt);
                        });
                        roundSelect.prop('disabled', finalRounds.length === 0);
                    })
                    .catch(function () {
                        Toast.error('Failed to load rounds.');
                    });
            }

            table.ajax.reload();
        });

        // ── Round filter ──
        $('#roundFilter').on('change', function () {
            table.ajax.reload();
        });

        // ── Reset filters ──
        $('#resetFilters').on('click', function () {
            $('#seasonFilter').val('');
            $('#roundFilter').html('<option value="">Final Rounds</option>').prop('disabled', true);
            table.ajax.reload();
        });

        // ── Confirm / change winner ──
        $(document).on('click', '.confirm-winner-btn', function () {
            const roundId = $(this).data('round-id');
            const contestantId = $(this).data('contestant-id');
            const business = $(this).data('business');
            const isChange = $(this).text().indexOf('Change') !== -1;

            Alert.confirm(
                `This will make <strong>${business}</strong> the winner of this season. ` +
                (isChange ? 'The previous winner will be demoted to runner-up/finalist.' : 'Only after confirmation will this winner appear in the public API.'),
                {
                    title: isChange ? 'Change Winner?' : 'Confirm Winner?',
                    type: 'success',
                    confirmText: isChange ? 'Yes, change winner' : 'Yes, confirm winner'
                }
            ).then(confirmed => {
                if (!confirmed) return;

                axios.post(`{{ route('admin.winners.confirm-winner', ':round') }}`.replace(':round', roundId), {
                    contestant_id: contestantId
                })
                    .then(res => {
                        Toast.success(res.data.message);
                        table.ajax.reload(null, false);
                    })
                    .catch(err => {
                        Toast.error(err.response?.data?.message || 'Failed to confirm winner.');
                    });
            });
        });
    })();
</script>
@endpush
