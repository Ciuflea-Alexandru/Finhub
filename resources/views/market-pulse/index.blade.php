<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Market Pulse') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                        {{-- Most Held Stocks --}}
                        <div class="border p-4 rounded-lg">
                            <h3 class="font-bold text-xl mb-3">Most Held Stocks</h3>
                            @if ($mostHeld->isNotEmpty())
                                <div class="flex space-x-4">
                                    @foreach ($mostHeld as $stock)
                                        <a href="{{ route('stocks.details', ['symbol' => $stock['symbol']]) }}" class="flex flex-col items-center p-2 border rounded-lg hover:bg-gray-100">
                                            <img src="{{ $stock['logo'] ?? '' }}" alt="{{ $stock['symbol'] }} Logo" class="h-20 w-20 mb-2">
                                            <span class="font-bold">{{ $stock['symbol'] }}</span>
                                            <span class="text-gray-600 text-sm">({{ $stock['holding_count'] }} users)</span>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-600">No data available.</p>
                            @endif
                        </div>

                        {{-- Trending Adds --}}
                        <div class="border p-4 rounded-lg">
                            <h3 class="font-bold text-xl mb-3">Trending Adds</h3>
                            @if ($trendingAdds->isNotEmpty())
                                <div class="flex space-x-4">
                                    @foreach ($trendingAdds as $stock)
                                        <a href="{{ route('stocks.details', ['symbol' => $stock['symbol']]) }}" class="flex flex-col items-center p-2 border rounded-lg hover:bg-gray-100">
                                            <img src="{{ $stock['logo'] ?? '' }}" alt="{{ $stock['symbol'] }} Logo" class="h-20 w-20 mb-2">
                                            <span class="font-bold">{{ $stock['symbol'] }}</span>
                                            <span class="text-gray-600 text-sm">({{ $stock['new_add_count'] }} adds)</span>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-600">No data available.</p>
                            @endif
                        </div>

                        {{-- Upcoming Earnings (from Finnhub) --}}
                        <div class="border p-4 rounded-lg col-span-full">
                            <h3 class="font-bold text-xl mb-3">Upcoming Earnings</h3>
                            @if (!empty($upcomingEarnings))
                                <div class="flex space-x-4">
                                    @foreach ($upcomingEarnings as $earning)
                                        @if ($earning['stock_id'])
                                            <a href="{{ route('stocks.show', $earning['stock_id']) }}" class="flex flex-col items-center p-2 border rounded-lg hover:bg-gray-100">
                                                <img src="{{ $earning['stock_logo'] ?? '' }}" alt="{{ $earning['symbol'] }} Logo" class="h-20 w-20 mb-2">
                                                <span class="font-bold">{{ $earning['symbol'] }}</span>
                                            </a>
                                        @else
                                            <a href="{{ route('stocks.details', ['symbol' => $earning['symbol']]) }}" class="flex flex-col items-center p-2 border rounded-lg hover:bg-gray-100">
                                                <img src="{{ $earning['stock_logo'] ?? '' }}" alt="{{ $earning['symbol'] }} Logo" class="h-20 w-20 mb-2">
                                                <span class="font-bold">{{ $earning['symbol'] }}</span>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-600">No upcoming earnings reports for tomorrow.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
