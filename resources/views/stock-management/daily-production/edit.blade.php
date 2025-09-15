<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center">
                    <a href="{{ route('stock-management.daily-production.show', $dailyProduction) }}" class="mr-4 text-gray-600 hover:text-gray-900 transition-colors duration-200">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Edit Production</h1>
                        <p class="mt-2 text-gray-600">Update daily production information</p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="p-6">
                    <form method="POST" action="{{ route('stock-management.daily-production.update', $dailyProduction) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Stock Addition -->
                            <div class="md:col-span-2">
                                <label for="stock_addition_id" class="block text-sm font-medium text-gray-700 mb-2">Select Stock Addition</label>
                                <select id="stock_addition_id" name="stock_addition_id" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('stock_addition_id') border-red-500 @enderror" required>
                                    <option value="">Choose stock addition...</option>
                                    @foreach($availableStockAdditions as $addition)
                                        <option value="{{ $addition->id }}" {{ old('stock_addition_id', $dailyProduction->stock_addition_id) == $addition->id ? 'selected' : '' }}>
                                            {{ $addition->product->name }} - {{ $addition->mineVendor->name }} ({{ $addition->available_pieces }} pieces available)
                                        </option>
                                    @endforeach
                                </select>
                                @error('stock_addition_id')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Machine Name -->
                            <div>
                                <label for="machine_name" class="block text-sm font-medium text-gray-700 mb-2">Machine Name</label>
                                <input type="text" id="machine_name" name="machine_name" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('machine_name') border-red-500 @enderror" value="{{ old('machine_name', $dailyProduction->machine_name) }}" required>
                                @error('machine_name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Operator Name -->
                            <div>
                                <label for="operator_name" class="block text-sm font-medium text-gray-700 mb-2">Operator Name</label>
                                <input type="text" id="operator_name" name="operator_name" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('operator_name') border-red-500 @enderror" value="{{ old('operator_name', $dailyProduction->operator_name) }}" required>
                                @error('operator_name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Product -->
                            <div>
                                <label for="product" class="block text-sm font-medium text-gray-700 mb-2">Product</label>
                                <input type="text" id="product" name="product" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('product') border-red-500 @enderror" value="{{ old('product', $dailyProduction->product) }}" required>
                                @error('product')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Condition Status -->
                            <div>
                                <label for="condition_status" class="block text-sm font-medium text-gray-700 mb-2">Condition Status</label>
                                <select id="condition_status" name="condition_status" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('condition_status') border-red-500 @enderror" required>
                                    <option value="">Select condition...</option>
                                    <option value="Block" {{ old('condition_status', $dailyProduction->condition_status) == 'Block' ? 'selected' : '' }}>Block</option>
                                    <option value="Slabs" {{ old('condition_status', $dailyProduction->condition_status) == 'Slabs' ? 'selected' : '' }}>Slabs</option>
                                    <option value="Polished" {{ old('condition_status', $dailyProduction->condition_status) == 'Polished' ? 'selected' : '' }}>Polished</option>
                                </select>
                                @error('condition_status')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Total Pieces -->
                            <div>
                                <label for="total_pieces" class="block text-sm font-medium text-gray-700 mb-2">Total Pieces Produced</label>
                                <input type="number" id="total_pieces" name="total_pieces" min="1" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('total_pieces') border-red-500 @enderror" value="{{ old('total_pieces', $dailyProduction->total_pieces) }}" required>
                                @error('total_pieces')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Total Sqft -->
                            <div>
                                <label for="total_sqft" class="block text-sm font-medium text-gray-700 mb-2">Total Square Feet</label>
                                <input type="number" id="total_sqft" name="total_sqft" step="0.01" min="0" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('total_sqft') border-red-500 @enderror" value="{{ old('total_sqft', $dailyProduction->total_sqft) }}" required>
                                @error('total_sqft')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Date -->
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Production Date</label>
                                <input type="date" id="date" name="date" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('date') border-red-500 @enderror" value="{{ old('date', $dailyProduction->date->format('Y-m-d')) }}" required>
                                @error('date')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Notes -->
                            <div class="md:col-span-2">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                                <textarea id="notes" name="notes" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('notes') border-red-500 @enderror" placeholder="Add any additional notes...">{{ old('notes', $dailyProduction->notes) }}</textarea>
                                @error('notes')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Stock Info Display -->
                        <div id="stock-info" class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Selected Stock Information</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-gray-700">Product:</span>
                                    <span id="selected-product" class="text-gray-900">{{ $dailyProduction->stockAddition->product->name }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Vendor:</span>
                                    <span id="selected-vendor" class="text-gray-900">{{ $dailyProduction->stockAddition->mineVendor->name }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Available Pieces:</span>
                                    <span id="available-pieces" class="text-gray-900">{{ $dailyProduction->stockAddition->available_pieces }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Available Sqft:</span>
                                    <span id="available-sqft" class="text-gray-900">{{ number_format($dailyProduction->stockAddition->available_sqft, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end space-x-4 mt-8">
                            <a href="{{ route('stock-management.daily-production.show', $dailyProduction) }}" class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors duration-200">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                Update Production
                            </button>
                        </div>
                    </form>
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
            const availablePieces = document.getElementById('available-pieces');
            const availableSqft = document.getElementById('available-sqft');
            const productInput = document.getElementById('product');
            const piecesInput = document.getElementById('total_pieces');
            const sqftInput = document.getElementById('total_sqft');

            // Stock data from the server
            const stockData = @json($availableStockAdditions->keyBy('id'));

            stockSelect.addEventListener('change', function() {
                const selectedId = this.value;

                if (selectedId && stockData[selectedId]) {
                    const stock = stockData[selectedId];

                    selectedProduct.textContent = stock.product.name;
                    selectedVendor.textContent = stock.mine_vendor.name;
                    availablePieces.textContent = stock.available_pieces;
                    availableSqft.textContent = stock.available_sqft;

                    stockInfo.classList.remove('hidden');

                    // Auto-fill product name
                    productInput.value = stock.product.name;

                    // Set max values for inputs
                    piecesInput.max = stock.available_pieces;
                    sqftInput.max = stock.available_sqft;
                } else {
                    stockInfo.classList.add('hidden');
                }
            });

            // Auto-calculate sqft based on pieces
            piecesInput.addEventListener('input', function() {
                const selectedId = stockSelect.value;
                if (selectedId && stockData[selectedId]) {
                    const stock = stockData[selectedId];
                    const piecesPerSqft = stock.total_sqft / stock.total_pieces;
                    const calculatedSqft = this.value * piecesPerSqft;
                    sqftInput.value = calculatedSqft.toFixed(2);
                }
            });

            // Initialize stock info display on page load
            const currentStockId = stockSelect.value;
            if (currentStockId && stockData[currentStockId]) {
                const stock = stockData[currentStockId];
                selectedProduct.textContent = stock.product.name;
                selectedVendor.textContent = stock.mine_vendor.name;
                availablePieces.textContent = stock.available_pieces;
                availableSqft.textContent = stock.available_sqft;
                stockInfo.classList.remove('hidden');
            }
        });
    </script>
</x-app-layout>
