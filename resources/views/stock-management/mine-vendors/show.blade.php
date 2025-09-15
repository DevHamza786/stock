<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Vendor Details</h1>
                        <p class="mt-2 text-gray-600">View vendor information and stock history</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('stock-management.mine-vendors.edit', $mineVendor) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Edit Vendor
                        </a>
                        <a href="{{ route('stock-management.mine-vendors.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Back to List
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Vendor Information -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900">Vendor Information</h2>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Vendor Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $mineVendor->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $mineVendor->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $mineVendor->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Contact Person</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $mineVendor->contact_person ?? 'Not specified' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Phone Number</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $mineVendor->phone ?? 'Not specified' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email Address</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $mineVendor->email ?? 'Not specified' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Created Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $mineVendor->created_at->format('M d, Y') }}</dd>
                                </div>
                                @if($mineVendor->address)
                                <div class="md:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Address</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $mineVendor->address }}</dd>
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
                                    <div class="text-2xl font-bold text-indigo-600">{{ number_format($mineVendor->stockAdditions->sum('total_pieces')) }}</div>
                                    <div class="text-sm text-gray-500">Total Pieces Purchased</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600">{{ number_format($mineVendor->stockAdditions->sum('total_sqft'), 2) }}</div>
                                    <div class="text-sm text-gray-500">Total Sqft Purchased</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-purple-600">{{ $mineVendor->stockAdditions->count() }}</div>
                                    <div class="text-sm text-gray-500">Total Purchases</div>
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
                            <a href="{{ route('stock-management.mine-vendors.edit', $mineVendor) }}" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 text-center block">
                                Edit Vendor
                            </a>
                            <a href="{{ route('stock-management.stock-additions.create', ['mine_vendor_id' => $mineVendor->id]) }}" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 text-center block">
                                Add Stock
                            </a>
                            <a href="{{ route('stock-management.mine-vendors.create') }}" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 text-center block">
                                Create New Vendor
                            </a>
                            <form method="POST" action="{{ route('stock-management.mine-vendors.destroy', $mineVendor) }}" class="w-full" onsubmit="return confirm('Are you sure you want to delete this vendor?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                    Delete Vendor
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Vendor Status -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Vendor Status</h3>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-3 h-3 {{ $mineVendor->is_active ? 'bg-green-400' : 'bg-red-400' }} rounded-full"></div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $mineVendor->is_active ? 'Active' : 'Inactive' }}</p>
                                    <p class="text-sm text-gray-500">
                                        @if($mineVendor->is_active)
                                            Available for stock purchases
                                        @else
                                            Not available for new purchases
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vendor Statistics -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Vendor Statistics</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Total Stock Additions</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $mineVendor->stockAdditions->count() }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Last Purchase</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($mineVendor->stockAdditions->count() > 0)
                                        {{ $mineVendor->stockAdditions->sortByDesc('created_at')->first()->created_at->format('M d, Y') }}
                                    @else
                                        No purchases yet
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Average Pieces per Purchase</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $mineVendor->stockAdditions->count() > 0 ? number_format($mineVendor->stockAdditions->avg('total_pieces'), 0) : 0 }}
                                </dd>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Additions History -->
            @if($mineVendor->stockAdditions->count() > 0)
            <div class="mt-8 bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Stock Purchase History</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stone Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size (3D)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Pieces</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available Pieces</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Condition</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($mineVendor->stockAdditions->sortByDesc('created_at') as $stockAddition)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $stockAddition->date->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $stockAddition->product->name }}</td>
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
                                        <a href="{{ route('stock-management.stock-additions.show', $stockAddition) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200">
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No stock purchases yet</h3>
                    <p class="text-gray-500 mb-4">Start adding stock from this vendor.</p>
                    <a href="{{ route('stock-management.stock-additions.create', ['mine_vendor_id' => $mineVendor->id]) }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                        Add First Stock
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
