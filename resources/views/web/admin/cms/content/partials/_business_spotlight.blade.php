<div class="accordion custom-accordionwithicon custom-accordion-border accordion-border-box" id="businessSpotlightAccordion">
    
    {{-- Hero Section --}}
    @php $hero = $cmsData->get('business_spotlight_hero'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingHero">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHero" aria-expanded="true" aria-controls="collapseHero">
                <i class="ri-image-line me-2"></i> Hero Section
            </button>
        </h2>
        <div id="collapseHero" class="accordion-collapse collapse show" aria-labelledby="headingHero" data-bs-parent="#businessSpotlightAccordion">
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

    {{-- Video Section --}}
    @php $video = $cmsData->get('business_spotlight_video'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingVideo">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVideo" aria-expanded="false" aria-controls="collapseVideo">
                <i class="ri-video-line me-2"></i> Video Section
            </button>
        </h2>
        <div id="collapseVideo" class="accordion-collapse collapse" aria-labelledby="headingVideo" data-bs-parent="#businessSpotlightAccordion">
            <div class="accordion-body">
                <form id="videoForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Video Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $video?->title }}" placeholder="Enter video section title">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Video Subtitle</label>
                            <textarea name="sub_title" class="form-control" rows="2" placeholder="Enter video section subtitle">{{ $video?->sub_title }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Video URL (YouTube/Vimeo)</label>
                            <input type="url" name="video_url" class="form-control" value="{{ $video?->description }}" placeholder="https://www.youtube.com/watch?v=...">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Video Thumbnail</label>
                            <input type="file" name="thumbnail" class="form-control" accept="image/*">
                            @if($video?->image)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $video->image) }}" alt="Video Thumbnail" class="rounded border" style="max-height: 150px;">
                                </div>
                            @endif
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveVideoBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- List Section --}}
    @php $list = $cmsData->get('business_spotlight_list'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingList">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseList" aria-expanded="false" aria-controls="collapseList">
                <i class="ri-group-line me-2"></i> Business List Header
            </button>
        </h2>
        <div id="collapseList" class="accordion-collapse collapse" aria-labelledby="headingList" data-bs-parent="#businessSpotlightAccordion">
            <div class="accordion-body">
                <form id="listForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $list?->title }}" placeholder="e.g. Discover More Business">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Section Subtitle</label>
                            <textarea name="sub_title" class="form-control" rows="3" placeholder="Enter section description">{{ $list?->sub_title }}</textarea>
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveListBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Highlights Section --}}
    @php $highlights = $cmsData->get('business_spotlight_highlights'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingHighlights">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHighlights" aria-expanded="false" aria-controls="collapseHighlights">
                <i class="ri-star-line me-2"></i> Highlights Header
            </button>
        </h2>
        <div id="collapseHighlights" class="accordion-collapse collapse" aria-labelledby="headingHighlights" data-bs-parent="#businessSpotlightAccordion">
            <div class="accordion-body">
                <form id="highlightsForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $highlights?->title }}" placeholder="e.g. Past Six Months Highlights">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Section Subtitle</label>
                            <textarea name="sub_title" class="form-control" rows="3" placeholder="Enter section description">{{ $highlights?->sub_title }}</textarea>
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveHighlightsBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Picks Section --}}
    @php $picks = $cmsData->get('business_spotlight_picks'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingPicks">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePicks" aria-expanded="false" aria-controls="collapsePicks">
                <i class="ri-thumb-up-line me-2"></i> Editor's Picks Header
            </button>
        </h2>
        <div id="collapsePicks" class="accordion-collapse collapse" aria-labelledby="headingPicks" data-bs-parent="#businessSpotlightAccordion">
            <div class="accordion-body">
                <form id="picksForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $picks?->title }}" placeholder="e.g. Editor's Picks">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Section Subtitle</label>
                            <textarea name="sub_title" class="form-control" rows="3" placeholder="Enter section description">{{ $picks?->sub_title }}</textarea>
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="savePicksBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Ladder Section --}}
    @php $ladder = $cmsData->get('business_spotlight_ladder'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingLadder">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLadder" aria-expanded="false" aria-controls="collapseLadder">
                <i class="ri-bar-chart-line me-2"></i> Spotlight Ladder Header
            </button>
        </h2>
        <div id="collapseLadder" class="accordion-collapse collapse" aria-labelledby="headingLadder" data-bs-parent="#businessSpotlightAccordion">
            <div class="accordion-body">
                <form id="ladderForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $ladder?->title }}" placeholder="e.g. OSI Spotlight Ladder">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Section Subtitle</label>
                            <textarea name="sub_title" class="form-control" rows="3" placeholder="Enter section description">{{ $ladder?->sub_title }}</textarea>
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveLadderBtn">
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
        axios.post("{{ route('admin.cms.business_spotlight.update.hero') }}", formData)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // Video Logic
    $('#videoForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveVideoBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        axios.post("{{ route('admin.cms.business_spotlight.update.video') }}", formData)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // List Logic
    $('#listForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveListBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        axios.post("{{ route('admin.cms.business_spotlight.update.list') }}", formData)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // Highlights Logic
    $('#highlightsForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveHighlightsBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        axios.post("{{ route('admin.cms.business_spotlight.update.highlights') }}", formData)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // Picks Logic
    $('#picksForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#savePicksBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        axios.post("{{ route('admin.cms.business_spotlight.update.picks') }}", formData)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // Ladder Logic
    $('#ladderForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveLadderBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        axios.post("{{ route('admin.cms.business_spotlight.update.ladder') }}", formData)
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
