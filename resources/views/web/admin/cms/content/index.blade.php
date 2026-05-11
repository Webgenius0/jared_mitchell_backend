@extends('layout.master-layout')
@section('title', 'CMS Content')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Manage CMS Content</h4>
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">CMS Content</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Pages</h5>
                    </div>
                    <div class="card-body p-2">
                        <div class="list-group list-group-flush">
                            @foreach ($pages as $page)
                                <a href="{{ route('admin.cms.content.index', ['page' => $page->value]) }}"
                                    class="list-group-item list-group-item-action {{ $currentPage === $page->value ? 'active' : '' }}">
                                    {{ ucfirst($page->value) }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="accordion" id="cmsAccordion">
                    {{-- Hero Section --}}
                    @php $hero = $cmsData->get('hero'); @endphp
                    <div class="accordion-item card mb-3">
                        <h2 class="accordion-header" id="headingHero">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHero" aria-expanded="true" aria-controls="collapseHero">
                                <i class="ri-slideshow-line me-2"></i> Hero Section
                            </button>
                        </h2>
                        <div id="collapseHero" class="accordion-collapse collapse show" aria-labelledby="headingHero" data-bs-parent="#cmsAccordion">
                            <div class="accordion-body">
                                <form id="heroForm" enctype="multipart/form-data">
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Title</label>
                                            <input type="text" name="title" class="form-control" value="{{ $hero?->title }}" placeholder="Enter hero title">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Subtitle</label>
                                            <input type="text" name="sub_title" class="form-control" value="{{ $hero?->sub_title }}" placeholder="Enter hero subtitle">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Description</label>
                                            <textarea name="description" class="form-control" rows="3" placeholder="Enter hero description">{{ $hero?->description }}</textarea>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Hero Video</label>
                                            <input type="file" name="video_file" class="form-control" accept="video/*">
                                            @if($hero?->video)
                                                <div class="mt-2 d-flex align-items-center gap-2">
                                                    <span class="badge bg-success-subtle text-success">Current Video: {{ basename($hero->video) }}</span>
                                                    <a href="{{ asset('storage/' . $hero->video) }}" target="_blank" class="btn btn-sm btn-link p-0">View Video</a>
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
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePartners" aria-expanded="false" aria-controls="collapsePartners">
                                <i class="ri-team-line me-2"></i> Partners Section
                            </button>
                        </h2>
                        <div id="collapsePartners" class="accordion-collapse collapse" aria-labelledby="headingPartners" data-bs-parent="#cmsAccordion">
                            <div class="accordion-body">
                                <form id="partnersForm" enctype="multipart/form-data">
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Section Title</label>
                                            <input type="text" name="title" class="form-control" value="{{ $partners?->title }}" placeholder="e.g. Powered by our community partners">
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
                                                                            <img src="{{ asset('storage/' . $item['image']) }}" alt="Partner" class="rounded border" style="height: 40px; width: auto;">
                                                                        @endif
                                                                        <input type="file" name="partners[{{ $index }}][image_file]" class="form-control form-control-sm" accept="image/*">
                                                                        <input type="hidden" name="partners[{{ $index }}][existing_image]" value="{{ $item['image'] }}">
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <input type="url" name="partners[{{ $index }}][link]" class="form-control form-control-sm" value="{{ $item['link'] }}" placeholder="https://...">
                                                                </td>
                                                                <td class="text-center">
                                                                    <button type="button" class="btn btn-sm btn-soft-danger remove-partner-btn">
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
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFeatures" aria-expanded="false" aria-controls="collapseFeatures">
                                <i class="ri-rocket-line me-2"></i> Features Section
                            </button>
                        </h2>
                        <div id="collapseFeatures" class="accordion-collapse collapse" aria-labelledby="headingFeatures" data-bs-parent="#cmsAccordion">
                            <div class="accordion-body">
                                <form id="featuresForm" enctype="multipart/form-data">
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Title</label>
                                            <input type="text" name="title" class="form-control" value="{{ $features?->title }}" placeholder="e.g. Everything You Need to Grow Your Business">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Description</label>
                                            <textarea name="description" class="form-control" rows="4" placeholder="Enter section description">{{ $features?->description }}</textarea>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Background Image</label>
                                            <input type="file" name="bg_file" class="form-control" accept="image/*">
                                            @if($features?->bg)
                                                <div class="mt-2">
                                                    <img src="{{ asset('storage/' . $features->bg) }}" alt="Background" class="rounded border" style="max-height: 150px; width: auto;">
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
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseWhyChoose" aria-expanded="false" aria-controls="collapseWhyChoose">
                                <i class="ri-question-line me-2"></i> Why Choose Section
                            </button>
                        </h2>
                        <div id="collapseWhyChoose" class="accordion-collapse collapse" aria-labelledby="headingWhyChoose" data-bs-parent="#cmsAccordion">
                            <div class="accordion-body">
                                <form id="whyChooseForm" enctype="multipart/form-data">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Main Title</label>
                                            <input type="text" name="title" class="form-control" value="{{ $whyChoose?->title }}" placeholder="e.g. WHY CHOOSE OSI">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Main Subtitle</label>
                                            <input type="text" name="sub_title" class="form-control" value="{{ $whyChoose?->sub_title }}" placeholder="e.g. Fostering a culture of support...">
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
                                                                <button type="button" class="btn btn-sm btn-soft-danger remove-why-choose-btn">
                                                                    <i class="ri-delete-bin-line"></i>
                                                                </button>
                                                            </div>
                                                            <div class="row g-3">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Background Image</label>
                                                                    <input type="file" name="items[{{ $index }}][image_file]" class="form-control form-control-sm" accept="image/*">
                                                                    <input type="hidden" name="items[{{ $index }}][existing_image]" value="{{ $item['image'] }}">
                                                                    @if($item['image'])
                                                                        <div class="mt-2 text-center">
                                                                            <img src="{{ asset('storage/' . $item['image']) }}" alt="Card Image" class="rounded" style="height: 60px; width: auto;">
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                <div class="col-md-8">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Title</label>
                                                                        <input type="text" name="items[{{ $index }}][title]" class="form-control form-control-sm" value="{{ $item['title'] }}" placeholder="e.g. Creators">
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Subtitle</label>
                                                                        <input type="text" name="items[{{ $index }}][sub_title]" class="form-control form-control-sm" value="{{ $item['sub_title'] }}" placeholder="e.g. Build exposure...">
                                                                    </div>
                                                                    <div>
                                                                        <label class="form-label">Description</label>
                                                                        <textarea name="items[{{ $index }}][description]" class="form-control form-control-sm" rows="2" placeholder="Enter card description">{{ $item['description'] }}</textarea>
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
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCoreValues" aria-expanded="false" aria-controls="collapseCoreValues">
                                <i class="ri-heart-line me-2"></i> Core Values Section
                            </button>
                        </h2>
                        <div id="collapseCoreValues" class="accordion-collapse collapse" aria-labelledby="headingCoreValues" data-bs-parent="#cmsAccordion">
                            <div class="accordion-body">
                                <form id="coreValuesForm" enctype="multipart/form-data">
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Section Title</label>
                                            <input type="text" name="title" class="form-control" value="{{ $coreValues?->title }}" placeholder="e.g. Our Core Values">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Section Background Image</label>
                                            <input type="file" name="bg_file" class="form-control" accept="image/*">
                                            @if($coreValues?->bg)
                                                <div class="mt-2 text-center">
                                                    <img src="{{ asset('storage/' . $coreValues->bg) }}" alt="Core Values BG" class="rounded" style="height: 100px; width: auto;">
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
                                                                <button type="button" class="btn btn-sm btn-soft-danger remove-core-value-btn">
                                                                    <i class="ri-delete-bin-line"></i>
                                                                </button>
                                                            </div>
                                                            <div class="row g-3">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Icon (Remix Icon Class)</label>
                                                                    <input type="text" name="items[{{ $index }}][icon]" class="form-control form-control-sm" value="{{ $item['icon'] }}" placeholder="e.g. ri-star-line">
                                                                </div>
                                                                <div class="col-md-8">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Title</label>
                                                                        <input type="text" name="items[{{ $index }}][title]" class="form-control form-control-sm" value="{{ $item['title'] }}" placeholder="e.g. Intentional Visibility">
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Subtitle</label>
                                                                        <input type="text" name="items[{{ $index }}][sub_title]" class="form-control form-control-sm" value="{{ $item['sub_title'] }}" placeholder="e.g. Visibility should be thoughtful...">
                                                                    </div>
                                                                    <div>
                                                                        <label class="form-label">Description</label>
                                                                        <textarea name="items[{{ $index }}][description]" class="form-control form-control-sm" rows="2" placeholder="Enter value description">{{ $item['description'] }}</textarea>
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
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseWhatYouGet" aria-expanded="false" aria-controls="collapseWhatYouGet">
                                <i class="ri-checkbox-circle-line me-2"></i> What You're Really Getting
                            </button>
                        </h2>
                        <div id="collapseWhatYouGet" class="accordion-collapse collapse" aria-labelledby="headingWhatYouGet" data-bs-parent="#cmsAccordion">
                            <div class="accordion-body">
                                <form id="whatYouGetForm">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Section Title</label>
                                            <input type="text" name="title" class="form-control" value="{{ $whatYouGet?->title }}" placeholder="e.g. What You're Really Getting">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Section Subtitle</label>
                                            <input type="text" name="sub_title" class="form-control" value="{{ $whatYouGet?->sub_title }}" placeholder="e.g. You're not buying a membership...">
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
                                                                    <input type="text" name="items[{{ $index }}][icon]" class="form-control form-control-sm" value="{{ $item['icon'] }}" placeholder="Icon (e.g. ri-star-line)">
                                                                </div>
                                                                <div class="col-md-7">
                                                                    <input type="text" name="items[{{ $index }}][title]" class="form-control form-control-sm" value="{{ $item['title'] }}" placeholder="Title (e.g. Business visibility)">
                                                                </div>
                                                                <div class="col-md-1 text-end">
                                                                    <button type="button" class="btn btn-sm btn-soft-danger remove-what-you-get-btn">
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
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBossBeginnings" aria-expanded="false" aria-controls="collapseBossBeginnings">
                                <i class="ri-lightbulb-line me-2"></i> Boss Beginnings Section
                            </button>
                        </h2>
                        <div id="collapseBossBeginnings" class="accordion-collapse collapse" aria-labelledby="headingBossBeginnings" data-bs-parent="#cmsAccordion">
                            <div class="accordion-body">
                                <form id="bossBeginningsForm" enctype="multipart/form-data">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Title</label>
                                            <input type="text" name="title" class="form-control" value="{{ $bossBeginnings?->title }}" placeholder="e.g. Boss Beginnings">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Subtitle</label>
                                            <input type="text" name="sub_title" class="form-control" value="{{ $bossBeginnings?->sub_title }}" placeholder="e.g. A Business Shower">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Description</label>
                                            <textarea name="description" class="form-control" rows="4" placeholder="Enter section description">{{ $bossBeginnings?->description }}</textarea>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Main Image</label>
                                            <input type="file" name="image_file" class="form-control" accept="image/*">
                                            @if($bossBeginnings?->image)
                                                <div class="mt-2">
                                                    <img src="{{ asset('storage/' . $bossBeginnings->image) }}" alt="Boss Beginnings" class="rounded border" style="max-height: 200px; width: auto;">
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
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSpotlight" aria-expanded="false" aria-controls="collapseSpotlight">
                                <i class="ri-star-line me-2"></i> Success Stories Spotlight
                            </button>
                        </h2>
                        <div id="collapseSpotlight" class="accordion-collapse collapse" aria-labelledby="headingSpotlight" data-bs-parent="#cmsAccordion">
                            <div class="accordion-body">
                                <form id="spotlightForm">
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Section Title</label>
                                            <input type="text" name="title" class="form-control" value="{{ $spotlight?->title }}" placeholder="e.g. Celebrating Local Success Stories">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Section Subtitle</label>
                                            <textarea name="sub_title" class="form-control" rows="3" placeholder="Enter section subtitle">{{ $spotlight?->sub_title }}</textarea>
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
                    {{-- Highlights Section --}}
                    @php $highlights = $cmsData->get('highlights'); @endphp
                    <div class="accordion-item card mb-3">
                        <h2 class="accordion-header" id="headingHighlights">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHighlights" aria-expanded="false" aria-controls="collapseHighlights">
                                <i class="ri-calendar-event-line me-2"></i> Past Six Months Highlights
                            </button>
                        </h2>
                        <div id="collapseHighlights" class="accordion-collapse collapse" aria-labelledby="headingHighlights" data-bs-parent="#cmsAccordion">
                            <div class="accordion-body">
                                <form id="highlightsForm">
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Section Title</label>
                                            <input type="text" name="title" class="form-control" value="{{ $highlights?->title }}" placeholder="e.g. Past Six Months Highlights">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Section Subtitle</label>
                                            <input type="text" name="sub_title" class="form-control" value="{{ $highlights?->sub_title }}" placeholder="e.g. Celebrating our community's achievements...">
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
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

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
    
    $('#addPartnerBtn').on('click', function() {
        const $tbody = $('#partnersTable tbody');
        $tbody.find('.empty-row').remove();
        
        const row = `
            <tr>
                <td>
                    <input type="file" name="partners[${partnerCount}][image_file]" class="form-control form-control-sm" accept="image/*">
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

    $(document).on('click', '.remove-partner-btn', function() {
        $(this).closest('tr').remove();
        if ($('#partnersTable tbody tr').length === 0) {
            $('#partnersTable tbody').append('<tr class="empty-row"><td colspan="3" class="text-center text-muted">No partners added yet.</td></tr>');
        }
    });

    $('#partnersForm').on('submit', function(e) {
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
    $('#featuresForm').on('submit', function(e) {
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
    
    $('#addWhyChooseBtn').on('click', function() {
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

    $(document).on('click', '.remove-why-choose-btn', function() {
        $(this).closest('.why-choose-item').remove();
        if ($('#whyChooseContainer .why-choose-item').length === 0) {
            $('#whyChooseContainer').append('<div class="text-center text-muted py-3 empty-msg">No cards added yet.</div>');
        }
    });

    $('#whyChooseForm').on('submit', function(e) {
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
    
    $('#addCoreValueBtn').on('click', function() {
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

    $(document).on('click', '.remove-core-value-btn', function() {
        $(this).closest('.core-value-item').remove();
        if ($('#coreValuesContainer .core-value-item').length === 0) {
            $('#coreValuesContainer').append('<div class="text-center text-muted py-3 core-value-empty">No values added yet.</div>');
        }
    });

    $('#coreValuesForm').on('submit', function(e) {
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
    
    $('#addWhatYouGetBtn').on('click', function() {
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

    $(document).on('click', '.remove-what-you-get-btn', function() {
        $(this).closest('.what-you-get-item').remove();
        if ($('#whatYouGetContainer .what-you-get-item').length === 0) {
            $('#whatYouGetContainer').append('<div class="text-center text-muted py-3 what-you-get-empty">No cards added yet.</div>');
        }
    });

    $('#whatYouGetForm').on('submit', function(e) {
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
    $('#bossBeginningsForm').on('submit', function(e) {
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

    // Spotlight Logic
    $('#spotlightForm').on('submit', function(e) {
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
    $('#highlightsForm').on('submit', function(e) {
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
});
</script>
@endpush
