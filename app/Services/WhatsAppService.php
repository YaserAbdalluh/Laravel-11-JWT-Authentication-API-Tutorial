<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $baseUrl;
    protected string $deviceId;
    protected string $deviceToken;

    public function __construct()
    {
        $this->baseUrl = config('services.whatsapp.api_url');
        $this->deviceId = config('services.whatsapp.device_id');
        $this->deviceToken = config('services.whatsapp.device_token');
    }

    public function sendVerificationCode(string $phoneNumber, string $code): bool
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->post($this->baseUrl, [
                    'deviceId' => $this->deviceId,
                    'deviceToken' => $this->deviceToken,
                    'number' => $phoneNumber,
                    'message' => "رمز التحقق الخاص بك هو: $code",
                ]);

        return $response->successful();
    }
}