<x-app-layout>
    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Create Gate Pass (Excel View)</h1>
                <p class="mt-2 text-gray-600">Create multiple gate pass entries in Excel-style table format</p>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="p-6">
                    @if($stockAdditions->count() == 0)
                        <div class="text-center py-12">
                            <svg class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Available Stock</h3>
                            <p class="text-gray-500 mb-4">There is no available stock to issue. Please add stock first.</p>
                            <a href="{{ route('stock-management.stock-additions.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                Add Stock
                            </a>
                        </div>
                    @else
                    <form method="POST" action="{{ route('stock-management.gate-pass.store-multiple') }}" id="excel-form">  
                        @csrf

                        <!-- Common Fields -->
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg border">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Gate Pass Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Date -->
                                <div>
                                    <x-input-label for="date" :value="__('Date')" />
                                    <x-text-input id="date" name="date" type="date" class="mt-1 block w-full" :value="old('date', now()->format('Y-m-d'))" required />
                                    <x-input-error :messages="$errors->get('date')" class="mt-2" />
                                </div>

                                <!-- Status -->
                                <div>
                                    <x-input-label for="status" :value="__('Status')" />
                                    <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        <option value="Pending" {{ old('status', 'Pending') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="Approved" {{ old('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="Completed" {{ old('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                                </div>

                                <!-- Destination -->
                                <div>
                                    <x-input-label for="destination" :value="__('Destination')" />
                                    <x-text-input id="destination" name="destination" type="text" class="mt-1 block w-full" :value="old('destination')" placeholder="Destination" />
                                    <x-input-error :messages="$errors->get('destination')" class="mt-2" />
                                </div>

                                <!-- Vehicle Number -->
                                <div>
                                    <x-input-label for="vehicle_number" :value="__('Vehicle Number')" />
                                    <x-text-input id="vehicle_number" name="vehicle_number" type="text" class="mt-1 block w-full" :value="old('vehicle_number')" placeholder="Vehicle Number" />
                                    <x-input-error :messages="$errors->get('vehicle_number')" class="mt-2" />
                                </div>

                                <!-- Driver Name -->
                                <div>
                                    <x-input-label for="driver_name" :value="__('Driver Name')" />
                                    <x-text-input id="driver_name" name="driver_name" type="text" class="mt-1 block w-full" :value="old('driver_name')" placeholder="Driver Name" />
                                    <x-input-error :messages="$errors->get('driver_name')" class="mt-2" />
                                </div>

                                <!-- Client Name -->
                                <div>
                                    <x-input-label for="client_name" :value="__('Client Name')" />
                                    <x-text-input id="client_name" name="client_name" type="text" class="mt-1 block w-full" :value="old('client_name')" placeholder="Client Name" />
                                    <x-input-error :messages="$errors->get('client_name')" class="mt-2" />
                                </div>

                                <!-- Client Number -->
                                <div>
                                    <x-input-label for="client_number" :value="__('Client Number')" />
                                    <x-text-input id="client_number" name="client_number" type="text" class="mt-1 block w-full" :value="old('client_number')" placeholder="Client Number" />
                                    <x-input-error :messages="$errors->get('client_number')" class="mt-2" />
                                </div>

                                <!-- Notes -->
                                <div class="md:col-span-2">
                                    <x-input-label for="notes" :value="__('Notes')" />
                                    <x-text-input id="notes" name="notes" type="text" class="mt-1 block w-full" :value="old('notes')" placeholder="Gate pass notes (optional)" />
                                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Excel-style Table -->
                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Gate Pass Items</h3>
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
                                <table class="min-w-full divide-y divide-gray-200" id="gate-pass-table">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-8">#</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-48">Stock Item</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Product</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Condition</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Available</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Issue Qty</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Particulars</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200" id="gate-pass-table-body">
                                        <!-- Rows will be dynamically added here -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Summary Section -->
                            <div class="mt-4 p-4 bg-green-50 rounded-lg border border-green-200">
                                <h4 class="text-sm font-medium text-green-900 mb-2">Summary</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <span class="font-medium text-green-700">Total Rows:</span>
                                        <span id="total-rows" class="ml-2 text-green-600">0</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-green-700">Total Items:</span>
                                        <span id="total-items" class="ml-2 text-green-600">0</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-green-700">Total Quantity:</span>
                                        <span id="total-quantity" class="ml-2 text-green-600">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('stock-management.gate-pass.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-6 rounded-lg mr-4 transition-colors duration-200">
                                Cancel
                            </a>
                            <x-primary-button class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors duration-200">
                                {{ __('Create All Gate Passes') }}
                            </x-primary-button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($stockAdditions->count() > 0)
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <style>
        /* Select2 custom styling for better fit */
        .select2-container--default .select2-selection--single {
            height: auto !important;
            min-height: 38px;
            border: 1px solid #d1d5db !important;
            border-radius: 0.375rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
            padding-left: 8px;
            font-size: 0.75rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px;
        }
        .select2-dropdown {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 6px;
        }
        .select2-results__option {
            font-size: 0.75rem;
            padding: 8px;
        }
    </style>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let rowCounter = 0;
            const stockAdditions = @json($stockAdditions);
            
            const tableBody = document.getElementById('gate-pass-table-body');
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
                row.className = 'gate-pass-row';
                row.dataset.rowIndex = rowCounter;
                
                row.innerHTML = `
                    <td class="px-3 py-2 text-sm text-gray-600">${rowCounter}</td>
                    <td class="px-3 py-2">
                        <select name="items[${rowCounter}][stock_addition_id]" class="w-full text-xs border-gray-300 focus:border-green-500 focus:ring-green-500 rounded stock-addition-select" required>
                            <option value="">Select Stock Item</option>
                            ${stockAdditions.map(stock => `
                                <option value="${stock.id}" data-product="${stock.product?.name || ''}" data-condition="${stock.condition_status || ''}" data-available="${stock.available_pieces || 0}" data-sqft="${stock.available_sqft || 0}" data-weight="${stock.weight || 0}">
                                    ${stock.product?.name || 'N/A'} - ${stock.condition_status || 'N/A'} (${stock.available_pieces || 0} pieces${stock.available_sqft ? `, ${parseFloat(stock.available_sqft).toFixed(2)} sqft` : ''}${stock.weight ? `, ${parseFloat(stock.weight).toFixed(2)} kg` : ''})${stock.pid ? ` - PID: ${stock.pid}` : ''}
                                </option>
                            `).join('')}
                        </select>
                    </td>
                    <td class="px-3 py-2 text-sm text-gray-900 product-cell">-</td>
                    <td class="px-3 py-2 text-sm text-gray-900 condition-cell">-</td>
                    <td class="px-3 py-2 text-sm text-gray-900 available-cell">-</td>
                    <td class="px-3 py-2">
                        <input type="number" name="items[${rowCounter}][quantity_issued]" class="w-full text-xs border-gray-300 focus:border-green-500 focus:ring-green-500 rounded quantity-input" min="1" placeholder="Qty" required>
                    </td>
                    <td class="px-3 py-2">
                        <textarea name="items[${rowCounter}][particulars]" class="w-full text-xs border-gray-300 focus:border-green-500 focus:ring-green-500 rounded particulars-input" rows="2" placeholder="Particulars..."></textarea>
                    </td>
                    <td class="px-3 py-2 text-center">
                        <button type="button" class="remove-row text-red-600 hover:text-red-800 text-xs font-medium" ${rowCounter === 1 ? 'disabled' : ''}>
                            ${rowCounter === 1 ? '−' : '🗑️'}
                        </button>
                    </td>
                `;
                
                tableBody.appendChild(row);
                
                // Initialize Select2 on the new dropdown
                const selectElement = row.querySelector('.stock-addition-select');
                $(selectElement).select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: 'Select Stock Item',
                    allowClear: true,
                    dropdownParent: $('body'), // Use body to avoid z-index issues in table
                    minimumResultsForSearch: 0 // Always show search box
                });
                
                // Add event listeners to new row
                setupRowEventListeners(row);
                
                // Update summary
                updateSummary();
            }
            
            function setupRowEventListeners(row) {
                const stockAdditionSelect = row.querySelector('.stock-addition-select');
                const quantityInput = row.querySelector('.quantity-input');
                const removeBtn = row.querySelector('.remove-row');
                
                // Stock addition selection handler (using jQuery for Select2 compatibility)
                $(stockAdditionSelect).on('change', function() {
                    const selectedValue = $(this).val();
                    const selectedOption = this.querySelector(`option[value="${selectedValue}"]`);
                    
                    if (selectedOption && selectedValue) {
                        const product = selectedOption.dataset.product;
                        const condition = selectedOption.dataset.condition;
                        const available = selectedOption.dataset.available;
                        const sqft = selectedOption.dataset.sqft;
                        const weight = selectedOption.dataset.weight;
                        
                        row.querySelector('.product-cell').textContent = product || '-';
                        row.querySelector('.condition-cell').textContent = condition || '-';
                        
                        // Show available pieces and sqft/weight
                        let availableText = `${available || 0} pieces`;
                        if (sqft && parseFloat(sqft) > 0) {
                            availableText += `, ${parseFloat(sqft).toFixed(2)} sqft`;
                        }
                        if (weight && parseFloat(weight) > 0) {
                            availableText += `, ${parseFloat(weight).toFixed(2)} kg`;
                        }
                        row.querySelector('.available-cell').textContent = availableText;
                        
                        // Set max quantity
                        quantityInput.max = available;
                        quantityInput.placeholder = `Max: ${available}`;
                    } else {
                        row.querySelector('.product-cell').textContent = '-';
                        row.querySelector('.condition-cell').textContent = '-';
                        row.querySelector('.available-cell').textContent = '-';
                        quantityInput.max = '';
                        quantityInput.placeholder = 'Qty';
                    }
                });
                
                // Quantity input handler for summary
                quantityInput.addEventListener('input', updateSummary);
                
                // Remove row handler
                removeBtn.addEventListener('click', function() {
                    if (rowCounter > 1) {
                        // Destroy Select2 before removing row
                        const selectElement = row.querySelector('.stock-addition-select');
                        if ($(selectElement).hasClass('select2-hidden-accessible')) {
                            $(selectElement).select2('destroy');
                        }
                        row.remove();
                        renumberRows();
                        updateSummary();
                    }
                });
            }
            
            function renumberRows() {
                const rows = tableBody.querySelectorAll('.gate-pass-row');
                rows.forEach((row, index) => {
                    const rowNumber = index + 1;
                    row.dataset.rowIndex = rowNumber;
                    row.querySelector('td:first-child').textContent = rowNumber;
                    
                    // Update input names
                    const inputs = row.querySelectorAll('input, select');
                    inputs.forEach(input => {
                        const name = input.getAttribute('name');
                        if (name) {
                            input.setAttribute('name', name.replace(/items\[\d+\]/, `items[${rowNumber}]`));
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
                    // Destroy all Select2 instances before clearing
                    tableBody.querySelectorAll('.stock-addition-select').forEach(select => {
                        if ($(select).hasClass('select2-hidden-accessible')) {
                            $(select).select2('destroy');
                        }
                    });
                    tableBody.innerHTML = '';
                    rowCounter = 0;
                    addRow();
                }
            }
            
            function updateSummary() {
                const rows = tableBody.querySelectorAll('.gate-pass-row');
                let totalRows = 0;
                let totalItems = 0;
                let totalQuantity = 0;
                
                rows.forEach(row => {
                    const stockSelect = row.querySelector('.stock-addition-select');
                    const quantityInput = row.querySelector('.quantity-input');
                    const quantity = parseInt(quantityInput.value) || 0;
                    const selectedValue = $(stockSelect).val(); // Use jQuery to get Select2 value
                    
                    if (selectedValue && quantity > 0) {
                        totalRows++;
                        totalItems++;
                        totalQuantity += quantity;
                    }
                });
                
                document.getElementById('total-rows').textContent = totalRows;
                document.getElementById('total-items').textContent = totalItems;
                document.getElementById('total-quantity').textContent = totalQuantity;
            }
            
            // Form submission handler
            document.getElementById('excel-form').addEventListener('submit', function(e) {
                const rows = tableBody.querySelectorAll('.gate-pass-row');
                let hasValidRows = false;
                
                rows.forEach(row => {
                    const stockSelect = row.querySelector('.stock-addition-select');
                    const quantityInput = row.querySelector('.quantity-input');
                    const selectedValue = $(stockSelect).val(); // Use jQuery to get Select2 value
                    
                    if (stockSelect && selectedValue && quantityInput && quantityInput.value) {
                        hasValidRows = true;
                    }
                });
                
                if (!hasValidRows) {
                    e.preventDefault();
                    alert('Please add at least one complete gate pass item before submitting.');
                    return false;
                }
            });
        });
    </script>
    @endif
</x-app-layout>