@extends('layout.master-layout')

@section('title', 'AI Newsletter Studio & Generator')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title & Header Actions -->
        <div class="row mb-3">
            <div class="col-12 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h4 class="mb-1 d-flex align-items-center gap-2">
                        <i class="ri-magic-line text-warning fs-3"></i> AI Newsletter Generator & Dynamic Studio
                    </h4>
                    <p class="text-muted mb-0">Generate AI content, customize dynamic layout templates, test, and broadcast to active subscribers.</p>
                </div>
                <a href="{{ route('admin.newsletters.index') }}" class="btn btn-soft-secondary">
                    <i class="ri-arrow-left-line me-1 align-middle"></i> Back to Subscribers
                </a>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column: Controls & AI Generator -->
            <div class="col-lg-5">
                <!-- AI Generator Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-dark text-white d-flex align-items-center gap-2">
                        <i class="ri-cpu-line text-warning fs-5"></i>
                        <h6 class="card-title text-white mb-0">1. Select Topic & AI Guidance</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Preset Category Topic <span class="text-danger">*</span></label>
                            <select id="aiTopicSelect" class="form-select form-select-lg">
                                <option value="artist_spotlight">🎤 Artist & Business Spotlight Digest</option>
                                <option value="contest_update">🏆 Contest & Season Announcement (Boss Beginnings)</option>
                                <option value="royalty_advice">🤖 OSI AI Music Industry & Royalty Advice</option>
                                <option value="shop_promo">🛒 Merchandise & Shop Promotional Release</option>
                                <option value="general_news">📢 General Platform Update & News</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Custom Guidance / Key Notes (Optional)</label>
                            <textarea id="aiCustomNotes" class="form-control" rows="3" placeholder="e.g. Highlight winner John Doe, include 10% shop coupon, mention new track release..."></textarea>
                        </div>
                        <button type="button" id="btnGenerateAi" class="btn btn-warning w-100 fw-bold py-2 fs-6 shadow-sm">
                            <i class="ri-sparkles-line me-1"></i> ✨ Generate Content with AI
                        </button>
                    </div>
                </div>

                <!-- Dynamic Styling Controls -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light d-flex align-items-center gap-2">
                        <i class="ri-palette-line text-info fs-5"></i>
                        <h6 class="card-title mb-0 fw-bold">2. Dynamic Template Customization</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold mb-1">Select Email Layout Template <span class="text-danger">*</span></label>
                            <select id="inputTemplateStyle" class="form-select fw-semibold text-primary">
                                <optgroup label="System Preset Layouts">
                                    <option value="modern" selected>🎨 Modern Gradient & Sleek Accent</option>
                                    <option value="minimalist">📄 Minimalist Clean White</option>
                                    <option value="dark">🌙 Cyber Dark Glow</option>
                                    <option value="promotional">🛍️ Promotional Announcement Card</option>
                                </optgroup>
                                @if(isset($customTemplates) && $customTemplates->count() > 0)
                                <optgroup label="Your Custom Saved Drag & Drop Templates">
                                    @foreach($customTemplates as $ct)
                                        <option value="custom_{{ $ct->id }}">⭐ {{ $ct->name }} ({{ ucfirst($ct->category) }})</option>
                                    @endforeach
                                </optgroup>
                                @endif
                            </select>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold mb-1">Theme Accent Color</label>
                                <input type="color" id="inputPrimaryColor" class="form-control form-control-color w-100" value="#6366f1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold mb-1">CTA Button Text</label>
                                <input type="text" id="inputCtaText" class="form-control" value="Explore Platform">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold mb-1">CTA Button Link Target URL</label>
                            <input type="url" id="inputCtaUrl" class="form-control" value="https://admin.oursocialimage.net">
                        </div>
                        <div class="mb-0">
                            <label class="form-label small fw-semibold mb-1">Hero Banner Image URL (Optional)</label>
                            <input type="url" id="inputBannerUrl" class="form-control" placeholder="https://example.com/banner.jpg">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Subject, Editor & Actions -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                        <h6 class="card-title text-white mb-0 d-flex align-items-center gap-2">
                            <i class="ri-edit-box-line fs-5"></i> Compose & Review Newsletter
                        </h6>
                        <span class="badge bg-white text-primary fw-bold">Subscribers Targeted: {{ $activeSubscribersCount ?? 0 }}</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email Subject Line <span class="text-danger">*</span></label>
                            <input type="text" id="inputSubject" class="form-control form-control-lg fw-semibold" placeholder="Enter or AI-generate subject line...">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Email Content (HTML Editor)</label>
                            <textarea id="inputHtmlContent" class="form-control" rows="12" placeholder="AI generated HTML content will appear here..."></textarea>
                        </div>

                        <!-- Action Controls Box -->
                        <div class="bg-light p-3 rounded border">
                            <div class="row align-items-center g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1"><i class="ri-mail-send-line me-1"></i> Send Instant Test Email</label>
                                    <div class="input-group">
                                        <input type="email" id="inputTestEmail" class="form-control" value="{{ Auth::user()->email ?? '' }}" placeholder="test email...">
                                        <button type="button" id="btnSendTest" class="btn btn-info text-white fw-bold">Send Test</button>
                                    </div>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <button type="button" id="btnBroadcastNow" class="btn btn-success btn-lg fw-bold w-100 shadow">
                                        <i class="ri-send-plane-fill me-1"></i> 🚀 Broadcast to {{ $activeSubscribersCount ?? 0 }} Active Subscribers
                                    </button>
                                </div>
                            </div>
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
                    template_style: $('#inputTemplateStyle').val(),
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
                            template_style: $('#inputTemplateStyle').val(),
                            banner_image_url: $('#inputBannerUrl').val(),
                            cta_button_text: $('#inputCtaText').val(),
                            cta_button_url: $('#inputCtaUrl').val(),
                        },
                        success: function(res) {
                            $btn.prop('disabled', false).html('<i class="ri-send-plane-fill me-1"></i> 🚀 Broadcast to Active Subscribers');
                            if (res.success) {
                                Alert.success(res.message);
                                setTimeout(() => window.location.href = "{{ route('admin.newsletters.index') }}", 1500);
                            } else {
                                Alert.error(res.message);
                            }
                        },
                        error: function(err) {
                            $btn.prop('disabled', false).html('<i class="ri-send-plane-fill me-1"></i> 🚀 Broadcast to Active Subscribers');
                            Alert.error(err.responseJSON?.message || 'Failed to dispatch broadcast.');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
