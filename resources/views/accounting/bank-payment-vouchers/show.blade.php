<x-app-layout>
    <div class="py-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('Bank Payment Voucher') }}</h1>
                    <p class="mt-2 text-gray-600">{{ __('Review the voucher details below.') }}</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('accounting.bank-payment-vouchers.index') }}"
                       class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50">
                        {{ __('Back to list') }}
                    </a>
                    <button type="button"
                            onclick="window.print()"
                            class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-green-700">
                        🖨️ {{ __('Print') }}
                    </button>
                </div>
            </div>

            <div class="rounded-2xl border-4 border-lime-600 bg-lime-300/70 shadow-xl print:border-2 print:bg-white">
                <div class="border border-black bg-lime-200/60 p-6 print:p-4">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 uppercase tracking-wide">{{ __('Date') }}</label>
                            <div class="mt-2 min-h-[40px] rounded-md border border-black bg-white px-3 py-2 text-base font-semibold text-gray-800">
                                {{ optional($bankPaymentVoucher->payment_date)->format('d-m-Y') ?? '—' }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 uppercase tracking-wide">{{ __('Voucher No. (Auto)') }}</label>
                            <div class="mt-2 min-h-[40px] rounded-md border border-black bg-white px-3 py-2 text-base font-semibold text-gray-800">
                                {{ $bankPaymentVoucher->voucher_number }}
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-900 uppercase tracking-wide">{{ __('Bank') }}</label>
                            <div class="mt-2 min-h-[40px] rounded-md border border-black bg-white px-3 py-2 text-base font-semibold text-gray-800">
                                {{ $bankPaymentVoucher->payment_method ?? '—' }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-center text-lg font-semibold tracking-wide text-red-600">
                        {{ __('Cr in Bank') }}
                    </div>

                    @php
                        $entries = [
                            [
                                'account_code' => '—',
                                'account_name' => optional($bankPaymentVoucher->vendor)->name ?? __('Vendor'),
                                'particulars' => $bankPaymentVoucher->notes ?? __('Payment to vendor'),
                                'type' => 'Dr',
                                'amount' => $bankPaymentVoucher->amount,
                                'cheque_no' => $bankPaymentVoucher->reference_number ?? '—',
                                'cheque_date' => optional($bankPaymentVoucher->payment_date)->format('d-m-Y') ?? '—',
                                'bill_adjustment' => '—',
                            ],
                            [
                                'account_code' => '—',
                                'account_name' => $bankPaymentVoucher->payment_method ? __('Bank (') . $bankPaymentVoucher->payment_method . ')' : __('Bank'),
                                'particulars' => __('Auto credit to bank'),
                                'type' => 'Cr',
                                'amount' => $bankPaymentVoucher->amount,
                                'cheque_no' => '—',
                                'cheque_date' => '—',
                                'bill_adjustment' => '—',
                            ],
                        ];
                        $totalAmount = collect($entries)->reduce(function ($carry, $entry) {
                            return $carry + ($entry['type'] === 'Dr' ? $entry['amount'] : 0);
                        }, 0);
                    @endphp

                    <div class="mt-4 overflow-hidden rounded-md border border-black">
                        <table class="min-w-full divide-y divide-black text-sm font-semibold text-gray-900">
                            <thead class="bg-lime-300/80 uppercase tracking-wide">
                                <tr>
                                    <th scope="col" class="border-r border-black px-3 py-2 text-left">{{ __('Account Code') }}</th>
                                    <th scope="col" class="border-r border-black px-3 py-2 text-left">{{ __('Account Name') }}</th>
                                    <th scope="col" class="border-r border-black px-3 py-2 text-left">{{ __('Particulars') }}</th>
                                    <th scope="col" class="border-r border-black px-3 py-2 text-center">{{ __('Dr/Cr') }}</th>
                                    <th scope="col" class="border-r border-black px-3 py-2 text-right">{{ __('Amount') }}</th>
                                    <th scope="col" class="border-r border-black px-3 py-2 text-left">{{ __('Chq No') }}</th>
                                    <th scope="col" class="border-r border-black px-3 py-2 text-left">{{ __('Chq Date') }}</th>
                                    <th scope="col" class="px-3 py-2 text-left">{{ __('Bill Adjustment') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-lime-100/80">
                                @foreach($entries as $entry)
                                    <tr>
                                        <td class="border-r border-black px-3 py-2 align-top">{{ $entry['account_code'] }}</td>
                                        <td class="border-r border-black px-3 py-2 align-top">{{ $entry['account_name'] }}</td>
                                        <td class="border-r border-black px-3 py-2 align-top">{{ $entry['particulars'] }}</td>
                                        <td class="border-r border-black px-3 py-2 text-center align-top {{ $entry['type'] === 'Dr' ? 'text-red-600' : '' }}">
                                            {{ $entry['type'] }}
                                        </td>
                                        <td class="border-r border-black px-3 py-2 text-right align-top">
                                            {{ number_format($entry['amount'], 2) }}
                                        </td>
                                        <td class="border-r border-black px-3 py-2 align-top">{{ $entry['cheque_no'] }}</td>
                                        <td class="border-r border-black px-3 py-2 align-top">{{ $entry['cheque_date'] }}</td>
                                        <td class="px-3 py-2 align-top">{{ $entry['bill_adjustment'] }}</td>
                                    </tr>
                                @endforeach

                                @for($i = count($entries); $i < 5; $i++)
                                    <tr>
                                        <td class="border-r border-black px-3 py-6">&nbsp;</td>
                                        <td class="border-r border-black px-3 py-6">&nbsp;</td>
                                        <td class="border-r border-black px-3 py-6">&nbsp;</td>
                                        <td class="border-r border-black px-3 py-6">&nbsp;</td>
                                        <td class="border-r border-black px-3 py-6">&nbsp;</td>
                                        <td class="border-r border-black px-3 py-6">&nbsp;</td>
                                        <td class="border-r border-black px-3 py-6">&nbsp;</td>
                                        <td class="px-3 py-6">&nbsp;</td>
                                    </tr>
                                @endfor
                            </tbody>
                            <tfoot class="bg-lime-300/80 text-base font-bold">
                                <tr>
                                    <td colspan="4" class="border-r border-black px-3 py-2 text-right uppercase tracking-wide">{{ __('Total') }}</td>
                                    <td class="border-r border-black px-3 py-2 text-right">
                                        {{ number_format($totalAmount, 2) }}
                                    </td>
                                    <td colspan="3" class="px-3 py-2">&nbsp;</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-8 grid grid-cols-1 gap-6 text-sm font-semibold uppercase tracking-wide text-gray-800 md:grid-cols-3">
                        <div class="flex flex-col gap-6">
                            <div>
                                <div class="min-h-[60px] border-b border-black"></div>
                                <p class="mt-2 text-center">{{ __('Prepared By') }}</p>
                            </div>
                            <div>
                                <div class="min-h-[60px] border-b border-black"></div>
                                <p class="mt-2 text-center">{{ __('Checked By') }}</p>
                            </div>
                        </div>
                        <div class="flex flex-col gap-6">
                            <div>
                                <div class="min-h-[60px] border-b border-black"></div>
                                <p class="mt-2 text-center">{{ __('Accounts Department') }}</p>
                            </div>
                            <div>
                                <div class="min-h-[60px] border-b border-black"></div>
                                <p class="mt-2 text-center">{{ __('Manager Approval') }}</p>
                            </div>
                        </div>
                        <div class="flex flex-col gap-6">
                            <div>
                                <div class="min-h-[60px] border-b border-black"></div>
                                <p class="mt-2 text-center">{{ __('Finance Controller') }}</p>
                            </div>
                            <div>
                                <div class="min-h-[60px] border-b border-black"></div>
                                <p class="mt-2 text-center">{{ __('CEO Approval') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            body {
                background: #fff !important;
            }
            .print\:border-2 {
                border-width: 2px !important;
            }
            .print\:bg-white {
                background-color: #fff !important;
            }
            .print\:p-4 {
                padding: 1rem !important;
            }
            button, a {
                display: none !important;
            }
        }
    </style>
</x-app-layout>
<x-app-layout>
    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Bank Payment Voucher</h1>
                    <p class="mt-2 text-gray-600">Review payment voucher details.</p>
                </div>
                <a href="{{ route('accounting.bank-payment-vouchers.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors duration-200">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to list
                </a>
            </div>

            @if (session('success'))
                <div class="mb-6">
                    <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-700">
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            <div class="bg-white shadow-lg rounded-xl border border-gray-200 overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4 bg-gray-50">
                    <div class="flex flex-wrap items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 uppercase tracking-wide">Voucher Number</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $bankPaymentVoucher->voucher_number }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500 uppercase tracking-wide">Payment Date</p>
                            <p class="text-xl font-semibold text-gray-900">{{ $bankPaymentVoucher->payment_date->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-6 space-y-6">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Information</h2>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Vendor</dt>
                                <dd class="mt-1 text-base text-gray-900">{{ $bankPaymentVoucher->vendor->name ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Amount</dt>
                                <dd class="mt-1 text-base text-gray-900">${{ number_format($bankPaymentVoucher->amount, 2) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Payment Method</dt>
                                <dd class="mt-1 text-base text-gray-900">{{ $bankPaymentVoucher->payment_method ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Reference Number</dt>
                                <dd class="mt-1 text-base text-gray-900">{{ $bankPaymentVoucher->reference_number ?? '—' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-2">Notes</h2>
                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-4 text-gray-700">
                            {!! nl2br(e($bankPaymentVoucher->notes)) ?: '<span class="text-gray-400">No additional notes provided.</span>' !!}
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm text-gray-500">
                        Created on {{ $bankPaymentVoucher->created_at->format('M d, Y \a\t h:i A') }}
                    </div>
                    <div class="text-sm text-gray-500 mt-2 sm:mt-0">
                        Recorded by {{ $bankPaymentVoucher->creator->name ?? 'System' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

