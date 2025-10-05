<x-app-layout>
    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center">
                    <a href="{{ route('stock-management.gate-pass.index') }}" class="mr-4 text-gray-600 hover:text-gray-900 transition-colors duration-200">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Create Gate Pass</h1>
                        <p class="mt-2 text-gray-600">Create gate pass for stock dispatch</p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="p-6">
                    @if(!isset($stockAdditions) || $stockAdditions->count() == 0)
                        <div class="text-center py-12">
                            <svg class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Available Stock for Dispatch</h3>
                            <p class="text-gray-500 mb-4">You need to have stock with available pieces before you can create a gate pass.</p>
                            <a href="{{ route('stock-management.stock-additions.create') }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                Add Stock First
                            </a>
                        </div>
                    @else
                    <form method="POST" action="{{ route('stock-management.gate-pass.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Stock Addition -->
                            <div class="md:col-span-2">
                                <label for="stock_addition_search" class="block text-sm font-medium text-gray-700 mb-2">Select Stock Addition (Available for Dispatch)</label>
                                <div class="relative">
                                    <input type="text" id="stock_addition_search" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('stock_addition_id') border-red-500 @enderror" placeholder="Search stock addition..." autocomplete="off">
                                    <input type="hidden" id="stock_addition_id" name="stock_addition_id" value="{{ old('stock_addition_id') }}" required>
                                    <div id="stock_dropdown" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                        <div class="p-2 text-gray-500 text-sm">Choose stock addition...</div>
                                        @foreach($stockAdditions as $addition)
                                            <div class="stock-option px-3 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0" data-value="{{ $addition->id }}" data-text="{{ $addition->product->name ?? 'N/A' }} - {{ $addition->mineVendor->name ?? 'N/A' }} ({{ $addition->available_pieces }} pieces available, {{ number_format($addition->available_sqft, 2) }} sqft) - {{ $addition->date ? $addition->date->format('M d, Y') : 'N/A' }}@if($addition->pid) - PID: {{ $addition->pid }}@endif">
                                                <div class="font-medium text-gray-900">{{ $addition->product->name ?? 'N/A' }} - {{ $addition->mineVendor->name ?? 'N/A' }}</div>
                                                <div class="text-sm text-gray-600">
                                                    {{ $addition->available_pieces }} pieces available, {{ number_format($addition->available_sqft, 2) }} sqft
                                                    @if($addition->pid) | PID: {{ $addition->pid }} @endif
                                                </div>
                                                <div class="text-xs text-gray-500">{{ $addition->date ? $addition->date->format('M d, Y') : 'N/A' }}</div>
                                            </div>
                                    @endforeach
                                    </div>
                                </div>
                                @error('stock_addition_id')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Quantity Issued -->
                            <div>
                                <label for="quantity_issued" class="block text-sm font-medium text-gray-700 mb-2">Quantity to Dispatch</label>
                                <input type="number" id="quantity_issued" name="quantity_issued" min="1" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('quantity_issued') border-red-500 @enderror" value="{{ old('quantity_issued') }}" required>
                                @error('quantity_issued')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Destination -->
                            <div>
                                <label for="destination" class="block text-sm font-medium text-gray-700 mb-2">Destination</label>
                                <input type="text" id="destination" name="destination" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('destination') border-red-500 @enderror" value="{{ old('destination') }}" placeholder="Enter destination address">
                                @error('destination')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Vehicle Number -->
                            <div>
                                <label for="vehicle_number" class="block text-sm font-medium text-gray-700 mb-2">Vehicle Number</label>
                                <input type="text" id="vehicle_number" name="vehicle_number" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('vehicle_number') border-red-500 @enderror" value="{{ old('vehicle_number') }}" placeholder="Enter vehicle number">
                                @error('vehicle_number')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Driver Name -->
                            <div>
                                <label for="driver_name" class="block text-sm font-medium text-gray-700 mb-2">Driver Name</label>
                                <input type="text" id="driver_name" name="driver_name" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('driver_name') border-red-500 @enderror" value="{{ old('driver_name') }}" placeholder="Enter driver name">
                                @error('driver_name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select id="status" name="status" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('status') border-red-500 @enderror" required>
                                    <option value="">Select status...</option>
                                    <option value="Pending" {{ old('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="Approved" {{ old('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="Dispatched" {{ old('status') == 'Dispatched' ? 'selected' : '' }}>Dispatched</option>
                                </select>
                                @error('status')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Date -->
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Gate Pass Date</label>
                                <input type="date" id="date" name="date" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('date') border-red-500 @enderror" value="{{ old('date', date('Y-m-d')) }}" required>
                                @error('date')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Notes -->
                            <div class="md:col-span-2">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                                <textarea id="notes" name="notes" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('notes') border-red-500 @enderror" placeholder="Add any additional notes...">{{ old('notes') }}</textarea>
                                @error('notes')
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
                                    <span class="font-medium text-gray-700">PID:</span>
                                    <span id="selected-pid" class="text-gray-900"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Available Pieces:</span>
                                    <span id="available-pieces" class="text-gray-900 text-green-600 font-semibold"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Available Sqft:</span>
                                    <span id="available-sqft" class="text-gray-900"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end space-x-4 mt-8">
                            <a href="{{ route('stock-management.gate-pass.index') }}" class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors duration-200">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                Create Gate Pass
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
            const stockSearch = document.getElementById('stock_addition_search');
            const stockSelect = document.getElementById('stock_addition_id');
            const stockDropdown = document.getElementById('stock_dropdown');
            const stockOptions = document.querySelectorAll('.stock-option');
            const stockInfo = document.getElementById('stock-info');
            const selectedProduct = document.getElementById('selected-product');
            const selectedVendor = document.getElementById('selected-vendor');
            const selectedPid = document.getElementById('selected-pid');
            const availablePieces = document.getElementById('available-pieces');
            const availableSqft = document.getElementById('available-sqft');
            const quantityInput = document.getElementById('quantity_issued');

            // Stock data from the server
            const stockData = @json(isset($stockAdditions) ? $stockAdditions->keyBy('id') : []);

            // Show dropdown when input is focused
            stockSearch.addEventListener('focus', function() {
                stockDropdown.classList.remove('hidden');
                filterOptions();
            });

            // Hide dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!stockSearch.contains(e.target) && !stockDropdown.contains(e.target)) {
                    stockDropdown.classList.add('hidden');
                }
            });

            // Search functionality
            stockSearch.addEventListener('input', function() {
                filterOptions();
                stockDropdown.classList.remove('hidden');
            });

            // Filter options based on search term
            function filterOptions() {
                const searchTerm = stockSearch.value.toLowerCase();
                let hasVisibleOptions = false;

                stockOptions.forEach(function(option) {
                    const optionText = option.dataset.text.toLowerCase();
                    if (optionText.includes(searchTerm)) {
                        option.style.display = 'block';
                        hasVisibleOptions = true;
                    } else {
                        option.style.display = 'none';
                    }
                });

                // Show/hide the placeholder based on search results
                const placeholder = stockDropdown.querySelector('.p-2');
                if (placeholder) {
                    placeholder.style.display = hasVisibleOptions ? 'none' : 'block';
                }
            }

            // Handle option selection
            stockOptions.forEach(function(option) {
                option.addEventListener('click', function() {
                    const value = this.dataset.value;
                    const text = this.dataset.text;
                    
                    stockSelect.value = value;
                    stockSearch.value = text;
                    stockDropdown.classList.add('hidden');

                    // Update stock info
                    updateStockInfo(value);
                });
            });

            // Update stock information display
            function updateStockInfo(selectedId) {
                if (selectedId && stockData[selectedId]) {
                    const stock = stockData[selectedId];

                    selectedProduct.textContent = stock.product?.name || 'N/A';
                    selectedVendor.textContent = stock.mine_vendor?.name || 'N/A';
                    selectedPid.textContent = stock.pid || 'N/A';
                    availablePieces.textContent = stock.available_pieces || 0;
                    availableSqft.textContent = stock.available_sqft ? parseFloat(stock.available_sqft).toFixed(2) : '0.00';

                    stockInfo.classList.remove('hidden');

                    // Set max value for quantity input based on available stock
                    quantityInput.max = stock.available_pieces || 0;
                    quantityInput.value = Math.min(quantityInput.value || 1, stock.available_pieces || 0);
                } else {
                    stockInfo.classList.add('hidden');
                }
            }

            // Initialize with old value if exists
            @if(old('stock_addition_id'))
                const oldValue = {{ old('stock_addition_id') }};
                if (stockData[oldValue]) {
                    const stock = stockData[oldValue];
                    const displayText = `${stock.product?.name || 'N/A'} - ${stock.mine_vendor?.name || 'N/A'} (${stock.available_pieces} pieces available, ${parseFloat(stock.available_sqft).toFixed(2)} sqft) - ${stock.date ? new Date(stock.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A'}${stock.pid ? ` - PID: ${stock.pid}` : ''}`;
                    stockSearch.value = displayText;
                    updateStockInfo(oldValue);
                }
            @endif
        });
    </script>
</x-app-layout>
