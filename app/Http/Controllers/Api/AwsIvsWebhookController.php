<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LiveStream;
use Illuminate\Support\Facades\Cache;
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

            $eventName = $payload['detail']['event_name'] 
                ?? $payload['event_name'] 
                ?? ($payload['Records'][0]['eventName'] ?? null);

            $channelArn = $payload['detail']['channel_arn'] ?? $payload['channel_arn'] ?? ($payload['resources'][0] ?? null);
            $recordingStatus = $payload['detail']['recording_status'] ?? $payload['recording_status'] ?? null;
            
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

            // Find matching stream
            $liveStream = null;

            if ($channelArn) {
                $channelId = basename($channelArn);
                $liveStream = LiveStream::where('channel_arn', $channelArn)
                    ->orWhere('channel_arn', 'LIKE', '%' . $channelId)
                    ->first();
            }

            if (!$liveStream) {
                // Fallback: match the latest active or pending stream
                $liveStream = LiveStream::whereIn('status', ['live', 'pending'])->latest()->first();
            }

            if (!$liveStream) {
                return response()->json([
                    'status' => false,
                    'message' => 'No matching live stream found for channel ARN',
                ], 404);
            }

            // Store real AWS S3 VOD URL in Cache for retrieval upon stream end
            if ($vodUrl) {
                Cache::put("stream_{$liveStream->id}_recorded_vod_url", $vodUrl, 86400);
                Log::info("AWS Webhook cached recorded VOD URL for stream {$liveStream->id}: {$vodUrl}");
            }

            // Determine Event Intent
            $isStreamStartEvent = in_array($eventName, ['Stream Start', 'Recording Start', 'SESSION_CREATED'])
                || ($recordingStatus === 'RECORDING_STARTED');

            $isStreamEndEvent = in_array($eventName, ['Stream End', 'Recording End', 'SESSION_ENDED', 'Stream Failure'])
                || ($recordingStatus === 'RECORDING_ENDED' || $recordingStatus === 'RECORDING_FAILED');

            // 1. IF STREAM IS STARTING OR RECORDING IS STARTING -> ENSURE STATUS IS LIVE!
            if ($isStreamStartEvent) {
                $liveStream->update(['status' => 'live']);
                Log::info("Live stream {$liveStream->id} marked as LIVE via AWS Webhook ({$eventName})");
                return response()->json([
                    'status' => true,
                    'message' => 'Live stream status updated to live',
                    'data' => $liveStream,
                ]);
            }

            // 2. ONLY IF STREAM HAS TRULY ENDED OR FAILED VIA AWS -> UPDATE STATUS TO ENDED!
            if ($isStreamEndEvent) {
                $updateData = ['status' => 'ended'];
                $finalVodUrl = $vodUrl ?? Cache::get("stream_{$liveStream->id}_recorded_vod_url");
                if ($finalVodUrl) {
                    $updateData['vod_url'] = $finalVodUrl;
                }
                $liveStream->update($updateData);

                Log::info("Live stream {$liveStream->id} ended via AWS IVS Webhook ({$eventName})", $updateData);

                return response()->json([
                    'status' => true,
                    'message' => 'Live stream ended successfully',
                    'data' => $liveStream,
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Webhook received and logged',
            ]);

        } catch (\Exception $e) {
            Log::error('AWS IVS Webhook Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
