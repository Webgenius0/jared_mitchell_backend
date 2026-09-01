@extends('layout.master-layout')

@section('title', 'Custom Email Templates Studio')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title & Action -->
        <div class="row mb-3 align-items-center">
            <div class="col-md-6">
                <h4 class="mb-1 d-flex align-items-center gap-2">
                    <i class="ri-layout-3-line text-primary fs-3"></i> Custom Email Templates Studio
                </h4>
                <p class="text-muted mb-0">Manage and design custom drag-and-drop HTML email templates for your broadcasts.</p>
            </div>
            <div class="col-md-6 text-md-end mt-2 mt-md-0 d-flex gap-2 justify-content-md-end flex-wrap">
                <a href="{{ route('admin.email-templates.create') }}" class="btn btn-primary bg-gradient fw-bold shadow">
                    <i class="ri-layout-grid-line me-1 align-middle"></i> 🎨 Visual Drag & Drop Builder
                </a>
                <a href="{{ route('admin.email-templates.canva') }}" class="btn btn-warning bg-gradient fw-bold text-dark shadow">
                    <i class="ri-code-box-line me-1 align-middle"></i> 📄 Import Canva HTML Template
                </a>
            </div>
        </div>

        <!-- Template Cards Grid -->
        <div class="row g-4 mt-1">
            @forelse($templates as $template)
            <div class="col-md-6 col-lg-4">
                <div class="card card-animate border-0 shadow-sm h-100">
                    <div class="card-header bg-light d-flex align-items-center justify-content-between">
                        <span class="badge bg-primary-subtle text-primary text-uppercase fw-bold">{{ $template->category }}</span>
                        <small class="text-muted"><i class="ri-time-line me-1"></i> {{ $template->updated_at->diffForHumans() }}</small>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-dark mb-2">{{ $template->name }}</h5>
                        <p class="card-text text-muted small mb-3">{{ Str::limit($template->description ?? 'Custom designed drag & drop newsletter template.', 90) }}</p>
                        
                        <!-- Preview Box Wrapper -->
                        <div class="border rounded bg-white p-2 text-center mb-3" style="max-height: 140px; overflow: hidden; opacity: 0.85;">
                            <iframe src="{{ route('admin.email-templates.preview', $template->id) }}" style="width: 100%; height: 200px; border: none; transform: scale(0.65); transform-origin: 0 0; pointer-events: none;"></iframe>
                        </div>
                    </div>
                    <div class="card-footer bg-light d-flex align-items-center justify-content-between">
                        <button type="button" class="btn btn-sm btn-soft-info btn-preview-modal" data-url="{{ route('admin.email-templates.preview', $template->id) }}" data-name="{{ $template->name }}">
                            <i class="ri-eye-line me-1"></i> Preview
                        </button>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.email-templates.edit', $template->id) }}" class="btn btn-sm btn-soft-warning" title="Edit in Visual Builder">
                                <i class="ri-edit-box-line me-1"></i> Edit
                            </a>
                            <form action="{{ route('admin.email-templates.destroy', $template->id) }}" method="POST" class="delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-soft-danger delete-btn" title="Delete Template">
                                    <i class="ri-delete-bin-fill"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm text-center py-5">
                    <div class="card-body py-5">
                        <div class="avatar-lg mx-auto mb-3">
                            <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-1">
                                <i class="ri-layout-3-line"></i>
                            </span>
                        </div>
                        <h4 class="fw-bold">No Custom Templates Created Yet</h4>
                        <p class="text-muted mb-4">Click below to open the Visual Drag & Drop Builder and create your first custom layout!</p>
                        <a href="{{ route('admin.email-templates.create') }}" class="btn btn-primary bg-gradient fw-bold px-4 shadow">
                            <i class="ri-magic-line me-1"></i> ✨ Open Drag & Drop Builder
                        </a>
                    </div>
                </div>
            </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $templates->links() }}
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="templatePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title text-white" id="modalTemplateName">Template Preview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="previewIframe" src="" style="width: 100%; height: 500px; border: none;"></iframe>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.btn-preview-modal').on('click', function() {
            const url = $(this).data('url');
            const name = $(this).data('name');
            $('#modalTemplateName').text(name);
            $('#previewIframe').attr('src', url);
            $('#templatePreviewModal').modal('show');
        });

        $('.delete-btn').on('click', function(e) {
            e.preventDefault();
            const form = $(this).closest('form');
            Alert.confirm('Are you sure you want to delete this custom template?', {
                title: 'Delete Template?',
                type: 'danger',
                confirmText: 'Yes, delete it'
            }).then(confirmed => {
                if (confirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
