<div class="accordion custom-accordionwithicon custom-accordion-border accordion-border-box" id="eventAccordion">
    
    {{-- Hero Section --}}
    @php $hero = $cmsData->get('events_page_hero'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingHero">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHero" aria-expanded="true" aria-controls="collapseHero">
                <i class="ri-image-line me-2"></i> Hero Section
            </button>
        </h2>
        <div id="collapseHero" class="accordion-collapse collapse show" aria-labelledby="headingHero" data-bs-parent="#eventAccordion">
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
    @php $video = $cmsData->get('events_page_video'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingVideo">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVideo" aria-expanded="false" aria-controls="collapseVideo">
                <i class="ri-video-line me-2"></i> Video Section
            </button>
        </h2>
        <div id="collapseVideo" class="accordion-collapse collapse" aria-labelledby="headingVideo" data-bs-parent="#eventAccordion">
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

    {{-- Host Section --}}
    @php $host = $cmsData->get('events_page_host'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingHost">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHost" aria-expanded="false" aria-controls="collapseHost">
                <i class="ri-home-heart-line me-2"></i> Host Your Event Section
            </button>
        </h2>
        <div id="collapseHost" class="accordion-collapse collapse" aria-labelledby="headingHost" data-bs-parent="#eventAccordion">
            <div class="accordion-body">
                <form id="hostForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Main Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $host?->title }}" placeholder="e.g. Host Your Event With OSI">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Main Description</label>
                            <textarea name="sub_title" class="form-control" rows="3" placeholder="Enter main description">{{ $host?->sub_title }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Side Image</label>
                            <input type="file" name="image_file" class="form-control" accept="image/*">
                            @if($host?->image)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $host->image) }}" alt="Host Image" class="rounded border" style="max-height: 150px;">
                                </div>
                            @endif
                        </div>

                        <hr>
                        <h6>Feature Items (4 items)</h6>
                        
                        @for($i = 0; $i < 4; $i++)
                            @php $item = $host?->metadata[$i] ?? null; @endphp
                            <div class="col-md-6 border p-3 rounded mb-2">
                                <h7 class="fw-bold mb-2 d-block">Feature {{ $i + 1 }}</h7>
                                <div class="mb-2">
                                    <label class="form-label">Icon (Remix Icon Class)</label>
                                    <input type="text" name="items[{{ $i }}][icon]" class="form-control" value="{{ $item['icon'] ?? '' }}" placeholder="e.g. ri-notification-3-line">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="items[{{ $i }}][title]" class="form-control" value="{{ $item['title'] ?? '' }}" placeholder="Enter title">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Description</label>
                                    <textarea name="items[{{ $i }}][description]" class="form-control" rows="2" placeholder="Enter description">{{ $item['description'] ?? '' }}</textarea>
                                </div>
                            </div>
                        @endfor

                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveHostBtn">
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
        axios.post("{{ route('admin.cms.event.update.hero') }}", formData)
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
        axios.post("{{ route('admin.cms.event.update.video') }}", formData)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // Host Logic
    $('#hostForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveHostBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        axios.post("{{ route('admin.cms.event.update.host') }}", formData)
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
