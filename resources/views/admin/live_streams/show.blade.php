@extends('layout.master-layout')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Stream Details: {{ $stream->title }}</h4>
                    <div class="page-title-right">
                        <a href="{{ route('live-streams.index') }}" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="row mb-4">
            <div class="col-12 d-flex gap-2 align-items-center justify-content-between flex-wrap">
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    @if($stream->status !== 'ended')
                        <a href="{{ route('live-streams.broadcast', $stream->id) }}" class="btn btn-success btn-lg shadow-sm">
                            <i class="ri-vidicon-line me-2 align-middle"></i> Start Web Broadcast (Browser)
                        </a>

                        @if($stream->status !== 'live')
                            <form action="{{ route('live-streams.start-live', $stream->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                                    <i class="ri-live-line me-2 align-middle"></i> Mark Status as LIVE
                                </button>
                            </form>
                        @else
                            <span class="badge bg-success fs-6 py-2 px-3 align-middle"><i class="ri-radio-button-line me-1"></i> STATUS: LIVE</span>
                        @endif

                        <form action="{{ route('live-streams.end', $stream->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to end this stream?');">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-lg shadow-sm">
                                <i class="ri-stop-circle-line me-2 align-middle"></i> End Stream
                            </button>
                        </form>
                    @else
                        <span class="badge bg-secondary fs-6 py-2 px-3"><i class="ri-checkbox-circle-line me-1"></i> STATUS: ENDED</span>
                    @endif
                </div>

                <div class="badge bg-primary fs-6 px-3 py-2 shadow-sm">
                    <i class="ri-user-line me-1"></i> Live Viewers: <span id="viewer-count" class="fw-bold">0</span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0">OBS Connection Details</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">Use the details below in your OBS Studio (or any RTMP encoder) to start streaming.</p>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Ingest Server (URL)</label>
                            <div class="input-group">
                                <input type="text" readonly value="{{ $stream->ingest_endpoint }}" id="ingest_url" class="form-control bg-light">
                                <button onclick="copyToClipboard('ingest_url')" class="btn btn-outline-secondary" type="button">Copy</button>
                            </div>
                        </div>

                        <div>
                            <label class="form-label fw-bold">Stream Key</label>
                            <div class="input-group">
                                <input type="password" readonly value="{{ $stream->stream_key }}" id="stream_key" class="form-control bg-light">
                                <button onclick="document.getElementById('stream_key').type = 'text'" class="btn btn-outline-secondary" type="button">Show</button>
                                <button onclick="copyToClipboard('stream_key')" class="btn btn-outline-secondary" type="button">Copy</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0">Frontend Playback</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">This URL is used by the video player on the frontend to display the live stream.</p>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Playback URL (m3u8)</label>
                            <div class="input-group">
                                <input type="text" readonly value="{{ $stream->playback_url }}" id="playback_url" class="form-control bg-light">
                                <button onclick="copyToClipboard('playback_url')" class="btn btn-outline-secondary" type="button">Copy</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    function copyToClipboard(elementId) {
        var copyText = document.getElementById(elementId);
        
        if (copyText.type === 'password') {
            copyText.type = 'text';
            copyText.select();
            document.execCommand("copy");
            copyText.type = 'password';
        } else {
            copyText.select();
            document.execCommand("copy");
        }
        
        Toastify({
            text: "Copied to clipboard!",
            duration: 3000,
            close: true,
            gravity: "top", 
            position: "right", 
            backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
        }).showToast();
    }

    function fetchLiveStats() {
        fetch("{{ route('live-streams.stats', $stream->id) }}")
            .then(res => res.json())
            .then(data => {
                if (data.status) {
                    document.getElementById('viewer-count').innerText = data.viewer_count;
                }
            })
            .catch(err => console.error("Failed to fetch viewer count", err));
    }

    setInterval(fetchLiveStats, 5000);
    fetchLiveStats();
</script>
@endpush
@endsection
