@php
    $defaultLines = [
        [
            'account_id' => null,
            'type' => 'Dr',
            'amount' => null,
            'particulars' => null,
            'bill_id' => null,
            'bill_amount' => null,
            'bill_adjustment' => null,
        ],
    ];

    $oldLines = collect(old('lines', $defaultLines))->values();
    
    // Prepare accounts data for JavaScript
    $accountsData = $accounts->map(function($account) {
        return [
            'id' => $account->id,
            'code' => $account->account_code,
            'name' => $account->account_name,
            'payable' => $account->account_subtype === 'ACCOUNTS_PAYABLE' ? '1' : '0'
        ];
    })->toArray();

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
                <a href="{{ route('accounting.cash-payment-vouchers.index') }}"
                   class="inline-flex items-center gap-2 font-medium text-blue-600 hover:text-blue-700">
                    <span aria-hidden="true" class="text-lg">←</span>
                    {{ __('Cash Payment Vouchers') }}
                </a>
                <span class="text-gray-400">/</span>
                <span>{{ __('Create') }}</span>
            </div>

            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('Create Cash Payment Voucher') }}</h1>
                <p class="mt-2 text-gray-600">
                    {{ __('Select the cash account being credited and allocate the counter ledgers below. Payable ledgers can be knocked off against open bills.') }}
                </p>
            </div>

            <form id="cashVoucherForm" method="POST" action="{{ route('accounting.cash-payment-vouchers.store') }}">
                @csrf

                <input type="hidden" name="amount" id="cashTotalAmountInput" value="{{ old('amount', '0.00') }}">

                <div class="rounded-2xl border border-gray-200 bg-white shadow-xl">
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-5">
                        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    {{ __('Voucher Number') }}
                                </p>
                                <p class="mt-1 text-xl font-semibold text-gray-900">
                                    {{ $nextVoucherNumber }}
                                </p>
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
                                    class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                            </div>
                            <div>
                                <label for="cash_account_id" class="block text-sm font-semibold uppercase tracking-wide text-gray-700">
                                    {{ __('Cash Account') }}
                                </label>
                                <div class="relative mt-2" x-data="searchableSelect({
                                    options: [
                                        @foreach($cashAccounts as $cashAccount)
                                        { value: '{{ $cashAccount->id }}', text: '{{ $cashAccount->account_code }} — {{ addslashes($cashAccount->account_name) }}', code: '{{ $cashAccount->account_code }}', name: '{{ addslashes($cashAccount->account_name) }}' },
                                        @endforeach
                                    ],
                                    selectedValue: '{{ old('cash_account_id') ?? '' }}',
                                    name: 'cash_account_id',
                                    placeholder: 'Type to search cash account...'
                                })" x-init="init()">
                                    <input type="hidden" name="cash_account_id" :value="selectedValue" id="cash_account_id">
                                    <input 
                                        type="text" 
                                        x-model="searchQuery"
                                        @input="filterOptions()"
                                        @focus="showDropdown = true; if(!searchQuery) searchQuery = '';"
                                        @blur="setTimeout(() => showDropdown = false, 200)"
                                        @keydown.escape="showDropdown = false"
                                        placeholder="Type to search cash account..."
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    >
                                    <div x-show="showDropdown && filteredOptions.length > 0" 
                                         x-cloak
                                         class="absolute z-[9999] w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-xl max-h-60 overflow-auto"
                                         style="position: absolute; z-index: 9999;">
                                        <template x-for="option in filteredOptions" :key="option.value">
                                            <div @click="selectOption(option)"
                                                 class="px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 grid grid-cols-2 gap-2"
                                                 :class="{ 'bg-blue-100': option.value == selectedValue }">
                                                <div class="font-medium text-gray-900" x-text="option.code"></div>
                                                <div class="text-sm text-gray-600" x-text="option.name"></div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-6 space-y-6">
                        <div class="overflow-hidden rounded-xl border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200 text-sm font-medium text-gray-900">
                                <thead class="bg-gray-100 uppercase tracking-wide text-xs text-gray-500">
                                    <tr>
                                        <th class="px-4 py-3 text-left">{{ __('Account Code') }}</th>
                                        <th class="px-4 py-3 text-left">{{ __('Account Name') }}</th>
                                        <th class="px-4 py-3 text-left hidden lg:table-cell">{{ __('Details') }}</th>
                                        <th class="px-4 py-3 text-center">{{ __('Dr/Cr') }}</th>
                                        <th class="px-4 py-3 text-right">{{ __('Amount') }}</th>
                                        <th class="px-4 py-3 text-left">{{ __('Bill / Notes') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="cashVoucherLines" class="divide-y divide-gray-100">
                                    @foreach($oldLines as $index => $line)
                                        @php
                                            $selectedAccount = $accounts->firstWhere('id', (int) ($line['account_id'] ?? 0));
                                            $isPayable = $selectedAccount && $selectedAccount->account_subtype === 'ACCOUNTS_PAYABLE';
                                            $initialBillId = $line['bill_id'] ?? null;
                                            $initialBillAmount = $line['bill_amount'] ?? null;
                                        @endphp
                                        <tr data-row-index="{{ $index }}" class="entry-row">
                                            <td class="border-r border-gray-200 px-4 py-3 align-top" style="overflow: visible !important; position: relative;">
                                                <div class="relative account-select-wrapper" style="overflow: visible !important;" 
                                                     x-data="voucherAccountSelect({
                                                         accounts: @json($accountsData),
                                                         selectedValue: '{{ $line['account_id'] ?? '' }}',
                                                         rowIndex: {{ $index }},
                                                         fieldName: 'lines[{{ $index }}][account_id]'
                                                     })" 
                                                     x-init="init()">
                                                    <input type="hidden" name="lines[{{ $index }}][account_id]" :value="selectedValue" data-field="account_id">
                                                    <input 
                                                        type="text" 
                                                        x-model="searchQuery"
                                                        @input="filterOptions()"
                                                        @focus="showDropdown = true"
                                                        @blur="setTimeout(() => showDropdown = false, 200)"
                                                        @keydown.escape="showDropdown = false"
                                                        :placeholder="placeholder || 'Type to search account...'"
                                                        class="w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                                                    >
                                                    <div x-show="showDropdown && filteredOptions.length > 0" 
                                                         x-cloak
                                                         x-transition
                                                         class="absolute w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-2xl max-h-60 overflow-auto"
                                                         style="position: absolute !important; z-index: 99999 !important; top: 100% !important; left: 0 !important; right: 0 !important; margin-top: 0.25rem !important;">
                                                        <template x-for="option in filteredOptions" :key="option.id">
                                                            <div @click="selectOption(option)"
                                                                 class="px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 grid grid-cols-2 gap-2"
                                                                 :class="{ 'bg-blue-100': option.id == selectedValue }">
                                                                <div class="font-medium text-gray-900" x-text="option.code"></div>
                                                                <div class="text-sm text-gray-600" x-text="option.name"></div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                                <input type="hidden" data-field="account_name" value="{{ $line['account_name'] ?? ($selectedAccount->account_name ?? '') }}">
                                            </td>
                                            <td class="border-r border-gray-200 px-4 py-3 align-top">
                                                <input
                                                    type="text"
                                                    class="account-name-input w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                                                    value="{{ $line['account_name'] ?? ($selectedAccount->account_name ?? '') }}"
                                                    placeholder="{{ __('Account name') }}"
                                                    readonly
                                                >
                                            </td>
                                            <td class="border-r border-gray-200 px-4 py-3 align-top hidden lg:table-cell">
                                                <textarea
                                                    name="lines[{{ $index }}][particulars]"
                                                    data-field="particulars"
                                                    rows="2"
                                                    placeholder="{{ __('Describe what this payment covers...') }}"
                                                    class="w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                                                >{{ $line['particulars'] }}</textarea>
                                            </td>
                                            <td class="border-r border-gray-200 px-4 py-3 align-top text-center">
                                                <select
                                                    name="lines[{{ $index }}][type]"
                                                    data-field="type"
                                                    class="drcr-select rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                                                >
                                                    <option value="Dr" {{ ($line['type'] ?? 'Dr') === 'Dr' ? 'selected' : '' }}>Dr</option>
                                                    <option value="Cr" {{ ($line['type'] ?? '') === 'Cr' ? 'selected' : '' }}>Cr</option>
                                                </select>
                                            </td>
                                            <td class="border-r border-gray-200 px-4 py-3 align-top text-right">
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
                                            <td class="px-4 py-3 align-top">
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
                                <tfoot class="bg-gray-100 text-base font-semibold text-gray-600">
                                    <tr>
                                        <td colspan="3" class="border-r border-gray-200 px-4 py-3 text-right uppercase tracking-wide">
                                            {{ __('Total (Dr)') }}
                                        </td>
                                        <td class="border-r border-gray-200 px-4 py-3 text-right">
                                            <span id="cashTotalDisplay">{{ number_format(old('amount', 0), 2) }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <button
                                                type="button"
                                                id="addCashEntryRow"
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
                                    placeholder="{{ __('Receipt or voucher reference') }}"
                                    class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                            </div>
                            <div>
                                <label for="notes" class="block text-sm font-semibold uppercase tracking-wide text-gray-700">
                                    {{ __('Notes') }}
                                </label>
                                <textarea
                                    id="notes"
                                    name="notes"
                                    rows="3"
                                    placeholder="{{ __('Optional notes about this payment') }}"
                                    class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                >{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('accounting.cash-payment-vouchers.index') }}"
                       class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        {{ __('Save Cash Voucher') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <template id="cash-voucher-row-template">
        <tr class="entry-row">
            <td class="border-r border-gray-200 px-4 py-3 align-top" style="overflow: visible !important; position: relative;">
                <div class="relative account-select-wrapper" style="overflow: visible !important;" 
                     x-data="voucherAccountSelect({
                         accounts: @json($accountsData),
                         selectedValue: '',
                         rowIndex: null,
                         fieldName: ''
                     })" 
                     x-init="init()">
                    <input type="hidden" data-field="account_id" :value="selectedValue">
                    <input 
                        type="text" 
                        x-model="searchQuery"
                        @input="filterOptions()"
                        @focus="showDropdown = true"
                        @blur="setTimeout(() => showDropdown = false, 200)"
                        @keydown.escape="showDropdown = false"
                        :placeholder="placeholder || 'Type to search account...'"
                        class="w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                    >
                    <div x-show="showDropdown && filteredOptions.length > 0" 
                         x-cloak
                         x-transition
                         class="absolute w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-2xl max-h-60 overflow-auto"
                         style="position: absolute !important; z-index: 99999 !important; top: 100% !important; left: 0 !important; right: 0 !important; margin-top: 0.25rem !important;">
                        <template x-for="option in filteredOptions" :key="option.id">
                            <div @click="selectOption(option)"
                                 class="px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 grid grid-cols-2 gap-2"
                                 :class="{ 'bg-blue-100': option.id == selectedValue }">
                                <div class="font-medium text-gray-900" x-text="option.code"></div>
                                <div class="text-sm text-gray-600" x-text="option.name"></div>
                            </div>
                        </template>
                    </div>
                </div>
                <input type="hidden" data-field="account_name">
            </td>
            <td class="border-r border-gray-200 px-4 py-3 align-top">
                <input
                    type="text"
                    class="account-name-input w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                    placeholder="{{ __('Account name') }}"
                    readonly
                >
            </td>
            <td class="border-r border-gray-200 px-4 py-3 align-top hidden lg:table-cell">
                <textarea
                    data-field="particulars"
                    rows="2"
                    placeholder="{{ __('Describe what this payment covers...') }}"
                    class="w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                ></textarea>
            </td>
            <td class="border-r border-gray-200 px-4 py-3 align-top text-center">
                <select
                    data-field="type"
                    class="drcr-select rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                >
                    <option value="Dr">Dr</option>
                    <option value="Cr">Cr</option>
                </select>
            </td>
            <td class="border-r border-gray-200 px-4 py-3 align-top text-right">
                <input
                    type="number"
                    data-field="amount"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                    class="amount-input w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm text-right focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                >
            </td>
            <td class="px-4 py-3 align-top">
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
        const linesContainer = document.getElementById('cashVoucherLines');
        const addRowButton = document.getElementById('addCashEntryRow');
        const rowTemplate = document.getElementById('cash-voucher-row-template');
        const totalDisplay = document.getElementById('cashTotalDisplay');
        const totalInput = document.getElementById('cashTotalAmountInput');
        const cashAccountSelect = document.getElementById('cash_account_id');
        const vendorBillsData = @json($vendorBillOptions);

        function formatCurrency(value) {
            return Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function renumberRows() {
            linesContainer.querySelectorAll('.entry-row').forEach((row, index) => {
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

            linesContainer.querySelectorAll('.entry-row').forEach(row => {
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
                netCashAmount: debitTotal - creditTotal,
            };
        }

        function recalculateTotals() {
            const totals = calculateTotals();
            totalDisplay.textContent = formatCurrency(totals.debitTotal);
            totalInput.value = totals.netCashAmount.toFixed(2);
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

            billSelect.innerHTML = `<option value=\"\">${'{{ __('Select outstanding bill') }}'}</option>`;

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
                    ? `{{ __('Outstanding:') }} ${formatCurrency(selectedOption.dataset.balance)}`
                    : '';
            } else {
                billSelect.value = '';
                billAmountInput.value = '';
                billBalanceText.textContent = hasBills
                    ? `{{ __('Outstanding:') }} ${formatCurrency(bills[0].balance)}`
                    : '';
            }

            billSelect.dataset.initialValue = '';
            billAmountInput.dataset.initialValue = '';
        }

        function updateAccountRow(row) {
            const accountSelect = row.querySelector('.account-select');
            const accountNameField = row.querySelector('[data-field=\"account_name\"]');
            const accountNameInput = row.querySelector('.account-name-input');
            const billSelect = row.querySelector('.bill-select');

            if (!accountSelect) {
                return;
            }

            const selectedOption = accountSelect.selectedOptions[0];
            const accountName = selectedOption ? selectedOption.dataset.name : '';
            const accountCode = selectedOption ? selectedOption.dataset.code : '—';
            const isPayable = selectedOption ? selectedOption.dataset.payable === '1' : false;
            const accountId = selectedOption ? selectedOption.value : null;

            if (accountNameField) {
                accountNameField.value = accountName || '';
            }
            if (accountNameInput) {
                accountNameInput.value = accountName || '';
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
        }

        function attachRowListeners(row) {
            row.querySelectorAll('.amount-input, .drcr-select').forEach(element => {
                element.addEventListener('input', recalculateTotals);
                element.addEventListener('change', recalculateTotals);
            });

            const accountSelect = row.querySelector('.account-select');
            if (accountSelect) {
                accountSelect.addEventListener('change', () => {
                    updateAccountRow(row);
                    recalculateTotals();
                });
                updateAccountRow(row);
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
                        billBalanceText.textContent = `{{ __('Outstanding:') }} ${formatCurrency(balance)}`;
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
                const rowElement = newRow.querySelector('tr');
                const rowIndex = linesContainer.children.length;
                
                // Update field names for the new row
                if (rowElement) {
                    const accountSelectWrapper = rowElement.querySelector('.account-select-wrapper');
                    if (accountSelectWrapper) {
                        const hiddenInput = accountSelectWrapper.querySelector('input[type="hidden"][data-field="account_id"]');
                        if (hiddenInput) {
                            hiddenInput.name = `lines[${rowIndex}][account_id]`;
                        }
                        // Update Alpine.js data attribute - use Alpine.data format
                        const accountsData = @json($accountsData);
                        accountSelectWrapper.setAttribute('x-data', `voucherAccountSelect({
                            accounts: ${JSON.stringify(accountsData)},
                            selectedValue: '',
                            rowIndex: ${rowIndex},
                            fieldName: 'lines[${rowIndex}][account_id]'
                        })`);
                    }
                    
                    // Update all other field names in the row
                    rowElement.querySelectorAll('[data-field]').forEach(field => {
                        const fieldName = field.getAttribute('data-field');
                        if (fieldName && fieldName !== 'account_id') {
                            if (field.tagName === 'INPUT' || field.tagName === 'TEXTAREA' || field.tagName === 'SELECT') {
                                field.name = `lines[${rowIndex}][${fieldName}]`;
                            }
                        }
                    });
                }
                
                linesContainer.appendChild(newRow);
                renumberRows();
                const appendedRow = linesContainer.lastElementChild;
                attachRowListeners(appendedRow);
                
                // Initialize Alpine.js for the new row
                if (typeof Alpine !== 'undefined' && appendedRow) {
                    Alpine.initTree(appendedRow);
                }
                
                recalculateTotals();
            });
        }

        linesContainer.querySelectorAll('.entry-row').forEach(row => attachRowListeners(row));
        renumberRows();
        recalculateTotals();

        document.getElementById('cashVoucherForm').addEventListener('submit', (event) => {
            const totals = calculateTotals();

            if (!cashAccountSelect.value) {
                event.preventDefault();
                alert('{{ __('Please select a cash account before saving the voucher.') }}');
                cashAccountSelect.focus();
                return;
            }

            if (totals.netCashAmount <= 0) {
                event.preventDefault();
                alert('{{ __('Total debits must exceed credits to create a cash payment.') }}');
            }
        });
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    <script>
        // Define functions immediately - Alpine will use them when processing x-data
        function voucherAccountSelect(config) {
            return {
                accounts: config.accounts || [],
                filteredOptions: config.accounts || [],
                searchQuery: '',
                selectedValue: config.selectedValue || '',
                selectedText: '',
                showDropdown: false,
                rowIndex: config.rowIndex,
                fieldName: config.fieldName,
                placeholder: 'Type to search account...',

                init() {
                    // Set initial selected text
                    if (this.selectedValue) {
                        const selected = this.accounts.find(acc => acc.id == this.selectedValue);
                        if (selected) {
                            this.selectedText = selected.code + ' — ' + selected.name;
                            this.searchQuery = this.selectedText;
                        }
                    }
                    this.filteredOptions = this.accounts;
                    
                    // Set up name attribute for form submission
                    if (this.fieldName) {
                        const hiddenInput = this.$el.querySelector('input[type="hidden"][data-field="account_id"]');
                        if (hiddenInput) {
                            hiddenInput.name = this.fieldName;
                        }
                    }
                },

                filterOptions() {
                    const query = this.searchQuery.toLowerCase().trim();
                    if (!query) {
                        this.filteredOptions = this.accounts;
                        return;
                    }
                    
                    this.filteredOptions = this.accounts.filter(account => {
                        const code = (account.code || '').toLowerCase();
                        const name = (account.name || '').toLowerCase();
                        return code.includes(query) || name.includes(query);
                    });
                },

                selectOption(option) {
                    this.selectedValue = option.id;
                    this.selectedText = option.code + ' — ' + option.name;
                    this.searchQuery = this.selectedText;
                    this.showDropdown = false;
                    
                    // Update hidden input
                    const hiddenInput = this.$el.querySelector('input[type="hidden"][data-field="account_id"]');
                    if (hiddenInput) {
                        hiddenInput.value = option.id;
                    }
                    
                    // Trigger account update for voucher row
                    const row = this.$el.closest('.entry-row');
                    if (row) {
                        // Update account name field
                        const accountNameInput = row.querySelector('.account-name-input');
                        const accountNameField = row.querySelector('[data-field="account_name"]');
                        if (accountNameInput) {
                            accountNameInput.value = option.name;
                        }
                        if (accountNameField) {
                            accountNameField.value = option.name;
                        }
                        
                        // Trigger the existing updateAccountRow function if it exists
                        if (typeof updateAccountRow === 'function') {
                            updateAccountRow(row);
                        }
                    }
                }
            }
        }
        
        function searchableSelect(config) {
            return {
                options: config.options || [],
                filteredOptions: config.options || [],
                searchQuery: '',
                selectedValue: config.selectedValue || '',
                selectedText: '',
                showDropdown: false,

                init() {
                    // Set initial selected text
                    if (this.selectedValue) {
                        const selected = this.options.find(opt => opt.value == this.selectedValue);
                        if (selected) {
                            this.selectedText = selected.text;
                            this.searchQuery = selected.text;
                        }
                    }
                    this.filteredOptions = this.options;
                },

                filterOptions() {
                    const query = this.searchQuery.toLowerCase().trim();
                    if (!query) {
                        this.filteredOptions = this.options;
                        return;
                    }
                    
                    this.filteredOptions = this.options.filter(option => {
                        const code = (option.code || '').toLowerCase();
                        const name = (option.name || '').toLowerCase();
                        const text = (option.text || '').toLowerCase();
                        return code.includes(query) || name.includes(query) || text.includes(query);
                    });
                },

                selectOption(option) {
                    this.selectedValue = option.value;
                    this.selectedText = option.text;
                    this.searchQuery = option.text;
                    this.showDropdown = false;
                    const hiddenInput = document.getElementById(config.name);
                    if (hiddenInput) {
                        hiddenInput.value = option.value;
                    }
                }
            }
        }
    </script>
</x-app-layout>

