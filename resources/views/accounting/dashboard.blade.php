<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Accounting Dashboard</h1>
                        <p class="mt-2 text-gray-600">Manage your Chart of Accounts and Financial Transactions</p>
                    </div>
                    <div class="flex space-x-4">
                        <a href="{{ route('accounting.chart-of-accounts.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Add Account
                        </a>
                        <a href="{{ route('accounting.journal-entries.create') }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            New Journal Entry
                        </a>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Accounts</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $totalAccounts }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Journal Entries</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $totalJournalEntries }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Draft Entries</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $draftEntries }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Posted Entries</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $postedEntries }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Balances by Type -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200 mb-8">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Account Balances by Type</h3>
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        @foreach(['ASSET', 'LIABILITY', 'EQUITY', 'REVENUE', 'EXPENSE'] as $type)
                            <div class="text-center">
                                <p class="text-sm font-medium text-gray-500">{{ ucfirst(strtolower($type)) }}</p>
                                <p class="text-xl font-semibold text-gray-900">
                                    ${{ number_format($accountBalances->get($type)?->total_balance ?? 0, 2) }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Chart of Accounts -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Chart of Accounts</h3>
                            <a href="{{ route('accounting.chart-of-accounts.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View All</a>
                        </div>
                        <div class="space-y-2">
                            <a href="{{ route('accounting.chart-of-accounts.create') }}" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg">
                                + Add New Account
                            </a>
                            <a href="{{ route('accounting.chart-of-accounts.index', ['account_type' => 'ASSET']) }}" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg">
                                View Assets
                            </a>
                            <a href="{{ route('accounting.chart-of-accounts.index', ['account_type' => 'LIABILITY']) }}" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg">
                                View Liabilities
                            </a>
                            <a href="{{ route('accounting.chart-of-accounts.index', ['account_type' => 'EQUITY']) }}" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg">
                                View Equity
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Financial Reports -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Financial Reports</h3>
                        </div>
                        <div class="space-y-2">
                            <a href="{{ route('accounting.trial-balance') }}" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg">
                                Trial Balance
                            </a>
                            <a href="{{ route('accounting.balance-sheet') }}" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg">
                                Balance Sheet
                            </a>
                            <a href="{{ route('accounting.income-statement') }}" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg">
                                Income Statement
                            </a>
                            <a href="{{ route('accounting.general-ledger') }}" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg">
                                General Ledger
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Journal Entries -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Recent Journal Entries</h3>
                        <a href="{{ route('accounting.journal-entries.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View All</a>
                    </div>

                    @if($recentEntries->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entry #</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($recentEntries as $entry)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <a href="{{ route('accounting.journal-entries.show', $entry) }}" class="text-blue-600 hover:text-blue-800">
                                                    {{ $entry->entry_number }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $entry->entry_date->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                {{ Str::limit($entry->description, 50) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $entry->entry_type }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    @if($entry->status === 'POSTED') bg-green-100 text-green-800
                                                    @elseif($entry->status === 'DRAFT') bg-yellow-100 text-yellow-800
                                                    @else bg-red-100 text-red-800
                                                    @endif">
                                                    {{ $entry->status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                ${{ number_format($entry->total_debit, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Journal Entries</h3>
                            <p class="text-gray-500 mb-4">Get started by creating your first journal entry.</p>
                            <a href="{{ route('accounting.journal-entries.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                Create Journal Entry
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
