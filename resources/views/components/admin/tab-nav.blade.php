@props(['tabs', 'active' => null])

<ul class="nav nav-tabs nav-tabs-custom nav-primary mb-3" role="tablist">
    @foreach($tabs as $key => $label)
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ ($active ?? array_key_first($tabs)) === $key ? 'active' : '' }}"
               data-bs-toggle="tab"
               href="#tab-{{ $key }}"
               role="tab"
               data-tab-key="{{ $key }}">
                {{ $label }}
            </a>
        </li>
    @endforeach
</ul>
