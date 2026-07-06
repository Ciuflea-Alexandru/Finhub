<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FinnhubService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://finnhub.io/api/v1';

    public function __construct()
    {
        // Always pull your API key from the .env file!
        $this->apiKey = config('services.finnhub.key');
    }

    public function getQuote(string $symbol)
    {
        $response = Http::get("{$this->baseUrl}/quote", [
            'symbol' => $symbol,
            'token' => $this->apiKey,
        ]);

        return $response->json();
    }
}
