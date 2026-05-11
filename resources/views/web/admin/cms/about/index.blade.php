@extends('layout.master-layout')
@section('title', 'About CMS Content')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Manage About CMS Content</h4>
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">About CMS Content</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3">
                @include('web.admin.cms.partials._sidebar')
            </div>

            <div class="col-lg-9">
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
                                                    <img src="{{ asset('storage/' . $hero->bg) }}" alt="Hero Background" class="rounded border" style="max-height: 200px; width: auto;">
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
                                                    <img src="{{ asset('storage/' . $society->image) }}" alt="Society Image" class="rounded border" style="max-height: 200px; width: auto;">
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
                                                    <img src="{{ asset('storage/' . $origin->image) }}" alt="Origin Image" class="rounded border" style="max-height: 200px; width: auto;">
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
                                                                            <img src="{{ asset('storage/' . $item['image']) }}" alt="Icon" class="rounded border" style="height: 50px;">
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
});
</script>
@endpush
