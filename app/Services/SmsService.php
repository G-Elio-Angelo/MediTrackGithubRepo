<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function send(string $to, string $message)
    {
        if (env('SMS_MOCK', 'true') === 'true') {
            Log::info("[SMS MOCK] to={$to} message={$message}");
            return true;
        }

        $apiUrl = env('SMS_API_URL');
        $token = env('SMS_API_TOKEN');
        $deviceId = env('SMS_DEVICE_ID');

        if (!$apiUrl || !$token || !$deviceId) {
            Log::error('SMS API credentials missing');
            return false;
        }

        try {
            $payload = [[
                'phone_number' => $to,
                'message' => $message,
                'device_id' => $deviceId,
            ]];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->post($apiUrl, $payload);

            if ($response->successful()) {
                Log::info("[SMS SENT] to={$to}");
                return true;
            } else {
                Log::error('SMS failed: ' . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error('SMS error: ' . $e->getMessage());
            return false;
        }
    }
}
