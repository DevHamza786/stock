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
    
    // Prepare accounts data for JavaScript
    $accountsData = $accounts->map(function($account) {
        return [
            'id' => $account->id,
            'code' => $account->account_code,
            'name' => $account->account_name,
            'payable' => $account->account_subtype === 'ACCOUNTS_PAYABLE' ? '1' : '0'
        ];
    })->toArray();
    
    // Prepare bank accounts data for JavaScript
    $bankAccountsData = $bankAccounts->map(function($bankAccount) {
        return [
            'value' => $bankAccount->id,
            'id' => $bankAccount->id,
            'code' => $bankAccount->account_code,
            'name' => $bankAccount->account_name,
            'text' => $bankAccount->account_code . ' — ' . $bankAccount->account_name
        ];
    })->toArray();
    
    // Add vendor accounts
    foreach($vendors as $vendor) {
        if($vendor->chartOfAccount) {
            $accountsData[] = [
                'id' => $vendor->chartOfAccount->id,
                'code' => $vendor->chartOfAccount->account_code,
                'name' => $vendor->name . ' (Vendor)',
                'payable' => '1'
            ];
        }
    }

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
    <script>
        // Store accounts data globally BEFORE Alpine.js processes the page
        // This must be defined before any x-data attributes that reference it
        window.voucherAccountsData = @json($accountsData);
        window.bankAccountsData = @json($bankAccountsData);
        
        // Debug: Log bank accounts data
        console.log('Bank Accounts Data:', window.bankAccountsData);
        
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
                    // Initialize filtered options with all accounts
                    this.filteredOptions = this.accounts || [];
                    
                    // Set initial selected text
                    if (this.selectedValue && this.accounts && this.accounts.length > 0) {
                        const selected = this.accounts.find(acc => String(acc.id) === String(this.selectedValue));
                        if (selected) {
                            this.selectedText = selected.code + ' — ' + selected.name;
                            this.searchQuery = selected.code;
                        }
                    }
                    
                    // Set up name attribute for form submission
                    if (this.fieldName) {
                        const hiddenInput = this.$el.querySelector('input[type="hidden"][data-field="account_id"]');
                        if (hiddenInput) {
                            hiddenInput.name = this.fieldName;
                        }
                    }
                    
                    // Debug: Log accounts count
                    console.log('VoucherAccountSelect initialized with', this.accounts?.length || 0, 'accounts');
                },

                handleFocus() {
                    this.showDropdown = true;
                    if (!this.searchQuery) {
                        this.searchQuery = '';
                    }
                    this.filterOptions();
                },

                filterOptions() {
                    const query = this.searchQuery ? this.searchQuery.toLowerCase().trim() : '';
                    if (!query) {
                        // Show all accounts when no query
                        this.filteredOptions = this.accounts || [];
                        return;
                    }
                    
                    // Filter accounts based on query
                    this.filteredOptions = (this.accounts || []).filter(account => {
                        const code = (account.code || '').toLowerCase();
                        const name = (account.name || '').toLowerCase();
                        return code.includes(query) || name.includes(query);
                    });
                },

                selectOption(option) {
                    this.selectedValue = option.id;
                    this.selectedText = option.code + ' — ' + option.name;
                    this.searchQuery = option.code; // Show only account code in the input
                    this.showDropdown = false;
                    
                    // Update hidden input for account_id
                    const hiddenInput = this.$el.querySelector('input[type="hidden"][data-field="account_id"]');
                    if (hiddenInput) {
                        hiddenInput.value = option.id;
                    }
                    
                    // Find the row and update account name field
                    const row = this.$el.closest('.entry-row');
                    if (row) {
                        // Update account name input field (visible field)
                        const accountNameInput = row.querySelector('.account-name-input');
                        if (accountNameInput) {
                            accountNameInput.value = option.name;
                            // Ensure it has the name attribute for form submission
                            if (!accountNameInput.name) {
                                const rowIndex = row.getAttribute('data-row-index') || 
                                                Array.from(row.parentElement.children).indexOf(row);
                                accountNameInput.name = `lines[${rowIndex}][account_name]`;
                            }
                        }
                        
                        // Update account display helper text (like purchase voucher)
                        const accountDisplay = row.querySelector('.account-display');
                        if (accountDisplay) {
                            accountDisplay.textContent = option.code + ' — ' + option.name;
                            accountDisplay.style.display = 'block';
                        }
                        
                        // Update hidden account_name field if it exists
                        const accountNameField = row.querySelector('[data-field="account_name"]');
                        if (accountNameField && accountNameField.tagName === 'INPUT') {
                            accountNameField.value = option.name;
                            // Ensure it has the name attribute for form submission
                            if (!accountNameField.name) {
                                const rowIndex = row.getAttribute('data-row-index') || 
                                                Array.from(row.parentElement.children).indexOf(row);
                                accountNameField.name = `lines[${rowIndex}][account_name]`;
                            }
                        }
                        
                        // Trigger the existing updateAccountDisplay function if it exists
                        if (typeof updateAccountDisplay === 'function') {
                            updateAccountDisplay(row);
                        }
                    }
                }
            }
        }
    </script>
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
                                <div class="relative mt-2" style="overflow: visible !important; position: relative;" x-data="searchableSelect({
                                    options: window.bankAccountsData || [],
                                    selectedValue: '{{ old('bank_account_id') ?? '' }}',
                                    name: 'bank_account_id',
                                    placeholder: 'Type to search bank account...'
                                })" x-init="init()">
                                    <input type="hidden" name="bank_account_id" :value="selectedValue" id="bank_account_id">
                                    <input 
                                        type="text" 
                                        x-model="searchQuery"
                                        @input="filterOptions()"
                                        @focus="handleFocus()"
                                        @click="handleFocus()"
                                        @blur="setTimeout(() => showDropdown = false, 200)"
                                        @keydown.escape="showDropdown = false"
                                        @keydown.arrow-down.prevent="if(filteredOptions && filteredOptions.length > 0) showDropdown = true"
                                        placeholder="Type to search bank account..."
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-base font-medium text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    >
                                    <div x-show="showDropdown && filteredOptions.length > 0" 
                                         x-cloak
                                         x-transition
                                         class="absolute w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-2xl max-h-60 overflow-auto"
                                         style="position: absolute !important; z-index: 99999 !important; top: 100% !important; left: 0 !important; right: 0 !important; margin-top: 0.25rem !important;">
                                        <template x-for="option in filteredOptions" :key="option.value || option.id">
                                            <div @click="selectOption(option)"
                                                 class="px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 grid grid-cols-2 gap-2"
                                                 :class="{ 'bg-blue-100': String(option.value || option.id) === String(selectedValue) }">
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
                                        <th class="border-r border-gray-200 px-3 py-2 text-left">{{ __('Account Code') }}</th>
                                        <th class="border-r border-gray-200 px-3 py-2 text-left">{{ __('Account Name') }}</th>
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
                                            <td class="border-r border-gray-200 align-top px-3 py-3" style="overflow: visible !important; position: relative;">
                                                <div class="relative account-select-wrapper" style="overflow: visible !important;" 
                                                     x-data="voucherAccountSelect({
                                                         accounts: window.voucherAccountsData || [],
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
                                                        @focus="handleFocus()"
                                                        @click="handleFocus()"
                                                        @blur="setTimeout(() => showDropdown = false, 200)"
                                                        @keydown.escape="showDropdown = false"
                                                        @keydown.arrow-down.prevent="if(filteredOptions && filteredOptions.length > 0) showDropdown = true"
                                                        placeholder="Search by account code..."
                                                        class="w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                                                    >
                                                    <div x-show="showDropdown && filteredOptions && filteredOptions.length > 0" 
                                                         x-cloak
                                                         x-transition:enter="transition ease-out duration-100"
                                                         x-transition:enter-start="opacity-0 scale-95"
                                                         x-transition:enter-end="opacity-100 scale-100"
                                                         x-transition:leave="transition ease-in duration-75"
                                                         x-transition:leave-start="opacity-100 scale-100"
                                                         x-transition:leave-end="opacity-0 scale-95"
                                                         class="absolute w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-2xl max-h-60 overflow-auto"
                                                         style="position: absolute !important; z-index: 99999 !important; top: 100% !important; left: 0 !important; right: 0 !important; margin-top: 0.25rem !important;">
                                                        <template x-for="option in filteredOptions" :key="option.id">
                                                            <div @click="selectOption(option); $event.stopPropagation();"
                                                                 @mousedown.prevent
                                                                 class="px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 grid grid-cols-2 gap-2"
                                                                 :class="{ 'bg-blue-100': String(option.id) === String(selectedValue) }">
                                                                <div class="font-medium text-gray-900" x-text="option.code"></div>
                                                                <div class="text-sm text-gray-600" x-text="option.name"></div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                                <input
                                                    type="hidden"
                                                    data-field="account_name"
                                                    value="{{ $line['account_name'] ?? ($selectedAccount->account_name ?? '') }}"
                                                >
                                            </td>
                                            <td class="border-r border-gray-200 align-top px-3 py-3">
                                                <input
                                                    type="text"
                                                    name="lines[{{ $index }}][account_name]"
                                                    class="account-name-input w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                                                    value="{{ $line['account_name'] ?? ($selectedAccount->account_name ?? '') }}"
                                                    placeholder="{{ __('Account name') }}"
                                                    readonly
                                                >
                                                <div class="mt-1 text-xs text-gray-500 account-display" style="display: none;"></div>
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
            <td class="border-r border-gray-200 align-top px-3 py-3" style="overflow: visible !important; position: relative;">
                <div class="relative account-select-wrapper" style="overflow: visible !important;" 
                     x-data="voucherAccountSelect({
                         accounts: window.voucherAccountsData || [],
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
                        @focus="handleFocus()"
                        @click="handleFocus()"
                        @blur="setTimeout(() => showDropdown = false, 200)"
                        @keydown.escape="showDropdown = false"
                        @keydown.arrow-down.prevent="if(filteredOptions && filteredOptions.length > 0) showDropdown = true"
                        placeholder="Search by account code..."
                        class="w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                    >
                    <div x-show="showDropdown && filteredOptions && filteredOptions.length > 0" 
                         x-cloak
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-2xl max-h-60 overflow-auto"
                         style="position: absolute !important; z-index: 99999 !important; top: 100% !important; left: 0 !important; right: 0 !important; margin-top: 0.25rem !important;">
                        <template x-for="option in filteredOptions" :key="option.id">
                            <div @click="selectOption(option); $event.stopPropagation();"
                                 @mousedown.prevent
                                 class="px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 grid grid-cols-2 gap-2"
                                 :class="{ 'bg-blue-100': String(option.id) === String(selectedValue) }">
                                <div class="font-medium text-gray-900" x-text="option.code"></div>
                                <div class="text-sm text-gray-600" x-text="option.name"></div>
                            </div>
                        </template>
                    </div>
                </div>
                <input type="hidden" data-field="account_name">
            </td>
            <td class="border-r border-gray-200 align-top px-3 py-3">
                <input
                    type="text"
                    data-field="account_name"
                    class="account-name-input w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"
                    placeholder="{{ __('Account name') }}"
                    readonly
                >
                <div class="mt-1 text-xs text-gray-500 account-display" style="display: none;"></div>
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
                const rowElement = newRow.querySelector('tr');
                const rowIndex = rowsContainer.children.length;
                
                // Update field names for the new row
                if (rowElement) {
                    const accountSelectWrapper = rowElement.querySelector('.account-select-wrapper');
                    if (accountSelectWrapper) {
                        const hiddenInput = accountSelectWrapper.querySelector('input[type="hidden"][data-field="account_id"]');
                        if (hiddenInput) {
                            hiddenInput.name = `lines[${rowIndex}][account_id]`;
                        }
                        // Update Alpine.js data attribute - use global accounts data
                        accountSelectWrapper.setAttribute('x-data', `voucherAccountSelect({
                            accounts: window.voucherAccountsData || [],
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
                    
                    // Update account name input field name attribute
                    const accountNameInput = rowElement.querySelector('.account-name-input');
                    if (accountNameInput) {
                        accountNameInput.name = `lines[${rowIndex}][account_name]`;
                    }
                }
                rowsContainer.appendChild(newRow);
                renumberRows();
                const appendedRow = rowsContainer.lastElementChild;
                attachRowListeners(appendedRow);
                
                // Initialize Alpine.js for the new row
                if (typeof Alpine !== 'undefined' && appendedRow) {
                    Alpine.initTree(appendedRow);
                }
                
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
    <style>
        [x-cloak] { display: none !important; }
    </style>
    <script>
        function searchableSelect(config) {
            return {
                options: config.options || [],
                filteredOptions: config.options || [],
                searchQuery: '',
                selectedValue: config.selectedValue || '',
                selectedText: '',
                showDropdown: false,

                init() {
                    // Initialize filtered options with all options
                    this.filteredOptions = this.options || [];
                    
                    // Set initial selected text
                    if (this.selectedValue) {
                        const selected = this.options.find(opt => String(opt.value || opt.id) === String(this.selectedValue));
                        if (selected) {
                            this.selectedText = selected.text || (selected.code + ' — ' + selected.name);
                            this.searchQuery = this.selectedText;
                        }
                    }
                    
                    // Debug: Log options count
                    console.log('SearchableSelect initialized with', this.options?.length || 0, 'options');
                },

                handleFocus() {
                    this.showDropdown = true;
                    if (!this.searchQuery) {
                        this.searchQuery = '';
                    }
                    this.filterOptions();
                },

                filterOptions() {
                    const query = this.searchQuery ? this.searchQuery.toLowerCase().trim() : '';
                    if (!query) {
                        // Show all options when no query
                        this.filteredOptions = this.options || [];
                        return;
                    }
                    
                    // Filter options based on query
                    this.filteredOptions = (this.options || []).filter(option => {
                        const code = (option.code || '').toLowerCase();
                        const name = (option.name || '').toLowerCase();
                        const text = (option.text || '').toLowerCase();
                        return code.includes(query) || name.includes(query) || text.includes(query);
                    });
                },

                selectOption(option) {
                    this.selectedValue = option.value || option.id;
                    this.selectedText = option.text || (option.code + ' — ' + option.name);
                    this.searchQuery = this.selectedText;
                    this.showDropdown = false;
                    const hiddenInput = document.getElementById(config.name);
                    if (hiddenInput) {
                        hiddenInput.value = option.value || option.id;
                    }
                }
            }
        }
    </script>
</x-app-layout>

