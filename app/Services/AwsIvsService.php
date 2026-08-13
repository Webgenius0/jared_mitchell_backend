<?php

namespace App\Services;

use Aws\IVS\IVSClient;
use Aws\Exception\AwsException;
use Illuminate\Support\Facades\Log;

class AwsIvsService
{
    protected $client;

    public function __construct()
    {
        $this->client = new IVSClient([
            'version' => 'latest',
            'region'  => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);
    }

    /**
     * Create a new IVS Channel
     *
     * @param string $name
     * @return array|null
     */
    public function createChannel(string $name)
    {
        try {
            $params = [
                'name' => $name,
                'type' => 'STANDARD', // STANDARD or BASIC
                'latencyMode' => 'LOW',
            ];

            // If a recording configuration ARN is provided, attach it so streams are recorded as VOD
            $recordingConfigArn = env('AWS_IVS_RECORDING_CONFIGURATION_ARN');
            if ($recordingConfigArn) {
                $params['recordingConfigurationArn'] = $recordingConfigArn;
            }

            $result = $this->client->createChannel($params);

            $channel = $result->get('channel');
            $streamKey = $result->get('streamKey');

            return [
                'channel_arn' => $channel['arn'],
                'ingest_endpoint' => 'rtmps://' . $channel['ingestEndpoint'] . ':443/app/',
                'playback_url' => $channel['playbackUrl'],
                'stream_key' => $streamKey['value'],
            ];
        } catch (AwsException $e) {
            Log::error('AWS IVS Create Channel Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get stream details including viewer count from AWS IVS
     *
     * @param string $arn
     * @return array|null
     */
    public function getStreamDetail(string $arn)
    {
        try {
            $result = $this->client->getStream([
                'channelArn' => $arn,
            ]);

            $stream = $result->get('stream');
            return [
                'viewer_count' => $stream['viewerCount'] ?? 0,
                'state' => $stream['state'] ?? 'OFFLINE',
                'health' => $stream['health'] ?? 'UNKNOWN',
            ];
        } catch (AwsException $e) {
            // Stream might be offline or channel not actively broadcasting
            return null;
        }
    }

    /**
     * Delete an IVS Channel
     *
     * @param string $arn
     * @return bool
     */
    public function deleteChannel(string $arn): bool
    {
        try {
            $this->client->deleteChannel([
                'arn' => $arn,
            ]);
            return true;
        } catch (AwsException $e) {
            Log::error('AWS IVS Delete Channel Error: ' . $e->getMessage());
            return false;
        }
    }
}
