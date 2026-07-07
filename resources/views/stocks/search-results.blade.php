<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Search Stocks') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="GET" action="{{ route('stocks.search') }}">
                        <input type="text" name="query" placeholder="Enter stock symbol (e.g., AAPL)" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" value="{{ $query ?? '' }}">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Search
                        </button>
                    </form>

                    @if (!empty($results) && isset($results['c']) && $results['c'] != 0)
                        <div class="mt-6">
                            @if (!empty($profile['logo']))
                                <img src="{{ $profile['logo'] }}" alt="{{ $profile['name'] }} Logo" class="h-16 w-16">
                            @endif
                            <p><strong>Name:</strong> {{ $profile['name'] ?? 'N/A' }}</p>
                            <p><strong>Symbol:</strong> {{ $query }}</p>
                            <p><strong>Exchange:</strong> {{ $profile['exchange'] ?? 'N/A' }}</p>
                            <p><strong>Current Price:</strong> {{ $results['c'] }}</p>
                            <p><strong>High Price of the day:</strong> {{ $results['h'] }}</p>
                            <p><strong>Low Price of the day:</strong> {{ $results['l'] }}</p>
                            <p><strong>Open Price of the day:</strong> {{ $results['o'] ?? 'N/A' }}</p>
                            <p><strong>Previous Close Price:</strong> {{ $results['pc'] ?? 'N/A' }}</p>
                            <p>
                                <strong>Change (24h):</strong>
                                <span class="font-bold {{ $change_percent >= 0 ? 'text-green-500' : 'text-red-500' }}">
                                    {{ $change_percent }}%
                                </span>
                            </p>

                            @if($timestamp)
                                <p class="text-xs text-gray-500">Last updated: {{ date('Y-m-d H:i:s', (int)$timestamp) }} UTC</p>
                            @endif

                            <form method="POST" action="{{ route('stocks.store') }}" class="mt-4">
                                @csrf
                                <input type="hidden" name="symbol" value="{{ $query }}">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Add to Portfolio
                                </button>
                            </form>
                        </div>
                    @elseif(!empty($query))
                        <p class="mt-6">No results found for "{{ $query }}".</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
