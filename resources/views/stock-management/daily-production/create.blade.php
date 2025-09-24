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
                        <p class="mt-2 text-gray-600">Record daily production activities</p>
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
                    <form method="POST" action="{{ route('stock-management.daily-production.store') }}">
                        @csrf

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

                            <!-- Product -->
                            <div>
                                <label for="product" class="block text-sm font-medium text-gray-700 mb-2">Product</label>
                                <input type="text" id="product" name="product" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('product') border-red-500 @enderror" value="{{ old('product') }}" required>
                                @error('product')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Condition Status -->
                            <div>
                                <label for="condition_status" class="block text-sm font-medium text-gray-700 mb-2">Condition Status</label>
                                <select id="condition_status" name="condition_status" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('condition_status') border-red-500 @enderror" required>
                                    <option value="">Select condition...</option>
                                    <option value="Block" {{ old('condition_status') == 'Block' ? 'selected' : '' }}>Block</option>
                                    <option value="Slabs" {{ old('condition_status') == 'Slabs' ? 'selected' : '' }}>Slabs</option>
                                    <option value="Polished" {{ old('condition_status') == 'Polished' ? 'selected' : '' }}>Polished</option>
                                </select>
                                @error('condition_status')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Total Pieces -->
                            <div>
                                <label for="total_pieces" class="block text-sm font-medium text-gray-700 mb-2">Total Pieces Produced</label>
                                <input type="number" id="total_pieces" name="total_pieces" min="1" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('total_pieces') border-red-500 @enderror" value="{{ old('total_pieces') }}" required>
                                @error('total_pieces')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Total Sqft -->
                            <div>
                                <label for="total_sqft" class="block text-sm font-medium text-gray-700 mb-2">Total Square Feet</label>
                                <input type="number" id="total_sqft" name="total_sqft" step="0.01" min="0" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('total_sqft') border-red-500 @enderror" value="{{ old('total_sqft') }}" required>
                                @error('total_sqft')
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
                            <div class="md:col-span-2">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                                <textarea id="notes" name="notes" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('notes') border-red-500 @enderror" placeholder="Add any additional notes...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Stock Info Display -->
                        <div id="stock-info" class="mt-6 p-4 bg-gray-50 rounded-lg hidden">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Selected Stock Issued Information</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const stockSelect = document.getElementById('stock_issued_id');
            const stockInfo = document.getElementById('stock-info');
            const selectedProduct = document.getElementById('selected-product');
            const selectedVendor = document.getElementById('selected-vendor');
            const availablePieces = document.getElementById('available-pieces');
            const availableSqft = document.getElementById('available-sqft');
            const productInput = document.getElementById('product');
            const piecesInput = document.getElementById('total_pieces');
            const sqftInput = document.getElementById('total_sqft');

            // Stock issued data from the server
            const stockIssuedData = @json(isset($availableStockIssued) ? $availableStockIssued->keyBy('id') : []);

            stockSelect.addEventListener('change', function() {
                const selectedId = this.value;

                if (selectedId && stockIssuedData[selectedId]) {
                    const stockIssued = stockIssuedData[selectedId];
                    const stockAddition = stockIssued.stock_addition;

                    selectedProduct.textContent = stockAddition.product.name;
                    selectedVendor.textContent = stockAddition.mine_vendor.name;
                    availablePieces.textContent = stockIssued.quantity_issued;
                    availableSqft.textContent = stockIssued.sqft_issued;

                    stockInfo.classList.remove('hidden');

                    // Auto-fill product name
                    productInput.value = stockAddition.product.name;

                    // Auto-fill condition status if available
                    const conditionStatusSelect = document.getElementById('condition_status');
                    if (stockAddition.condition_status && conditionStatusSelect) {
                        // Try to find matching option
                        const options = conditionStatusSelect.querySelectorAll('option');
                        for (let option of options) {
                            if (option.value === stockAddition.condition_status) {
                                conditionStatusSelect.value = stockAddition.condition_status;
                                break;
                            }
                        }
                    }

                    // Auto-fill machine name and operator name from stock issued if available
                    const machineSelect = document.getElementById('machine_name');
                    const operatorSelect = document.getElementById('operator_name');

                    if (stockIssued.machine_name && !machineSelect.value) {
                        // Find and select the machine option
                        const machineOptions = machineSelect.querySelectorAll('option');
                        for (let option of machineOptions) {
                            if (option.value === stockIssued.machine_name) {
                                machineSelect.value = stockIssued.machine_name;
                                break;
                            }
                        }
                    }

                    if (stockIssued.operator_name && !operatorSelect.value) {
                        // Find and select the operator option
                        const operatorOptions = operatorSelect.querySelectorAll('option');
                        for (let option of operatorOptions) {
                            if (option.value === stockIssued.operator_name) {
                                operatorSelect.value = stockIssued.operator_name;
                                break;
                            }
                        }
                    }

                    // Set max values for inputs based on issued stock
                    piecesInput.max = stockIssued.quantity_issued;
                    sqftInput.max = stockIssued.sqft_issued;
                } else {
                    stockInfo.classList.add('hidden');
                }
            });

            // Auto-calculate sqft based on pieces
            piecesInput.addEventListener('input', function() {
                const selectedId = stockSelect.value;
                if (selectedId && stockIssuedData[selectedId]) {
                    const stockIssued = stockIssuedData[selectedId];
                    const piecesPerSqft = stockIssued.sqft_issued / stockIssued.quantity_issued;
                    const calculatedSqft = this.value * piecesPerSqft;
                    sqftInput.value = calculatedSqft.toFixed(2);
                }
            });
        });
    </script>
</x-app-layout>
