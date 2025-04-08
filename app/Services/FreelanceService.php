<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FreelanceService
{
    protected string $apiUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.freelancing.api_url');
        $this->apiKey = config('services.freelancing.api_key');
    }

    public function getJobs($category = null)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->get($this->apiUrl . '/jobs', [
            'category' => $category,
        ]);

        return $response->json();
    }
}
