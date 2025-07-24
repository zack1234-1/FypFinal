<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Recording;

class AgoraMeetingService
{
    protected $customerId;
    protected $customerCertificate;
    protected $baseUrl;

    public function __construct()
    {
        $this->customerId = env('AGORA_CUSTOMER_ID');
        $this->customerCertificate = env('AGORA_CUSTOMER_CERTIFICATE');
        $this->baseUrl = 'https://api.agora.io/v1/apps/' . env('AGORA_APP_ID');
    }

    private function getAuthHeader()
    {
        return [
            'Authorization' => 'Basic ' . base64_encode($this->customerId . ':' . $this->customerCertificate),
        ];
    }

    public function acquireResource(string $channelName)
    {
        $response = Http::withHeaders($this->getAuthHeader())->post("{$this->baseUrl}/cloud_recording/acquire", [
            'cname' => $channelName,
            'uid' => '1',
            'clientRequest' => []
        ]);

        return $response->json();
    }

    public function startRecording(string $channelName, string $token, string $resourceId)
    {
        $response = Http::withHeaders($this->getAuthHeader())->post("{$this->baseUrl}/cloud_recording/resourceid/{$resourceId}/mode/mix/start", [
            'cname' => $channelName,
            'uid' => '1',
            'clientRequest' => [
                'token' => $token,
                'recordingConfig' => [
                    'maxIdleTime' => 30,
                    'streamTypes' => 2,
                    'channelType' => 1,
                    'videoStreamType' => 0,
                    'transcodingConfig' => [
                        'width' => 640,
                        'height' => 360,
                        'fps' => 15,
                        'bitrate' => 600,
                        'mixedVideoLayout' => 1
                    ]
                ],
                'storageConfig' => [
                    'vendor' => 1,
                    'region' => 6,
                    'bucket' => env('AGORA_BUCKET_NAME'),
                    'accessKey' => env('AGORA_BUCKET_KEY'),
                    'secretKey' => env('AGORA_BUCKET_SECRET'),
                    'fileNamePrefix' => ['recordings']
                ]
            ]
        ]);

        return $response->json();
    }

    public function stopRecording(string $channelName, string $resourceId, string $sid, int $workspaceId = 1)
    {
        $response = Http::withHeaders($this->getAuthHeader())->post("{$this->baseUrl}/cloud_recording/resourceid/{$resourceId}/sid/{$sid}/mode/mix/stop", [
            'cname' => $channelName,
            'uid' => '1',
            'clientRequest' => (object)[]
        ]);

        $data = $response->json();

        if (isset($data['serverResponse']['fileList'])) {
            $files = json_decode($data['serverResponse']['fileList'], true);
            foreach ($files as $item) {
                $s3Url = 'https://' . env('AGORA_BUCKET_NAME') . '.s3.amazonaws.com/' . $item['s3key'];

                $fileContent = file_get_contents($s3Url);

                Recording::updateOrCreate([
                    'file_name' => basename($item['s3key']),
                ], [
                    'mime_type' => 'video/mp4',
                    'recording_blob' => $fileContent,
                    'user_id' => auth()->id() ?? 1,
                    'workspace_id' => $workspaceId,
                ]);
            }
        }

        return $data;
    }

    public function getRecordings()
    {
        return Recording::latest()->get();
    }
}
