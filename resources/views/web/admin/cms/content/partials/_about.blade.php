<div class="accordion" id="aboutAccordion">
    {{-- About Hero Section --}}
    @php $hero = $cmsData->get('about_hero'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingHero">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHero" aria-expanded="true" aria-controls="collapseHero">
                <i class="ri-slideshow-line me-2"></i> About Hero Section
            </button>
        </h2>
        <div id="collapseHero" class="accordion-collapse collapse show" aria-labelledby="headingHero" data-bs-parent="#aboutAccordion">
            <div class="accordion-body">
                <form id="heroForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $hero?->title }}" placeholder="e.g. About Us">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Subtitle</label>
                            <textarea name="sub_title" class="form-control" rows="2" placeholder="e.g. Building bridges between business...">{{ $hero?->sub_title }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Background Image</label>
                            <input type="file" name="bg_file" class="form-control" accept="image/*">
                            @if($hero?->bg)
                                <div class="mt-2">
                                    <img src="{{ asset( $hero->bg) }}" alt="Hero Background" class="rounded border" style="max-height: 200px; width: auto;">
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

    {{-- Society Section --}}
    @php $society = $cmsData->get('about_society'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingSociety">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSociety" aria-expanded="false" aria-controls="collapseSociety">
                <i class="ri-community-line me-2"></i> Image of Our Society Section
            </button>
        </h2>
        <div id="collapseSociety" class="accordion-collapse collapse" aria-labelledby="headingSociety" data-bs-parent="#aboutAccordion">
            <div class="accordion-body">
                <form id="societyForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $society?->title }}" placeholder="e.g. We Are the Image of Our Society">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Enter section description">{{ $society?->description }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Section Image</label>
                            <input type="file" name="image_file" class="form-control" accept="image/*">
                            @if($society?->image)
                                <div class="mt-2">
                                    <img src="{{ asset($society->image) }}" alt="Society Image" class="rounded border" style="max-height: 200px; width: auto;">
                                </div>
                            @endif
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveSocietyBtn">
                                <i class="ri-save-line me-1"></i> Save Society Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Origin Story Section --}}
    @php $origin = $cmsData->get('about_origin'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingOrigin">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOrigin" aria-expanded="false" aria-controls="collapseOrigin">
                <i class="ri-history-line me-2"></i> Our Origin Story Section
            </button>
        </h2>
        <div id="collapseOrigin" class="accordion-collapse collapse" aria-labelledby="headingOrigin" data-bs-parent="#aboutAccordion">
            <div class="accordion-body">
                <form id="originForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $origin?->title }}" placeholder="e.g. Our Origin Story">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Enter origin story description">{{ $origin?->description }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Section Image</label>
                            <input type="file" name="image_file" class="form-control" accept="image/*">
                            @if($origin?->image)
                                <div class="mt-2">
                                    <img src="{{ asset($origin->image) }}" alt="Origin Image" class="rounded border" style="max-height: 200px; width: auto;">
                                </div>
                            @endif
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveOriginBtn">
                                <i class="ri-save-line me-1"></i> Save Origin Story
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Mission & Purpose Section --}}
    @php 
        $mission = $cmsData->get('about_mission'); 
        $missionItems = $mission?->metadata ?? [];
    @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingMission">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMission" aria-expanded="false" aria-controls="collapseMission">
                <i class="ri-rocket-line me-2"></i> Mission & Purpose Section
            </button>
        </h2>
        <div id="collapseMission" class="accordion-collapse collapse" aria-labelledby="headingMission" data-bs-parent="#aboutAccordion">
            <div class="accordion-body">
                <form id="missionForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Main Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $mission?->title }}" placeholder="e.g. Mission & Purpose">
                        </div>
                        
                        <div class="col-12 mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Cards (Mission, Purpose, etc.)</h6>
                                <button type="button" class="btn btn-sm btn-soft-primary" id="addMissionBtn">
                                    <i class="ri-add-line me-1"></i> Add Card
                                </button>
                            </div>
                            <div id="missionContainer">
                                @forelse($missionItems as $index => $item)
                                    <div class="card border border-dashed mb-3 mission-item">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="card-title mb-0">Card #{{ $index + 1 }}</h6>
                                                <button type="button" class="btn btn-sm btn-soft-danger remove-mission-btn">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Icon / Image</label>
                                                    <input type="file" name="items[{{ $index }}][image_file]" class="form-control form-control-sm" accept="image/*">
                                                    <input type="hidden" name="items[{{ $index }}][existing_image]" value="{{ $item['image'] ?? '' }}">
                                                    @if($item['image'] ?? null)
                                                        <div class="mt-2">
                                                            <img src="{{ asset($item['image']) }}" alt="Icon" class="rounded border" style="height: 50px;">
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="mb-3">
                                                        <label class="form-label">Title</label>
                                                        <input type="text" name="items[{{ $index }}][title]" class="form-control form-control-sm" value="{{ $item['title'] ?? '' }}" placeholder="e.g. Mission">
                                                    </div>
                                                    <div>
                                                        <label class="form-label">Description</label>
                                                        <textarea name="items[{{ $index }}][description]" class="form-control form-control-sm" rows="3" placeholder="Enter card description">{{ $item['description'] ?? '' }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-3 mission-empty">No cards added yet.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveMissionBtn">
                                <i class="ri-save-line me-1"></i> Save Mission Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- What We Do Section --}}
    @php 
        $whatWeDo = $cmsData->get('about_what_we_do'); 
        $whatWeDoItems = $whatWeDo?->metadata ?? [];
    @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingWhatWeDo">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseWhatWeDo" aria-expanded="false" aria-controls="collapseWhatWeDo">
                <i class="ri-briefcase-line me-2"></i> What We Do Section
            </button>
        </h2>
        <div id="collapseWhatWeDo" class="accordion-collapse collapse" aria-labelledby="headingWhatWeDo" data-bs-parent="#aboutAccordion">
            <div class="accordion-body">
                <form id="whatWeDoForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $whatWeDo?->title }}" placeholder="e.g. What We Do">
                        </div>
                        
                        <div class="col-12 mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Cards</h6>
                                <button type="button" class="btn btn-sm btn-soft-primary" id="addWhatWeDoBtn">
                                    <i class="ri-add-line me-1"></i> Add Card
                                </button>
                            </div>
                            <div id="whatWeDoContainer">
                                @forelse($whatWeDoItems as $index => $item)
                                    <div class="card border border-dashed mb-3 what-we-do-item">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="card-title mb-0">Card #{{ $index + 1 }}</h6>
                                                <button type="button" class="btn btn-sm btn-soft-danger remove-what-we-do-btn">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <div>
                                                        <label class="form-label">Image</label>
                                                        <input type="file" name="items[{{ $index }}][image_file]" class="form-control form-control-sm" accept="image/*">
                                                        <input type="hidden" name="items[{{ $index }}][existing_image]" value="{{ $item['image'] ?? '' }}">
                                                        @if($item['image'] ?? null)
                                                            <div class="mt-2">
                                                                <img src="{{ asset(  $item['image']) }}" alt="Icon" class="rounded border" style="height: 50px;">
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="mb-3">
                                                        <label class="form-label">Title</label>
                                                        <input type="text" name="items[{{ $index }}][title]" class="form-control form-control-sm" value="{{ $item['title'] ?? '' }}" placeholder="Enter title">
                                                    </div>
                                                    <div>
                                                        <label class="form-label">Description</label>
                                                        <textarea name="items[{{ $index }}][description]" class="form-control form-control-sm" rows="3" placeholder="Enter description">{{ $item['description'] ?? '' }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-3 what-we-do-empty">No cards added yet.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveWhatWeDoBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- How It Works Section --}}
    @php 
        $howItWorks = $cmsData->get('about_how_it_works'); 
        $howItWorksItems = $howItWorks?->metadata ?? [];
    @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingHowItWorks">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHowItWorks" aria-expanded="false" aria-controls="collapseHowItWorks">
                <i class="ri-settings-line me-2"></i> How Our Social Image Works Section
            </button>
        </h2>
        <div id="collapseHowItWorks" class="accordion-collapse collapse" aria-labelledby="headingHowItWorks" data-bs-parent="#aboutAccordion">
            <div class="accordion-body">
                <form id="howItWorksForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $howItWorks?->title }}" placeholder="e.g. How Our Social Image Works">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Section Subtitle</label>
                            <input type="text" name="sub_title" class="form-control" value="{{ $howItWorks?->sub_title }}" placeholder="e.g. A simple ecosystem built...">
                        </div>
                        
                        <div class="col-12 mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Cards</h6>
                                <button type="button" class="btn btn-sm btn-soft-primary" id="addHowItWorksBtn">
                                    <i class="ri-add-line me-1"></i> Add Card
                                </button>
                            </div>
                            <div id="howItWorksContainer">
                                @forelse($howItWorksItems as $index => $item)
                                    <div class="card border border-dashed mb-3 how-it-works-item">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="card-title mb-0">Card #{{ $index + 1 }}</h6>
                                                <button type="button" class="btn btn-sm btn-soft-danger remove-how-it-works-btn">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <div>
                                                        <label class="form-label">Image</label>
                                                        <input type="file" name="items[{{ $index }}][image_file]" class="form-control form-control-sm" accept="image/*">
                                                        <input type="hidden" name="items[{{ $index }}][existing_image]" value="{{ $item['image'] ?? '' }}">
                                                        @if($item['image'] ?? null)
                                                            <div class="mt-2">
                                                                <img src="{{ asset( $item['image']) }}" alt="Icon" class="rounded border" style="height: 50px;">
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="mb-3">
                                                        <label class="form-label">Title</label>
                                                        <input type="text" name="items[{{ $index }}][title]" class="form-control form-control-sm" value="{{ $item['title'] ?? '' }}" placeholder="Enter card title">
                                                    </div>
                                                    <div>
                                                        <label class="form-label">Description</label>
                                                        <textarea name="items[{{ $index }}][description]" class="form-control form-control-sm" rows="3" placeholder="Enter card description">{{ $item['description'] ?? '' }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-3 how-it-works-empty">No cards added yet.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveHowItWorksBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Who We Serve Section --}}
    @php $serve = $cmsData->get('about_who_we_serve'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingServe">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseServe" aria-expanded="false" aria-controls="collapseServe">
                <i class="ri-group-line me-2"></i> Who We Serve Section
            </button>
        </h2>
        <div id="collapseServe" class="accordion-collapse collapse" aria-labelledby="headingServe" data-bs-parent="#aboutAccordion">
            <div class="accordion-body">
                <form id="serveForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $serve?->title }}" placeholder="e.g. Who We Serve">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Enter description">{{ $serve?->description }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Side Image</label>
                            <input type="file" name="image_file" class="form-control" accept="image/*">
                            @if($serve?->image)
                                <div class="mt-2">
                                    <img src="{{ asset(  $serve->image) }}" alt="Serve Image" class="rounded border" style="height: 100px;">
                                </div>
                            @endif
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveServeBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Why OSI Exists Section --}}
    @php $whyExists = $cmsData->get('about_why_exists'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingWhyExists">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseWhyExists" aria-expanded="false" aria-controls="collapseWhyExists">
                <i class="ri-question-line me-2"></i> Why OSI Exists Section
            </button>
        </h2>
        <div id="collapseWhyExists" class="accordion-collapse collapse" aria-labelledby="headingWhyExists" data-bs-parent="#aboutAccordion">
            <div class="accordion-body">
                <form id="whyExistsForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $whyExists?->title }}" placeholder="e.g. Why OSI Exists">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Enter description">{{ $whyExists?->description }}</textarea>
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveWhyExistsBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Our Impact Section --}}
    @php 
        $impact = $cmsData->get('about_our_impact'); 
        $impactItems = $impact?->metadata ?? [];
    @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingImpact">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseImpact" aria-expanded="false" aria-controls="collapseImpact">
                <i class="ri-pulse-line me-2"></i> Our Impact Section
            </button>
        </h2>
        <div id="collapseImpact" class="accordion-collapse collapse" aria-labelledby="headingImpact" data-bs-parent="#aboutAccordion">
            <div class="accordion-body">
                <form id="impactForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $impact?->title }}" placeholder="e.g. Our Impact">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Subtitle</label>
                            <textarea name="sub_title" class="form-control" rows="2" placeholder="Enter subtitle">{{ $impact?->sub_title }}</textarea>
                        </div>
                        
                        <div class="col-md-12 mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label mb-0">Impact Items (Checklist)</label>
                                <button type="button" class="btn btn-sm btn-success" id="addImpactBtn">
                                    <i class="ri-add-line me-1"></i> Add Item
                                </button>
                            </div>
                            <div id="impactContainer">
                                @forelse($impactItems as $index => $item)
                                    <div class="input-group mb-2 impact-item">
                                        <span class="input-group-text"><i class="ri-check-line text-success"></i></span>
                                        <input type="text" name="items[]" class="form-control" value="{{ $item }}" placeholder="Enter impact item">
                                        <button type="button" class="btn btn-outline-danger remove-impact-btn">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-3 impact-empty">No items added yet.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveImpactBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Founder Message Section --}}
    @php 
        $founder = $cmsData->get('about_founder_message'); 
        $founderItems = $founder?->metadata ?? [];
    @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingFounder">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFounder" aria-expanded="false" aria-controls="collapseFounder">
                <i class="ri-user-voice-line me-2"></i> Founder Message Section
            </button>
        </h2>
        <div id="collapseFounder" class="accordion-collapse collapse" aria-labelledby="headingFounder" data-bs-parent="#aboutAccordion">
            <div class="accordion-body">
                <form id="founderForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $founder?->title }}" placeholder="e.g. A Message From the Founder">
                        </div>

                        <div class="col-md-12 mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label mb-0">Messages (Slider Items)</label>
                                <button type="button" class="btn btn-sm btn-success" id="addFounderBtn">
                                    <i class="ri-add-line me-1"></i> Add Message
                                </button>
                            </div>
                            <div id="founderContainer">
                                @forelse($founderItems as $index => $item)
                                    <div class="card border border-dashed mb-3 founder-item">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="card-title mb-0">Message #{{ $index + 1 }}</h6>
                                                <button type="button" class="btn btn-sm btn-soft-danger remove-founder-btn">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Founder Image</label>
                                                    <input type="file" name="items[{{ $index }}][image_file]" class="form-control form-control-sm" accept="image/*">
                                                    <input type="hidden" name="items[{{ $index }}][existing_image]" value="{{ $item['image'] ?? '' }}">
                                                    @if($item['image'] ?? null)
                                                        <div class="mt-2 text-center">
                                                            <img src="{{ asset( $item['image']) }}" alt="Founder" class="rounded border shadow-sm" style="height: 80px; width: 80px; object-fit: cover;">
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="row g-2">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Name</label>
                                                            <input type="text" name="items[{{ $index }}][name]" class="form-control form-control-sm" value="{{ $item['name'] ?? '' }}" placeholder="e.g. Jared Mitchell Sr.">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Designation</label>
                                                            <input type="text" name="items[{{ $index }}][designation]" class="form-control form-control-sm" value="{{ $item['designation'] ?? '' }}" placeholder="e.g. Founder & CEO">
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label">Sub Label</label>
                                                            <input type="text" name="items[{{ $index }}][sub_label]" class="form-control form-control-sm" value="{{ $item['sub_label'] ?? '' }}" placeholder="e.g. Our Social Image">
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label">Message/Quote</label>
                                                            <textarea name="items[{{ $index }}][message]" class="form-control form-control-sm" rows="3" placeholder="Enter message text">{{ $item['message'] ?? '' }}</textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-3 founder-empty">No messages added yet.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveFounderBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Join Section --}}
    @php $join = $cmsData->get('about_join'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingJoin">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseJoin" aria-expanded="false" aria-controls="collapseJoin">
                <i class="ri-user-add-line me-2"></i> Join Our Community Section
            </button>
        </h2>
        <div id="collapseJoin" class="accordion-collapse collapse" aria-labelledby="headingJoin" data-bs-parent="#aboutAccordion">
            <div class="accordion-body">
                <form id="joinForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $join?->title }}" placeholder="e.g. Join our creator community...">
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveJoinBtn">
                                <i class="ri-save-line me-1"></i> Save Section
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Sponsors Section --}}
    @php 
        $sponsors = $cmsData->get('about_sponsors'); 
        $sponsorItems = $sponsors?->metadata ?? [];
    @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingSponsors">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSponsors" aria-expanded="false" aria-controls="collapseSponsors">
                <i class="ri-shield-user-line me-2"></i> Sponsors Section
            </button>
        </h2>
        <div id="collapseSponsors" class="accordion-collapse collapse" aria-labelledby="headingSponsors" data-bs-parent="#aboutAccordion">
            <div class="accordion-body">
                <form id="sponsorsForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label mb-0">Sponsor Logos & Links</label>
                                <button type="button" class="btn btn-sm btn-primary" id="addSponsorBtn">
                                    <i class="ri-add-line me-1"></i> Add Sponsor
                                </button>
                            </div>
                            <div class="row g-3" id="sponsorsContainer">
                                @forelse($sponsorItems as $index => $item)
                                    <div class="col-md-3 sponsor-item">
                                        <div class="card border shadow-none mb-0">
                                            <div class="card-body p-2">
                                                <div class="text-end mb-2">
                                                    <button type="button" class="btn btn-sm btn-soft-danger remove-sponsor-btn">
                                                        <i class="ri-close-line"></i>
                                                    </button>
                                                </div>
                                                <div class="text-center">
                                                    <input type="file" name="items[{{ $index }}][image_file]" class="form-control form-control-sm mb-2" accept="image/*">
                                                    <input type="hidden" name="items[{{ $index }}][existing_image]" value="{{ $item['image'] ?? '' }}">
                                                    @if($item['image'] ?? null)
                                                        <img src="{{ asset($item['image']) }}" alt="Sponsor" class="img-fluid rounded mb-2" style="max-height: 50px;">
                                                    @else
                                                        <div class="bg-light rounded py-3 text-muted" style="font-size: 10px;">No Image</div>
                                                    @endif
                                                </div>
                                                <div class="mt-3">
                                                    <label class="form-label small mb-1">Logo Link</label>
                                                    <input type="url" name="items[{{ $index }}][link]" class="form-control form-control-sm" value="{{ $item['link'] ?? '' }}" placeholder="https://...">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-center text-muted py-3 sponsors-empty">No sponsor logos added yet.</div>
                                @endforelse
                            </div>
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveSponsorsBtn">
                                <i class="ri-save-line me-1"></i> Save Sponsors
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- About Newsletter Section --}}
    @php $newsletterAbout = $cmsData->get('about_newsletter'); @endphp
    <div class="accordion-item card mb-3">
        <h2 class="accordion-header" id="headingNewsletterAbout">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNewsletterAbout" aria-expanded="false" aria-controls="collapseNewsletterAbout">
                <i class="ri-mail-send-line me-2"></i> Newsletter Section
            </button>
        </h2>
        <div id="collapseNewsletterAbout" class="accordion-collapse collapse" aria-labelledby="headingNewsletterAbout" data-bs-parent="#aboutAccordion">
            <div class="accordion-body">
                <form id="newsletterAboutForm">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $newsletterAbout?->title }}" placeholder="e.g. Subscribe to our newsletter...">
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" id="saveNewsletterAboutBtn">
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
    // About Hero Logic
    $('#heroForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveHeroBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        axios.post("{{ route('admin.cms.about.update.hero') }}", formData)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // Society Logic
    $('#societyForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveSocietyBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        axios.post("{{ route('admin.cms.about.update.society') }}", formData)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // Origin Logic
    $('#originForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveOriginBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        axios.post("{{ route('admin.cms.about.update.origin') }}", formData)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // Mission Logic
    let missionCount = {{ count($missionItems) }};
    $('#addMissionBtn').on('click', function() {
        const $container = $('#missionContainer');
        $container.find('.mission-empty').remove();
        const card = `
            <div class="card border border-dashed mb-3 mission-item">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="card-title mb-0">New Card</h6>
                        <button type="button" class="btn btn-sm btn-soft-danger remove-mission-btn">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Icon / Image</label>
                            <input type="file" name="items[${missionCount}][image_file]" class="form-control form-control-sm" accept="image/*">
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="items[${missionCount}][title]" class="form-control form-control-sm" placeholder="e.g. Mission">
                            </div>
                            <div>
                                <label class="form-label">Description</label>
                                <textarea name="items[${missionCount}][description]" class="form-control form-control-sm" rows="3" placeholder="Enter card description"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $container.append(card);
        missionCount++;
    });
    $(document).on('click', '.remove-mission-btn', function() {
        $(this).closest('.mission-item').remove();
        if ($('#missionContainer .mission-item').length === 0) {
            $('#missionContainer').append('<div class="text-center text-muted py-3 mission-empty">No cards added yet.</div>');
        }
    });
    $('#missionForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveMissionBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        axios.post("{{ route('admin.cms.about.update.mission') }}", formData)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // What We Do Logic
    let whatWeDoCount = {{ count($whatWeDoItems) }};
    $('#addWhatWeDoBtn').on('click', function() {
        const $container = $('#whatWeDoContainer');
        $container.find('.what-we-do-empty').remove();
        const card = `
            <div class="card border border-dashed mb-3 what-we-do-item">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="card-title mb-0">New Card</h6>
                        <button type="button" class="btn btn-sm btn-soft-danger remove-what-we-do-btn">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div>
                                <label class="form-label">Image</label>
                                <input type="file" name="items[${whatWeDoCount}][image_file]" class="form-control form-control-sm" accept="image/*">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="items[${whatWeDoCount}][title]" class="form-control form-control-sm" placeholder="Enter title">
                            </div>
                            <div>
                                <label class="form-label">Description</label>
                                <textarea name="items[${whatWeDoCount}][description]" class="form-control form-control-sm" rows="3" placeholder="Enter description"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $container.append(card);
        whatWeDoCount++;
    });
    $(document).on('click', '.remove-what-we-do-btn', function() {
        $(this).closest('.what-we-do-item').remove();
        if ($('#whatWeDoContainer .what-we-do-item').length === 0) {
            $('#whatWeDoContainer').append('<div class="text-center text-muted py-3 what-we-do-empty">No cards added yet.</div>');
        }
    });
    $('#whatWeDoForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveWhatWeDoBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        axios.post("{{ route('admin.cms.about.update.what_we_do') }}", formData)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // How It Works Logic
    let howItWorksCount = {{ count($howItWorksItems) }};
    $('#addHowItWorksBtn').on('click', function() {
        const $container = $('#howItWorksContainer');
        $container.find('.how-it-works-empty').remove();
        const card = `
            <div class="card border border-dashed mb-3 how-it-works-item">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="card-title mb-0">New Card</h6>
                        <button type="button" class="btn btn-sm btn-soft-danger remove-how-it-works-btn">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div>
                                <label class="form-label">Image</label>
                                <input type="file" name="items[${howItWorksCount}][image_file]" class="form-control form-control-sm" accept="image/*">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="items[${howItWorksCount}][title]" class="form-control form-control-sm" placeholder="Enter card title">
                            </div>
                            <div>
                                <label class="form-label">Description</label>
                                <textarea name="items[${howItWorksCount}][description]" class="form-control form-control-sm" rows="3" placeholder="Enter card description"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $container.append(card);
        howItWorksCount++;
    });
    $(document).on('click', '.remove-how-it-works-btn', function() {
        $(this).closest('.how-it-works-item').remove();
        if ($('#howItWorksContainer .how-it-works-item').length === 0) {
            $('#howItWorksContainer').append('<div class="text-center text-muted py-3 how-it-works-empty">No cards added yet.</div>');
        }
    });
    $('#howItWorksForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveHowItWorksBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        axios.post("{{ route('admin.cms.about.update.how_it_works') }}", formData)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // Who We Serve Logic
    $('#serveForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveServeBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        axios.post("{{ route('admin.cms.about.update.who_we_serve') }}", formData)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // Why OSI Exists Logic
    $('#whyExistsForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveWhyExistsBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        axios.post("{{ route('admin.cms.about.update.why_exists') }}", formData)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // Our Impact Logic
    $('#addImpactBtn').on('click', function() {
        const $container = $('#impactContainer');
        $container.find('.impact-empty').remove();
        const item = `
            <div class="input-group mb-2 impact-item">
                <span class="input-group-text"><i class="ri-check-line text-success"></i></span>
                <input type="text" name="items[]" class="form-control" placeholder="Enter impact item">
                <button type="button" class="btn btn-outline-danger remove-impact-btn">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
        `;
        $container.append(item);
    });
    $(document).on('click', '.remove-impact-btn', function() {
        $(this).closest('.impact-item').remove();
        if ($('#impactContainer .impact-item').length === 0) {
            $('#impactContainer').append('<div class="text-center text-muted py-3 impact-empty">No items added yet.</div>');
        }
    });
    $('#impactForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveImpactBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        axios.post("{{ route('admin.cms.about.update.our_impact') }}", formData)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // Founder Message Logic
    let founderCount = {{ count($founderItems) }};
    $('#addFounderBtn').on('click', function() {
        const $container = $('#founderContainer');
        $container.find('.founder-empty').remove();
        const card = `
            <div class="card border border-dashed mb-3 founder-item">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="card-title mb-0">New Message</h6>
                        <button type="button" class="btn btn-sm btn-soft-danger remove-founder-btn">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Founder Image</label>
                            <input type="file" name="items[${founderCount}][image_file]" class="form-control form-control-sm" accept="image/*">
                        </div>
                        <div class="col-md-8">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="items[${founderCount}][name]" class="form-control form-control-sm" placeholder="e.g. Jared Mitchell Sr.">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Designation</label>
                                    <input type="text" name="items[${founderCount}][designation]" class="form-control form-control-sm" placeholder="e.g. Founder & CEO">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Sub Label</label>
                                    <input type="text" name="items[${founderCount}][sub_label]" class="form-control form-control-sm" placeholder="e.g. Our Social Image">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Message/Quote</label>
                                    <textarea name="items[${founderCount}][message]" class="form-control form-control-sm" rows="3" placeholder="Enter message text"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $container.append(card);
        founderCount++;
    });
    $(document).on('click', '.remove-founder-btn', function() {
        $(this).closest('.founder-item').remove();
        if ($('#founderContainer .founder-item').length === 0) {
            $('#founderContainer').append('<div class="text-center text-muted py-3 founder-empty">No messages added yet.</div>');
        }
    });
    $('#founderForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveFounderBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        axios.post("{{ route('admin.cms.about.update.founder_message') }}", formData)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // Join Logic
    $('#joinForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveJoinBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        axios.post("{{ route('admin.cms.about.update.join') }}", formData)
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
    $('#newsletterAboutForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveNewsletterAboutBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        axios.post("{{ route('admin.cms.about.update.newsletter') }}", formData)
            .then(res => {
                Toast.success(res.data.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                Toast.fromResponse(err.response?.data);
                $btn.prop('disabled', false).html(originalText);
            });
    });

    // Sponsors Logic
    let sponsorCount = {{ count($sponsorItems) }};
    $('#addSponsorBtn').on('click', function() {
        const $container = $('#sponsorsContainer');
        $container.find('.sponsors-empty').remove();
        const item = `
            <div class="col-md-3 sponsor-item">
                <div class="card border shadow-none mb-0">
                    <div class="card-body p-2">
                        <div class="text-end mb-2">
                            <button type="button" class="btn btn-sm btn-soft-danger remove-sponsor-btn">
                                <i class="ri-close-line"></i>
                            </button>
                        </div>
                        <div class="text-center">
                            <input type="file" name="items[${sponsorCount}][image_file]" class="form-control form-control-sm mb-2" accept="image/*">
                            <div class="bg-light rounded py-3 text-muted" style="font-size: 10px;">New Logo</div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label small mb-1">Logo Link</label>
                            <input type="url" name="items[${sponsorCount}][link]" class="form-control form-control-sm" placeholder="https://...">
                        </div>
                    </div>
                </div>
            </div>
        `;
        $container.append(item);
        sponsorCount++;
    });
    $(document).on('click', '.remove-sponsor-btn', function() {
        $(this).closest('.sponsor-item').remove();
        if ($('#sponsorsContainer .sponsor-item').length === 0) {
            $('#sponsorsContainer').append('<div class="col-12 text-center text-muted py-3 sponsors-empty">No sponsor logos added yet.</div>');
        }
    });
    $('#sponsorsForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveSponsorsBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        const formData = new FormData(this);
        axios.post("{{ route('admin.cms.about.update.sponsors') }}", formData)
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
