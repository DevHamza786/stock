<x-app-layout>
    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Edit Gate Pass</h1>
                        <p class="mt-2 text-gray-600">Update gate pass information</p>
                    </div>
                    <a href="{{ route('stock-management.gate-pass.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center transition-colors duration-200">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Gate Passes
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

                    @if(isset($stockAdditions) && $stockAdditions->count() > 0)
                    <form method="POST" action="{{ route('stock-management.gate-pass.update', $gatePass) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Form Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Stock Addition -->
                            <div>
                                <label for="stock_addition_id" class="block text-sm font-medium text-gray-700 mb-2">Stock Addition</label>
                                <select id="stock_addition_id" name="stock_addition_id" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('stock_addition_id') border-red-500 @enderror" required>
                                    <option value="">Select stock addition...</option>
                                    @foreach($stockAdditions as $addition)
                                        <option value="{{ $addition->id }}" {{ old('stock_addition_id', $gatePass->stockIssued->stock_addition_id) == $addition->id ? 'selected' : '' }}>
                                            {{ $addition->product->name }} - {{ $addition->mineVendor->name }} 
                                            ({{ $addition->available_pieces }} pieces available, {{ number_format($addition->available_sqft, 2) }} sqft)
                                            @if($addition->pid) - PID: {{ $addition->pid }} @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('stock_addition_id')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Quantity Issued -->
                            <div>
                                <label for="quantity_issued" class="block text-sm font-medium text-gray-700 mb-2">Quantity to Dispatch</label>
                                <input type="number" id="quantity_issued" name="quantity_issued" min="1" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('quantity_issued') border-red-500 @enderror" value="{{ old('quantity_issued', $gatePass->quantity_issued) }}" required>
                                @error('quantity_issued')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Destination -->
                            <div>
                                <label for="destination" class="block text-sm font-medium text-gray-700 mb-2">Destination</label>
                                <input type="text" id="destination" name="destination" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('destination') border-red-500 @enderror" value="{{ old('destination', $gatePass->destination) }}" placeholder="Enter destination address">
                                @error('destination')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Vehicle Number -->
                            <div>
                                <label for="vehicle_number" class="block text-sm font-medium text-gray-700 mb-2">Vehicle Number</label>
                                <input type="text" id="vehicle_number" name="vehicle_number" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('vehicle_number') border-red-500 @enderror" value="{{ old('vehicle_number', $gatePass->vehicle_number) }}" placeholder="Enter vehicle number">
                                @error('vehicle_number')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Driver Name -->
                            <div>
                                <label for="driver_name" class="block text-sm font-medium text-gray-700 mb-2">Driver Name</label>
                                <input type="text" id="driver_name" name="driver_name" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('driver_name') border-red-500 @enderror" value="{{ old('driver_name', $gatePass->driver_name) }}" placeholder="Enter driver name">
                                @error('driver_name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select id="status" name="status" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('status') border-red-500 @enderror" required>
                                    <option value="">Select status...</option>
                                    <option value="Pending" {{ old('status', $gatePass->status) == 'Pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="Approved" {{ old('status', $gatePass->status) == 'Approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="Dispatched" {{ old('status', $gatePass->status) == 'Dispatched' ? 'selected' : '' }}>Dispatched</option>
                                </select>
                                @error('status')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Date -->
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Gate Pass Date</label>
                                <input type="date" id="date" name="date" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('date') border-red-500 @enderror" value="{{ old('date', $gatePass->date->format('Y-m-d')) }}" required>
                                @error('date')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Notes -->
                            <div class="md:col-span-2">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                                <textarea id="notes" name="notes" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('notes') border-red-500 @enderror" placeholder="Add any additional notes...">{{ old('notes', $gatePass->notes) }}</textarea>
                                @error('notes')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Stock Info Display -->
                        <div id="stock-info" class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Selected Stock Information</h3>
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-gray-700">Product:</span>
                                    <span id="selected-product" class="text-gray-900">{{ $gatePass->stockIssued->stockAddition->product->name }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Vendor:</span>
                                    <span id="selected-vendor" class="text-gray-900">{{ $gatePass->stockIssued->stockAddition->mineVendor->name }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">PID:</span>
                                    <span id="selected-pid" class="text-gray-900">{{ $gatePass->stockIssued->stockAddition->pid ?? 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Available Pieces:</span>
                                    <span id="available-pieces" class="text-gray-900 text-green-600 font-semibold">{{ $gatePass->stockIssued->stockAddition->available_pieces }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Available Sqft:</span>
                                    <span id="available-sqft" class="text-gray-900">{{ number_format($gatePass->stockIssued->stockAddition->available_sqft, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end space-x-4 mt-8">
                            <a href="{{ route('stock-management.gate-pass.index') }}" class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors duration-200">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                Update Gate Pass
                            </button>
                        </div>
                    </form>
                    @else
                    <div class="text-center py-12">
                        <svg class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No stock additions available</h3>
                        <p class="text-gray-500 mb-4">You need to add stock before creating gate passes.</p>
                        <a href="{{ route('stock-management.stock-additions.create') }}" class="bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Add Stock First
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const stockSelect = document.getElementById('stock_addition_id');
            const stockInfo = document.getElementById('stock-info');
            const selectedProduct = document.getElementById('selected-product');
            const selectedVendor = document.getElementById('selected-vendor');
            const selectedPid = document.getElementById('selected-pid');
            const availablePieces = document.getElementById('available-pieces');
            const availableSqft = document.getElementById('available-sqft');
            const quantityInput = document.getElementById('quantity_issued');

            // Stock data from the server
            const stockData = @json(isset($stockAdditions) ? $stockAdditions->keyBy('id') : []);

            stockSelect.addEventListener('change', function() {
                const selectedId = this.value;

                if (selectedId && stockData[selectedId]) {
                    const stock = stockData[selectedId];

                    selectedProduct.textContent = stock.product.name;
                    selectedVendor.textContent = stock.mine_vendor.name;
                    selectedPid.textContent = stock.pid || 'N/A';
                    availablePieces.textContent = stock.available_pieces;
                    availableSqft.textContent = parseFloat(stock.available_sqft).toFixed(2);

                    stockInfo.classList.remove('hidden');

                    // Set max value for quantity input based on available stock
                    quantityInput.max = stock.available_pieces;
                } else {
                    stockInfo.classList.add('hidden');
                }
            });
        });
    </script>
</x-app-layout>
