<div class="accordion custom-accordionwithicon custom-accordion-border accordion-border-box" id="serviceAccordion">
    
    {{-- Hero Section --}}
    @php $hero = $cmsData->get('services_hero'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingHero">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHero" aria-expanded="true" aria-controls="collapseHero">
                <i class="ri-image-line me-2"></i> Hero Section
            </button>
        </h2>
        <div id="collapseHero" class="accordion-collapse collapse show" aria-labelledby="headingHero" data-bs-parent="#serviceAccordion">
            <div class="accordion-body">
                <form id="heroForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Hero Title</label>
                            <textarea name="title" class="form-control" rows="2" placeholder="Enter hero title">{{ $hero?->title }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Hero Sub Title</label>
                            <textarea name="sub_title" class="form-control" rows="2" placeholder="Enter hero sub title">{{ $hero?->sub_title }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Background Image</label>
                            <input type="file" name="bg_image" class="form-control" accept="image/*">
                            @if($hero?->image)
                                <div class="mt-2">
                                    <img src="{{ asset($hero->image) }}" alt="Hero Background" class="rounded border" style="max-height: 150px;">
                                </div>
                            @endif
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveHeroBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Overview Section --}}
    @php $overview = $cmsData->get('services_overview'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingOverview">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOverview" aria-expanded="false" aria-controls="collapseOverview">
                <i class="ri-text-wrap me-2"></i> Services Overview
            </button>
        </h2>
        <div id="collapseOverview" class="accordion-collapse collapse" aria-labelledby="headingOverview" data-bs-parent="#serviceAccordion">
            <div class="accordion-body">
                <form id="overviewForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Description Content</label>
                            <textarea name="description" class="form-control" rows="6" placeholder="Enter overview description">{{ $overview?->description }}</textarea>
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveOverviewBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Grow Section --}}
    @php $grow = $cmsData->get('services_grow'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingGrow">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGrow" aria-expanded="false" aria-controls="collapseGrow">
                <i class="ri-funds-line me-2"></i> Grow With Us Section
            </button>
        </h2>
        <div id="collapseGrow" class="accordion-collapse collapse" aria-labelledby="headingGrow" data-bs-parent="#serviceAccordion">
            <div class="accordion-body">
                <form id="growForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $grow?->title }}" placeholder="e.g. Grow With Our Social Image">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description Content</label>
                            <textarea name="description" class="form-control" rows="6" placeholder="Enter grow section description">{{ $grow?->description }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Side Image</label>
                            <input type="file" name="image_file" class="form-control" accept="image/*">
                            @if($grow?->image)
                                <div class="mt-2">
                                    <img src="{{ asset($grow->image) }}" alt="Grow Image" class="rounded border" style="max-height: 200px; width: auto;">
                                </div>
                            @endif
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveGrowBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Partners Section --}}
    @php 
        $partners = $cmsData->get('services_partners'); 
        $partnerItems = $partners?->metadata ?? [];
    @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingPartners">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePartners" aria-expanded="false" aria-controls="collapsePartners">
                <i class="ri-team-line me-2"></i> Partners & Sponsors Section
            </button>
        </h2>
        <div id="collapsePartners" class="accordion-collapse collapse" aria-labelledby="headingPartners" data-bs-parent="#serviceAccordion">
            <div class="accordion-body">
                <form id="partnersForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $partners?->title }}" placeholder="e.g. Our Members Grow With the Support of Our Partners and Sponsors">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Section Subtitle</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Enter section description">{{ $partners?->description }}</textarea>
                        </div>
                        
                        <div class="col-md-12 mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label mb-0">Partner Logos & Links</label>
                                <button type="button" class="btn btn-sm btn-primary" id="addPartnerBtn">
                                    <i class="ri-add-line me-1"></i> Add Partner
                                </button>
                            </div>
                            <div class="row g-3" id="partnersContainer">
                                @forelse($partnerItems as $index => $item)
                                    <div class="col-md-4 partner-item">
                                        <div class="card border shadow-none mb-0">
                                            <div class="card-body p-2">
                                                <div class="text-end mb-2">
                                                    <button type="button" class="btn btn-sm btn-soft-danger remove-partner-btn">
                                                        <i class="ri-close-line"></i>
                                                    </button>
                                                </div>
                                                <div class="text-center">
                                                    <input type="file" name="items[{{ $index }}][image_file]" class="form-control form-control-sm mb-2" accept="image/*">
                                                    <input type="hidden" name="items[{{ $index }}][existing_image]" value="{{ $item['image'] ?? '' }}">
                                                    @if($item['image'] ?? null)
                                                        <img src="{{ asset($item['image']) }}" alt="Partner" class="img-fluid rounded mb-2" style="max-height: 50px;">
                                                    @endif
                                                </div>
                                                <div class="mt-3">
                                                    <label class="form-label small mb-1">Website Link</label>
                                                    <input type="url" name="items[{{ $index }}][link]" class="form-control form-control-sm" value="{{ $item['link'] ?? '' }}" placeholder="https://...">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-center text-muted py-3 partners-empty">No partner logos added yet.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="savePartnersBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Who OSI Is For Section --}}
    @php 
        $whoFor = $cmsData->get('services_who_for'); 
        $whoForItems = $whoFor?->metadata ?? [];
    @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingWhoFor">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseWhoFor" aria-expanded="false" aria-controls="collapseWhoFor">
                <i class="ri-user-search-line me-2"></i> Who OSI Is For Section
            </button>
        </h2>
        <div id="collapseWhoFor" class="accordion-collapse collapse" aria-labelledby="headingWhoFor" data-bs-parent="#serviceAccordion">
            <div class="accordion-body">
                <form id="whoForForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $whoFor?->title }}" placeholder="e.g. Who OSI Is For">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Section Subtitle</label>
                            <input type="text" name="sub_title" class="form-control" value="{{ $whoFor?->sub_title }}" placeholder="Enter subtitle">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Bottom Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Enter bottom description">{{ $whoFor?->description }}</textarea>
                        </div>
                        
                        <div class="col-md-12 mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label mb-0">Audience Cards (Icon/Image + Title)</label>
                                <button type="button" class="btn btn-sm btn-primary" id="addWhoForBtn">
                                    <i class="ri-add-line me-1"></i> Add Card
                                </button>
                            </div>
                            <div class="row g-3" id="whoForContainer">
                                @forelse($whoForItems as $index => $item)
                                    <div class="col-md-4 who-for-item">
                                        <div class="card border shadow-none mb-0">
                                            <div class="card-body p-2">
                                                <div class="text-end mb-2">
                                                    <button type="button" class="btn btn-sm btn-soft-danger remove-who-for-btn">
                                                        <i class="ri-close-line"></i>
                                                    </button>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label small mb-1">Title</label>
                                                    <input type="text" name="items[{{ $index }}][title]" class="form-control form-control-sm" value="{{ $item['title'] ?? '' }}" placeholder="e.g. CREATORS">
                                                </div>
                                                <!-- <div class="mb-2">
                                                    <label class="form-label small mb-1">Icon Class (Remix Icon)</label>
                                                    <input type="text" name="items[{{ $index }}][icon]" class="form-control form-control-sm" value="{{ $item['icon'] ?? '' }}" placeholder="ri-user-line">
                                                </div> -->
                                                <div class="text-center">
                                                    <label class="form-label small mb-1">Upload Image</label>
                                                    <input type="file" name="items[{{ $index }}][image_file]" class="form-control form-control-sm mb-2" accept="image/*">
                                                    <input type="hidden" name="items[{{ $index }}][existing_image]" value="{{ $item['image'] ?? '' }}">
                                                    @if($item['image'] ?? null)
                                                        <img src="{{ asset($item['image']) }}" alt="Audience" class="img-fluid rounded mb-2" style="max-height: 40px;">
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-center text-muted py-3 who-for-empty">No audience cards added yet.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveWhoForBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Artist Spotlight Section --}}
    @php $artistSpotlight = $cmsData->get('services_artist_spotlight'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingArtistSpotlight">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseArtistSpotlight" aria-expanded="false" aria-controls="collapseArtistSpotlight">
                <i class="ri-star-line me-2"></i> Artist Spotlight Form
            </button>
        </h2>
        <div id="collapseArtistSpotlight" class="accordion-collapse collapse" aria-labelledby="headingArtistSpotlight" data-bs-parent="#serviceAccordion">
            <div class="accordion-body">
                <form id="artistSpotlightForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Form Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $artistSpotlight?->title }}" placeholder="e.g. Artist Spotlight Submission Form">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Subtitle Text</label>
                            <textarea name="sub_title" class="form-control" rows="3" placeholder="Enter subtitle">{{ $artistSpotlight?->sub_title }}</textarea>
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveArtistSpotlightBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Business Spotlight Section --}}
    @php $businessSpotlight = $cmsData->get('services_business_spotlight'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingBusinessSpotlight">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBusinessSpotlight" aria-expanded="false" aria-controls="collapseBusinessSpotlight">
                <i class="ri-briefcase-line me-2"></i> Business Spotlight Form
            </button>
        </h2>
        <div id="collapseBusinessSpotlight" class="accordion-collapse collapse" aria-labelledby="headingBusinessSpotlight" data-bs-parent="#serviceAccordion">
            <div class="accordion-body">
                <form id="businessSpotlightForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Form Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $businessSpotlight?->title }}" placeholder="e.g. BUSINESS SPOTLIGHT Submission Form">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Subtitle Text</label>
                            <textarea name="sub_title" class="form-control" rows="3" placeholder="Enter subtitle">{{ $businessSpotlight?->sub_title }}</textarea>
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveBusinessSpotlightBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- FAQ Section --}}
    @php $faq = $cmsData->get('services_faq'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingFaq">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFaq" aria-expanded="false" aria-controls="collapseFaq">
                <i class="ri-question-answer-line me-2"></i> Frequently Asked Questions
            </button>
        </h2>
        <div id="collapseFaq" class="accordion-collapse collapse" aria-labelledby="headingFaq" data-bs-parent="#serviceAccordion">
            <div class="accordion-body">
                <form id="faqForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $faq?->title }}" placeholder="e.g. Frequently Asked Questions">
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveFaqBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Risk Free Section --}}
    @php 
        $riskFree = $cmsData->get('services_risk_free'); 
        $riskFreePoints = is_array($riskFree?->metadata) ? $riskFree->metadata : [];
    @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingRiskFree">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRiskFree" aria-expanded="false" aria-controls="collapseRiskFree">
                <i class="ri-shield-check-line me-2"></i> Risk Free Section
            </button>
        </h2>
        <div id="collapseRiskFree" class="accordion-collapse collapse" aria-labelledby="headingRiskFree" data-bs-parent="#serviceAccordion">
            <div class="accordion-body">
                <form id="riskFreeForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $riskFree?->title }}" placeholder="e.g. Try OSI Risk-Free">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Section Subtitle/Description</label>
                            <textarea name="sub_title" class="form-control" rows="3" placeholder="Enter subtitle">{{ $riskFree?->sub_title }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Feature Points (One per line)</label>
                            <textarea name="points_raw" class="form-control" rows="4" placeholder="Cancel Anytime&#10;No Penalties&#10;Flexible Plans">{{ implode("\n", $riskFreePoints) }}</textarea>
                            <small class="text-muted">Enter each point on a new line.</small>
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveRiskFreeBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Newsletter Section --}}
    @php $newsletter = $cmsData->get('services_newsletter'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingNewsletter">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNewsletter" aria-expanded="false" aria-controls="collapseNewsletter">
                <i class="ri-mail-send-line me-2"></i> Newsletter Section
            </button>
        </h2>
        <div id="collapseNewsletter" class="accordion-collapse collapse" aria-labelledby="headingNewsletter" data-bs-parent="#serviceAccordion">
            <div class="accordion-body">
                <form id="newsletterForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Newsletter Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $newsletter?->title }}" placeholder="e.g. Sign up for the newsletter">
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveNewsletterBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
$(function() {
    // Hero Logic
    $('#heroForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveHeroBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        axios.post("{{ route('admin.cms.services.update.hero') }}", formData)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // Overview Logic
    $('#overviewForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveOverviewBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        axios.post("{{ route('admin.cms.services.update.overview') }}", formData)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // Grow Logic
    $('#growForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveGrowBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        axios.post("{{ route('admin.cms.services.update.grow') }}", formData)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // Partners Logic
    let partnerCount = {{ count($partnerItems) }};
    $('#addPartnerBtn').on('click', function() {
        const $container = $('#partnersContainer');
        $container.find('.partners-empty').remove();
        const item = `
            <div class="col-md-4 partner-item">
                <div class="card border shadow-none mb-0">
                    <div class="card-body p-2">
                        <div class="text-end mb-2">
                            <button type="button" class="btn btn-sm btn-soft-danger remove-partner-btn">
                                <i class="ri-close-line"></i>
                            </button>
                        </div>
                        <div class="text-center">
                            <input type="file" name="items[${partnerCount}][image_file]" class="form-control form-control-sm mb-2" accept="image/*">
                            <div class="bg-light rounded py-3 text-muted" style="font-size: 10px;">New Logo</div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label small mb-1">Website Link</label>
                            <input type="url" name="items[${partnerCount}][link]" class="form-control form-control-sm" placeholder="https://...">
                        </div>
                    </div>
                </div>
            </div>
        `;
        $container.append(item);
        partnerCount++;
    });

    $(document).on('click', '.remove-partner-btn', function() {
        $(this).closest('.partner-item').remove();
        if ($('#partnersContainer .partner-item').length === 0) {
            $('#partnersContainer').append('<div class="col-12 text-center text-muted py-3 partners-empty">No partner logos added yet.</div>');
        }
    });

    $('#partnersForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#savePartnersBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        axios.post("{{ route('admin.cms.services.update.partners') }}", formData)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // Who For Logic
    let whoForCount = {{ count($whoForItems) }};
    $('#addWhoForBtn').on('click', function() {
        const $container = $('#whoForContainer');
        $container.find('.who-for-empty').remove();
        const item = `
            <div class="col-md-4 who-for-item">
                <div class="card border shadow-none mb-0">
                    <div class="card-body p-2">
                        <div class="text-end mb-2">
                            <button type="button" class="btn btn-sm btn-soft-danger remove-who-for-btn">
                                <i class="ri-close-line"></i>
                            </button>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1">Title</label>
                            <input type="text" name="items[${whoForCount}][title]" class="form-control form-control-sm" placeholder="e.g. CREATORS">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1">Icon Class (Remix Icon)</label>
                            <input type="text" name="items[${whoForCount}][icon]" class="form-control form-control-sm" placeholder="ri-user-line">
                        </div> -->
                        <div class="text-center">
                            <label class="form-label small mb-1">Or Upload Image</label>
                            <input type="file" name="items[${whoForCount}][image_file]" class="form-control form-control-sm mb-2" accept="image/*">
                        </div>
                    </div>
                </div>
            </div>
        `;
        $container.append(item);
        whoForCount++;
    });

    $(document).on('click', '.remove-who-for-btn', function() {
        $(this).closest('.who-for-item').remove();
        if ($('#whoForContainer .who-for-item').length === 0) {
            $('#whoForContainer').append('<div class="col-12 text-center text-muted py-3 who-for-empty">No audience cards added yet.</div>');
        }
    });

    $('#whoForForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveWhoForBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        axios.post("{{ route('admin.cms.services.update.who_for') }}", formData)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // Artist Spotlight Logic
    $('#artistSpotlightForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveArtistSpotlightBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        axios.post("{{ route('admin.cms.services.update.artist_spotlight') }}", formData)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // Business Spotlight Logic
    $('#businessSpotlightForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveBusinessSpotlightBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        axios.post("{{ route('admin.cms.services.update.business_spotlight') }}", formData)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // FAQ Logic
    $('#faqForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveFaqBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = $(this).serialize();
        axios.post("{{ route('admin.cms.services.update.faq') }}", formData)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // Risk Free Logic
    $('#riskFreeForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveRiskFreeBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        
        const formData = new FormData(this);
        // Convert raw points text to array
        const pointsRaw = formData.get('points_raw');
        const points = pointsRaw.split('\n').map(p => p.trim()).filter(p => p !== '');
        
        // Remove raw text and add formatted points
        formData.delete('points_raw');
        points.forEach((point, index) => {
            formData.append(`points[${index}]`, point);
        });

        axios.post("{{ route('admin.cms.services.update.risk_free') }}", formData)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // Newsletter Logic
    $('#newsletterForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveNewsletterBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        axios.post("{{ route('admin.cms.services.update.newsletter') }}", formData)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });
});
</script>
@endpush
