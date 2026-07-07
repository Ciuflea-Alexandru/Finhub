<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <a href="{{ route('stocks.search') }}" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Search Stocks
                    </a>
                    <br>
                    <br>

                    <h3 class="font-semibold text-lg">Your Stocks</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Logo
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Symbol
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Name
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Exchange
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Current Price
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Change (24h)
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Last Updated
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                            @foreach ($stocks as $stock)
                                <tr id="stock-{{ $stock['symbol'] }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($stock['logo'])
                                            <a href="{{ route('stocks.show', $stock['id']) }}">
                                                <img src="{{ $stock['logo'] }}" alt="{{ $stock['name'] }} Logo" class="h-8 w-8">
                                            </a>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('stocks.show', $stock['id']) }}" class="text-blue-500 hover:underline">
                                            {{ $stock['symbol'] }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $stock['name'] ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $stock['exchange'] ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap" id="price-{{ $stock['symbol'] }}">
                                        {{ $stock['price'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-bold" id="change-{{ $stock['symbol'] }}">
                                        <span class="{{ $stock['change_percent'] >= 0 ? 'text-green-500' : 'text-red-500' }}">
                                            {{ $stock['change_percent'] }}%
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500" id="timestamp-{{ $stock['symbol'] }}">
                                        {{ date('Y-m-d H:i:s', (int)$stock['timestamp']) }} UTC
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <form action="{{ route('stocks.destroy', $stock['id']) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setInterval(function () {
                fetch('{{ route('api.stocks') }}')
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(stock => {
                            const priceCell = document.getElementById('price-' + stock.symbol);
                            const changeCell = document.getElementById('change-' + stock.symbol);
                            const timestampCell = document.getElementById('timestamp-' + stock.symbol);

                            if (priceCell) {
                                priceCell.textContent = stock.price;
                            }

                            if (changeCell) {
                                const changeSpan = changeCell.querySelector('span');
                                changeSpan.textContent = stock.change_percent + '%';
                                if (stock.change_percent >= 0) {
                                    changeSpan.classList.remove('text-red-500');
                                    changeSpan.classList.add('text-green-500');
                                } else {
                                    changeSpan.classList.remove('text-green-500');
                                    changeSpan.classList.add('text-red-500');
                                }
                            }

                            if (timestampCell) {
                                const date = new Date(stock.timestamp * 1000);
                                timestampCell.textContent = date.toLocaleString();
                            }
                        });
                    });
            }, 10000); // 10000 milliseconds = 10 seconds
        });
    </script>
    @endpush
</x-app-layout>
