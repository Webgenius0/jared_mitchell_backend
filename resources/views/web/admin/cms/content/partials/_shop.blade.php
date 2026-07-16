<div class="accordion custom-accordionwithicon custom-accordion-border accordion-border-box" id="shopAccordion">

    {{-- Hero Section --}}
    @php $hero = $cmsData->get('shop_page_hero'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingHero">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHero"
                aria-expanded="true" aria-controls="collapseHero">
                <i class="ri-image-line me-2"></i> Hero Section
            </button>
        </h2>
        <div id="collapseHero" class="accordion-collapse collapse show" aria-labelledby="headingHero"
            data-bs-parent="#shopAccordion">
            <div class="accordion-body">
                <form id="heroForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Hero Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $hero?->title }}"
                                placeholder="e.g. The OSI Shop">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Hero Sub-title</label>
                            <textarea name="sub_title" class="form-control" rows="2"
                                placeholder="e.g. Support the culture. Fund community programs...">{{ $hero?->sub_title }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Hero Description</label>
                            <textarea name="description" class="form-control" rows="3"
                                placeholder="Enter hero description">{{ $hero?->description }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Background Image</label>
                            <input type="file" name="bg_image" class="form-control" accept="image/*">
                            @if($hero?->bg)
                                <div class="mt-2">
                                    <img src="{{ asset($hero->bg) }}" alt="Hero Background" class="rounded border"
                                        style="max-height: 150px;">
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

    {{-- Features Section --}}
    @php $features = $cmsData->get('shop_page_features'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingFeatures">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseFeatures" aria-expanded="false" aria-controls="collapseFeatures">
                <i class="ri-grid-line me-2"></i> Shop Categories/Features (6 items)
            </button>
        </h2>
        <div id="collapseFeatures" class="accordion-collapse collapse" aria-labelledby="headingFeatures"
            data-bs-parent="#shopAccordion">
            <div class="accordion-body">
                <form id="featuresForm">
                    <div class="row g-3">
                        @for($i = 0; $i < 6; $i++)
                            @php $item = $features?->metadata[$i] ?? null; @endphp
                            <div class="col-md-4 border p-3 rounded mb-2">
                                <h7 class="fw-bold mb-2 d-block">Item {{ $i + 1 }}</h7>
                                <div class="mb-2">
                                    <label class="form-label fs-12">Icon (Remix Icon Class)</label>
                                    <input type="text" name="items[{{ $i }}][icon]" class="form-control form-control-sm"
                                        value="{{ $item['icon'] ?? '' }}" placeholder="e.g. ri-t-shirt-line">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fs-12">Title</label>
                                    <input type="text" name="items[{{ $i }}][title]" class="form-control form-control-sm"
                                        value="{{ $item['title'] ?? '' }}" placeholder="Enter title">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fs-12">Description</label>
                                    <textarea name="items[{{ $i }}][description]" class="form-control form-control-sm"
                                        rows="2" placeholder="Enter description">{{ $item['description'] ?? '' }}</textarea>
                                </div>
                            </div>
                        @endfor

                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveFeaturesBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Featured from the OSI Shop Section --}}
    @php $featured = $cmsData->get('shop_page_featured'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingFeatured">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseFeatured" aria-expanded="false" aria-controls="collapseFeatured">
                <i class="ri-star-line me-2"></i> Featured from the OSI Shop
            </button>
        </h2>
        <div id="collapseFeatured" class="accordion-collapse collapse" aria-labelledby="headingFeatured"
            data-bs-parent="#shopAccordion">
            <div class="accordion-body">
                <form id="featuredForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $featured?->title }}"
                                placeholder="e.g. Featured from the OSI Shop">
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveFeaturedBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Support Section --}}
    @php $support = $cmsData->get('shop_page_support'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingSupport">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseSupport" aria-expanded="false" aria-controls="collapseSupport">
                <i class="ri-heart-line me-2"></i> What Your Purchase Supports (3items)
            </button>
        </h2>
        <div id="collapseSupport" class="accordion-collapse collapse" aria-labelledby="headingSupport"
            data-bs-parent="#shopAccordion">
            <div class="accordion-body">
                <form id="supportForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $support?->title }}"
                                placeholder="e.g. What Your Purchase Supports">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Section Sub-title</label>
                            <textarea name="sub_title" class="form-control" rows="2"
                                placeholder="e.g. Your purchase directly funds...">{{ $support?->sub_title }}</textarea>
                        </div>

                        <hr>
                        <h6>Items (4 items)</h6>
                        @for($i = 0; $i < 3; $i++)
                            @php $item = $support?->metadata[$i] ?? null; @endphp
                            <div class="col-md-6 border p-3 rounded mb-2">
                                <h7 class="fw-bold mb-2 d-block">Item {{ $i + 1 }}</h7>
                                <div class="mb-2">
                                    <label class="form-label fs-12">Icon (Remix Icon Class)</label>
                                    <input type="text" name="items[{{ $i }}][icon]" class="form-control form-control-sm"
                                        value="{{ $item['icon'] ?? '' }}" placeholder="e.g. ri-mic-line">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fs-12">Title</label>
                                    <input type="text" name="items[{{ $i }}][title]" class="form-control form-control-sm"
                                        value="{{ $item['title'] ?? '' }}" placeholder="Enter title">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fs-12">Description</label>
                                    <textarea name="items[{{ $i }}][description]" class="form-control form-control-sm"
                                        rows="2" placeholder="Enter description">{{ $item['description'] ?? '' }}</textarea>
                                </div>
                            </div>
                        @endfor

                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveSupportBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Footer Features Section --}}
    <!-- @php $footer = $cmsData->get('shop_page_footer_features'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingFooter">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFooter" aria-expanded="false" aria-controls="collapseFooter">
                <i class="ri-shield-check-line me-2"></i> Shop Footer Features (4 items)
            </button>
        </h2>
        <div id="collapseFooter" class="accordion-collapse collapse" aria-labelledby="headingFooter" data-bs-parent="#shopAccordion">
            <div class="accordion-body">
                <form id="footerFeaturesForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Bottom Text</label>
                            <input type="text" name="bottom_text" class="form-control" value="{{ $footer?->sub_title }}" placeholder="e.g. Powered by OSI. Built for the culture.">
                        </div>

                        <hr>
                        <h6>Items (4 items)</h6>
                        @for($i = 0; $i < 4; $i++)
                            @php $item = $footer?->metadata[$i] ?? null; @endphp
                            <div class="col-md-3 border p-3 rounded mb-2">
                                <h7 class="fw-bold mb-2 d-block">Item {{ $i + 1 }}</h7>
                                <div class="mb-2">
                                    <label class="form-label fs-12">Title</label>
                                    <input type="text" name="items[{{ $i }}][title]" class="form-control form-control-sm" value="{{ $item['title'] ?? '' }}" placeholder="Enter title">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fs-12">Description</label>
                                    <textarea name="items[{{ $i }}][description]" class="form-control form-control-sm" rows="2" placeholder="Enter description">{{ $item['description'] ?? '' }}</textarea>
                                </div>
                            </div>
                        @endfor

                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveFooterBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div> -->



    {{-- Limited Drops Section --}}
    @php $limitedDrops = $cmsData->get('shop_page_limited_drops'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingLimitedDrops">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseLimitedDrops" aria-expanded="false" aria-controls="collapseLimitedDrops">
                <i class="ri-flashlight-line me-2"></i> Limited Drops
            </button>
        </h2>
        <div id="collapseLimitedDrops" class="accordion-collapse collapse" aria-labelledby="headingLimitedDrops"
            data-bs-parent="#shopAccordion">
            <div class="accordion-body">
                <form id="limitedDropsForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $limitedDrops?->title }}"
                                placeholder="e.g. Limited Drops">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Section Subtitle</label>
                            <textarea name="sub_title" class="form-control" rows="3"
                                placeholder="Enter section subtitle">{{ $limitedDrops?->sub_title }}</textarea>
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveLimitedDropsBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- FAQ Section --}}
    @php $faq = $cmsData->get('shop_page_faq'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingFaq">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseFaq" aria-expanded="false" aria-controls="collapseFaq">
                <i class="ri-question-answer-line me-2"></i> FAQ Section
            </button>
        </h2>
        <div id="collapseFaq" class="accordion-collapse collapse" aria-labelledby="headingFaq"
            data-bs-parent="#shopAccordion">
            <div class="accordion-body">
                <form id="faqForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $faq?->title }}"
                                placeholder="e.g. Frequently Asked Questions">
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

</div>

@push('scripts')
    <script>
        $(function () {
            // Hero Logic
            $('#heroForm').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#saveHeroBtn');
                const originalText = $btn.html();
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
                const formData = new FormData(this);
                axios.post("{{ route('admin.cms.shop.update.hero') }}", formData)
                    .then(res => {
                        Toast.success(res.data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    })
                    .catch(err => {
                        Toast.fromResponse(err.response?.data);
                        $btn.prop('disabled', false).html(originalText);
                    });
            });

            // Features Logic
            $('#featuresForm').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#saveFeaturesBtn');
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
                            current[k] = current[k] || (isNaN(keys[i + 1]) ? {} : []);
                            current = current[k];
                        }
                    }
                });

                axios.post("{{ route('admin.cms.shop.update.features') }}", data)
                    .then(res => {
                        Toast.success(res.data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    })
                    .catch(err => {
                        Toast.fromResponse(err.response?.data);
                        $btn.prop('disabled', false).html(originalText);
                    });
            });

            // Support Logic
            $('#supportForm').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#saveSupportBtn');
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
                            current[k] = current[k] || (isNaN(keys[i + 1]) ? {} : []);
                            current = current[k];
                        }
                    }
                });

                axios.post("{{ route('admin.cms.shop.update.support') }}", data)
                    .then(res => {
                        Toast.success(res.data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    })
                    .catch(err => {
                        Toast.fromResponse(err.response?.data);
                        $btn.prop('disabled', false).html(originalText);
                    });
            });

            // Footer Features Logic
            $('#footerFeaturesForm').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#saveFooterBtn');
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
                            current[k] = current[k] || (isNaN(keys[i + 1]) ? {} : []);
                            current = current[k];
                        }
                    }
                });

                axios.post("{{ route('admin.cms.shop.update.footer_features') }}", data)
                    .then(res => {
                        Toast.success(res.data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    })
                    .catch(err => {
                        Toast.fromResponse(err.response?.data);
                        $btn.prop('disabled', false).html(originalText);
                    });
            });

            // Featured Logic
            $('#featuredForm').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#saveFeaturedBtn');
                const originalText = $btn.html();
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
                const formData = $(this).serialize();
                axios.post("{{ route('admin.cms.shop.update.featured') }}", formData)
                    .then(res => {
                        Toast.success(res.data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    })
                    .catch(err => {
                        Toast.fromResponse(err.response?.data);
                        $btn.prop('disabled', false).html(originalText);
                    });
            });

            // Limited Drops Logic
            $('#limitedDropsForm').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#saveLimitedDropsBtn');
                const originalText = $btn.html();
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
                const formData = $(this).serialize();
                axios.post("{{ route('admin.cms.shop.update.limited_drops') }}", formData)
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
            $('#faqForm').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#saveFaqBtn');
                const originalText = $btn.html();
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
                const formData = $(this).serialize();
                axios.post("{{ route('admin.cms.shop.update.faq') }}", formData)
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