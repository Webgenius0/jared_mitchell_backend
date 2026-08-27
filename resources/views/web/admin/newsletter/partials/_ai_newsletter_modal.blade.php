<!-- ✨ AI Newsletter Generation & Dynamic Broadcast Studio Modal -->
<div class="modal fade" id="aiNewsletterModal" tabindex="-1" aria-labelledby="aiNewsletterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title text-white d-flex align-items-center gap-2" id="aiNewsletterModalLabel">
                    <i class="ri-magic-line text-warning fs-4"></i> AI Newsletter Generator & Dynamic Email Studio
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <!-- Left Column: Settings & AI Generator -->
                    <div class="col-lg-6">
                        <div class="bg-light p-3 rounded mb-3 border">
                            <h6 class="fw-bold mb-3"><i class="ri-cpu-line me-1 text-primary"></i> 1. Select Topic & AI Prompt</h6>
                            <div class="mb-3">
                                <label class="form-label fw-medium">Preset Category Topic <span class="text-danger">*</span></label>
                                <select id="aiTopicSelect" class="form-select">
                                    <option value="artist_spotlight">🎤 Artist & Business Spotlight Digest</option>
                                    <option value="contest_update">🏆 Contest & Season Announcement (Boss Beginnings)</option>
                                    <option value="royalty_advice">🤖 OSI AI Music Industry & Royalty Advice</option>
                                    <option value="shop_promo">🛒 Merchandise & Shop Promotional Release</option>
                                    <option value="general_news">📢 General Platform Update & News</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-medium">Custom Guidance / Key Notes (Optional)</label>
                                <textarea id="aiCustomNotes" class="form-control" rows="2" placeholder="e.g. Highlight winner John Doe, include 10% shop coupon..."></textarea>
                            </div>
                            <button type="button" id="btnGenerateAi" class="btn btn-warning w-100 fw-bold">
                                <i class="ri-sparkles-line me-1"></i> ✨ Generate Content with AI
                            </button>
                        </div>

                        <!-- Dynamic Styling & Branding Controls -->
                        <div class="bg-light p-3 rounded border">
                            <h6 class="fw-bold mb-3"><i class="ri-palette-line me-1 text-info"></i> 2. Dynamic Template Customization</h6>
                            <div class="row g-2 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label small mb-1 fw-medium">Theme Accent Color</label>
                                    <input type="color" id="inputPrimaryColor" class="form-control form-control-color w-100" value="#6366f1">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small mb-1 fw-medium">CTA Button Text</label>
                                    <input type="text" id="inputCtaText" class="form-control" value="Explore Platform">
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small mb-1 fw-medium">CTA Button Link URL</label>
                                <input type="url" id="inputCtaUrl" class="form-control" value="https://admin.oursocialimage.net">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small mb-1 fw-medium">Hero Banner Image URL (Optional)</label>
                                <input type="url" id="inputBannerUrl" class="form-control" placeholder="https://example.com/banner.jpg">
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Subject, Content & Test Mail -->
                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email Subject Line <span class="text-danger">*</span></label>
                            <input type="text" id="inputSubject" class="form-control fw-semibold" placeholder="Enter or AI-generate subject line...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email Content (HTML Edit)</label>
                            <textarea id="inputHtmlContent" class="form-control" rows="8" placeholder="AI generated HTML content will appear here..."></textarea>
                        </div>

                        <!-- Test Email Action -->
                        <div class="card border border-info-subtle bg-info-subtle p-3 mb-0">
                            <label class="form-label small fw-bold text-dark mb-1"><i class="ri-mail-send-line me-1"></i> Send Instant Test Email</label>
                            <div class="input-group">
                                <input type="email" id="inputTestEmail" class="form-control" value="{{ Auth::user()->email ?? '' }}" placeholder="enter test email...">
                                <button type="button" id="btnSendTest" class="btn btn-info text-white fw-bold">Send Test</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" id="btnBroadcastNow" class="btn btn-success fw-bold px-4">
                    <i class="ri-send-plane-fill me-1"></i> 🚀 Broadcast to All Active Subscribers
                </button>
            </div>
        </div>
    </div>
</div>
