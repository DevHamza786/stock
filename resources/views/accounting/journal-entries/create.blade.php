<x-app-layout>
    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center">
                    <a href="{{ route('accounting.journal-entries.index') }}" class="mr-4 text-gray-600 hover:text-gray-900 transition-colors duration-200">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Create Journal Entry</h1>
                        <p class="mt-2 text-gray-600">Record a new accounting transaction</p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="p-6">
                    <form method="POST" action="{{ route('accounting.journal-entries.store') }}" id="journal-entry-form">
                        @csrf

                        <!-- Entry Details -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <div>
                                <label for="entry_date" class="block text-sm font-medium text-gray-700 mb-2">Entry Date *</label>
                                <input type="date" id="entry_date" name="entry_date" value="{{ old('entry_date', date('Y-m-d')) }}" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('entry_date') border-red-500 @enderror" required>
                                @error('entry_date')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="entry_type" class="block text-sm font-medium text-gray-700 mb-2">Entry Type *</label>
                                <select id="entry_type" name="entry_type" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('entry_type') border-red-500 @enderror" required>
                                    <option value="">Select entry type...</option>
                                    @foreach($entryTypes as $key => $value)
                                        <option value="{{ $key }}" {{ old('entry_type') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                    @endforeach
                                </select>
                                @error('entry_type')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Balance Check</label>
                                <div id="balance-check" class="px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                                    <span class="text-sm text-gray-500">Add transactions to check balance</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                            <textarea id="description" name="description" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror" placeholder="Enter journal entry description..." required>{{ old('description') }}</textarea>
                            @error('description')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea id="notes" name="notes" rows="2" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('notes') border-red-500 @enderror" placeholder="Additional notes...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Transactions -->
                        <div class="mb-8">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Transactions</h3>
                                <button type="button" id="add-transaction" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                    Add Transaction
                                </button>
                            </div>

                            <div id="transactions-container">
                                <!-- Transactions will be added here dynamically -->
                            </div>

                            @error('transactions')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end space-x-4">
                            <a href="{{ route('accounting.journal-entries.index') }}" class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors duration-200">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                Create Journal Entry
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let transactionCount = 0;
        const accounts = @json($accounts);

        document.getElementById('add-transaction').addEventListener('click', function() {
            addTransaction();
        });

        function addTransaction() {
            transactionCount++;
            const container = document.getElementById('transactions-container');

            const transactionHtml = `
                <div class="transaction-row border border-gray-200 rounded-lg p-4 mb-4" data-index="${transactionCount}">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Account *</label>
                            <select name="transactions[${transactionCount}][account_id]" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                <option value="">Select account...</option>
                                ${accounts.map(account => `<option value="${account.id}">${account.account_code} - ${account.account_name}</option>`).join('')}
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                            <input type="text" name="transactions[${transactionCount}][description]" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Transaction description..." required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Debit Amount</label>
                            <input type="number" name="transactions[${transactionCount}][debit_amount]" step="0.01" min="0" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent debit-amount" placeholder="0.00">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Credit Amount</label>
                            <input type="number" name="transactions[${transactionCount}][credit_amount]" step="0.01" min="0" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent credit-amount" placeholder="0.00">
                        </div>
                        <div class="flex items-end">
                            <button type="button" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 remove-transaction">
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', transactionHtml);
            updateBalanceCheck();

            // Add event listeners for the new transaction
            const newRow = container.lastElementChild;
            newRow.querySelector('.remove-transaction').addEventListener('click', function() {
                newRow.remove();
                updateBalanceCheck();
            });

            newRow.querySelector('.debit-amount').addEventListener('input', function() {
                if (this.value > 0) {
                    newRow.querySelector('.credit-amount').value = '';
                }
                updateBalanceCheck();
            });

            newRow.querySelector('.credit-amount').addEventListener('input', function() {
                if (this.value > 0) {
                    newRow.querySelector('.debit-amount').value = '';
                }
                updateBalanceCheck();
            });
        }

        function updateBalanceCheck() {
            const debitInputs = document.querySelectorAll('.debit-amount');
            const creditInputs = document.querySelectorAll('.credit-amount');

            let totalDebit = 0;
            let totalCredit = 0;

            debitInputs.forEach(input => {
                totalDebit += parseFloat(input.value) || 0;
            });

            creditInputs.forEach(input => {
                totalCredit += parseFloat(input.value) || 0;
            });

            const balanceCheck = document.getElementById('balance-check');
            const difference = Math.abs(totalDebit - totalCredit);

            if (totalDebit === 0 && totalCredit === 0) {
                balanceCheck.innerHTML = '<span class="text-sm text-gray-500">Add transactions to check balance</span>';
                balanceCheck.className = 'px-3 py-2 border border-gray-300 rounded-lg bg-gray-50';
            } else if (difference < 0.01) {
                balanceCheck.innerHTML = `<span class="text-sm text-green-600 font-medium">✓ Balanced: $${totalDebit.toFixed(2)}</span>`;
                balanceCheck.className = 'px-3 py-2 border border-green-300 rounded-lg bg-green-50';
            } else {
                balanceCheck.innerHTML = `<span class="text-sm text-red-600 font-medium">✗ Unbalanced: Debit $${totalDebit.toFixed(2)} | Credit $${totalCredit.toFixed(2)}</span>`;
                balanceCheck.className = 'px-3 py-2 border border-red-300 rounded-lg bg-red-50';
            }
        }

        // Add initial transaction
        addTransaction();
    </script>
</x-app-layout>
