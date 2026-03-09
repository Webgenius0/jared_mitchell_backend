@props(['status'])

@if($status === 'active')
    <span class="badge bg-success-subtle text-success">Active</span>
@else
    <span class="badge bg-danger-subtle text-danger">Inactive</span>
@endif
