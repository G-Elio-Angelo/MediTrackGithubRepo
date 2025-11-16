<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $apiToken;
    protected $baseUrl;
    protected $fakeMode = false;

    public function __construct()
    {
        $this->apiToken = env('IPROG_API_TOKEN');
        $this->baseUrl = 'https://sms.iprogtech.com/api/v1';
        $this->fakeMode = env('SMS_FAKE', config('app.env') === 'local');
    }

    /**
     * Send SMS to single or multiple recipients
     */
   public function sendSMS($phoneNumber, $message)
{
    if ($this->fakeMode || empty($this->apiToken)) {
        $formattedPhone = $this->formatPhoneNumber($phoneNumber);
        Log::info('[SMS - FAKE MODE] Sending SMS (simulated)', [
            'phone' => $formattedPhone,
            'message' => $message,
        ]);

        return [
            'success' => true,
            'provider' => 'local-log',
            'message' => 'SMS simulated (fake mode)',
        ];
    }

    try {
        $formattedPhone = $this->formatPhoneNumber($phoneNumber);

        Log::info('iProg SMS: Attempting to send SMS', [
            'phone' => $formattedPhone,
            'message_length' => strlen($message)
        ]);

        // First attempt (normal token param)
        $response = Http::timeout(30)
            ->asForm()
            ->post("{$this->baseUrl}/sms_messages/send_bulk", [
                'api_token' => $this->apiToken,
                'phone_number' => $formattedPhone,
                'message' => $message,
            ]);

        $result = $response->json() ?? [];

        // ✅ Explicit success detection
        $isSuccess = $response->successful() &&
                     isset($result['status']) &&
                     strtolower($result['status']) === 'success';

        if ($isSuccess) {
            Log::info('iProg SMS: Message sent successfully (1st attempt)', [
                'phone' => $formattedPhone,
                'message_ids' => $result['message_ids'] ?? 'N/A'
            ]);

            return [
                'success' => true,
                'provider' => 'iProg',
                'message' => $result['message'] ?? 'SMS sent successfully',
                'message_id' => $result['message_ids'] ?? null
            ];
        }

        // Only retry if first call truly failed or token invalid
        $needsAlternate = !$response->successful()
            || (isset($result['message']) && str_contains(strtolower($result['message']), 'invalid token'));

        if ($needsAlternate) {
            Log::warning('iProg SMS: First attempt failed, retrying with alternate auth', [
                'phone' => $formattedPhone,
                'status' => $response->status(),
                'response' => $result,
            ]);

            // Attempt 2 (token param)
            $response2 = Http::timeout(30)
                ->asForm()
                ->post("{$this->baseUrl}/sms_messages/send_bulk", [
                    'token' => $this->apiToken,
                    'phone_number' => $formattedPhone,
                    'message' => $message,
                ]);

            $result2 = $response2->json() ?? [];

            $isSuccess2 = $response2->successful() &&
                          isset($result2['status']) &&
                          strtolower($result2['status']) === 'success';

            if ($isSuccess2) {
                Log::info('iProg SMS: Message sent successfully (2nd attempt)', [
                    'phone' => $formattedPhone,
                    'message_ids' => $result2['message_ids'] ?? 'N/A'
                ]);

                return [
                    'success' => true,
                    'provider' => 'iProg',
                    'message' => $result2['message'] ?? 'SMS sent successfully',
                    'message_id' => $result2['message_ids'] ?? null
                ];
            }

            // Attempt 3 (Bearer token)
            $response3 = Http::timeout(30)
                ->withHeaders(['Authorization' => 'Bearer ' . $this->apiToken])
                ->post("{$this->baseUrl}/sms_messages/send_bulk", [
                    'phone_number' => $formattedPhone,
                    'message' => $message,
                ]);

            $result3 = $response3->json() ?? [];

            $isSuccess3 = $response3->successful() &&
                          isset($result3['status']) &&
                          strtolower($result3['status']) === 'success';

            if ($isSuccess3) {
                Log::info('iProg SMS: Message sent successfully (3rd attempt - Bearer)', [
                    'phone' => $formattedPhone,
                    'message_ids' => $result3['message_ids'] ?? 'N/A'
                ]);

                return [
                    'success' => true,
                    'provider' => 'iProg',
                    'message' => $result3['message'] ?? 'SMS sent successfully',
                    'message_id' => $result3['message_ids'] ?? null
                ];
            }

            // All attempts failed
            Log::warning('iProg SMS: All attempts failed', [
                'phone' => $formattedPhone,
                'attempts' => [
                    'first' => ['status' => $response->status(), 'body' => $result],
                    'second' => ['status' => $response2->status(), 'body' => $result2],
                    'third' => ['status' => $response3->status(), 'body' => $result3],
                ]
            ]);

            return [
                'success' => false,
                'provider' => 'iProg',
                'message' => $result3['message']
                    ?? $result2['message']
                    ?? $result['message']
                    ?? 'Failed to send SMS (all attempts)'
            ];
        }

        // Default fallback if not successful
        Log::warning('iProg SMS: Failed to send (no success detected)', [
            'phone' => $formattedPhone,
            'response' => $result
        ]);

        return [
            'success' => false,
            'provider' => 'iProg',
            'message' => $result['message'] ?? 'Failed to send SMS'
        ];

    } catch (\Exception $e) {
        Log::error('iProg SMS: Exception occurred', [
            'error' => $e->getMessage(),
            'phone' => $phoneNumber
        ]);

        return [
            'success' => false,
            'provider' => 'iProg',
            'message' => 'SMS service error: ' . $e->getMessage()
        ];
    }
}


    /**
     * Generic send method used by controllers for compatibility.
     * Delegates to sendSMS. Accepts single phone or array of phones.
     *
     * @param string|array $phoneNumber
     * @param string $message
     * @return array
     */
    public function send($phoneNumber, $message)
    {
        // If array provided, send individually and aggregate results
        if (is_array($phoneNumber)) {
            $results = [];
            foreach ($phoneNumber as $p) {
                $results[] = $this->sendSMS($p, $message);
            }
            return [
                'success' => collect($results)->every(fn($r) => $r['success'] ?? false),
                'details' => $results,
            ];
        }

        return $this->sendSMS($phoneNumber, $message);
    }

