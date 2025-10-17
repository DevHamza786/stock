<x-app-layout>
    <div class="py-8">
    <div class="w-full px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Production Details</h1>
                        <p class="mt-2 text-gray-600">View daily production details and related information</p>
                    </div>
                    <div class="flex space-x-3 no-print">
                        <a href="{{ route('stock-management.daily-production.print', $dailyProduction) }}" target="_blank" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Print
                        </a>
                        <a href="{{ route('stock-management.daily-production.edit', $dailyProduction) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Edit Production
                        </a>
                        <a href="{{ route('stock-management.daily-production.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Back to List
                        </a>
                    </div>
                </div>
            </div>

            <div class="w-full">
                <!-- Main Production Information -->
                <div class="w-full">
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900">Production Information</h2>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Machine Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $dailyProduction->machine_name }}
                                        @if($dailyProduction->machine)
                                            <span class="text-xs text-gray-500 ml-2">({{ $dailyProduction->machine->description ?? 'Active' }})</span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Operator Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $dailyProduction->operator_name }}
                                        @if($dailyProduction->operator)
                                            <span class="text-xs text-gray-500 ml-2">
                                                @if($dailyProduction->operator->employee_id)
                                                    (ID: {{ $dailyProduction->operator->employee_id }})
                                                @endif
                                                @if($dailyProduction->operator->phone)
                                                    - {{ $dailyProduction->operator->phone }}
                                                @endif
                                            </span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Production Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $dailyProduction->date->format('M d, Y') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $dailyProduction->status === 'open' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($dailyProduction->status) }}
                                        </span>
                                    </dd>
                                </div>
                                @if($dailyProduction->stone)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Particulars</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $dailyProduction->stone }}</dd>
                                </div>
                                @endif
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Total Pieces Produced</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($dailyProduction->total_pieces) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Total Square Feet</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($dailyProduction->total_sqft, 2) }} sqft</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Total Weight</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($dailyProduction->total_weight, 2) }} kg</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Wastage</dt>
                                    <dd class="mt-1">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $dailyProduction->wastage_sqft > 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                            {{ number_format($dailyProduction->wastage_sqft, 2) }} sqft
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Production Efficiency</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($dailyProduction->efficiency, 2) }} pieces/hour</dd>
                                </div>
                                @if($dailyProduction->stockAddition)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Source Stock Addition</dt>
                                    <dd class="mt-1">
                                        <a href="{{ route('stock-management.stock-additions.show', $dailyProduction->stockAddition) }}" 
                                           class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 transition-colors duration-200">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                            </svg>
                                            View Stock Addition
                                        </a>
                                        <div class="text-xs text-gray-500 mt-1">
                                            Stock PID: {{ $dailyProduction->stockAddition->pid ?? 'N/A' }} | 
                                            {{ $dailyProduction->stockAddition->product->name ?? 'N/A' }} - 
                                            {{ $dailyProduction->stockAddition->mineVendor->name ?? 'N/A' }}
                                        </div>
                                    </dd>
                                </div>
                                @endif
                                @if($dailyProduction->notes)
                                <div class="md:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Notes</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $dailyProduction->notes }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- Production Items -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200 mt-6">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900">Production Items ({{ $dailyProduction->items->count() }})</h2>
                        </div>
                        <div class="p-6">
                            @if($dailyProduction->items->count() > 0)
                                <div class="space-y-4">
                                    @foreach($dailyProduction->items as $index => $item)
                                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                            <div class="flex items-center justify-between mb-3">
                                                <h3 class="text-lg font-semibold text-gray-900">Production Item #{{ $index + 1 }}</h3>
                                                <div class="flex items-center space-x-2">
                                                    @php
                                                        // Find the corresponding produced stock addition for this item
                                                        $producedStock = $producedStockAdditions->where('stone', $item->product_name)
                                                            ->where('condition_status', $item->condition_status)
                                                            ->first();
                                                    @endphp
                                                    @if($producedStock)
                                                        <a href="{{ route('stock-management.stock-additions.show', $producedStock) }}" 
                                                           class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded hover:bg-green-200 transition-colors duration-200"
                                                           title="View New Product">
                                                            <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                            </svg>
                                                            New Product
                                                        </a>
                                                        <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                                            PID: {{ $producedStock->pid ?? 'N/A' }}
                                                        </span>
                                                    @endif
                                                    
                                                    @if($dailyProduction->stockAddition)
                                                        <a href="{{ route('stock-management.stock-additions.show', $dailyProduction->stockAddition) }}" 
                                                           class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded hover:bg-blue-200 transition-colors duration-200"
                                                           title="View Source Stock">
                                                            <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                            </svg>
                                                            Source Stock
                                                        </a>
                                                        <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                                            PID: {{ $dailyProduction->stockAddition->pid ?? 'N/A' }}
                                                        </span>
                                                    @endif
                                                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                                        {{ $item->total_pieces }} pieces
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Production Item Details -->
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm mb-4">
                                                <div>
                                                    <dt class="font-medium text-gray-700">Product Name</dt>
                                                    <dd class="text-gray-900">{{ $item->product_name }}</dd>
                                                </div>
                                                @if($item->size)
                                                <div>
                                                    <dt class="font-medium text-gray-700">Size (cm)</dt>
                                                    <dd class="text-gray-900">{{ $item->size }}</dd>
                                                </div>
                                                @endif
                                                @if($item->diameter)
                                                <div>
                                                    <dt class="font-medium text-gray-700">Diameter/Thickness</dt>
                                                    <dd class="text-gray-900">{{ $item->diameter }}</dd>
                                                </div>
                                                @endif
                                                <div>
                                                    <dt class="font-medium text-gray-700">Condition Status</dt>
                                                    <dd class="text-gray-900">{{ $item->condition_status }}</dd>
                                                </div>
                                                @if($item->special_status)
                                                <div>
                                                    <dt class="font-medium text-gray-700">Special Status</dt>
                                                    <dd class="text-gray-900">{{ $item->special_status }}</dd>
                                                </div>
                                                @endif
                                                @if($item->total_sqft > 0)
                                                <div>
                                                    <dt class="font-medium text-gray-700">Total Sqft</dt>
                                                    <dd class="text-gray-900">{{ number_format($item->total_sqft, 2) }} sqft</dd>
                                                </div>
                                                @endif
                                                @if($item->total_weight > 0)
                                                <div>
                                                    <dt class="font-medium text-gray-700">Total Weight</dt>
                                                    <dd class="text-gray-900">{{ number_format($item->total_weight, 2) }} kg</dd>
                                                </div>
                                                @endif
                                                @if($item->size)
                                                <div>
                                                    <dt class="font-medium text-gray-700">Per Piece Sqft</dt>
                                                    <dd class="text-gray-900">{{ number_format($item->total_sqft / $item->total_pieces, 4) }} sqft</dd>
                                                </div>
                                                @endif
                                                @if($item->narration)
                                                <div class="md:col-span-2 lg:col-span-3">
                                                    <dt class="font-medium text-gray-700">Narration</dt>
                                                    <dd class="text-gray-900">{{ $item->narration }}</dd>
                                                </div>
                                                @endif
                                            </div>

                                            <!-- New Stock Information -->
                                            @if($producedStock)
                                            <div class="bg-green-50 p-3 rounded-lg border border-green-200">
                                                <h4 class="text-sm font-semibold text-green-900 mb-2">New Stock Created</h4>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                                    <div>
                                                        <span class="font-medium text-green-700">Stock PID:</span>
                                                        <span class="text-green-900 font-mono">{{ $producedStock->pid ?? 'N/A' }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="font-medium text-green-700">Available Pieces:</span>
                                                        <span class="text-green-900">{{ number_format($producedStock->available_pieces) }}</span>
                                                    </div>
                                                    @if($producedStock->available_sqft > 0)
                                                    <div>
                                                        <span class="font-medium text-green-700">Available Sqft:</span>
                                                        <span class="text-green-900">{{ number_format($producedStock->available_sqft, 2) }} sqft</span>
                                                    </div>
                                                    @endif
                                                    @if($producedStock->available_weight > 0)
                                                    <div>
                                                        <span class="font-medium text-green-700">Available Weight:</span>
                                                        <span class="text-green-900">{{ number_format($producedStock->available_weight, 2) }} kg</span>
                                                    </div>
                                                    @endif
                                                    <div class="md:col-span-2">
                                                        <a href="{{ route('stock-management.stock-additions.show', $producedStock) }}" 
                                                           class="inline-flex items-center text-xs text-green-600 hover:text-green-800 transition-colors duration-200">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                            </svg>
                                                            View New Stock Addition Details
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8 text-gray-500">
                                    <svg class="h-12 w-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                    <p>No production items found.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Machine & Operator Information -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900">Machine & Operator Details</h2>
                        </div>
                        <div class="p-6">
                            <dl class="space-y-4">
                                @if($dailyProduction->machine)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Machine Details</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <div class="font-medium">{{ $dailyProduction->machine->name }}</div>
                                        @if($dailyProduction->machine->description)
                                            <div class="text-gray-600">{{ $dailyProduction->machine->description }}</div>
                                        @endif
                                        <div class="text-xs text-gray-500 mt-1">
                                            Status: {{ $dailyProduction->machine->status ? 'Active' : 'Inactive' }}
                                        </div>
                                    </dd>
                                </div>
                                @endif

                                @if($dailyProduction->operator)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Operator Details</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <div class="font-medium">{{ $dailyProduction->operator->name }}</div>
                                        @if($dailyProduction->operator->employee_id)
                                            <div class="text-gray-600">Employee ID: {{ $dailyProduction->operator->employee_id }}</div>
                                        @endif
                                        @if($dailyProduction->operator->phone)
                                            <div class="text-gray-600">Phone: {{ $dailyProduction->operator->phone }}</div>
                                        @endif
                                        @if($dailyProduction->operator->email)
                                            <div class="text-gray-600">Email: {{ $dailyProduction->operator->email }}</div>
                                        @endif
                                        <div class="text-xs text-gray-500 mt-1">
                                            Status: {{ $dailyProduction->operator->status ? 'Active' : 'Inactive' }}
                                        </div>
                                    </dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- Production Summary -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900">Production Summary</h2>
                        </div>
                        <div class="p-6">
                            <dl class="space-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Total Products</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $dailyProduction->items->count() }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Total Pieces</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($dailyProduction->total_pieces) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Total Sqft</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($dailyProduction->total_sqft, 2) }} sqft</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Total Weight</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($dailyProduction->total_weight, 2) }} kg</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Utilization Rate</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        @if($dailyProduction->stockIssued)
                                            {{ number_format(($dailyProduction->total_pieces / $dailyProduction->stockIssued->quantity_issued) * 100, 1) }}%
                                        @else
                                            N/A
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Produced Stock Additions Section -->
            @if($producedStockAdditions->count() > 0)
            <div class="mt-8">
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Produced Stock Additions ({{ $producedStockAdditions->count() }})</h2>
                        <p class="text-sm text-gray-600 mt-1">New stock items created from this production</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($producedStockAdditions as $stockAddition)
                                <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="text-sm font-semibold text-green-900">Stock PID: {{ $stockAddition->pid ?? 'N/A' }}</h3>
                                        <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded">
                                            {{ $stockAddition->available_pieces }} pieces
                                        </span>
                                    </div>
                                    <div class="space-y-2 text-sm">
                                        <div>
                                            <span class="font-medium text-green-700">Product:</span>
                                            <span class="text-green-900">{{ $stockAddition->stone }}</span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-green-700">Condition:</span>
                                            <span class="text-green-900">{{ $stockAddition->condition_status }}</span>
                                        </div>
                                        @if($stockAddition->available_sqft > 0)
                                        <div>
                                            <span class="font-medium text-green-700">Available Sqft:</span>
                                            <span class="text-green-900">{{ number_format($stockAddition->available_sqft, 2) }} sqft</span>
                                        </div>
                                        @endif
                                        @if($stockAddition->available_weight > 0)
                                        <div>
                                            <span class="font-medium text-green-700">Available Weight:</span>
                                            <span class="text-green-900">{{ number_format($stockAddition->available_weight, 2) }} kg</span>
                                        </div>
                                        @endif
                                        <div class="pt-2">
                                            <a href="{{ route('stock-management.stock-additions.show', $stockAddition) }}" 
                                               class="inline-flex items-center text-xs text-green-600 hover:text-green-800 transition-colors duration-200">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                </svg>
                                                View Stock Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Print Styles -->
    <style media="print">
        .no-print {
            display: none !important;
        }
        body {
            font-size: 12px;
        }
        .bg-gray-50, .bg-green-50, .bg-blue-50 {
            background-color: #f9fafb !important;
        }
        .border {
            border: 1px solid #d1d5db !important;
        }
    </style>
</x-app-layout>
