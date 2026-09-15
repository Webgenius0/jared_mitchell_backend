<div class="accordion" id="contactAccordion">
    {{-- Contact Hero Section --}}
    @php $hero = $cmsData->get('contact_hero'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingHero">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHero" aria-expanded="true" aria-controls="collapseHero">
                <i class="ri-slideshow-line me-2"></i> Hero Section
            </button>
        </h2>
        <div id="collapseHero" class="accordion-collapse collapse show" aria-labelledby="headingHero" data-bs-parent="#contactAccordion">
            <div class="accordion-body">
                <form id="heroForm" action="{{ route('admin.cms.contact.update.hero') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Banner Image</label>
                            <input type="file" name="image_file" class="form-control" accept="image/*">
                            @if($hero?->image)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $hero->image) }}" alt="Hero Image" class="rounded border" style="height: 100px;">
                                </div>
                            @endif
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $hero?->title }}" placeholder="e.g. We're Here To Support You.">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Subtitle</label>
                            <textarea name="sub_title" class="form-control" rows="2" placeholder="e.g. Whether you're reaching out for support...">{{ $hero?->sub_title }}</textarea>
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="button" class="btn btn-primary px-4 save-btn" data-form="heroForm">
                                <i class="ri-save-line me-1"></i> Save Hero Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Spotlight Applications Section --}}
    @php 
        $spotlight = $cmsData->get('contact_spotlight'); 
        $spotlightItems = $spotlight?->metadata ?? [];
    @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingSpotlight">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSpotlight" aria-expanded="false" aria-controls="collapseSpotlight">
                <i class="ri-star-line me-2"></i> Spotlight Story or Talent Application
            </button>
        </h2>
        <div id="collapseSpotlight" class="accordion-collapse collapse" aria-labelledby="headingSpotlight" data-bs-parent="#contactAccordion">
            <div class="accordion-body">
                <form id="spotlightForm" action="{{ route('admin.cms.contact.update.spotlight') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $spotlight?->title }}" placeholder="e.g. Submit a Spotlight Story...">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Subtitle</label>
                            <textarea name="sub_title" class="form-control" rows="2" placeholder="e.g. If you are an artist, business owner...">{{ $spotlight?->sub_title }}</textarea>
                        </div>
                        
                        <div class="col-12 mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Cards (Spotlights)</h6>
                                <button type="button" class="btn btn-sm btn-soft-primary" id="addSpotlightBtn">
                                    <i class="ri-add-line me-1"></i> Add Card
                                </button>
                            </div>
                            <div id="spotlightContainer">
                                @forelse($spotlightItems as $index => $item)
                                    <div class="card border border-dashed mb-3 spotlight-item">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="card-title mb-0">Card #{{ $index + 1 }}</h6>
                                                <button type="button" class="btn btn-sm btn-soft-danger remove-spotlight-btn">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Image</label>
                                                    <input type="file" name="items[{{ $index }}][image_file]" class="form-control form-control-sm" accept="image/*">
                                                    <input type="hidden" name="items[{{ $index }}][existing_image]" value="{{ $item['image'] ?? '' }}">
                                                    @if($item['image'] ?? null)
                                                        <div class="mt-2">
                                                            <img src="{{ asset('storage/' . $item['image']) }}" alt="Image" class="rounded border" style="height: 50px;">
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="mb-2">
                                                        <label class="form-label">Title</label>
                                                        <input type="text" name="items[{{ $index }}][title]" class="form-control form-control-sm" value="{{ $item['title'] ?? '' }}">
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label">Description</label>
                                                        <textarea name="items[{{ $index }}][description]" class="form-control form-control-sm" rows="2">{{ $item['description'] ?? '' }}</textarea>
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label">Link</label>
                                                        <input type="text" name="items[{{ $index }}][link]" class="form-control form-control-sm" value="{{ $item['link'] ?? '' }}" placeholder="e.g. /artist-submission">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-3 spotlight-empty">No cards added yet.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="col-12 text-end mt-4">
                            <button type="button" class="btn btn-primary px-4 save-btn" data-form="spotlightForm">
                                <i class="ri-save-line me-1"></i> Save Spotlight Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Opportunities Section --}}
    @php 
        $opportunities = $cmsData->get('contact_opportunities'); 
        $opportunitiesItems = $opportunities?->metadata ?? [];
    @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingOpportunities">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOpportunities" aria-expanded="false" aria-controls="collapseOpportunities">
                <i class="ri-hand-heart-line me-2"></i> Sponsorship & Vendor Opportunities
            </button>
        </h2>
        <div id="collapseOpportunities" class="accordion-collapse collapse" aria-labelledby="headingOpportunities" data-bs-parent="#contactAccordion">
            <div class="accordion-body">
                <form id="opportunitiesForm" action="{{ route('admin.cms.contact.update.opportunities') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $opportunities?->title }}" placeholder="e.g. Sponsorship & Vendor Opportunities">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Subtitle</label>
                            <textarea name="sub_title" class="form-control" rows="2" placeholder="e.g. If you're interested in sponsoring OSI events...">{{ $opportunities?->sub_title }}</textarea>
                        </div>
                        
                        <div class="col-12 mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Cards (Opportunities)</h6>
                                <button type="button" class="btn btn-sm btn-soft-primary" id="addOpportunitiesBtn">
                                    <i class="ri-add-line me-1"></i> Add Card
                                </button>
                            </div>
                            <div id="opportunitiesContainer">
                                @forelse($opportunitiesItems as $index => $item)
                                    <div class="card border border-dashed mb-3 opportunities-item">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="card-title mb-0">Card #{{ $index + 1 }}</h6>
                                                <button type="button" class="btn btn-sm btn-soft-danger remove-opportunities-btn">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Icon Image</label>
                                                    <input type="file" name="items[{{ $index }}][icon_file]" class="form-control form-control-sm" accept="image/*">
                                                    <input type="hidden" name="items[{{ $index }}][existing_icon]" value="{{ $item['icon'] ?? '' }}">
                                                    @if($item['icon'] ?? null)
                                                        <div class="mt-2">
                                                            <img src="{{ asset('storage/' . $item['icon']) }}" alt="Icon" class="rounded border" style="height: 50px;">
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="mb-2">
                                                        <label class="form-label">Title</label>
                                                        <input type="text" name="items[{{ $index }}][title]" class="form-control form-control-sm" value="{{ $item['title'] ?? '' }}">
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label">Description</label>
                                                        <textarea name="items[{{ $index }}][description]" class="form-control form-control-sm" rows="2">{{ $item['description'] ?? '' }}</textarea>
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label">Link</label>
                                                        <input type="text" name="items[{{ $index }}][link]" class="form-control form-control-sm" value="{{ $item['link'] ?? '' }}" placeholder="e.g. /apply">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-3 opportunities-empty">No cards added yet.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="col-12 text-end mt-4">
                            <button type="button" class="btn btn-primary px-4 save-btn" data-form="opportunitiesForm">
                                <i class="ri-save-line me-1"></i> Save Opportunities Section
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
$(document).ready(function() {
    // Spotlight repeater
    let spotlightIndex = {{ count($spotlightItems) }};
    $('#addSpotlightBtn').click(function() {
        $('.spotlight-empty').remove();
        let html = `
            <div class="card border border-dashed mb-3 spotlight-item">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="card-title mb-0">New Card</h6>
                        <button type="button" class="btn btn-sm btn-soft-danger remove-spotlight-btn"><i class="ri-delete-bin-line"></i></button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Image</label>
                            <input type="file" name="items[${spotlightIndex}][image_file]" class="form-control form-control-sm" accept="image/*">
                        </div>
                        <div class="col-md-8">
                            <div class="mb-2">
                                <label class="form-label">Title</label>
                                <input type="text" name="items[${spotlightIndex}][title]" class="form-control form-control-sm">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Description</label>
                                <textarea name="items[${spotlightIndex}][description]" class="form-control form-control-sm" rows="2"></textarea>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Link</label>
                                <input type="text" name="items[${spotlightIndex}][link]" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
        $('#spotlightContainer').append(html);
        spotlightIndex++;
    });

    $(document).on('click', '.remove-spotlight-btn', function() {
        $(this).closest('.spotlight-item').remove();
    });

    // Opportunities repeater
    let opportunitiesIndex = {{ count($opportunitiesItems) }};
    $('#addOpportunitiesBtn').click(function() {
        $('.opportunities-empty').remove();
        let html = `
            <div class="card border border-dashed mb-3 opportunities-item">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="card-title mb-0">New Card</h6>
                        <button type="button" class="btn btn-sm btn-soft-danger remove-opportunities-btn"><i class="ri-delete-bin-line"></i></button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Icon Image</label>
                            <input type="file" name="items[${opportunitiesIndex}][icon_file]" class="form-control form-control-sm" accept="image/*">
                        </div>
                        <div class="col-md-8">
                            <div class="mb-2">
                                <label class="form-label">Title</label>
                                <input type="text" name="items[${opportunitiesIndex}][title]" class="form-control form-control-sm">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Description</label>
                                <textarea name="items[${opportunitiesIndex}][description]" class="form-control form-control-sm" rows="2"></textarea>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Link</label>
                                <input type="text" name="items[${opportunitiesIndex}][link]" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
        $('#opportunitiesContainer').append(html);
        opportunitiesIndex++;
    });

    $(document).on('click', '.remove-opportunities-btn', function() {
        $(this).closest('.opportunities-item').remove();
    });

    // Form submission
    $('.save-btn').click(function(e) {
        e.preventDefault();
        let formId = $(this).data('form');
        let form = $('#' + formId);
        let formData = new FormData(form[0]);
        let url = form.attr('action');

        let btn = $(this);
        let originalText = btn.html();
        btn.html('<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> Saving...');
        btn.prop('disabled', true);

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                btn.html(originalText);
                btn.prop('disabled', false);
                if (response.success) {
                    Toast.success(response.message);
                } else {
                    Toast.error(response.message || 'Something went wrong');
                }
            },
            error: function(xhr) {
                btn.html(originalText);
                btn.prop('disabled', false);
                if(xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    let errorMsg = '';
                    for (let key in errors) {
                        errorMsg += errors[key][0] + '<br>';
                    }
                    Toast.error(errorMsg);
                } else {
                    Toast.error('An error occurred while saving.');
                }
            }
        });
    });
});
</script>
@endpush
