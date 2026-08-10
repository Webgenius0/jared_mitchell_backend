@extends('layout.master-layout')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Direct Web Broadcast: {{ $stream->title }}</h4>
                    <div class="page-title-right">
                        <a href="{{ route('live-streams.show', $stream->id) }}" class="btn btn-secondary">Back to Details</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row justify-content-center">
            <div class="col-xl-9 col-lg-10">
                <div class="card">
                    <div class="card-header text-center">
                        <h5 class="card-title mb-0">
                            Status: <span id="status" class="text-secondary fw-bold">INITIALIZING...</span>
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="ratio ratio-16x9 bg-dark mb-4 mx-auto rounded shadow-sm" style="max-width: 800px;">
                            <canvas id="preview-canvas" class="w-100 h-100"></canvas>
                        </div>

                        <div class="d-flex justify-content-center gap-3">
                            <button id="btn-start" onclick="startBroadcast()" class="btn btn-success btn-lg shadow-sm" disabled>
                                <i class="ri-live-line me-2"></i> Start Live Broadcast
                            </button>
                            <button id="btn-stop" onclick="stopBroadcast()" class="btn btn-danger btn-lg shadow-sm d-none">
                                <i class="ri-stop-circle-line me-2"></i> Stop Broadcast
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<!-- Load AWS IVS Web Broadcast SDK -->
<script src="https://web-broadcast.live-video.net/1.8.0/amazon-ivs-web-broadcast.js"></script>

<script>
    let client;

    async function initBroadcastClient() {
        if (!window.IVSBroadcastClient) {
            console.error("IVS Web Broadcast SDK not loaded");
            return;
        }

        const IVSBroadcastClient = window.IVSBroadcastClient;

        // Initialize client
        // The SDK expects a valid RTMPS or HTTPS URL (e.g. 'rtmps://<ingest-server>')
        client = IVSBroadcastClient.create({
            streamConfig: IVSBroadcastClient.STANDARD_LANDSCAPE,
            // $stream->ingest_endpoint is saved as "rtmps://something:443/app/" 
            // The SDK expects "rtmps://something" or "https://something"
            ingestEndpoint: "{{ $stream->ingest_endpoint }}", 
        });

        try {
            // Request camera and microphone access
            const mediaStream = await navigator.mediaDevices.getUserMedia({
                video: true,
                audio: true
            });

            // Add Video Track
            await client.addVideoInputDevice(mediaStream, 'camera1', { index: 0 });

            // Add Audio Track
            await client.addAudioInputDevice(mediaStream, 'mic1');

            // Attach preview to canvas
            const previewCanvas = document.getElementById('preview-canvas');
            client.attachPreview(previewCanvas);

            // Ready to broadcast
            document.getElementById('status').innerText = "READY";
            document.getElementById('status').className = "text-white bg-success px-2 py-1 rounded fw-bold";
            document.getElementById('btn-start').disabled = false;

        } catch (err) {
            console.error("Failed to access camera or microphone", err);
            document.getElementById('status').innerText = "ERROR: NO CAMERA/MIC ACCESS";
            document.getElementById('status').className = "text-white bg-danger px-2 py-1 rounded fw-bold";
            alert("Could not access your camera or microphone. Please allow permissions in your browser.");
        }
    }

    function startBroadcast() {
        if (!client) return;

        client.startBroadcast("{{ $stream->stream_key }}")
            .then(() => {
                document.getElementById('status').innerText = "LIVE";
                document.getElementById('status').className = "text-success fw-bold";
                document.getElementById('btn-start').classList.add('d-none');
                document.getElementById('btn-stop').classList.remove('d-none');
            })
            .catch((err) => {
                console.error("Failed to start broadcast", err);
                alert("Failed to start broadcast. Check console for details.");
            });
    }

    function stopBroadcast() {
        if (!client) return;

        client.stopBroadcast();
        document.getElementById('status').innerText = "STOPPED";
        document.getElementById('status').className = "text-danger fw-bold";
        document.getElementById('btn-stop').classList.add('d-none');
        document.getElementById('btn-start').classList.remove('d-none');
    }

    // Initialize on page load
    window.onload = initBroadcastClient;
</script>
@endpush
