<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LiveStream;
use Illuminate\Support\Facades\Log;

class AwsIvsWebhookController extends Controller
{
    /**
     * Handle incoming AWS IVS EventBridge / SNS / S3 recording notification webhooks
     */
    public function handle(Request $request)
    {
        try {
            $rawContent = $request->getContent();
            $payload = json_decode($rawContent, true);

            if (!is_array($payload)) {
                $payload = $request->all();
            }

            Log::info('AWS IVS Webhook received', $payload);

            // Handle SNS notification wrapper if sent via AWS SNS
            if (isset($payload['Type']) && $payload['Type'] === 'SubscriptionConfirmation') {
                if (isset($payload['SubscribeURL'])) {
                    @file_get_contents($payload['SubscribeURL']);
                    Log::info('AWS SNS Subscription Confirmed: ' . $payload['SubscribeURL']);
                    return response()->json(['message' => 'Subscription confirmed']);
                }
            }

            if (isset($payload['Message']) && is_string($payload['Message'])) {
                $payload = json_decode($payload['Message'], true) ?? $payload;
            }

            $channelArn = $payload['detail']['channel_arn'] ?? $payload['channel_arn'] ?? ($payload['resources'][0] ?? null);
            $recordingStatus = $payload['detail']['recording_status'] ?? $payload['recording_status'] ?? null;
            $streamId = $payload['detail']['stream_id'] ?? $payload['stream_id'] ?? null;
            
            $s3Bucket = $payload['detail']['recording_s3_bucket_name'] 
                ?? $payload['recording_s3_bucket_name'] 
                ?? ($payload['Records'][0]['s3']['bucket']['name'] ?? null)
                ?? env('AWS_IVS_S3_BUCKET') 
                ?? 'oursocialimage-livestreaming-bucket';

            $s3KeyPrefix = $payload['detail']['recording_s3_key_prefix'] 
                ?? $payload['detail']['recording_s3_key'] 
                ?? $payload['recording_s3_key_prefix'] 
                ?? ($payload['Records'][0]['s3']['object']['key'] ?? null);
            
            $vodUrl = $payload['vod_url'] ?? $payload['detail']['vod_url'] ?? null;

            if (!$vodUrl && $s3KeyPrefix) {
                $region = env('AWS_DEFAULT_REGION', 'us-east-1');
                
                if (str_ends_with($s3KeyPrefix, '.m3u8')) {
                    $masterPlaylistPath = ltrim($s3KeyPrefix, '/');
                } else {
                    $masterPlaylistPath = rtrim($s3KeyPrefix, '/') . '/media/hls/master.m3u8';
                }
                
                $cloudfront = env('AWS_IVS_CLOUDFRONT_URL');
                if ($cloudfront) {
                    $vodUrl = rtrim($cloudfront, '/') . '/' . ltrim($masterPlaylistPath, '/');
                } else {
                    $vodUrl = "https://{$s3Bucket}.s3.{$region}.amazonaws.com/" . ltrim($masterPlaylistPath, '/');
                }
            }

            $liveStream = null;

            if ($channelArn) {
                $channelId = basename($channelArn);
                $liveStream = LiveStream::where('channel_arn', $channelArn)
                    ->orWhere('channel_arn', 'LIKE', '%' . $channelId)
                    ->first();
            }

            if (!$liveStream) {
                // Fallback 1: match the latest active or pending stream
                $liveStream = LiveStream::whereIn('status', ['live', 'pending'])->latest()->first();
            }

            if (!$liveStream) {
                // Fallback 2: match the latest created stream overall (for test payloads or manual requests)
                $liveStream = LiveStream::latest()->first();
            }

            if ($liveStream) {
                $updateData = [];
                if ($vodUrl) {
                    $updateData['vod_url'] = $vodUrl;
                }
                // Only update status to ended if currently live or pending, or if VOD URL is provided
                if (in_array($liveStream->status, ['live', 'pending']) || $vodUrl) {
                    $updateData['status'] = 'ended';
                }

                if (!empty($updateData)) {
                    $liveStream->update($updateData);
                }

                Log::info("Live stream {$liveStream->id} updated via AWS IVS Webhook", $updateData);

                return response()->json([
                    'status' => true,
                    'message' => 'Live stream updated successfully',
                    'data' => $liveStream,
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'No live streams exist in database to update',
            ], 404);

        } catch (\Exception $e) {
            Log::error('AWS IVS Webhook Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
