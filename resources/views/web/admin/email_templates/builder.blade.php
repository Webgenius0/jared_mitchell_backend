@extends('layout.master-layout')

@section('title', isset($template) ? 'Edit Email Template' : 'Visual Drag & Drop Email Template Builder')

@push('styles')
<style>
    .builder-container {
        min-height: calc(100vh - 160px);
    }
    .block-item {
        cursor: grab;
        transition: all 0.2s ease;
        user-select: none;
    }
    .block-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        border-color: #6366f1 !important;
    }
    .canvas-dropzone {
        min-height: 500px;
        background: #ffffff;
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 20px;
        transition: border-color 0.2s;
    }
    .canvas-dropzone.drag-over {
        border-color: #6366f1;
        background: #f8fafc;
    }
    .canvas-block {
        position: relative;
        margin-bottom: 15px;
        border: 1px dashed transparent;
        padding: 8px;
        border-radius: 8px;
        transition: all 0.2s;
    }
    .canvas-block:hover {
        border-color: #6366f1;
    }
    .canvas-block .block-actions {
        position: absolute;
        top: 6px;
        right: 6px;
        display: none;
        gap: 4px;
        z-index: 10;
    }
    .canvas-block:hover .block-actions {
        display: flex;
    }
    .editable-text:focus {
        outline: 2px solid #6366f1;
        border-radius: 4px;
    }
    .preview-mode {
        border: none !important;
        padding: 0 !important;
    }
    .preview-mode .block-actions {
        display: none !important;
    }
