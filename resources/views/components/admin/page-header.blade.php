@props(['title', 'subtitle' => null])

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">{{ $title }}</h4>
            <div class="page-title-right">
                @if($subtitle)
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                        @if(isset($breadcrumbs))
                            {{ $breadcrumbs }}
                        @endif
                        <li class="breadcrumb-item active">{{ $title }}</li>
                    </ol>
                @endif
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
