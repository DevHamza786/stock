<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Stock Additions</h1>
                        <p class="mt-2 text-gray-600">Manage your stock inventory additions</p>
                    </div>
                    <a href="{{ route('stock-management.stock-additions.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center transition-colors duration-200">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add New Stock
                    </a>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="p-6">

                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Search and Filter Section -->
                    <div class="mb-6">
                        <form method="GET" action="{{ route('stock-management.stock-additions.index') }}" class="space-y-4">
                            <!-- Search Bar -->
                            <div class="flex flex-col sm:flex-row gap-4">
                                <div class="flex-1">
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                            </svg>
                                        </div>
                                        <input type="text" name="search" value="{{ request('search') }}"
                                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                               placeholder="Search by product, vendor, stone, size, or condition...">
                                    </div>
                                </div>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                    Search
                                </button>
                                @if(request()->hasAny(['search', 'product_id', 'vendor_id', 'condition_status', 'stock_status', 'date_from', 'date_to']))
                                    <a href="{{ route('stock-management.stock-additions.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                        Clear
                                    </a>
                                @endif
                            </div>

                            <!-- Filters -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <!-- Product Filter -->
                                <div>
                                    <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                                    <select name="product_id" id="product_id" class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm">
                                        <option value="">All Products</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Vendor Filter -->
                                <div>
                                    <label for="vendor_id" class="block text-sm font-medium text-gray-700 mb-1">Vendor</label>
                                    <select name="vendor_id" id="vendor_id" class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm">
                                        <option value="">All Vendors</option>
                                        @foreach($vendors as $vendor)
                                            <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                                {{ $vendor->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Condition Status Filter -->
                                <div>
                                    <label for="condition_status" class="block text-sm font-medium text-gray-700 mb-1">Condition</label>
                                    <select name="condition_status" id="condition_status" class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm">
                                        <option value="">All Conditions</option>
                                        @foreach($conditionStatuses as $status)
                                            <option value="{{ $status }}" {{ request('condition_status') == $status ? 'selected' : '' }}>
                                                {{ $status }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Stock Status Filter -->
                                <div>
                                    <label for="stock_status" class="block text-sm font-medium text-gray-700 mb-1">Stock Status</label>
                                    <select name="stock_status" id="stock_status" class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm">
                                        <option value="">All Stock</option>
                                        <option value="available" {{ request('stock_status') == 'available' ? 'selected' : '' }}>Available</option>
                                        <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Date Range Filters -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                                           class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm">
                                </div>
                                <div>
                                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                                           class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm">
                                </div>
                            </div>

                            <!-- Hidden fields to preserve sorting -->
                            <input type="hidden" name="sort_by" value="{{ request('sort_by', 'date') }}">
                            <input type="hidden" name="sort_direction" value="{{ request('sort_direction', 'desc') }}">
                        </form>
                    </div>

                    <!-- Results Summary -->
                    <div class="mb-4 flex justify-between items-center">
                        <div class="text-sm text-gray-700">
                            Showing {{ $stockAdditions->firstItem() ?? 0 }} to {{ $stockAdditions->lastItem() ?? 0 }} of {{ $stockAdditions->total() }} results
                        </div>
                        @if(request()->hasAny(['search', 'product_id', 'vendor_id', 'condition_status', 'stock_status', 'date_from', 'date_to']))
                            <div class="text-sm text-blue-600">
                                <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"></path>
                                </svg>
                                Filters Applied
                            </div>
                        @endif
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'product', 'sort_direction' => request('sort_by') == 'product' && request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}" class="group inline-flex items-center hover:text-gray-700">
                                            Product
                                            @if(request('sort_by') == 'product')
                                                @if(request('sort_direction') == 'asc')
                                                    <svg class="ml-1 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                    </svg>
                                                @else
                                                    <svg class="ml-1 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                @endif
                                            @else
                                                <svg class="ml-1 h-4 w-4 text-gray-300 group-hover:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                                </svg>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'vendor', 'sort_direction' => request('sort_by') == 'vendor' && request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}" class="group inline-flex items-center hover:text-gray-700">
                                            Vendor
                                            @if(request('sort_by') == 'vendor')
                                                @if(request('sort_direction') == 'asc')
                                                    <svg class="ml-1 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                    </svg>
                                                @else
                                                    <svg class="ml-1 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                @endif
                                            @else
                                                <svg class="ml-1 h-4 w-4 text-gray-300 group-hover:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                                </svg>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stone</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size/Weight</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'total_pieces', 'sort_direction' => request('sort_by') == 'total_pieces' && request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}" class="group inline-flex items-center hover:text-gray-700">
                                            Pieces
                                            @if(request('sort_by') == 'total_pieces')
                                                @if(request('sort_direction') == 'asc')
                                                    <svg class="ml-1 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                    </svg>
                                                @else
                                                    <svg class="ml-1 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                @endif
                                            @else
                                                <svg class="ml-1 h-4 w-4 text-gray-300 group-hover:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                                </svg>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'total_sqft', 'sort_direction' => request('sort_by') == 'total_sqft' && request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}" class="group inline-flex items-center hover:text-gray-700">
                                            Sqft
                                            @if(request('sort_by') == 'total_sqft')
                                                @if(request('sort_direction') == 'asc')
                                                    <svg class="ml-1 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                    </svg>
                                                @else
                                                    <svg class="ml-1 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                @endif
                                            @else
                                                <svg class="ml-1 h-4 w-4 text-gray-300 group-hover:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                                </svg>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'available_pieces', 'sort_direction' => request('sort_by') == 'available_pieces' && request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}" class="group inline-flex items-center hover:text-gray-700">
                                            Available
                                            @if(request('sort_by') == 'available_pieces')
                                                @if(request('sort_direction') == 'asc')
                                                    <svg class="ml-1 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                    </svg>
                                                @else
                                                    <svg class="ml-1 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                @endif
                                            @else
                                                <svg class="ml-1 h-4 w-4 text-gray-300 group-hover:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                                </svg>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'date', 'sort_direction' => request('sort_by') == 'date' && request('sort_direction') == 'asc' ? 'desc' : 'asc']) }}" class="group inline-flex items-center hover:text-gray-700">
                                            Date
                                            @if(request('sort_by') == 'date')
                                                @if(request('sort_direction') == 'asc')
                                                    <svg class="ml-1 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                    </svg>
                                                @else
                                                    <svg class="ml-1 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                @endif
                                            @else
                                                <svg class="ml-1 h-4 w-4 text-gray-300 group-hover:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                                </svg>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($stockAdditions as $addition)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $addition->product->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $addition->mineVendor->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $addition->stone }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if(strtolower($addition->condition_status) === 'block')
                                                @if($addition->weight)
                                                    <span class="font-medium text-green-600">{{ number_format($addition->weight, 2) }} kg</span>
                                                    <br>
                                                    <span class="text-xs text-gray-400">Weight per piece</span>
                                                @else
                                                    <span class="text-gray-400">N/A</span>
                                                @endif
                                            @else
                                                @if($addition->length && $addition->height)
                                                    <span class="font-medium text-blue-600">{{ $addition->length }} × {{ $addition->height }} cm</span>
                                                    <br>
                                                    <span class="text-xs text-gray-400">{{ number_format($addition->length * $addition->height, 2) }} cm²</span>
                                                @else
                                                    <span class="text-gray-400">{{ $addition->size_3d ?? 'N/A' }}</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ number_format($addition->total_pieces) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ number_format($addition->total_sqft, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $addition->available_pieces > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ number_format($addition->available_pieces) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $addition->date->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('stock-management.stock-additions.show', $addition) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                                <a href="{{ route('stock-management.stock-additions.edit', $addition) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                                <form method="POST" action="{{ route('stock-management.stock-additions.destroy', $addition) }}" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this stock addition?')">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            No stock additions found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Enhanced Pagination -->
                    @if($stockAdditions->hasPages())
                        <div class="mt-6 flex items-center justify-between">
                            <div class="flex-1 flex justify-between sm:hidden">
                                @if($stockAdditions->onFirstPage())
                                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-500 bg-white cursor-not-allowed">
                                        Previous
                                    </span>
                                @else
                                    <a href="{{ $stockAdditions->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        Previous
                                    </a>
                                @endif

                                @if($stockAdditions->hasMorePages())
                                    <a href="{{ $stockAdditions->nextPageUrl() }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        Next
                                    </a>
                                @else
                                    <span class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-500 bg-white cursor-not-allowed">
                                        Next
                                    </span>
                                @endif
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Showing
                                        <span class="font-medium">{{ $stockAdditions->firstItem() }}</span>
                                        to
                                        <span class="font-medium">{{ $stockAdditions->lastItem() }}</span>
                                        of
                                        <span class="font-medium">{{ $stockAdditions->total() }}</span>
                                        results
                                    </p>
                                </div>
                                <div>
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                        {{-- Previous Page Link --}}
                                        @if ($stockAdditions->onFirstPage())
                                            <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 cursor-not-allowed">
                                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        @else
                                            <a href="{{ $stockAdditions->previousPageUrl() }}" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </a>
                                        @endif

                                        {{-- Pagination Elements --}}
                                        @foreach ($stockAdditions->getUrlRange(1, $stockAdditions->lastPage()) as $page => $url)
                                            @if ($page == $stockAdditions->currentPage())
                                                <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-blue-50 text-sm font-medium text-blue-600">
                                                    {{ $page }}
                                                </span>
                                            @else
                                                <a href="{{ $url }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                                    {{ $page }}
                                                </a>
                                            @endif
                                        @endforeach

                                        {{-- Next Page Link --}}
                                        @if ($stockAdditions->hasMorePages())
                                            <a href="{{ $stockAdditions->nextPageUrl() }}" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </a>
                                        @else
                                            <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 cursor-not-allowed">
                                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        @endif
                                    </nav>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-submit form when filter selects change
            const filterSelects = document.querySelectorAll('#product_id, #vendor_id, #condition_status, #stock_status');
            filterSelects.forEach(select => {
                select.addEventListener('change', function() {
                    this.form.submit();
                });
            });

            // Auto-submit form when date inputs change
            const dateInputs = document.querySelectorAll('#date_from, #date_to');
            dateInputs.forEach(input => {
                input.addEventListener('change', function() {
                    this.form.submit();
                });
            });

            // Add loading state to search button
            const searchForm = document.querySelector('form[method="GET"]');
            const searchButton = searchForm.querySelector('button[type="submit"]');

            searchForm.addEventListener('submit', function() {
                searchButton.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Searching...
                `;
                searchButton.disabled = true;
            });

            // Clear filters functionality
            const clearButton = document.querySelector('a[href*="stock-additions"]');
            if (clearButton) {
                clearButton.addEventListener('click', function(e) {
                    // Reset all form inputs
                    const form = document.querySelector('form[method="GET"]');
                    form.reset();
                });
            }
        });
    </script>
</x-app-layout>
