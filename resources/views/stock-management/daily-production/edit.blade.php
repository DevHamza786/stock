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
                        <p class="mt-2 text-gray-600">Update daily production information with multiple items</p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="p-6">
                    <form method="POST" action="{{ route('stock-management.daily-production.update', $dailyProduction) }}" id="production-form">
                        @csrf
                        @method('PUT')

                        <!-- Current Production Items Summary -->
                        @if($dailyProduction->items && $dailyProduction->items->count() > 0)
                        <div class="bg-green-50 p-6 rounded-lg mb-6 border border-green-200">
                            <h3 class="text-lg font-semibold text-green-900 mb-4">Current Production Items</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($dailyProduction->items as $item)
                                <div class="bg-white p-4 rounded-lg border border-green-200">
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="font-medium text-gray-900">{{ $item->product_name }}</h4>
                                        <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">{{ $item->condition_status }}</span>
                                    </div>
                                    <div class="space-y-1 text-sm text-gray-600">
                                        @if($item->size)
                                        <div><span class="font-medium">Size:</span> {{ $item->size }} cm</div>
                                        @endif
                                        @if($item->diameter)
                                        <div><span class="font-medium">Diameter:</span> {{ $item->diameter }}</div>
                                        @endif
                                        <div><span class="font-medium">Pieces:</span> {{ $item->total_pieces }}</div>
                                        <div><span class="font-medium">Sqft:</span> {{ number_format($item->total_sqft, 2) }}</div>
                                        @if($item->size && $item->total_pieces > 0)
                                        <div><span class="font-medium">Per Piece:</span> {{ number_format($item->total_sqft / $item->total_pieces, 4) }} sqft</div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="mt-4 p-3 bg-green-100 rounded-lg">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium text-green-900">Total Production:</span>
                                    <div class="text-right">
                                        <div class="font-bold text-green-900">{{ $dailyProduction->items->sum('total_pieces') }} pieces</div>
                                        <div class="text-sm text-green-700">{{ number_format($dailyProduction->items->sum('total_sqft'), 2) }} sqft</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Basic Information Section -->
                        <div class="bg-gray-50 p-6 rounded-lg mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Production Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Stock Issued -->
                                <div class="md:col-span-2">
                                    <label for="stock_issued_id" class="block text-sm font-medium text-gray-700 mb-2">Stock Issued for Production</label>
                                    <select id="stock_issued_id" name="stock_issued_id" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('stock_issued_id') border-red-500 @enderror" required>
                                        <option value="">Choose stock issued for production...</option>
                                        @foreach($availableStockIssued as $issued)
                                            <option value="{{ $issued->id }}" {{ old('stock_issued_id', $dailyProduction->stock_issued_id) == $issued->id ? 'selected' : '' }}>
                                                {{ $issued->stockAddition->product->name }} - {{ $issued->stockAddition->mineVendor->name }} - 
                                                {{ ucfirst($issued->stockAddition->condition_status) }} - 
                                                @if(strtolower($issued->stockAddition->condition_status) === 'block')
                                                    Weight: {{ number_format($issued->stockAddition->weight, 2) }} kg - {{ $issued->quantity_issued }} pieces issued ({{ $issued->date->format('M d, Y') }})
                                                @else
                                                    Size: {{ $issued->stockAddition->length }} × {{ $issued->stockAddition->height }} cm - {{ $issued->quantity_issued }} pieces issued ({{ $issued->date->format('M d, Y') }})
                                                @endif
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
                                            <option value="{{ $machine->name }}" {{ old('machine_name', $dailyProduction->machine_name) == $machine->name ? 'selected' : '' }}>
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
                                            <option value="{{ $operator->name }}" {{ old('operator_name', $dailyProduction->operator_name) == $operator->name ? 'selected' : '' }}>
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
                                    <input type="date" id="date" name="date" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('date') border-red-500 @enderror" value="{{ old('date', $dailyProduction->date->format('Y-m-d')) }}" required>
                                    @error('date')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Status -->
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Production Status</label>
                                    <select id="status" name="status" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('status') border-red-500 @enderror" required>
                                        <option value="open" {{ old('status', $dailyProduction->status) == 'open' ? 'selected' : '' }}>Open</option>
                                        <option value="close" {{ old('status', $dailyProduction->status) == 'close' ? 'selected' : '' }}>Close</option>
                                    </select>
                                    @error('status')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                    <p class="text-xs text-gray-500 mt-1">Open: Production is ongoing | Close: Production is completed</p>
                                </div>

                                <!-- Notes -->
                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                                    <textarea id="notes" name="notes" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('notes') border-red-500 @enderror" placeholder="Add any additional notes...">{{ old('notes', $dailyProduction->notes) }}</textarea>
                                    @error('notes')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Stone -->
                                <div>
                                    <label for="stone" class="block text-sm font-medium text-gray-700 mb-2">Stone Type</label>
                                    <input type="text" id="stone" name="stone" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('stone') border-red-500 @enderror" value="{{ old('stone', $dailyProduction->stone) }}" readonly>
                                    @error('stone')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                    <p class="text-xs text-gray-500 mt-1">This field is automatically filled from the stock addition</p>
                                </div>
                            </div>
                        </div>

                        <!-- Stock Info Display -->
                        <div id="stock-info" class="mt-6 p-4 bg-blue-50 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Selected Stock Issued Information</h3>
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-gray-700">Product:</span>
                                    <span id="selected-product" class="text-gray-900">{{ $dailyProduction->stockAddition->product->name }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Vendor:</span>
                                    <span id="selected-vendor" class="text-gray-900">{{ $dailyProduction->stockAddition->mineVendor->name }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Issued Pieces:</span>
                                    <span id="available-pieces" class="text-gray-900">{{ $dailyProduction->stockIssued->quantity_issued ?? 0 }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Issued Sqft:</span>
                                    <span id="available-sqft" class="text-gray-900">{{ number_format($dailyProduction->stockIssued->sqft_issued ?? 0, 2) }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Sqft per Piece:</span>
                                    <span id="sqft-per-piece" class="text-gray-900">{{ $dailyProduction->stockIssued ? number_format($dailyProduction->stockIssued->sqft_issued / $dailyProduction->stockIssued->quantity_issued, 2) : '0.00' }}</span>
                                </div>
                            </div>

                            <!-- Status Bars -->
                            <div class="mt-4 space-y-3">
                                <!-- Pieces Status -->
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm font-medium text-gray-700">Pieces Status</span>
                                        <span id="pieces-status-value" class="text-sm font-semibold text-green-600">0</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div id="pieces-status-bar" class="bg-green-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Blue = More pieces than issued (smaller pieces)</p>
                                </div>

                                <!-- Remaining Sqft -->
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm font-medium text-gray-700">Remaining Sqft</span>
                                        <span id="remaining-sqft-value" class="text-sm font-semibold text-red-600">0.00</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div id="remaining-sqft-bar" class="bg-red-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Summary Table -->
                            <div class="mt-4 bg-white rounded-lg p-3 border border-gray-200">
                                <h4 class="text-sm font-semibold text-gray-800 mb-2">Production Summary</h4>
                                <div class="grid grid-cols-3 gap-4 text-sm">
                                    <div class="text-center">
                                        <div class="text-lg font-bold text-blue-600" id="total-production-sqft">0.00</div>
                                        <div class="text-xs text-gray-500">Total Production Sqft</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-lg font-bold text-gray-600" id="issued-sqft-display">0.00</div>
                                        <div class="text-xs text-gray-500">Issued Sqft</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-lg font-bold" id="difference-display">0.00</div>
                                        <div class="text-xs text-gray-500">Difference</div>
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
                                <!-- Production items will be loaded here -->
                            </div>

                            <div id="no-items-message" class="text-center py-8 text-gray-500 hidden">
                                <svg class="h-12 w-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                <p>No production items added yet. Click "Add Production Item" to start.</p>
                            </div>

                            <!-- Total Summary -->
                            <div id="total-summary" class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg hidden">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h3 class="text-lg font-semibold text-blue-900">Production Summary</h3>
                                        <p class="text-sm text-blue-700">Total production overview</p>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-2xl font-bold text-blue-900">
                                            <span id="total-sqft-display">0.00</span> sqft
                                        </div>
                                        <div class="text-sm text-blue-700">
                                            <span id="total-pieces-display">0</span> pieces
                                        </div>
                                    </div>
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
                    <label class="block text-sm font-medium text-gray-700 mb-2">Size (cm) - e.g., 60*90, H*L</label>
                    <input type="text" name="items[INDEX][size]" class="size-input block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="e.g., 21*60" oninput="calculatePieceSize(this)">
                    <p class="text-xs text-gray-500 mt-1">Enter size in cm (height × length)</p>
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
                    <input type="number" name="items[INDEX][total_pieces]" class="total-pieces-input block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" min="1" required oninput="calculatePieceSize(this)">
                    <p class="text-xs text-gray-500 mt-1">Number of pieces produced</p>
                </div>

                <!-- Total Sqft -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Total Sqft</label>
                    <input type="number" name="items[INDEX][total_sqft]" class="total-sqft-input block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" step="0.01" min="0" required readonly>
                    <p class="text-xs text-gray-500 mt-1">Auto-calculated from size and pieces</p>
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

            let itemIndex = 0;
            let stockIssuedData = @json(isset($availableStockIssued) ? $availableStockIssued->keyBy('id') : []);
            let currentStockIssued = null;

            // Load existing production items
            const existingItems = @json($dailyProduction->items);
            existingItems.forEach((item, index) => {
                addProductionItem(item, index);
            });

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
                    
                    // Auto-fill stone field
                    const stoneField = document.getElementById('stone');
                    if (stoneField) {
                        stoneField.value = currentStockIssued.stone || stockAddition.stone || '';
                    }

                    // Calculate and display sqft per piece
                    const sqftPerPieceValue = parseFloat(currentStockIssued.sqft_issued) / currentStockIssued.quantity_issued;
                    sqftPerPiece.textContent = sqftPerPieceValue.toFixed(2);

                    stockInfo.classList.remove('hidden');

                    // Auto-fill machine and operator fields
                    const machineSelect = document.getElementById('machine_name');
                    const operatorSelect = document.getElementById('operator_name');

                    if (currentStockIssued.machine_name && machineSelect) {
                        const machineOptions = machineSelect.querySelectorAll('option');
                        let machineFound = false;
                        machineOptions.forEach(option => {
                            if (option.value === currentStockIssued.machine_name) {
                                option.selected = true;
                                machineFound = true;
                            }
                        });

                        if (!machineFound && currentStockIssued.machine_name) {
                            const newOption = document.createElement('option');
                            newOption.value = currentStockIssued.machine_name;
                            newOption.textContent = currentStockIssued.machine_name;
                            newOption.selected = true;
                            machineSelect.appendChild(newOption);
                        }
                    }

                    if (currentStockIssued.operator_name && operatorSelect) {
                        const operatorOptions = operatorSelect.querySelectorAll('option');
                        let operatorFound = false;
                        operatorOptions.forEach(option => {
                            if (option.value === currentStockIssued.operator_name) {
                                option.selected = true;
                                operatorFound = true;
                            }
                        });

                        if (!operatorFound && currentStockIssued.operator_name) {
                            const newOption = document.createElement('option');
                            newOption.value = currentStockIssued.operator_name;
                            newOption.textContent = currentStockIssued.operator_name;
                            newOption.selected = true;
                            operatorSelect.appendChild(newOption);
                        }
                    }
                } else {
                    stockInfo.classList.add('hidden');
                    currentStockIssued = null;

                    // Clear machine and operator selections
                    const machineSelect = document.getElementById('machine_name');
                    const operatorSelect = document.getElementById('operator_name');

                    if (machineSelect) {
                        machineSelect.selectedIndex = 0;
                    }
                    if (operatorSelect) {
                        operatorSelect.selectedIndex = 0;
                    }
                }
            });

            // Add production item
            addItemBtn.addEventListener('click', function() {
                if (!currentStockIssued) {
                    alert('Please select a stock issued first.');
                    return;
                }
                addProductionItem();
            });

            function addProductionItem(existingItem = null, index = null) {
                const template = document.getElementById('production-item-template');
                const clone = template.content.cloneNode(true);

                // Update index placeholders
                const html = clone.querySelector('.production-item').outerHTML;
                const currentIndex = index !== null ? index : itemIndex;
                const updatedHtml = html.replace(/INDEX/g, currentIndex);

                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = updatedHtml;
                const newItem = tempDiv.firstElementChild;

                // Update title
                const title = newItem.querySelector('.production-item-title');
                title.textContent = `Production #${currentIndex + 1}`;

                // Fill with existing data if provided
                if (existingItem) {
                    newItem.querySelector('input[name*="[product_name]"]').value = existingItem.product_name || '';
                    newItem.querySelector('input[name*="[size]"]').value = existingItem.size || '';
                    newItem.querySelector('input[name*="[diameter]"]').value = existingItem.diameter || '';
                    newItem.querySelector('select[name*="[condition_status]"]').value = existingItem.condition_status || '';
                    newItem.querySelector('input[name*="[special_status]"]').value = existingItem.special_status || '';
                    newItem.querySelector('input[name*="[total_pieces]"]').value = existingItem.total_pieces || '';
                    newItem.querySelector('input[name*="[total_sqft]"]').value = existingItem.total_sqft || '';
                    newItem.querySelector('textarea[name*="[narration]"]').value = existingItem.narration || '';
                    
                    // Calculate and display piece size if size is provided
                    if (existingItem.size && existingItem.total_pieces) {
                        const sizeInput = newItem.querySelector('.size-input');
                        const totalPiecesInput = newItem.querySelector('.total-pieces-input');
                        const totalSqftInput = newItem.querySelector('.total-sqft-input');
                        const pieceSizeDisplay = newItem.querySelector('.piece-size-display');
                        
                        if (sizeInput && totalPiecesInput && totalSqftInput && pieceSizeDisplay) {
                            const size = existingItem.size.trim();
                            const totalPieces = parseInt(existingItem.total_pieces) || 1;
                            
                            const sizeMatch = size.match(/(\d+(?:\.\d+)?)\s*[×*x]\s*(\d+(?:\.\d+)?)/i);
                            if (sizeMatch) {
                                const height = parseFloat(sizeMatch[1]);
                                const length = parseFloat(sizeMatch[2]);
                                const areaCm = height * length;
                                const areaSqft = areaCm / 929.0304;
                                const perPieceSqft = areaSqft;
                                
                                pieceSizeDisplay.textContent = perPieceSqft.toFixed(4);
                            }
                        }
                    }
                }

                // Set initial piece size display
                if (currentStockIssued) {
                    const sqftPerPieceValue = parseFloat(currentStockIssued.sqft_issued) / currentStockIssued.quantity_issued;
                    const pieceSizeDisplay = newItem.querySelector('.piece-size-display');
                    if (pieceSizeDisplay) {
                        pieceSizeDisplay.textContent = sqftPerPieceValue.toFixed(2);
                    }
                }

                // Add event listeners
                setupProductionItemEvents(newItem);

                itemsContainer.appendChild(newItem);
                noItemsMessage.classList.add('hidden');

                if (index === null) {
                    itemIndex++;
                }
                
                // Update status bars and summary after adding item
                updateStatusBars();
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
                    if (itemsContainer.children.length === 0) {
                        noItemsMessage.classList.remove('hidden');
                    }
                    // Update status bars and summary after removing item
                    updateStatusBars();
                });

                // Auto-calculate sqft based on pieces
                totalPiecesInput.addEventListener('input', function() {
                    if (currentStockIssued && this.value) {
                        const totalPieces = parseInt(this.value);
                        let sqftPerPieceValue;
                        let calculatedSqft;

                        if (totalPieces > currentStockIssued.quantity_issued) {
                            // If pieces > issued pieces: divide issued sqft by total pieces
                            sqftPerPieceValue = parseFloat(currentStockIssued.sqft_issued) / totalPieces;
                            calculatedSqft = parseFloat(currentStockIssued.sqft_issued);
                        } else {
                            // If pieces <= issued pieces: use original calculation
                            sqftPerPieceValue = parseFloat(currentStockIssued.sqft_issued) / currentStockIssued.quantity_issued;
                            calculatedSqft = totalPieces * sqftPerPieceValue;
                        }

                        totalSqftInput.value = calculatedSqft.toFixed(2);

                        // Update piece size display
                        const pieceSizeDisplay = item.querySelector('.piece-size-display');
                        if (pieceSizeDisplay) {
                            pieceSizeDisplay.textContent = sqftPerPieceValue.toFixed(2);
                        }
                        
                        // Update status bars and summary
                        updateStatusBars();
                    }
                });

                // Update piece size display when sqft changes
                totalSqftInput.addEventListener('input', function() {
                    if (currentStockIssued && this.value && totalPiecesInput.value) {
                        const sqftPerPieceValue = parseFloat(this.value) / parseInt(totalPiecesInput.value);
                        const pieceSizeDisplay = item.querySelector('.piece-size-display');
                        if (pieceSizeDisplay) {
                            pieceSizeDisplay.textContent = sqftPerPieceValue.toFixed(2);
                        }
                        
                        // Update status bars and summary
                        updateStatusBars();
                    }
                });

                // Check for product matching
                function checkProductMatching() {
                    const productName = productNameInput.value;
                    const size = item.querySelector('input[name*="[size]"]').value;
                    const diameter = item.querySelector('input[name*="[diameter]"]').value;
                    const conditionStatus = item.querySelector('select[name*="[condition_status]"]').value;
                    const specialStatus = item.querySelector('input[name*="[special_status]"]').value;

                    if (productName && conditionStatus) {
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

                // Validate total sqft does not exceed issued sqft
                let totalSqft = 0;
                items.forEach(item => {
                    const sqft = parseFloat(item.querySelector('.total-sqft-input').value) || 0;
                    totalSqft += sqft;
                });

                const issuedSqft = parseFloat(stockIssued.sqft_issued);
                if (totalSqft > issuedSqft) {
                    e.preventDefault();
                    alert(`Total production sqft (${totalSqft.toFixed(2)}) cannot exceed issued sqft (${issuedSqft.toFixed(2)}). Please reduce production quantities.`);
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

            // Initialize stock info display on page load
            const currentStockId = stockSelect.value;
            if (currentStockId && stockIssuedData[currentStockId]) {
                const stock = stockIssuedData[currentStockId];
                selectedProduct.textContent = stock.stock_addition.product.name;
                selectedVendor.textContent = stock.stock_addition.mine_vendor.name;
                availablePieces.textContent = stock.quantity_issued;
                availableSqft.textContent = parseFloat(stock.sqft_issued).toFixed(2);

                const sqftPerPieceValue = parseFloat(stock.sqft_issued) / stock.quantity_issued;
                sqftPerPiece.textContent = sqftPerPieceValue.toFixed(2);

                stockInfo.classList.remove('hidden');
                currentStockIssued = stock;
                
                // Update status bars and summary
                updateStatusBars();
            }

            // Load existing production items on page load
            @if($dailyProduction->items && $dailyProduction->items->count() > 0)
                @foreach($dailyProduction->items as $index => $item)
                    addProductionItem({
                        product_name: '{{ $item->product_name }}',
                        size: '{{ $item->size }}',
                        diameter: '{{ $item->diameter }}',
                        condition_status: '{{ $item->condition_status }}',
                        special_status: '{{ $item->special_status }}',
                        total_pieces: {{ $item->total_pieces }},
                        total_sqft: {{ $item->total_sqft }},
                        narration: '{{ $item->narration }}'
                    }, {{ $index }});
                @endforeach
            @endif

            // Function to calculate piece size from cm to sqft
            function calculatePieceSize(input) {
                const item = input.closest('.production-item');
                const sizeInput = item.querySelector('.size-input');
                const totalPiecesInput = item.querySelector('.total-pieces-input');
                const totalSqftInput = item.querySelector('.total-sqft-input');
                const pieceSizeDisplay = item.querySelector('.piece-size-display');

                const size = sizeInput.value.trim();
                const totalPieces = parseInt(totalPiecesInput.value) || 1; // Default to 1 if not entered

                if (size) {
                    // Parse size (e.g., "21*60" or "21×60")
                    const sizeMatch = size.match(/(\d+(?:\.\d+)?)\s*[×*x]\s*(\d+(?:\.\d+)?)/i);
                    
                    if (sizeMatch) {
                        const height = parseFloat(sizeMatch[1]);
                        const length = parseFloat(sizeMatch[2]);
                        
                        // Convert cm² to sqft (1 cm² = 1/929.0304 sqft)
                        const areaCm = height * length;
                        const areaSqft = areaCm / 929.0304;
                        
                        // Calculate total sqft and per piece sqft
                        const totalSqft = areaSqft * totalPieces;
                        const perPieceSqft = areaSqft;
                        
                        // Update the fields
                        totalSqftInput.value = totalSqft.toFixed(2);
                        pieceSizeDisplay.textContent = perPieceSqft.toFixed(4);
                        
                        // Update total summary
                        updateTotalSummary();
                        
                        // Check if total exceeds issued sqft
                        checkSqftLimit();
                        
                        // Update status bars
                        updateStatusBars();
                    } else {
                        // Invalid size format
                        totalSqftInput.value = '';
                        pieceSizeDisplay.textContent = '0.0000';
                    }
                } else {
                    // Clear calculations if size is empty
                    totalSqftInput.value = '';
                    pieceSizeDisplay.textContent = '0.0000';
                    
                    // Update total summary
                    updateTotalSummary();
                    
                    // Check if total exceeds issued sqft
                    checkSqftLimit();
                    
                    // Update status bars
                    updateStatusBars();
                }
            }

            // Function to update total summary
            function updateTotalSummary() {
                const totalSummary = document.getElementById('total-summary');
                const totalSqftDisplay = document.getElementById('total-sqft-display');
                const totalPiecesDisplay = document.getElementById('total-pieces-display');
                
                let totalSqft = 0;
                let totalPieces = 0;
                
                // Calculate totals from all production items
                const productionItems = document.querySelectorAll('.production-item');
                productionItems.forEach(item => {
                    const sqftInput = item.querySelector('.total-sqft-input');
                    const piecesInput = item.querySelector('.total-pieces-input');
                    
                    if (sqftInput && piecesInput) {
                        const sqft = parseFloat(sqftInput.value) || 0;
                        const pieces = parseInt(piecesInput.value) || 0;
                        
                        totalSqft += sqft;
                        totalPieces += pieces;
                    }
                });
                
                // Update display
                totalSqftDisplay.textContent = totalSqft.toFixed(2);
                totalPiecesDisplay.textContent = totalPieces;
                
                // Show/hide summary based on whether there are items
                if (productionItems.length > 0) {
                    totalSummary.classList.remove('hidden');
                } else {
                    totalSummary.classList.add('hidden');
                }
            }

            // Function to check if total sqft exceeds issued sqft
            function checkSqftLimit() {
                const stockSelect = document.getElementById('stock_issued_id');
                if (!stockSelect.value) return;

                const stockIssued = stockIssuedData[stockSelect.value];
                if (!stockIssued) return;

                const issuedSqft = parseFloat(stockIssued.sqft_issued);
                const totalSqftDisplay = document.getElementById('total-sqft-display');
                const totalSummary = document.getElementById('total-summary');

                if (totalSqftDisplay && totalSummary) {
                    const currentTotal = parseFloat(totalSqftDisplay.textContent) || 0;
                    
                    if (currentTotal > issuedSqft) {
                        // Show warning by changing background color
                        totalSummary.classList.remove('bg-blue-50', 'border-blue-200');
                        totalSummary.classList.add('bg-red-50', 'border-red-200');
                        
                        // Update text colors
                        const title = totalSummary.querySelector('h3');
                        const subtitle = totalSummary.querySelector('p');
                        const sqftDisplay = totalSummary.querySelector('.text-2xl');
                        const piecesDisplay = totalSummary.querySelector('.text-sm');
                        
                        if (title) title.classList.remove('text-blue-900');
                        if (subtitle) subtitle.classList.remove('text-blue-700');
                        if (sqftDisplay) sqftDisplay.classList.remove('text-blue-900');
                        if (piecesDisplay) piecesDisplay.classList.remove('text-blue-700');
                        
                        if (title) title.classList.add('text-red-900');
                        if (subtitle) subtitle.classList.add('text-red-700');
                        if (sqftDisplay) sqftDisplay.classList.add('text-red-900');
                        if (piecesDisplay) piecesDisplay.classList.add('text-red-700');
                    } else {
                        // Reset to normal colors
                        totalSummary.classList.remove('bg-red-50', 'border-red-200');
                        totalSummary.classList.add('bg-blue-50', 'border-blue-200');
                        
                        // Reset text colors
                        const title = totalSummary.querySelector('h3');
                        const subtitle = totalSummary.querySelector('p');
                        const sqftDisplay = totalSummary.querySelector('.text-2xl');
                        const piecesDisplay = totalSummary.querySelector('.text-sm');
                        
                        if (title) title.classList.remove('text-red-900');
                        if (subtitle) subtitle.classList.remove('text-red-700');
                        if (sqftDisplay) sqftDisplay.classList.remove('text-red-900');
                        if (piecesDisplay) piecesDisplay.classList.remove('text-red-700');
                        
                        if (title) title.classList.add('text-blue-900');
                        if (subtitle) subtitle.classList.add('text-blue-700');
                        if (sqftDisplay) sqftDisplay.classList.add('text-blue-900');
                        if (piecesDisplay) piecesDisplay.classList.add('text-blue-700');
                    }
                }
            }
        }

        // Function to update status bars
        function updateStatusBars() {
            const stockSelect = document.getElementById('stock_issued_id');
            if (!stockSelect.value) return;

            const stockIssued = stockIssuedData[stockSelect.value];
            if (!stockIssued) return;

            const issuedPieces = parseInt(stockIssued.quantity_issued);
            const issuedSqft = parseFloat(stockIssued.sqft_issued);

            // Calculate total production pieces and sqft
            let totalPieces = 0;
            let totalSqft = 0;

            const productionItems = document.querySelectorAll('.production-item');
            productionItems.forEach(item => {
                const piecesInput = item.querySelector('.total-pieces-input');
                const sqftInput = item.querySelector('.total-sqft-input');

                if (piecesInput && sqftInput) {
                    const pieces = parseInt(piecesInput.value) || 0;
                    const sqft = parseFloat(sqftInput.value) || 0;

                    totalPieces += pieces;
                    totalSqft += sqft;
                }
            });

            // Update pieces status bar
            const piecesStatusValue = document.getElementById('pieces-status-value');
            const piecesStatusBar = document.getElementById('pieces-status-bar');
            
            if (piecesStatusValue && piecesStatusBar) {
                piecesStatusValue.textContent = totalPieces;
                
                if (totalPieces > issuedPieces) {
                    // More pieces than issued (smaller pieces)
                    piecesStatusValue.classList.remove('text-green-600', 'text-red-600');
                    piecesStatusValue.classList.add('text-blue-600');
                    piecesStatusBar.classList.remove('bg-green-500', 'bg-red-500');
                    piecesStatusBar.classList.add('bg-blue-500');
                    piecesStatusBar.style.width = '100%';
                } else if (totalPieces === issuedPieces) {
                    // Equal pieces
                    piecesStatusValue.classList.remove('text-green-600', 'text-blue-600');
                    piecesStatusValue.classList.add('text-green-600');
                    piecesStatusBar.classList.remove('bg-blue-500', 'bg-red-500');
                    piecesStatusBar.classList.add('bg-green-500');
                    piecesStatusBar.style.width = '100%';
                } else {
                    // Fewer pieces than issued
                    piecesStatusValue.classList.remove('text-green-600', 'text-blue-600');
                    piecesStatusValue.classList.add('text-red-600');
                    piecesStatusBar.classList.remove('bg-green-500', 'bg-blue-500');
                    piecesStatusBar.classList.add('bg-red-500');
                    const percentage = (totalPieces / issuedPieces) * 100;
                    piecesStatusBar.style.width = Math.min(percentage, 100) + '%';
                }
            }

            // Update remaining sqft bar
            const remainingSqft = issuedSqft - totalSqft;
            const remainingSqftValue = document.getElementById('remaining-sqft-value');
            const remainingSqftBar = document.getElementById('remaining-sqft-bar');
            
            if (remainingSqftValue && remainingSqftBar) {
                remainingSqftValue.textContent = remainingSqft.toFixed(2);
                
                if (remainingSqft > 0) {
                    // Still have remaining sqft
                    remainingSqftValue.classList.remove('text-green-600', 'text-blue-600');
                    remainingSqftValue.classList.add('text-red-600');
                    remainingSqftBar.classList.remove('bg-green-500', 'bg-blue-500');
                    remainingSqftBar.classList.add('bg-red-500');
                    const percentage = (remainingSqft / issuedSqft) * 100;
                    remainingSqftBar.style.width = Math.min(percentage, 100) + '%';
                } else if (remainingSqft === 0) {
                    // Exactly used all sqft
                    remainingSqftValue.classList.remove('text-red-600', 'text-blue-600');
                    remainingSqftValue.classList.add('text-green-600');
                    remainingSqftBar.classList.remove('bg-red-500', 'bg-blue-500');
                    remainingSqftBar.classList.add('bg-green-500');
                    remainingSqftBar.style.width = '0%';
                } else {
                    // Exceeded issued sqft
                    remainingSqftValue.classList.remove('text-green-600', 'text-red-600');
                    remainingSqftValue.classList.add('text-blue-600');
                    remainingSqftBar.classList.remove('bg-green-500', 'bg-red-500');
                    remainingSqftBar.classList.add('bg-blue-500');
                    remainingSqftBar.style.width = '100%';
                }
            }

            // Update summary table
            const totalProductionSqft = document.getElementById('total-production-sqft');
            const issuedSqftDisplay = document.getElementById('issued-sqft-display');
            const differenceDisplay = document.getElementById('difference-display');

            if (totalProductionSqft && issuedSqftDisplay && differenceDisplay) {
                totalProductionSqft.textContent = totalSqft.toFixed(2);
                issuedSqftDisplay.textContent = issuedSqft.toFixed(2);
                
                const difference = totalSqft - issuedSqft;
                differenceDisplay.textContent = difference.toFixed(2);
                
                if (difference > 0) {
                    // Exceeded issued sqft
                    differenceDisplay.classList.remove('text-green-600', 'text-red-600');
                    differenceDisplay.classList.add('text-blue-600');
                } else if (difference === 0) {
                    // Exactly matches issued sqft
                    differenceDisplay.classList.remove('text-red-600', 'text-blue-600');
                    differenceDisplay.classList.add('text-green-600');
                } else {
                    // Less than issued sqft
                    differenceDisplay.classList.remove('text-green-600', 'text-blue-600');
                    differenceDisplay.classList.add('text-red-600');
                }
            }
        }
        });
    </script>
</x-app-layout>