/*Iprog SMS*/ 
    public function sendOTP($phoneNumber, $customMessage = null)
    {
        try {
            $formattedPhone = $this->formatPhoneNumber($phoneNumber);

            Log::info('iProg OTP: Attempting to send OTP', [
                'phone' => $formattedPhone
            ]);

            $payload = [
                'api_token' => $this->apiToken,
                'phone_number' => $formattedPhone,
            ];

            if ($customMessage) {
                $payload['message'] = $customMessage;
            }

            $response = Http::timeout(30)
                ->post("{$this->baseUrl}/otp/send_otp", $payload);

            $result = $response->json();

            if ($response->successful() && isset($result['status']) && $result['status'] == 'success') {
                Log::info('iProg OTP: Sent successfully', [
                    'phone' => $formattedPhone,
                    'otp_code' => $result['data']['otp_code'] ?? 'N/A'
                ]);

                return [
                    'success' => true,
                    'provider' => 'iProg',
                    'otp_code' => $result['data']['otp_code'] ?? null,
                    'expires_at' => $result['data']['otp_code_expires_at'] ?? null,
                    'message' => $result['message'] ?? 'OTP sent successfully'
                ];
            }

            return [
                'success' => false,
                'provider' => 'iProg',
                'message' => $result['message'] ?? 'Failed to send OTP'
            ];

        } catch (\Exception $e) {
            Log::error('iProg OTP: Exception occurred', [
                'error' => $e->getMessage(),
                'phone' => $phoneNumber
            ]);

            return [
                'success' => false,
                'provider' => 'iProg',
                'message' => 'OTP service error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify OTP code
     */
    public function verifyOTP($phoneNumber, $otpCode)
    {
        try {
            $formattedPhone = $this->formatPhoneNumber($phoneNumber);

            $response = Http::timeout(30)
                ->post("{$this->baseUrl}/otp/verify_otp", [
                    'api_token' => $this->apiToken,
                    'phone_number' => $formattedPhone,
                    'otp' => $otpCode
                ]);

            $result = $response->json();

            return [
                'success' => $result['status'] === 'success',
                'message' => $result['message'] ?? 'Verification failed'
            ];

        } catch (\Exception $e) {
            Log::error('iProg OTP Verify: Exception occurred', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'OTP verification error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check SMS status
     */
    public function checkSMSStatus($messageId)
    {
        try {
            $response = Http::timeout(30)
                ->get("{$this->baseUrl}/sms_messages/status", [
                    'api_token' => $this->apiToken,
                    'message_id' => $messageId
                ]);

            $result = $response->json();

            return [
                'success' => $response->successful(),
                'status' => $result['message_status'] ?? 'unknown',
                'message' => $result
            ];

        } catch (\Exception $e) {
            Log::error('iProg Check Status: Exception occurred', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Check remaining credits
     */
    public function checkCredits()
    {
        try {
            $response = Http::timeout(30)
                ->get("{$this->baseUrl}/account/sms_credits", [
                    'api_token' => $this->apiToken
                ]);

            $result = $response->json();

            if ($response->successful() && isset($result['status']) && $result['status'] == 'success') {
                return [
                    'success' => true,
                    'credits' => $result['data']['load_balance'] ?? 0,
                    'message' => $result['message'] ?? 'Credits retrieved'
                ];
            }

            return [
                'success' => false,
                'credits' => 0,
                'message' => 'Failed to retrieve credits'
            ];

        } catch (\Exception $e) {
            Log::error('iProg Check Credits: Exception occurred', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'credits' => 0,
                'message' => 'Credits check error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Format phone number to international format (63XXXXXXXXXX)
     */
    protected function formatPhoneNumber($phoneNumber)
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);

        // If starts with 0, replace with 63
        if (substr($cleaned, 0, 1) === '0') {
            return '63' . substr($cleaned, 1);
        }

        // If already starts with 63, return as is
        if (substr($cleaned, 0, 2) === '63') {
            return $cleaned;
        }

        // Otherwise, prepend 63
        return '63' . $cleaned;
    }

    /**
     * Get provider name for display
     */
    public function getProviderName()
    {
        return 'iProg SMS';
    }
}