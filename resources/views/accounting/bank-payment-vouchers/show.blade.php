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

