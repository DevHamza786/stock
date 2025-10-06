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

                        <!-- Stock Items Section -->
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Stock Items</h3>
                                <button type="button" id="add-item-btn" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                    <svg class="h-4 w-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Add Item
                                </button>
                            </div>

                            <div id="items-container">
                                <!-- Items will be added dynamically here -->
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

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

                            <!-- Client Name -->
                            <div>
                                <label for="client_name" class="block text-sm font-medium text-gray-700 mb-2">Client Name</label>
                                <input type="text" id="client_name" name="client_name" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('client_name') border-red-500 @enderror" value="{{ old('client_name', $gatePass->client_name) }}" placeholder="Enter client name">
                                @error('client_name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Client Number -->
                            <div>
                                <label for="client_number" class="block text-sm font-medium text-gray-700 mb-2">Client Number</label>
                                <input type="text" id="client_number" name="client_number" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('client_number') border-red-500 @enderror" value="{{ old('client_number', $gatePass->client_number) }}" placeholder="Enter client number">
                                @error('client_number')
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
            let itemCounter = 0;
            const stockData = @json(isset($stockAdditions) ? $stockAdditions->keyBy('id') : []);
            const addItemBtn = document.getElementById('add-item-btn');
            const itemsContainer = document.getElementById('items-container');
            const existingItems = @json($gatePass->items ?? []);

            // Add existing items first
            if (existingItems.length > 0) {
                existingItems.forEach(function(item) {
                    addItem(item);
                });
            } else {
                // Add first item by default if no existing items
                addItem();
            }

            // Add item button click handler
            addItemBtn.addEventListener('click', function() {
                addItem();
            });

            function addItem(existingItem = null) {
                const itemIndex = itemCounter++;
                const itemHtml = `
                    <div class="item-row border border-gray-200 rounded-lg p-4 mb-4 bg-gray-50" data-index="${itemIndex}">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-md font-medium text-gray-900">Item ${itemIndex + 1}</h4>
                            <button type="button" class="remove-item-btn text-red-600 hover:text-red-800 font-semibold" ${itemCounter === 1 ? 'style="display: none;"' : ''}>
                                <svg class="h-4 w-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Remove
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Stock Addition Selection -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Select Stock Addition</label>
                                <div class="relative">
                                    <input type="text" class="stock-search block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Search stock addition..." autocomplete="off">
                                    <input type="hidden" class="stock-addition-id" name="items[${itemIndex}][stock_addition_id]" value="${existingItem ? existingItem.stock_addition_id : ''}" required>
                                    <div class="stock-dropdown absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                        <div class="p-2 text-gray-500 text-sm">Choose stock addition...</div>
                                        ${generateStockOptions()}
                                    </div>
                                </div>
                            </div>

                            <!-- Quantity -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity to Dispatch</label>
                                <input type="number" class="quantity-input block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent" name="items[${itemIndex}][quantity_issued]" value="${existingItem ? existingItem.quantity_issued : ''}" min="1" required>
                            </div>

                            <!-- Stock Info Display -->
                            <div class="stock-info hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Stock Information</label>
                                <div class="bg-white p-3 rounded border text-sm">
                                    <div class="grid grid-cols-2 gap-2">
                                        <div><span class="font-medium">Product:</span> <span class="selected-product"></span></div>
                                        <div><span class="font-medium">Vendor:</span> <span class="selected-vendor"></span></div>
                                        <div><span class="font-medium">PID:</span> <span class="selected-pid"></span></div>
                                        <div><span class="font-medium">Available:</span> <span class="available-pieces text-green-600 font-semibold"></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                itemsContainer.insertAdjacentHTML('beforeend', itemHtml);
                initializeItemEvents(itemIndex, existingItem);
                updateRemoveButtons();
            }

            function generateStockOptions() {
                let options = '';
                Object.values(stockData).forEach(stock => {
                    const displayText = `${stock.product?.name || 'N/A'} - ${stock.mine_vendor?.name || 'N/A'} (${stock.available_pieces} pieces available, ${parseFloat(stock.available_sqft).toFixed(2)} sqft) - ${stock.date ? new Date(stock.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A'}${stock.pid ? ` - PID: ${stock.pid}` : ''}`;
                    options += `
                        <div class="stock-option px-3 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0" data-value="${stock.id}" data-text="${displayText}">
                            <div class="font-medium text-gray-900">${stock.product?.name || 'N/A'} - ${stock.mine_vendor?.name || 'N/A'}</div>
                            <div class="text-sm text-gray-600">
                                ${stock.available_pieces} pieces available, ${parseFloat(stock.available_sqft).toFixed(2)} sqft
                                ${stock.pid ? ` | PID: ${stock.pid}` : ''}
                            </div>
                            <div class="text-xs text-gray-500">${stock.date ? new Date(stock.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A'}</div>
                        </div>
                    `;
                });
                return options;
            }

            function initializeItemEvents(itemIndex, existingItem = null) {
                const itemRow = document.querySelector(`[data-index="${itemIndex}"]`);
                const stockSearch = itemRow.querySelector('.stock-search');
                const stockSelect = itemRow.querySelector('.stock-addition-id');
                const stockDropdown = itemRow.querySelector('.stock-dropdown');
                const stockOptions = itemRow.querySelectorAll('.stock-option');
                const stockInfo = itemRow.querySelector('.stock-info');
                const quantityInput = itemRow.querySelector('.quantity-input');
                const removeBtn = itemRow.querySelector('.remove-item-btn');

                // Initialize with existing item data if provided
                if (existingItem && stockData[existingItem.stock_addition_id]) {
                    const stock = stockData[existingItem.stock_addition_id];
                    const displayText = `${stock.product?.name || 'N/A'} - ${stock.mine_vendor?.name || 'N/A'} (${stock.available_pieces} pieces available, ${parseFloat(stock.available_sqft).toFixed(2)} sqft) - ${stock.date ? new Date(stock.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A'}${stock.pid ? ` - PID: ${stock.pid}` : ''}`;
                    stockSearch.value = displayText;
                    updateStockInfo(existingItem.stock_addition_id);
                }

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

                        itemRow.querySelector('.selected-product').textContent = stock.product?.name || 'N/A';
                        itemRow.querySelector('.selected-vendor').textContent = stock.mine_vendor?.name || 'N/A';
                        itemRow.querySelector('.selected-pid').textContent = stock.pid || 'N/A';
                        itemRow.querySelector('.available-pieces').textContent = stock.available_pieces || 0;

                        stockInfo.classList.remove('hidden');

                        // Set max value for quantity input based on available stock + current issued quantity
                        const currentIssued = existingItem ? existingItem.quantity_issued : 0;
                        const maxAllowed = (stock.available_pieces || 0) + currentIssued;
                        quantityInput.max = maxAllowed;

                        // Set current value if not already set
                        if (!quantityInput.value) {
                            quantityInput.value = Math.min(1, maxAllowed);
                        }
                    } else {
                        stockInfo.classList.add('hidden');
                    }
                }

                // Remove item button
                removeBtn.addEventListener('click', function() {
                    itemRow.remove();
                    updateRemoveButtons();
                });
            }

            function updateRemoveButtons() {
                const itemRows = document.querySelectorAll('.item-row');
                itemRows.forEach((row, index) => {
                    const removeBtn = row.querySelector('.remove-item-btn');
                    removeBtn.style.display = itemRows.length > 1 ? 'block' : 'none';
                });
            }
        });
    </script>
</x-app-layout>
