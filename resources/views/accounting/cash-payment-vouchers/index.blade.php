<x-app-layout>
    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-12 xl:px-16 space-y-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('Cash Payment Vouchers') }}</h1>
                    <p class="mt-2 text-gray-600">{{ __('Review cash disbursements and their ledger allocations.') }}</p>
                </div>
                <a href="{{ route('accounting.cash-payment-vouchers.create') }}"
                   class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    {{ __('New Cash Voucher') }}
                </a>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-xl">
                <div class="relative overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-900">
                        <thead class="bg-gray-50 uppercase tracking-wide text-xs text-gray-500">
                            <tr>
                                <th class="px-6 py-3 text-left">{{ __('Voucher #') }}</th>
                                <th class="px-6 py-3 text-left">{{ __('Payment Date') }}</th>
                                <th class="px-6 py-3 text-left">{{ __('Cash Account') }}</th>
                                <th class="px-6 py-3 text-right">{{ __('Amount') }}</th>
                                <th class="px-6 py-3 text-left">{{ __('Reference #') }}</th>
                                <th class="px-6 py-3 text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($vouchers as $voucher)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-3 font-semibold text-gray-900">
                                        {{ $voucher->voucher_number }}
                                    </td>
                                    <td class="px-6 py-3 text-gray-700">
                                        {{ $voucher->payment_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-3 text-gray-700">
                                        {{ optional($voucher->cashAccount)->account_code }}
                                        {{ optional($voucher->cashAccount) ? '— ' . $voucher->cashAccount->account_name : '—' }}
                                    </td>
                                    <td class="px-6 py-3 text-right font-semibold text-gray-900">
                                        {{ number_format($voucher->amount, 2) }}
                                    </td>
                                    <td class="px-6 py-3 text-gray-700">
                                        {{ $voucher->reference_number ?? '—' }}
                                    </td>
                                    <td class="px-6 py-3 text-right text-sm">
                                        <span class="text-gray-400 text-xs">{{ __('Saved') }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        {{ __('No cash payment vouchers recorded yet.') }}
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

