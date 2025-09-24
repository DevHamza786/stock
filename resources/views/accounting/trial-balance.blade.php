<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Trial Balance</h1>
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

            <!-- Trial Balance Report -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="p-6">
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-bold text-gray-900">Trial Balance</h2>
                        <p class="text-gray-600">As of {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Type</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Debit Balance</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Credit Balance</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($accounts as $accountData)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $accountData['account']->account_code }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <div class="flex items-center">
                                                @if($accountData['account']->level > 1)
                                                    <span class="ml-{{ ($accountData['account']->level - 1) * 4 }} text-gray-400">└─</span>
                                                @endif
                                                {{ $accountData['account']->account_name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                @if($accountData['account']->account_type === 'ASSET') bg-green-100 text-green-800
                                                @elseif($accountData['account']->account_type === 'LIABILITY') bg-red-100 text-red-800
                                                @elseif($accountData['account']->account_type === 'EQUITY') bg-blue-100 text-blue-800
                                                @elseif($accountData['account']->account_type === 'REVENUE') bg-yellow-100 text-yellow-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ $accountData['account']->account_type }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                            @if($accountData['debit_balance'] > 0)
                                                ${{ number_format($accountData['debit_balance'], 2) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                            @if($accountData['credit_balance'] > 0)
                                                ${{ number_format($accountData['credit_balance'], 2) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                <tr class="bg-gray-100 font-semibold">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" colspan="3">TOTAL</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">${{ number_format($totalDebits, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">${{ number_format($totalCredits, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Balance Check -->
                    <div class="mt-8 p-4 rounded-lg {{ abs($totalDebits - $totalCredits) < 0.01 ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                        <div class="flex items-center">
                            @if(abs($totalDebits - $totalCredits) < 0.01)
                                <svg class="h-5 w-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-green-800 font-medium">Trial Balance is Balanced</span>
                            @else
                                <svg class="h-5 w-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-red-800 font-medium">Trial Balance is Unbalanced - Difference: ${{ number_format(abs($totalDebits - $totalCredits), 2) }}</span>
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
