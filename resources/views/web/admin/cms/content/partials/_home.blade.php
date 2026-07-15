<div class="accordion" id="cmsAccordion">
    {{-- Hero Section --}}
    @php $hero = $cmsData->get('hero'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingHero">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHero"
                aria-expanded="true" aria-controls="collapseHero">
                <i class="ri-slideshow-line me-2"></i> Hero Section
            </button>
        </h2>
        <div id="collapseHero" class="accordion-collapse collapse show" aria-labelledby="headingHero"
            data-bs-parent="#cmsAccordion">
            <div class="accordion-body">
                <form id="heroForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $hero?->title }}"
                                placeholder="Enter hero title">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Subtitle</label>
                            <input type="text" name="sub_title" class="form-control" value="{{ $hero?->sub_title }}"
                                placeholder="Enter hero subtitle">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"
                                placeholder="Enter hero description">{{ $hero?->description }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Hero Video</label>
                            <input type="file" name="video_file" class="form-control" accept="video/*">
                            @if($hero?->video)
                                <div class="mt-2 d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success">Current Video:
                                        {{ basename($hero->video) }}</span>
                                    <a href="{{ asset($hero->video) }}" target="_blank" class="btn btn-sm btn-link p-0">View
                                        Video</a>
                                </div>
                            @endif
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveHeroBtn">
                                <i class="ri-save-line me-1"></i> Save Hero Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Partners Section --}}
    @php
        $partners = $cmsData->get('partners');
        $partnerItems = $partners?->metadata ?? [];
    @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingPartners">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapsePartners" aria-expanded="false" aria-controls="collapsePartners">
                <i class="ri-team-line me-2"></i> Partners Section
            </button>
        </h2>
        <div id="collapsePartners" class="accordion-collapse collapse" aria-labelledby="headingPartners"
            data-bs-parent="#cmsAccordion">
            <div class="accordion-body">
                <form id="partnersForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $partners?->title }}"
                                placeholder="e.g. Powered by our community partners">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Section Sub Title</label>
                            <input type="text" name="sub_title" class="form-control" value="{{ $partners?->sub_title }}"
                                placeholder="e.g. Powered by our community partners">
                        </div>

                        <div class="col-md-12">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label mb-0">Partner Logos & Links</label>
                                <button type="button" class="btn btn-sm btn-soft-primary" id="addPartnerBtn">
                                    <i class="ri-add-line me-1"></i> Add Partner
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered align-middle" id="partnersTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Logo</th>
                                            <th>Website Link</th>
                                            <th style="width: 50px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($partnerItems as $index => $item)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-3">
                                                        @if($item['image'])
                                                            <img src="{{ asset($item['image']) }}" alt="Partner"
                                                                class="rounded border" style="height: 40px; width: auto;">
                                                        @endif
                                                        <input type="file" name="partners[{{ $index }}][image_file]"
                                                            class="form-control form-control-sm" accept="image/*">
                                                        <input type="hidden" name="partners[{{ $index }}][existing_image]"
                                                            value="{{ $item['image'] }}">
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="url" name="partners[{{ $index }}][link]"
                                                        class="form-control form-control-sm" value="{{ $item['link'] }}"
                                                        placeholder="https://...">
                                                </td>
                                                <td class="text-center">
                                                    <button type="button"
                                                        class="btn btn-sm btn-soft-danger remove-partner-btn">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr class="empty-row">
                                                <td colspan="3" class="text-center text-muted">No partners added yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="savePartnersBtn">
                                <i class="ri-save-line me-1"></i> Save Partners Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Features Section --}}
    @php $features = $cmsData->get('features'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingFeatures">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseFeatures" aria-expanded="false" aria-controls="collapseFeatures">
                <i class="ri-rocket-line me-2"></i> Features Section
            </button>
        </h2>
        <div id="collapseFeatures" class="accordion-collapse collapse" aria-labelledby="headingFeatures"
            data-bs-parent="#cmsAccordion">
            <div class="accordion-body">
                <form id="featuresForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $features?->title }}"
                                placeholder="e.g. Everything You Need to Grow Your Business">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4"
                                placeholder="Enter section description">{{ $features?->description }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Background Image</label>
                            <input type="file" name="bg_file" class="form-control" accept="image/*">
                            @if($features?->bg)
                                <div class="mt-2">
                                    <img src="{{ asset($features->bg) }}" alt="Background" class="rounded border"
                                        style="max-height: 150px; width: auto;">
                                </div>
                            @endif
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveFeaturesBtn">
                                <i class="ri-save-line me-1"></i> Save Features Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Why Choose Section --}}
    @php
        $whyChoose = $cmsData->get('why_choose');
        $whyChooseItems = $whyChoose?->metadata ?? [];
    @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingWhyChoose">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseWhyChoose" aria-expanded="false" aria-controls="collapseWhyChoose">
                <i class="ri-question-line me-2"></i> Why Choose Section
            </button>
        </h2>
        <div id="collapseWhyChoose" class="accordion-collapse collapse" aria-labelledby="headingWhyChoose"
            data-bs-parent="#cmsAccordion">
            <div class="accordion-body">
                <form id="whyChooseForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Main Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $whyChoose?->title }}"
                                placeholder="e.g. WHY CHOOSE OSI">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Main Subtitle</label>
                            <input type="text" name="sub_title" class="form-control"
                                value="{{ $whyChoose?->sub_title }}"
                                placeholder="e.g. Fostering a culture of support...">
                        </div>

                        <div class="col-md-12 mt-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label mb-0">Choice Cards</label>
                                <button type="button" class="btn btn-sm btn-soft-primary" id="addWhyChooseBtn">
                                    <i class="ri-add-line me-1"></i> Add Card
                                </button>
                            </div>

                            <div id="whyChooseContainer">
                                @forelse($whyChooseItems as $index => $item)
                                    <div class="card border border-dashed mb-3 why-choose-item">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="card-title mb-0">Card #{{ $index + 1 }}</h6>
                                                <button type="button"
                                                    class="btn btn-sm btn-soft-danger remove-why-choose-btn">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Background Image</label>
                                                    <input type="file" name="items[{{ $index }}][image_file]"
                                                        class="form-control form-control-sm" accept="image/*">
                                                    <input type="hidden" name="items[{{ $index }}][existing_image]"
                                                        value="{{ $item['image'] }}">
                                                    @if($item['image'])
                                                        <div class="mt-2 text-center">
                                                            <img src="{{ asset($item['image']) }}" alt="Card Image"
                                                                class="rounded" style="height: 60px; width: auto;">
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="mb-3">
                                                        <label class="form-label">Title</label>
                                                        <input type="text" name="items[{{ $index }}][title]"
                                                            class="form-control form-control-sm"
                                                            value="{{ $item['title'] }}" placeholder="e.g. Creators">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Subtitle</label>
                                                        <input type="text" name="items[{{ $index }}][sub_title]"
                                                            class="form-control form-control-sm"
                                                            value="{{ $item['sub_title'] }}"
                                                            placeholder="e.g. Build exposure...">
                                                    </div>
                                                    <div>
                                                        <label class="form-label">Description</label>
                                                        <textarea name="items[{{ $index }}][description]"
                                                            class="form-control form-control-sm" rows="2"
                                                            placeholder="Enter card description">{{ $item['description'] }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-3 empty-msg">No cards added yet.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveWhyChooseBtn">
                                <i class="ri-save-line me-1"></i> Save Why Choose Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Core Values Section --}}
    @php
        $coreValues = $cmsData->get('core_values');
        $coreValueItems = $coreValues?->metadata ?? [];
    @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingCoreValues">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseCoreValues" aria-expanded="false" aria-controls="collapseCoreValues">
                <i class="ri-heart-line me-2"></i> Core Values Section
            </button>
        </h2>
        <div id="collapseCoreValues" class="accordion-collapse collapse" aria-labelledby="headingCoreValues"
            data-bs-parent="#cmsAccordion">
            <div class="accordion-body">
                <form id="coreValuesForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $coreValues?->title }}"
                                placeholder="e.g. Our Core Values">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Section Background Image</label>
                            <input type="file" name="bg_file" class="form-control" accept="image/*">
                            @if($coreValues?->bg)
                                <div class="mt-2 text-center">
                                    <img src="{{ asset($coreValues->bg) }}" alt="Core Values BG" class="rounded"
                                        style="height: 100px; width: auto;">
                                </div>
                            @endif
                        </div>

                        <div class="col-md-12 mt-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label mb-0">Value Cards</label>
                                <button type="button" class="btn btn-sm btn-soft-primary" id="addCoreValueBtn">
                                    <i class="ri-add-line me-1"></i> Add Value
                                </button>
                            </div>

                            <div id="coreValuesContainer">
                                @forelse($coreValueItems as $index => $item)
                                    <div class="card border border-dashed mb-3 core-value-item">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="card-title mb-0">Value #{{ $index + 1 }}</h6>
                                                <button type="button"
                                                    class="btn btn-sm btn-soft-danger remove-core-value-btn">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Icon (Remix Icon Class)</label>
                                                    <input type="text" name="items[{{ $index }}][icon]"
                                                        class="form-control form-control-sm" value="{{ $item['icon'] }}"
                                                        placeholder="e.g. ri-star-line">
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="mb-3">
                                                        <label class="form-label">Title</label>
                                                        <input type="text" name="items[{{ $index }}][title]"
                                                            class="form-control form-control-sm"
                                                            value="{{ $item['title'] }}"
                                                            placeholder="e.g. Intentional Visibility">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Subtitle</label>
                                                        <input type="text" name="items[{{ $index }}][sub_title]"
                                                            class="form-control form-control-sm"
                                                            value="{{ $item['sub_title'] }}"
                                                            placeholder="e.g. Visibility should be thoughtful...">
                                                    </div>
                                                    <div>
                                                        <label class="form-label">Description</label>
                                                        <textarea name="items[{{ $index }}][description]"
                                                            class="form-control form-control-sm" rows="2"
                                                            placeholder="Enter value description">{{ $item['description'] }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-3 core-value-empty">No values added yet.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveCoreValuesBtn">
                                <i class="ri-save-line me-1"></i> Save Core Values Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- What You Get Section --}}
    @php
        $whatYouGet = $cmsData->get('what_you_get');
        $whatYouGetItems = $whatYouGet?->metadata ?? [];
    @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingWhatYouGet">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseWhatYouGet" aria-expanded="false" aria-controls="collapseWhatYouGet">
                <i class="ri-checkbox-circle-line me-2"></i> What You're Really Getting
            </button>
        </h2>
        <div id="collapseWhatYouGet" class="accordion-collapse collapse" aria-labelledby="headingWhatYouGet"
            data-bs-parent="#cmsAccordion">
            <div class="accordion-body">
                <form id="whatYouGetForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $whatYouGet?->title }}"
                                placeholder="e.g. What You're Really Getting">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Section Subtitle</label>
                            <input type="text" name="sub_title" class="form-control"
                                value="{{ $whatYouGet?->sub_title }}"
                                placeholder="e.g. You're not buying a membership...">
                        </div>

                        <div class="col-md-12 mt-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label mb-0">Feature Cards</label>
                                <button type="button" class="btn btn-sm btn-soft-primary" id="addWhatYouGetBtn">
                                    <i class="ri-add-line me-1"></i> Add Card
                                </button>
                            </div>

                            <div id="whatYouGetContainer">
                                @forelse($whatYouGetItems as $index => $item)
                                    <div class="card border border-dashed mb-2 what-you-get-item">
                                        <div class="card-body py-2">
                                            <div class="row g-2 align-items-center">
                                                <div class="col-md-4">
                                                    <input type="text" name="items[{{ $index }}][icon]"
                                                        class="form-control form-control-sm" value="{{ $item['icon'] }}"
                                                        placeholder="Icon (e.g. ri-star-line)">
                                                </div>
                                                <div class="col-md-7">
                                                    <input type="text" name="items[{{ $index }}][title]"
                                                        class="form-control form-control-sm" value="{{ $item['title'] }}"
                                                        placeholder="Title (e.g. Business visibility)">
                                                </div>
                                                <div class="col-md-1 text-end">
                                                    <button type="button"
                                                        class="btn btn-sm btn-soft-danger remove-what-you-get-btn">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-3 what-you-get-empty">No cards added yet.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveWhatYouGetBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Boss Beginnings Section --}}
    @php $bossBeginnings = $cmsData->get('boss_beginnings'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingBossBeginnings">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseBossBeginnings" aria-expanded="false" aria-controls="collapseBossBeginnings">
                <i class="ri-lightbulb-line me-2"></i> Boss Beginnings Section
            </button>
        </h2>
        <div id="collapseBossBeginnings" class="accordion-collapse collapse" aria-labelledby="headingBossBeginnings"
            data-bs-parent="#cmsAccordion">
            <div class="accordion-body">
                <form id="bossBeginningsForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $bossBeginnings?->title }}"
                                placeholder="e.g. Boss Beginnings">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subtitle</label>
                            <input type="text" name="sub_title" class="form-control"
                                value="{{ $bossBeginnings?->sub_title }}" placeholder="e.g. A Business Shower">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4"
                                placeholder="Enter section description">{{ $bossBeginnings?->description }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Main Image</label>
                            <input type="file" name="image_file" class="form-control" accept="image/*">
                            @if($bossBeginnings?->image)
                                <div class="mt-2">
                                    <img src="{{ asset($bossBeginnings->image) }}" alt="Boss Beginnings"
                                        class="rounded border" style="max-height: 200px; width: auto;">
                                </div>
                            @endif
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveBossBeginningsBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Spotlight Section --}}
    @php $spotlight = $cmsData->get('spotlight'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingSpotlight">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseSpotlight" aria-expanded="false" aria-controls="collapseSpotlight">
                <i class="ri-star-line me-2"></i> Celebrating Business Spotlight winers
            </button>
        </h2>
        <div id="collapseSpotlight" class="accordion-collapse collapse" aria-labelledby="headingSpotlight"
            data-bs-parent="#cmsAccordion">
            <div class="accordion-body">
                <form id="spotlightForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $spotlight?->title }}"
                                placeholder="e.g. Celebrating Local Success Stories">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Section Subtitle</label>
                            <textarea name="sub_title" class="form-control" rows="3"
                                placeholder="Enter section subtitle">{{ $spotlight?->sub_title }}</textarea>
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveSpotlightBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Boss Beginning Winners Section --}}
    @php $bossBeginningWinners = $cmsData->get('boss_beginning_winners'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingBossBeginningWinners">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseBossBeginningWinners" aria-expanded="false"
                aria-controls="collapseBossBeginningWinners">
                <i class="ri-star-line me-2"></i> Boss Beginning Winners Showing
            </button>
        </h2>
        <div id="collapseBossBeginningWinners" class="accordion-collapse collapse"
            aria-labelledby="headingBossBeginningWinners" data-bs-parent="#cmsAccordion">
            <div class="accordion-body">
                <form id="bossBeginningWinnersForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control"
                                value="{{ $bossBeginningWinners?->title }}" placeholder="e.g. Boss Beginning Winners">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Section Subtitle</label>
                            <textarea name="sub_title" class="form-control" rows="3"
                                placeholder="Enter section subtitle">{{ $bossBeginningWinners?->sub_title }}</textarea>
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveBossBeginningWinnersBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Events Section --}}
    @php $events = $cmsData->get('events'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingEvents">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseEvents" aria-expanded="false" aria-controls="collapseEvents">
                <i class="ri-calendar-event-line me-2"></i> Events Section
            </button>
        </h2>
        <div id="collapseEvents" class="accordion-collapse collapse" aria-labelledby="headingEvents"
            data-bs-parent="#cmsAccordion">
            <div class="accordion-body">
                <form id="eventsForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $events?->title }}"
                                placeholder="e.g. Events">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Section Description</label>
                            <textarea name="description" class="form-control" rows="3"
                                placeholder="Enter section description">{{ $events?->description }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Background Image</label>
                            <input type="file" name="bg_file" class="form-control" accept="image/*">
                            @if($events?->bg)
                                <div class="mt-2">
                                    <img src="{{ asset($events->bg) }}" alt="Events Background" class="rounded border"
                                        style="max-height: 200px; width: auto;">
                                </div>
                            @endif
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveEventsBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Next Boss Beginnings – Westside Beauty Lounge --}}
    @php $nextBossBeginnings = $cmsData->get('next_boss_beginnings_westside_beauty_lounge'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingNextBossBeginnings">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseNextBossBeginnings" aria-expanded="false"
                aria-controls="collapseNextBossBeginnings">
                <i class="ri-star-line me-2"></i> Next Boss Beginnings Westside Beauty Lounge
            </button>
        </h2>
        <div id="collapseNextBossBeginnings" class="accordion-collapse collapse"
            aria-labelledby="headingNextBossBeginnings" data-bs-parent="#cmsAccordion">
            <div class="accordion-body">
                <form id="nextBossBeginningsForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control"
                                value="{{ $nextBossBeginnings?->title }}" placeholder="e.g. Next Boss Beginnings">
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveNextBossBeginningsBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Upcoming Events --}}
    @php $upcomingEvents = $cmsData->get('upcoming_events'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingUpcomingEvents">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseUpcomingEvents" aria-expanded="false" aria-controls="collapseUpcomingEvents">
                <i class="ri-calendar-event-line me-2"></i> Upcoming Events
            </button>
        </h2>
        <div id="collapseUpcomingEvents" class="accordion-collapse collapse" aria-labelledby="headingUpcomingEvents"
            data-bs-parent="#cmsAccordion">
            <div class="accordion-body">
                <form id="upcomingEventsForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $upcomingEvents?->title }}"
                                placeholder="Enter section title">
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveUpcomingEventsBtn">
                                <i class="ri-save-line me-1"></i> Save Upcoming Events
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Past Event Highlights Section --}}
    @php $pastEvents = $cmsData->get('past_event_highlights'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingPastEvents">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapsePastEvents" aria-expanded="false" aria-controls="collapsePastEvents">
                <i class="ri-calendar-event-line me-2"></i> Past Event Highlights
            </button>
        </h2>
        <div id="collapsePastEvents" class="accordion-collapse collapse" aria-labelledby="headingPastEvents"
            data-bs-parent="#cmsAccordion">
            <div class="accordion-body">
                <form id="pastEventsForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $pastEvents?->title }}"
                                placeholder="e.g. Past Event Highlights">
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="savePastEventsBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- Highlights Section --}}
    @php $highlights = $cmsData->get('highlights'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingHighlights">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseHighlights" aria-expanded="false" aria-controls="collapseHighlights">
                <i class="ri-calendar-event-line me-2"></i> Past Six Months Highlights
            </button>
        </h2>
        <div id="collapseHighlights" class="accordion-collapse collapse" aria-labelledby="headingHighlights"
            data-bs-parent="#cmsAccordion">
            <div class="accordion-body">
                <form id="highlightsForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $highlights?->title }}"
                                placeholder="e.g. Past Six Months Highlights">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Section Subtitle</label>
                            <input type="text" name="sub_title" class="form-control"
                                value="{{ $highlights?->sub_title }}"
                                placeholder="e.g. Celebrating our community's achievements...">
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






    {{-- Shop Section --}}
    @php $shop = $cmsData->get('shop'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingShop">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseShop" aria-expanded="false" aria-controls="collapseShop">
                <i class="ri-shopping-bag-line me-2"></i> Shop Section Header
            </button>
        </h2>
        <div id="collapseShop" class="accordion-collapse collapse" aria-labelledby="headingShop"
            data-bs-parent="#cmsAccordion">
            <div class="accordion-body">
                <form id="shopForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $shop?->title }}"
                                placeholder="e.g. Shop OSI Apparel, Ebooks, and Digital">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Section Subtitle</label>
                            <textarea name="sub_title" class="form-control" rows="3"
                                placeholder="Enter section subtitle">{{ $shop?->sub_title }}</textarea>
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveShopBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- CTA Section --}}
    @php $cta = $cmsData->get('cta'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingCta">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseCta" aria-expanded="false" aria-controls="collapseCta">
                <i class="ri-megaphone-line me-2"></i> Become part of a growing network that celebrates
            </button>
        </h2>
        <div id="collapseCta" class="accordion-collapse collapse" aria-labelledby="headingCta"
            data-bs-parent="#cmsAccordion">
            <div class="accordion-body">
                <form id="ctaForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">CTA Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $cta?->title }}"
                                placeholder="e.g. Ready to grow your business?">
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveCtaBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{--Event Sponsors Section --}}
    @php
        $event_sponsors = $cmsData->get('event_sponsors');
        $event_sponsorsItems = $event_sponsors?->metadata ?? [];
    @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingEventSponsors">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseEventSponsors" aria-expanded="false" aria-controls="collapseEventSponsors">
                <i class="ri-team-line me-2"></i>Our Event Sponsors
            </button>
        </h2>
        <div id="collapseEventSponsors" class="accordion-collapse collapse" aria-labelledby="headingEventSponsors"
            data-bs-parent="#cmsAccordion">
            <div class="accordion-body">
                <form id="event_sponsorsForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $event_sponsors?->title }}"
                                placeholder="e.g. Event Sponsors">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Section Sub Title</label>
                            <input type="text" name="sub_title" class="form-control"
                                value="{{ $event_sponsors?->sub_title }}"
                                placeholder="e.g. Join us in celebrating the incredible talent in our community">
                        </div>

                        <div class="col-md-12">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label mb-0">Event Sponsors Logos & Links</label>
                                <button type="button" class="btn btn-sm btn-soft-primary" id="addEventSponsorBtn">
                                    <i class="ri-add-line me-1"></i> Add Event Sponsor
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered align-middle" id="event_sponsorsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Logo</th>
                                            <th>Website Link</th>
                                            <th style="width: 50px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($event_sponsorsItems as $index => $item)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-3">
                                                        @if($item['image'])
                                                            <img src="{{ asset($item['image']) }}" alt="Event Sponsor"
                                                                class="rounded border" style="height: 40px; width: auto;">
                                                        @endif
                                                        <input type="file" name="event_sponsors[{{ $index }}][image_file]"
                                                            class="form-control form-control-sm" accept="image/*">
                                                        <input type="hidden"
                                                            name="event_sponsors[{{ $index }}][existing_image]"
                                                            value="{{ $item['image'] }}">
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="url" name="event_sponsors[{{ $index }}][link]"
                                                        class="form-control form-control-sm" value="{{ $item['link'] }}"
                                                        placeholder="https://...">
                                                </td>
                                                <td class="text-center">
                                                    <button type="button"
                                                        class="btn btn-sm btn-soft-danger remove-event_sponsors-btn">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr class="empty-row">
                                                <td colspan="3" class="text-center text-muted">No Event Sponsors added
                                                    yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveEventSponsorsBtn">
                                <i class="ri-save-line me-1"></i> Save Event Sponsors Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Newsletter Section --}}
    @php $newsletter = $cmsData->get('newsletter'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingNewsletter">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseNewsletter" aria-expanded="false" aria-controls="collapseNewsletter">
                <i class="ri-mail-send-line me-2"></i> Newsletter Section Header
            </button>
        </h2>
        <div id="collapseNewsletter" class="accordion-collapse collapse" aria-labelledby="headingNewsletter"
            data-bs-parent="#cmsAccordion">
            <div class="accordion-body">
                <form id="newsletterForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Newsletter Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $newsletter?->title }}"
                                placeholder="e.g. Stay inspired. Get the latest spotlights...">
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
        $(function () {
            // Hero Logic
            $('#heroForm').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#saveHeroBtn');
                const originalText = $btn.html();

                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

                const formData = new FormData(this);

                axios.post("{{ route('admin.cms.content.update.hero') }}", formData)
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

            $('#addPartnerBtn').on('click', function () {
                const $tbody = $('#partnersTable tbody');
                $tbody.find('.empty-row').remove();

                const row = `
                                                                                                                            <tr>
                                                                                                                                <td>
                                                                                                                                    <div class="d-flex align-items-center gap-3">
                                                                                                                                        <input type="file" name="partners[${partnerCount}][image_file]" class="form-control form-control-sm" accept="image/*">
                                                                                                                                    </div>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input type="url" name="partners[${partnerCount}][link]" class="form-control form-control-sm" placeholder="https://...">
                                                                                                                                </td>
                                                                                                                                <td class="text-center">
                                                                                                                                    <button type="button" class="btn btn-sm btn-soft-danger remove-partner-btn">
                                                                                                                                        <i class="ri-delete-bin-line"></i>
                                                                                                                                    </button>
                                                                                                                                </td>
                                                                                                                            </tr>
                                                                                                                        `;
                $tbody.append(row);
                partnerCount++;
            });

            $(document).on('click', '.remove-partner-btn', function () {
                $(this).closest('tr').remove();
                if ($('#partnersTable tbody tr').length === 0) {
                    $('#partnersTable tbody').append('<tr class="empty-row"><td colspan="3" class="text-center text-muted">No partners added yet.</td></tr>');
                }
            });

            $('#partnersForm').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#savePartnersBtn');
                const originalText = $btn.html();

                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

                const formData = new FormData(this);

                axios.post("{{ route('admin.cms.content.update.partners') }}", formData)
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

                axios.post("{{ route('admin.cms.content.update.features') }}", formData)
                    .then(res => {
                        Toast.success(res.data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    })
                    .catch(err => {
                        Toast.fromResponse(err.response?.data);
                        $btn.prop('disabled', false).html(originalText);
                    });
            });

            // Why Choose Logic
            let whyChooseCount = {{ count($whyChooseItems) }};

            $('#addWhyChooseBtn').on('click', function () {
                const $container = $('#whyChooseContainer');
                $container.find('.empty-msg').remove();

                const card = `
                                                                                                                            <div class="card border border-dashed mb-3 why-choose-item">
                                                                                                                                <div class="card-body">
                                                                                                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                                                                                                        <h6 class="card-title mb-0">New Card</h6>
                                                                                                                                        <button type="button" class="btn btn-sm btn-soft-danger remove-why-choose-btn">
                                                                                                                                            <i class="ri-delete-bin-line"></i>
                                                                                                                                        </button>
                                                                                                                                    </div>
                                                                                                                                    <div class="row g-3">
                                                                                                                                        <div class="col-md-4">
                                                                                                                                            <label class="form-label">Background Image</label>
                                                                                                                                            <input type="file" name="items[${whyChooseCount}][image_file]" class="form-control form-control-sm" accept="image/*">
                                                                                                                                        </div>
                                                                                                                                        <div class="col-md-8">
                                                                                                                                            <div class="mb-3">
                                                                                                                                                <label class="form-label">Title</label>
                                                                                                                                                <input type="text" name="items[${whyChooseCount}][title]" class="form-control form-control-sm" placeholder="e.g. Creators">
                                                                                                                                            </div>
                                                                                                                                            <div class="mb-3">
                                                                                                                                                <label class="form-label">Subtitle</label>
                                                                                                                                                <input type="text" name="items[${whyChooseCount}][sub_title]" class="form-control form-control-sm" placeholder="e.g. Build exposure...">
                                                                                                                                            </div>
                                                                                                                                            <div>
                                                                                                                                                <label class="form-label">Description</label>
                                                                                                                                                <textarea name="items[${whyChooseCount}][description]" class="form-control form-control-sm" rows="2" placeholder="Enter card description"></textarea>
                                                                                                                                            </div>
                                                                                                                                        </div>
                                                                                                                                    </div>
                                                                                                                                </div>
                                                                                                                            </div>
                                                                                                                        `;
                $container.append(card);
                whyChooseCount++;
            });

            $(document).on('click', '.remove-why-choose-btn', function () {
                $(this).closest('.why-choose-item').remove();
                if ($('#whyChooseContainer .why-choose-item').length === 0) {
                    $('#whyChooseContainer').append('<div class="text-center text-muted py-3 empty-msg">No cards added yet.</div>');
                }
            });

            $('#whyChooseForm').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#saveWhyChooseBtn');
                const originalText = $btn.html();

                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

                const formData = new FormData(this);

                axios.post("{{ route('admin.cms.content.update.why_choose') }}", formData)
                    .then(res => {
                        Toast.success(res.data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    })
                    .catch(err => {
                        Toast.fromResponse(err.response?.data);
                        $btn.prop('disabled', false).html(originalText);
                    });
            });

            // Core Values Logic
            let coreValueCount = {{ count($coreValueItems) }};

            $('#addCoreValueBtn').on('click', function () {
                const $container = $('#coreValuesContainer');
                $container.find('.core-value-empty').remove();

                const card = `
                                                                                                                            <div class="card border border-dashed mb-3 core-value-item">
                                                                                                                                <div class="card-body">
                                                                                                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                                                                                                        <h6 class="card-title mb-0">New Value</h6>
                                                                                                                                        <button type="button" class="btn btn-sm btn-soft-danger remove-core-value-btn">
                                                                                                                                            <i class="ri-delete-bin-line"></i>
                                                                                                                                        </button>
                                                                                                                                    </div>
                                                                                                                                    <div class="row g-3">
                                                                                                                                        <div class="col-md-4">
                                                                                                                                            <label class="form-label">Icon (Remix Icon Class)</label>
                                                                                                                                            <input type="text" name="items[${coreValueCount}][icon]" class="form-control form-control-sm" placeholder="e.g. ri-star-line">
                                                                                                                                        </div>
                                                                                                                                        <div class="col-md-8">
                                                                                                                                            <div class="mb-3">
                                                                                                                                                <label class="form-label">Title</label>
                                                                                                                                                <input type="text" name="items[${coreValueCount}][title]" class="form-control form-control-sm" placeholder="e.g. Intentional Visibility">
                                                                                                                                            </div>
                                                                                                                                            <div class="mb-3">
                                                                                                                                                <label class="form-label">Subtitle</label>
                                                                                                                                                <input type="text" name="items[${coreValueCount}][sub_title]" class="form-control form-control-sm" placeholder="e.g. Visibility should be thoughtful...">
                                                                                                                                            </div>
                                                                                                                                            <div>
                                                                                                                                                <label class="form-label">Description</label>
                                                                                                                                                <textarea name="items[${coreValueCount}][description]" class="form-control form-control-sm" rows="2" placeholder="Enter value description"></textarea>
                                                                                                                                            </div>
                                                                                                                                        </div>
                                                                                                                                    </div>
                                                                                                                                </div>
                                                                                                                            </div>
                                                                                                                        `;
                $container.append(card);
                coreValueCount++;
            });

            $(document).on('click', '.remove-core-value-btn', function () {
                $(this).closest('.core-value-item').remove();
                if ($('#coreValuesContainer .core-value-item').length === 0) {
                    $('#coreValuesContainer').append('<div class="text-center text-muted py-3 core-value-empty">No values added yet.</div>');
                }
            });

            $('#coreValuesForm').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#saveCoreValuesBtn');
                const originalText = $btn.html();

                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

                const formData = new FormData(this);

                axios.post("{{ route('admin.cms.content.update.core_values') }}", formData)
                    .then(res => {
                        Toast.success(res.data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    })
                    .catch(err => {
                        Toast.fromResponse(err.response?.data);
                        $btn.prop('disabled', false).html(originalText);
                    });
            });

            // What You Get Logic
            let whatYouGetCount = {{ count($whatYouGetItems) }};

            $('#addWhatYouGetBtn').on('click', function () {
                const $container = $('#whatYouGetContainer');
                $container.find('.what-you-get-empty').remove();

                const card = `
                                                                                                                            <div class="card border border-dashed mb-2 what-you-get-item">
                                                                                                                                <div class="card-body py-2">
                                                                                                                                    <div class="row g-2 align-items-center">
                                                                                                                                        <div class="col-md-4">
                                                                                                                                            <input type="text" name="items[${whatYouGetCount}][icon]" class="form-control form-control-sm" placeholder="Icon (e.g. ri-star-line)">
                                                                                                                                        </div>
                                                                                                                                        <div class="col-md-7">
                                                                                                                                            <input type="text" name="items[${whatYouGetCount}][title]" class="form-control form-control-sm" placeholder="Title (e.g. Business visibility)">
                                                                                                                                        </div>
                                                                                                                                        <div class="col-md-1 text-end">
                                                                                                                                            <button type="button" class="btn btn-sm btn-soft-danger remove-what-you-get-btn">
                                                                                                                                                <i class="ri-delete-bin-line"></i>
                                                                                                                                            </button>
                                                                                                                                        </div>
                                                                                                                                    </div>
                                                                                                                                </div>
                                                                                                                            </div>
                                                                                                                        `;
                $container.append(card);
                whatYouGetCount++;
            });

            $(document).on('click', '.remove-what-you-get-btn', function () {
                $(this).closest('.what-you-get-item').remove();
                if ($('#whatYouGetContainer .what-you-get-item').length === 0) {
                    $('#whatYouGetContainer').append('<div class="text-center text-muted py-3 what-you-get-empty">No cards added yet.</div>');
                }
            });

            $('#whatYouGetForm').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#saveWhatYouGetBtn');
                const originalText = $btn.html();

                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

                const formData = new FormData(this);

                axios.post("{{ route('admin.cms.content.update.what_you_get') }}", formData)
                    .then(res => {
                        Toast.success(res.data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    })
                    .catch(err => {
                        Toast.fromResponse(err.response?.data);
                        $btn.prop('disabled', false).html(originalText);
                    });
            });

            // Boss Beginnings Logic
            $('#bossBeginningsForm').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#saveBossBeginningsBtn');
                const originalText = $btn.html();

                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

                const formData = new FormData(this);

                axios.post("{{ route('admin.cms.content.update.boss_beginnings') }}", formData)
                    .then(res => {
                        Toast.success(res.data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    })
                    .catch(err => {
                        Toast.fromResponse(err.response?.data);
                        $btn.prop('disabled', false).html(originalText);
                    });
            });

            // Boss Beginning Winners Logic
            $('#bossBeginningWinnersForm').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#saveBossBeginningWinnersBtn');
                const originalText = $btn.html();

                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

                const formData = new FormData(this);

                axios.post("{{ route('admin.cms.content.update.boss_beginning_winners') }}", formData)
                    .then(res => {
                        Toast.success(res.data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    })
                    .catch(err => {
                        Toast.fromResponse(err.response?.data);
                        $btn.prop('disabled', false).html(originalText);
                    });
            });

            // Next Boss Beginnings Logic
            $('#nextBossBeginningsForm').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#saveNextBossBeginningsBtn');
                const originalText = $btn.html();

                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

                const formData = new FormData(this);

                axios.post("{{ route('admin.cms.content.update.next_boss_beginnings') }}", formData)
                    .then(res => {
                        Toast.success(res.data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    })
                    .catch(err => {
                        Toast.fromResponse(err.response?.data);
                        $btn.prop('disabled', false).html(originalText);
                    });
            });

            // Upcoming Events Logic
            $('#upcomingEventsForm').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#saveUpcomingEventsBtn');
                const originalText = $btn.html();

                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

                const formData = new FormData(this);

                axios.post("{{ route('admin.cms.content.update.upcoming_events') }}", formData)
                    .then(res => {
                        Toast.success(res.data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    })
                    .catch(err => {
                        Toast.fromResponse(err.response?.data);
                        $btn.prop('disabled', false).html(originalText);
                    });
            });

            // Past Event Highlights Logic
            $('#pastEventsForm').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#savePastEventsBtn');
                const originalText = $btn.html();

                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

                const formData = new FormData(this);

                axios.post("{{ route('admin.cms.content.update.past_event_highlights') }}", formData)
                    .then(res => {
                        Toast.success(res.data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    })
                    .catch(err => {
                        Toast.fromResponse(err.response?.data);
                        $btn.prop('disabled', false).html(originalText);
                    });
            });


            // event_sponsors Logic
            let event_sponsorsCount = {{ count($event_sponsorsItems) }};

            $('#addEventSponsorBtn').on('click', function () {
                const $tbody = $('#event_sponsorsTable tbody');
                $tbody.find('.empty-row').remove();

                const row = `
                                                                                                                            <tr>
                                                                                                                                <td>
                                                                                                                                    <div class="d-flex align-items-center gap-3">
                                                                                                                                        <input type="file" name="event_sponsors[${event_sponsorsCount}][image_file]" class="form-control form-control-sm" accept="image/*">
                                                                                                                                    </div>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input type="url" name="event_sponsors[${event_sponsorsCount}][link]" class="form-control form-control-sm" placeholder="https://...">
                                                                                                                                </td>
                                                                                                                                <td class="text-center">
                                                                                                                                    <button type="button" class="btn btn-sm btn-soft-danger remove-event_sponsors-btn">
                                                                                                                                        <i class="ri-delete-bin-line"></i>
                                                                                                                                    </button>
                                                                                                                                </td>
                                                                                                                            </tr>
                                                                                                                        `;
                $tbody.append(row);
                event_sponsorsCount++;
            });

            $(document).on('click', '.remove-event_sponsors-btn', function () {
                $(this).closest('tr').remove();
                if ($('#event_sponsorsTable tbody tr').length === 0) {
                    $('#event_sponsorsTable tbody').append('<tr class="empty-row"><td colspan="3" class="text-center text-muted">No event_sponsors added yet.</td></tr>');
                }
            });

            $('#event_sponsorsForm').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#saveEventSponsorsBtn');
                const originalText = $btn.html();

                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

                const formData = new FormData(this);

                axios.post("{{ route('admin.cms.content.update.event_sponsors') }}", formData)
                    .then(res => {
                        Toast.success(res.data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    })
                    .catch(err => {
                        Toast.fromResponse(err.response?.data);
                        $btn.prop('disabled', false).html(originalText);
                    });
            });


            // Spotlight Logic
            $('#spotlightForm').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#saveSpotlightBtn');
                const originalText = $btn.html();

                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

                const formData = new FormData(this);

                axios.post("{{ route('admin.cms.content.update.spotlight') }}", formData)
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
            $('#highlightsForm').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#saveHighlightsBtn');
                const originalText = $btn.html();

                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

                const formData = new FormData(this);

                axios.post("{{ route('admin.cms.content.update.highlights') }}", formData)
                    .then(res => {
                        Toast.success(res.data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    })
                    .catch(err => {
                        Toast.fromResponse(err.response?.data);
                        $btn.prop('disabled', false).html(originalText);
                    });
            });

            // Events Logic
            $('#eventsForm').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#saveEventsBtn');
                const originalText = $btn.html();

                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

                const formData = new FormData(this);

                axios.post("{{ route('admin.cms.content.update.events') }}", formData)
                    .then(res => {
                        Toast.success(res.data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    })
                    .catch(err => {
                        Toast.fromResponse(err.response?.data);
                        $btn.prop('disabled', false).html(originalText);
                    });
            });

            // Shop Logic
            $('#shopForm').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#saveShopBtn');
                const originalText = $btn.html();

                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

                const formData = new FormData(this);

                axios.post("{{ route('admin.cms.content.update.shop') }}", formData)
                    .then(res => {
                        Toast.success(res.data.message);
                        setTimeout(() => window.location.reload(), 1000);
                    })
                    .catch(err => {
                        Toast.fromResponse(err.response?.data);
                        $btn.prop('disabled', false).html(originalText);
                    });
            });

            // CTA Logic
            $('#ctaForm').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#saveCtaBtn');
                const originalText = $btn.html();

                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

                const formData = new FormData(this);

                axios.post("{{ route('admin.cms.content.update.cta') }}", formData)
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
            $('#newsletterForm').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#saveNewsletterBtn');
                const originalText = $btn.html();

                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

                const formData = new FormData(this);

                axios.post("{{ route('admin.cms.content.update.newsletter') }}", formData)
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