<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ZoomService
{
    public function getAccessToken()
    {
        return Cache::remember('zoom_access_token', 3500, function () {
            $response = Http::asForm()
                ->withBasicAuth(
                    config('services.zoom.client_id'),
                    config('services.zoom.client_secret')
                )
                ->post('https://zoom.us/oauth/token', [
                    'grant_type' => 'account_credentials',
                    'account_id' => config('services.zoom.account_id'),
                ]);

            if ($response->failed()) {
                throw new \Exception('Failed to get Zoom access token: ' . $response->body());
            }

            return $response->json()['access_token'];
        });
    }

    public function createMeeting($topic, $start_time, $duration = 30)
    {
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)
            ->post('https://api.zoom.us/v2/users/me/meetings', [
                'topic' => $topic,
                'type' => 2,
                'start_time' => $start_time,
                'duration' => $duration,
                'timezone' => 'Asia/Kuala_Lumpur',
                'settings' => [
                    'join_before_host' => true,
                    'host_video' => true,
                    'participant_video' => true,
                ],
            ]);

        if ($response->failed()) {
            throw new \Exception('Failed to create Zoom meeting: ' . $response->body());
        }

        return $response->json();
    }
}
