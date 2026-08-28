<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\LiveStream;
use App\Services\AwsIvsService;
use Illuminate\Http\Request;

class LiveStreamController extends Controller
{
    protected $ivsService;

    public function __construct(AwsIvsService $ivsService)
    {
        $this->ivsService = $ivsService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $streams = LiveStream::with('streamable')->latest()->get();
        return view('admin.live_streams.index', compact('streams'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $events = \App\Models\Event::select('id', 'title')->latest()->get();
        $artistSpotlights = \App\Models\ArtistSpotlight::select('id', 'full_legal_name', 'email')->latest()->get();
        $businessSpotlights = \App\Models\BusinessSpotlight::select('id', 'business_name', 'email')->latest()->get();
        $seasons = \App\Models\Contest\Season::select('id', 'title')->latest()->get();

        return view('admin.live_streams.create', compact('events', 'artistSpotlights', 'businessSpotlights', 'seasons'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'tag_type' => 'nullable|in:boss_beginning,event,artist,business',
            'season_id' => 'nullable|exists:seasons,id',
            'event_id' => 'nullable|exists:events,id',
            'artist_spotlight_id' => 'nullable|exists:artist_spotlights,id',
            'business_spotlight_id' => 'nullable|exists:business_spotlights,id',
        ]);

        $tagType = $request->input('tag_type', 'boss_beginning');
        $streamableType = null;
        $streamableId = null;

        if ($tagType === 'boss_beginning' && $request->filled('season_id')) {
            $streamableType = \App\Models\Contest\Season::class;
            $streamableId = $request->input('season_id');
        } elseif ($tagType === 'event' && $request->filled('event_id')) {
            $streamableType = \App\Models\Event::class;
            $streamableId = $request->input('event_id');
        } elseif ($tagType === 'artist' && $request->filled('artist_spotlight_id')) {
            $streamableType = \App\Models\ArtistSpotlight::class;
            $streamableId = $request->input('artist_spotlight_id');
        } elseif ($tagType === 'business' && $request->filled('business_spotlight_id')) {
            $streamableType = \App\Models\BusinessSpotlight::class;
            $streamableId = $request->input('business_spotlight_id');
        }

        // Create an IVS channel
        $channelName = 'stream-' . time() . '-' . rand(100, 999);
        $ivsData = $this->ivsService->createChannel($channelName);

        if (!$ivsData) {
            return back()->with('error', 'Failed to create AWS IVS Channel. Please check your AWS credentials and try again.');
        }

        $stream = LiveStream::create([
            'title' => $request->title,
            'description' => $request->description,
            'channel_arn' => $ivsData['channel_arn'],
            'ingest_endpoint' => $ivsData['ingest_endpoint'],
            'stream_key' => $ivsData['stream_key'],
            'playback_url' => $ivsData['playback_url'],
            'tag_type' => $tagType,
            'streamable_type' => $streamableType,
            'streamable_id' => $streamableId,
            'status' => 'pending',
        ]);

        return redirect()->route('live-streams.show', $stream->id)->with('success', 'Live stream created successfully. You can now connect OBS.');
    }

    /**
     * Show the Web Broadcast page for the stream.
     */
    public function broadcast($id)
    {
        $stream = LiveStream::with('streamable')->findOrFail($id);
        return view('admin.live_streams.broadcast', compact('stream'));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $stream = LiveStream::with('streamable')->findOrFail($id);
        return view('admin.live_streams.show', compact('stream'));
    }

    /**
     * Mark the stream as live.
     */
    public function startLive(Request $request, $id)
    {
        $stream = LiveStream::findOrFail($id);
        $stream->update(['status' => 'live']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Stream is now live.',
                'data' => $stream,
            ]);
        }

        return back()->with('success', 'Stream is now marked as LIVE.');
    }

    /**
     * Mark the stream as pending (stopped broadcast).
     */
    public function stopLive(Request $request, $id)
    {
        $stream = LiveStream::findOrFail($id);
        $stream->update(['status' => 'pending']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Stream broadcast stopped and status marked as pending.',
                'data' => $stream,
            ]);
        }

        return back()->with('success', 'Stream broadcast stopped and status marked as PENDING.');
    }

    /**
     * Get live statistics (viewer count & status) for admin broadcast view
     */
    public function stats($id)
    {
        $stream = LiveStream::findOrFail($id);

        $activeViewersListKey = "live_stream_{$id}_active_viewers";
        $activeViewers = \Illuminate\Support\Facades\Cache::get($activeViewersListKey, []);
        $currentTime = now()->timestamp;

        $activeViewers = array_filter($activeViewers, function ($timestamp) use ($currentTime) {
            return ($currentTime - $timestamp) <= 30;
        });

        $heartbeatCount = count($activeViewers);
        $awsViewerCount = 0;

        if ($stream->channel_arn) {
            $ivsDetail = $this->ivsService->getStreamDetail($stream->channel_arn);
            if ($ivsDetail && isset($ivsDetail['viewer_count'])) {
                $awsViewerCount = (int) $ivsDetail['viewer_count'];
            }
        }

        $totalViewers = max($awsViewerCount, $heartbeatCount);

        return response()->json([
            'status' => true,
            'viewer_count' => $totalViewers,
            'aws_viewer_count' => $awsViewerCount,
            'heartbeat_viewer_count' => $heartbeatCount,
            'stream_status' => $stream->status,
        ]);
    }

    /**
     * Mark the stream as ended.
     */
    public function endStream(Request $request, $id)
    {
        $stream = LiveStream::findOrFail($id);

        if ($stream->status !== 'ended') {
            $vodUrl = $request->input('vod_url', $stream->vod_url);

            if (!$vodUrl && $stream->channel_arn) {
                $channelId = basename($stream->channel_arn);
                $s3Bucket = env('AWS_IVS_S3_BUCKET', 'oursocialimage-livestreaming-bucket');
                $region = env('AWS_DEFAULT_REGION', 'us-east-1');
                $cloudfront = env('AWS_IVS_CLOUDFRONT_URL');

                $masterPlaylistPath = "ivs/v1/" . env('AWS_ACCOUNT_ID', '') . "/{$channelId}/media/hls/master.m3u8";
                if ($cloudfront) {
                    $vodUrl = rtrim($cloudfront, '/') . '/' . ltrim($masterPlaylistPath, '/');
                } else {
                    $vodUrl = "https://{$s3Bucket}.s3.{$region}.amazonaws.com/" . ltrim($masterPlaylistPath, '/');
                }
            }

            // Delete the channel from AWS IVS to stop billing for it
            $deleted = $this->ivsService->deleteChannel($stream->channel_arn);
            
            $stream->update([
                'status' => 'ended',
                'vod_url' => $vodUrl,
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Stream has been ended and saved as VOD.',
                    'data' => $stream,
                ]);
            }
            return back()->with('success', 'Stream has been ended and saved as VOD.');
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Stream is already ended.',
                'data' => $stream,
            ]);
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function update(Request $request, $id)
    {
        $stream = LiveStream::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'vod_url' => 'nullable|string',
        ]);

        $stream->update([
            'title' => $request->title,
            'description' => $request->description,
            'vod_url' => $request->vod_url,
        ]);

        return back()->with('success', 'Live stream details updated successfully.');
    }

    public function destroy($id)
    {
        $stream = LiveStream::findOrFail($id);
        
        if ($stream->status !== 'ended') {
            $this->ivsService->deleteChannel($stream->channel_arn);
        }

        $stream->delete();

        return redirect()->route('admin.live-streams.index')->with('success', 'Live stream record deleted.');
    }
}
