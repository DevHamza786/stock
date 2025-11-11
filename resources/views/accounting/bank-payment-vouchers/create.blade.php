<x-app-layout>
    <div class="py-10">
        <div class="max-w-none mx-auto px-4 sm:px-6 lg:px-12 xl:px-16">
            <div class="mb-6 flex items-center gap-4 text-sm text-gray-500">
                <a href="{{ route('accounting.bank-payment-vouchers.index') }}" class="flex items-center gap-2 font-medium text-blue-600 hover:text-blue-700">
                    <span aria-hidden="true" class="text-lg">←</span>
                    {{ __('Back to vouchers') }}
                </a>
            </div>

            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">{{ __('Create Bank Payment Voucher') }}</h1>
                <p class="mt-2 text-gray-600">{{ __('Record a bank payment against suppliers with the layout your team uses on paper.') }}</p>
            </div>

            <form id="bankVoucherForm" method="POST" action="{{ route('accounting.bank-payment-vouchers.store') }}" onsubmit="return prepareVoucherSubmission()">
                @csrf

                <input type="hidden" name="amount" id="totalAmountInput" value="{{ old('amount', '0.00') }}">

                <div class="rounded-2xl border border-gray-200 bg-white shadow-xl">
                    <div class="border border-gray-200 bg-white p-6">
                        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <label for="payment_date" class="block text-sm font-semibold uppercase tracking-wide text-gray-800">{{ __('Date') }}</label>
                                <input
                                    type="date"
                                    id="payment_date"
                                    name="payment_date"
                                    value="{{ old('payment_date', now()->format('Y-m-d')) }}"
                                    required
                                    class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-base font-semibold text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold uppercase tracking-wide text-gray-800">{{ __('Voucher No. (Auto)') }}</label>
                                <div class="mt-2 flex h-[42px] items-center rounded-lg border border-gray-300 bg-gray-50 px-3 text-base font-semibold text-gray-900 shadow-inner">
                                    {{ $nextVoucherNumber }}
                                </div>
                            </div>

                            <div>
                                <label for="vendor_id" class="block text-sm font-semibold uppercase tracking-wide text-gray-800">{{ __('Vendor') }}</label>
                                <select
                                    id="vendor_id"
                                    name="vendor_id"
                                    required
                                    class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-base font-semibold text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                                    <option value="">{{ __('Select vendor') }}</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                            {{ $vendor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="payment_method" class="block text-sm font-semibold uppercase tracking-wide text-gray-800">{{ __('Bank') }}</label>
                            <input
                                type="text"
                                id="payment_method"
                                name="payment_method"
                                value="{{ old('payment_method') }}"
                                placeholder="{{ __('e.g., Meezan Bank MPR Branch') }}"
                                class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-base font-semibold text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>

                        <div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white">
                            <table class="min-w-full divide-y divide-gray-200 text-sm font-semibold text-gray-900">
                                <thead class="bg-gray-100 uppercase tracking-wide">
                                    <tr>
                                        <th class="border-r border-gray-200 px-3 py-2 text-left">{{ __('Account Code') }}</th>
                                        <th class="border-r border-gray-200 px-3 py-2 text-left">{{ __('Account Name') }}</th>
                                        <th class="border-r border-gray-200 px-3 py-2 text-left">{{ __('Particulars') }}</th>
                                        <th class="border-r border-gray-200 px-3 py-2 text-center">{{ __('Dr/Cr') }}</th>
                                        <th class="border-r border-gray-200 px-3 py-2 text-right">{{ __('Amount') }}</th>
                                        <th class="border-r border-gray-200 px-3 py-2 text-left">{{ __('Chq No') }}</th>
                                        <th class="border-r border-gray-200 px-3 py-2 text-left">{{ __('Chq Date') }}</th>
                                        <th class="px-3 py-2 text-left">{{ __('Bill Adjustment') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="voucherEntryRows">
                                    @php
                                        $oldEntries = old('entries', [
                                            ['account_code' => '', 'account_name' => '', 'particulars' => '', 'type' => 'Dr', 'amount' => '', 'cheque_no' => '', 'cheque_date' => '', 'bill_adjustment' => ''],
                                            ['account_code' => '', 'account_name' => '', 'particulars' => '', 'type' => 'Dr', 'amount' => '', 'cheque_no' => '', 'cheque_date' => '', 'bill_adjustment' => ''],
                                        ]);
                                    @endphp
                                    @foreach($oldEntries as $index => $entry)
                                        <tr data-row-index="{{ $index }}" class="entry-row">
                                            <td class="border-r border-gray-200 px-3 py-2 align-top">
                                                <select
                                                    name="entries[{{ $index }}][account_code]"
                                                    class="account-select w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300">
                                                    <option value="">{{ __('Select account head') }}</option>
                                                    @foreach($accounts as $account)
                                                        <option value="{{ $account->account_code }}"
                                                            data-name="{{ $account->account_name }}"
                                                            {{ ($entry['account_code'] ?? '') === $account->account_code ? 'selected' : '' }}>
                                                            {{ $account->account_code }} — {{ $account->account_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="border-r border-gray-200 px-3 py-2 align-top">
                                                <input type="text"
                                                       name="entries[{{ $index }}][account_name]"
                                                       value="{{ $entry['account_name'] }}"
                                                       placeholder="{{ __('Account name') }}"
                                                       class="account-name-input w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300">
                                            </td>
                                            <td class="border-r border-gray-200 px-3 py-2 align-top">
                                                <textarea name="entries[{{ $index }}][particulars]"
                                                          rows="2"
                                                          placeholder="{{ __('Describe what this payment covers...') }}"
                                                          class="w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300">{{ $entry['particulars'] }}</textarea>
                                            </td>
                                            <td class="border-r border-gray-200 px-3 py-2 align-top text-center">
                                                <select name="entries[{{ $index }}][type]"
                                                        class="rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300 drcr-select">
                                                    <option value="Dr" {{ ($entry['type'] ?? 'Dr') === 'Dr' ? 'selected' : '' }}>Dr</option>
                                                    <option value="Cr" {{ ($entry['type'] ?? '') === 'Cr' ? 'selected' : '' }}>Cr</option>
                                                </select>
                                            </td>
                                            <td class="border-r border-gray-200 px-3 py-2 align-top text-right">
                                                <input type="number"
                                                       name="entries[{{ $index }}][amount]"
                                                       value="{{ $entry['amount'] }}"
                                                       step="0.01"
                                                       min="0"
                                                       placeholder="0.00"
                                                       class="w-full rounded border border-gray-300 bg-white px-2 py-1 text-right text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300 amount-input">
                                            </td>
                                            <td class="border-r border-gray-200 px-3 py-2 align-top">
                                                <input type="text"
                                                       name="entries[{{ $index }}][cheque_no]"
                                                       value="{{ $entry['cheque_no'] }}"
                                                       placeholder="—"
                                                       class="w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300">
                                            </td>
                                            <td class="border-r border-gray-200 px-3 py-2 align-top">
                                                <input type="date"
                                                       name="entries[{{ $index }}][cheque_date]"
                                                       value="{{ $entry['cheque_date'] }}"
                                                       class="w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300">
                                            </td>
                                            <td class="px-3 py-2 align-top">
                                                <div class="flex items-center gap-2">
                                                    <input type="text"
                                                           name="entries[{{ $index }}][bill_adjustment]"
                                                           value="{{ $entry['bill_adjustment'] }}"
                                                           placeholder="—"
                                                           class="w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300">
                                                    <button type="button"
                                                            class="remove-row inline-flex h-8 w-8 items-center justify-center rounded-full bg-red-500 text-white shadow hover:bg-red-600"
                                                            title="{{ __('Remove row') }}">×</button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-100 text-base font-bold">
                                    <tr>
                                        <td colspan="4" class="border-r border-gray-200 px-3 py-2 text-right uppercase tracking-wide">{{ __('Total') }}</td>
                                        <td class="border-r border-gray-200 px-3 py-2 text-right">
                                            <span id="totalAmountDisplay">{{ number_format(old('amount', 0), 2) }}</span>
                                        </td>
                                        <td colspan="3" class="px-3 py-2 text-right">
                                            <button type="button"
                                                    id="addEntryRow"
                                                    class="inline-flex items-center rounded border border-gray-300 bg-white px-3 py-1 text-sm font-semibold text-blue-600 transition hover:bg-blue-50">
                                                {{ __('+ Add Row') }}
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="mt-6 grid gap-6 md:grid-cols-2">
                            <div>
                                <label for="reference_number" class="block text-sm font-semibold uppercase tracking-wide text-gray-800">{{ __('Reference Number') }}</label>
                                <input
                                    type="text"
                                    id="reference_number"
                                    name="reference_number"
                                    value="{{ old('reference_number') }}"
                                    placeholder="{{ __('Bank or cheque reference') }}"
                                    class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-base font-semibold text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                            </div>
                            <div>
                                <label for="notes" class="block text-sm font-semibold uppercase tracking-wide text-gray-800">{{ __('Notes') }}</label>
                                <textarea
                                    id="notes"
                                    name="notes"
                                    rows="3"
                                    placeholder="{{ __('Additional details about this payment...') }}"
                                    class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-base font-semibold text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap items-center justify-end gap-3">
                    <a href="{{ route('accounting.bank-payment-vouchers.index') }}"
                       class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        {{ __('Save Voucher') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <template id="voucher-row-template">
        <tr class="entry-row">
            <td class="border-r border-gray-200 px-3 py-2 align-top">
                <select
                    data-field="account_code"
                    class="account-select w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300">
                    <option value="">{{ __('Select account head') }}</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->account_code }}" data-name="{{ $account->account_name }}">{{ $account->account_code }} — {{ $account->account_name }}</option>
                    @endforeach
                </select>
            </td>
            <td class="border-r border-gray-200 px-3 py-2 align-top">
                <input type="text"
                       data-field="account_name"
                       placeholder="{{ __('Account name') }}"
                       class="account-name-input w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300">
            </td>
            <td class="border-r border-gray-200 px-3 py-2 align-top">
                <textarea rows="2"
                          data-field="particulars"
                          placeholder="{{ __('Describe what this payment covers...') }}"
                          class="w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300"></textarea>
            </td>
            <td class="border-r border-gray-200 px-3 py-2 align-top text-center">
                <select data-field="type"
                        class="rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300 drcr-select">
                    <option value="Dr">Dr</option>
                    <option value="Cr">Cr</option>
                </select>
            </td>
            <td class="border-r border-gray-200 px-3 py-2 align-top text-right">
                <input type="number"
                       data-field="amount"
                       step="0.01"
                       min="0"
                       placeholder="0.00"
                       class="w-full rounded border border-gray-300 bg-white px-2 py-1 text-right text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300 amount-input">
            </td>
            <td class="border-r border-gray-200 px-3 py-2 align-top">
                <input type="text"
                       data-field="cheque_no"
                       placeholder="—"
                       class="w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300">
            </td>
            <td class="border-r border-gray-200 px-3 py-2 align-top">
                <input type="date"
                       data-field="cheque_date"
                       class="w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300">
            </td>
            <td class="px-3 py-2 align-top">
                <div class="flex items-center gap-2">
                    <input type="text"
                           data-field="bill_adjustment"
                           placeholder="—"
                           class="w-full rounded border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-300">
                    <button type="button"
                            class="remove-row inline-flex h-8 w-8 items-center justify-center rounded-full bg-red-500 text-white shadow hover:bg-red-600"
                            title="{{ __('Remove row') }}">×</button>
        </div>
            </td>
        </tr>
    </template>

    <script>
        const formElement = document.getElementById('bankVoucherForm');
        const rowsContainer = document.getElementById('voucherEntryRows');
        const addRowButton = document.getElementById('addEntryRow');
        const rowTemplate = document.getElementById('voucher-row-template');
        const totalDisplay = document.getElementById('totalAmountDisplay');
        const totalInput = document.getElementById('totalAmountInput');
        const vendorSelectControl = document.getElementById('vendor_id');

        function recalculateTotals() {
            let total = 0;
            rowsContainer.querySelectorAll('.entry-row').forEach(row => {
                const typeSelect = row.querySelector('.drcr-select');
                const amountInput = row.querySelector('.amount-input');
                const amount = parseFloat(amountInput.value || '0');
                if (!isNaN(amount) && typeSelect.value === 'Dr') {
                    total += amount;
                }
            });

            totalDisplay.textContent = total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            totalInput.value = total.toFixed(2);
        }

        function renumberRows() {
            rowsContainer.querySelectorAll('.entry-row').forEach((row, index) => {
                row.dataset.rowIndex = index;
                row.querySelectorAll('[name]').forEach(input => {
                    const fieldMatch = input.name.match(/entries\[\d+]\[(.+)]/);
                    if (fieldMatch) {
                        input.name = `entries[${index}][${fieldMatch[1]}]`;
                    }
                });
            });
        }

        function attachRowListeners(row) {
            row.querySelectorAll('.amount-input, .drcr-select').forEach(element => {
                element.addEventListener('input', recalculateTotals);
                element.addEventListener('change', recalculateTotals);
            });

            const accountSelect = row.querySelector('.account-select');
            const accountNameInput = row.querySelector('.account-name-input');
            if (accountSelect && accountNameInput) {
                accountSelect.addEventListener('change', () => {
                    const selectedOption = accountSelect.selectedOptions[0];
                    if (selectedOption && selectedOption.dataset.name) {
                        accountNameInput.value = selectedOption.dataset.name;
                    } else {
                        accountNameInput.value = '';
                    }
                });

                if (accountSelect.value) {
                    const selectedOption = accountSelect.selectedOptions[0];
                    if (selectedOption && selectedOption.dataset.name) {
                        accountNameInput.value = selectedOption.dataset.name;
                    }
                }
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
                attachRowListeners(rowsContainer.lastElementChild);
                recalculateTotals();
            });
        }

        rowsContainer.querySelectorAll('.entry-row').forEach(row => attachRowListeners(row));
        recalculateTotals();

        function prepareVoucherSubmission() {
            recalculateTotals();

            if (!vendorSelectControl || !vendorSelectControl.value) {
                alert('{{ __('Please select a vendor before saving the voucher.') }}');
                vendorSelectControl?.focus();
                return false;
            }

            if (!totalInput.value || parseFloat(totalInput.value) <= 0) {
                alert('{{ __('Please enter at least one debit amount before saving the voucher.') }}');
                return false;
            }

            return true;
        }
    </script>
</x-app-layout>
