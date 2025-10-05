<x-app-layout>
    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <a href="{{ route('accounting.journal-entries.index') }}" class="mr-4 text-gray-600 hover:text-gray-900 transition-colors duration-200">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </a>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">{{ $journalEntry->entry_number }}</h1>
                            <p class="mt-2 text-gray-600">{{ $journalEntry->description }}</p>
                        </div>
                    </div>
                    <div class="flex space-x-4">
                        @if($journalEntry->status === 'DRAFT')
                            <a href="{{ route('accounting.journal-entries.edit', $journalEntry) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                Edit Entry
                            </a>
                            <form method="POST" action="{{ route('accounting.journal-entries.post', $journalEntry) }}" class="inline">
                                @csrf
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                    Post Entry
                                </button>
                            </form>
                        @endif
                        @if($journalEntry->status === 'POSTED')
                            <form method="POST" action="{{ route('accounting.journal-entries.reverse', $journalEntry) }}" class="inline">
                                @csrf
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200" onclick="return confirm('Are you sure you want to reverse this entry?')">
                                    Reverse Entry
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="w-full">
                <!-- Entry Details -->
                <div class="w-full">
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200 mb-8">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Entry Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Entry Number</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $journalEntry->entry_number }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Entry Date</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $journalEntry->entry_date->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Entry Type</label>
                                    <p class="mt-1">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ $journalEntry->entry_type }}
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Status</label>
                                    <p class="mt-1">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($journalEntry->status === 'POSTED') bg-green-100 text-green-800
                                            @elseif($journalEntry->status === 'DRAFT') bg-yellow-100 text-yellow-800
                                            @else bg-red-100 text-red-800
                                            @endif">
                                            {{ $journalEntry->status }}
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Created By</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $journalEntry->creator->name }}</p>
                                </div>
                                @if($journalEntry->approver)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Approved By</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $journalEntry->approver->name }}</p>
                                    </div>
                                @endif
                                @if($journalEntry->posted_at)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Posted At</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $journalEntry->posted_at->format('M d, Y H:i') }}</p>
                                    </div>
                                @endif
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-500">Description</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $journalEntry->description }}</p>
                                </div>
                                @if($journalEntry->notes)
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-500">Notes</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $journalEntry->notes }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Transactions -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Transactions</h3>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Debit</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credit</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($journalEntry->transactions as $transaction)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <a href="{{ route('accounting.chart-of-accounts.show', $transaction->account) }}" class="text-blue-600 hover:text-blue-800">
                                                        {{ $transaction->account->account_code }} - {{ $transaction->account->account_name }}
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
                                        <tr class="bg-gray-50 font-semibold">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" colspan="2">Total</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($journalEntry->total_debit, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($journalEntry->total_credit, 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Entry Summary -->
                <div class="mt-8 bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Entry Summary</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Total Debit</label>
                                <p class="mt-1 text-2xl font-semibold text-gray-900">${{ number_format($journalEntry->total_debit, 2) }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Total Credit</label>
                                <p class="mt-1 text-2xl font-semibold text-gray-900">${{ number_format($journalEntry->total_credit, 2) }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Balance Status</label>
                                <p class="mt-1">
                                    @if($journalEntry->isBalanced())
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            ✓ Balanced
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            ✗ Unbalanced
                                        </span>
                                    @endif
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Number of Transactions</label>
                                <p class="mt-1 text-lg font-medium text-gray-900">{{ $journalEntry->transactions->count() }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Created</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $journalEntry->created_at->format('M d, Y H:i') }}</p>
                            </div>

                            @if($journalEntry->updated_at != $journalEntry->created_at)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Last Updated</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $journalEntry->updated_at->format('M d, Y H:i') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
