<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $stock->name ?? $stock->symbol }} Details
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">


                    {{-- Main content section: Flex container for left and right columns --}}
                    <div class="flex space-x-6 mt-6">

                        {{-- Left Column: Live Quote and Company Information --}}
                        <div class="flex-1">

                            <div class="flex items-center mb-4">
                                @if (!empty($profile['logo']))
                                <img src="{{ $profile['logo'] }}" alt="{{ $profile['name'] }} Logo" class="h-20 w-20 mr-4">
                                @endif
                                <div>
                                    <h3 class="text-2xl font-bold">{{ $profile['name'] ?? $stock->symbol }} ({{ $stock->symbol }})</h3>
                                    <p class="text-gray-600">{{ $profile['exchange'] ?? 'N/A' }}</p>
                                    </div>
                                </div>

                            <h4 class="text-xl font-semibold mb-2">Live Quote</h4>
                            <p><strong>Current Price:</strong> {{ $quote['c'] ?? 'N/A' }}</p>
                            <p>
                                <strong>Change (24h):</strong>
                                <span class="font-bold {{ $change_percent >= 0 ? 'text-green-500' : 'text-red-500' }}">
                                    {{ $change_percent }}%
                                </span>
                            </p>
                            <p><strong>High Price of the day:</strong> {{ $quote['h'] ?? 'N/A' }}</p>
                            <p><strong>Low Price of the day:</strong> {{ $quote['l'] ?? 'N/A' }}</p>
                            <p><strong>Open Price of the day:</strong> {{ $quote['o'] ?? 'N/A' }}</p>
                            <p><strong>Previous Close Price:</strong> {{ $quote['pc'] ?? 'N/A' }}</p>
                            @if($timestamp)
                                <p class="text-xs text-gray-500">Last updated: {{ date('Y-m-d H:i:s', (int)$timestamp) }} UTC</p>
                            @endif

                            <h4 class="text-xl font-semibold mb-2 mt-6">Company Information</h4>
                            <p><strong>Industry:</strong> {{ $profile['finnhubIndustry'] ?? 'N/A' }}</p>
                            <p><strong>Website:</strong> <a href="{{ $profile['weburl'] ?? '#' }}" target="_blank" class="text-blue-500 hover:underline">{{ $profile['weburl'] ?? 'N/A' }}</a></p>
                            <p><strong>Market Cap:</strong> {{ number_format($profile['marketCapitalization'] ?? 0) }}</p>
                            <p><strong>IPO Date:</strong> {{ $profile['ipo'] ?? 'N/A' }}</p>
                            <p><strong>Shares Outstanding:</strong> {{ number_format($profile['shareOutstanding'] ?? 0) }}</p>

                            <div class="mt-8">
                                <form action="{{ route('stocks.destroy', $stock->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove {{ $stock->symbol }} from your portfolio?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                                        Remove from Portfolio
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Right Column: Recent News --}}
                        <div class="flex-1">
                            <h4 class="text-xl font-semibold mb-2">Recent News</h4>
                            @if (!empty($news))
                                <div class="flex flex-col space-y-4">
                                    @foreach ($news as $article)
                                        <div class="border p-4 rounded-lg">
                                            <a href="{{ $article['url'] }}" target="_blank" class="text-blue-500 hover:underline font-bold text-lg">{{ $article['headline'] }}</a>
                                            <p class="text-gray-700 text-sm mt-1">{{ $article['summary'] }}</p>
                                            <p class="text-gray-500 text-xs mt-2">Source: {{ $article['source'] }} - {{ date('Y-m-d H:i', $article['datetime']) }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p>No recent news available for {{ $stock->symbol }}.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
