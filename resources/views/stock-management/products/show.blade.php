<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Product Details</h1>
                        <p class="mt-2 text-gray-600">View product information and stock details</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('stock-management.products.edit', $product) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Edit Product
                        </a>
                        <a href="{{ route('stock-management.products.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Back to List
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Product Information -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900">Product Information</h2>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Product Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $product->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Category</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $product->category ?? 'Not specified' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Created Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $product->created_at->format('M d, Y') }}</dd>
                                </div>
                                @if($product->description)
                                <div class="md:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $product->description }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- Stock Summary -->
                    <div class="mt-8 bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900">Stock Summary</h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600">{{ number_format($product->stockAdditions->sum('total_pieces')) }}</div>
                                    <div class="text-sm text-gray-500">Total Pieces</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600">{{ number_format($product->total_available_stock) }}</div>
                                    <div class="text-sm text-gray-500">Available Pieces</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-purple-600">{{ number_format($product->total_available_sqft, 2) }}</div>
                                    <div class="text-sm text-gray-500">Available Sqft</div>
                                </div>
                            </div>
                            <div class="mt-6">
                                <div class="flex justify-between text-sm text-gray-600 mb-1">
                                    <span>Stock Usage</span>
                                    <span>{{ $product->stockAdditions->sum('total_pieces') > 0 ? number_format((($product->stockAdditions->sum('total_pieces') - $product->total_available_stock) / $product->stockAdditions->sum('total_pieces')) * 100, 1) : 0 }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $product->stockAdditions->sum('total_pieces') > 0 ? (($product->stockAdditions->sum('total_pieces') - $product->total_available_stock) / $product->stockAdditions->sum('total_pieces')) * 100 : 0 }}%"></div>
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
                            <a href="{{ route('stock-management.products.edit', $product) }}" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 text-center block">
                                Edit Product
                            </a>
                            <a href="{{ route('stock-management.stock-additions.create', ['product_id' => $product->id]) }}" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 text-center block">
                                Add Stock
                            </a>
                            <a href="{{ route('stock-management.products.create') }}" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 text-center block">
                                Create New Product
                            </a>
                            <form method="POST" action="{{ route('stock-management.products.destroy', $product) }}" class="w-full" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                    Delete Product
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Product Status -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Product Status</h3>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-3 h-3 {{ $product->is_active ? 'bg-green-400' : 'bg-red-400' }} rounded-full"></div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $product->is_active ? 'Active' : 'Inactive' }}</p>
                                    <p class="text-sm text-gray-500">
                                        @if($product->is_active)
                                            Available for stock management
                                        @else
                                            Not available for new stock
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Statistics -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Product Statistics</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Total Stock Additions</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $product->stockAdditions->count() }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Last Stock Addition</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($product->stockAdditions->count() > 0)
                                        {{ $product->stockAdditions->sortByDesc('created_at')->first()->created_at->format('M d, Y') }}
                                    @else
                                        No stock additions yet
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Average Pieces per Addition</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $product->stockAdditions->count() > 0 ? number_format($product->stockAdditions->avg('total_pieces'), 0) : 0 }}
                                </dd>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Additions History -->
            @if($product->stockAdditions->count() > 0)
            <div class="mt-8 bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Stock Additions History</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mine Vendor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stone Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size (3D)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Pieces</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available Pieces</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Condition</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($product->stockAdditions->sortByDesc('created_at') as $stockAddition)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $stockAddition->date->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $stockAddition->mineVendor->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $stockAddition->stone }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $stockAddition->size_3d }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($stockAddition->total_pieces) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($stockAddition->available_pieces) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $stockAddition->condition_status === 'Polished' ? 'bg-green-100 text-green-800' : ($stockAddition->condition_status === 'Slabs' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                            {{ $stockAddition->condition_status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('stock-management.stock-additions.show', $stockAddition) }}" class="text-blue-600 hover:text-blue-900 transition-colors duration-200">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="mt-8 bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="px-6 py-12 text-center">
                    <svg class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No stock additions yet</h3>
                    <p class="text-gray-500 mb-4">Start adding stock for this product.</p>
                    <a href="{{ route('stock-management.stock-additions.create', ['product_id' => $product->id]) }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                        Add First Stock
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
