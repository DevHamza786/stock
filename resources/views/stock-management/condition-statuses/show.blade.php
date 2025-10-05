<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Condition Status Details</h1>
                        <p class="mt-2 text-gray-600">View condition status information and usage</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('stock-management.condition-statuses.edit', $conditionStatus) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center transition-colors duration-200">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit
                        </a>
                        <a href="{{ route('stock-management.condition-statuses.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center transition-colors duration-200">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to List
                        </a>
                    </div>
                </div>
            </div>

            <div class="w-full">
                <!-- Main Information -->
                <div class="w-full">
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="p-6">
                            <!-- Condition Status Header -->
                            <div class="flex items-center mb-6">
                                <div class="h-16 w-16 rounded-xl flex items-center justify-center mr-4" style="background-color: {{ $conditionStatus->color }};">
                                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-2xl font-bold text-gray-900">{{ $conditionStatus->name }}</h2>
                                    <div class="flex items-center space-x-4 mt-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $conditionStatus->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $conditionStatus->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                        <span class="text-sm text-gray-500">Sort Order: {{ $conditionStatus->sort_order }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            @if($conditionStatus->description)
                                <div class="mb-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Description</h3>
                                    <p class="text-gray-600">{{ $conditionStatus->description }}</p>
                                </div>
                            @endif

                            <!-- Color Information -->
                            <div class="mb-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Color Information</h3>
                                <div class="flex items-center space-x-4">
                                    <div class="h-8 w-8 rounded-lg" style="background-color: {{ $conditionStatus->color }};"></div>
                                    <span class="text-sm text-gray-600 font-mono">{{ $conditionStatus->color }}</span>
                                    <span class="text-sm text-gray-500">Used for badges and UI elements</span>
                                </div>
                            </div>

                            <!-- Usage Statistics -->
                            <div class="mb-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Usage Statistics</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-blue-50 p-4 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="text-2xl font-bold text-blue-900">{{ $conditionStatus->stockAdditions()->count() }}</div>
                                                <div class="text-sm text-blue-600">Stock Additions</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-green-50 p-4 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="text-2xl font-bold text-green-900">{{ $conditionStatus->dailyProductionItems()->count() }}</div>
                                                <div class="text-sm text-green-600">Daily Productions</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Activity -->
                            @if($conditionStatus->stockAdditions()->count() > 0 || $conditionStatus->dailyProductionItems()->count() > 0)
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Activity</h3>
                                    <div class="space-y-3">
                                        @foreach($conditionStatus->stockAdditions()->with('product')->latest()->limit(5)->get() as $stockAddition)
                                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                                <div class="flex items-center">
                                                    <div class="h-8 w-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                                        <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">Stock Addition</div>
                                                        <div class="text-sm text-gray-500">{{ $stockAddition->product->name }} - {{ $stockAddition->date->format('M d, Y') }}</div>
                                                    </div>
                                                </div>
                                                <a href="{{ route('stock-management.stock-additions.show', $stockAddition) }}" class="text-blue-600 hover:text-blue-900 text-sm">
                                                    View
                                                </a>
                                            </div>
                                        @endforeach

                                        @foreach($conditionStatus->dailyProductionItems()->with('dailyProduction')->latest()->limit(3)->get() as $productionItem)
                                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                                <div class="flex items-center">
                                                    <div class="h-8 w-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                                        <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">Daily Production</div>
                                                        <div class="text-sm text-gray-500">{{ $productionItem->dailyProduction->machine_name ?? 'N/A' }} - {{ $productionItem->dailyProduction->date->format('M d, Y') ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                                <a href="{{ route('stock-management.daily-production.show', $productionItem->dailyProduction) }}" class="text-green-600 hover:text-green-900 text-sm">
                                                    View
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mt-8 bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                    </div>
                    <div class="p-6">
                        <div class="flex flex-wrap gap-4">
                            <a href="{{ route('stock-management.condition-statuses.edit', $conditionStatus) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors duration-200">
                                Edit Condition Status
                            </a>

                            <form method="POST" action="{{ route('stock-management.condition-statuses.toggle-status', $conditionStatus) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors duration-200">
                                    {{ $conditionStatus->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>

                            @if($conditionStatus->stockAdditions()->count() == 0 && $conditionStatus->dailyProductionItems()->count() == 0)
                                <form method="POST" action="{{ route('stock-management.condition-statuses.destroy', $conditionStatus) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this condition status?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors duration-200">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
