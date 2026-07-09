<?php

namespace App\Http\Controllers;

use App\Services\FinnhubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class EarningsController extends Controller
{
    protected $finnhubService;

    public function __construct(FinnhubService $finnhubService)
    {
        $this->finnhubService = $finnhubService;
    }

    public function index()
    {
        $user = Auth::user();
        $userStocks = $user->stocks;
        $userStockSymbols = $userStocks->pluck('symbol')->toArray();

        // Create a map from symbol to stock ID and logo for easy lookup
        $stockDetailsMap = $userStocks->keyBy('symbol')->map(function ($stock) {
            return [
                'id' => $stock->id,
                'logo' => $stock->logo,
            ];
        })->toArray();

        $from = now()->toDateString();
        $to = now()->addDays(20)->toDateString();

        $earningsCalendar = Cache::remember("earnings_calendar_{$from}_{$to}", now()->addDays(), function () use ($from, $to) {
            $response = $this->finnhubService->getEarningsCalendar($from, $to);
            return $response['earningsCalendar'] ?? [];
        });

        // Filter the reports to include only those for the saved stocks
        $userEarnings = collect($earningsCalendar)->filter(function ($earning) use ($userStockSymbols) {
            return in_array($earning['symbol'], $userStockSymbols);
        })->map(function ($earning) use ($stockDetailsMap) {
            // Add stock ID and logo to the earning report
            $symbol = $earning['symbol'];
            $earning['stock_id'] = $stockDetailsMap[$symbol]['id'] ?? null;
            $earning['stock_logo'] = $stockDetailsMap[$symbol]['logo'] ?? null;
            return $earning;
        })->sortBy('date')->values()->all();

        $chartLabels = [];
        $chartData = [];
        $currentDate = now()->copy();

        for ($i = 0; $i <= 20; $i++) {
            $dateString = $currentDate->toDateString();
            $chartLabels[] = $currentDate->format('M d');
            $chartData[$dateString] = 0;
            $currentDate->addDay();
        }

        foreach ($userEarnings as $earning) {
            if (isset($chartData[$earning['date']])) {
                $chartData[$earning['date']]++;
            }
        }

        return view('earnings.index', [
            'earnings' => $userEarnings,
            'chartLabels' => array_values($chartLabels),
            'chartCounts' => array_values($chartData),
        ]);
    }
}
