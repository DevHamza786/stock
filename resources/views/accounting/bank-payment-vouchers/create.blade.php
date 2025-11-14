@php
    $defaultLines = [
        [
            'account_id' => null,
            'type' => 'Dr',
            'amount' => null,
            'particulars' => null,
            'cheque_no' => null,
            'cheque_date' => null,
            'bill_id' => null,
            'bill_amount' => null,
            'bill_adjustment' => null,
        ],
    ];

    $oldLines = collect(old('lines', $defaultLines))->values();

    $vendorBillOptions = $vendorBills->mapWithKeys(function ($bills, $accountId) {
        return [
            $accountId => $bills->map(function ($bill) {
                return [
                    'id' => $bill->id,
                    'label' => trim(
                        ($bill->bill_number ?: __('Bill')) .
                        ($bill->vendor_reference ? ' • ' . $bill->vendor_reference : '') .
                        ' • ' . number_format($bill->balance_amount, 2)
                    ),
                    'balance' => (float) $bill->balance_amount,
                    'bill_number' => $bill->bill_number,
                    'reference' => $bill->vendor_reference,
                    'bill_date' => optional($bill->bill_date)->format('Y-m-d'),
                ];
            })->values(),
        ];
    })->toArray();
@endphp

<x-app-layout>
    <div class="py-10">
        <div class="max-w-none mx-auto px-4 sm:px-6 lg:px-12 xl:px-16 space-y-8">
            <div class="flex items-center gap-3 text-sm text-gray-500">
                <a href="{{ route('accounting.bank-payment-vouchers.index') }}"
                   class="inline-flex items-center gap-2 font-medium text-blue-600 hover:text-blue-700">
                    <span aria-hidden="true" class="text-lg">←</span>
                    {{ __('Back to vouchers') }}
                </a>
                <span class="text-gray-400">/</span>
                <span>{{ __('Create Bank Payment Voucher') }}</span>
            </div>

            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('Create Bank Payment/Receipt Voucher') }}</h1>
                <p class="mt-2 text-gray-600">
                    {{ __('Select the voucher type, bank account, and allocate the counter ledger entries below. Payable ledgers can be knocked off against open bills.') }}
                </p>
            </div>

            <form id="bankVoucherForm" method="POST" action="{{ route('accounting.bank-payment-vouchers.store') }}">
                @csrf

                <input type="hidden" name="amount" id="totalAmountInput" value="{{ old('amount', '0.00') }}">
                <input type="hidden" name="voucher_type" id="voucher_type" value="{{ old('voucher_type', $voucherType ?? 'payment') }}">

                <div class="rounded-2xl border border-gray-200 bg-white shadow-xl">
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-5">
                        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                            <div>
                                <label for="voucher_type_select" class="block text-sm font-semibold uppercase tracking-wide text-gray-700">
                                    {{ __('Voucher Type') }}
                                </label>
                                <select
                                    id="voucher_type_select"
                                    class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-base font-medium text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                                    <option value="payment" {{ old('voucher_type', $voucherType ?? 'payment') === 'payment' ? 'selected' : '' }}>
                                        {{ __('Payment') }}
                                    </option>
                                    <option value="receipt" {{ old('voucher_type', $voucherType ?? 'payment') === 'receipt' ? 'selected' : '' }}>
                                        {{ __('Receipt') }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label for="payment_date" class="block text-sm font-semibold uppercase tracking-wide text-gray-700">
                                    {{ __('Payment Date') }}
                                </label>
                                <input
                                    type="date"
                                    id="payment_date"
                                    name="payment_date"
                                    value="{{ old('payment_date', now()->format('Y-m-d')) }}"
                                    required
                                    class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-base font-medium text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold uppercase tracking-wide text-gray-700">
                                    {{ __('Voucher No.') }}
                                </label>
                                <div class="mt-2 flex h-[42px] items-center rounded-lg border border-gray-300 bg-gray-100 px-3 text-base font-semibold text-gray-900 shadow-inner" id="voucher_number_display">
                                    {{ $nextVoucherNumber }}
                                </div>
                            </div>

                            <div>
                                <label for="bank_account_id" class="block text-sm font-semibold uppercase tracking-wide text-gray-700">
                                    {{ __('Bank Account') }}
                                </label>
                                <select
                                    id="bank_account_id"
                                    name="bank_account_id"
                                    required
                                    class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-base font-medium text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                                    <option value="">{{ __('Select bank account') }}</option>
                                    @foreach($bankAccounts as $bankAccount)
                                        <option value="{{ $bankAccount->id }}" {{ (int) old('bank_account_id') === $bankAccount->id ? 'selected' : '' }}>
                                            {{ $bankAccount->account_code }} — {{ $bankAccount->account_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-6 space-y-6">
                        <div class="overflow-hidden rounded-xl border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200 text-sm font-medium text-gray-900">
                                <thead class="bg-gray-100 uppercase tracking-wide text-xs text-gray-500">
                                    <tr>
                                        <th class="border-r border-gray-200 px-3 py-2 text-left">{{ __('Account') }}</th>
                                        <th class="border-r border-gray-200 px-3 py-2 text-left hidden md:table-cell">{{ __('Account Name') }}</th>
                                        <th class="border-r border-gray-200 px-3 py-2 text-left">{{ __('Particulars') }}</th>
                                        <th class="border-r border-gray-200 px-3 py-2 text-center">{{ __('Dr/Cr') }}</th>
                                        <th class="border-r border-gray-200 px-3 py-2 text-right">{{ __('Amount') }}</th>
                                        <th class="border-r border-gray-200 px-3 py-2 text-left">{{ __('Cheque No') }}</th>
                                        <th class="border-r border-gray-200 px-3 py-2 text-left hidden lg:table-cell">{{ __('Cheque Date') }}</th>
                                        <th class="px-3 py-2 text-left">{{ __('Bill / Notes') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="voucherEntryRows" class="divide-y divide-gray-100">
                                    @foreach($oldLines as $index => $line)
                                        @php
                                            $selectedAccount = $accounts->firstWhere('id', (int) ($line['account_id'] ?? 0));
                                            $isPayable = $selectedAccount && $selectedAccount->account_subtype === 'ACCOUNTS_PAYABLE';
                                            $initialBillId = $line['bill_id'] ?? null;
                                            $initialBillAmount = $line['bill_amount'] ?? null;
                                        @endphp
                                        <tr data-row-index="{{ $index }}" class="entry-row">
                                            <td class="border-r border-gray-200 align-top px-3 py-3">
                                                <select
                                                    name="lines[{{ $index }}][account_id]"
                                                    data-field="account_id"
                                                    class="account-select w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                                                >
                                                    <option value="">{{ __('Select ledger') }}</option>
                                                    @foreach($accounts as $account)
                                                        <option
                                                            value="{{ $account->id }}"
                                                            data-name="{{ $account->account_name }}"
                                                            data-code="{{ $account->account_code }}"
                                                            data-payable="{{ $account->account_subtype === 'ACCOUNTS_PAYABLE' ? '1' : '0' }}"
                                                            {{ (int) ($line['account_id'] ?? 0) === $account->id ? 'selected' : '' }}
                                                        >
                                                            {{ $account->account_code }} — {{ $account->account_name }}
                                                        </option>
                                                    @endforeach
                                                    @foreach($vendors as $vendor)
                                                        @if($vendor->chartOfAccount)
                                                            <option
                                                                value="{{ $vendor->chartOfAccount->id }}"
                                                                data-name="{{ $vendor->name }}"
                                                                data-code="{{ $vendor->chartOfAccount->account_code }}"
                                                                data-payable="1"
                                                                {{ (int) ($line['account_id'] ?? 0) === $vendor->chartOfAccount->id ? 'selected' : '' }}
                                                            >
                                                                {{ $vendor->chartOfAccount->account_code }} — {{ $vendor->name }} ({{ __('Vendor') }})
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                <input
                                                    type="hidden"
                                                    data-field="account_name"
                                                    value="{{ $line['account_name'] ?? ($selectedAccount->account_name ?? '') }}"
                                                >
                                                <div class="mt-2 text-xs text-gray-500">
                                                    <span class="account-code-display">{{ $selectedAccount->account_code ?? '—' }}</span>
                                                </div>
                                            </td>
                                            <td class="border-r border-gray-200 align-top px-3 py-3 hidden md:table-cell">
                                                <input
                                                    type="text"
                                                    class="account-name-input w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                                                    value="{{ $line['account_name'] ?? ($selectedAccount->account_name ?? '') }}"
                                                    placeholder="{{ __('Account name') }}"
                                                    readonly
                                                >
                                            </td>
                                            <td class="border-r border-gray-200 align-top px-3 py-3">
                                                <textarea
                                                    name="lines[{{ $index }}][particulars]"
                                                    data-field="particulars"
                                                    rows="2"
                                                    placeholder="{{ __('Describe what this payment covers...') }}"
                                                    class="w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                                                >{{ $line['particulars'] }}</textarea>
                                            </td>
                                            <td class="border-r border-gray-200 align-top px-3 py-3 text-center">
                                                <select
                                                    name="lines[{{ $index }}][type]"
                                                    data-field="type"
                                                    class="drcr-select rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                                                >
                                                    <option value="Dr" {{ ($line['type'] ?? 'Dr') === 'Dr' ? 'selected' : '' }}>Dr</option>
                                                    <option value="Cr" {{ ($line['type'] ?? '') === 'Cr' ? 'selected' : '' }}>Cr</option>
                                                </select>
                                            </td>
                                            <td class="border-r border-gray-200 align-top px-3 py-3 text-right">
                                                <input
                                                    type="number"
                                                    name="lines[{{ $index }}][amount]"
                                                    data-field="amount"
                                                    value="{{ $line['amount'] }}"
                                                    step="0.01"
                                                    min="0"
                                                    placeholder="0.00"
                                                    class="amount-input w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm text-right focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                                                >
                                            </td>
                                            <td class="border-r border-gray-200 align-top px-3 py-3">
                                                <input
                                                    type="text"
                                                    name="lines[{{ $index }}][cheque_no]"
                                                    data-field="cheque_no"
                                                    value="{{ $line['cheque_no'] }}"
                                                    placeholder="{{ __('Cheque no.') }}"
                                                    class="w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                                                >
                                            </td>
                                            <td class="border-r border-gray-200 align-top px-3 py-3 hidden lg:table-cell">
                                                <input
                                                    type="date"
                                                    name="lines[{{ $index }}][cheque_date]"
                                                    data-field="cheque_date"
                                                    value="{{ $line['cheque_date'] }}"
                                                    class="w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                                                >
                                            </td>
                                            <td class="align-top px-3 py-3">
                                                <div class="space-y-3">
                                                    <div class="bill-allocation {{ $isPayable ? '' : 'hidden' }}">
                                                        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-600">
                                                            {{ __('Apply to Bill') }}
                                                        </label>
                                                        <select
                                                            class="bill-select mt-1 w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                                                            data-field="bill_id"
                                                            data-initial-value="{{ $initialBillId }}"
                                                        >
                                                            <option value="">{{ __('Select outstanding bill') }}</option>
                                                        </select>
                                                        <input
                                                            type="number"
                                                            class="bill-amount-input mt-2 w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                                                            data-field="bill_amount"
                                                            data-initial-value="{{ $initialBillAmount }}"
                                                            step="0.01"
                                                            min="0"
                                                            placeholder="{{ __('Amount to apply') }}"
                                                        >
                                                        <p class="bill-balance text-xs text-gray-500"></p>
                                                    </div>

                                                    <div>
                                                        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-600">
                                                            {{ __('Notes') }}
                                                        </label>
                                                        <input
                                                            type="text"
                                                            name="lines[{{ $index }}][bill_adjustment]"
                                                            data-field="bill_adjustment"
                                                            value="{{ $line['bill_adjustment'] }}"
                                                            placeholder="{{ __('Optional memo') }}"
                                                            class="w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                                                        >
                                                    </div>

                                                    <button
                                                        type="button"
                                                        class="remove-row inline-flex h-8 w-8 items-center justify-center rounded-full bg-red-500 text-white shadow hover:bg-red-600"
                                                        title="{{ __('Remove row') }}"
                                                    >×</button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-100 text-base font-semibold">
                                    <tr>
                                        <td colspan="4" class="border-r border-gray-200 px-3 py-2 text-right uppercase tracking-wide text-gray-600">
                                            {{ __('Total (Dr)') }}
                                        </td>
                                        <td class="border-r border-gray-200 px-3 py-2 text-right">
                                            <span id="totalAmountDisplay">{{ number_format(old('amount', 0), 2) }}</span>
                                        </td>
                                        <td colspan="3" class="px-3 py-2 text-right">
                                            <button
                                                type="button"
                                                id="addEntryRow"
                                                class="inline-flex items-center rounded border border-gray-300 bg-white px-3 py-1 text-sm font-semibold text-blue-600 shadow-sm transition hover:bg-blue-50"
                                            >
                                                {{ __('+ Add Row') }}
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <label for="reference_number" class="block text-sm font-semibold uppercase tracking-wide text-gray-700">
                                    {{ __('Reference Number') }}
                                </label>
                                <input
                                    type="text"
                                    id="reference_number"
                                    name="reference_number"
                                    value="{{ old('reference_number') }}"
                                    placeholder="{{ __('Bank or cheque reference') }}"
                                    class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-base font-medium text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                            </div>
                            <div>
                                <label for="notes" class="block text-sm font-semibold uppercase tracking-wide text-gray-700">
                                    {{ __('Notes') }}
                                </label>
                                <textarea
                                    id="notes"
                                    name="notes"
                                    rows="3"
                                    placeholder="{{ __('Additional details about this payment...') }}"
                                    class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-base font-medium text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                >{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-center justify-end gap-3">
                    <a href="{{ route('accounting.bank-payment-vouchers.index') }}"
                       class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
                        {{ __('Cancel') }}
                    </a>
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        {{ __('Save Voucher') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <template id="voucher-row-template">
        <tr class="entry-row">
            <td class="border-r border-gray-200 align-top px-3 py-3">
                <select
                    data-field="account_id"
                    class="account-select w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                >
                    <option value="">{{ __('Select ledger') }}</option>
                    @foreach($accounts as $account)
                        <option
                            value="{{ $account->id }}"
                            data-name="{{ $account->account_name }}"
                            data-code="{{ $account->account_code }}"
                            data-payable="{{ $account->account_subtype === 'ACCOUNTS_PAYABLE' ? '1' : '0' }}"
                        >
                            {{ $account->account_code }} — {{ $account->account_name }}
                        </option>
                    @endforeach
                </select>
                <input type="hidden" data-field="account_name">
                <div class="mt-2 text-xs text-gray-500">
                    <span class="account-code-display">—</span>
                </div>
            </td>
            <td class="border-r border-gray-200 align-top px-3 py-3 hidden md:table-cell">
                <input
                    type="text"
                    class="account-name-input w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                    placeholder="{{ __('Account name') }}"
                    readonly
                >
            </td>
            <td class="border-r border-gray-200 align-top px-3 py-3">
                <textarea
                    data-field="particulars"
                    rows="2"
                    placeholder="{{ __('Describe what this payment covers...') }}"
                    class="w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                ></textarea>
            </td>
            <td class="border-r border-gray-200 align-top px-3 py-3 text-center">
                <select
                    data-field="type"
                    class="drcr-select rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                >
                    <option value="Dr">Dr</option>
                    <option value="Cr">Cr</option>
                </select>
            </td>
            <td class="border-r border-gray-200 align-top px-3 py-3 text-right">
                <input
                    type="number"
                    data-field="amount"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                    class="amount-input w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm text-right focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                >
            </td>
            <td class="border-r border-gray-200 align-top px-3 py-3">
                <input
                    type="text"
                    data-field="cheque_no"
                    placeholder="{{ __('Cheque no.') }}"
                    class="w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                >
            </td>
            <td class="border-r border-gray-200 align-top px-3 py-3 hidden lg:table-cell">
                <input
                    type="date"
                    data-field="cheque_date"
                    class="w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                >
            </td>
            <td class="align-top px-3 py-3">
                <div class="space-y-3">
                    <div class="bill-allocation hidden">
                        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-600">
                            {{ __('Apply to Bill') }}
                        </label>
                        <select
                            class="bill-select mt-1 w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                            data-field="bill_id"
                            data-initial-value=""
                        >
                            <option value="">{{ __('Select outstanding bill') }}</option>
                        </select>
                        <input
                            type="number"
                            class="bill-amount-input mt-2 w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                            data-field="bill_amount"
                            data-initial-value=""
                            step="0.01"
                            min="0"
                            placeholder="{{ __('Amount to apply') }}"
                        >
                        <p class="bill-balance text-xs text-gray-500"></p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-600">
                            {{ __('Notes') }}
                        </label>
                        <input
                            type="text"
                            data-field="bill_adjustment"
                            placeholder="{{ __('Optional memo') }}"
                            class="w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                        >
                    </div>

                    <button
                        type="button"
                        class="remove-row inline-flex h-8 w-8 items-center justify-center rounded-full bg-red-500 text-white shadow hover:bg-red-600"
                        title="{{ __('Remove row') }}"
                    >×</button>
                </div>
            </td>
        </tr>
    </template>

    <script>
        const rowsContainer = document.getElementById('voucherEntryRows');
        const addRowButton = document.getElementById('addEntryRow');
        const rowTemplate = document.getElementById('voucher-row-template');
        const totalDisplay = document.getElementById('totalAmountDisplay');
        const totalInput = document.getElementById('totalAmountInput');
        const bankSelect = document.getElementById('bank_account_id');
        const vendorBillsData = @json($vendorBillOptions);

        function formatNumber(value) {
            return Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function renumberRows() {
            rowsContainer.querySelectorAll('.entry-row').forEach((row, index) => {
                row.dataset.rowIndex = index;

                row.querySelectorAll('[data-field]').forEach(element => {
                    const field = element.dataset.field;
                    element.name = `lines[${index}][${field}]`;
                });
            });
        }

        function calculateTotals() {
            let debitTotal = 0;
            let creditTotal = 0;

            rowsContainer.querySelectorAll('.entry-row').forEach(row => {
                const typeSelect = row.querySelector('.drcr-select');
                const amountInput = row.querySelector('.amount-input');
                const amount = parseFloat(amountInput.value || '0');

                if (isNaN(amount)) {
                    return;
                }

                if (typeSelect.value === 'Dr') {
                    debitTotal += amount;
                } else {
                    creditTotal += amount;
                }
            });

            return {
                debitTotal,
                creditTotal,
                netBankAmount: debitTotal - creditTotal,
            };
        }

        function recalculateTotals() {
            const totals = calculateTotals();
            totalDisplay.textContent = formatNumber(totals.debitTotal);
            totalInput.value = totals.netBankAmount.toFixed(2);
        }

        function populateBillOptions(row, accountId) {
            const billSection = row.querySelector('.bill-allocation');
            const billSelect = row.querySelector('.bill-select');
            const billAmountInput = row.querySelector('.bill-amount-input');
            const billBalanceText = row.querySelector('.bill-balance');

            if (!billSection || !billSelect || !billAmountInput || !billBalanceText) {
                return;
            }

            const bills = vendorBillsData[accountId] || [];
            const hasBills = bills.length > 0;

            billSelect.innerHTML = `<option value="">${'{{ __('Select outstanding bill') }}'}</option>`;

            if (hasBills) {
                bills.forEach(bill => {
                    const option = document.createElement('option');
                    option.value = bill.id;
                    option.textContent = bill.label;
                    option.dataset.balance = bill.balance;
                    billSelect.appendChild(option);
                });

                billSection.classList.remove('hidden');
            } else {
                billSection.classList.add('hidden');
            }

            const initialBillId = billSelect.dataset.initialValue;
            const initialBillAmount = billAmountInput.dataset.initialValue;

            if (hasBills && initialBillId) {
                billSelect.value = initialBillId;
                billAmountInput.value = initialBillAmount || '';
                const selectedOption = billSelect.selectedOptions[0];
                billBalanceText.textContent = selectedOption
                    ? `{{ __('Outstanding:') }} ${formatNumber(selectedOption.dataset.balance)}`
                    : '';
            } else {
                billSelect.value = '';
                billAmountInput.value = '';
                billBalanceText.textContent = hasBills
                    ? `{{ __('Outstanding:') }} ${formatNumber(bills[0].balance)}`
                    : '';
            }

            // Clear the stored initial value after it has been applied once
            billSelect.dataset.initialValue = '';
            billAmountInput.dataset.initialValue = '';
        }

        function updateAccountDisplay(row) {
            const accountSelect = row.querySelector('.account-select');
            const accountNameInput = row.querySelector('.account-name-input');
            const accountCodeLabel = row.querySelector('.account-code-display');
            const billSelect = row.querySelector('.bill-select');
            const amountInput = row.querySelector('.amount-input');

            if (!accountSelect) {
                return;
            }

            const selectedOption = accountSelect.selectedOptions[0];
            const accountName = selectedOption ? selectedOption.dataset.name : '';
            const accountCode = selectedOption ? selectedOption.dataset.code : '—';
            const isPayable = selectedOption ? selectedOption.dataset.payable === '1' : false;
            const accountId = selectedOption ? selectedOption.value : null;

            if (accountNameInput) {
                accountNameInput.value = accountName || '';
            }

            if (accountCodeLabel) {
                accountCodeLabel.textContent = accountCode || '—';
            }

            if (billSelect) {
                billSelect.dataset.initialValue = billSelect.dataset.initialValue || billSelect.value || '';
            }

            if (isPayable && accountId) {
                populateBillOptions(row, accountId);
            } else {
                const billSection = row.querySelector('.bill-allocation');
                if (billSection) {
                    billSection.classList.add('hidden');
                }

                if (billSelect) {
                    billSelect.value = '';
                }

                const billAmountInput = row.querySelector('.bill-amount-input');
                const billBalanceText = row.querySelector('.bill-balance');
                if (billAmountInput) {
                    billAmountInput.value = '';
                }
                if (billBalanceText) {
                    billBalanceText.textContent = '';
                }
            }

            if (amountInput && selectedOption && selectedOption.dataset.payable === '1') {
                amountInput.setAttribute('min', '0.01');
            }
        }

        function attachRowListeners(row) {
            const inputsToWatch = row.querySelectorAll('.amount-input, .drcr-select');
            inputsToWatch.forEach(element => {
                element.addEventListener('input', recalculateTotals);
                element.addEventListener('change', recalculateTotals);
            });

            const accountSelect = row.querySelector('.account-select');
            if (accountSelect) {
                accountSelect.addEventListener('change', () => {
                    updateAccountDisplay(row);
                    recalculateTotals();
                });
                updateAccountDisplay(row);
            }

            const billSelect = row.querySelector('.bill-select');
            const billAmountInput = row.querySelector('.bill-amount-input');
            const billBalanceText = row.querySelector('.bill-balance');

            if (billSelect && billAmountInput) {
                billSelect.addEventListener('change', () => {
                    const selectedOption = billSelect.selectedOptions[0];
                    if (selectedOption) {
                        const balance = parseFloat(selectedOption.dataset.balance || '0');
                        billAmountInput.max = balance;
                        billBalanceText.textContent = `{{ __('Outstanding:') }} ${formatNumber(balance)}`;
                        if (!billAmountInput.value) {
                            billAmountInput.value = balance.toFixed(2);
                        } else if (parseFloat(billAmountInput.value) > balance) {
                            billAmountInput.value = balance.toFixed(2);
                        }
                    } else {
                        billAmountInput.value = '';
                        billBalanceText.textContent = '';
                    }
                });

                billAmountInput.addEventListener('input', () => {
                    const selectedOption = billSelect.selectedOptions[0];
                    const lineAmount = parseFloat((row.querySelector('.amount-input')?.value) || '0');
                    const billBalance = selectedOption ? parseFloat(selectedOption.dataset.balance || '0') : Infinity;
                    const value = parseFloat(billAmountInput.value || '0');

                    if (value > billBalance) {
                        billAmountInput.value = billBalance.toFixed(2);
                    } else if (value > lineAmount) {
                        billAmountInput.value = lineAmount.toFixed(2);
                    }
                });
            }

            const removeButton = row.querySelector('.remove-row');
            if (removeButton) {
                removeButton.addEventListener('click', () => {
                    row.remove();
                    renumberRows();
                    recalculateTotals();
                });
            }
        }

        if (addRowButton) {
            addRowButton.addEventListener('click', () => {
                const newRow = rowTemplate.content.cloneNode(true);
                rowsContainer.appendChild(newRow);
                renumberRows();
                const appendedRow = rowsContainer.lastElementChild;
                attachRowListeners(appendedRow);
                recalculateTotals();
            });
        }

        rowsContainer.querySelectorAll('.entry-row').forEach(row => attachRowListeners(row));
        renumberRows();
        recalculateTotals();

        // Handle voucher type change
        const voucherTypeSelect = document.getElementById('voucher_type_select');
        const voucherTypeHidden = document.getElementById('voucher_type');
        const voucherNumberDisplay = document.getElementById('voucher_number_display');
        
        if (voucherTypeSelect) {
            voucherTypeSelect.addEventListener('change', function() {
                const selectedType = this.value;
                voucherTypeHidden.value = selectedType;
                
                // Update voucher number display (would need to fetch from server in real implementation)
                // For now, just update the prefix display
                const currentNumber = voucherNumberDisplay.textContent.trim();
                const newPrefix = selectedType === 'receipt' ? 'BRV' : 'BPV';
                const year = new Date().getFullYear();
                // Extract the number part and update prefix
                const numberPart = currentNumber.split('-').pop();
                voucherNumberDisplay.textContent = `${newPrefix}-${year}-${numberPart}`;
            });
        }

        document.getElementById('bankVoucherForm').addEventListener('submit', (event) => {
            const totals = calculateTotals();

            if (!bankSelect.value) {
                event.preventDefault();
                alert('{{ __('Please select a bank account before saving the voucher.') }}');
                bankSelect.focus();
                return;
            }

            if (totals.netBankAmount <= 0) {
                event.preventDefault();
                alert('{{ __('Total debits must exceed credits to create a bank payment.') }}');
            }
        });
    </script>
</x-app-layout>

