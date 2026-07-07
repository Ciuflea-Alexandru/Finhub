<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Services\FinnhubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class StockController extends Controller
{
    protected $finnhubService;

    public function __construct(FinnhubService $finnhubService)
    {
        $this->finnhubService = $finnhubService;
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = [];
        $profile = [];
        $timestamp = null;
        $changePercent = 0;

        if ($query) {
            $query = Str::upper($query);
            $results = $this->finnhubService->getQuote($query);
            $profile = $this->finnhubService->getCompanyProfile($query);
            $timestamp = microtime(true);

            // Calculate 24-hour percentage change
            if (!empty($results['c']) && !empty($results['pc'])) {
                $changePercent = (($results['c'] - $results['pc']) / $results['pc']) * 100;
            }
        }

        return view('stocks.search-results', [
            'results' => $results,
            'profile' => $profile,
            'query' => $query,
            'timestamp' => $timestamp,
            'change_percent' => round($changePercent, 2),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Validate data and convert symbol to uppercase
        $validated = $request->validate([
            'symbol' => 'required|string|max:10',
        ]);
        $validated['symbol'] = Str::upper($validated['symbol']);
        $data = $this->finnhubService->getQuote($validated['symbol']);

        if (empty($data) || !isset($data['c']) || $data['c'] == 0) {
            return redirect()->back()->withErrors(['symbol' => 'Invalid stock symbol or no data available.']);
        }

        if ($user->stocks()->where('symbol', $validated['symbol'])->exists()) {
            return redirect()->back()->withErrors(['symbol' => 'You already have this stock in your portfolio.']);
        }

        $profile = $this->finnhubService->getCompanyProfile($validated['symbol']);

        $user->stocks()->create([
            'symbol' => $validated['symbol'],
            'name' => $profile['name'] ?? null,
            'logo' => $profile['logo'] ?? null,
            'exchange' => $profile['exchange'] ?? null,
        ]);

        return redirect()->route('dashboard')->with('success', 'Stock added successfully!');
    }

    public function show(Stock $stock)
    {
        if ($stock->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $symbol = $stock->symbol;
        $quote = $this->finnhubService->getQuote($symbol);
        $profile = $this->finnhubService->getCompanyProfile($symbol);
        $timestamp = microtime(true);
        $changePercent = 0;

        if (!empty($quote['c']) && !empty($quote['pc'])) {
            $changePercent = (($quote['c'] - $quote['pc']) / $quote['pc']) * 100;
        }

        // Fetch company news for the last 30 days
        $from = now()->subDays(30)->toDateString();
        $to = now()->toDateString();
        $allNews = $this->finnhubService->getCompanyNews($symbol, $from, $to);

        // Sort news by datetime and take the top 3
        usort($allNews, function ($a, $b) {
            return $b['datetime'] <=> $a['datetime'];
        });
        $news = array_slice($allNews, 0, 3);


        return view('stocks.show', [
            'stock' => $stock,
            'quote' => $quote,
            'profile' => $profile,
            'timestamp' => $timestamp,
            'change_percent' => round($changePercent, 2),
            'news' => $news,
        ]);
    }

    public function destroy(Stock $stock)
    {
        if ($stock->user_id !== Auth::id()) {
            abort(403);
        }

        $stock->delete();

        return redirect()->route('dashboard')->with('success', 'Stock removed successfully!');
    }
}
