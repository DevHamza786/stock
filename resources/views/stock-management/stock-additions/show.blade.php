<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Stock Details</h1>
                        <p class="mt-2 text-gray-600">View stock addition details and related information</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('stock-management.stock-additions.edit', $stockAddition) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Edit Stock
                        </a>
                        <a href="{{ route('stock-management.stock-additions.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Back to List
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Stock Information -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900">Stock Information</h2>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Product</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $stockAddition->product->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Mine Vendor</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $stockAddition->mineVendor->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Stone Type</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $stockAddition->stone }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Size (3D)</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $stockAddition->size_3d }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Total Pieces</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($stockAddition->total_pieces) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Total Sqft</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($stockAddition->total_sqft, 2) }} sqft</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Condition Status</dt>
                                    <dd class="mt-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($stockAddition->condition_status === 'Block') bg-blue-100 text-blue-800
                                            @elseif($stockAddition->condition_status === 'Slabs') bg-green-100 text-green-800
                                            @elseif($stockAddition->condition_status === 'Polished') bg-purple-100 text-purple-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $stockAddition->condition_status }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Date Added</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $stockAddition->date->format('M d, Y') }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Stock Usage Summary -->
                    <div class="mt-8 bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900">Stock Usage Summary</h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600">{{ number_format($stockAddition->total_pieces) }}</div>
                                    <div class="text-sm text-gray-500">Total Pieces</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600">{{ number_format($stockAddition->available_pieces) }}</div>
                                    <div class="text-sm text-gray-500">Available Pieces</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-red-600">{{ number_format($stockAddition->total_pieces - $stockAddition->available_pieces) }}</div>
                                    <div class="text-sm text-gray-500">Issued Pieces</div>
                                </div>
                            </div>
                            <div class="mt-6">
                                <div class="flex justify-between text-sm text-gray-600 mb-1">
                                    <span>Stock Usage</span>
                                    <span>{{ number_format((($stockAddition->total_pieces - $stockAddition->available_pieces) / $stockAddition->total_pieces) * 100, 1) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ (($stockAddition->total_pieces - $stockAddition->available_pieces) / $stockAddition->total_pieces) * 100 }}%"></div>
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
                            @if($stockAddition->available_pieces > 0)
                                <a href="{{ route('stock-management.stock-issued.create', ['stock_addition_id' => $stockAddition->id]) }}" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 text-center block">
                                    Issue Stock
                                </a>
                            @endif
                            <a href="{{ route('stock-management.daily-production.create', ['stock_addition_id' => $stockAddition->id]) }}" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 text-center block">
                                Record Production
                            </a>
                            <a href="{{ route('stock-management.stock-additions.edit', $stockAddition) }}" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 text-center block">
                                Edit Stock
                            </a>
                        </div>
                    </div>

                    <!-- Stock Status -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Stock Status</h3>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center">
                                @if($stockAddition->available_pieces > 0)
                                    <div class="flex-shrink-0">
                                        <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">In Stock</p>
                                        <p class="text-sm text-gray-500">{{ number_format($stockAddition->available_pieces) }} pieces available</p>
                                    </div>
                                @else
                                    <div class="flex-shrink-0">
                                        <div class="w-3 h-3 bg-red-400 rounded-full"></div>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">Out of Stock</p>
                                        <p class="text-sm text-gray-500">All pieces have been issued</p>
                                    </div>
                                @endif
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
                                <dd class="mt-1 text-sm text-gray-900">{{ $stockAddition->product->category ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Vendor Contact</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $stockAddition->mineVendor->contact_person ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Vendor Phone</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $stockAddition->mineVendor->phone ?? 'N/A' }}</dd>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            @if($stockAddition->stockIssued->count() > 0 || $stockAddition->dailyProduction->count() > 0)
            <div class="mt-8 bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Recent Activity</h2>
                </div>
                <div class="p-6">
                    <div class="flow-root">
                        <ul class="-mb-8">
                            @foreach($stockAddition->stockIssued->take(5) as $issued)
                            <li>
                                <div class="relative pb-8">
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">Stock issued: <span class="font-medium text-gray-900">{{ number_format($issued->quantity_issued) }} pieces</span></p>
                                                <p class="text-sm text-gray-500">{{ $issued->purpose ?? 'Production' }}</p>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                {{ $issued->date->format('M d, Y') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
