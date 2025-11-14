<x-app-layout>
    <div class="py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-12 xl:px-16 space-y-8">
            <div class="flex items-center gap-3 text-sm text-gray-500">
                <a href="{{ route('accounting.purchase-vouchers.index') }}"
                   class="inline-flex items-center gap-2 font-medium text-blue-600 hover:text-blue-700">
                    <span aria-hidden="true" class="text-lg">←</span>
                    {{ __('Purchase Vouchers') }}
                </a>
                <span class="text-gray-400">/</span>
                <span>{{ __('Edit Draft') }}</span>
            </div>

            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('Edit Purchase Voucher') }}</h1>
                <p class="mt-2 text-gray-600">
                    {{ __('Update the draft purchase voucher and post it to the system.') }}
                </p>
            </div>

            @if($voucher->stockAddition)
                <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3">
                    <p class="text-sm text-blue-800">
                        <strong>{{ __('Linked Stock Addition:') }}</strong> {{ $voucher->stockAddition->pid ?? 'N/A' }}
                        @if($voucher->stockAddition->mineVendor)
                            — {{ __('Vendor:') }} {{ $voucher->stockAddition->mineVendor->name }}
                        @endif
                    </p>
                </div>
            @endif

            <form method="POST" action="{{ route('accounting.purchase-vouchers.update', $voucher) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="rounded-2xl border border-gray-200 bg-white shadow-xl">
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-5">
                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    {{ __('Voucher Number') }}
                                </p>
                                <p class="mt-1 text-xl font-semibold text-gray-900">
                                    {{ $voucher->voucher_number }}
                                </p>
                            </div>
                            <div class="space-y-3">
                                <div>
                                    <label for="account_code_search"
                                           class="block text-sm font-semibold uppercase tracking-wide text-gray-700">
                                        {{ __('Account Code') }}
                                    </label>
                                    <input
                                        type="text"
                                        id="account_code_search"
                                        placeholder="{{ __('Search by account code...') }}"
                                        class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                        autocomplete="off"
                                    >
                                    <input type="hidden" id="payable_account_id" name="payable_account_id" value="{{ old('payable_account_id', optional($voucher->bill)->chart_of_account_id) }}" required>
                                </div>
                                <div>
                                    <label for="account_name_search"
                                           class="block text-sm font-semibold uppercase tracking-wide text-gray-700">
                                        {{ __('Account Name') }}
                                    </label>
                                    <input
                                        type="text"
                                        id="account_name_search"
                                        placeholder="{{ __('Search by account name...') }}"
                                        class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                        autocomplete="off"
                                    >
                                    <div id="account_display" class="mt-2 text-sm text-gray-600">
                                        @if($voucher->bill && $voucher->bill->account)
                                            {{ $voucher->bill->account->account_code }} — {{ $voucher->bill->account->account_name }}
                                        @endif
                                    </div>
                                </div>
                                <div id="account_dropdown" class="hidden absolute z-10 mt-1 w-full bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto"></div>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-6 space-y-6">
                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <label for="vendor_reference" class="block text-sm font-semibold uppercase tracking-wide text-gray-700">
                                    {{ __('Vendor / Supplier') }}
                                </label>
                                <input
                                    type="text"
                                    id="vendor_reference"
                                    name="vendor_reference"
                                    value="{{ old('vendor_reference', optional($voucher->bill)->vendor_reference) }}"
                                    placeholder="{{ __('Vendor or supplier name') }}"
                                    class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                            </div>
                            <div>
                                <label for="bill_number" class="block text-sm font-semibold uppercase tracking-wide text-gray-700">
                                    {{ __('Bill / Invoice Number') }}
                                </label>
                                <input
                                    type="text"
                                    id="bill_number"
                                    name="bill_number"
                                    value="{{ old('bill_number', optional($voucher->bill)->bill_number) }}"
                                    placeholder="{{ __('Reference number') }}"
                                    class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <label for="bill_date" class="block text-sm font-semibold uppercase tracking-wide text-gray-700">
                                    {{ __('Bill Date') }}
                                </label>
                                <input
                                    type="date"
                                    id="bill_date"
                                    name="bill_date"
                                    value="{{ old('bill_date', optional($voucher->bill)->bill_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                                    required
                                    class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                            </div>
                            <div>
                                <label for="due_date" class="block text-sm font-semibold uppercase tracking-wide text-gray-700">
                                    {{ __('Due Date') }}
                                </label>
                                <input
                                    type="date"
                                    id="due_date"
                                    name="due_date"
                                    value="{{ old('due_date', optional($voucher->bill)->due_date?->format('Y-m-d')) }}"
                                    class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <label for="total_amount" class="block text-sm font-semibold uppercase tracking-wide text-gray-700">
                                    {{ __('Bill Amount') }}
                                </label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    id="total_amount"
                                    name="total_amount"
                                    value="{{ old('total_amount', $voucher->total_amount) }}"
                                    required
                                    placeholder="0.00"
                                    class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                            </div>
                            <div>
                                <label for="bill_adjustment" class="block text-sm font-semibold uppercase tracking-wide text-gray-700">
                                    {{ __('Bill Adjustment') }}
                                </label>
                                <input
                                    type="text"
                                    id="bill_adjustment"
                                    name="bill_adjustment"
                                    value="{{ old('bill_adjustment', optional($voucher->bill)->bill_adjustment) }}"
                                    placeholder="{{ __('Bill adjustment details') }}"
                                    class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                            </div>
                        </div>

                        <div>
                            <label for="particulars" class="block text-sm font-semibold uppercase tracking-wide text-gray-700">
                                {{ __('Particulars / Narrations') }}
                            </label>
                            <textarea
                                id="particulars"
                                name="particulars"
                                rows="3"
                                placeholder="{{ __('Enter particulars or narrations for this bill') }}"
                                class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            >{{ old('particulars', optional($voucher->bill)->particulars) }}</textarea>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-semibold uppercase tracking-wide text-gray-700">
                                {{ __('Notes') }}
                            </label>
                            <textarea
                                id="notes"
                                name="notes"
                                rows="4"
                                placeholder="{{ __('Optional notes about this bill') }}"
                                class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            >{{ old('notes', $voucher->notes) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('accounting.purchase-vouchers.index') }}"
                       class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit"
                            name="save"
                            value="save"
                            class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
                        {{ __('Save Draft') }}
                    </button>
                    <button type="submit"
                            name="post"
                            value="1"
                            class="inline-flex items-center rounded-lg bg-green-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        {{ __('Post Voucher') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const accounts = @json($accounts ?? []);

        const accountCodeInput = document.getElementById('account_code_search');
        const accountNameInput = document.getElementById('account_name_search');
        const accountIdInput = document.getElementById('payable_account_id');
        const accountDisplay = document.getElementById('account_display');
        const accountDropdown = document.getElementById('account_dropdown');

        function filterAccounts(codeTerm = '', nameTerm = '') {
            return accounts.filter(acc => {
                const codeMatch = !codeTerm || acc.code.toLowerCase().includes(codeTerm.toLowerCase());
                const nameMatch = !nameTerm || acc.name.toLowerCase().includes(nameTerm.toLowerCase());
                return codeMatch && nameMatch;
            });
        }

        function showDropdown(filteredAccounts) {
            if (filteredAccounts.length === 0) {
                accountDropdown.classList.add('hidden');
                return;
            }

            accountDropdown.innerHTML = filteredAccounts.slice(0, 10).map(acc => `
                <div class="px-4 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100" 
                     onclick="selectAccount(${acc.id}, '${acc.code.replace(/'/g, "\\'")}', '${acc.name.replace(/'/g, "\\'")}')">
                    <div class="font-medium text-gray-900">${acc.code}</div>
                    <div class="text-sm text-gray-500">${acc.name}</div>
                </div>
            `).join('');
            accountDropdown.classList.remove('hidden');
        }

        function selectAccount(id, code, name) {
            accountIdInput.value = id;
            accountDisplay.textContent = `${code} — ${name}`;
            accountCodeInput.value = code;
            accountNameInput.value = name;
            accountDropdown.classList.add('hidden');
        }

        accountCodeInput.addEventListener('input', function() {
            const filtered = filterAccounts(this.value, accountNameInput.value);
            showDropdown(filtered);
        });

        accountNameInput.addEventListener('input', function() {
            const filtered = filterAccounts(accountCodeInput.value, this.value);
            showDropdown(filtered);
        });

        // Initialize display if account is already selected
        @if($voucher->bill && $voucher->bill->account)
            accountCodeInput.value = '{{ $voucher->bill->account->account_code }}';
            accountNameInput.value = '{{ $voucher->bill->account->account_name }}';
        @endif

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!accountCodeInput.contains(e.target) && !accountNameInput.contains(e.target) && !accountDropdown.contains(e.target)) {
                accountDropdown.classList.add('hidden');
            }
        });
    </script>
</x-app-layout>

