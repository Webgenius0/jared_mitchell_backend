@props([
    'showUrl' => null,
    'editUrl' => null,
    'deleteUrl' => null,
    'deletable' => true,
    'itemName' => 'item',
])

<div class="d-flex gap-2">
    @if($showUrl)
        <a href="{{ $showUrl }}" class="btn btn-sm btn-soft-info" title="View">
            <i class="ri-eye-line"></i>
        </a>
    @endif

    @if($editUrl)
        <a href="{{ $editUrl }}" class="btn btn-sm btn-soft-primary" title="Edit">
            <i class="ri-pencil-line"></i>
        </a>
    @endif

    @if($deleteUrl && $deletable)
        <button type="button" class="btn btn-sm btn-soft-danger btn-delete"
                data-url="{{ $deleteUrl }}"
                data-name="{{ $itemName }}"
                title="Delete">
            <i class="ri-delete-bin-line"></i>
        </button>
    @endif
</div>
