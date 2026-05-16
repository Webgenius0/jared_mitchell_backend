<div class="accordion custom-accordionwithicon custom-accordion-border accordion-border-box" id="sponsorshipAccordion">
    
    {{-- Hero Section --}}
    @php $hero = $cmsData->get('sponsorship_page_hero'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingHero">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHero" aria-expanded="true" aria-controls="collapseHero">
                <i class="ri-image-line me-2"></i> Hero Section
            </button>
        </h2>
        <div id="collapseHero" class="accordion-collapse collapse show" aria-labelledby="headingHero" data-bs-parent="#sponsorshipAccordion">
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
    @php $video = $cmsData->get('sponsorship_page_video'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingVideo">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVideo" aria-expanded="false" aria-controls="collapseVideo">
                <i class="ri-video-line me-2"></i> Video Section
            </button>
        </h2>
        <div id="collapseVideo" class="accordion-collapse collapse" aria-labelledby="headingVideo" data-bs-parent="#sponsorshipAccordion">
            <div class="accordion-body">
                <form id="videoForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Video URL (YouTube/Vimeo)</label>
                            <input type="url" name="video_url" class="form-control" value="{{ $video?->sub_title }}" placeholder="https://youtube.com/...">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Video Thumbnail</label>
                            <input type="file" name="video_thumbnail" class="form-control" accept="image/*">
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

    {{-- Why Section --}}
    @php 
        $why = $cmsData->get('sponsorship_page_why'); 
        $meta = $why?->metadata ?? [];
    @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingWhy">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseWhy" aria-expanded="false" aria-controls="collapseWhy">
                <i class="ri-question-line me-2"></i> Why Your Sponsorship Matters Section
            </button>
        </h2>
        <div id="collapseWhy" class="accordion-collapse collapse" aria-labelledby="headingWhy" data-bs-parent="#sponsorshipAccordion">
            <div class="accordion-body">
                <form id="whyForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $why?->title }}" placeholder="e.g. Why Your Sponsorship Matters">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Intro Description</label>
                            <textarea name="intro" class="form-control" rows="3" placeholder="Enter intro description">{{ $why?->sub_title }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Sponsorship Funding Directly Supports: (One per line)</label>
                            <textarea name="supports" class="form-control" rows="5" placeholder="1. Community events...\n2. Artist & business...">{{ isset($meta['supports']) ? (is_array($meta['supports']) ? implode("\n", $meta['supports']) : $meta['supports']) : '' }}</textarea>
                        </div>

                        <hr>
                        <h6>Feature Cards (3 items)</h6>
                        @for($i = 0; $i < 3; $i++)
                            @php $item = $meta['features'][$i] ?? null; @endphp
                            <div class="col-md-4 border p-3 rounded mb-2">
                                <h7 class="fw-bold mb-2 d-block">Card {{ $i + 1 }}</h7>
                                <div class="mb-2">
                                    <label class="form-label fs-12">Icon (Remix Icon Class)</label>
                                    <input type="text" name="features[{{ $i }}][icon]" class="form-control form-control-sm" value="{{ $item['icon'] ?? '' }}" placeholder="e.g. ri-video-line">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fs-12">Title</label>
                                    <input type="text" name="features[{{ $i }}][title]" class="form-control form-control-sm" value="{{ $item['title'] ?? '' }}" placeholder="Enter title">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fs-12">Description</label>
                                    <textarea name="features[{{ $i }}][description]" class="form-control form-control-sm" rows="2" placeholder="Enter description">{{ $item['description'] ?? '' }}</textarea>
                                </div>
                            </div>
                        @endfor

                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveWhyBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Steps Section --}}
    @php $steps = $cmsData->get('sponsorship_page_steps'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingSteps">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSteps" aria-expanded="false" aria-controls="collapseSteps">
                <i class="ri-list-check-2 me-2"></i> How Sponsorship Works (5 Steps)
            </button>
        </h2>
        <div id="collapseSteps" class="accordion-collapse collapse" aria-labelledby="headingSteps" data-bs-parent="#sponsorshipAccordion">
            <div class="accordion-body">
                <form id="stepsForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $steps?->title }}" placeholder="e.g. How Sponsorship Works">
                        </div>

                        <hr>
                        <h6>Sponsorship Steps (5 steps)</h6>
                        @for($i = 0; $i < 5; $i++)
                            @php $item = $steps?->metadata[$i] ?? null; @endphp
                            <div class="col-md-12 border p-3 rounded mb-3">
                                <h7 class="fw-bold mb-2 d-block">Step {{ $i + 1 }}</h7>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label fs-12">Step Title</label>
                                        <input type="text" name="items[{{ $i }}][title]" class="form-control form-control-sm" value="{{ $item['title'] ?? '' }}" placeholder="e.g. 1. Choose Your Tier">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label fs-12">Description</label>
                                        <input type="text" name="items[{{ $i }}][description]" class="form-control form-control-sm" value="{{ $item['description'] ?? '' }}" placeholder="Enter short description">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fs-12">List Items (Optional, One per line)</label>
                                        <textarea name="items[{{ $i }}][list]" class="form-control form-control-sm" rows="3" placeholder="Item 1\nItem 2...">{{ isset($item['list']) ? (is_array($item['list']) ? implode("\n", $item['list']) : $item['list']) : '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endfor

                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveStepsBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Levels Header Section --}}
    @php $levelsHeader = $cmsData->get('sponsorship_page_levels_header'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingLevelsHeader">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLevelsHeader" aria-expanded="false" aria-controls="collapseLevelsHeader">
                <i class="ri-heading me-2"></i> Sponsorship Levels Header
            </button>
        </h2>
        <div id="collapseLevelsHeader" class="accordion-collapse collapse" aria-labelledby="headingLevelsHeader" data-bs-parent="#sponsorshipAccordion">
            <div class="accordion-body">
                <form id="levelsHeaderForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $levelsHeader?->title }}" placeholder="e.g. Choose Your Sponsorship Level">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Section Description</label>
                            <textarea name="sub_title" class="form-control" rows="2" placeholder="Enter section description">{{ $levelsHeader?->sub_title }}</textarea>
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveLevelsHeaderBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Footer Section --}}
    @php $footer = $cmsData->get('sponsorship_page_footer'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingFooter">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFooter" aria-expanded="false" aria-controls="collapseFooter">
                <i class="ri-door-open-line me-2"></i> Sponsorship Footer Section
            </button>
        </h2>
        <div id="collapseFooter" class="accordion-collapse collapse" aria-labelledby="headingFooter" data-bs-parent="#sponsorshipAccordion">
            <div class="accordion-body">
                <form id="footerForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $footer?->title }}" placeholder="e.g. Become a Sponsor Today">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Section Description</label>
                            <textarea name="sub_title" class="form-control" rows="2" placeholder="Enter section description">{{ $footer?->sub_title }}</textarea>
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveFooterBtn">
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
        axios.post("{{ route('admin.cms.sponsorship.update.hero') }}", formData)
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
        axios.post("{{ route('admin.cms.sponsorship.update.video') }}", formData)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // Why Logic
    $('#whyForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveWhyBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        
        const formData = new FormData(this);
        const data = {};
        formData.forEach((value, key) => {
            if (key === 'supports') {
                data[key] = value.split('\n').filter(item => item.trim() !== '');
            } else {
                const keys = key.split(/[\[\]]+/).filter(k => k !== '');
                let current = data;
                for (let i = 0; i < keys.length; i++) {
                    const k = keys[i];
                    if (i === keys.length - 1) {
                        current[k] = value;
                    } else {
                        current[k] = current[k] || (isNaN(keys[i+1]) ? {} : []);
                        current = current[k];
                    }
                }
            }
        });

        axios.post("{{ route('admin.cms.sponsorship.update.why') }}", data)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // Steps Logic
    $('#stepsForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveStepsBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        
        const formData = new FormData(this);
        const data = {};
        formData.forEach((value, key) => {
            if (key.includes('[list]')) {
                const keys = key.split(/[\[\]]+/).filter(k => k !== '');
                let current = data;
                for (let i = 0; i < keys.length; i++) {
                    const k = keys[i];
                    if (i === keys.length - 1) {
                        current[k] = value.split('\n').filter(item => item.trim() !== '');
                    } else {
                        current[k] = current[k] || (isNaN(keys[i+1]) ? {} : []);
                        current = current[k];
                    }
                }
            } else {
                const keys = key.split(/[\[\]]+/).filter(k => k !== '');
                let current = data;
                for (let i = 0; i < keys.length; i++) {
                    const k = keys[i];
                    if (i === keys.length - 1) {
                        current[k] = value;
                    } else {
                        current[k] = current[k] || (isNaN(keys[i+1]) ? {} : []);
                        current = current[k];
                    }
                }
            }
        });

        axios.post("{{ route('admin.cms.sponsorship.update.steps') }}", data)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // Levels Header Logic
    $('#levelsHeaderForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveLevelsHeaderBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        axios.post("{{ route('admin.cms.sponsorship.update.levels_header') }}", formData)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // Footer Logic
    $('#footerForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveFooterBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        axios.post("{{ route('admin.cms.sponsorship.update.footer') }}", formData)
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
