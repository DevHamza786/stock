<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Production Details</h1>
                        <p class="mt-2 text-gray-600">View daily production details and related information</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('stock-management.daily-production.edit', $dailyProduction) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Edit Production
                        </a>
                        <a href="{{ route('stock-management.daily-production.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Back to List
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Production Information -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900">Production Information</h2>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Product</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $dailyProduction->product }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Machine Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $dailyProduction->machine_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Operator Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $dailyProduction->operator_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Production Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $dailyProduction->date->format('M d, Y') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Total Pieces Produced</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($dailyProduction->total_pieces) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Total Sqft Produced</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($dailyProduction->total_sqft, 2) }} sqft</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Condition Status</dt>
                                    <dd class="mt-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($dailyProduction->condition_status === 'Polished') bg-green-100 text-green-800
                                            @elseif($dailyProduction->condition_status === 'Slabs') bg-yellow-100 text-yellow-800
                                            @elseif($dailyProduction->condition_status === 'Blocks') bg-blue-100 text-blue-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $dailyProduction->condition_status }}
                                        </span>
                                    </dd>
                                </div>
                                @if($dailyProduction->notes)
                                <div class="md:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Notes</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $dailyProduction->notes }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- Production Statistics -->
                    <div class="mt-8 bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900">Production Statistics</h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-purple-600">{{ number_format($dailyProduction->total_pieces) }}</div>
                                    <div class="text-sm text-gray-500">Pieces Produced</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600">{{ number_format($dailyProduction->total_sqft, 2) }}</div>
                                    <div class="text-sm text-gray-500">Sqft Produced</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600">{{ number_format($dailyProduction->total_sqft / $dailyProduction->total_pieces, 2) }}</div>
                                    <div class="text-sm text-gray-500">Avg Sqft per Piece</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                        </div>
                        <div class="p-6 space-y-3">
                            <a href="{{ route('stock-management.daily-production.edit', $dailyProduction) }}" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 text-center block">
                                Edit Production
                            </a>
                            <a href="{{ route('stock-management.daily-production.create') }}" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 text-center block">
                                Record New Production
                            </a>
                            <form method="POST" action="{{ route('stock-management.daily-production.destroy', $dailyProduction) }}" class="w-full" onsubmit="return confirm('Are you sure you want to delete this production record?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                    Delete Production
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Source Stock Information -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Source Stock</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Stock Addition</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <a href="{{ route('stock-management.stock-additions.show', $dailyProduction->stockAddition) }}" class="text-blue-600 hover:text-blue-900">
                                            {{ $dailyProduction->stockAddition->product->name }}
                                        </a>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Stone Type</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $dailyProduction->stockAddition->stone }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Mine Vendor</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $dailyProduction->stockAddition->mineVendor->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Size (3D)</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $dailyProduction->stockAddition->size_3d }}</dd>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Production Status -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Production Status</h3>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">Production Completed</p>
                                    <p class="text-sm text-gray-500">{{ $dailyProduction->condition_status }} quality</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Related Information -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Related Information</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Product Category</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $dailyProduction->stockAddition->product->category ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Vendor Contact</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $dailyProduction->stockAddition->mineVendor->contact_person ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Vendor Phone</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $dailyProduction->stockAddition->mineVendor->phone ?? 'N/A' }}</dd>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Production Efficiency -->
            <div class="mt-8 bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Production Efficiency</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Production Metrics -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Production Metrics</h3>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Pieces per Hour (Estimated)</span>
                                    <span class="text-sm font-medium text-gray-900">{{ number_format($dailyProduction->total_pieces / 8, 1) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Sqft per Hour (Estimated)</span>
                                    <span class="text-sm font-medium text-gray-900">{{ number_format($dailyProduction->total_sqft / 8, 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Average Piece Size</span>
                                    <span class="text-sm font-medium text-gray-900">{{ number_format($dailyProduction->total_sqft / $dailyProduction->total_pieces, 2) }} sqft</span>
                                </div>
                            </div>
                        </div>

                        <!-- Quality Metrics -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Quality Metrics</h3>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Condition Status</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($dailyProduction->condition_status === 'Polished') bg-green-100 text-green-800
                                        @elseif($dailyProduction->condition_status === 'Slabs') bg-yellow-100 text-yellow-800
                                        @elseif($dailyProduction->condition_status === 'Blocks') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ $dailyProduction->condition_status }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Production Quality</span>
                                    <span class="text-sm font-medium text-gray-900">
                                        @if($dailyProduction->condition_status === 'Polished')
                                            Excellent
                                        @elseif($dailyProduction->condition_status === 'Slabs')
                                            Good
                                        @elseif($dailyProduction->condition_status === 'Blocks')
                                            Standard
                                        @else
                                            Basic
                                        @endif
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Operator Performance</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $dailyProduction->operator_name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
