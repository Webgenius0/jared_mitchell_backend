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
            Log::info('AWS IVS Webhook received', $request->all());

            $payload = $request->all();

            // Handle SNS notification wrapper if sent via AWS SNS
            if (isset($payload['Type']) && $payload['Type'] === 'SubscriptionConfirmation') {
                // Confirm SNS subscription if AWS sends confirmation URL
                if (isset($payload['SubscribeURL'])) {
                    file_get_contents($payload['SubscribeURL']);
                    return response()->json(['message' => 'Subscription confirmed']);
                }
            }

            if (isset($payload['Message']) && is_string($payload['Message'])) {
                $payload = json_decode($payload['Message'], true) ?? $payload;
            }

            $channelArn = $payload['detail']['channel_arn'] ?? $payload['channel_arn'] ?? null;
            $recordingStatus = $payload['detail']['recording_status'] ?? $payload['recording_status'] ?? null;
            $streamId = $payload['detail']['stream_id'] ?? $payload['stream_id'] ?? null;
            
            // Extract S3 master playlist URL if provided by AWS EventBridge / Lambda
            $vodUrl = $payload['detail']['recording_s3_key'] ?? $payload['vod_url'] ?? null;

            if ($vodUrl && !str_starts_with($vodUrl, 'http')) {
                $s3Domain = env('AWS_IVS_CLOUDFRONT_URL') ?? ('https://' . env('AWS_IVS_S3_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION', 'us-east-1') . '.amazonaws.com');
                $vodUrl = rtrim($s3Domain, '/') . '/' . ltrim($vodUrl, '/');
            }

            if ($channelArn) {
                $liveStream = LiveStream::where('channel_arn', $channelArn)->first();

                if ($liveStream) {
                    $updateData = ['status' => 'ended'];
                    if ($vodUrl) {
                        $updateData['vod_url'] = $vodUrl;
                    }
                    $liveStream->update($updateData);

                    Log::info("Live stream {$liveStream->id} updated via AWS IVS Webhook", $updateData);

                    return response()->json([
                        'status' => true,
                        'message' => 'Live stream updated successfully',
                        'data' => $liveStream,
                    ]);
                }
            }

            return response()->json([
                'status' => false,
                'message' => 'No matching live stream found for channel ARN',
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
