<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">General Ledger</h1>
                        <p class="mt-2 text-gray-600">Account transaction details</p>
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

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200 mb-8">
                <div class="p-6">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="account_id" class="block text-sm font-medium text-gray-700 mb-2">Account</label>
                            <select id="account_id" name="account_id" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Select account...</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" {{ $accountId == $account->id ? 'selected' : '' }}>
                                        {{ $account->account_code }} - {{ $account->account_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
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
                                Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- General Ledger Report -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="p-6">
                    @if($accountId)
                        @php
                            $selectedAccount = $accounts->firstWhere('id', $accountId);
                        @endphp

                        <div class="text-center mb-8">
                            <h2 class="text-2xl font-bold text-gray-900">General Ledger</h2>
                            <p class="text-gray-600">{{ $selectedAccount->account_code }} - {{ $selectedAccount->account_name }}</p>
                            <p class="text-gray-500">For the period {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</p>
                        </div>

                        @if($transactions->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Journal Entry</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Debit</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Credit</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @php
                                            $runningBalance = $selectedAccount->opening_balance;
                                        @endphp

                                        <!-- Opening Balance -->
                                        <tr class="bg-blue-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                Opening Balance
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                Beginning balance
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                                -
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                                -
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                                ${{ number_format($runningBalance, 2) }}
                                            </td>
                                        </tr>

                                        @foreach($transactions as $transaction)
                                            @php
                                                if ($selectedAccount->normal_balance === 'DEBIT') {
                                                    $runningBalance += $transaction->debit_amount - $transaction->credit_amount;
                                                } else {
                                                    $runningBalance += $transaction->credit_amount - $transaction->debit_amount;
                                                }
                                            @endphp
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $transaction->journalEntry->entry_date->format('M d, Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <a href="{{ route('accounting.journal-entries.show', $transaction->journalEntry) }}" class="text-blue-600 hover:text-blue-800">
                                                        {{ $transaction->journalEntry->entry_number }}
                                                    </a>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-500">
                                                    {{ $transaction->description }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                                    @if($transaction->debit_amount > 0)
                                                        ${{ number_format($transaction->debit_amount, 2) }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                                    @if($transaction->credit_amount > 0)
                                                        ${{ number_format($transaction->credit_amount, 2) }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                                    ${{ number_format($runningBalance, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Account Summary -->
                            <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-6">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <p class="text-sm font-medium text-gray-500">Opening Balance</p>
                                    <p class="text-lg font-semibold text-gray-900">${{ number_format($selectedAccount->opening_balance, 2) }}</p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <p class="text-sm font-medium text-gray-500">Total Debits</p>
                                    <p class="text-lg font-semibold text-gray-900">${{ number_format($transactions->sum('debit_amount'), 2) }}</p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <p class="text-sm font-medium text-gray-500">Total Credits</p>
                                    <p class="text-lg font-semibold text-gray-900">${{ number_format($transactions->sum('credit_amount'), 2) }}</p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <p class="text-sm font-medium text-gray-500">Ending Balance</p>
                                    <p class="text-lg font-semibold text-gray-900">${{ number_format($runningBalance, 2) }}</p>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-12">
                                <svg class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No Transactions Found</h3>
                                <p class="text-gray-500">No transactions found for the selected account and date range.</p>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-12">
                            <svg class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Select an Account</h3>
                            <p class="text-gray-500">Please select an account to view its general ledger.</p>
                        </div>
                    @endif
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
