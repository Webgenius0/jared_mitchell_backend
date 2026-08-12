<div class="table-responsive">
    <table class="table table-bordered table-nowrap align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th style="width: 40px;">
                    <input type="checkbox" class="form-check-input" id="selectAll">
                </th>
                <th style="width: 50px;">#</th>
                <th>Business</th>
                <th>Owner</th>
                <th>Season</th>
                <th class="text-center" style="width: 90px;">AI Rating</th>
                <th>Status</th>
                <th>Applied Date</th>
                <th class="text-center" style="width: 160px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($applications as $application)
            <tr>
                <td>
                    <input type="checkbox" class="form-check-input row-checkbox" value="{{ $application->id }}">
                </td>
                <td>{{ $loop->iteration + ($applications->currentPage() - 1) * $applications->perPage() }}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="fs-14 mb-1">{{ $application->business?->business_name ?? '—' }}</h6>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="fs-14 mb-1">{{ $application->business?->user?->profile?->name ?? '—' }}</h6>
                            <p class="text-muted mb-0">{{ $application->business?->user?->email ?? '—' }}</p>
                        </div>
                    </div>
                </td>
                <td>{{ $application->season?->title ?? '—' }}</td>
                <td class="text-center">
                    @if($application->ai_score !== null)
                        @php
                            $score = (float) $application->ai_score;
                            $scoreClass = $score >= 70
                                ? 'bg-success-subtle text-success'
                                : ($score >= 50
                                    ? 'bg-warning-subtle text-warning'
                                    : 'bg-danger-subtle text-danger');
                        @endphp
                        <span class="badge {{ $scoreClass }}"
                              title="AI reviewed {{ $application->ai_reviewed_at?->format('M d, Y h:i A') ?? '' }}">
                            {{ number_format($score, 1) }}
                        </span>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    @if($application->status == 'pending')
                        <span class="badge bg-warning-subtle text-warning">Pending</span>
                    @elseif($application->status == 'approved')
                        <span class="badge bg-success-subtle text-success">Approved</span>
                    @elseif($application->status == 'rejected')
                        <span class="badge bg-danger-subtle text-danger">Cancelled</span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($application->status) }}</span>
                    @endif
                </td>
                <td>{{ $application->created_at->format('M d, Y h:i A') }}</td>
                <td class="text-center">
                    <div class="d-flex gap-2 justify-content-center">
                        {{-- View --}}
                        <button type="button" class="btn btn-sm btn-soft-info view-btn"
                            data-id="{{ $application->id }}" title="View Details">
                            <i class="ri-eye-fill"></i>
                        </button>

                        {{-- Single Status Dropdown --}}
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-soft-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ri-edit-2-line"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @if($application->status !== 'approved')
                                <li>
                                    <a class="dropdown-item status-update-btn text-success" href="#"
                                       data-id="{{ $application->id }}" data-status="approved">
                                        <i class="ri-check-line me-2"></i>Approve
                                    </a>
                                </li>
                                @endif
                                @if($application->status !== 'rejected')
                                <li>
                                    <a class="dropdown-item status-update-btn text-warning" href="#"
                                       data-id="{{ $application->id }}" data-status="rejected">
                                        <i class="ri-close-line me-2"></i>Cancel
                                    </a>
                                </li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item delete-btn text-danger" href="#"
                                       data-id="{{ $application->id }}">
                                        <i class="ri-delete-bin-fill me-2"></i>Delete
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">No contest applications found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3 pagination-links d-flex justify-content-center">
    {{ $applications->links() }}
</div>
