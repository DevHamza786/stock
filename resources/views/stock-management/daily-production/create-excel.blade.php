<x-app-layout>
    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Record Production (Excel View)</h1>
                <p class="mt-2 text-gray-600">Record multiple daily production entries in Excel-style table format</p>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="p-6">
                    @if($availableStockIssued->count() == 0)
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
                    <form method="POST" action="{{ route('stock-management.daily-production.store-multiple') }}" id="excel-form">
                        @csrf

                        <!-- Common Fields -->
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg border">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Production Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Date -->
                                <div>
                                    <x-input-label for="date" :value="__('Date')" />
                                    <x-text-input id="date" name="date" type="date" class="mt-1 block w-full" :value="old('date', now()->format('Y-m-d'))" required />
                                    <x-input-error :messages="$errors->get('date')" class="mt-2" />
                                </div>
                                
                                <!-- Machine -->
                                <div>
                                    <x-input-label for="machine_name" :value="__('Machine')" />
                                    <select id="machine_name" name="machine_name" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        <option value="">Select Machine</option>
                                        @foreach($machines as $machine)
                                            <option value="{{ $machine->name }}" {{ old('machine_name') == $machine->name ? 'selected' : '' }}>
                                                {{ $machine->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('machine_name')" class="mt-2" />
                                </div>

                                <!-- Operator -->
                                <div>
                                    <x-input-label for="operator_name" :value="__('Operator')" />
                                    <select id="operator_name" name="operator_name" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        <option value="">Select Operator</option>
                                        @foreach($operators as $operator)
                                            <option value="{{ $operator->name }}" {{ old('operator_name') == $operator->name ? 'selected' : '' }}>
                                                {{ $operator->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('operator_name')" class="mt-2" />
                                </div>

                                <!-- Status -->
                                <div>
                                    <x-input-label for="status" :value="__('Status')" />
                                    <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        <option value="open" {{ old('status', 'open') == 'open' ? 'selected' : '' }}>Open</option>
                                        <option value="close" {{ old('status') == 'close' ? 'selected' : '' }}>Close</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                                </div>

                                <!-- Notes -->
                                <div class="md:col-span-2">
                                    <x-input-label for="notes" :value="__('Notes')" />
                                    <x-text-input id="notes" name="notes" type="text" class="mt-1 block w-full" :value="old('notes')" placeholder="Production notes (optional)" />
                                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Excel-style Table -->
                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Production Items</h3>
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
                                <table class="min-w-full divide-y divide-gray-200" id="production-table">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-8">#</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40">Stock Issued</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Product Name</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Condition</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Size</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Diameter</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Pieces</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Sqft</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Weight (kg)</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Special Status</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200" id="production-table-body">
                                        <!-- Rows will be dynamically added here -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Summary Section -->
                            <div class="mt-4 p-4 bg-green-50 rounded-lg border border-green-200">
                                <h4 class="text-sm font-medium text-green-900 mb-2">Summary</h4>
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                                    <div>
                                        <span class="font-medium text-green-700">Total Rows:</span>
                                        <span id="total-rows" class="ml-2 text-green-600">0</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-green-700">Total Pieces:</span>
                                        <span id="total-pieces" class="ml-2 text-green-600">0</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-green-700">Total Sqft:</span>
                                        <span id="total-sqft" class="ml-2 text-green-600">0.00</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-green-700">Total Weight:</span>
                                        <span id="total-weight" class="ml-2 text-green-600">0.00 kg</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('stock-management.daily-production.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-6 rounded-lg mr-4 transition-colors duration-200">
                                Cancel
                            </a>
                            <x-primary-button class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors duration-200">
                                {{ __('Record All Production Items') }}
                            </x-primary-button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($availableStockIssued->count() > 0)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let rowCounter = 0;
            const stockIssued = @json($availableStockIssued);
            const conditionStatuses = @json($conditionStatuses);
            
            const tableBody = document.getElementById('production-table-body');
            const addRowBtn = document.getElementById('add-row');
            const clearAllBtn = document.getElementById('clear-all');
            
            // Add first row on page load
            addRow();
            
            // Add row functionality
            addRowBtn.addEventListener('click', addRow);
            clearAllBtn.addEventListener('click', clearAllRows);
            
            function addRow() {
                rowCounter++;
                const row = document.createElement('tr');
                row.className = 'production-row';
                row.dataset.rowIndex = rowCounter;
                
                row.innerHTML = `
                    <td class="px-3 py-2 text-sm text-gray-600">${rowCounter}</td>
                    <td class="px-3 py-2">
                        <select name="productions[${rowCounter}][stock_issued_id]" class="w-full text-xs border-gray-300 focus:border-green-500 focus:ring-green-500 rounded stock-issued-select" required>
                            <option value="">Select Stock Issued</option>
                            ${stockIssued.map(issued => `
                                <option value="${issued.id}" data-product="${issued.stock_addition?.product?.name || ''}" data-vendor="${issued.stock_addition?.mine_vendor?.name || ''}" data-condition="${issued.stock_addition?.condition_status || ''}">
                                    ${issued.stock_addition?.product?.name || 'N/A'} - ${issued.stock_addition?.mine_vendor?.name || 'N/A'} (${issued.pieces_issued} pcs)
                                </option>
                            `).join('')}
                        </select>
                    </td>
                    <td class="px-3 py-2">
                        <input type="text" name="productions[${rowCounter}][product_name]" class="w-full text-xs border-gray-300 focus:border-green-500 focus:ring-green-500 rounded product-name-input" placeholder="Product Name" required>
                    </td>
                    <td class="px-3 py-2">
                        <select name="productions[${rowCounter}][condition_status]" class="w-full text-xs border-gray-300 focus:border-green-500 focus:ring-green-500 rounded condition-select" required>
                            <option value="">Select Condition</option>
                            ${conditionStatuses.map(status => `<option value="${status.name}">${status.name}</option>`).join('')}
                        </select>
                    </td>
                    <td class="px-3 py-2">
                        <input type="text" name="productions[${rowCounter}][size]" class="w-full text-xs border-gray-300 focus:border-green-500 focus:ring-green-500 rounded size-input" placeholder="Size">
                    </td>
                    <td class="px-3 py-2">
                        <input type="text" name="productions[${rowCounter}][diameter]" class="w-full text-xs border-gray-300 focus:border-green-500 focus:ring-green-500 rounded diameter-input" placeholder="Diameter">
                    </td>
                    <td class="px-3 py-2">
                        <input type="number" name="productions[${rowCounter}][total_pieces]" class="w-full text-xs border-gray-300 focus:border-green-500 focus:ring-green-500 rounded pieces-input" min="1" placeholder="Pieces" required>
                    </td>
                    <td class="px-3 py-2">
                        <input type="number" name="productions[${rowCounter}][total_sqft]" class="w-full text-xs border-gray-300 focus:border-green-500 focus:ring-green-500 rounded sqft-input" step="0.0001" placeholder="Sqft" required>
                    </td>
                    <td class="px-3 py-2">
                        <input type="number" name="productions[${rowCounter}][total_weight]" class="w-full text-xs border-gray-300 focus:border-green-500 focus:ring-green-500 rounded weight-input" step="0.1" placeholder="Weight">
                    </td>
                    <td class="px-3 py-2">
                        <input type="text" name="productions[${rowCounter}][special_status]" class="w-full text-xs border-gray-300 focus:border-green-500 focus:ring-green-500 rounded special-status-input" placeholder="Special Status">
                    </td>
                    <td class="px-3 py-2 text-center">
                        <button type="button" class="remove-row text-red-600 hover:text-red-800 text-xs font-medium" ${rowCounter === 1 ? 'disabled' : ''}>
                            ${rowCounter === 1 ? '−' : '🗑️'}
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
                const stockIssuedSelect = row.querySelector('.stock-issued-select');
                const productNameInput = row.querySelector('.product-name-input');
                const conditionSelect = row.querySelector('.condition-select');
                const piecesInput = row.querySelector('.pieces-input');
                const sqftInput = row.querySelector('.sqft-input');
                const weightInput = row.querySelector('.weight-input');
                const removeBtn = row.querySelector('.remove-row');
                
                // Stock issued selection handler
                stockIssuedSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption.value) {
                        const productName = selectedOption.dataset.product;
                        const condition = selectedOption.dataset.condition;
                        
                        if (productName) {
                            productNameInput.value = productName;
                        }
                        if (condition) {
                            conditionSelect.value = condition;
                        }
                    }
                });
                
                // Input change handlers for summary
                [piecesInput, sqftInput, weightInput].forEach(input => {
                    input.addEventListener('input', updateSummary);
                });
                
                // Remove row handler
                removeBtn.addEventListener('click', function() {
                    if (rowCounter > 1) {
                        row.remove();
                        renumberRows();
                        updateSummary();
                    }
                });
            }
            
            function renumberRows() {
                const rows = tableBody.querySelectorAll('.production-row');
                rows.forEach((row, index) => {
                    const rowNumber = index + 1;
                    row.dataset.rowIndex = rowNumber;
                    row.querySelector('td:first-child').textContent = rowNumber;
                    
                    // Update input names
                    const inputs = row.querySelectorAll('input, select');
                    inputs.forEach(input => {
                        const name = input.getAttribute('name');
                        if (name) {
                            input.setAttribute('name', name.replace(/productions\[\d+\]/, `productions[${rowNumber}]`));
                        }
                    });
                    
                    // Update remove button
                    const removeBtn = row.querySelector('.remove-row');
                    if (rowNumber === 1) {
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
                const rows = tableBody.querySelectorAll('.production-row');
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
                        totalWeight += weight;
                    }
                });
                
                document.getElementById('total-rows').textContent = totalRows;
                document.getElementById('total-pieces').textContent = totalPieces;
                document.getElementById('total-sqft').textContent = totalSqft.toFixed(4);
                document.getElementById('total-weight').textContent = totalWeight.toFixed(2);
            }
            
            // Form submission handler
            document.getElementById('excel-form').addEventListener('submit', function(e) {
                const rows = tableBody.querySelectorAll('.production-row');
                let hasValidRows = false;
                
                rows.forEach(row => {
                    const stockIssuedSelect = row.querySelector('.stock-issued-select');
                    const productNameInput = row.querySelector('.product-name-input');
                    const conditionSelect = row.querySelector('.condition-select');
                    const piecesInput = row.querySelector('.pieces-input');
                    
                    if (stockIssuedSelect.value && productNameInput.value && conditionSelect.value && piecesInput.value) {
                        hasValidRows = true;
                    }
                });
                
                if (!hasValidRows) {
                    e.preventDefault();
                    alert('Please add at least one complete production item before submitting.');
                    return false;
                }
            });
        });
    </script>
    @endif
</x-app-layout>
