<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Services\FinnhubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB; // Added this line

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
            'finnhubIndustry' => $profile['finnhubIndustry'] ?? null,
        ]);

        // Record the add event in the new table
        DB::table('stock_add_events')->insert([
            'user_id' => $user->id,
            'stock_symbol' => $validated['symbol'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('success', 'Stock added successfully!');
    }

    public function show(Stock $stock)
    {
        if ($stock->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $symbol = $stock->symbol; // Ensure $symbol is defined
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
            'symbol' => $symbol, // Pass $symbol to the view
            'quote' => $quote,
            'profile' => $profile,
            'timestamp' => $timestamp,
            'change_percent' => round($changePercent, 2),
            'news' => $news,
        ]);
    }

    public function details($symbol)
    {
        $symbol = Str::upper($symbol);

        // Check if the user owns this stock to pass the model instance if they do
        $stock = Auth::user()->stocks()->where('symbol', $symbol)->first();

        $quote = $this->finnhubService->getQuote($symbol);
        $profile = $this->finnhubService->getCompanyProfile($symbol);
        $timestamp = microtime(true);
        $changePercent = 0;

        if (empty($profile)) {
            abort(404, 'Stock symbol not found.');
        }

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
            'stock' => $stock, // This will be null if the user doesn't own the stock
            'symbol' => $symbol,
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
