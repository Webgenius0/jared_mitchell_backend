@extends('layout.master-layout')

@section('title', 'Event Details')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Event Details: {{ $event->title }}</h4>
                    <div class="page-title-right">
                        <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-secondary">
                            <i class="ri-edit-2-line align-bottom me-1"></i> Edit Event
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center">
                            @if($event->cover_image_path)
                                <img src="{{ asset('storage/' . $event->cover_image_path) }}" alt="" class="img-fluid rounded shadow">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center shadow" style="height: 200px;">
                                    <i class="ri-image-line fs-24 text-muted"></i>
                                </div>
                            @endif
                            <h4 class="mt-3">{{ $event->title }}</h4>
                            <p class="text-muted">{{ $event->event_type }} | {{ ucfirst($event->status) }}</p>
                        </div>
                        <div class="mt-4">
                            <div class="d-flex align-items-center mb-2">
                                <i class="ri-calendar-event-line me-2 text-primary"></i>
                                <span>{{ $event->starts_at->format('M d, Y') }}</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="ri-time-line me-2 text-primary"></i>
                                <span>{{ $event->starts_at->format('H:i') }} - {{ $event->ends_at->format('H:i') }} ({{ $event->timezone }})</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="ri-map-pin-line me-2 text-primary"></i>
                                <span>{{ $event->venue_name ?? 'Online' }}</span>
                            </div>
                            @if($event->address)
                            <div class="ms-4 text-muted small">{{ $event->address }}, {{ $event->city }}, {{ $event->state }}</div>
                            @endif
                            <div class="d-flex align-items-center mt-3">
                                <i class="ri-user-star-line me-2 text-primary"></i>
                                <span>Hosted by: {{ $event->hosted_by ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Ticket Tiers</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            @foreach($event->ticketTiers as $tier)
                            <li class="list-group-item d-flex justify-content-between align-items-center ps-0">
                                <div>
                                    <h6 class="mb-0">{{ $tier->name }}</h6>
                                    <small class="text-muted">${{ number_format($tier->price, 2) }}</small>
                                </div>
                                <span class="badge bg-primary rounded-pill">
                                    {{ $tier->quantity_sold }} / {{ $tier->quantity_available ?? '∞' }}
                                </span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header border-bottom-dashed">
                        <h5 class="card-title mb-0">Registrations</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-nowrap align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ref</th>
                                        <th>Attendee</th>
                                        <th>Tier</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($event->registrations as $reg)
                                    <tr>
                                        <td><span class="fw-medium text-primary">{{ $reg->booking_reference }}</span></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1">
                                                    <h6 class="fs-14 mb-1">{{ $reg->first_name }} {{ $reg->last_name }}</h6>
                                                    <p class="text-muted mb-0">{{ $reg->email }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $reg->ticketTier?->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-success-subtle text-success text-uppercase">{{ $reg->payment_status }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-uppercase">{{ $reg->status }}</span>
                                        </td>
                                        <td>{{ $reg->created_at->format('M d, Y') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No registrations yet.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
