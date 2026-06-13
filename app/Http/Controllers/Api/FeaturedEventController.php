<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Log;

class FeaturedEventController extends Controller
{
    use ApiResponse;

    public function index()
    {
        try {
            $events = Event::where('is_featured', 1)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Featured events retrieved successfully.',
                'data' => [
                    'events' => $events,
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to retrieve featured events: ' . $e->getMessage());

            return $this->error(
                'Failed to retrieve events. Please try again later.'
            );
        }
    }
}