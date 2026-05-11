<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Pages</h5>
    </div>
    <div class="card-body p-2">
        <div class="list-group list-group-flush">
            @foreach ($pages as $page)
                <a href="{{ route('admin.cms.content.index', ['page' => $page->value]) }}"
                    class="list-group-item list-group-item-action {{ $currentPage === $page->value ? 'active' : '' }}">
                    <div class="d-flex align-items-center">
                        <i class="ri-pages-line me-2"></i>
                        <span>{{ ucfirst($page->value) }} Page</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</div>
