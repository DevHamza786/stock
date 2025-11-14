@php
    // Separate debit and credit entries for clear double-entry display
    $debitLines = $bankPaymentVoucher->lines->where('entry_type', 'Dr')->sortBy('account.account_code');
    $creditLines = $bankPaymentVoucher->lines->where('entry_type', 'Cr')->sortBy('account.account_code');
    $totals = [
        'debit' => $debitLines->sum('amount'),
        'credit' => $creditLines->sum('amount'),
    ];
@endphp

<x-app-layout>
    <div class="py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-12 xl:px-16 space-y-8">
            <div class="flex items-center gap-3 text-sm text-gray-500">
                <a href="{{ route('accounting.bank-payment-vouchers.index') }}"
                   class="inline-flex items-center gap-2 font-medium text-blue-600 hover:text-blue-700">
                    <span aria-hidden="true" class="text-lg">←</span>
                    {{ __('Bank Payment Vouchers') }}
                </a>
                <span class="text-gray-400">/</span>
                <span>{{ __('Voucher Details') }}</span>
            </div>

            <div class="flex flex-wrap items-start justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('Bank Payment Voucher') }}</h1>
                    <p class="mt-2 text-gray-600">
                        {{ __('Recorded on :date', ['date' => $bankPaymentVoucher->payment_date->format('M d, Y')]) }}
                    </p>
                </div>

            <div class="flex items-center gap-3">
                    <a href="{{ route('accounting.bank-payment-vouchers.index') }}"
                       class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
                        {{ __('Back to list') }}
                    </a>
                    <a href="{{ route('accounting.bank-payment-vouchers.print', $bankPaymentVoucher) }}"
                       target="_blank"
                       class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700">
                        🖨️ {{ __('Print') }}
                    </a>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-xl">
                <div class="border-b border-gray-200 bg-gray-50 px-6 py-5">
                    <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                {{ __('Voucher Number') }}
                            </p>
                            <p class="mt-1 text-xl font-semibold text-gray-900">
                                {{ $bankPaymentVoucher->voucher_number }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                {{ __('Bank Account') }}
                            </p>
                            <p class="mt-1 text-sm font-medium text-gray-900">
                                {{ optional($bankPaymentVoucher->bankAccount)->account_code }}
                                {{ optional($bankPaymentVoucher->bankAccount) ? '— ' . $bankPaymentVoucher->bankAccount->account_name : '—' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                {{ __('Net Amount') }}
                            </p>
                            <p class="mt-1 text-xl font-semibold text-gray-900">
                                {{ number_format($bankPaymentVoucher->amount, 2) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-6 space-y-8">
                    <!-- Double Entry Display -->
                    <div class="grid gap-6 md:grid-cols-2">
                        <!-- Debit Side -->
                        <div class="overflow-hidden rounded-xl border-2 border-green-200 bg-green-50">
                            <div class="bg-green-600 px-4 py-3">
                                <h3 class="text-sm font-bold uppercase tracking-wide text-white">Debit (Dr)</h3>
                            </div>
                            <div class="divide-y divide-green-100">
                                @forelse($debitLines as $line)
                                    <div class="px-4 py-3 hover:bg-green-100 transition">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="font-semibold text-gray-900">
                                                    {{ optional($line->account)->account_code ?? '—' }}
                                                </div>
                                                <div class="text-sm text-gray-600">
                                                    {{ optional($line->account)->account_name ?? '—' }}
                                                </div>
                                                @if($line->particulars)
                                                    <div class="text-xs text-gray-500 mt-1">{{ $line->particulars }}</div>
                                                @endif
                                                @if($line->billPayments->isNotEmpty())
                                                    <div class="mt-2 rounded border border-blue-200 bg-blue-50 px-2 py-1 text-xs text-blue-700">
                                                        @foreach($line->billPayments as $payment)
                                                            <div>{{ optional($payment->bill)->bill_number ?? __('Bill') }}: {{ number_format($payment->amount, 2) }}</div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="text-right">
                                                <div class="text-lg font-bold text-green-700">
                                                    {{ number_format($line->amount, 2) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-4 py-8 text-center text-gray-500">No debit entries</div>
                                @endforelse
                                <div class="bg-green-200 px-4 py-3 border-t-2 border-green-300">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-gray-900">Total Debit:</span>
                                        <span class="text-lg font-bold text-green-800">{{ number_format($totals['debit'], 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Credit Side -->
                        <div class="overflow-hidden rounded-xl border-2 border-red-200 bg-red-50">
                            <div class="bg-red-600 px-4 py-3">
                                <h3 class="text-sm font-bold uppercase tracking-wide text-white">Credit (Cr)</h3>
                            </div>
                            <div class="divide-y divide-red-100">
                                @forelse($creditLines as $line)
                                    <div class="px-4 py-3 hover:bg-red-100 transition">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="font-semibold text-gray-900">
                                                    {{ optional($line->account)->account_code ?? '—' }}
                                                </div>
                                                <div class="text-sm text-gray-600">
                                                    {{ optional($line->account)->account_name ?? '—' }}
                                                </div>
                                                @if($line->particulars)
                                                    <div class="text-xs text-gray-500 mt-1">{{ $line->particulars }}</div>
                                                @endif
                                                @if($line->cheque_no || $line->cheque_date)
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        Cheque: {{ $line->cheque_no ?? '—' }} 
                                                        @if($line->cheque_date)
                                                            ({{ $line->cheque_date->format('M d, Y') }})
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="text-right">
                                                <div class="text-lg font-bold text-red-700">
                                                    {{ number_format($line->amount, 2) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-4 py-8 text-center text-gray-500">No credit entries</div>
                                @endforelse
                                <div class="bg-red-200 px-4 py-3 border-t-2 border-red-300">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-gray-900">Total Credit:</span>
                                        <span class="text-lg font-bold text-red-800">{{ number_format($totals['credit'], 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Table View (for reference) -->
                    <div class="overflow-hidden rounded-xl border border-gray-200">
                        <div class="bg-gray-100 px-4 py-2 border-b border-gray-200">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-600">Detailed Entry List</h3>
                        </div>
                        <table class="min-w-full divide-y divide-gray-200 text-sm font-medium text-gray-900">
                            <thead class="bg-gray-50 uppercase tracking-wide text-xs text-gray-500">
                                <tr>
                                    <th class="px-4 py-3 text-left">{{ __('Account') }}</th>
                                    <th class="px-4 py-3 text-left hidden lg:table-cell">{{ __('Details') }}</th>
                                    <th class="px-4 py-3 text-center">{{ __('Dr/Cr') }}</th>
                                    <th class="px-4 py-3 text-right">{{ __('Amount') }}</th>
                                    <th class="px-4 py-3 text-left hidden xl:table-cell">{{ __('Cheque Info') }}</th>
                                    <th class="px-4 py-3 text-left">{{ __('Bill / Notes') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($bankPaymentVoucher->lines->sortByDesc(fn ($line) => $line->entry_type === 'Dr') as $line)
                                    <tr class="bg-white hover:bg-gray-50 transition">
                                        <td class="px-4 py-3 align-top">
                                            <div class="font-semibold text-gray-900">
                                                {{ optional($line->account)->account_code ?? '—' }}
                                            </div>
                                            <div class="text-sm text-gray-600">
                                                {{ optional($line->account)->account_name ?? '—' }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 align-top hidden lg:table-cell text-sm text-gray-600">
                                            {{ $line->particulars ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 align-top text-center">
                                            <span class="inline-flex min-w-[3rem] items-center justify-center rounded-full px-2.5 py-1 text-xs font-semibold
                                                {{ $line->entry_type === 'Dr' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                {{ $line->entry_type }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 align-top text-right text-sm font-semibold text-gray-900">
                                            {{ number_format($line->amount, 2) }}
                                        </td>
                                        <td class="px-4 py-3 align-top hidden xl:table-cell text-sm text-gray-600">
                                            @if($line->cheque_no || $line->cheque_date)
                                                <div>{{ $line->cheque_no ?? '—' }}</div>
                                                <div class="text-xs text-gray-500">
                                                    {{ optional($line->cheque_date)->format('M d, Y') ?? '—' }}
                                                </div>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 align-top text-sm text-gray-600 space-y-2">
                                            @if($line->billPayments->isNotEmpty())
                                                <div class="rounded border border-blue-100 bg-blue-50 px-3 py-2 text-xs text-blue-700">
                                                    <p class="font-semibold uppercase tracking-wide text-blue-600">
                                                        {{ __('Applied to bill') }}
                                                    </p>
                                                    @foreach($line->billPayments as $payment)
                                                        <div class="mt-1">
                                                            <div>{{ optional($payment->bill)->bill_number ?? __('Bill') }}</div>
                                                            <div class="text-gray-500">
                                                                {{ __('Applied: :amount', ['amount' => number_format($payment->amount, 2)]) }}
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                            <div>{{ $line->bill_adjustment ?? '—' }}</div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-600">
                                {{ __('Reference Number') }}
                            </h2>
                            <p class="mt-2 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-800">
                                {{ $bankPaymentVoucher->reference_number ?? '—' }}
                            </p>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-600">
                                {{ __('Notes') }}
                            </h2>
                            <div class="mt-2 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-800">
                                {!! nl2br(e($bankPaymentVoucher->notes ?? '—')) !!}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 text-sm text-gray-500">
                    <div class="flex flex-col gap-1 md:flex-row md:items-center md:justify-between">
                        <div>
                            {{ __('Created on :date', [
                                'date' => $bankPaymentVoucher->created_at->format('M d, Y \\a\\t h:i A'),
                            ]) }}
                        </div>
                        <div>
                            {{ __('Recorded by :name', ['name' => optional($bankPaymentVoucher->creator)->name ?? __('System')]) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

