<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Chart of Accounts</h1>
                        <p class="mt-2 text-gray-600">Manage your accounting accounts and their hierarchy</p>
                    </div>
                    <div class="flex space-x-4">
                        <a href="{{ route('accounting.dashboard') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Back to Dashboard
                        </a>
                        <a href="{{ route('accounting.chart-of-accounts.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Add New Account
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200 mb-8">
                <div class="p-6">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="account_type" class="block text-sm font-medium text-gray-700 mb-2">Account Type</label>
                            <select id="account_type" name="account_type" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">All Types</option>
                                @foreach($accountTypes as $key => $value)
                                    <option value="{{ $key }}" {{ request('account_type') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="is_active" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select id="is_active" name="is_active" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">All Status</option>
                                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Search by name or code..." class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Accounts Table -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtype</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Normal Balance</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Balance</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($accounts as $account)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $account->account_code }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <div class="flex items-center">
                                            @if($account->level > 1)
                                                <span class="ml-{{ ($account->level - 1) * 4 }} text-gray-400">└─</span>
                                            @endif
                                            {{ $account->account_name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($account->account_type === 'ASSET') bg-green-100 text-green-800
                                            @elseif($account->account_type === 'LIABILITY') bg-red-100 text-red-800
                                            @elseif($account->account_type === 'EQUITY') bg-blue-100 text-blue-800
                                            @elseif($account->account_type === 'REVENUE') bg-yellow-100 text-yellow-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ $account->account_type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $account->account_subtype }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($account->normal_balance === 'DEBIT') bg-blue-100 text-blue-800
                                            @else bg-green-100 text-green-800
                                            @endif">
                                            {{ $account->normal_balance }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($account->current_balance, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($account->is_active) bg-green-100 text-green-800
                                            @else bg-red-100 text-red-800
                                            @endif">
                                            {{ $account->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('accounting.chart-of-accounts.show', $account) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                            <a href="{{ route('accounting.chart-of-accounts.edit', $account) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <form method="POST" action="{{ route('accounting.chart-of-accounts.toggle-status', $account) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-yellow-600 hover:text-yellow-900">
                                                    {{ $account->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                        <svg class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Accounts Found</h3>
                                        <p class="text-gray-500 mb-4">Get started by creating your first account.</p>
                                        <a href="{{ route('accounting.chart-of-accounts.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                            Add Account
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($accounts->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $accounts->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
