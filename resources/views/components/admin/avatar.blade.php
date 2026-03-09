@props(['src' => null, 'name' => '', 'size' => 'sm'])

@php
    $dimensions = match($size) {
        'xs' => 'avatar-xs',
        'sm' => 'avatar-sm',
        'md' => 'avatar-md',
        'lg' => 'avatar-lg',
        'xl' => 'avatar-xl',
        default => 'avatar-sm',
    };
    $initials = collect(explode(' ', $name))->map(fn($w) => strtoupper(mb_substr($w, 0, 1)))->take(2)->join('');
    $colors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary'];
    $color = $colors[ord(mb_substr($name, 0, 1) ?: 'A') % count($colors)];
@endphp

<div class="{{ $dimensions }}">
    @if($src)
        <img src="{{ asset('storage/' . $src) }}" alt="{{ $name }}" class="rounded-circle img-thumbnail {{ $dimensions }}">
    @else
        <div class="avatar-title rounded-circle bg-{{ $color }}-subtle text-{{ $color }}">
            {{ $initials ?: '?' }}
        </div>
    @endif
</div>
