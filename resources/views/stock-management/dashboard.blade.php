<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
                <p class="mt-2 text-gray-600">Welcome to StockPro - Your comprehensive stock management solution</p>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Stock Additions -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200 hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Stock Additions</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalStockAdditions) }}</p>
                                <p class="text-xs text-gray-500 mt-1">All time</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Available Stock -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200 hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Available Stock</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($availableStock) }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ number_format($availableSqft, 2) }} sqft</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Production -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200 hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Production</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalDailyProduction) }}</p>
                                <p class="text-xs text-gray-500 mt-1">Pieces produced</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Gate Passes -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200 hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Gate Passes</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalGatePasses) }}</p>
                                <p class="text-xs text-gray-500 mt-1">Dispatches</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200 mb-8">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Quick Actions</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a href="{{ route('stock-management.stock-additions.create') }}" class="group bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold py-3 px-4 rounded-lg text-center transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg">
                            <div class="flex items-center justify-center">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Stock
                            </div>
                        </a>
                        <a href="{{ route('stock-management.stock-issued.create') }}" class="group bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-3 px-4 rounded-lg text-center transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg">
                            <div class="flex items-center justify-center">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                Issue Stock
                            </div>
                        </a>
                        <a href="{{ route('stock-management.daily-production.create') }}" class="group bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold py-3 px-4 rounded-lg text-center transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg">
                            <div class="flex items-center justify-center">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                </svg>
                                Record Production
                            </div>
                        </a>
                        <a href="{{ route('stock-management.gate-pass.create') }}" class="group bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-semibold py-3 px-4 rounded-lg text-center transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg">
                            <div class="flex items-center justify-center">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                Create Gate Pass
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stock Levels by Product -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200 mb-8">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Stock Levels by Product</h3>
                        <a href="{{ route('stock-management.stock-additions.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View All →</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available Pieces</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available Sqft</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($stockLevelsByProduct as $stock)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $stock['product'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($stock['available_pieces']) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($stock['available_sqft'], 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $stock['available_pieces'] > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $stock['available_pieces'] > 0 ? 'In Stock' : 'Out of Stock' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No stock available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recent Stock Additions -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">Recent Stock Additions</h3>
                            <a href="{{ route('stock-management.stock-additions.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View All →</a>
                        </div>
                        <div class="space-y-4">
                            @forelse($recentStockAdditions as $addition)
                                <div class="border-l-4 border-blue-500 pl-4 py-2 hover:bg-gray-50 rounded-r-lg transition-colors duration-200">
                                    <p class="text-sm font-medium text-gray-900">{{ $addition->product->name }}</p>
                                    <p class="text-sm text-gray-600">{{ $addition->mineVendor->name }} - {{ $addition->total_pieces }} pieces</p>
                                    <p class="text-xs text-gray-500">{{ $addition->date->format('M d, Y') }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 text-center py-4">No recent stock additions</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Recent Production -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">Recent Production</h3>
                            <a href="{{ route('stock-management.daily-production.index') }}" class="text-purple-600 hover:text-purple-800 text-sm font-medium">View All →</a>
                        </div>
                        <div class="space-y-4">
                            @forelse($recentDailyProduction as $production)
                                <div class="border-l-4 border-purple-500 pl-4 py-2 hover:bg-gray-50 rounded-r-lg transition-colors duration-200">
                                    <p class="text-sm font-medium text-gray-900">{{ $production->product }}</p>
                                    <p class="text-sm text-gray-600">{{ $production->machine_name }} - {{ $production->operator_name }}</p>
                                    <p class="text-sm text-gray-600">{{ $production->total_pieces }} pieces</p>
                                    <p class="text-xs text-gray-500">{{ $production->date->format('M d, Y') }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 text-center py-4">No recent production</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Production Chart -->
            @if($monthlyProduction->count() > 0)
            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200 mt-8">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Monthly Production Trend</h3>
                    <div class="h-64">
                        <canvas id="productionChart"></canvas>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    @if($monthlyProduction->count() > 0)
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('productionChart').getContext('2d');
        const productionData = @json($monthlyProduction);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: productionData.map(item => item.month),
                datasets: [{
                    label: 'Pieces Produced',
                    data: productionData.map(item => item.total_pieces),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }, {
                    label: 'Sqft Produced',
                    data: productionData.map(item => item.total_sqft),
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    tension: 0.1,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });
    </script>
    @endif
</x-app-layout>
