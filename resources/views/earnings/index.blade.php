<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Upcoming Earnings Reports') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="font-bold text-xl mb-4">Earnings Activity for Next 14 Days</h3>
                    <div class="mb-8">
                        <canvas id="earningsChart" width="400" height="150"></canvas>
                    </div>

                    <h3 class="font-bold text-xl mb-4">Detailed Reports</h3>
                    @if (!empty($earnings))
                        <div class="grid grid-cols-1 gap-4">
                            @foreach ($earnings as $earning)
                                <div class="border p-4 rounded-lg">
                                    {{-- Header section for logo, symbol, and date --}}
                                    <div class="flex items-center mb-2">
                                        @if ($earning['stock_logo'])
                                            <a href="{{ route('stocks.show', $earning['stock_id']) }}" class="mr-3">
                                                <img src="{{ $earning['stock_logo'] }}" alt="{{ $earning['symbol'] }} Logo" class="h-8 w-8">
                                            </a>
                                        @endif
                                        <a href="{{ route('stocks.show', $earning['stock_id']) }}" class="font-bold text-lg text-blue-500 hover:underline">
                                            {{ $earning['symbol'] }} - {{ $earning['date'] }}
                                        </a>
                                    </div>
                                    {{-- Information below the header --}}
                                    <p class="text-gray-700">Time: {{ $earning['hour'] }}</p>
                                    <p class="text-gray-700">EPS Estimate: {{ $earning['epsEstimate'] ?? 'N/A' }}</p>
                                    <p class="text-gray-700">Revenue Estimate: {{ number_format($earning['revenueEstimate'] ?? 0) }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p>No upcoming earnings reports for your saved stocks in the next 20 days.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('earningsChart').getContext('2d');
            const earningsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Number of Earnings Reports',
                        data: @json($chartCounts),
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1,
                        fill: false
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
