<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BusinessMediaController extends Controller
{
    /**
     * GET /media/business/{media}?signature=...&expires=...
     *
     * Serve a private business media file via a temporary signed URL.
     * The route must be registered with ->name('business.media.serve').
     */
    public function serve(Request $request, BusinessMedia $media): StreamedResponse
    {
        // Validate the signed URL
        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid or expired media URL.');
        }

        if (! Storage::disk('local')->exists($media->file_path)) {
            abort(404, 'Media file not found.');
        }

        return Storage::disk('local')->download(
            $media->file_path,
            $media->file_name ?? basename($media->file_path),
            ['Content-Type' => $media->mime_type ?? 'application/octet-stream']
        );
    }
}
