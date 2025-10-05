<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Gate Pass Details</h1>
                        <p class="mt-2 text-gray-600">View gate pass details and dispatch information</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('stock-management.gate-pass.edit', $gatePass) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Edit Gate Pass
                        </a>
                        <a href="{{ route('stock-management.gate-pass.print', $gatePass) }}" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200" target="_blank">
                            Print Gate Pass
                        </a>
                        <a href="{{ route('stock-management.gate-pass.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Back to List
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content - Left Side -->
                <div class="lg:col-span-2">
                    <!-- Main Gate Pass Information -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900">Gate Pass Information</h2>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Gate Pass Number</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono">GP-{{ str_pad($gatePass->id, 4, '0', STR_PAD_LEFT) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($gatePass->status === 'Dispatched') bg-green-100 text-green-800
                                            @elseif($gatePass->status === 'Approved') bg-blue-100 text-blue-800
                                            @else bg-yellow-100 text-yellow-800 @endif">
                                            {{ $gatePass->status ?? 'Pending' }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Product</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $gatePass->stockIssued->stockAddition->product->name ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Quantity Dispatched</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($gatePass->quantity_issued) }} pieces</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Square Feet Dispatched</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($gatePass->sqft_issued, 2) }} sqft</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Dispatch Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $gatePass->date->format('M d, Y') }}</dd>
                                </div>
                                @if($gatePass->destination)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Destination</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $gatePass->destination }}</dd>
                                </div>
                                @endif
                                @if($gatePass->vehicle_number)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Vehicle Number</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $gatePass->vehicle_number }}</dd>
                                </div>
                                @endif
                                @if($gatePass->driver_name)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Driver Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $gatePass->driver_name }}</dd>
                                </div>
                                @endif
                                @if($gatePass->notes)
                                <div class="md:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Notes</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $gatePass->notes }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- Dispatch Details -->
                    <div class="mt-8 bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900">Dispatch Details</h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <!-- Logistics Information -->
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Logistics Information</h3>
                                    <div class="space-y-4">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600">Destination</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $gatePass->destination ?? 'Not specified' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600">Vehicle Number</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $gatePass->vehicle_number ?? 'Not specified' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600">Driver Name</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $gatePass->driver_name ?? 'Not specified' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600">Dispatch Date</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $gatePass->date->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dispatch Metrics -->
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Dispatch Metrics</h3>
                                    <div class="space-y-4">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600">Dispatch Status</span>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($gatePass->status === 'Dispatched') bg-green-100 text-green-800
                                                @elseif($gatePass->status === 'Approved') bg-blue-100 text-blue-800
                                                @else bg-yellow-100 text-yellow-800 @endif">
                                                {{ $gatePass->status ?? 'Pending' }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600">Dispatch Efficiency</span>
                                            <span class="text-sm font-medium text-gray-900">
                                                @if($gatePass->status === 'Dispatched')
                                                    100%
                                                @elseif($gatePass->status === 'Approved')
                                                    90%
                                                @else
                                                    0%
                                                @endif
                                            </span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600">Gate Pass Number</span>
                                            <span class="text-sm font-medium text-gray-900 font-mono">GP-{{ str_pad($gatePass->id, 4, '0', STR_PAD_LEFT) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar - Right Side -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Dispatch Statistics -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900">Dispatch Statistics</h2>
                        </div>
                        <div class="p-6">
                            <div class="space-y-6">
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-orange-600">{{ number_format($gatePass->quantity_issued) }}</div>
                                    <div class="text-sm text-gray-500">Pieces Dispatched</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-blue-600">{{ number_format($gatePass->sqft_issued, 2) }}</div>
                                    <div class="text-sm text-gray-500">Sqft Dispatched</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-green-600">{{ number_format($gatePass->sqft_issued / $gatePass->quantity_issued, 2) }}</div>
                                    <div class="text-sm text-gray-500">Avg Sqft per Piece</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                <a href="{{ route('stock-management.gate-pass.edit', $gatePass) }}" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 block text-center">
                                    Edit Gate Pass
                                </a>
                                <a href="{{ route('stock-management.gate-pass.print', $gatePass) }}" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 block text-center" target="_blank">
                                    Print Gate Pass
                                </a>
                                <a href="{{ route('stock-management.gate-pass.create') }}" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 block text-center">
                                    Create New Gate Pass
                                </a>
                                <form method="POST" action="{{ route('stock-management.gate-pass.destroy', $gatePass) }}" onsubmit="return confirm('Are you sure you want to delete this gate pass?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                        Delete Gate Pass
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
