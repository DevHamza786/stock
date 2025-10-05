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
                                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                                    {{ $item->total_pieces }} pieces
                                                </span>
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
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
                                                <div>
                                                    <dt class="font-medium text-gray-700">Total Sqft</dt>
                                                    <dd class="text-gray-900">{{ number_format($item->total_sqft, 2) }} sqft</dd>
                                                </div>
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
                                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($dailyProduction->total_sqft, 2) }}</dd>
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
        </div>
    </div>
</x-app-layout>
