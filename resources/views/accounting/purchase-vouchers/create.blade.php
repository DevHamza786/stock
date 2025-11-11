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
                <span>{{ __('Create') }}</span>
            </div>

            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('Create Purchase Voucher') }}</h1>
                <p class="mt-2 text-gray-600">
                    {{ __('Record a vendor bill into the payable ledger. The bill will be available for knock-off during payment.') }}
                </p>
            </div>

            <form method="POST" action="{{ route('accounting.purchase-vouchers.store') }}" class="space-y-6">
                @csrf

                <div class="rounded-2xl border border-gray-200 bg-white shadow-xl">
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-5">
                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    {{ __('Voucher Number') }}
                                </p>
                                <p class="mt-1 text-xl font-semibold text-gray-900">
                                    {{ $nextVoucherNumber }}
                                </p>
                            </div>
                            <div>
                                <label for="payable_account_id"
                                       class="block text-sm font-semibold uppercase tracking-wide text-gray-700">
                                    {{ __('Payable Ledger') }}
                                </label>
                                <select
                                    id="payable_account_id"
                                    name="payable_account_id"
                                    required
                                    class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                >
                                    <option value="">{{ __('Select payable account') }}</option>
                                    @foreach($payableAccounts as $account)
                                        <option value="{{ $account->id }}" {{ (int) old('payable_account_id') === $account->id ? 'selected' : '' }}>
                                            {{ $account->account_code }} — {{ $account->account_name }}
                                        </option>
                                    @endforeach
                                </select>
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
                                    value="{{ old('vendor_reference') }}"
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
                                    value="{{ old('bill_number') }}"
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
                                    value="{{ old('bill_date', now()->format('Y-m-d')) }}"
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
                                    value="{{ old('due_date') }}"
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
                                    value="{{ old('total_amount') }}"
                                    required
                                    placeholder="0.00"
                                    class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                            </div>
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
                            >{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('accounting.purchase-vouchers.index') }}"
                       class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        {{ __('Save Purchase Voucher') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

