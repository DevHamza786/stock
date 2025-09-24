<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Income Statement</h1>
                        <p class="mt-2 text-gray-600">For the period {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</p>
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
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                            <input type="date" id="start_date" name="start_date" value="{{ $startDate }}" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                            <input type="date" id="end_date" name="end_date" value="{{ $endDate }}" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                Update Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Income Statement Report -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="p-6">
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-bold text-gray-900">Income Statement</h2>
                        <p class="text-gray-600">For the period {{ \Carbon\Carbon::parse($startDate)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('F d, Y') }}</p>
                    </div>

                    <div class="max-w-2xl mx-auto">
                        <!-- Revenue Section -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">REVENUE</h3>
                            <div class="space-y-3">
                                @foreach($revenue as $revenueData)
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <div class="flex items-center">
                                            @if($revenueData['account']->level > 1)
                                                <span class="ml-{{ ($revenueData['account']->level - 1) * 4 }} text-gray-400">└─</span>
                                            @endif
                                            <span class="text-sm text-gray-900">{{ $revenueData['account']->account_name }}</span>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">${{ number_format($revenueData['balance'], 2) }}</span>
                                    </div>
                                @endforeach
                                <div class="flex justify-between items-center py-3 border-t-2 border-gray-300 font-semibold">
                                    <span class="text-lg text-gray-900">TOTAL REVENUE</span>
                                    <span class="text-lg font-bold text-gray-900">${{ number_format($totalRevenue, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Expenses Section -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">EXPENSES</h3>
                            <div class="space-y-3">
                                @foreach($expenses as $expenseData)
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <div class="flex items-center">
                                            @if($expenseData['account']->level > 1)
                                                <span class="ml-{{ ($expenseData['account']->level - 1) * 4 }} text-gray-400">└─</span>
                                            @endif
                                            <span class="text-sm text-gray-900">{{ $expenseData['account']->account_name }}</span>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">${{ number_format($expenseData['balance'], 2) }}</span>
                                    </div>
                                @endforeach
                                <div class="flex justify-between items-center py-3 border-t-2 border-gray-300 font-semibold">
                                    <span class="text-lg text-gray-900">TOTAL EXPENSES</span>
                                    <span class="text-lg font-bold text-gray-900">${{ number_format($totalExpenses, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Net Income -->
                        <div class="border-t-4 border-gray-400 pt-4">
                            <div class="flex justify-between items-center py-4">
                                <span class="text-xl font-bold text-gray-900">NET INCOME</span>
                                <span class="text-xl font-bold {{ $netIncome >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    ${{ number_format($netIncome, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-green-800">Total Revenue</p>
                                    <p class="text-2xl font-semibold text-green-900">${{ number_format($totalRevenue, 2) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-red-800">Total Expenses</p>
                                    <p class="text-2xl font-semibold text-red-900">${{ number_format($totalExpenses, 2) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-blue-800">Net Income</p>
                                    <p class="text-2xl font-semibold {{ $netIncome >= 0 ? 'text-green-600' : 'text-red-600' }}">${{ number_format($netIncome, 2) }}</p>
                                </div>
                            </div>
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
