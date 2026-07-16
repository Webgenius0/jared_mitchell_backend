<div class="accordion custom-accordionwithicon custom-accordion-border accordion-border-box" id="spotlightLadderAccordion">
    
    {{-- Hero Section --}}
    @php $hero = $cmsData->get('spotlight_ladder_hero'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingHero">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHero" aria-expanded="true" aria-controls="collapseHero">
                <i class="ri-image-line me-2"></i> Weekly Spotlight Ladder Hero
            </button>
        </h2>
        <div id="collapseHero" class="accordion-collapse collapse show" aria-labelledby="headingHero" data-bs-parent="#spotlightLadderAccordion">
            <div class="accordion-body">
                <form id="heroForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Hero Title</label>
                            <textarea name="title" class="form-control" rows="2" placeholder="Enter hero title">{{ $hero?->title }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Hero Description</label>
                            <textarea name="sub_title" class="form-control" rows="3" placeholder="Enter hero description">{{ $hero?->sub_title }}</textarea>
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

    {{-- Details Section --}}
    @php 
        $details = $cmsData->get('spotlight_ladder_details'); 
        $detailItems = $details?->metadata ?? [];
    @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingDetails">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDetails" aria-expanded="false" aria-controls="collapseDetails">
                <i class="ri-list-check-2 me-2"></i> Details Section
            </button>
        </h2>
        <div id="collapseDetails" class="accordion-collapse collapse" aria-labelledby="headingDetails" data-bs-parent="#spotlightLadderAccordion">
            <div class="accordion-body">
                <form id="detailsForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $details?->title }}" placeholder="e.g. Spotlight Ladder Details">
                        </div>

                        <div class="col-12 mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Items (Heading & Description)</h6>
                                <button type="button" class="btn btn-sm btn-soft-primary" id="addDetailBtn">
                                    <i class="ri-add-line me-1"></i> Add Item
                                </button>
                            </div>
                            <div id="detailsContainer">
                                @forelse($detailItems as $index => $item)
                                    <div class="card border border-dashed mb-3 detail-item">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="card-title mb-0">Item #{{ $index + 1 }}</h6>
                                                <button type="button" class="btn btn-sm btn-soft-danger remove-detail-btn">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-12">
                                                    <label class="form-label">Heading</label>
                                                    <input type="text" name="items[{{ $index }}][heading]" class="form-control" value="{{ $item['heading'] ?? '' }}" placeholder="Enter heading">
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="form-label">Description</label>
                                                    <textarea name="items[{{ $index }}][description]" class="form-control" rows="3" placeholder="Enter description">{{ $item['description'] ?? '' }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-3 details-empty">No items added yet.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveDetailsBtn">
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
        axios.post("{{ route('admin.cms.spotlight_ladder.update.hero') }}", formData)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // Details Logic
    let detailCount = {{ count($detailItems) }};
    $('#addDetailBtn').on('click', function() {
        const $container = $('#detailsContainer');
        $container.find('.details-empty').remove();
        const card = `
            <div class="card border border-dashed mb-3 detail-item">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="card-title mb-0">New Item</h6>
                        <button type="button" class="btn btn-sm btn-soft-danger remove-detail-btn">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Heading</label>
                            <input type="text" name="items[${detailCount}][heading]" class="form-control" placeholder="Enter heading">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="items[${detailCount}][description]" class="form-control" rows="3" placeholder="Enter description"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $container.append(card);
        detailCount++;
    });
    $(document).on('click', '.remove-detail-btn', function() {
        $(this).closest('.detail-item').remove();
        if ($('#detailsContainer .detail-item').length === 0) {
            $('#detailsContainer').append('<div class="text-center text-muted py-3 details-empty">No items added yet.</div>');
        }
    });
    $('#detailsForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveDetailsBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        axios.post("{{ route('admin.cms.spotlight_ladder.update.details') }}", formData)
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
