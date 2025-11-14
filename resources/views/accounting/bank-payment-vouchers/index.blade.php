<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Bank Payment Vouchers</h1>
                    <p class="mt-2 text-gray-600">Review recorded bank payments and their ledger allocations.</p>
                </div>
                <a href="{{ route('accounting.bank-payment-vouchers.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200 shadow-sm hover:shadow-md">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Voucher
                </a>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200 mb-8">
                <div class="p-6">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="voucher_type" class="block text-sm font-medium text-gray-700 mb-2">Voucher Type</label>
                            <select id="voucher_type" name="voucher_type" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="bank" {{ request('voucher_type') == 'bank' ? 'selected' : '' }}>Bank Payment</option>
                                <option value="cash" {{ request('voucher_type') == 'cash' ? 'selected' : '' }}>Cash Payment</option>
                                <option value="all" {{ request('voucher_type') == 'all' || !request('voucher_type') ? 'selected' : '' }}>All Vouchers</option>
                            </select>
                        </div>
                        <div>
                            <label for="bank_account_id" class="block text-sm font-medium text-gray-700 mb-2">Bank Account</label>
                            <select id="bank_account_id" name="bank_account_id" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">All Banks</option>
                                @foreach($bankAccounts as $bankAccount)
                                    <option value="{{ $bankAccount->id }}" {{ request('bank_account_id') == $bankAccount->id ? 'selected' : '' }}>
                                        {{ $bankAccount->account_code }} — {{ $bankAccount->account_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                            <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                            <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div class="md:col-span-4 flex gap-2">
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                Apply Filters
                            </button>
                            <a href="{{ route('accounting.bank-payment-vouchers.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition-colors duration-200">
                                Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white shadow-lg rounded-xl border border-gray-200 overflow-hidden">
                <div class="relative overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Voucher #</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bank Account</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Net Amount</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference #</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($vouchers as $voucher)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        {{ $voucher->voucher_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $voucher->payment_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ optional($voucher->bankAccount)->account_code }} {{ optional($voucher->bankAccount) ? '— ' . $voucher->bankAccount->account_name : '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-semibold">
                                        {{ number_format($voucher->amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $voucher->reference_number ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('accounting.bank-payment-vouchers.show', $voucher) }}" class="text-blue-600 hover:text-blue-900 transition-colors duration-200">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <svg class="h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                            </svg>
                                            <p class="text-lg font-medium text-gray-700">No vouchers recorded yet</p>
                                            <p class="mt-1 text-sm text-gray-500">Start by creating a new bank payment voucher.</p>
                                            <a href="{{ route('accounting.bank-payment-vouchers.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                                Create Voucher
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $vouchers->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

