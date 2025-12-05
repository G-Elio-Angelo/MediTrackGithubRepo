<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MailService
{
    private $apiKey;
    private $baseUrl = 'https://api.sendgrid.com/v3/mail/send';

    public function __construct()
    {
        $this->apiKey = config('services.sendgrid.api_key', env('SENDGRID_API_KEY'));
    }

    public function sendEmail($to, $subject, $content, $fromEmail = null, $fromName = null)
    {
        $sendgridFallbackFrom = env('SENDGRID_FROM_EMAIL');

        $fromEmail = $fromEmail ?: $sendgridFallbackFrom ?: config('mail.from.address');
        $fromName = $fromName ?: config('mail.from.name');

        if (empty($this->apiKey)) {
            Log::warning('SendGrid API key not configured; email not sent', ['to' => $to, 'subject' => $subject]);

            return [
                'success' => false,
                'message' => 'SendGrid API key not configured'
            ];
        }

        $data = [
            'personalizations' => [
                [
                    'to' => [
                        [
                            'email' => $to,
                        ]
                    ],
                    'subject' => $subject
                ]
            ],
            'from' => [
                'email' => $fromEmail,
                'name' => $fromName
            ],
            'content' => [
                [
                    'type' => 'text/html',
                    'value' => $content
                ]
            ]
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl, $data);

            if ($response->successful()) {
                Log::info('Email sent successfully via SendGrid', [
                    'to' => $to,
                    'subject' => $subject,
                    'status_code' => $response->status()
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Email sent successfully',
                    'response' => $response->json()
                ];
            } else {
                $status = $response->status();
                $body = $response->body();

                Log::warning('SendGrid email request returned non-success', [
                    'to' => $to,
                    'subject' => $subject,
                    'status_code' => $status,
                    'response' => $body
                ]);

                if ($status === 403 && stripos($body, 'sender identity') !== false || stripos($body, 'from address') !== false) {
                    if (!empty($sendgridFallbackFrom) && strcasecmp($sendgridFallbackFrom, $fromEmail) !== 0) {
                        Log::info('Retrying SendGrid send with fallback SENDGRID_FROM_EMAIL', ['fallback' => $sendgridFallbackFrom]);

                        $data['from']['email'] = $sendgridFallbackFrom;

                        $retry = Http::withHeaders([
                            'Authorization' => 'Bearer ' . $this->apiKey,
                            'Content-Type' => 'application/json'
                        ])->post($this->baseUrl, $data);

                        if ($retry->successful()) {
                            Log::info('Email sent successfully via SendGrid (retry with fallback)', [
                                'to' => $to,
                                'subject' => $subject,
                                'status_code' => $retry->status()
                            ]);

                            return [
                                'success' => true,
                                'message' => 'Email sent successfully (retry with fallback)',
                                'response' => $retry->json()
                            ];
                        }
                        $status = $retry->status();
                        $body = $retry->body();
                    }

                    Log::error('SendGrid rejected email due to unverified sender identity. Verify the sender in SendGrid or set SENDGRID_FROM_EMAIL to a verified address.', [
                        'to' => $to,
                        'subject' => $subject,
                        'status_code' => $status,
                        'response' => $body,
                        'help' => 'https://sendgrid.com/docs/for-developers/sending-email/sender-identity/'
                    ]);

                    return [
                        'success' => false,
                        'message' => 'SendGrid rejected the sender address. Verify the sender identity in SendGrid or set SENDGRID_FROM_EMAIL to a verified sender.',
                        'error' => $body
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'Failed to send email',
                    'error' => $body,
                    'status_code' => $status
                ];
            }
        } catch (\Exception $e) {
            Log::error('SendGrid email exception', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Email service error',
                'error' => $e->getMessage()
            ];
        }
    }
}