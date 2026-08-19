<?php

namespace App\Http\Controllers\Web\Admin;

use App\Helpers\FileHandle;
use App\Http\Controllers\Controller;
use App\Models\VideoChannel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class VideoChannelController extends Controller
{
    /**
     * Display the video channels management page.
     */
    public function index(): View
    {
        $categories = VideoChannel::CATEGORIES;

        // Retrieve all video channel items ordered by 'order' asc
        $allVideos = VideoChannel::where('is_active', true)
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Group videos by category key
        $videosByCategory = [];
        foreach ($categories as $key => $label) {
            $videosByCategory[$key] = $allVideos->where('category', $key)->values();
        }

        return view('web.admin.video-channels.index', compact('categories', 'videosByCategory'));
    }

    /**
     * Store multiple video files under a specific category.
     */
    public function store(Request $request)
    {
        $validCategories = array_keys(VideoChannel::CATEGORIES);

        $request->validate([
            'category' => ['required', 'string', 'in:' . implode(',', $validCategories)],
            'videos'   => ['required', 'array', 'min:1'],
            'videos.*' => ['required', 'file', 'mimes:mp4,mov,avi,wmv,webm,m4v,3gp,flv', 'max:512000'], // max 500MB per video file
        ], [
            'category.required' => 'Please select a valid video category.',
            'videos.required'   => 'Please upload at least one video file.',
            'videos.*.mimes'    => 'Uploaded file must be a valid video format (MP4, MOV, AVI, WMV, WEBM, M4V, 3GP, FLV).',
            'videos.*.max'      => 'Video file size must not exceed 500MB.',
        ]);

        $category = $request->input('category');
        $uploadedCount = 0;

        DB::transaction(function () use ($request, $category, &$uploadedCount) {
            $maxOrder = VideoChannel::where('category', $category)->max('order') ?? 0;

            foreach ($request->file('videos') as $file) {
                if (!$file || !$file->isValid()) {
                    continue;
                }

                $maxOrder++;
                $videoPath = FileHandle::fileUpload($file, 'video-channels');

                if ($videoPath) {
                    VideoChannel::create([
                        'category'   => $category,
                        'video_path' => $videoPath,
                        'order'      => $maxOrder,
                        'is_active'  => true,
                    ]);
                    $uploadedCount++;
                }
            }
        });

        $message = "Successfully uploaded {$uploadedCount} video(s) to " . (VideoChannel::CATEGORIES[$category] ?? 'category') . ".";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()->route('admin.video-channels.index')->with('success', $message);
    }

    /**
     * Delete an individual video.
     */
    public function destroy(Request $request, VideoChannel $videoChannel)
    {
        if ($videoChannel->video_path) {
            $relativeStoragePath = str_replace('storage/', '', $videoChannel->video_path);
            if (Storage::disk('public')->exists($relativeStoragePath)) {
                Storage::disk('public')->delete($relativeStoragePath);
            }
        }

        $videoChannel->delete();

        $message = 'Video deleted successfully.';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Update video display order for a category via AJAX.
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'category'    => ['required', 'string'],
            'order_ids'   => ['required', 'array'],
            'order_ids.*' => ['integer', 'exists:video_channels,id'],
        ]);

        $orderIds = $request->input('order_ids');

        foreach ($orderIds as $index => $id) {
            VideoChannel::where('id', $id)->update(['order' => $index + 1]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Video order updated successfully.',
        ]);
    }
}
