@props(['role'])

@php
    $colors = [
        'super-admin' => 'danger',
        'admin'       => 'primary',
        'manager'     => 'warning',
        'user'        => 'info',
    ];
    $color = $colors[$role] ?? 'secondary';
@endphp

<span class="badge bg-{{ $color }}-subtle text-{{ $color }}">{{ ucfirst($role) }}</span>
