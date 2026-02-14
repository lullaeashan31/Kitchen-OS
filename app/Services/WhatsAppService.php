<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $baseUrl = 'https://graph.facebook.com/v22.0';
    protected $phoneNumberId;
    protected $accessToken;

    public function __construct()
    {
        $this->phoneNumberId = config('whatsapp.phone_number_id');
        $this->accessToken = config('whatsapp.access_token');
    }

    public function sendMessage($to, $messageBody)
    {
        if (!$this->phoneNumberId || !$this->accessToken) {
            Log::error('WhatsApp credentials not found.');
            return ['error' => 'Credentials missing'];
        }

        $url = "{$this->baseUrl}/{$this->phoneNumberId}/messages";

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => (strlen($to) == 10) ? '91' . $to : $to,
            'type' => 'text',
            'text' => [
                'body' => $messageBody
            ]
        ];

        try {
            $response = Http::withToken($this->accessToken)
                ->post($url, $payload);

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error('WhatsApp Send Error', $response->json());
                return ['error' => $response->json()];
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp Exception: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}
