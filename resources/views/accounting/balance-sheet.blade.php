<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Balance Sheet</h1>
                        <p class="mt-2 text-gray-600">As of {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</p>
                    </div>
                    <div class="flex space-x-4">
                        <a href="{{ route('accounting.dashboard') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Back to Dashboard
                        </a>
                        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Print Report
                        </button>
                    </div>
                </div>
            </div>

            <!-- Date Filter -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200 mb-8">
                <div class="p-6">
                    <form method="GET" class="flex items-end space-x-4">
                        <div>
                            <label for="date" class="block text-sm font-medium text-gray-700 mb-2">As of Date</label>
                            <input type="date" id="date" name="date" value="{{ $date }}" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                Update Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Balance Sheet Report -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="p-6">
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-bold text-gray-900">Balance Sheet</h2>
                        <p class="text-gray-600">As of {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</p>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Assets -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">ASSETS</h3>
                            <div class="space-y-4">
                                @foreach($assets as $assetData)
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <div class="flex items-center">
                                            @if($assetData['account']->level > 1)
                                                <span class="ml-{{ ($assetData['account']->level - 1) * 4 }} text-gray-400">└─</span>
                                            @endif
                                            <span class="text-sm text-gray-900">{{ $assetData['account']->account_name }}</span>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">${{ number_format($assetData['balance'], 2) }}</span>
                                    </div>
                                @endforeach
                                <div class="flex justify-between items-center py-3 border-t-2 border-gray-300 font-semibold">
                                    <span class="text-lg text-gray-900">TOTAL ASSETS</span>
                                    <span class="text-lg font-bold text-gray-900">${{ number_format($totalAssets, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Liabilities & Equity -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">LIABILITIES & EQUITY</h3>

                            <!-- Liabilities -->
                            <div class="mb-6">
                                <h4 class="text-md font-medium text-gray-700 mb-3">LIABILITIES</h4>
                                <div class="space-y-4">
                                    @foreach($liabilities as $liabilityData)
                                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                            <div class="flex items-center">
                                                @if($liabilityData['account']->level > 1)
                                                    <span class="ml-{{ ($liabilityData['account']->level - 1) * 4 }} text-gray-400">└─</span>
                                                @endif
                                                <span class="text-sm text-gray-900">{{ $liabilityData['account']->account_name }}</span>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">${{ number_format($liabilityData['balance'], 2) }}</span>
                                        </div>
                                    @endforeach
                                    <div class="flex justify-between items-center py-2 border-t border-gray-200 font-medium">
                                        <span class="text-sm text-gray-900">Total Liabilities</span>
                                        <span class="text-sm font-semibold text-gray-900">${{ number_format($totalLiabilities, 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Equity -->
                            <div>
                                <h4 class="text-md font-medium text-gray-700 mb-3">EQUITY</h4>
                                <div class="space-y-4">
                                    @foreach($equity as $equityData)
                                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                            <div class="flex items-center">
                                                @if($equityData['account']->level > 1)
                                                    <span class="ml-{{ ($equityData['account']->level - 1) * 4 }} text-gray-400">└─</span>
                                                @endif
                                                <span class="text-sm text-gray-900">{{ $equityData['account']->account_name }}</span>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">${{ number_format($equityData['balance'], 2) }}</span>
                                        </div>
                                    @endforeach
                                    <div class="flex justify-between items-center py-2 border-t border-gray-200 font-medium">
                                        <span class="text-sm text-gray-900">Total Equity</span>
                                        <span class="text-sm font-semibold text-gray-900">${{ number_format($totalEquity, 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-between items-center py-3 border-t-2 border-gray-300 font-semibold mt-4">
                                <span class="text-lg text-gray-900">TOTAL LIABILITIES & EQUITY</span>
                                <span class="text-lg font-bold text-gray-900">${{ number_format($totalLiabilities + $totalEquity, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Balance Check -->
                    <div class="mt-8 p-4 rounded-lg {{ abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01 ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                        <div class="flex items-center">
                            @if(abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01)
                                <svg class="h-5 w-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-green-800 font-medium">Balance Sheet is Balanced</span>
                            @else
                                <svg class="h-5 w-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-red-800 font-medium">Balance Sheet is Unbalanced - Difference: ${{ number_format(abs($totalAssets - ($totalLiabilities + $totalEquity)), 2) }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</x-app-layout>
