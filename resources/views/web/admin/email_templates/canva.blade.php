@extends('layout.master-layout')

@section('title', 'Canva HTML Email Template Studio')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- Header Controls -->
        <div class="row mb-3 align-items-center">
            <div class="col-md-6">
                <h4 class="mb-1 d-flex align-items-center gap-2">
                    <i class="ri-code-box-line text-warning fs-3"></i> 
                    Canva & External HTML Template Studio
                </h4>
                <p class="text-muted mb-0">Import, preview, and save 100% pure Canva HTML email exports cleanly into your backend.</p>
            </div>
            <div class="col-md-6 text-md-end d-flex gap-2 justify-content-md-end flex-wrap mt-2 mt-md-0">
                <a href="{{ route('admin.email-templates.index') }}" class="btn btn-soft-secondary">
                    <i class="ri-arrow-left-line me-1"></i> Back to Gallery
                </a>
                <a href="{{ route('admin.email-templates.create') }}" class="btn btn-soft-primary">
                    <i class="ri-layout-grid-line me-1"></i> Switch to Visual Builder
                </a>
                <button type="button" id="btnSaveCanvaTemplate" class="btn btn-success fw-bold px-3 shadow">
                    <i class="ri-save-line me-1"></i> 💾 Save Template
                </button>
                <button type="button" id="btnDirectBroadcastCanva" class="btn btn-danger fw-bold px-3 shadow">
                    <i class="ri-send-plane-fill me-1"></i> 🚀 Direct Broadcast (No AI)
                </button>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Side: Template Settings & Import Inputs -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-dark text-white fw-bold d-flex align-items-center gap-2">
                        <i class="ri-settings-4-line text-warning"></i> Template & Broadcast Details
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Template Name <span class="text-danger">*</span></label>
                            <input type="text" id="canvaTemplateName" class="form-control" placeholder="e.g. Canva Q4 Performance Newsletter">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Broadcast Subject Line <span class="text-danger">*</span></label>
                            <input type="text" id="canvaSubject" class="form-control border-primary" placeholder="e.g. 🚨 Exclusive Update from Our Social Image!">
                            <small class="text-muted">Subject line used when sending this pure Canva email to subscribers.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                            <select id="canvaTemplateCategory" class="form-select">
                                <option value="general">📢 General News</option>
                                <option value="spotlight">🎤 Artist Spotlight</option>
                                <option value="contest">🏆 Contest Announcement</option>
                                <option value="promotion">🛒 Shop & Merch Release</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Short Description</label>
                            <textarea id="canvaTemplateDescription" class="form-control" rows="2" placeholder="Notes about this Canva design..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Canva HTML Upload & Editor -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white fw-bold d-flex align-items-center gap-2">
                        <i class="ri-file-code-line fs-5"></i> Import Canva HTML Export
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info border-0 mb-3">
                            <i class="ri-information-line me-1"></i> 
                            <strong>Canva Export Instructions:</strong> Export design from Canva as <em>HTML Email</em>, then upload the <code>.html</code> file or paste the raw HTML code below.
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Option A: Upload <code>.html</code> File</label>
                            <input type="file" id="canvaFileImport" class="form-control" accept=".html,.htm">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Option B: Paste Raw Canva HTML Code</label>
                            <textarea id="canvaRawHtml" class="form-control font-monospace" rows="12" placeholder="Paste Canva HTML code here (<!DOCTYPE html>...)"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Live iFrame Preview -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-light fw-bold d-flex align-items-center justify-content-between">
                        <span class="d-flex align-items-center gap-2">
                            <i class="ri-eye-line text-primary fs-5"></i> Live Template Visual Preview
                        </span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">100% Pure Canva Render</span>
                    </div>
                    <div class="card-body p-0">
                        <iframe id="iframePreview" style="width: 100%; height: 680px; border: none; background: #f0f1f5;" srcdoc="<p style='text-align:center; padding-top:100px; color:#94a3b8; font-family:sans-serif;'>Paste or upload Canva HTML to view live visual preview here...</p>"></iframe>
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
        const $iframe = $('#iframePreview');
        const $textarea = $('#canvaRawHtml');

        // Update Live iFrame Preview when HTML changes
        function updatePreview() {
            const html = $textarea.val().trim();
            if (html) {
                $iframe.attr('srcdoc', html);
            } else {
                $iframe.attr('srcdoc', "<p style='text-align:center; padding-top:100px; color:#94a3b8; font-family:sans-serif;'>Paste or upload Canva HTML to view live visual preview here...</p>");
            }
        }

        $textarea.on('input propertychange change', updatePreview);

        // File Reader for Uploaded .html file
        $('#canvaFileImport').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    $textarea.val(evt.target.result);
                    updatePreview();
                    Alert.success('Canva HTML File loaded successfully!');
                };
                reader.readAsText(file);
            }
        });

        // Save Canva Template Handler
        $('#btnSaveCanvaTemplate').on('click', function() {
            const name = $('#canvaTemplateName').val().trim();
            const category = $('#canvaTemplateCategory').val();
            const description = $('#canvaTemplateDescription').val().trim();
            const htmlContent = $textarea.val().trim();

            if (!name) {
                Alert.warning('Please enter Template Name.');
                return;
            }

            if (!htmlContent) {
                Alert.warning('Please upload or paste Canva HTML code.');
                return;
            }

            const $btn = $(this);
            $btn.prop('disabled', true).html('<i class="ri-loader-4-line spinner me-1"></i> Saving Canva Template...');

            $.ajax({
                url: "{{ route('admin.email-templates.store') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    name: name,
                    category: category,
                    description: description,
                    html_content: htmlContent
                },
                success: function(res) {
                    $btn.prop('disabled', false).html('<i class="ri-save-line me-1"></i> 💾 Save Template');
                    if (res.success) {
                        Alert.success('Canva Email Template saved successfully!');
                        setTimeout(() => window.location.href = res.redirect, 1500);
                    } else {
                        Alert.error(res.message);
                    }
                },
                error: function(err) {
                    $btn.prop('disabled', false).html('<i class="ri-save-line me-1"></i> 💾 Save Template');
                    Alert.error(err.responseJSON?.message || 'Failed to save Canva template.');
                }
            });
        });

        // Direct Broadcast Pure Canva Template (ZERO AI STEP REQUIRED!)
        $('#btnDirectBroadcastCanva').on('click', function() {
            const name = $('#canvaTemplateName').val().trim();
            const subject = $('#canvaSubject').val().trim();
            const category = $('#canvaTemplateCategory').val();
            const description = $('#canvaTemplateDescription').val().trim();
            const htmlContent = $textarea.val().trim();

            if (!name) {
                Alert.warning('Please enter Template Name.');
                return;
            }

            if (!subject) {
                Alert.warning('Please enter Broadcast Subject Line.');
                return;
            }

            if (!htmlContent) {
                Alert.warning('Please upload or paste Canva HTML code.');
                return;
            }

            if (!confirm('Are you sure you want to broadcast this 100% pure Canva design directly to ALL active subscribers (NO AI required)?')) {
                return;
            }

            const $btn = $(this);
            $btn.prop('disabled', true).html('<i class="ri-loader-4-line spinner me-1"></i> Broadcasting...');

            $.ajax({
                url: "{{ route('admin.email-templates.store') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    name: name,
                    category: category,
                    description: description,
                    html_content: htmlContent
                },
                success: function(res) {
                    if (res.success && res.data?.id) {
                        $.ajax({
                            url: "/admin/email-templates/" + res.data.id + "/broadcast",
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                subject: subject
                            },
                            success: function(bRes) {
                                $btn.prop('disabled', false).html('<i class="ri-send-plane-fill me-1"></i> 🚀 Direct Broadcast (No AI)');
                                if (bRes.success) {
                                    Alert.success(bRes.message);
                                    setTimeout(() => window.location.href = "{{ route('admin.newsletters.index') }}", 1800);
                                } else {
                                    Alert.error(bRes.message);
                                }
                            },
                            error: function(bErr) {
                                $btn.prop('disabled', false).html('<i class="ri-send-plane-fill me-1"></i> 🚀 Direct Broadcast (No AI)');
                                Alert.error(bErr.responseJSON?.message || 'Broadcast failed.');
                            }
                        });
                    } else {
                        $btn.prop('disabled', false).html('<i class="ri-send-plane-fill me-1"></i> 🚀 Direct Broadcast (No AI)');
                        Alert.error(res.message);
                    }
                },
                error: function(err) {
                    $btn.prop('disabled', false).html('<i class="ri-send-plane-fill me-1"></i> 🚀 Direct Broadcast (No AI)');
                    Alert.error(err.responseJSON?.message || 'Failed to save template before broadcast.');
                }
            });
        });
    });
</script>
@endpush
