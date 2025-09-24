<x-app-layout>
    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center">
                    <a href="{{ route('accounting.chart-of-accounts.index') }}" class="mr-4 text-gray-600 hover:text-gray-900 transition-colors duration-200">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Create New Account</h1>
                        <p class="mt-2 text-gray-600">Add a new account to your chart of accounts</p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="p-6">
                    <form method="POST" action="{{ route('accounting.chart-of-accounts.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Account Code -->
                            <div>
                                <label for="account_code" class="block text-sm font-medium text-gray-700 mb-2">Account Code *</label>
                                <input type="text" id="account_code" name="account_code" value="{{ old('account_code') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('account_code') border-red-500 @enderror" placeholder="e.g., 1130" required>
                                @error('account_code')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Account Name -->
                            <div>
                                <label for="account_name" class="block text-sm font-medium text-gray-700 mb-2">Account Name *</label>
                                <input type="text" id="account_name" name="account_name" value="{{ old('account_name') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('account_name') border-red-500 @enderror" placeholder="e.g., Stone Inventory" required>
                                @error('account_name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Account Type -->
                            <div>
                                <label for="account_type" class="block text-sm font-medium text-gray-700 mb-2">Account Type *</label>
                                <select id="account_type" name="account_type" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('account_type') border-red-500 @enderror" required>
                                    <option value="">Select account type...</option>
                                    @foreach($accountTypes as $key => $value)
                                        <option value="{{ $key }}" {{ old('account_type') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                    @endforeach
                                </select>
                                @error('account_type')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Account Subtype -->
                            <div>
                                <label for="account_subtype" class="block text-sm font-medium text-gray-700 mb-2">Account Subtype *</label>
                                <select id="account_subtype" name="account_subtype" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('account_subtype') border-red-500 @enderror" required>
                                    <option value="">Select account subtype...</option>
                                    @foreach($accountSubtypes as $key => $value)
                                        <option value="{{ $key }}" {{ old('account_subtype') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                    @endforeach
                                </select>
                                @error('account_subtype')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Normal Balance -->
                            <div>
                                <label for="normal_balance" class="block text-sm font-medium text-gray-700 mb-2">Normal Balance *</label>
                                <select id="normal_balance" name="normal_balance" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('normal_balance') border-red-500 @enderror" required>
                                    <option value="">Select normal balance...</option>
                                    <option value="DEBIT" {{ old('normal_balance') == 'DEBIT' ? 'selected' : '' }}>Debit</option>
                                    <option value="CREDIT" {{ old('normal_balance') == 'CREDIT' ? 'selected' : '' }}>Credit</option>
                                </select>
                                @error('normal_balance')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Parent Account -->
                            <div>
                                <label for="parent_account_id" class="block text-sm font-medium text-gray-700 mb-2">Parent Account</label>
                                <select id="parent_account_id" name="parent_account_id" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('parent_account_id') border-red-500 @enderror">
                                    <option value="">No parent account</option>
                                    @foreach($parentAccounts as $parent)
                                        <option value="{{ $parent->id }}" {{ old('parent_account_id') == $parent->id ? 'selected' : '' }}>
                                            {{ $parent->account_code }} - {{ $parent->account_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('parent_account_id')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Level -->
                            <div>
                                <label for="level" class="block text-sm font-medium text-gray-700 mb-2">Level *</label>
                                <select id="level" name="level" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('level') border-red-500 @enderror" required>
                                    <option value="">Select level...</option>
                                    <option value="1" {{ old('level') == '1' ? 'selected' : '' }}>Level 1 (Main Account)</option>
                                    <option value="2" {{ old('level') == '2' ? 'selected' : '' }}>Level 2 (Sub Account)</option>
                                    <option value="3" {{ old('level') == '3' ? 'selected' : '' }}>Level 3 (Detail Account)</option>
                                    <option value="4" {{ old('level') == '4' ? 'selected' : '' }}>Level 4 (Sub Detail)</option>
                                    <option value="5" {{ old('level') == '5' ? 'selected' : '' }}>Level 5 (Final Detail)</option>
                                </select>
                                @error('level')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Opening Balance -->
                            <div>
                                <label for="opening_balance" class="block text-sm font-medium text-gray-700 mb-2">Opening Balance *</label>
                                <input type="number" id="opening_balance" name="opening_balance" value="{{ old('opening_balance', 0) }}" step="0.01" min="0" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('opening_balance') border-red-500 @enderror" required>
                                @error('opening_balance')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="md:col-span-2">
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea id="description" name="description" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror" placeholder="Enter account description...">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status Options -->
                            <div class="md:col-span-2">
                                <div class="flex items-center space-x-6">
                                    <div class="flex items-center">
                                        <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <label for="is_active" class="ml-2 block text-sm text-gray-900">Active Account</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="is_system_account" name="is_system_account" value="1" {{ old('is_system_account') ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <label for="is_system_account" class="ml-2 block text-sm text-gray-900">System Account</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end space-x-4 mt-8">
                            <a href="{{ route('accounting.chart-of-accounts.index') }}" class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors duration-200">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                Create Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-set normal balance based on account type
        document.getElementById('account_type').addEventListener('change', function() {
            const accountType = this.value;
            const normalBalanceSelect = document.getElementById('normal_balance');

            if (accountType === 'ASSET' || accountType === 'EXPENSE') {
                normalBalanceSelect.value = 'DEBIT';
            } else if (accountType === 'LIABILITY' || accountType === 'EQUITY' || accountType === 'REVENUE') {
                normalBalanceSelect.value = 'CREDIT';
            }
        });
    </script>
</x-app-layout>
