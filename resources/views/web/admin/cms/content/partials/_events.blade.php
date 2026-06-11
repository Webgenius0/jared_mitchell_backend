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
                            <label class="form-label">Video URL (YouTube/Vimeo)</label>
                            <input type="url" name="video_url" class="form-control" value="{{ (isset($video) && !Str::startsWith($video->video, 'uploads/')) ? $video->video : '' }}" placeholder="https://www.youtube.com/watch?v=...">
                        </div>
                        <div class="col-md-12 text-center">
                            <span class="text-muted fw-bold">OR</span>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Upload Video</label>
                            <input type="file" name="video_file" class="form-control" accept="video/*">
                            @if(isset($video) && $video->video && Str::startsWith($video->video, 'uploads/'))
                                <div class="mt-2">
                                    <video controls style="max-height: 150px;" class="rounded border">
                                        <source src="{{ asset('storage/' . $video->video) }}">
                                        Your browser does not support the video tag.
                                    </video>
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

    {{-- Vendor Section --}}
    @php 
        $vendor = $cmsData->get('events_page_vendor'); 
        $meta = $vendor?->metadata ?? [];
    @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingVendor">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVendor" aria-expanded="false" aria-controls="collapseVendor">
                <i class="ri-store-3-line me-2"></i> Vendor With OSI Section
            </button>
        </h2>
        <div id="collapseVendor" class="accordion-collapse collapse" aria-labelledby="headingVendor" data-bs-parent="#eventAccordion">
            <div class="accordion-body">
                <form id="vendorForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Main Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $vendor?->title }}" placeholder="e.g. Vendor With OSI">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Main Description</label>
                            <textarea name="sub_title" class="form-control" rows="2" placeholder="Enter main description">{{ $vendor?->sub_title }}</textarea>
                        </div>

                        <hr>
                        <h6>1. Pricing Cards (3 items)</h6>
                        @for($i = 0; $i < 3; $i++)
                            @php $p = $meta['pricing'][$i] ?? null; @endphp
                            <div class="col-md-4 border p-3 rounded mb-2">
                                <h7 class="fw-bold mb-2 d-block">Pricing Card {{ $i + 1 }}</h7>
                                <input type="text" name="pricing[{{ $i }}][icon]" class="form-control mb-2" value="{{ $p['icon'] ?? '' }}" placeholder="Icon (e.g. ri-store-2-line)">
                                <input type="text" name="pricing[{{ $i }}][title]" class="form-control mb-2" value="{{ $p['title'] ?? '' }}" placeholder="Title">
                                <input type="text" name="pricing[{{ $i }}][price]" class="form-control mb-2" value="{{ $p['price'] ?? '' }}" placeholder="Price/Info">
                                <textarea name="pricing[{{ $i }}][description]" class="form-control" rows="2" placeholder="Description">{{ $p['description'] ?? '' }}</textarea>
                            </div>
                        @endfor

                        <hr>
                        <div class="col-md-6">
                            <h6>2. Benefits Included</h6>
                            <input type="text" name="benefits[title]" class="form-control mb-2" value="{{ $meta['benefits']['title'] ?? 'Benefits Included With Every Booth' }}" placeholder="Section Title">
                            <label class="form-label fs-12 text-muted">Items (One per line)</label>
                            <textarea name="benefits[items]" class="form-control" rows="5" placeholder="Item 1\nItem 2...">{{ isset($meta['benefits']['items']) ? (is_array($meta['benefits']['items']) ? implode("\n", $meta['benefits']['items']) : $meta['benefits']['items']) : '' }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <h6>3. Member Perks (Top)</h6>
                            <input type="text" name="member_perks_top[title]" class="form-control mb-2" value="{{ $meta['member_perks_top']['title'] ?? 'Member Perks' }}" placeholder="Section Title">
                            <textarea name="member_perks_top[condition]" class="form-control mb-2" rows="2" placeholder="Condition text (e.g. If you have an OSI membership...)">{{ $meta['member_perks_top']['condition'] ?? '' }}</textarea>
                            <label class="form-label fs-12 text-muted">Items (One per line)</label>
                            <textarea name="member_perks_top[items]" class="form-control" rows="4" placeholder="Item 1\nItem 2...">{{ isset($meta['member_perks_top']['items']) ? (is_array($meta['member_perks_top']['items']) ? implode("\n", $meta['member_perks_top']['items']) : $meta['member_perks_top']['items']) : '' }}</textarea>
                        </div>

                        <div class="col-md-6 mt-3">
                            <h6>4. Member Perks (Bottom Left)</h6>
                            <input type="text" name="member_perks_bottom[title]" class="form-control mb-2" value="{{ $meta['member_perks_bottom']['title'] ?? 'Member Perks' }}" placeholder="Section Title">
                            <textarea name="member_perks_bottom[description]" class="form-control mb-2" rows="2" placeholder="Description text (e.g. Every registered vendor receives:)">{{ $meta['member_perks_bottom']['description'] ?? '' }}</textarea>
                            <label class="form-label fs-12 text-muted">Items (One per line)</label>
                            <textarea name="member_perks_bottom[items]" class="form-control" rows="5" placeholder="Item 1\nItem 2...">{{ isset($meta['member_perks_bottom']['items']) ? (is_array($meta['member_perks_bottom']['items']) ? implode("\n", $meta['member_perks_bottom']['items']) : $meta['member_perks_bottom']['items']) : '' }}</textarea>
                        </div>

                        <div class="col-md-6 mt-3">
                            <h6>5. What Vendors Provide</h6>
                            <input type="text" name="what_vendors_provide[title]" class="form-control mb-2" value="{{ $meta['what_vendors_provide']['title'] ?? 'What Vendors Provide' }}" placeholder="Section Title">
                            <label class="form-label fs-12 text-muted">Items (One per line)</label>
                            <textarea name="what_vendors_provide[items]" class="form-control" rows="5" placeholder="Item 1\nItem 2...">{{ isset($meta['what_vendors_provide']['items']) ? (is_array($meta['what_vendors_provide']['items']) ? implode("\n", $meta['what_vendors_provide']['items']) : $meta['what_vendors_provide']['items']) : '' }}</textarea>
                        </div>

                        <div class="col-md-12 mt-3">
                            <h6>6. Why Vendors Love Working With OSI</h6>
                            <input type="text" name="why_vendors_love[title]" class="form-control mb-2" value="{{ $meta['why_vendors_love']['title'] ?? 'Why Vendors Love Working With OSI' }}" placeholder="Section Title">
                            <textarea name="why_vendors_love[description]" class="form-control mb-2" rows="2" placeholder="Intro text (e.g. OSI events are community-powered...)">{{ $meta['why_vendors_love']['description'] ?? '' }}</textarea>
                            <label class="form-label fs-12 text-muted">Items (One per line)</label>
                            <textarea name="why_vendors_love[items]" class="form-control" rows="5" placeholder="Item 1\nItem 2...">{{ isset($meta['why_vendors_love']['items']) ? (is_array($meta['why_vendors_love']['items']) ? implode("\n", $meta['why_vendors_love']['items']) : $meta['why_vendors_love']['items']) : '' }}</textarea>
                        </div>

                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveVendorBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Booth Features Section --}}
    @php $booth = $cmsData->get('events_page_booth_features'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingBooth">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBooth" aria-expanded="false" aria-controls="collapseBooth">
                <i class="ri-checkbox-circle-line me-2"></i> Booth Features Section
            </button>
        </h2>
        <div id="collapseBooth" class="accordion-collapse collapse" aria-labelledby="headingBooth" data-bs-parent="#eventAccordion">
            <div class="accordion-body">
                <form id="boothForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $booth?->title }}" placeholder="e.g. What You Get with Every Booth">
                        </div>
                        
                        <hr>
                        <h6>Feature Items (3 items)</h6>
                        @for($i = 0; $i < 3; $i++)
                            @php $item = $booth?->metadata[$i] ?? null; @endphp
                            <div class="col-md-4 border p-3 rounded mb-2">
                                <h7 class="fw-bold mb-2 d-block">Feature {{ $i + 1 }}</h7>
                                <div class="mb-2">
                                    <label class="form-label">Icon (Remix Icon Class)</label>
                                    <input type="text" name="items[{{ $i }}][icon]" class="form-control" value="{{ $item['icon'] ?? '' }}" placeholder="e.g. ri-camera-line">
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
                            <button type="submit" class="btn btn-primary px-4" id="saveBoothBtn">
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

    // Vendor Logic
    $('#vendorForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveVendorBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        
        // Prepare data: convert newline textareas to arrays
        const formData = new FormData(this);
        const data = {};
        formData.forEach((value, key) => {
            // Handle arrays (items)
            if (key.includes('[items]')) {
                data[key] = value.split('\n').filter(item => item.trim() !== '');
            } else {
                // Parse nested keys like pricing[0][title]
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

        axios.post("{{ route('admin.cms.event.update.vendor') }}", data)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // Booth Features Logic
    $('#boothForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveBoothBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        const data = {};
        formData.forEach((value, key) => {
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
        });

        axios.post("{{ route('admin.cms.event.update.booth_features') }}", data)
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
