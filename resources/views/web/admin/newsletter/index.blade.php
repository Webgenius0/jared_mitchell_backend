@extends('layout.master-layout')

@section('title', 'Newsletter Subscriptions & AI Broadcast')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title & Header Actions -->
        <div class="row mb-3">
            <div class="col-12 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h4 class="mb-0">Newsletter & AI Broadcast Management</h4>
                <button type="button" class="btn btn-primary bg-gradient" data-bs-toggle="modal" data-bs-target="#aiNewsletterModal">
                    <i class="ri-magic-line me-1 align-middle"></i> ✨ Generate AI Newsletter
                </button>
            </div>
        </div>

        <!-- Quick Stats Cards -->
        <div class="row">
            <div class="col-md-4">
                <div class="card card-animate border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-uppercase fw-medium text-muted mb-0">Active Subscribers</p>
                            <h2 class="mt-2 mb-0 fw-semibold text-success">{{ $activeSubscribersCount ?? 0 }}</h2>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-success-subtle text-success rounded-circle fs-3">
                                <i class="ri-user-follow-line"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-animate border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-uppercase fw-medium text-muted mb-0">Total Subscriptions</p>
                            <h2 class="mt-2 mb-0 fw-semibold text-primary">{{ $newsletters->total() ?? 0 }}</h2>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-3">
                                <i class="ri-mail-send-line"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-animate border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-uppercase fw-medium text-muted mb-0">Broadcasts Sent</p>
                            <h2 class="mt-2 mb-0 fw-semibold text-info">{{ $broadcasts->total() ?? 0 }}</h2>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-info-subtle text-info rounded-circle fs-3">
                                <i class="ri-send-plane-fill"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs for Subscribers & History -->
        <div class="row mt-2">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-0 align-items-center d-flex">
                        <ul class="nav nav-tabs-custom card-header-tabs border-bottom-0" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#subscribers-tab" role="tab">
                                    <i class="ri-contacts-line me-1"></i> Active Subscribers ({{ $newsletters->total() }})
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#history-tab" role="tab">
                                    <i class="ri-history-line me-1"></i> Broadcast History ({{ $broadcasts->total() }})
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body tab-content">
                        <!-- Subscribers Tab -->
                        <div class="tab-pane active" id="subscribers-tab" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th>Subscriber Email</th>
                                            <th>Status</th>
                                            <th>Subscribed Date</th>
                                            <th class="text-center" style="width: 100px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($newsletters as $newsletter)
                                        <tr>
                                            <td>{{ $loop->iteration + ($newsletters->currentPage() - 1) * $newsletters->perPage() }}</td>
                                            <td class="fw-medium">{{ $newsletter->email }}</td>
                                            <td>
                                                @if($newsletter->status == 'active')
                                                    <span class="badge bg-success-subtle text-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger">Unsubscribed</span>
                                                @endif
                                            </td>
                                            <td>{{ $newsletter->created_at->format('M d, Y h:i A') }}</td>
                                            <td class="text-center">
                                                <form action="{{ route('admin.newsletters.destroy', $newsletter->id) }}" method="POST" class="delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-soft-danger delete-btn" title="Delete Subscriber">
                                                        <i class="ri-delete-bin-fill"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">No subscribers found.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                {{ $newsletters->links() }}
                            </div>
                        </div>

                        <!-- Broadcast History Tab -->
                        <div class="tab-pane" id="history-tab" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th>Subject Line</th>
                                            <th>Topic Category</th>
                                            <th>Targeted / Sent</th>
                                            <th>Status</th>
                                            <th>Date Dispatched</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($broadcasts as $broadcast)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td class="fw-semibold text-primary">{{ $broadcast->subject }}</td>
                                            <td><span class="badge bg-light text-dark text-capitalize">{{ str_replace('_', ' ', $broadcast->topic_type) }}</span></td>
                                            <td>
                                                <span class="text-success fw-bold">{{ $broadcast->sent_count }}</span> / {{ $broadcast->total_subscribers }}
                                                @if($broadcast->failed_count > 0)
                                                    <span class="badge bg-danger-subtle text-danger ms-1">{{ $broadcast->failed_count }} failed</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($broadcast->status == 'completed')
                                                    <span class="badge bg-success">Completed</span>
                                                @elseif($broadcast->status == 'processing')
                                                    <span class="badge bg-warning text-dark"><i class="ri-loader-4-line spinner me-1"></i> Processing</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($broadcast->status) }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $broadcast->created_at->format('M d, Y h:i A') }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">No newsletter broadcasts sent yet.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                {{ $broadcasts->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ✨ AI Newsletter Generation & Dynamic Broadcast Studio Modal Partial -->
