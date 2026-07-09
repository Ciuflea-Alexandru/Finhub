<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class MarketPulseController extends Controller
{
    public function index()
    {
        // Now the controller only job is to read the precalculated data from the cache.
        $mostHeld = collect(Cache::get('market_pulse_most_held', []));
        $trendingAdds = collect(Cache::get('market_pulse_trending_adds', []));
        $upcomingEarnings = collect(Cache::get('market_pulse_upcoming_earnings', []));

        return view('market-pulse.index', [
            'mostHeld' => $mostHeld,
            'trendingAdds' => $trendingAdds,
            'upcomingEarnings' => $upcomingEarnings,
        ]);
    }
}
