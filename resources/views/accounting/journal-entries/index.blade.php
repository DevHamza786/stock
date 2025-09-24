<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Journal Entries</h1>
                        <p class="mt-2 text-gray-600">Manage your accounting journal entries</p>
                    </div>
                    <div class="flex space-x-4">
                        <a href="{{ route('accounting.dashboard') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Back to Dashboard
                        </a>
                        <a href="{{ route('accounting.journal-entries.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            New Journal Entry
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200 mb-8">
                <div class="p-6">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select id="status" name="status" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">All Status</option>
                                @foreach($statuses as $key => $value)
                                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="entry_type" class="block text-sm font-medium text-gray-700 mb-2">Entry Type</label>
                            <select id="entry_type" name="entry_type" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">All Types</option>
                                @foreach($entryTypes as $key => $value)
                                    <option value="{{ $key }}" {{ request('entry_type') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                            <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                            <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Journal Entries Table -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entry #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($journalEntries as $entry)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <a href="{{ route('accounting.journal-entries.show', $entry) }}" class="text-blue-600 hover:text-blue-800">
                                            {{ $entry->entry_number }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $entry->entry_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ Str::limit($entry->description, 50) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ $entryTypes[$entry->entry_type] ?? $entry->entry_type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($entry->status === 'POSTED') bg-green-100 text-green-800
                                            @elseif($entry->status === 'DRAFT') bg-yellow-100 text-yellow-800
                                            @else bg-red-100 text-red-800
                                            @endif">
                                            {{ $statuses[$entry->status] ?? $entry->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($entry->total_debit, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $entry->creator->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('accounting.journal-entries.show', $entry) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                            @if($entry->status === 'DRAFT')
                                                <a href="{{ route('accounting.journal-entries.edit', $entry) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                                <form method="POST" action="{{ route('accounting.journal-entries.post', $entry) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-green-600 hover:text-green-900">Post</button>
                                                </form>
                                            @endif
                                            @if($entry->status === 'POSTED')
                                                <form method="POST" action="{{ route('accounting.journal-entries.reverse', $entry) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to reverse this entry?')">Reverse</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                        <svg class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Journal Entries</h3>
                                        <p class="text-gray-500 mb-4">Get started by creating your first journal entry.</p>
                                        <a href="{{ route('accounting.journal-entries.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                            Create Journal Entry
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($journalEntries->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $journalEntries->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
