<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center">
                    <a href="{{ route('stock-management.daily-production.index') }}" class="mr-4 text-gray-600 hover:text-gray-900 transition-colors duration-200">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Record Production</h1>
                        <p class="mt-2 text-gray-600">Record daily production activities with multiple products</p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="p-6">
                    @if(!isset($availableStockIssued) || $availableStockIssued->count() == 0)
                        <div class="text-center py-12">
                            <svg class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Stock Issued for Production</h3>
                            <p class="text-gray-500 mb-4">You need to issue stock for production before you can record production.</p>
                            <a href="{{ route('stock-management.stock-issued.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                Issue Stock for Production
                            </a>
                        </div>
                    @else
                    <form method="POST" action="{{ route('stock-management.daily-production.store') }}" id="production-form">
                        @csrf

                        <!-- Basic Information Section -->
                        <div class="bg-gray-50 p-6 rounded-lg mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Production Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Stock Issued -->
                            <div class="md:col-span-2">
                                    <label for="stock_issued_id" class="block text-sm font-medium text-gray-700 mb-2">Select Stock Issued for Production</label>
                                    <select id="stock_issued_id" name="stock_issued_id" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('stock_issued_id') border-red-500 @enderror" required>
                                        <option value="">Choose stock issued for production...</option>
                                        @foreach($availableStockIssued as $issued)
                                            <option value="{{ $issued->id }}" {{ old('stock_issued_id') == $issued->id ? 'selected' : '' }}>
                                                {{ $issued->stockAddition->product->name }} - {{ $issued->stockAddition->mineVendor->name }} - {{ $issued->quantity_issued }} pieces issued ({{ $issued->date->format('M d, Y') }})
                                        </option>
                                    @endforeach
                                </select>
                                    @error('stock_issued_id')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Machine Name -->
                            <div>
                                <label for="machine_name" class="block text-sm font-medium text-gray-700 mb-2">Machine Name</label>
                                    <select id="machine_name" name="machine_name" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('machine_name') border-red-500 @enderror" required>
                                        <option value="">Select machine...</option>
                                        @foreach($machines as $machine)
                                            <option value="{{ $machine->name }}" {{ old('machine_name') == $machine->name ? 'selected' : '' }}>
                                                {{ $machine->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @error('machine_name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Operator Name -->
                            <div>
                                <label for="operator_name" class="block text-sm font-medium text-gray-700 mb-2">Operator Name</label>
                                    <select id="operator_name" name="operator_name" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('operator_name') border-red-500 @enderror" required>
                                        <option value="">Select operator...</option>
                                        @foreach($operators as $operator)
                                            <option value="{{ $operator->name }}" {{ old('operator_name') == $operator->name ? 'selected' : '' }}>
                                                {{ $operator->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @error('operator_name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Date -->
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Production Date</label>
                                <input type="date" id="date" name="date" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('date') border-red-500 @enderror" value="{{ old('date', date('Y-m-d')) }}" required>
                                @error('date')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Notes -->
                                <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                                <textarea id="notes" name="notes" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('notes') border-red-500 @enderror" placeholder="Add any additional notes...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Stock Info Display -->
                        <div id="stock-info" class="mt-6 p-4 bg-blue-50 rounded-lg hidden">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Selected Stock Issued Information</h3>
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
                                    <span class="font-medium text-gray-700">Issued Pieces:</span>
                                    <span id="available-pieces" class="text-gray-900"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Issued Sqft:</span>
                                    <span id="available-sqft" class="text-gray-900"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Sqft per Piece:</span>
                                    <span id="sqft-per-piece" class="text-gray-900"></span>
                                </div>
                            </div>
                            <div class="mt-4 p-3 bg-white rounded-lg border">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <div class="flex items-center justify-between">
                                            <span class="font-medium text-gray-700">Pieces Status:</span>
                                            <span id="remaining-pieces" class="text-lg font-semibold text-green-600">0</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                            <div id="stock-progress" class="bg-green-600 h-2 rounded-full transition-all duration-300" style="width: 100%"></div>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">Blue = More pieces than issued (smaller pieces)</p>
                                    </div>
                                    <div>
                                        <div class="flex items-center justify-between">
                                            <span class="font-medium text-gray-700">Remaining Sqft:</span>
                                            <span id="remaining-sqft" class="text-lg font-semibold text-blue-600">0</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                            <div id="sqft-progress" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 100%"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 p-2 bg-gray-50 rounded text-sm">
                                    <div class="flex justify-between">
                                        <span>Total Production Sqft:</span>
                                        <span id="total-production-sqft" class="font-medium">0.00</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Issued Sqft:</span>
                                        <span id="issued-sqft" class="font-medium">0.00</span>
                                    </div>
                                    <div class="flex justify-between border-t pt-1 mt-1">
                                        <span>Difference:</span>
                                        <span id="sqft-difference" class="font-medium">0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Production Items Section -->
                        <div class="mt-8">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Production Items</h3>
                                <button type="button" id="add-production-item" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 flex items-center">
                                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Add Production Item
                                </button>
                            </div>

                            <div id="production-items-container">
                                <!-- Production items will be added here dynamically -->
                            </div>

                            <div id="no-items-message" class="text-center py-8 text-gray-500">
                                <svg class="h-12 w-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                <p>No production items added yet. Click "Add Production Item" to start.</p>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end space-x-4 mt-8">
                            <a href="{{ route('stock-management.daily-production.index') }}" class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors duration-200">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                Record Production
                            </button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Production Item Template -->
    <template id="production-item-template">
        <div class="production-item bg-gray-50 p-6 rounded-lg mb-4 border border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-md font-semibold text-gray-900 production-item-title">Production #1</h4>
                <button type="button" class="remove-production-item text-red-600 hover:text-red-800 font-semibold">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Product Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Product Name</label>
                    <input type="text" name="items[INDEX][product_name]" class="product-name-input block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                </div>

                <!-- Size -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Size (e.g., 60*90, H*L)</label>
                    <input type="text" name="items[INDEX][size]" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="e.g., 60*90">
                </div>

                <!-- Diameter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Diameter (W)</label>
                    <input type="text" name="items[INDEX][diameter]" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="e.g., 6cm, 2cm">
                </div>

                <!-- Condition Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Condition Status</label>
                    <select name="items[INDEX][condition_status]" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                        <option value="">Select condition...</option>
                        @foreach($conditionStatuses as $status)
                            <option value="{{ $status->name }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Special Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Special Status</label>
                    <input type="text" name="items[INDEX][special_status]" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="e.g., Polished, Hound, Bushed">
                </div>

                <!-- Total Pieces -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Total Pieces</label>
                    <input type="number" name="items[INDEX][total_pieces]" class="total-pieces-input block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" min="1" required>
                    <p class="text-xs text-gray-500 mt-1">If > issued pieces: sqft ÷ total pieces = piece size</p>
                </div>

                <!-- Total Sqft -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Total Sqft</label>
                    <input type="number" name="items[INDEX][total_sqft]" class="total-sqft-input block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" step="0.01" min="0" required>
                    <p class="text-xs text-gray-500 mt-1">If > issued pieces: equals issued sqft</p>
                </div>

                <!-- Piece Size Display -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Piece Size</label>
                    <div class="block w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700">
                        <span class="piece-size-display">0.00</span> sqft per piece
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Size of each individual piece</p>
                </div>

                <!-- Narration -->
                <div class="md:col-span-2 lg:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Narration</label>
                    <textarea name="items[INDEX][narration]" rows="2" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="Additional notes for this production item..."></textarea>
                </div>
            </div>

            <!-- Product Matching Warning -->
            <div class="product-matching-warning mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg hidden">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <p class="text-sm text-yellow-800">
                        <strong>Warning:</strong> A product with similar specifications already exists in this production.
                        The quantities will be merged instead of creating a duplicate entry.
                    </p>
                </div>
            </div>
        </div>
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const stockSelect = document.getElementById('stock_issued_id');
            const stockInfo = document.getElementById('stock-info');
            const selectedProduct = document.getElementById('selected-product');
            const selectedVendor = document.getElementById('selected-vendor');
            const availablePieces = document.getElementById('available-pieces');
            const availableSqft = document.getElementById('available-sqft');
            const sqftPerPiece = document.getElementById('sqft-per-piece');
            const addItemBtn = document.getElementById('add-production-item');
            const itemsContainer = document.getElementById('production-items-container');
            const noItemsMessage = document.getElementById('no-items-message');
            const productionForm = document.getElementById('production-form');
            const remainingPieces = document.getElementById('remaining-pieces');
            const stockProgress = document.getElementById('stock-progress');
            const remainingSqft = document.getElementById('remaining-sqft');
            const sqftProgress = document.getElementById('sqft-progress');
            const totalProductionSqft = document.getElementById('total-production-sqft');
            const issuedSqft = document.getElementById('issued-sqft');
            const sqftDifference = document.getElementById('sqft-difference');

            let itemIndex = 0;
            let stockIssuedData = @json(isset($availableStockIssued) ? $availableStockIssued->keyBy('id') : []);
            let currentStockIssued = null;

            // Stock selection handler
            stockSelect.addEventListener('change', function() {
                const selectedId = this.value;

                if (selectedId && stockIssuedData[selectedId]) {
                    currentStockIssued = stockIssuedData[selectedId];
                    const stockAddition = currentStockIssued.stock_addition;

                    selectedProduct.textContent = stockAddition.product.name;
                    selectedVendor.textContent = stockAddition.mine_vendor.name;
                    availablePieces.textContent = currentStockIssued.quantity_issued;
                    availableSqft.textContent = parseFloat(currentStockIssued.sqft_issued).toFixed(2);
                    issuedSqft.textContent = parseFloat(currentStockIssued.sqft_issued).toFixed(2);

                    // Calculate and display sqft per piece
                    const sqftPerPieceValue = parseFloat(currentStockIssued.sqft_issued) / currentStockIssued.quantity_issued;
                    sqftPerPiece.textContent = sqftPerPieceValue.toFixed(2);

                    stockInfo.classList.remove('hidden');

                    // Auto-fill machine and operator fields
                    const machineSelect = document.getElementById('machine_name');
                    const operatorSelect = document.getElementById('operator_name');

                    if (currentStockIssued.machine_name && machineSelect) {
                        // Find and select the machine option
                        const machineOptions = machineSelect.querySelectorAll('option');
                        let machineFound = false;
                        machineOptions.forEach(option => {
                            if (option.value === currentStockIssued.machine_name) {
                                option.selected = true;
                                machineFound = true;
                            }
                        });

                        // If machine not found in options, add it as a new option
                        if (!machineFound && currentStockIssued.machine_name) {
                            const newOption = document.createElement('option');
                            newOption.value = currentStockIssued.machine_name;
                            newOption.textContent = currentStockIssued.machine_name;
                            newOption.selected = true;
                            machineSelect.appendChild(newOption);
                        }
                    }

                    if (currentStockIssued.operator_name && operatorSelect) {
                        // Find and select the operator option
                        const operatorOptions = operatorSelect.querySelectorAll('option');
                        let operatorFound = false;
                        operatorOptions.forEach(option => {
                            if (option.value === currentStockIssued.operator_name) {
                                option.selected = true;
                                operatorFound = true;
                            }
                        });

                        // If operator not found in options, add it as a new option
                        if (!operatorFound && currentStockIssued.operator_name) {
                            const newOption = document.createElement('option');
                            newOption.value = currentStockIssued.operator_name;
                            newOption.textContent = currentStockIssued.operator_name;
                            newOption.selected = true;
                            operatorSelect.appendChild(newOption);
                        }
                    }

                    // Update remaining stock display
                    updateRemainingStock();
                } else {
                    stockInfo.classList.add('hidden');
                    currentStockIssued = null;

                    // Clear machine and operator selections
                    const machineSelect = document.getElementById('machine_name');
                    const operatorSelect = document.getElementById('operator_name');

                    if (machineSelect) {
                        machineSelect.selectedIndex = 0; // Select the first option (placeholder)
                    }
                    if (operatorSelect) {
                        operatorSelect.selectedIndex = 0; // Select the first option (placeholder)
                    }
                }
            });

            // Helper function to get total pieces used
            function getTotalPiecesUsed() {
                const items = itemsContainer.querySelectorAll('.production-item');
                let totalUsedPieces = 0;
                items.forEach(item => {
                    const pieces = parseInt(item.querySelector('.total-pieces-input').value) || 0;
                    totalUsedPieces += pieces;
                });
                return totalUsedPieces;
            }

            // Function to update remaining stock display
            function updateRemainingStock() {
                if (!currentStockIssued) return;

                const items = itemsContainer.querySelectorAll('.production-item');
                let totalUsedPieces = 0;
                let totalUsedSqft = 0;

                items.forEach(item => {
                    const pieces = parseInt(item.querySelector('.total-pieces-input').value) || 0;
                    const sqft = parseFloat(item.querySelector('.total-sqft-input').value) || 0;
                    totalUsedPieces += pieces;
                    totalUsedSqft += sqft;
                });

                // Update pieces display (pieces can be more than issued)
                const piecesDifference = totalUsedPieces - currentStockIssued.quantity_issued;
                const piecesPercentage = Math.min(100, (currentStockIssued.quantity_issued / totalUsedPieces) * 100);

                if (piecesDifference > 0) {
                    document.getElementById('remaining-pieces').textContent = `+${piecesDifference} (${totalUsedPieces} total)`;
                    stockProgress.className = 'bg-blue-600 h-2 rounded-full transition-all duration-300';
                    document.getElementById('remaining-pieces').className = 'text-lg font-semibold text-blue-600';
                } else {
                    const remainingPieces = currentStockIssued.quantity_issued - totalUsedPieces;
                    document.getElementById('remaining-pieces').textContent = remainingPieces;

                    if (remainingPieces <= 0) {
                        stockProgress.className = 'bg-red-600 h-2 rounded-full transition-all duration-300';
                        document.getElementById('remaining-pieces').className = 'text-lg font-semibold text-red-600';
                    } else if (remainingPieces <= currentStockIssued.quantity_issued * 0.2) {
                        stockProgress.className = 'bg-yellow-600 h-2 rounded-full transition-all duration-300';
                        document.getElementById('remaining-pieces').className = 'text-lg font-semibold text-yellow-600';
                    } else {
                        stockProgress.className = 'bg-green-600 h-2 rounded-full transition-all duration-300';
                        document.getElementById('remaining-pieces').className = 'text-lg font-semibold text-green-600';
                    }
                }

                stockProgress.style.width = piecesPercentage + '%';

                // Update sqft display
                const remainingSqft = parseFloat(currentStockIssued.sqft_issued) - totalUsedSqft;
                const sqftPercentage = (remainingSqft / parseFloat(currentStockIssued.sqft_issued)) * 100;

                document.getElementById('remaining-sqft').textContent = remainingSqft.toFixed(2);
                sqftProgress.style.width = sqftPercentage + '%';

                // Update summary
                totalProductionSqft.textContent = totalUsedSqft.toFixed(2);
                const difference = totalUsedSqft - parseFloat(currentStockIssued.sqft_issued);
                sqftDifference.textContent = difference.toFixed(2);

                // Change color based on sqft difference
                if (Math.abs(difference) <= 0.01) {
                    sqftDifference.className = 'font-medium text-green-600';
                    document.getElementById('remaining-sqft').className = 'text-lg font-semibold text-green-600';
                    sqftProgress.className = 'bg-green-600 h-2 rounded-full transition-all duration-300';
                } else if (Math.abs(difference) <= parseFloat(currentStockIssued.sqft_issued) * 0.1) {
                    sqftDifference.className = 'font-medium text-yellow-600';
                    document.getElementById('remaining-sqft').className = 'text-lg font-semibold text-yellow-600';
                    sqftProgress.className = 'bg-yellow-600 h-2 rounded-full transition-all duration-300';
                } else {
                    sqftDifference.className = 'font-medium text-red-600';
                    document.getElementById('remaining-sqft').className = 'text-lg font-semibold text-red-600';
                    sqftProgress.className = 'bg-red-600 h-2 rounded-full transition-all duration-300';
                }
            }

            // Add production item
            addItemBtn.addEventListener('click', function() {
                if (!currentStockIssued) {
                    alert('Please select a stock issued first.');
                    return;
                }

                addProductionItem();
            });

            function addProductionItem() {
                const template = document.getElementById('production-item-template');
                const clone = template.content.cloneNode(true);

                // Update index placeholders
                const html = clone.querySelector('.production-item').outerHTML;
                const updatedHtml = html.replace(/INDEX/g, itemIndex);

                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = updatedHtml;
                const newItem = tempDiv.firstElementChild;

                // Update title
                const title = newItem.querySelector('.production-item-title');
                title.textContent = `Production #${itemIndex + 1}`;

                // Product name input (no auto-fill)
                const productNameInput = newItem.querySelector('.product-name-input');
                const totalPiecesInput = newItem.querySelector('.total-pieces-input');

                // No max limit for pieces input - allow any number of pieces

                // Add event listeners
                setupProductionItemEvents(newItem);

                // Set initial piece size display
                if (currentStockIssued) {
                    const sqftPerPiece = parseFloat(currentStockIssued.sqft_issued) / currentStockIssued.quantity_issued;
                    const pieceSizeDisplay = newItem.querySelector('.piece-size-display');
                    if (pieceSizeDisplay) {
                        pieceSizeDisplay.textContent = sqftPerPiece.toFixed(2);
                    }
                }

                itemsContainer.appendChild(newItem);
                noItemsMessage.classList.add('hidden');
                itemIndex++;
            }

            function setupProductionItemEvents(item) {
                const removeBtn = item.querySelector('.remove-production-item');
                const productNameInput = item.querySelector('.product-name-input');
                const totalPiecesInput = item.querySelector('.total-pieces-input');
                const totalSqftInput = item.querySelector('.total-sqft-input');
                const warningDiv = item.querySelector('.product-matching-warning');

                // Remove item
                removeBtn.addEventListener('click', function() {
                    item.remove();
                    updateItemTitles();
                    updateRemainingStock();
                    if (itemsContainer.children.length === 0) {
                        noItemsMessage.classList.remove('hidden');
                }
            });

                // Auto-calculate sqft based on pieces with stock validation
                totalPiecesInput.addEventListener('input', function() {
                    if (currentStockIssued && this.value) {
                        // Store current value for next validation
                        this.dataset.previousValue = this.value;

                        const totalPieces = parseInt(this.value);
                        let sqftPerPiece;
                        let calculatedSqft;

                        if (totalPieces > currentStockIssued.quantity_issued) {
                            // If pieces > issued pieces: divide issued sqft by total pieces
                            sqftPerPiece = parseFloat(currentStockIssued.sqft_issued) / totalPieces;
                            calculatedSqft = parseFloat(currentStockIssued.sqft_issued); // Total sqft = issued sqft
                        } else {
                            // If pieces <= issued pieces: use original calculation
                            sqftPerPiece = parseFloat(currentStockIssued.sqft_issued) / currentStockIssued.quantity_issued;
                            calculatedSqft = totalPieces * sqftPerPiece;
                        }

                        totalSqftInput.value = calculatedSqft.toFixed(2);

                        // Update piece size display
                        const pieceSizeDisplay = item.querySelector('.piece-size-display');
                        if (pieceSizeDisplay) {
                            pieceSizeDisplay.textContent = sqftPerPiece.toFixed(2);
                        }
                    }
                    updateRemainingStock();
                });

                // Allow any number of pieces (no max limit)
                // Pieces can be more than issued stock since smaller pieces can be produced

                // Update remaining stock when sqft changes
                totalSqftInput.addEventListener('input', function() {
                    updateRemainingStock();
                });

                // Check for product matching
                function checkProductMatching() {
                    const productName = productNameInput.value;
                    const size = item.querySelector('input[name*="[size]"]').value;
                    const diameter = item.querySelector('input[name*="[diameter]"]').value;
                    const conditionStatus = item.querySelector('select[name*="[condition_status]"]').value;
                    const specialStatus = item.querySelector('input[name*="[special_status]"]').value;

                    if (productName && conditionStatus) {
                        // Check if similar product exists in other items
                        const otherItems = itemsContainer.querySelectorAll('.production-item');
                        let hasMatch = false;

                        otherItems.forEach(otherItem => {
                            if (otherItem !== item) {
                                const otherProductName = otherItem.querySelector('.product-name-input').value;
                                const otherSize = otherItem.querySelector('input[name*="[size]"]').value;
                                const otherDiameter = otherItem.querySelector('input[name*="[diameter]"]').value;
                                const otherConditionStatus = otherItem.querySelector('select[name*="[condition_status]"]').value;
                                const otherSpecialStatus = otherItem.querySelector('input[name*="[special_status]"]').value;

                                if (otherProductName === productName &&
                                    otherSize === size &&
                                    otherDiameter === diameter &&
                                    otherConditionStatus === conditionStatus &&
                                    otherSpecialStatus === specialStatus) {
                                    hasMatch = true;
                                }
                            }
                        });

                        if (hasMatch) {
                            warningDiv.classList.remove('hidden');
                        } else {
                            warningDiv.classList.add('hidden');
                        }
                    } else {
                        warningDiv.classList.add('hidden');
                    }
                }

                // Add event listeners for product matching
                productNameInput.addEventListener('input', checkProductMatching);
                item.querySelector('input[name*="[size]"]').addEventListener('input', checkProductMatching);
                item.querySelector('input[name*="[diameter]"]').addEventListener('input', checkProductMatching);
                item.querySelector('select[name*="[condition_status]"]').addEventListener('change', checkProductMatching);
                item.querySelector('input[name*="[special_status]"]').addEventListener('input', checkProductMatching);
            }

            function updateItemTitles() {
                const items = itemsContainer.querySelectorAll('.production-item');
                items.forEach((item, index) => {
                    const title = item.querySelector('.production-item-title');
                    title.textContent = `Production #${index + 1}`;
                });
            }

            // Form validation
            productionForm.addEventListener('submit', function(e) {
                const items = itemsContainer.querySelectorAll('.production-item');
                if (items.length === 0) {
                    e.preventDefault();
                    alert('Please add at least one production item.');
                    return;
                }

                // Ensure stock is selected
                const selectedStockId = stockSelect.value;
                if (!selectedStockId) {
                    e.preventDefault();
                    alert('Please select a stock issued for production.');
                    return;
                }

                // Get current stock issued data
                const stockIssued = stockIssuedData[selectedStockId];
                if (!stockIssued) {
                    e.preventDefault();
                    alert('Invalid stock selection. Please refresh the page and try again.');
                    return;
                }

                // Calculate total pieces (no limit check - pieces can be more than issued stock)
                let totalPieces = 0;
                items.forEach(item => {
                    const pieces = parseInt(item.querySelector('.total-pieces-input').value) || 0;
                    totalPieces += pieces;
                });

                // Validate total sqft matches issued sqft
                let totalSqft = 0;
                items.forEach(item => {
                    const sqft = parseFloat(item.querySelector('.total-sqft-input').value) || 0;
                    totalSqft += sqft;
                });

                const sqftDifference = Math.abs(totalSqft - parseFloat(stockIssued.sqft_issued));
                if (sqftDifference > 0.01) {
                    e.preventDefault();
                    alert(`Total production sqft (${totalSqft.toFixed(2)}) must equal issued sqft (${parseFloat(stockIssued.sqft_issued).toFixed(2)}). The block size must be divided among all products.`);
                    return;
                }

                // Validate individual item quantities
                let hasInvalidItems = false;
                items.forEach((item, index) => {
                    const pieces = parseInt(item.querySelector('.total-pieces-input').value) || 0;
                    if (pieces <= 0) {
                        e.preventDefault();
                        alert(`Production item #${index + 1} must have at least 1 piece.`);
                        hasInvalidItems = true;
                        return;
                    }
                });

                if (hasInvalidItems) {
                    return;
                }
            });
        });
    </script>
</x-app-layout>
