<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LiveStream;
use App\Services\AwsIvsService;
use Illuminate\Support\Facades\Cache;

class LiveStreamController extends Controller
{

    // index
    public function index(Request $request)
    {
        try {
            $query = LiveStream::latest();

            if ($request->has('status')) {
                $query->where('status', $request->query('status'));
            }

            $liveStreams = $query->get();
            return response()->json([
                'status' => true,
                'message' => "Live streams fetched successfully",
                'data' => $liveStreams,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Failed to fetch live streams",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // active - Get currently live stream for frontend integration
    public function active()
    {
        try {
            $liveStream = LiveStream::where('status', 'live')->latest()->first();

            if (!$liveStream) {
                return response()->json([
                    'status' => true,
                    'is_live' => false,
                    'message' => "No live stream currently active",
                    'data' => null,
                ]);
            }

            return response()->json([
                'status' => true,
                'is_live' => true,
                'message' => "Active live stream fetched successfully",
                'data' => $liveStream,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Failed to fetch active live stream",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // heartbeat - Frontend player sends ping every 10-15 seconds while watching
    public function heartbeat(Request $request, $id)
    {
        try {
            $liveStream = LiveStream::find($id);
            if (!$liveStream) {
                return response()->json([
                    'status' => false,
                    'message' => "Live stream not found",
                ], 404);
            }

            $viewerKey = $request->input('viewer_id') ?? $request->ip();
            $activeViewersListKey = "live_stream_{$id}_active_viewers";
            $activeViewers = Cache::get($activeViewersListKey, []);
            $activeViewers[md5($viewerKey)] = now()->timestamp;

            $currentTime = now()->timestamp;
            $activeViewers = array_filter($activeViewers, function ($timestamp) use ($currentTime) {
                return ($currentTime - $timestamp) <= 30;
            });

            Cache::put($activeViewersListKey, $activeViewers, 300);

            return response()->json([
                'status' => true,
                'message' => "Heartbeat received",
                'viewer_count' => count($activeViewers),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Failed to record heartbeat",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // viewers - Return live viewer count
    public function viewers($id, AwsIvsService $ivsService)
    {
        try {
            $liveStream = LiveStream::find($id);
            if (!$liveStream) {
                return response()->json([
                    'status' => false,
                    'message' => "Live stream not found",
                ], 404);
            }

            $activeViewersListKey = "live_stream_{$id}_active_viewers";
            $activeViewers = Cache::get($activeViewersListKey, []);
            $currentTime = now()->timestamp;

            $activeViewers = array_filter($activeViewers, function ($timestamp) use ($currentTime) {
                return ($currentTime - $timestamp) <= 30;
            });

            $heartbeatCount = count($activeViewers);
            $awsViewerCount = 0;

            if ($liveStream->channel_arn) {
                $ivsDetail = $ivsService->getStreamDetail($liveStream->channel_arn);
                if ($ivsDetail && isset($ivsDetail['viewer_count'])) {
                    $awsViewerCount = (int) $ivsDetail['viewer_count'];
                }
            }

            $totalViewers = max($awsViewerCount, $heartbeatCount);

            return response()->json([
                'status' => true,
                'message' => "Viewer count retrieved successfully",
                'viewer_count' => $totalViewers,
                'aws_viewer_count' => $awsViewerCount,
                'heartbeat_viewer_count' => $heartbeatCount,
                'stream_status' => $liveStream->status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Failed to fetch viewer count",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // show
    public function show($id)
    {
        try {
            $liveStream = LiveStream::find($id);
            if (!$liveStream) {
                return response()->json([
                    'status' => false,
                    'message' => "Live stream not found",
                ], 404);
            }
            return response()->json([
                'status' => true,
                'message' => "Live stream fetched successfully",
                'data' => $liveStream,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Failed to fetch live stream",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // store
    public function store(Request $request)
    {
        try {
            $liveStream = LiveStream::create($request->all());
            return response()->json([
                'status' => true,
                'message' => "Live stream created successfully",
                'data' => $liveStream,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Failed to create live stream",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // update
    public function update(Request $request, $id)
    {
        try {
            $liveStream = LiveStream::find($id);
            if (!$liveStream) {
                return response()->json([
                    'status' => false,
                    'message' => "Live stream not found",
                ], 404);
            }
            $liveStream->update($request->all());
            return response()->json([
                'status' => true,
                'message' => "Live stream updated successfully",
                'data' => $liveStream,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Failed to update live stream",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // destroy
    public function destroy($id)
    {
        try {
            $liveStream = LiveStream::find($id);
            if (!$liveStream) {
                return response()->json([
                    'status' => false,
                    'message' => "Live stream not found",
                ], 404);
            }
            $liveStream->delete();
            return response()->json([
                'status' => true,
                'message' => "Live stream deleted successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Failed to delete live stream",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // start
    public function start($id)
    {
        try {
            $liveStream = LiveStream::find($id);
            if (!$liveStream) {
                return response()->json([
                    'status' => false,
                    'message' => "Live stream not found",
                ], 404);
            }
            $liveStream->update([
                'status' => 'live',
            ]);
            return response()->json([
                'status' => true,
                'message' => "Live stream started successfully",
                'data' => $liveStream,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Failed to start live stream",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // stop
    public function stop($id)
    {
        try {
            $liveStream = LiveStream::find($id);
            if (!$liveStream) {
                return response()->json([
                    'status' => false,
                    'message' => "Live stream not found",
                ], 404);
            }
            $liveStream->update([
                'status' => 'ended',
            ]);
            return response()->json([
                'status' => true,
                'message' => "Live stream stopped successfully",
                'data' => $liveStream,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Failed to stop live stream",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // pause
    public function pause($id)
    {
        try {
            $liveStream = LiveStream::find($id);
            if (!$liveStream) {
                return response()->json([
                    'status' => false,
                    'message' => "Live stream not found",
                ], 404);
            }
            $liveStream->update([
                'status' => 'pending',
            ]);
            return response()->json([
                'status' => true,
                'message' => "Live stream paused successfully",
                'data' => $liveStream,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Failed to pause live stream",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // resume
    public function resume($id)
    {
        try {
            $liveStream = LiveStream::find($id);
            if (!$liveStream) {
                return response()->json([
                    'status' => false,
                    'message' => "Live stream not found",
                ], 404);
            }
            $liveStream->update([
                'status' => 'live',
            ]);
            return response()->json([
                'status' => true,
                'message' => "Live stream resumed successfully",
                'data' => $liveStream,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Failed to resume live stream",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // restart
    public function restart($id)
    {
        try {
            $liveStream = LiveStream::find($id);
            if (!$liveStream) {
                return response()->json([
                    'status' => false,
                    'message' => "Live stream not found",
                ], 404);
            }
            $liveStream->update([
                'status' => 'live',
            ]);
            return response()->json([
                'status' => true,
                'message' => "Live stream restarted successfully",
                'data' => $liveStream,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Failed to restart live stream",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // end
    public function end($id)
    {
        try {
            $liveStream = LiveStream::find($id);
            if (!$liveStream) {
                return response()->json([
                    'status' => false,
                    'message' => "Live stream not found",
                ], 404);
            }
            $liveStream->update([
                'status' => 'ended',
            ]);
            return response()->json([
                'status' => true,
                'message' => "Live stream ended successfully",
                'data' => $liveStream,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Failed to end live stream",
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
            
