<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FinnhubService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateMarketPulseCache extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'pulse:update-cache';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Fetch and cache all data for the Market Pulse dashboard.';

    protected $finnhubService;

    /**
     * Create a new command instance.
     * @return void
     */
    public function __construct(FinnhubService $finnhubService)
    {
        parent::__construct();
        $this->finnhubService = $finnhubService;
    }

    /**
     * Execute the console command.
     * @return int
     */
    public function handle()
    {
        $this->info('Starting Market Pulse cache update loop (runs for 1 minute)...');

        $startTime = time();

        while (time() - $startTime < 60) {
            $this->info('Updating cache...');

            // 1. Most Held Stocks (still based on current holdings)
            $mostHeldStocks = DB::table('stocks')
                ->select('symbol', DB::raw('COUNT(user_id) as holding_count'))
                ->groupBy('symbol')
                ->orderByDesc('holding_count')
                ->limit(5)
                ->get();
            $mostHeld = $mostHeldStocks->map(function ($stock) {
                $profile = $this->finnhubService->getCompanyProfile($stock->symbol);
                $stock->logo = $profile['logo'] ?? '';
                return (array) $stock;
            })->toArray();
            Cache::put('market_pulse_most_held', $mostHeld, now()->addHours(1));
            $this->info('Cached Most Held Stocks.');

            // 2. Trending Adds (now based on stock_add_events table)
            $trendingAddsStocks = DB::table('stock_add_events') // Query the new table
                ->where('created_at', '>=', now()->subDays(7))
                ->select('stock_symbol as symbol', DB::raw('COUNT(user_id) as new_add_count'))
                ->groupBy('stock_symbol')
                ->orderByDesc('new_add_count')
                ->limit(5)
                ->get();
            $trendingAdds = $trendingAddsStocks->map(function ($stock) {
                $profile = $this->finnhubService->getCompanyProfile($stock->symbol);
                $stock->logo = $profile['logo'] ?? '';
                return (array) $stock;
            })->toArray();
            Cache::put('market_pulse_trending_adds', $trendingAdds, now()->addHours(1));
            $this->info('Cached Trending Adds.');

            // 4. Stocks Close to Earnings Reports
            $tomorrow = now()->addDay()->toDateString();
            $response = $this->finnhubService->getEarningsCalendar($tomorrow, $tomorrow);
            $earningsCalendar = $response['earningsCalendar'] ?? [];
            $calendarSymbols = collect($earningsCalendar)->pluck('symbol')->unique()->toArray();
            $stockDetails = collect(DB::table('stocks')
                            ->whereIn('symbol', $calendarSymbols)
                            ->select('symbol', 'id', 'logo')
                            ->get())
                            ->keyBy('symbol');
            $upcomingEarnings = collect($earningsCalendar)->map(function ($earning) use ($stockDetails) {
                $symbol = $earning['symbol'];
                $earning['stock_id'] = $stockDetails[$symbol]->id ?? null;
                $earning['stock_logo'] = $stockDetails[$symbol]->logo ?? null;
                if (empty($earning['stock_logo'])) {
                    $profile = $this->finnhubService->getCompanyProfile($symbol);
                    $earning['stock_logo'] = $profile['logo'] ?? '';
                }
                return $earning;
            })->sortBy('date')->values()->all();
            Cache::put('market_pulse_upcoming_earnings', $upcomingEarnings, now()->addHours(6));
            $this->info('Cached Upcoming Earnings.');

            Log::info('Market Pulse cache updated successfully within loop.');
            $this->info('Cache updated. Sleeping for 10 seconds...'); // Changed sleep to 10 seconds
            sleep(10);
        }

        $this->info('Market Pulse cache update loop finished.');
        return Command::SUCCESS;
    }
}
