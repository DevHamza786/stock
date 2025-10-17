<x-app-layout>
    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Edit Stock (Excel View)</h1>
                <p class="mt-2 text-gray-600">Edit multiple stock entries in Excel-style table format</p>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="p-6">
                    <form method="POST" action="{{ route('stock-management.stock-additions.update-multiple') }}" id="excel-edit-form">
                        @csrf
                        @method('PUT')

                        <!-- Common Fields -->
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg border">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Common Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Date -->
                                <div>
                                    <x-input-label for="date" :value="__('Date')" />
                                    <x-text-input id="date" name="date" type="date" class="mt-1 block w-full" :value="old('date', $stockAdditions->first()->date->format('Y-m-d'))" required />
                                    <x-input-error :messages="$errors->get('date')" class="mt-2" />
                                </div>
                                
                                <!-- Mine Vendor -->
                                <div>
                                    <x-input-label for="mine_vendor_id" :value="__('Mine Vendor')" />
                                    <select id="mine_vendor_id" name="mine_vendor_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        <option value="">Select Vendor</option>
                                        @foreach($mineVendors as $vendor)
                                            <option value="{{ $vendor->id }}" {{ old('mine_vendor_id', $stockAdditions->first()->mine_vendor_id) == $vendor->id ? 'selected' : '' }}>
                                                {{ $vendor->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('mine_vendor_id')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Excel-style Table -->
                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Stock Items</h3>
                                <div class="flex gap-2">
                                    <button type="button" id="add-row" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Add Row
                                    </button>
                                    <button type="button" id="clear-all" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Clear All
                                    </button>
                                </div>
                            </div>

                            <!-- Excel Table -->
                            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200" id="stock-table">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-8">#</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Product</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Condition</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40">Particulars</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Length (cm)</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Height (cm)</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-28">Diameter/Thickness</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Weight (kg)</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Pieces</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Available Pieces</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Total Sqft</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200" id="stock-table-body">
                                        <!-- Rows will be dynamically added here -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Summary Section -->
                            <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                                <h4 class="text-sm font-medium text-blue-900 mb-2">Summary</h4>
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                                    <div>
                                        <span class="font-medium text-blue-700">Total Rows:</span>
                                        <span id="total-rows" class="ml-2 text-blue-600">0</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-blue-700">Total Pieces:</span>
                                        <span id="total-pieces" class="ml-2 text-blue-600">0</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-blue-700">Total Sqft:</span>
                                        <span id="total-sqft" class="ml-2 text-blue-600">0.00</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-blue-700">Total Weight:</span>
                                        <span id="total-weight" class="ml-2 text-blue-600">0.00 kg</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('stock-management.stock-additions.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-6 rounded-lg mr-4 transition-colors duration-200">
                                Cancel
                            </a>
                            <x-primary-button class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors duration-200">
                                {{ __('Update All Stock Items') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let rowCounter = 0;
            const products = @json($products);
            const conditionStatuses = @json($conditionStatuses);
            const stockAdditions = @json($stockAdditions);
            
            const tableBody = document.getElementById('stock-table-body');
            const addRowBtn = document.getElementById('add-row');
            const clearAllBtn = document.getElementById('clear-all');
            
            // Load existing stock additions
            stockAdditions.forEach(stock => {
                addRow(stock);
            });
            
            // If no existing stocks, add one empty row
            if (stockAdditions.length === 0) {
                addRow();
            }
            
            // Add row functionality
            addRowBtn.addEventListener('click', () => addRow());
            clearAllBtn.addEventListener('click', clearAllRows);
            
            function addRow(stockData = null) {
                rowCounter++;
                const row = document.createElement('tr');
                row.className = 'stock-row';
                row.dataset.rowIndex = rowCounter;
                
                const isEdit = stockData !== null;
                const stockId = isEdit ? stockData.id : '';
                const productId = isEdit ? stockData.product_id : '';
                const conditionStatus = isEdit ? stockData.condition_status : '';
                const stone = isEdit ? stockData.stone : '';
                const length = isEdit ? (stockData.length || '') : '';
                const height = isEdit ? (stockData.height || '') : '';
                const diameter = isEdit ? (stockData.diameter || '') : '';
                const weight = isEdit ? (stockData.weight || '') : '';
                const totalPieces = isEdit ? stockData.total_pieces : '';
                const availablePieces = isEdit ? stockData.available_pieces : '';
                const totalSqft = isEdit ? (stockData.total_sqft || '') : '';
                
                row.innerHTML = `
                    <td class="px-3 py-2 text-sm text-gray-600">${rowCounter}</td>
                    <td class="px-3 py-2">
                        <select name="stocks[${rowCounter}][product_id]" class="w-full text-xs border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded" required>
                            <option value="">Select Product</option>
                            ${products.map(product => `<option value="${product.id}" ${productId == product.id ? 'selected' : ''}>${product.name}</option>`).join('')}
                        </select>
                        <input type="hidden" name="stocks[${rowCounter}][id]" value="${stockId}">
                    </td>
                    <td class="px-3 py-2">
                        <select name="stocks[${rowCounter}][condition_status]" class="w-full text-xs border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded condition-select" required>
                            <option value="">Select Condition</option>
                            ${conditionStatuses.map(status => `<option value="${status.name}" ${conditionStatus === status.name ? 'selected' : ''}>${status.name}</option>`).join('')}
                        </select>
                    </td>
                    <td class="px-3 py-2">
                        <input type="text" name="stocks[${rowCounter}][stone]" class="w-full text-xs border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded" placeholder="Particulars" value="${stone}" required>
                    </td>
                    <td class="px-3 py-2">
                        <input type="number" name="stocks[${rowCounter}][length]" class="w-full text-xs border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded length-input" step="0.1" placeholder="cm" value="${length}">
                    </td>
                    <td class="px-3 py-2">
                        <input type="number" name="stocks[${rowCounter}][height]" class="w-full text-xs border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded height-input" step="0.1" placeholder="cm" value="${height}">
                    </td>
                    <td class="px-3 py-2">
                        <input type="text" name="stocks[${rowCounter}][diameter]" class="w-full text-xs border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded diameter-input" placeholder="e.g., 6cm" value="${diameter}">
                    </td>
                    <td class="px-3 py-2">
                        <input type="number" name="stocks[${rowCounter}][weight]" class="w-full text-xs border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded weight-input" step="0.1" placeholder="kg" value="${weight}">
                    </td>
                    <td class="px-3 py-2">
                        <input type="number" name="stocks[${rowCounter}][total_pieces]" class="w-full text-xs border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded pieces-input" min="1" placeholder="Pieces" value="${totalPieces}" required>
                    </td>
                    <td class="px-3 py-2">
                        <input type="number" name="stocks[${rowCounter}][available_pieces]" class="w-full text-xs border-gray-300 focus:border-green-500 focus:ring-green-500 rounded available-pieces-input bg-green-50" min="0" placeholder="Available" value="${availablePieces}" required>
                    </td>
                    <td class="px-3 py-2">
                        <input type="number" name="stocks[${rowCounter}][total_sqft]" class="w-full text-xs border-gray-300 bg-gray-100 rounded sqft-input" step="0.0001" value="${totalSqft}" readonly>
                    </td>
                    <td class="px-3 py-2 text-center">
                        <button type="button" class="remove-row text-red-600 hover:text-red-800 text-xs font-medium" ${rowCounter === 1 && stockAdditions.length <= 1 ? 'disabled' : ''}>
                            ${rowCounter === 1 && stockAdditions.length <= 1 ? '−' : '🗑️'}
                        </button>
                    </td>
                `;
                
                tableBody.appendChild(row);
                
                // Add event listeners to new row
                setupRowEventListeners(row);
                
                // Update summary
                updateSummary();
            }
            
            function setupRowEventListeners(row) {
                const conditionSelect = row.querySelector('.condition-select');
                const lengthInput = row.querySelector('.length-input');
                const heightInput = row.querySelector('.height-input');
                const diameterInput = row.querySelector('.diameter-input');
                const weightInput = row.querySelector('.weight-input');
                const piecesInput = row.querySelector('.pieces-input');
                const availablePiecesInput = row.querySelector('.available-pieces-input');
                const sqftInput = row.querySelector('.sqft-input');
                const removeBtn = row.querySelector('.remove-row');
                
                // Condition change handler
                conditionSelect.addEventListener('change', function() {
                    toggleRowFields(row);
                    calculateRowSqft(row);
                });
                
                // Input change handlers
                [lengthInput, heightInput, piecesInput].forEach(input => {
                    input.addEventListener('input', () => calculateRowSqft(row));
                });
                
                [weightInput, piecesInput].forEach(input => {
                    input.addEventListener('input', () => calculateRowSqft(row));
                });
                
                // Available pieces validation
                piecesInput.addEventListener('input', function() {
                    const maxPieces = parseInt(this.value) || 0;
                    availablePiecesInput.setAttribute('max', maxPieces);
                    if (parseInt(availablePiecesInput.value) > maxPieces) {
                        availablePiecesInput.value = maxPieces;
                    }
                });
                
                // Remove row handler
                removeBtn.addEventListener('click', function() {
                    if (rowCounter > 1 || stockAdditions.length > 1) {
                        row.remove();
                        renumberRows();
                        updateSummary();
                    }
                });
                
                // Initialize row fields
                toggleRowFields(row);
                calculateRowSqft(row);
            }
            
            function toggleRowFields(row) {
                const conditionSelect = row.querySelector('.condition-select');
                const lengthInput = row.querySelector('.length-input');
                const heightInput = row.querySelector('.height-input');
                const diameterInput = row.querySelector('.diameter-input');
                const weightInput = row.querySelector('.weight-input');
                
                const condition = conditionSelect.value.toLowerCase();
                const isBlock = condition === 'block' || condition === 'monuments';
                
                if (isBlock) {
                    // Hide dimension fields for block/monuments
                    lengthInput.style.display = 'none';
                    heightInput.style.display = 'none';
                    diameterInput.style.display = 'none';
                    weightInput.style.display = 'block';
                    weightInput.setAttribute('required', 'required');
                    lengthInput.removeAttribute('required');
                    heightInput.removeAttribute('required');
                } else {
                    // Show dimension fields for other conditions
                    lengthInput.style.display = 'block';
                    heightInput.style.display = 'block';
                    diameterInput.style.display = 'block';
                    weightInput.style.display = 'none';
                    lengthInput.setAttribute('required', 'required');
                    heightInput.setAttribute('required', 'required');
                    weightInput.removeAttribute('required');
                }
            }
            
            function calculateRowSqft(row) {
                const conditionSelect = row.querySelector('.condition-select');
                const lengthInput = row.querySelector('.length-input');
                const heightInput = row.querySelector('.height-input');
                const weightInput = row.querySelector('.weight-input');
                const piecesInput = row.querySelector('.pieces-input');
                const sqftInput = row.querySelector('.sqft-input');
                
                const condition = conditionSelect.value.toLowerCase();
                const length = parseFloat(lengthInput.value) || 0;
                const height = parseFloat(heightInput.value) || 0;
                const weight = parseFloat(weightInput.value) || 0;
                const pieces = parseInt(piecesInput.value) || 0;
                
                if (condition === 'block' || condition === 'monuments') {
                    // For block/monuments, set sqft to 0
                    sqftInput.value = '';
                } else {
                    // For other conditions, calculate sqft
                    if (length > 0 && height > 0 && pieces > 0) {
                        const cmToSqft = 0.00107639;
                        const singlePieceSizeCm = length * height;
                        const singlePieceSizeSqft = singlePieceSizeCm * cmToSqft;
                        const totalSqft = singlePieceSizeSqft * pieces;
                        sqftInput.value = totalSqft.toFixed(4);
                    } else {
                        sqftInput.value = '';
                    }
                }
                
                updateSummary();
            }
            
            function renumberRows() {
                const rows = tableBody.querySelectorAll('.stock-row');
                rows.forEach((row, index) => {
                    const rowNumber = index + 1;
                    row.dataset.rowIndex = rowNumber;
                    row.querySelector('td:first-child').textContent = rowNumber;
                    
                    // Update input names
                    const inputs = row.querySelectorAll('input, select');
                    inputs.forEach(input => {
                        const name = input.getAttribute('name');
                        if (name) {
                            input.setAttribute('name', name.replace(/stocks\[\d+\]/, `stocks[${rowNumber}]`));
                        }
                    });
                    
                    // Update remove button
                    const removeBtn = row.querySelector('.remove-row');
                    if (rowNumber === 1 && stockAdditions.length <= 1) {
                        removeBtn.disabled = true;
                        removeBtn.textContent = '−';
                    } else {
                        removeBtn.disabled = false;
                        removeBtn.textContent = '🗑️';
                    }
                });
                
                rowCounter = rows.length;
            }
            
            function clearAllRows() {
                if (confirm('Are you sure you want to clear all rows? This action cannot be undone.')) {
                    tableBody.innerHTML = '';
                    rowCounter = 0;
                    addRow();
                }
            }
            
            function updateSummary() {
                const rows = tableBody.querySelectorAll('.stock-row');
                let totalRows = 0;
                let totalPieces = 0;
                let totalSqft = 0;
                let totalWeight = 0;
                
                rows.forEach(row => {
                    const piecesInput = row.querySelector('.pieces-input');
                    const sqftInput = row.querySelector('.sqft-input');
                    const weightInput = row.querySelector('.weight-input');
                    const pieces = parseInt(piecesInput.value) || 0;
                    const sqft = parseFloat(sqftInput.value) || 0;
                    const weight = parseFloat(weightInput.value) || 0;
                    
                    if (pieces > 0) {
                        totalRows++;
                        totalPieces += pieces;
                        totalSqft += sqft;
                        totalWeight += (weight * pieces);
                    }
                });
                
                document.getElementById('total-rows').textContent = totalRows;
                document.getElementById('total-pieces').textContent = totalPieces;
                document.getElementById('total-sqft').textContent = totalSqft.toFixed(4);
                document.getElementById('total-weight').textContent = totalWeight.toFixed(2);
            }
            
            // Form submission handler
            document.getElementById('excel-edit-form').addEventListener('submit', function(e) {
                const rows = tableBody.querySelectorAll('.stock-row');
                let hasValidRows = false;
                
                rows.forEach(row => {
                    const productSelect = row.querySelector('select[name*="[product_id]"]');
                    const conditionSelect = row.querySelector('select[name*="[condition_status]"]');
                    const particularsInput = row.querySelector('input[name*="[stone]"]');
                    const piecesInput = row.querySelector('.pieces-input');
                    const availablePiecesInput = row.querySelector('.available-pieces-input');
                    
                    if (productSelect.value && conditionSelect.value && particularsInput.value && piecesInput.value && availablePiecesInput.value) {
                        hasValidRows = true;
                    }
                });
                
                if (!hasValidRows) {
                    e.preventDefault();
                    alert('Please add at least one complete stock item before submitting.');
                    return false;
                }
            });
        });
    </script>
</x-app-layout>