@include('web.admin.newsletter.partials._ai_newsletter_modal')
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // AI Generation Handler
        $('#btnGenerateAi').on('click', function() {
            const topic = $('#aiTopicSelect').val();
            const notes = $('#aiCustomNotes').val();
            const $btn = $(this);

            $btn.prop('disabled', true).html('<i class="ri-loader-4-line spinner me-1"></i> Generating AI Content...');

            $.ajax({
                url: "{{ route('admin.newsletters.generate-ai') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    topic_type: topic,
                    custom_notes: notes
                },
                success: function(res) {
                    $btn.prop('disabled', false).html('<i class="ri-sparkles-line me-1"></i> ✨ Generate Content with AI');
                    if (res.success) {
                        $('#inputSubject').val(res.subject);
                        $('#inputHtmlContent').val(res.html_content);
                        Alert.success('AI Newsletter Content Generated Successfully!');
                    } else {
                        Alert.error(res.message || 'Generation failed.');
                    }
                },
                error: function(err) {
                    $btn.prop('disabled', false).html('<i class="ri-sparkles-line me-1"></i> ✨ Generate Content with AI');
                    Alert.error(err.responseJSON?.message || 'Error communicating with AI service.');
                }
            });
        });

        // Send Test Email Handler
        $('#btnSendTest').on('click', function() {
            const testEmail = $('#inputTestEmail').val();
            const subject = $('#inputSubject').val();
            const htmlContent = $('#inputHtmlContent').val();

            if (!testEmail || !subject || !htmlContent) {
                Alert.warning('Please enter Test Email, Subject, and HTML Content.');
                return;
            }

            const $btn = $(this);
            $btn.prop('disabled', true).html('Sending...');

            $.ajax({
                url: "{{ route('admin.newsletters.test-mail') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    test_email: testEmail,
                    subject: subject,
                    html_content: htmlContent,
                    primary_color: $('#inputPrimaryColor').val(),
                    banner_image_url: $('#inputBannerUrl').val(),
                    cta_button_text: $('#inputCtaText').val(),
                    cta_button_url: $('#inputCtaUrl').val(),
                },
                success: function(res) {
                    $btn.prop('disabled', false).html('Send Test');
                    if (res.success) {
                        Alert.success(res.message);
                    } else {
                        Alert.error(res.message);
                    }
                },
                error: function(err) {
                    $btn.prop('disabled', false).html('Send Test');
                    Alert.error(err.responseJSON?.message || 'Failed to send test email.');
                }
            });
        });

        // Mass Broadcast Handler
        $('#btnBroadcastNow').on('click', function() {
            const subject = $('#inputSubject').val();
            const htmlContent = $('#inputHtmlContent').val();

            if (!subject || !htmlContent) {
                Alert.warning('Subject and HTML content are required before broadcasting.');
                return;
            }

            Alert.confirm('Are you sure you want to broadcast this newsletter to all active subscribers via Queue?', {
                title: 'Broadcast Newsletter?',
                type: 'warning',
                confirmText: 'Yes, Send Broadcast Now'
            }).then(confirmed => {
                if (confirmed) {
                    const $btn = $('#btnBroadcastNow');
                    $btn.prop('disabled', true).html('<i class="ri-loader-4-line spinner me-1"></i> Dispatching Broadcast...');

                    $.ajax({
                        url: "{{ route('admin.newsletters.broadcast') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            subject: subject,
                            html_content: htmlContent,
                            topic_type: $('#aiTopicSelect').val(),
                            ai_prompt: $('#aiCustomNotes').val(),
                            primary_color: $('#inputPrimaryColor').val(),
                            banner_image_url: $('#inputBannerUrl').val(),
                            cta_button_text: $('#inputCtaText').val(),
                            cta_button_url: $('#inputCtaUrl').val(),
                        },
                        success: function(res) {
                            $btn.prop('disabled', false).html('<i class="ri-send-plane-fill me-1"></i> 🚀 Broadcast to All Active Subscribers');
                            if (res.success) {
                                $('#aiNewsletterModal').modal('hide');
                                Alert.success(res.message);
                                setTimeout(() => window.location.reload(), 1500);
                            } else {
                                Alert.error(res.message);
                            }
                        },
                        error: function(err) {
                            $btn.prop('disabled', false).html('<i class="ri-send-plane-fill me-1"></i> 🚀 Broadcast to All Active Subscribers');
                            Alert.error(err.responseJSON?.message || 'Failed to dispatch broadcast.');
                        }
                    });
                }
            });
        });

        // Subscriber Delete Handler
        $('.delete-btn').on('click', function(e) {
            e.preventDefault();
            const form = $(this).closest('form');
            Alert.confirm('Are you sure you want to delete this subscriber?', {
                title: 'Delete Subscription?',
                type: 'danger',
                confirmText: 'Yes, delete it'
            }).then(confirmed => {
                if (confirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
