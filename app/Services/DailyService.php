<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DailyService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.daily.co/v1/';
    protected string $subdomain;

    public function __construct()
    {
        $this->apiKey = config('services.daily.api_key');
        $this->subdomain = config('services.daily.subdomain');
    }

    public function createRoom()
    {
        $response = Http::withToken($this->apiKey)
            ->post($this->baseUrl . 'rooms', [
                'properties' => [
                    'enable_chat' => true,
                    'start_video_off' => false,
                    'start_audio_off' => false,
                    'eject_at_room_exp' => true,
                    'enable_recording' => 'cloud'
                ]
            ]);

        return $response->json();
    }

    public function getRecordings()
    {
        $response = Http::withToken($this->apiKey)
            ->get($this->baseUrl . 'recordings')
            ->json();

        // Pretty print the result
        echo "<pre>";
        print_r($response);
        echo "</pre>";

        return $response;
    }

    public function getDownloadUrlById($recordingId)
    {
        $url = $this->baseUrl . 'recordings/' . $recordingId;

        $response = Http::withToken($this->apiKey)->get($url);

        Log::info("Recording detail for ID $recordingId", $response->json());

        if ($response->successful()) {
            return $response->json()['download_link'] ?? null;
        }

        Log::error('Failed to fetch download URL', ['response' => $response->json()]);
        return null;
    }

    public function getSubdomain()
    {
        return $this->subdomain;
    }
}
