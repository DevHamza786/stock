<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center">
                    <a href="{{ route('stock-management.stock-issued.index') }}" class="mr-4 text-gray-600 hover:text-gray-900 transition-colors duration-200">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Issue Stock</h1>
                        <p class="mt-2 text-gray-600">Issue stock for production purposes</p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="p-6">
                    @if(!isset($stockAdditions) || $stockAdditions->count() == 0)
                        <div class="text-center py-12">
                            <svg class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Available Stock</h3>
                            <p class="text-gray-500 mb-4">You need to add stock before you can issue it for production.</p>
                            <a href="{{ route('stock-management.stock-additions.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                Add Stock First
                            </a>
                        </div>
                    @else
                    <form method="POST" action="{{ route('stock-management.stock-issued.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Stock Addition -->
                            <div class="md:col-span-2">
                                <label for="stock_addition_id" class="block text-sm font-medium text-gray-700 mb-2">Select Stock Issuance</label>
                                <select id="stock_addition_id" name="stock_addition_id" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('stock_addition_id') border-red-500 @enderror" required>
                                    <option value="">Choose stock issuance...</option>
                                    @if(isset($stockAdditions) && $stockAdditions->count() > 0)
                                        @foreach($stockAdditions as $addition)
                                            <option value="{{ $addition->id }}" {{ old('stock_addition_id') == $addition->id ? 'selected' : '' }}>
                                                {{ $addition->product->name }} - {{ $addition->mineVendor->name }} - Size: {{ $addition->size_3d }} ({{ $addition->available_pieces }} pieces available)
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>No available stock additions</option>
                                    @endif
                                </select>
                                @error('stock_addition_id')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Quantity Issued -->
                            <div>
                                <label for="quantity_issued" class="block text-sm font-medium text-gray-700 mb-2">Quantity to Issue</label>
                                <input type="number" id="quantity_issued" name="quantity_issued" min="1" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('quantity_issued') border-red-500 @enderror" value="{{ old('quantity_issued') }}" required>
                                @error('quantity_issued')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Sqft Issued -->
                            <div>
                                <label for="sqft_issued" class="block text-sm font-medium text-gray-700 mb-2">Square Feet to Issue</label>
                                <input type="number" id="sqft_issued" name="sqft_issued" step="0.01" min="0" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('sqft_issued') border-red-500 @enderror" value="{{ old('sqft_issued') }}" required>
                                @error('sqft_issued')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Purpose -->
                            <div class="md:col-span-2">
                                <label for="purpose" class="block text-sm font-medium text-gray-700 mb-2">Purpose</label>
                                <select id="purpose" name="purpose" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('purpose') border-red-500 @enderror" required>
                                    <option value="">Select purpose...</option>
                                    <option value="Production" {{ old('purpose') == 'Production' ? 'selected' : '' }}>Production</option>
                                    <option value="Processing" {{ old('purpose') == 'Processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="Cutting" {{ old('purpose') == 'Cutting' ? 'selected' : '' }}>Cutting</option>
                                    <option value="Polishing" {{ old('purpose') == 'Polishing' ? 'selected' : '' }}>Polishing</option>
                                    <option value="Other" {{ old('purpose') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('purpose')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Machine Name -->
                            <div>
                                <label for="machine_name" class="block text-sm font-medium text-gray-700 mb-2">Machine Name</label>
                                <input type="text" id="machine_name" name="machine_name" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('machine_name') border-red-500 @enderror" value="{{ old('machine_name') }}" placeholder="Enter machine name...">
                                @error('machine_name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Operator Name -->
                            <div>
                                <label for="operator_name" class="block text-sm font-medium text-gray-700 mb-2">Operator Name</label>
                                <input type="text" id="operator_name" name="operator_name" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('operator_name') border-red-500 @enderror" value="{{ old('operator_name') }}" placeholder="Enter operator name...">
                                @error('operator_name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Notes -->
                            <div class="md:col-span-2">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                                <textarea id="notes" name="notes" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('notes') border-red-500 @enderror" placeholder="Add any additional notes...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Date -->
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Issue Date</label>
                                <input type="date" id="date" name="date" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('date') border-red-500 @enderror" value="{{ old('date', date('Y-m-d')) }}" required>
                                @error('date')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Stock Info Display -->
                        <div id="stock-info" class="mt-6 p-4 bg-gray-50 rounded-lg hidden">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Selected Stock Information</h3>
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-gray-700">Product:</span>
                                    <span id="selected-product" class="text-gray-900"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Vendor:</span>
                                    <span id="selected-vendor" class="text-gray-900"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Size:</span>
                                    <span id="selected-size" class="text-gray-900"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Available Pieces:</span>
                                    <span id="available-pieces" class="text-gray-900"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Available Sqft:</span>
                                    <span id="available-sqft" class="text-gray-900"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end space-x-4 mt-8">
                            <a href="{{ route('stock-management.stock-issued.index') }}" class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors duration-200">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                Issue Stock
                            </button>
                        </div>
                    </form>
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
            const selectedSize = document.getElementById('selected-size');
            const availablePieces = document.getElementById('available-pieces');
            const availableSqft = document.getElementById('available-sqft');
            const quantityInput = document.getElementById('quantity_issued');
            const sqftInput = document.getElementById('sqft_issued');

            // Stock data from the server
            const stockData = @json(isset($stockAdditions) ? $stockAdditions->keyBy('id') : []);

            stockSelect.addEventListener('change', function() {
                const selectedId = this.value;

                if (selectedId && stockData[selectedId]) {
                    const stock = stockData[selectedId];

                    selectedProduct.textContent = stock.product.name;
                    selectedVendor.textContent = stock.mine_vendor.name;
                    selectedSize.textContent = stock.size_3d;
                    availablePieces.textContent = stock.available_pieces;
                    availableSqft.textContent = stock.available_sqft;

                    stockInfo.classList.remove('hidden');

                    // Set max values for inputs
                    quantityInput.max = stock.available_pieces;
                    sqftInput.max = stock.available_sqft;
                } else {
                    stockInfo.classList.add('hidden');
                }
            });

            // Auto-calculate sqft based on quantity (if needed)
            quantityInput.addEventListener('input', function() {
                const selectedId = stockSelect.value;
                if (selectedId && stockData[selectedId]) {
                    const stock = stockData[selectedId];
                    const piecesPerSqft = stock.total_sqft / stock.total_pieces;
                    const calculatedSqft = this.value * piecesPerSqft;
                    sqftInput.value = calculatedSqft.toFixed(2);
                }
            });
        });
    </script>
</x-app-layout>
