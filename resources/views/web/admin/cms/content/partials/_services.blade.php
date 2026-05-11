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
                            <label class="form-label">Background Image</label>
                            <input type="file" name="bg_image" class="form-control" accept="image/*">
                            @if($hero?->image)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $hero->image) }}" alt="Hero Background" class="rounded border" style="max-height: 150px;">
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
                                    <img src="{{ asset('storage/' . $grow->image) }}" alt="Grow Image" class="rounded border" style="max-height: 200px; width: auto;">
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
                                                        <img src="{{ asset('storage/' . $item['image']) }}" alt="Partner" class="img-fluid rounded mb-2" style="max-height: 50px;">
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
                                                <div class="mb-2">
                                                    <label class="form-label small mb-1">Icon Class (Remix Icon)</label>
                                                    <input type="text" name="items[{{ $index }}][icon]" class="form-control form-control-sm" value="{{ $item['icon'] ?? '' }}" placeholder="ri-user-line">
                                                </div>
                                                <div class="text-center">
                                                    <label class="form-label small mb-1">Or Upload Image</label>
                                                    <input type="file" name="items[{{ $index }}][image_file]" class="form-control form-control-sm mb-2" accept="image/*">
                                                    <input type="hidden" name="items[{{ $index }}][existing_image]" value="{{ $item['image'] ?? '' }}">
                                                    @if($item['image'] ?? null)
                                                        <img src="{{ asset('storage/' . $item['image']) }}" alt="Audience" class="img-fluid rounded mb-2" style="max-height: 40px;">
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
                        </div>
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
});
</script>
@endpush
