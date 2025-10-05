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
                <!-- Main Content - Left Side -->
                <div class="lg:col-span-2">
                    <!-- Main Stock Information -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900">Stock Information</h2>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Product</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $stockAddition->product->name ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">PID</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $stockAddition->pid ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Mine Vendor</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $stockAddition->mineVendor->name ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Particulars</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $stockAddition->stone }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Dimensions</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        @if($stockAddition->length && $stockAddition->height)
                                            <div class="space-y-1">
                                                <div class="font-medium text-blue-600">{{ $stockAddition->length }} × {{ $stockAddition->height }} cm</div>
                                                @if($stockAddition->diameter)
                                                    <div class="text-sm text-green-600">Thickness: {{ $stockAddition->diameter }}</div>
                                                @endif
                                                <div class="text-xs text-gray-500">
                                                    Single piece: {{ number_format($stockAddition->length * $stockAddition->height, 2) }} cm²
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    Single piece: {{ number_format(($stockAddition->length * $stockAddition->height) * 0.00107639, 4) }} sqft
                                                </div>
                                            </div>
                                        @elseif($stockAddition->diameter)
                                            <div class="space-y-1">
                                                <div class="font-medium text-green-600">Thickness: {{ $stockAddition->diameter }}</div>
                                                @if($stockAddition->size_3d)
                                                    <div class="text-sm text-gray-600">Size (3D): {{ $stockAddition->size_3d }}</div>
                                                @endif
                                            </div>
                                        @elseif($stockAddition->size_3d)
                                            <span class="text-gray-600">{{ $stockAddition->size_3d }}</span>
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Total Pieces</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($stockAddition->total_pieces) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Total Sqft</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        @if($stockAddition->total_sqft)
                                            {{ number_format($stockAddition->total_sqft, 2) }} sqft
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Weight</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        @if($stockAddition->weight)
                                            {{ number_format($stockAddition->weight, 2) }} kg
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Condition Status</dt>
                                    <dd class="mt-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($stockAddition->condition_status === 'Block') bg-blue-100 text-blue-800
                                            @elseif($stockAddition->condition_status === 'Monuments') bg-indigo-100 text-indigo-800
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

                    <!-- Recent Activity -->
                    @if($stockAddition->stockLogs->count() > 0)
                    <div class="mt-8 bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900">Recent Activity</h2>
                        </div>
                        <div class="p-6">
                            <div class="flow-root">
                                <ul class="-mb-8">
                                    @foreach($stockAddition->stockLogs->take(10) as $log)
                                    <li>
                                        <div class="relative pb-8">
                                            @if(!$loop->last)
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                            @endif
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    @if($log->action_type === 'dispatched')
                                                        <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                            </svg>
                                                        </span>
                                                    @elseif($log->action_type === 'deleted')
                                                        <span class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center ring-8 ring-white">
                                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd" />
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                            </svg>
                                                        </span>
                                                    @elseif($log->action_type === 'produced')
                                                        <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                                                            </svg>
                                                        </span>
                                                    @else
                                                        <span class="h-8 w-8 rounded-full bg-gray-500 flex items-center justify-center ring-8 ring-white">
                                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                                            </svg>
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                    <div>
                                                        <p class="text-sm text-gray-900">{{ $log->description }}</p>
                                                        @if($log->quantity_changed != 0)
                                                            <p class="text-sm text-gray-500">
                                                                @if($log->quantity_changed > 0)
                                                                    <span class="text-green-600">+{{ $log->quantity_changed }} pieces</span>
                                                                @else
                                                                    <span class="text-red-600">{{ $log->quantity_changed }} pieces</span>
                                                                @endif
                                                                @if($log->sqft_changed != 0)
                                                                    @if($log->sqft_changed > 0)
                                                                        <span class="text-green-600">, +{{ number_format($log->sqft_changed, 2) }} sqft</span>
                                                                    @else
                                                                        <span class="text-red-600">, {{ number_format($log->sqft_changed, 2) }} sqft</span>
                                                                    @endif
                                                                @endif
                                                            </p>
                                                        @endif
                                                    </div>
                                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                        {{ $log->created_at->format('M d, Y') }}
                                                        <div class="text-xs">{{ $log->created_at->format('h:i A') }}</div>
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

                <!-- Sidebar - Right Side -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Stock Usage Summary -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900">Stock Usage Summary</h2>
                        </div>
                        <div class="p-6">
                            @if($stockAddition->condition_status === 'Block' || $stockAddition->condition_status === 'Monuments')
                                <!-- Weight-based summary for Block/Monuments -->
                                <div class="space-y-6">
                                    <div class="text-center">
                                        <div class="text-3xl font-bold text-blue-600">
                                            @if($stockAddition->weight)
                                                {{ number_format($stockAddition->weight, 1) }} kg
                                            @else
                                                N/A
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-500">Per Piece Weight</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-3xl font-bold text-green-600">{{ number_format($stockAddition->available_pieces) }}</div>
                                        <div class="text-sm text-gray-500">Available Pieces</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-3xl font-bold text-red-600">{{ number_format($stockAddition->total_pieces - $stockAddition->available_pieces) }}</div>
                                        <div class="text-sm text-gray-500">Issued Pieces</div>
                                    </div>
                                </div>
                                @if($stockAddition->available_weight)
                                    <div class="mt-6 space-y-4">
                                        <div class="text-center">
                                            <div class="text-lg font-bold text-indigo-600">
                                                {{ number_format($stockAddition->available_weight, 1) }} kg
                                            </div>
                                            <div class="text-sm text-gray-500">Available Weight</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-lg font-bold text-orange-600">
                                                {{ number_format(($stockAddition->weight * ($stockAddition->total_pieces - $stockAddition->available_pieces)), 1) }} kg
                                            </div>
                                            <div class="text-sm text-gray-500">Issued Weight</div>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <!-- Pieces-based summary for other conditions -->
                                <div class="space-y-6">
                                    <div class="text-center">
                                        <div class="text-3xl font-bold text-blue-600">{{ number_format($stockAddition->total_pieces) }}</div>
                                        <div class="text-sm text-gray-500">Total Pieces</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-3xl font-bold text-green-600">{{ number_format($stockAddition->available_pieces) }}</div>
                                        <div class="text-sm text-gray-500">Available Pieces</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-3xl font-bold text-red-600">{{ number_format($stockAddition->total_pieces - $stockAddition->available_pieces) }}</div>
                                        <div class="text-sm text-gray-500">Issued Pieces</div>
                                    </div>
                                </div>
                            @endif
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

                    <!-- Quick Actions -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                @if($stockAddition->available_pieces > 0)
                                    <a href="{{ route('stock-management.stock-issued.create', ['stock_addition_id' => $stockAddition->id]) }}" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 block text-center">
                                        Issue Stock
                                    </a>
                                @endif
                                <a href="{{ route('stock-management.daily-production.create', ['stock_addition_id' => $stockAddition->id]) }}" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 block text-center">
                                    Record Production
                                </a>
                                <a href="{{ route('stock-management.stock-additions.edit', $stockAddition) }}" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 block text-center">
                                    Edit Stock
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
