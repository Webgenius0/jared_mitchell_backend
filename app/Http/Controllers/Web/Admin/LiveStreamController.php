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
        $streams = LiveStream::latest()->get();
        return view('admin.live_streams.index', compact('streams'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.live_streams.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Create an IVS channel
        // Ensure channel name is valid for AWS (alphanumeric, hyphens, no spaces)
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
            'status' => 'pending',
        ]);

        return redirect()->route('live-streams.show', $stream->id)->with('success', 'Live stream created successfully. You can now connect OBS.');
    }

    /**
     * Show the Web Broadcast page for the stream.
     */
    public function broadcast($id)
    {
        $stream = LiveStream::findOrFail($id);
        return view('admin.live_streams.broadcast', compact('stream'));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $stream = LiveStream::findOrFail($id);
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
     * Mark the stream as ended.
     */
    public function endStream(Request $request, $id)
    {
        $stream = LiveStream::findOrFail($id);

        if ($stream->status !== 'ended') {
            // Delete the channel from AWS IVS to stop billing for it
            $deleted = $this->ivsService->deleteChannel($stream->channel_arn);
            
            if ($deleted) {
                $stream->update(['status' => 'ended']);
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Stream has been ended and the AWS channel was deleted.',
                        'data' => $stream,
                    ]);
                }
                return back()->with('success', 'Stream has been ended and the AWS channel was deleted. The video will be saved as VOD if recording was enabled.');
            } else {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Failed to delete AWS IVS Channel.',
                    ], 500);
                }
                return back()->with('error', 'Failed to delete AWS IVS Channel.');
            }
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