</style>
@endpush

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- Header Controls -->
        <div class="row mb-3 align-items-center">
            <div class="col-md-6">
                <h4 class="mb-1 d-flex align-items-center gap-2">
                    <i class="ri-layout-grid-line text-primary fs-3"></i> 
                    {{ isset($template) ? 'Edit Email Template' : 'Visual Drag & Drop Email Template Builder' }}
                </h4>
                <p class="text-muted mb-0">Drag blocks from the palette to build & save reusable HTML email templates.</p>
            </div>
            <div class="col-md-6 text-md-end d-flex gap-2 justify-content-md-end flex-wrap mt-2 mt-md-0">
                <a href="{{ route('admin.email-templates.index') }}" class="btn btn-soft-secondary">
                    <i class="ri-arrow-left-line me-1"></i> Back to Gallery
                </a>
                <button type="button" id="btnTogglePreview" class="btn btn-soft-info">
                    <i class="ri-eye-line me-1"></i> Toggle Live Preview
                </button>
                <button type="button" id="btnSaveTemplate" class="btn btn-success fw-bold px-4 shadow">
                    <i class="ri-save-line me-1"></i> 💾 Save Template
                </button>
            </div>
        </div>

        <div class="row g-4 builder-container">
            <!-- Left Sidebar: Settings & Block Palette -->
            <div class="col-lg-4">
                <!-- Meta Details Card -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-dark text-white fw-bold d-flex align-items-center gap-2">
                        <i class="ri-settings-4-line text-warning"></i> Template Settings
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Template Name <span class="text-danger">*</span></label>
                            <input type="text" id="templateName" class="form-control" value="{{ $template->name ?? '' }}" placeholder="e.g. VIP Music Release Announcement">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select id="templateCategory" class="form-select">
                                <option value="general" {{ (isset($template) && $template->category == 'general') ? 'selected' : '' }}>📢 General News</option>
                                <option value="spotlight" {{ (isset($template) && $template->category == 'spotlight') ? 'selected' : '' }}>🎤 Artist Spotlight</option>
                                <option value="contest" {{ (isset($template) && $template->category == 'contest') ? 'selected' : '' }}>🏆 Contest Announcement</option>
                                <option value="promotion" {{ (isset($template) && $template->category == 'promotion') ? 'selected' : '' }}>🛒 Shop & Merch Release</option>
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold">Short Description</label>
                            <textarea id="templateDescription" class="form-control" rows="2" placeholder="Brief note about when to use this template...">{{ $template->description ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Drag & Drop Block Palette -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light fw-bold d-flex align-items-center gap-2">
                        <i class="ri-draggable text-primary fs-5"></i> Drag & Drop Content Blocks
                    </div>
                    <div class="card-body p-3">
                        <div class="d-flex flex-column gap-2">
                            <!-- Block 1: Header Badge -->
                            <div class="block-item p-3 bg-white rounded border d-flex align-items-center gap-3" draggable="true" data-type="header">
                                <div class="avatar-xs bg-dark-subtle text-dark rounded d-flex align-items-center justify-content-center">
                                    <i class="ri-heading"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Brand Header Badge</h6>
                                    <small class="text-muted">Dark gradient header banner</small>
                                </div>
                            </div>

                            <!-- Block 2: Hero Banner -->
                            <div class="block-item p-3 bg-white rounded border d-flex align-items-center gap-3" draggable="true" data-type="banner">
                                <div class="avatar-xs bg-primary-subtle text-primary rounded d-flex align-items-center justify-content-center">
                                    <i class="ri-image-line"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Hero Banner Image</h6>
                                    <small class="text-muted">Full-width responsive banner image</small>
                                </div>
                            </div>

                            <!-- Block 3: Text Article -->
                            <div class="block-item p-3 bg-white rounded border d-flex align-items-center gap-3" draggable="true" data-type="text">
                                <div class="avatar-xs bg-info-subtle text-info rounded d-flex align-items-center justify-content-center">
                                    <i class="ri-article-line"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Heading & Body Text</h6>
                                    <small class="text-muted">Article section with styled paragraph</small>
                                </div>
                            </div>

                            <!-- Block 4: CTA Button -->
                            <div class="block-item p-3 bg-white rounded border d-flex align-items-center gap-3" draggable="true" data-type="cta">
                                <div class="avatar-xs bg-warning-subtle text-warning rounded d-flex align-items-center justify-content-center">
                                    <i class="ri-cursor-line"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Pill CTA Action Button</h6>
                                    <small class="text-muted">High-converting call to action button</small>
                                </div>
                            </div>

                            <!-- Block 5: Feature Cards Grid -->
                            <div class="block-item p-3 bg-white rounded border d-flex align-items-center gap-3" draggable="true" data-type="cards">
                                <div class="avatar-xs bg-danger-subtle text-danger rounded d-flex align-items-center justify-content-center">
                                    <i class="ri-layout-column-line"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">2-Column Feature Cards</h6>
                                    <small class="text-muted">Side-by-side products or artist spotlight</small>
                                </div>
                            </div>

                            <!-- Block 6: Quote Box -->
                            <div class="block-item p-3 bg-white rounded border d-flex align-items-center gap-3" draggable="true" data-type="quote">
                                <div class="avatar-xs bg-success-subtle text-success rounded d-flex align-items-center justify-content-center">
                                    <i class="ri-double-quotes-l"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Quote / Highlight Box</h6>
                                    <small class="text-muted">Styled accent callout text box</small>
                                </div>
                            </div>

                            <!-- Block 7: Social Footer -->
                            <div class="block-item p-3 bg-white rounded border d-flex align-items-center gap-3" draggable="true" data-type="footer">
                                <div class="avatar-xs bg-secondary-subtle text-secondary rounded d-flex align-items-center justify-content-center">
                                    <i class="ri-footprint-line"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Social Links & Footer</h6>
                                    <small class="text-muted">Copyright & Unsubscribe links</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Interactive Dropzone Canvas -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-light d-flex align-items-center justify-content-between">
                        <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                            <i class="ri-computer-line text-primary"></i> Canvas Studio (Drop blocks below)
                        </h6>
                        <span class="badge bg-primary-subtle text-primary">Drag & Re-order Active</span>
                    </div>
                    <div class="card-body p-4 bg-light">
                        <div id="emailCanvas" class="canvas-dropzone shadow-sm">
                            @if(isset($template) && !empty($template->html_content))
                                {!! $template->html_content !!}
                            @else
                                <!-- Default Starting Blocks -->
                                <div class="canvas-block" data-type="header">
                                    <div class="block-actions">
                                        <button type="button" class="btn btn-sm btn-danger btn-remove"><i class="ri-delete-bin-line"></i></button>
                                    </div>
                                    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); padding: 30px; text-align: center; border-radius: 10px; color: #ffffff;">
                                        <h2 style="margin:0; letter-spacing: 2px; text-transform: uppercase;">OUR SOCIAL IMAGE</h2>
                                        <p style="margin: 5px 0 0 0; color: #94a3b8; font-size: 13px;">Official Community Newsletter</p>
                                    </div>
                                </div>

                                <div class="canvas-block" data-type="text">
                                    <div class="block-actions">
                                        <button type="button" class="btn btn-sm btn-danger btn-remove"><i class="ri-delete-bin-line"></i></button>
                                    </div>
                                    <div style="padding: 20px; color: #334155;">
                                        <h3 class="editable-text" contenteditable="true" style="color: #0f172a; margin-top:0;">Welcome to Our Latest Update</h3>
                                        <p class="editable-text" contenteditable="true" style="line-height: 1.7;">We are thrilled to bring you the latest announcements, artist features, and platform updates. Click to edit any text directly on this canvas!</p>
                                    </div>
                                </div>

                                <div class="canvas-block" data-type="footer">
                                    <div class="block-actions">
                                        <button type="button" class="btn btn-sm btn-danger btn-remove"><i class="ri-delete-bin-line"></i></button>
                                    </div>
                                    <div style="background: #0f172a; padding: 24px; text-align: center; border-radius: 10px; color: #94a3b8; font-size: 13px;">
                                        <p style="margin: 0 0 8px 0; color: #ffffff; font-weight: bold;">OUR SOCIAL IMAGE</p>
                                        <p style="margin: 0;">© {{ date('Y') }} Our Social Image. All rights reserved. | <a href="#" style="color: #6366f1;">Unsubscribe</a></p>
                                    </div>
                                </div>
                            @endif
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
        const templates = {
            header: `
                <div class="canvas-block" data-type="header">
                    <div class="block-actions"><button type="button" class="btn btn-sm btn-danger btn-remove"><i class="ri-delete-bin-line"></i></button></div>
                    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); padding: 30px; text-align: center; border-radius: 10px; color: #ffffff;">
                        <h2 style="margin:0; letter-spacing: 2px; text-transform: uppercase;">OUR SOCIAL IMAGE</h2>
                        <p style="margin: 5px 0 0 0; color: #94a3b8; font-size: 13px;">Official Community Newsletter</p>
                    </div>
                </div>`,
            banner: `
                <div class="canvas-block" data-type="banner">
                    <div class="block-actions"><button type="button" class="btn btn-sm btn-danger btn-remove"><i class="ri-delete-bin-line"></i></button></div>
                    <div style="padding: 10px; text-align: center;">
                        <img src="https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=800&auto=format&fit=crop&q=80" alt="Banner" style="width:100%; max-height: 280px; object-fit: cover; border-radius: 10px;">
                    </div>
                </div>`,
            text: `
                <div class="canvas-block" data-type="text">
                    <div class="block-actions"><button type="button" class="btn btn-sm btn-danger btn-remove"><i class="ri-delete-bin-line"></i></button></div>
                    <div style="padding: 20px; color: #334155;">
                        <h3 class="editable-text" contenteditable="true" style="color: #0f172a; margin-top:0;">New Feature Highlight</h3>
                        <p class="editable-text" contenteditable="true" style="line-height: 1.7;">Enter your detailed announcement or newsletter copy here. Easily format text, add links, and customize styling.</p>
                    </div>
                </div>`,
            cta: `
                <div class="canvas-block" data-type="cta">
                    <div class="block-actions"><button type="button" class="btn btn-sm btn-danger btn-remove"><i class="ri-delete-bin-line"></i></button></div>
                    <div style="padding: 20px; text-align: center;">
                        <a href="https://admin.oursocialimage.net" target="_blank" style="display: inline-block; background: #6366f1; color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 50px; font-weight: bold; box-shadow: 0 4px 12px rgba(99,102,241,0.3);">Explore Platform Now →</a>
                    </div>
                </div>`,
            cards: `
                <div class="canvas-block" data-type="cards">
                    <div class="block-actions"><button type="button" class="btn btn-sm btn-danger btn-remove"><i class="ri-delete-bin-line"></i></button></div>
                    <div style="padding: 15px; display: flex; gap: 15px; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 220px; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                            <h4 class="editable-text" contenteditable="true" style="margin-top:0; color:#0f172a;">Spotlight Feature A</h4>
                            <p class="editable-text" contenteditable="true" style="font-size:14px; color:#64748b;">Highlight key artist or product announcement here.</p>
                        </div>
                        <div style="flex: 1; min-width: 220px; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                            <h4 class="editable-text" contenteditable="true" style="margin-top:0; color:#0f172a;">Spotlight Feature B</h4>
                            <p class="editable-text" contenteditable="true" style="font-size:14px; color:#64748b;">Highlight secondary updates or contest rules.</p>
                        </div>
                    </div>
                </div>`,
            quote: `
                <div class="canvas-block" data-type="quote">
                    <div class="block-actions"><button type="button" class="btn btn-sm btn-danger btn-remove"><i class="ri-delete-bin-line"></i></button></div>
                    <div style="margin: 15px 0; padding: 16px 20px; background: #f1f5f9; border-left: 4px solid #6366f1; border-radius: 0 8px 8px 0; font-style: italic; color: #475569;">
                        "Music is the universal language of mankind. Stay tuned for upcoming live voting rounds!"
                    </div>
                </div>`,
            footer: `
                <div class="canvas-block" data-type="footer">
                    <div class="block-actions"><button type="button" class="btn btn-sm btn-danger btn-remove"><i class="ri-delete-bin-line"></i></button></div>
                    <div style="background: #0f172a; padding: 24px; text-align: center; border-radius: 10px; color: #94a3b8; font-size: 13px;">
                        <p style="margin: 0 0 8px 0; color: #ffffff; font-weight: bold;">OUR SOCIAL IMAGE</p>
                        <p style="margin: 0;">© {{ date('Y') }} Our Social Image. All rights reserved. | <a href="#" style="color: #6366f1;">Unsubscribe</a></p>
                    </div>
                </div>`
        };

        const $canvas = $('#emailCanvas');

        // Drag & Drop Handlers
        $('.block-item').on('dragstart', function(e) {
            e.originalEvent.dataTransfer.setData('text/plain', $(this).data('type'));
        });

        $canvas.on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('drag-over');
        });

        $canvas.on('dragleave', function() {
            $(this).removeClass('drag-over');
        });

        $canvas.on('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('drag-over');
            const type = e.originalEvent.dataTransfer.getData('text/plain');
            if (templates[type]) {
                $canvas.append(templates[type]);
            }
        });

        // Remove Block Handler
        $canvas.on('click', '.btn-remove', function() {
            $(this).closest('.canvas-block').remove();
        });

        // Toggle Live Preview
        $('#btnTogglePreview').on('click', function() {
            $('.canvas-block').toggleClass('preview-mode');
        });

        // Save Template Handler
        $('#btnSaveTemplate').on('click', function() {
            const name = $('#templateName').val();
            const category = $('#templateCategory').val();
            const description = $('#templateDescription').val();

            if (!name) {
                Alert.warning('Template Name is required.');
                return;
            }

            // Clone canvas, strip editing UI controls
            const $clone = $canvas.clone();
            $clone.find('.block-actions').remove();
            $clone.find('.canvas-block').removeClass('canvas-block preview-mode').removeAttr('data-type');
            $clone.find('.editable-text').removeAttr('contenteditable');

            const htmlContent = $clone.html();

            const $btn = $(this);
            $btn.prop('disabled', true).html('<i class="ri-loader-4-line spinner me-1"></i> Saving...');

            const saveUrl = "{{ isset($template) ? route('admin.email-templates.update', $template->id) : route('admin.email-templates.store') }}";
            const method = "{{ isset($template) ? 'PUT' : 'POST' }}";

            $.ajax({
                url: saveUrl,
                type: method,
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
                        Alert.success(res.message);
                        setTimeout(() => window.location.href = res.redirect, 1500);
                    } else {
                        Alert.error(res.message);
                    }
                },
                error: function(err) {
                    $btn.prop('disabled', false).html('<i class="ri-save-line me-1"></i> 💾 Save Template');
                    Alert.error(err.responseJSON?.message || 'Failed to save template.');
                }
            });
        });
    });
</script>
@endpush
