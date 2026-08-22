@extends('layout.master-layout')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Create Live Stream</h4>
                    <div class="page-title-right">
                        <a href="{{ route('live-streams.index') }}" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('live-streams.store') }}" method="POST">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="title" class="form-label">Stream Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" required value="{{ old('title') }}">
                                @error('title') 
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="tag_type" class="form-label">Category Tag</label>
                                <select name="tag_type" id="tag_type" class="form-select" onchange="toggleTagSelectors()">
                                    <option value="boss_beginning" {{ old('tag_type', 'boss_beginning') == 'boss_beginning' ? 'selected' : '' }}>Boss Beginning Stream</option>
                                    <option value="event" {{ old('tag_type') == 'event' ? 'selected' : '' }}>Event Stream</option>
                                    <option value="artist" {{ old('tag_type') == 'artist' ? 'selected' : '' }}>Artist Spotlight Stream</option>
                                    <option value="business" {{ old('tag_type') == 'business' ? 'selected' : '' }}>Business Spotlight Stream</option>
                                </select>
                            </div>

                            <div class="mb-3 d-none" id="selector-boss_beginning">
                                <label for="season_id" class="form-label">Related Boss Beginning Season (Optional)</label>
                                <select name="season_id" id="season_id" class="form-select">
                                    <option value="">Select Season</option>
                                    @foreach($seasons as $season)
                                        <option value="{{ $season->id }}">{{ $season->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3 d-none" id="selector-event">
                                <label for="event_id" class="form-label">Related Event</label>
                                <select name="event_id" id="event_id" class="form-select">
                                    <option value="">Select Event</option>
                                    @foreach($events as $event)
                                        <option value="{{ $event->id }}">{{ $event->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3 d-none" id="selector-artist">
                                <label for="artist_spotlight_id" class="form-label">Related Artist Spotlight</label>
                                <select name="artist_spotlight_id" id="artist_spotlight_id" class="form-select">
                                    <option value="">Select Artist</option>
                                    @foreach($artistSpotlights as $artist)
                                        <option value="{{ $artist->id }}">{{ $artist->full_legal_name ?? $artist->artist_name }} ({{ $artist->email }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3 d-none" id="selector-business">
                                <label for="business_spotlight_id" class="form-label">Related Business Spotlight</label>
                                <select name="business_spotlight_id" id="business_spotlight_id" class="form-select">
                                    <option value="">Select Business</option>
                                    @foreach($businessSpotlights as $business)
                                        <option value="{{ $business->id }}">{{ $business->business_name }} ({{ $business->email }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="description" class="form-label">Description</label>
                                <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                                @error('description') 
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary">
                                Generate Stream (AWS IVS)
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleTagSelectors() {
        const type = document.getElementById('tag_type').value;
        document.getElementById('selector-boss_beginning').classList.add('d-none');
        document.getElementById('selector-event').classList.add('d-none');
        document.getElementById('selector-artist').classList.add('d-none');
        document.getElementById('selector-business').classList.add('d-none');

        if (type === 'boss_beginning') document.getElementById('selector-boss_beginning').classList.remove('d-none');
        if (type === 'event') document.getElementById('selector-event').classList.remove('d-none');
        if (type === 'artist') document.getElementById('selector-artist').classList.remove('d-none');
        if (type === 'business') document.getElementById('selector-business').classList.remove('d-none');
    }
    document.addEventListener('DOMContentLoaded', toggleTagSelectors);
</script>
@endpush
