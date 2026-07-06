<?php

namespace App\Http\Controllers;

use App\Services\FinnhubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        if ($query) {
            $results = $this->finnhubService->getQuote($query);
        }

        return view('stocks.search-results', ['results' => $results]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $user->stocks()->create([
            'symbol' => $request->input('symbol'),
            'name' => $request->input('name'),
        ]);

        return redirect()->route('dashboard')->with('success', 'Stock added successfully!');
    }
}
