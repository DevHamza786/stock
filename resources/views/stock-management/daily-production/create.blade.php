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
                                <label for="stock_issued_search" class="block text-sm font-medium text-gray-700 mb-2">Select Stock Issued for Production</label>
                                <div class="relative">
                                    <input type="text" id="stock_issued_search" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('stock_issued_id') border-red-500 @enderror" placeholder="Search stock issued for production..." autocomplete="off">
                                    <input type="hidden" id="stock_issued_id" name="stock_issued_id" value="{{ old('stock_issued_id') }}" required>
                                    <div id="stock_issued_dropdown" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                        <div class="p-2 text-gray-500 text-sm">Choose stock issued for production...</div>
                                        @if(isset($availableStockIssued) && $availableStockIssued->count() > 0)
                                            @foreach($availableStockIssued as $issued)
                                                <div class="stock-issued-option px-3 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0"
                                                     data-value="{{ $issued->id }}"
                                                     data-text="{{ $issued->stockAddition->pid ?? 'N/A' }} - {{ $issued->stockAddition->product->name ?? 'N/A' }} - {{ $issued->stockAddition->mineVendor->name ?? 'N/A' }} - {{ ucfirst($issued->stockAddition->condition_status) }}@if(in_array(strtolower($issued->stockAddition->condition_status), ['block', 'monuments'])) - Weight: {{ number_format($issued->stockAddition->weight, 2) }} kg @else - Size: {{ $issued->stockAddition->length }} × {{ $issued->stockAddition->height }} cm @endif - {{ $issued->quantity_issued }} pieces issued ({{ $issued->date->format('M d, Y') }})"
                                                     data-product="{{ $issued->stockAddition->product->name ?? 'N/A' }}"
                                                     data-vendor="{{ $issued->stockAddition->mineVendor->name ?? 'N/A' }}"
                                                     data-condition="{{ $issued->stockAddition->condition_status }}"
                                                     data-quantity="{{ $issued->quantity_issued }}"
                                                     data-sqft="{{ $issued->sqft_issued }}"
                                                     data-date="{{ $issued->date->format('M d, Y') }}"
                                                     data-stone="{{ $issued->stone }}"
                                                     data-machine="{{ $issued->machine?->name }}"
                                                     data-operator="{{ $issued->operator?->name }}"
                                                     data-pid="{{ $issued->stockAddition->pid ?? 'N/A' }}">
                                                    <div class="font-medium text-gray-900">
                                                        <span class="text-blue-600 font-mono text-sm">{{ $issued->stockAddition->pid ?? 'N/A' }}</span> - 
                                                        {{ $issued->stockAddition->product->name ?? 'N/A' }} - {{ $issued->stockAddition->mineVendor->name ?? 'N/A' }}
                                                    </div>
                                                    <div class="text-sm text-gray-600">
                                                        {{ ucfirst($issued->stockAddition->condition_status) }} -
                                                        @if(in_array(strtolower($issued->stockAddition->condition_status), ['block', 'monuments']))
                                                            Weight: {{ number_format($issued->stockAddition->weight, 2) }} kg
                                                        @else
                                                            Size: {{ $issued->stockAddition->length }} × {{ $issued->stockAddition->height }} cm
                                                        @endif
                                                    </div>
                                                    <div class="text-xs text-gray-500">{{ $issued->quantity_issued }} pieces issued ({{ $issued->date->format('M d, Y') }})</div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="p-3 text-gray-500 text-sm">No available stock issued for production</div>
                                        @endif
                                    </div>
                                </div>
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

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Production Status</label>
                                <select id="status" name="status" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('status') border-red-500 @enderror" required>
                                    <option value="open" {{ old('status', 'open') == 'open' ? 'selected' : '' }}>Open</option>
                                    <option value="close" {{ old('status') == 'close' ? 'selected' : '' }}>Close</option>
                                </select>
                                @error('status')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 mt-1">Open: Production is ongoing | Close: Production is completed</p>
                            </div>

                            <!-- Notes -->
                                <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                                <textarea id="notes" name="notes" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('notes') border-red-500 @enderror" placeholder="Add any additional notes...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                </div>

                                <!-- Particulars -->
                                <div>
                                    <label for="stone" class="block text-sm font-medium text-gray-700 mb-2">Particulars (Auto-filled)</label>
                                    <input type="text" id="stone" name="stone" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('stone') border-red-500 @enderror" placeholder="Will be auto-filled from stock addition..." readonly>
                                    @error('stone')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                    <p class="text-xs text-gray-500 mt-1">This field will be automatically filled from the selected stock issued</p>
                                </div>
                            </div>
                        </div>

                        <!-- Stock Info Display -->
                        <div id="stock-info" class="mt-6 p-4 bg-blue-50 rounded-lg hidden">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Selected Stock Issued Information</h3>
                            <div class="grid grid-cols-2 md:grid-cols-6 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-gray-700">PID:</span>
                                    <span id="selected-pid" class="text-blue-600 font-mono text-sm"></span>
                                </div>
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
                                <div id="issued-measurement-container">
                                    <span class="font-medium text-gray-700" id="issued-measurement-label">Issued Sqft:</span>
                                    <span id="available-measurement" class="text-gray-900"></span>
                                </div>
                                <div id="per-piece-measurement-container">
                                    <span class="font-medium text-gray-700" id="per-piece-measurement-label">Sqft per Piece:</span>
                                    <span id="measurement-per-piece" class="text-gray-900"></span>
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
                                            <span class="font-medium text-gray-700" id="remaining-measurement-label">Remaining Sqft:</span>
                                            <span id="remaining-measurement" class="text-lg font-semibold text-blue-600">0</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                            <div id="measurement-progress" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 100%"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 p-2 bg-gray-50 rounded text-sm">
                                    <div class="flex justify-between">
                                        <span id="total-production-measurement-label">Total Production Sqft:</span>
                                        <span id="total-production-measurement" class="font-medium">0.00</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span id="issued-measurement-summary-label">Issued Sqft:</span>
                                        <span id="issued-measurement-summary" class="font-medium">0.00</span>
                                    </div>
                                    <div class="flex justify-between border-t pt-1 mt-1">
                                        <span>Difference:</span>
                                        <span id="measurement-difference" class="font-medium">0.00</span>
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

                <!-- Condition Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Condition Status</label>
                    <select name="items[INDEX][condition_status]" class="condition-status-select block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" required onchange="updateProductionItemFields(this.closest('.production-item'), this.value)">
                        <option value="">Select condition...</option>
                    </select>
                </div>

                <!-- Size/Weight Field (conditional based on condition status) -->
                <div id="size-weight-container">
                    <label class="block text-sm font-medium text-gray-700 mb-2" id="size-weight-label">Size (cm) - e.g., 60*90, H*L</label>
                    <input type="text" name="items[INDEX][size]" class="size-input block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="e.g., 21*60" oninput="calculatePieceSize(this)">
                    <input type="number" name="items[INDEX][weight]" class="weight-input block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent hidden" placeholder="e.g., 150.5" step="0.01" min="0" oninput="calculatePieceSize(this)">
                    <p class="text-xs text-gray-500 mt-1" id="size-weight-help">Enter size in cm (height × length)</p>
                </div>

                <!-- Diameter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Diameter (W)</label>
                    <input type="text" name="items[INDEX][diameter]" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="e.g., 6cm, 2cm">
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

                <!-- Total Measurement (Sqft/Weight) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2" id="total-measurement-label">Total Sqft</label>
                    <input type="number" name="items[INDEX][total_sqft]" class="total-sqft-input block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" step="0.01" min="0" required readonly>
                    <p class="text-xs text-gray-500 mt-1" id="total-measurement-help">Auto-calculated from size and pieces</p>
                </div>
                
                <!-- Total Weight Field (for block/monuments) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2" id="total-weight-label">Total Weight (kg)</label>
                    <input type="number" name="items[INDEX][total_weight]" class="total-weight-input block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent hidden" step="0.01" min="0" required readonly>
                    <p class="text-xs text-gray-500 mt-1" id="total-weight-help">Auto-calculated from weight and pieces</p>
                </div>

                <!-- Piece Size Display -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2" id="piece-size-label">Piece Size</label>
                    <div class="block w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700">
                        <span class="piece-size-display">0.00</span> <span id="piece-size-unit">sqft</span> per piece
                    </div>
                    <p class="text-xs text-gray-500 mt-1" id="piece-size-help">Size of each individual piece</p>
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
            const stockSearch = document.getElementById('stock_issued_search');
            const stockSelect = document.getElementById('stock_issued_id');
            const stockDropdown = document.getElementById('stock_issued_dropdown');
            const stockInfo = document.getElementById('stock-info');
            const selectedProduct = document.getElementById('selected-product');
            const selectedVendor = document.getElementById('selected-vendor');
            const availablePieces = document.getElementById('available-pieces');
            const availableMeasurement = document.getElementById('available-measurement');
            const measurementPerPiece = document.getElementById('measurement-per-piece');
            const addItemBtn = document.getElementById('add-production-item');
            const itemsContainer = document.getElementById('production-items-container');
            const noItemsMessage = document.getElementById('no-items-message');
            const productionForm = document.getElementById('production-form');
            const remainingPieces = document.getElementById('remaining-pieces');
            const stockProgress = document.getElementById('stock-progress');
            const remainingMeasurement = document.getElementById('remaining-measurement');
            const measurementProgress = document.getElementById('measurement-progress');
            const totalProductionMeasurement = document.getElementById('total-production-measurement');
            const issuedMeasurementSummary = document.getElementById('issued-measurement-summary');
            const measurementDifference = document.getElementById('measurement-difference');

            let itemIndex = 0;
            let stockIssuedData = @json(isset($availableStockIssued) ? $availableStockIssued->keyBy('id') : []);
            let currentStockIssued = null;
            
            // Condition statuses data
            const conditionStatuses = @json($conditionStatuses);

            // Function to populate condition status dropdown
            function populateConditionStatusSelect(selectElement, selectedValue = '') {
                console.log('Populating condition status dropdown with selected value:', selectedValue);
                
                // Clear existing options
                selectElement.innerHTML = '<option value="">Select condition...</option>';
                
                // Add condition status options
                conditionStatuses.forEach(status => {
                    const option = document.createElement('option');
                    option.value = status.name;
                    option.textContent = status.name;
                    selectElement.appendChild(option);
                });
                
                // Set the selected value AFTER all options are added
                if (selectedValue) {
                    selectElement.value = selectedValue;
                    console.log('Set condition status to:', selectedValue, 'Current value:', selectElement.value);
                }
            }

            // Initialize search field if there's an old value
            @if(old('stock_issued_id'))
                const oldValue = '{{ old("stock_issued_id") }}';
                if (stockIssuedData[oldValue]) {
                    const stockIssued = stockIssuedData[oldValue];
                    const displayText = `${stockIssued.stock_addition.product.name} - ${stockIssued.stock_addition.mine_vendor.name} - ${stockIssued.stock_addition.condition_status} - ${stockIssued.quantity_issued} pieces issued (${stockIssued.date})`;
                    stockSearch.value = displayText;
                    stockSelect.value = oldValue;
                    handleStockSelection(oldValue);
                }
            @endif

            // Search functionality
            stockSearch.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const options = stockDropdown.querySelectorAll('.stock-issued-option');

                options.forEach(option => {
                    const text = option.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        option.style.display = 'block';
                    } else {
                        option.style.display = 'none';
                    }
                });

                stockDropdown.classList.remove('hidden');
            });

            // Show dropdown on focus
            stockSearch.addEventListener('focus', function() {
                stockDropdown.classList.remove('hidden');
            });

            // Hide dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!stockSearch.contains(e.target) && !stockDropdown.contains(e.target)) {
                    stockDropdown.classList.add('hidden');
                }
            });

            // Handle option selection
            stockDropdown.addEventListener('click', function(e) {
                const option = e.target.closest('.stock-issued-option');
                if (option) {
                    const value = option.dataset.value;
                    const text = option.dataset.text;

                    stockSelect.value = value;
                    stockSearch.value = text;
                    stockDropdown.classList.add('hidden');

                    // Trigger the stock selection logic
                    handleStockSelection(value);
                }
            });

            // Stock selection handler
            function handleStockSelection(selectedId) {
                if (selectedId && stockIssuedData[selectedId]) {
                    currentStockIssued = stockIssuedData[selectedId];
                    const stockAddition = currentStockIssued.stock_addition;
                    
                    // Debug logging
                    console.log('Selected Stock Issued:', currentStockIssued);
                    console.log('Machine:', currentStockIssued.machine);
                    console.log('Operator:', currentStockIssued.operator);

                    // Update PID display
                    const selectedPid = document.getElementById('selected-pid');
                    if (selectedPid) {
                        selectedPid.textContent = stockAddition.pid || 'N/A';
                    }
                    
                    selectedProduct.textContent = stockAddition.product.name;
                    selectedVendor.textContent = stockAddition.mine_vendor.name;
                    availablePieces.textContent = currentStockIssued.quantity_issued;
                    
                    // Check if condition status is block or monuments
                    const isBlockOrMonuments = stockAddition.condition_status && 
                        (stockAddition.condition_status.toLowerCase() === 'block' || 
                         stockAddition.condition_status.toLowerCase() === 'monuments');
                    
                    // Update labels and values based on condition status
                    if (isBlockOrMonuments) {
                        // Show weight information for block/monuments
                        document.getElementById('issued-measurement-label').textContent = 'Issued Weight:';
                        document.getElementById('per-piece-measurement-label').textContent = 'Weight per Piece:';
                        document.getElementById('remaining-measurement-label').textContent = 'Remaining Weight:';
                        document.getElementById('total-production-measurement-label').textContent = 'Total Production Weight:';
                        document.getElementById('issued-measurement-summary-label').textContent = 'Issued Weight:';
                        
                        document.getElementById('available-measurement').textContent = parseFloat(currentStockIssued.weight_issued).toFixed(2) + ' kg';
                        document.getElementById('issued-measurement-summary').textContent = parseFloat(currentStockIssued.weight_issued).toFixed(2) + ' kg';
                        
                        // Calculate and display weight per piece
                        const weightPerPieceValue = parseFloat(currentStockIssued.weight_issued) / currentStockIssued.quantity_issued;
                        document.getElementById('measurement-per-piece').textContent = weightPerPieceValue.toFixed(2) + ' kg';
                    } else {
                        // Show sqft information for other conditions
                        document.getElementById('issued-measurement-label').textContent = 'Issued Sqft:';
                        document.getElementById('per-piece-measurement-label').textContent = 'Sqft per Piece:';
                        document.getElementById('remaining-measurement-label').textContent = 'Remaining Sqft:';
                        document.getElementById('total-production-measurement-label').textContent = 'Total Production Sqft:';
                        document.getElementById('issued-measurement-summary-label').textContent = 'Issued Sqft:';
                        
                        document.getElementById('available-measurement').textContent = parseFloat(currentStockIssued.sqft_issued).toFixed(2) + ' sqft';
                        document.getElementById('issued-measurement-summary').textContent = parseFloat(currentStockIssued.sqft_issued).toFixed(2) + ' sqft';
                        
                        // Calculate and display sqft per piece
                        const sqftPerPieceValue = parseFloat(currentStockIssued.sqft_issued) / currentStockIssued.quantity_issued;
                        document.getElementById('measurement-per-piece').textContent = sqftPerPieceValue.toFixed(2) + ' sqft';
                    }

                    // Auto-fill stone field
                    const stoneField = document.getElementById('stone');
                    if (stoneField) {
                        stoneField.value = currentStockIssued.stone || stockAddition.stone || '';
                    }

                    stockInfo.classList.remove('hidden');

                    // Auto-fill machine and operator fields
                    const machineSelect = document.getElementById('machine_name');
                    const operatorSelect = document.getElementById('operator_name');

                    if (currentStockIssued.machine?.name && machineSelect) {
                        // Find and select the machine option
                        const machineOptions = machineSelect.querySelectorAll('option');
                        let machineFound = false;
                        machineOptions.forEach(option => {
                            if (option.value === currentStockIssued.machine.name) {
                                option.selected = true;
                                machineFound = true;
                            }
                        });

                        // If machine not found in options, add it as a new option
                        if (!machineFound && currentStockIssued.machine.name) {
                            const newOption = document.createElement('option');
                            newOption.value = currentStockIssued.machine.name;
                            newOption.textContent = currentStockIssued.machine.name;
                            newOption.selected = true;
                            machineSelect.appendChild(newOption);
                        }
                    }

                    if (currentStockIssued.operator?.name && operatorSelect) {
                        // Find and select the operator option
                        const operatorOptions = operatorSelect.querySelectorAll('option');
                        let operatorFound = false;
                        operatorOptions.forEach(option => {
                            if (option.value === currentStockIssued.operator.name) {
                                option.selected = true;
                                operatorFound = true;
                            }
                        });

                        // If operator not found in options, add it as a new option
                        if (!operatorFound && currentStockIssued.operator.name) {
                            const newOption = document.createElement('option');
                            newOption.value = currentStockIssued.operator.name;
                            newOption.textContent = currentStockIssued.operator.name;
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
            }

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
                let totalUsedMeasurement = 0;

                items.forEach(item => {
                    const pieces = parseInt(item.querySelector('.total-pieces-input').value) || 0;
                    const measurement = parseFloat(item.querySelector('.total-measurement-input').value) || 0;
                    totalUsedPieces += pieces;
                    totalUsedMeasurement += measurement;
                });

                // Update pieces display based on condition status
                const stockAddition = currentStockIssued.stock_addition;
                const isBlock = stockAddition.condition_status && 
                    (stockAddition.condition_status.toLowerCase() === 'block' || 
                     stockAddition.condition_status.toLowerCase() === 'monuments');
                
                if (isBlock) {
                    // For blocks: pieces can be more than issued (smaller blocks from bigger ones)
                    const piecesDifference = totalUsedPieces - currentStockIssued.quantity_issued;
                    
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
                    
                    const piecesPercentage = Math.min(100, (currentStockIssued.quantity_issued / totalUsedPieces) * 100);
                    stockProgress.style.width = piecesPercentage + '%';
                } else {
                    // For slabs: pieces should not exceed issued pieces (size constraint)
                    const remainingPieces = currentStockIssued.quantity_issued - totalUsedPieces;
                    
                    if (remainingPieces < 0) {
                        // Exceeded issued pieces - this should not happen for slabs
                        document.getElementById('remaining-pieces').textContent = `${remainingPieces} (Exceeded!)`;
                        stockProgress.className = 'bg-red-600 h-2 rounded-full transition-all duration-300';
                        document.getElementById('remaining-pieces').className = 'text-lg font-semibold text-red-600';
                    } else if (remainingPieces === 0) {
                        document.getElementById('remaining-pieces').textContent = '0';
                        stockProgress.className = 'bg-yellow-600 h-2 rounded-full transition-all duration-300';
                        document.getElementById('remaining-pieces').className = 'text-lg font-semibold text-yellow-600';
                    } else {
                        document.getElementById('remaining-pieces').textContent = remainingPieces;
                        stockProgress.className = 'bg-green-600 h-2 rounded-full transition-all duration-300';
                        document.getElementById('remaining-pieces').className = 'text-lg font-semibold text-green-600';
                    }
                    
                    const piecesPercentage = (totalUsedPieces / currentStockIssued.quantity_issued) * 100;
                    stockProgress.style.width = Math.min(100, piecesPercentage) + '%';
                }

                // Update measurement display (weight or sqft)
                let issuedMeasurement, remainingMeasurement, measurementPercentage;
                
                if (isBlock) {
                    // Handle weight for blocks
                    issuedMeasurement = parseFloat(currentStockIssued.weight_issued);
                    remainingMeasurement = issuedMeasurement - totalUsedMeasurement;
                    measurementPercentage = (remainingMeasurement / issuedMeasurement) * 100;
                    
                    document.getElementById('remaining-measurement').textContent = remainingMeasurement.toFixed(2);
                } else {
                    // Handle sqft for slabs and other conditions
                    issuedMeasurement = parseFloat(currentStockIssued.sqft_issued);
                    remainingMeasurement = issuedMeasurement - totalUsedMeasurement;
                    measurementPercentage = (remainingMeasurement / issuedMeasurement) * 100;
                    
                    document.getElementById('remaining-measurement').textContent = remainingMeasurement.toFixed(2);
                }

                const measurementProgress = document.getElementById('measurement-progress');
                measurementProgress.style.width = measurementPercentage + '%';

                // Update summary
                document.getElementById('total-production-measurement').textContent = totalUsedMeasurement.toFixed(2);
                const difference = totalUsedMeasurement - issuedMeasurement;
                document.getElementById('measurement-difference').textContent = difference.toFixed(2);

                // Change color based on measurement difference
                if (Math.abs(difference) <= 0.01) {
                    document.getElementById('measurement-difference').className = 'font-medium text-green-600';
                    document.getElementById('remaining-measurement').className = 'text-lg font-semibold text-green-600';
                    measurementProgress.className = 'bg-green-600 h-2 rounded-full transition-all duration-300';
                } else if (Math.abs(difference) <= issuedMeasurement * 0.1) {
                    document.getElementById('measurement-difference').className = 'font-medium text-yellow-600';
                    document.getElementById('remaining-measurement').className = 'text-lg font-semibold text-yellow-600';
                    measurementProgress.className = 'bg-yellow-600 h-2 rounded-full transition-all duration-300';
                } else {
                    document.getElementById('measurement-difference').className = 'font-medium text-red-600';
                    document.getElementById('remaining-measurement').className = 'text-lg font-semibold text-red-600';
                    measurementProgress.className = 'bg-red-600 h-2 rounded-full transition-all duration-300';
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

                // Populate condition status dropdown
                const conditionStatusSelect = newItem.querySelector('.condition-status-select');
                if (conditionStatusSelect) {
                    if (currentStockIssued) {
                        // Set condition status based on stock
                        const stockAddition = currentStockIssued.stock_addition;
                        const defaultConditionStatus = stockAddition.condition_status || '';
                        populateConditionStatusSelect(conditionStatusSelect, defaultConditionStatus);
                        
                        // Set initial field visibility based on stock condition status
                        updateProductionItemFields(newItem, defaultConditionStatus);
                    } else {
                        // Just populate without setting value
                        populateConditionStatusSelect(conditionStatusSelect);
                    }
                }

                // Product name input (no auto-fill)
                const productNameInput = newItem.querySelector('.product-name-input');
                const totalPiecesInput = newItem.querySelector('.total-pieces-input');

                // No max limit for pieces input - allow any number of pieces

                // Add event listeners
                setupProductionItemEvents(newItem);

                // Set initial piece size display
                if (currentStockIssued) {
                    const stockAddition = currentStockIssued.stock_addition;
                    const isBlock = stockAddition.condition_status && 
                        (stockAddition.condition_status.toLowerCase() === 'block' || 
                         stockAddition.condition_status.toLowerCase() === 'monuments');
                    
                    const pieceSizeDisplay = newItem.querySelector('.piece-size-display');
                    if (pieceSizeDisplay) {
                        if (isBlock) {
                            const weightPerPiece = parseFloat(currentStockIssued.weight_issued) / currentStockIssued.quantity_issued;
                            pieceSizeDisplay.textContent = weightPerPiece.toFixed(2);
                        } else {
                            const sqftPerPiece = parseFloat(currentStockIssued.sqft_issued) / currentStockIssued.quantity_issued;
                            pieceSizeDisplay.textContent = sqftPerPiece.toFixed(2);
                        }
                    }
                }

                itemsContainer.appendChild(newItem);
                noItemsMessage.classList.add('hidden');
                itemIndex++;
            }

            // Function to update production item fields based on condition status
            function updateProductionItemFields(item, conditionStatus) {
                const isBlock = conditionStatus && (conditionStatus.toLowerCase() === 'block' || conditionStatus.toLowerCase() === 'monuments');
                const isSlab = conditionStatus && conditionStatus.toLowerCase() === 'slab';
                
                // Update labels
                const totalSqftLabel = item.querySelector('#total-measurement-label');
                const totalWeightLabel = item.querySelector('#total-weight-label');
                const pieceSizeLabel = item.querySelector('#piece-size-label');
                const totalSqftHelp = item.querySelector('#total-measurement-help');
                const totalWeightHelp = item.querySelector('#total-weight-help');
                const pieceSizeHelp = item.querySelector('#piece-size-help');
                const pieceSizeUnit = item.querySelector('#piece-size-unit');
                
                // Update measurement fields visibility
                const totalSqftInput = item.querySelector('.total-sqft-input');
                const totalWeightInput = item.querySelector('.total-weight-input');
                
                // Update size/weight field
                const sizeWeightLabel = item.querySelector('#size-weight-label');
                const sizeWeightHelp = item.querySelector('#size-weight-help');
                const sizeInput = item.querySelector('.size-input');
                const weightInput = item.querySelector('.weight-input');
                
                if (isBlock) {
                    // Update for weight (block/monuments)
                    if (totalWeightLabel) totalWeightLabel.textContent = 'Total Weight (kg)';
                    if (pieceSizeLabel) pieceSizeLabel.textContent = 'Piece Weight';
                    if (totalWeightHelp) totalWeightHelp.textContent = 'Auto-calculated from weight and pieces';
                    if (pieceSizeHelp) pieceSizeHelp.textContent = 'Weight of each individual piece';
                    if (pieceSizeUnit) pieceSizeUnit.textContent = 'kg';
                    
                    // Show weight field, hide sqft field
                    if (totalSqftInput) totalSqftInput.classList.add('hidden');
                    if (totalWeightInput) totalWeightInput.classList.remove('hidden');
                    
                    // Switch to weight input field
                    if (sizeWeightLabel) sizeWeightLabel.textContent = 'Weight (kg)';
                    if (sizeWeightHelp) sizeWeightHelp.textContent = 'Enter weight in kg';
                    if (sizeInput) sizeInput.classList.add('hidden');
                    if (weightInput) weightInput.classList.remove('hidden');
                } else {
                    // Update for sqft (slabs and other conditions)
                    if (totalSqftLabel) totalSqftLabel.textContent = 'Total Sqft';
                    if (pieceSizeLabel) pieceSizeLabel.textContent = 'Piece Size';
                    if (totalSqftHelp) totalSqftHelp.textContent = 'Auto-calculated from size and pieces';
                    if (pieceSizeHelp) pieceSizeHelp.textContent = 'Size of each individual piece';
                    if (pieceSizeUnit) pieceSizeUnit.textContent = 'sqft';
                    
                    // Show sqft field, hide weight field
                    if (totalSqftInput) totalSqftInput.classList.remove('hidden');
                    if (totalWeightInput) totalWeightInput.classList.add('hidden');
                    
                    // Switch to size input field
                    if (sizeWeightLabel) sizeWeightLabel.textContent = 'Size (cm) - e.g., 60*90, H*L';
                    if (sizeWeightHelp) sizeWeightHelp.textContent = 'Enter size in cm (height × length)';
                    if (sizeInput) sizeInput.classList.remove('hidden');
                    if (weightInput) weightInput.classList.add('hidden');
                }
                
                // Clear existing values when switching fields
                if (sizeInput) sizeInput.value = '';
                if (weightInput) weightInput.value = '';
                
                // Recalculate piece size if there's existing data
                const totalPiecesInput = item.querySelector('.total-pieces-input');
                if (totalPiecesInput && totalPiecesInput.value) {
                    const activeInput = isBlock ? weightInput : sizeInput;
                    if (activeInput) {
                        calculatePieceSize(activeInput);
                    }
                }
            }

            function setupProductionItemEvents(item) {
                const removeBtn = item.querySelector('.remove-production-item');
                const productNameInput = item.querySelector('.product-name-input');
                const totalPiecesInput = item.querySelector('.total-pieces-input');
                const totalSqftInput = item.querySelector('.total-sqft-input');
                const totalWeightInput = item.querySelector('.total-weight-input');
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

                // Auto-calculate measurement based on pieces
                totalPiecesInput.addEventListener('input', function() {
                    if (this.value) {
                        const totalPieces = parseInt(this.value);
                        const conditionStatusSelect = item.querySelector('select[name*="[condition_status]"]');
                        const conditionStatus = conditionStatusSelect ? conditionStatusSelect.value : '';
                        const isBlock = conditionStatus && (conditionStatus.toLowerCase() === 'block' || conditionStatus.toLowerCase() === 'monuments');
                        
                        // Store current value for next validation
                        this.dataset.previousValue = this.value;

                        let measurementPerPiece;
                        let calculatedMeasurement;

                        if (isBlock) {
                            // Handle weight calculations for blocks
                            const weightInput = item.querySelector('.weight-input');
                            const weight = parseFloat(weightInput.value) || 0;
                            
                            if (weight > 0) {
                                measurementPerPiece = weight;
                                calculatedMeasurement = weight * totalPieces;
                                
                                // Validate against issued weight for block products
                                if (currentStockIssued) {
                                    const issuedWeight = parseFloat(currentStockIssued.weight_issued);
                                    if (calculatedMeasurement > issuedWeight) {
                                        const maxPieces = Math.floor(issuedWeight / weight);
                                        alert(`Total weight (${calculatedMeasurement.toFixed(2)} kg) cannot exceed issued weight (${issuedWeight.toFixed(2)} kg). Maximum pieces allowed: ${maxPieces}`);
                                        this.value = this.dataset.previousValue || '';
                                        return;
                                    }
                                }
                            } else {
                                calculatedMeasurement = 0;
                                measurementPerPiece = 0;
                            }
                        } else {
                            // Handle sqft calculations for slabs
                            const sizeInput = item.querySelector('.size-input');
                            const size = sizeInput.value.trim();
                            
                            if (size) {
                                const sizeMatch = size.match(/(\d+(?:\.\d+)?)\s*[×*x]\s*(\d+(?:\.\d+)?)/i);
                                if (sizeMatch) {
                                    const height = parseFloat(sizeMatch[1]);
                                    const length = parseFloat(sizeMatch[2]);
                                    const areaCm = height * length;
                                    measurementPerPiece = areaCm / 929.0304; // Convert to sqft
                                    calculatedMeasurement = measurementPerPiece * totalPieces;
                                } else {
                                    calculatedMeasurement = 0;
                                    measurementPerPiece = 0;
                                }
                            } else {
                                calculatedMeasurement = 0;
                                measurementPerPiece = 0;
                            }
                        }

                        // Update the correct measurement field based on product type
                        if (isBlock) {
                            const totalWeightInput = item.querySelector('.total-weight-input');
                            if (totalWeightInput) totalWeightInput.value = calculatedMeasurement.toFixed(2);
                        } else {
                            const totalSqftInput = item.querySelector('.total-sqft-input');
                            if (totalSqftInput) totalSqftInput.value = calculatedMeasurement.toFixed(2);
                        }

                        // Update piece size display
                        const pieceSizeDisplay = item.querySelector('.piece-size-display');
                        if (pieceSizeDisplay) {
                            pieceSizeDisplay.textContent = measurementPerPiece.toFixed(4);
                        }

                        // Update total summary
                        updateTotalSummary();
                    }
                    
                    // Stock validation (only if stock is selected)
                    if (currentStockIssued && this.value) {
                        const totalPieces = parseInt(this.value);
                        const stockAddition = currentStockIssued.stock_addition;
                        const isBlock = stockAddition.condition_status && 
                            (stockAddition.condition_status.toLowerCase() === 'block' || 
                             stockAddition.condition_status.toLowerCase() === 'monuments');
                        
                        if (!isBlock) {
                            // For sqft products: validate total sqft doesn't exceed issued sqft
                            const sizeInput = item.querySelector('.size-input');
                            const size = sizeInput.value.trim();
                            
                            if (size) {
                                const sizeMatch = size.match(/(\d+(?:\.\d+)?)\s*[×*x]\s*(\d+(?:\.\d+)?)/i);
                                if (sizeMatch) {
                                    const height = parseFloat(sizeMatch[1]);
                                    const length = parseFloat(sizeMatch[2]);
                                    const areaCm = height * length;
                                    const perPieceSqft = areaCm / 929.0304;
                                    const totalSqft = perPieceSqft * totalPieces;
                                    const issuedSqft = parseFloat(currentStockIssued.sqft_issued);
                                    
                                    if (totalSqft > issuedSqft) {
                                        const maxPieces = Math.floor(issuedSqft / perPieceSqft);
                                        alert(`Total sqft (${totalSqft.toFixed(2)}) cannot exceed issued sqft (${issuedSqft.toFixed(2)}). Maximum pieces allowed: ${maxPieces}`);
                                        this.value = this.dataset.previousValue || '';
                                        return;
                                    }
                                }
                            }
                        }
                    }
                    
                    updateRemainingStock();
                });

                // Allow any number of pieces (no max limit)
                // Pieces can be more than issued stock since smaller pieces can be produced

                // Update remaining stock when measurement changes
                if (totalSqftInput) {
                    totalSqftInput.addEventListener('input', function() {
                        updateRemainingStock();
                    });
                }
                
                if (totalWeightInput) {
                    totalWeightInput.addEventListener('input', function() {
                        updateRemainingStock();
                    });
                }

                // Check for product matching
                function checkProductMatching() {
                    const productName = productNameInput.value;
                    const size = item.querySelector('input[name*="[size]"]').value;
                    const weight = item.querySelector('input[name*="[weight]"]').value;
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
                                const otherWeight = otherItem.querySelector('input[name*="[weight]"]').value;
                                const otherDiameter = otherItem.querySelector('input[name*="[diameter]"]').value;
                                const otherConditionStatus = otherItem.querySelector('select[name*="[condition_status]"]').value;
                                const otherSpecialStatus = otherItem.querySelector('input[name*="[special_status]"]').value;

                                if (otherProductName === productName &&
                                    otherSize === size &&
                                    otherWeight === weight &&
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
                
                // Add event listeners with previous value tracking
                const sizeInput = item.querySelector('input[name*="[size]"]');
                const weightInput = item.querySelector('input[name*="[weight]"]');
                const diameterInput = item.querySelector('input[name*="[diameter]"]');
                
                sizeInput.addEventListener('input', function() {
                    this.dataset.previousValue = this.value;
                    checkProductMatching();
                });
                
                weightInput.addEventListener('input', function() {
                    this.dataset.previousValue = this.value;
                    checkProductMatching();
                });
                
                diameterInput.addEventListener('input', function() {
                    this.dataset.previousValue = this.value;
                    checkProductMatching();
                });
                
                // Condition status change handler
                const conditionStatusSelect = item.querySelector('select[name*="[condition_status]"]');
                conditionStatusSelect.addEventListener('change', function() {
                    checkProductMatching();
                    updateProductionItemFields(item, this.value);
                });
                
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

                // Validate total measurement does not exceed issued measurement
                let totalSqft = 0;
                let totalWeight = 0;
                items.forEach(item => {
                    const sqftInput = item.querySelector('.total-sqft-input');
                    const weightInput = item.querySelector('.total-weight-input');
                    const conditionStatusSelect = item.querySelector('select[name*="[condition_status]"]');
                    
                    const conditionStatus = conditionStatusSelect ? conditionStatusSelect.value : '';
                    const isBlock = conditionStatus && (conditionStatus.toLowerCase() === 'block' || conditionStatus.toLowerCase() === 'monuments');
                    
                    if (isBlock) {
                        if (weightInput) {
                            totalWeight += parseFloat(weightInput.value) || 0;
                        }
                    } else {
                        if (sqftInput) {
                            totalSqft += parseFloat(sqftInput.value) || 0;
                        }
                    }
                });

                // Check if this is a block/monuments or sqft-based product
                const stockAddition = stockIssued.stock_addition;
                const isBlockOrMonuments = stockAddition.condition_status && 
                    (stockAddition.condition_status.toLowerCase() === 'block' || 
                     stockAddition.condition_status.toLowerCase() === 'monuments');

                if (isBlockOrMonuments) {
                    // For block/monuments: validate against issued weight
                    const issuedWeight = parseFloat(stockIssued.weight_issued);
                    if (totalWeight > issuedWeight) {
                        e.preventDefault();
                        alert(`Total production weight (${totalWeight.toFixed(2)} kg) cannot exceed issued weight (${issuedWeight.toFixed(2)} kg). Please reduce production quantities.`);
                        return;
                    }
                } else {
                    // For slabs/other products: validate against issued sqft
                    const issuedSqft = parseFloat(stockIssued.sqft_issued);
                    if (totalSqft > issuedSqft) {
                        e.preventDefault();
                        alert(`Total production sqft (${totalSqft.toFixed(2)}) cannot exceed issued sqft (${issuedSqft.toFixed(2)}). Please reduce production quantities.`);
                        return;
                    }
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

        // Function to calculate piece size from cm to sqft
        function calculatePieceSize(input) {
            const item = input.closest('.production-item');
            const sizeInput = item.querySelector('.size-input');
            const weightInput = item.querySelector('.weight-input');
            const totalPiecesInput = item.querySelector('.total-pieces-input');
            const totalMeasurementInput = item.querySelector('.total-measurement-input');
            const pieceSizeDisplay = item.querySelector('.piece-size-display');
            const conditionStatusSelect = item.querySelector('select[name*="[condition_status]"]');
            
            const totalPieces = parseInt(totalPiecesInput.value) || 1; // Default to 1 if not entered
            const conditionStatus = conditionStatusSelect ? conditionStatusSelect.value : '';
            
            // Check if condition status is block
            const isBlock = conditionStatus && conditionStatus.toLowerCase() === 'block';

            if (isBlock) {
                // Handle weight calculation for blocks
                const weight = parseFloat(weightInput.value) || 0;
                
                if (weight > 0) {
                    // For blocks: total weight = weight per piece * total pieces
                    const totalWeight = weight * totalPieces;
                    const perPieceWeight = weight;

                    // Update the fields
                    const totalWeightInput = item.querySelector('.total-weight-input');
                    if (totalWeightInput) totalWeightInput.value = totalWeight.toFixed(2);
                    pieceSizeDisplay.textContent = perPieceWeight.toFixed(2);

                    // Update total summary
                    updateTotalSummary();

                    // Check if total exceeds issued weight
                    checkSqftLimit();
                    
                    // Validate against issued weight for block products
                    if (currentStockIssued && totalPieces > 0) {
                        const issuedWeight = parseFloat(currentStockIssued.weight_issued);
                        if (totalWeight > issuedWeight) {
                            const maxPieces = Math.floor(issuedWeight / weight);
                            alert(`Total weight (${totalWeight.toFixed(2)} kg) cannot exceed issued weight (${issuedWeight.toFixed(2)} kg). Maximum pieces allowed: ${maxPieces}`);
                            // Reset to previous valid state
                            weightInput.value = weightInput.dataset.previousValue || '';
                            calculatePieceSize(weightInput);
                            return;
                        }
                    }
                } else {
                    // Clear calculations if weight is empty
                    const totalWeightInput = item.querySelector('.total-weight-input');
                    if (totalWeightInput) totalWeightInput.value = '';
                    pieceSizeDisplay.textContent = '0.0000';
                }
            } else {
                // Handle sqft calculation for slabs and other conditions
                const size = sizeInput.value.trim();
                
                if (size) {
                    // Parse size (e.g., "21*60" or "21×60") for sqft calculation
                    const sizeMatch = size.match(/(\d+(?:\.\d+)?)\s*[×*x]\s*(\d+(?:\.\d+)?)/i);

                    if (sizeMatch) {
                        const height = parseFloat(sizeMatch[1]);
                        const length = parseFloat(sizeMatch[2]);

                        // Convert cm² to sqft (1 cm² = 1/929.0304 sqft)
                        const areaCm = height * length;
                        const perPieceSqft = areaCm / 929.0304;

                        // For slabs: total sqft = per piece sqft * total pieces
                        const totalSqft = perPieceSqft * totalPieces;

                        // Update the fields
                        const totalSqftInput = item.querySelector('.total-sqft-input');
                        if (totalSqftInput) totalSqftInput.value = totalSqft.toFixed(2);
                        pieceSizeDisplay.textContent = perPieceSqft.toFixed(4);

                        // Update total summary
                        updateTotalSummary();

                        // Check if total exceeds issued sqft
                        checkSqftLimit();
                        
                        // Validate against issued sqft for sqft products
                        if (currentStockIssued && totalPieces > 0) {
                            const stockAddition = currentStockIssued.stock_addition;
                            const isBlock = stockAddition.condition_status && 
                                (stockAddition.condition_status.toLowerCase() === 'block' || 
                                 stockAddition.condition_status.toLowerCase() === 'monuments');
                            
                            if (!isBlock) {
                                const issuedSqft = parseFloat(currentStockIssued.sqft_issued);
                                if (totalSqft > issuedSqft) {
                                    const maxPieces = Math.floor(issuedSqft / perPieceSqft);
                                    alert(`Total sqft (${totalSqft.toFixed(2)}) cannot exceed issued sqft (${issuedSqft.toFixed(2)}). Maximum pieces allowed: ${maxPieces}`);
                                    // Reset to previous valid state
                                    sizeInput.value = sizeInput.dataset.previousValue || '';
                                    calculatePieceSize(sizeInput);
                                    return;
                                }
                            }
                        }
                    } else {
                        // Invalid size format
                        const totalSqftInput = item.querySelector('.total-sqft-input');
                        if (totalSqftInput) totalSqftInput.value = '';
                        pieceSizeDisplay.textContent = '0.0000';
                    }
                } else {
                    // Clear calculations if size is empty
                    const totalSqftInput = item.querySelector('.total-sqft-input');
                    if (totalSqftInput) totalSqftInput.value = '';
                    pieceSizeDisplay.textContent = '0.0000';

                    // Update total summary
                    updateTotalSummary();

                    // Check if total exceeds issued measurement
                    checkSqftLimit();
                }
            }
        }

        // Function to update total summary
        function updateTotalSummary() {
            const totalSummary = document.getElementById('total-summary');
            const totalSqftDisplay = document.getElementById('total-sqft-display');
            const totalPiecesDisplay = document.getElementById('total-pieces-display');

            let totalSqft = 0;
            let totalWeight = 0;
            let totalPieces = 0;
            let sqftItems = 0;
            let weightItems = 0;

            // Calculate totals from all production items
            const productionItems = document.querySelectorAll('.production-item');
            productionItems.forEach(item => {
                const sqftInput = item.querySelector('.total-sqft-input');
                const weightInput = item.querySelector('.total-weight-input');
                const piecesInput = item.querySelector('.total-pieces-input');
                const conditionStatusSelect = item.querySelector('select[name*="[condition_status]"]');
                
                const conditionStatus = conditionStatusSelect ? conditionStatusSelect.value : '';
                const isBlock = conditionStatus && (conditionStatus.toLowerCase() === 'block' || conditionStatus.toLowerCase() === 'monuments');

                if (piecesInput) {
                    const pieces = parseInt(piecesInput.value) || 0;
                    totalPieces += pieces;

                    if (isBlock) {
                        // For block/monuments: use weight input
                        if (weightInput) {
                            const weight = parseFloat(weightInput.value) || 0;
                            totalWeight += weight;
                            weightItems += pieces;
                        }
                    } else {
                        // For slabs/others: use sqft input
                        if (sqftInput) {
                            const sqft = parseFloat(sqftInput.value) || 0;
                            totalSqft += sqft;
                            sqftItems += pieces;
                        }
                    }
                }
            });

            // Update display with separate summaries
            if (totalWeight > 0 && totalSqft > 0) {
                // Both weight and sqft products
                totalSqftDisplay.textContent = `${totalSqft.toFixed(2)} sqft (${sqftItems} pieces) | ${totalWeight.toFixed(2)} kg (${weightItems} pieces)`;
            } else if (totalWeight > 0) {
                // Only weight products
                totalSqftDisplay.textContent = `${totalWeight.toFixed(2)} kg`;
            } else if (totalSqft > 0) {
                // Only sqft products
                totalSqftDisplay.textContent = `${totalSqft.toFixed(2)} sqft`;
            } else {
                totalSqftDisplay.textContent = '0.00';
            }
            
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
    </script>
</x-app-layout>
