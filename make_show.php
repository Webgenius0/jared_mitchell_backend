<?php

$content = file_get_contents('resources/views/web/admin/events/edit.blade.php');

// Remove form tags
$content = preg_replace('/<form.*?>/', '', $content);
$content = str_replace('</form>', '', $content);
$content = str_replace('@csrf', '', $content);
$content = str_replace("@method('PUT')", '', $content);

// Update titles and buttons
$content = str_replace('Edit Event:', 'Event Details:', $content);
$content = str_replace("@section('title', 'Edit Event')", "@section('title', 'Event Details')", $content);
$content = preg_replace('/<button type="submit".*?Update Event.*?<\/button>/s', '', $content);
$content = str_replace('Cancel', 'Back to Events', $content);

// Remove all add/remove buttons
$content = preg_replace('/<button type="button" class="btn btn-soft-primary btn-sm".*?Add.*?<\/button>/', '', $content);
$content = preg_replace('/<button type="button" class="btn btn-soft-danger btn-icon.*?<\/button>/', '', $content);
$content = preg_replace('/<button type="button" id="add-artist-btn".*?<\/button>/', '', $content);
$content = preg_replace('/<button type="button" class="btn btn-sm btn-soft-danger remove-artist-btn.*?<\/button>/', '', $content);

// Disable all input, textarea, select (safely)
$content = str_replace('<input ', '<input disabled ', $content);
$content = str_replace('<select ', '<select disabled ', $content);
$content = str_replace('<textarea ', '<textarea disabled ', $content);
$content = str_replace('disabled type="hidden"', 'type="hidden"', $content);

// Remove input type hidden entirely
$content = preg_replace('/<input[^>]*type="hidden"[^>]*>/', '', $content);

// Change descriptionEditor ID to something else so Quill doesn't try to initialize it as editable?
$content = str_replace('id="descriptionEditor" class="snow-editor"', 'class="p-3"', $content);

// Append registrations table
$registrations_html = <<<HTML
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
                                        @forelse(\$event->registrations as \$reg)
                                        <tr>
                                            <td><span class="fw-medium text-primary">{{ \$reg->booking_reference }}</span></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-grow-1">
                                                        <h6 class="fs-14 mb-1">{{ \$reg->first_name }} {{ \$reg->last_name }}</h6>
                                                        <p class="text-muted mb-0">{{ \$reg->email }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ \$reg->ticketTier?->name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-success-subtle text-success text-uppercase">{{ \$reg->payment_status }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info text-uppercase">{{ \$reg->status }}</span>
                                            </td>
                                            <td>{{ \$reg->created_at->format('M d, Y') }}</td>
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
HTML;

$content = str_replace('                    </div>

                    <div class="col-lg-4">', $registrations_html . "\n" . '                    </div>

                    <div class="col-lg-4">', $content);

// Remove scripts pushing to scripts stack
$content = preg_replace('/@push\(\'scripts\'\).*?@endpush/s', '', $content);

file_put_contents('resources/views/web/admin/events/show.blade.php', $content);
echo "Success\n";
