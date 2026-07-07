<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FinnhubService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://finnhub.io/api/v1';

    public function __construct()
    {
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

    public function getCompanyProfile(string $symbol)
    {
        $response = Http::get("{$this->baseUrl}/stock/profile2", [
            'symbol' => $symbol,
            'token' => $this->apiKey,
        ]);

        return $response->json();
    }

    public function getCompanyNews(string $symbol, string $from, string $to)
    {
        $response = Http::get("{$this->baseUrl}/company-news", [
            'symbol' => $symbol,
            'from' => $from,
            'to' => $to,
            'token' => $this->apiKey,
        ]);

        return $response->json();
    }
}
