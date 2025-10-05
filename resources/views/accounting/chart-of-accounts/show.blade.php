<x-app-layout>
    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <a href="{{ route('accounting.chart-of-accounts.index') }}" class="mr-4 text-gray-600 hover:text-gray-900 transition-colors duration-200">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </a>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">{{ $chartOfAccount->account_name }}</h1>
                            <p class="mt-2 text-gray-600">Account Code: {{ $chartOfAccount->account_code }}</p>
                        </div>
                    </div>
                    <div class="flex space-x-4">
                        <a href="{{ route('accounting.chart-of-accounts.edit', $chartOfAccount) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Edit Account
                        </a>
                        <form method="POST" action="{{ route('accounting.chart-of-accounts.toggle-status', $chartOfAccount) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                {{ $chartOfAccount->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="w-full">
                <!-- Account Details -->
                <div class="w-full">
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200 mb-8">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Account Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Account Code</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $chartOfAccount->account_code }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Account Name</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $chartOfAccount->account_name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Account Type</label>
                                    <p class="mt-1">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($chartOfAccount->account_type === 'ASSET') bg-green-100 text-green-800
                                            @elseif($chartOfAccount->account_type === 'LIABILITY') bg-red-100 text-red-800
                                            @elseif($chartOfAccount->account_type === 'EQUITY') bg-blue-100 text-blue-800
                                            @elseif($chartOfAccount->account_type === 'REVENUE') bg-yellow-100 text-yellow-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ $chartOfAccount->account_type }}
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Account Subtype</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $chartOfAccount->account_subtype }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Normal Balance</label>
                                    <p class="mt-1">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($chartOfAccount->normal_balance === 'DEBIT') bg-blue-100 text-blue-800
                                            @else bg-green-100 text-green-800
                                            @endif">
                                            {{ $chartOfAccount->normal_balance }}
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Level</label>
                                    <p class="mt-1 text-sm text-gray-900">Level {{ $chartOfAccount->level }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Status</label>
                                    <p class="mt-1">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($chartOfAccount->is_active) bg-green-100 text-green-800
                                            @else bg-red-100 text-red-800
                                            @endif">
                                            {{ $chartOfAccount->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">System Account</label>
                                    <p class="mt-1">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($chartOfAccount->is_system_account) bg-blue-100 text-blue-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ $chartOfAccount->is_system_account ? 'Yes' : 'No' }}
                                        </span>
                                    </p>
                                </div>
                                @if($chartOfAccount->description)
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-500">Description</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $chartOfAccount->description }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Account Hierarchy -->
                    @if($chartOfAccount->parentAccount || $chartOfAccount->childAccounts->count() > 0)
                        <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200 mb-8">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Account Hierarchy</h3>

                                @if($chartOfAccount->parentAccount)
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-500 mb-2">Parent Account</label>
                                        <a href="{{ route('accounting.chart-of-accounts.show', $chartOfAccount->parentAccount) }}" class="text-blue-600 hover:text-blue-800">
                                            {{ $chartOfAccount->parentAccount->account_code }} - {{ $chartOfAccount->parentAccount->account_name }}
                                        </a>
                                    </div>
                                @endif

                                @if($chartOfAccount->childAccounts->count() > 0)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-2">Child Accounts</label>
                                        <div class="space-y-2">
                                            @foreach($chartOfAccount->childAccounts as $child)
                                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                                    <a href="{{ route('accounting.chart-of-accounts.show', $child) }}" class="text-blue-600 hover:text-blue-800">
                                                        {{ $child->account_code }} - {{ $child->account_name }}
                                                    </a>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                        @if($child->is_active) bg-green-100 text-green-800
                                                        @else bg-red-100 text-red-800
                                                        @endif">
                                                        {{ $child->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Recent Transactions -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Transactions</h3>

                            @if($chartOfAccount->transactions->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Journal Entry</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Debit</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credit</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($chartOfAccount->transactions->take(10) as $transaction)
                                                <tr>
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
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        @if($transaction->debit_amount > 0)
                                                            ${{ number_format($transaction->debit_amount, 2) }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        @if($transaction->credit_amount > 0)
                                                            ${{ number_format($transaction->credit_amount, 2) }}
                                                        @else
                                                            -
                                                        @endif
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
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Transactions</h3>
                                    <p class="text-gray-500">This account has no transactions yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Account Summary -->
                <div class="mt-8 bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Account Summary</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Opening Balance</label>
                                <p class="mt-1 text-2xl font-semibold text-gray-900">${{ number_format($chartOfAccount->opening_balance, 2) }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Current Balance</label>
                                <p class="mt-1 text-2xl font-semibold text-gray-900">${{ number_format($chartOfAccount->current_balance, 2) }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Calculated Balance</label>
                                <p class="mt-1 text-2xl font-semibold text-gray-900">${{ number_format($chartOfAccount->balance, 2) }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Total Transactions</label>
                                <p class="mt-1 text-lg font-medium text-gray-900">{{ $chartOfAccount->transactions->count() }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Total Debits</label>
                                <p class="mt-1 text-lg font-medium text-gray-900">${{ number_format($chartOfAccount->transactions->sum('debit_amount'), 2) }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Total Credits</label>
                                <p class="mt-1 text-lg font-medium text-gray-900">${{ number_format($chartOfAccount->transactions->sum('credit_amount'), 2) }}</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button onclick="updateBalance()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors duration-200">
                                Update Balance
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateBalance() {
            fetch('{{ route("accounting.chart-of-accounts.update-balance", $chartOfAccount) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    </script>
</x-app-layout>
