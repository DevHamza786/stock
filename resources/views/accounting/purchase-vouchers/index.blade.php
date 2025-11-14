<x-app-layout>
    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-12 xl:px-16 space-y-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('Purchase Book Vouchers') }}</h1>
                    <p class="mt-2 text-gray-600">{{ __('Track bills posted to payables and ready for settlement.') }}</p>
                </div>
                <a href="{{ route('accounting.purchase-vouchers.create') }}"
                   class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    {{ __('New Purchase Voucher') }}
                </a>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200 mb-8">
                <div class="p-6">
                    <form method="GET" class="flex gap-4">
                        <div class="flex-1">
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select id="status" name="status" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">{{ __('All Status') }}</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                                <option value="posted" {{ request('status') == 'posted' ? 'selected' : '' }}>{{ __('Posted') }}</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                {{ __('Apply') }}
                            </button>
                            <a href="{{ route('accounting.purchase-vouchers.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition-colors duration-200">
                                {{ __('Clear') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-xl">
                <div class="relative overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-900">
                        <thead class="bg-gray-50 uppercase tracking-wide text-xs text-gray-500">
                            <tr>
                                <th class="px-6 py-3 text-left">{{ __('Voucher #') }}</th>
                                <th class="px-6 py-3 text-left">{{ __('Bill Date') }}</th>
                                <th class="px-6 py-3 text-left">{{ __('Payable Ledger') }}</th>
                                <th class="px-6 py-3 text-left">{{ __('Vendor Reference') }}</th>
                                <th class="px-6 py-3 text-right">{{ __('Amount') }}</th>
                                <th class="px-6 py-3 text-left">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-left">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($vouchers as $voucher)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-3 font-semibold text-gray-900">
                                        {{ $voucher->voucher_number }}
                                    </td>
                                    <td class="px-6 py-3 text-gray-700">
                                        {{ optional($voucher->bill)->bill_date?->format('M d, Y') ?? '—' }}
                                    </td>
                                    <td class="px-6 py-3 text-gray-700">
                                        {{ optional($voucher->bill?->account)->account_code }}
                                        {{ optional($voucher->bill?->account) ? '— ' . $voucher->bill->account->account_name : '—' }}
                                    </td>
                                    <td class="px-6 py-3 text-gray-700">
                                        {{ $voucher->bill->vendor_reference ?? '—' }}
                                    </td>
                                    <td class="px-6 py-3 text-right font-semibold text-gray-900">
                                        {{ number_format($voucher->total_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-3">
                                        @if($voucher->status === 'draft')
                                            <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                                                {{ __('Draft') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                                {{ __('Posted') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3">
                                        @if($voucher->status === 'draft')
                                            <a href="{{ route('accounting.purchase-vouchers.edit', $voucher) }}" 
                                               class="text-blue-600 hover:text-blue-900 transition-colors duration-200">
                                                {{ __('Edit') }}
                                            </a>
                                        @else
                                            <span class="text-gray-400">{{ __('—') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        {{ __('No purchase vouchers recorded yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200 bg-gray-50 px-6 py-4">
                    {{ $vouchers->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

