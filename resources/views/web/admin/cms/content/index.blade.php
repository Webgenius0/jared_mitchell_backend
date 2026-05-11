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

                    {{-- Spotlight Section (Placeholder for future) --}}
                    <div class="accordion-item card mb-3">
                        <h2 class="accordion-header" id="headingSpotlight">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSpotlight" aria-expanded="false" aria-controls="collapseSpotlight">
                                <i class="ri-star-line me-2"></i> Spotlight Section
                            </button>
                        </h2>
                        <div id="collapseSpotlight" class="accordion-collapse collapse" aria-labelledby="headingSpotlight" data-bs-parent="#cmsAccordion">
                            <div class="accordion-body">
                                <p class="text-muted text-center py-4">Spotlight section content management coming soon.</p>
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
});
</script>
@endpush
