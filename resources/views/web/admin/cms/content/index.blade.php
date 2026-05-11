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
});
</script>
@endpush
