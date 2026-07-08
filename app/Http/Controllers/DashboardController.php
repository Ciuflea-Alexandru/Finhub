<?php

namespace App\Http\Controllers;

use App\Services\FinnhubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    protected $finnhubService;

    public function __construct(FinnhubService $finnhubService)
    {
        $this->finnhubService = $finnhubService;
    }
    public function __invoke(Request $request)
    {
        $user = Auth::user();

        // Get sorting parameters from the request, with defaults
        $sort_by = $request->input('sort', 'id');
        $direction = $request->input('direction', 'desc');

        // Get the user's stocks and apply sorting at the database level
        $stocks = $user->stocks()->orderBy($sort_by, $direction)->get();

        $stockData = [];

        foreach ($stocks as $stock) {
            // Retrieve stock data from cache or fetch from Finnhub.
            $cachedData = Cache::remember("stock_data_{$stock->symbol}", now()->addSeconds(10), function () use ($stock) {
                // Fetch stock data from Finnhub.
                $quote = $this->finnhubService->getQuote($stock->symbol);
                $changePercent = 0;
                // Calculate change percent.
                if (!empty($quote['c']) && !empty($quote['pc'])) {
                    $changePercent = (($quote['c'] - $quote['pc']) / $quote['pc']) * 100;
                }

                return [
                    'price' => $quote['c'] ?? 'N/A',
                    'change_percent' => round($changePercent, 2),
                    'timestamp' => microtime(true),
                ];
            });

            $stockData[] = [
                'id' => $stock->id,
                'symbol' => $stock->symbol,
                'name' => $stock->name,
                'logo' => $stock->logo,
                'exchange' => $stock->exchange,
                'price' => $cachedData['price'],
                'change_percent' => $cachedData['change_percent'],
                'timestamp' => $cachedData['timestamp'],
            ];
        }

        return view('dashboard', ['stocks' => $stockData]);
    }

    // Client-side JavaScript for polling to update the dashboard without requiring a full page reload.
    public function stocksApi(Request $request)
    {
        $user = Auth::user();
        $stocks = $user->stocks;
        $stockData = [];

        foreach ($stocks as $stock) {
            // Retrieve stock data from cache or fetch from Finnhub.
            $cachedData = Cache::remember("stock_data_{$stock->symbol}", now()->addSeconds(10), function () use ($stock) {
                // Fetch stock data from Finnhub
                $quote = $this->finnhubService->getQuote($stock->symbol);
                $changePercent = 0;

                // Calculate the 24-hour percentage change if current and previous close prices are available.
                if (!empty($quote['c']) && !empty($quote['pc'])) {
                    $changePercent = (($quote['c'] - $quote['pc']) / $quote['pc']) * 100;
                }

                return [
                    'price' => $quote['c'] ?? 'N/A',
                    'change_percent' => round($changePercent, 2),
                    'timestamp' => microtime(true),
                ];
            });

            $stockData[] = [
                'symbol' => $stock->symbol,
                'price' => $cachedData['price'],
                'change_percent' => $cachedData['change_percent'],
                'timestamp' => $cachedData['timestamp'],
            ];
        }

        return response()->json($stockData);
    }
}
