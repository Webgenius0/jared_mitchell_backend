<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Pages</h5>
    </div>
    <div class="card-body p-2">
        <div class="list-group list-group-flush">
            @foreach ($pages as $page)
                @php
                    $url = match($page->value) {
                        'about' => route('admin.cms.about.index'),
                        default => route('admin.cms.content.index', ['page' => $page->value])
                    };
                @endphp
                <a href="{{ $url }}"
                    class="list-group-item list-group-item-action {{ $currentPage === $page->value ? 'active' : '' }}">
                    {{ ucfirst($page->value) }}
                </a>
            @endforeach
        </div>
    </div>
</div>
