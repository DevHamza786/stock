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
                                    <td class="px-6 py-3 text-gray-700">
                                        {{ strtoupper($voucher->bill->status ?? 'open') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
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

