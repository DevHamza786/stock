<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Edit Stock</h1>
                <p class="mt-2 text-gray-600">Update stock addition information</p>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="p-6">
                    <!-- Warning if stock has been issued -->
                    @if($stockAddition->hasBeenIssued())
                        <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">Stock Has Been Issued</h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <p>This stock has been issued {{ $stockAddition->stockIssued()->count() }} time(s). You cannot modify dimensions (length, height), total pieces, or total sqft. You can only update other information like stone type, condition status, and date.</p>
                                        <p class="mt-2 font-medium">To modify dimensions, you must first delete all related stock issuances.</p>
                                        @if($stockAddition->stockIssued()->count() > 0)
                                            <div class="mt-3">
                                                <a href="{{ route('stock-management.stock-issued.index', ['search' => $stockAddition->product->name]) }}" class="inline-flex items-center text-sm font-medium text-yellow-800 hover:text-yellow-900">
                                                    View Related Stock Issuances
                                                    <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                    </svg>
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('stock-management.stock-additions.update', $stockAddition) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Product -->
                            <div>
                                <x-input-label for="product_id" :value="__('Product')" />
                                <select id="product_id" name="product_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">Select Product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ old('product_id', $stockAddition->product_id) == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('product_id')" class="mt-2" />
                            </div>

                            <!-- Mine Vendor -->
                            <div>
                                <x-input-label for="mine_vendor_id" :value="__('Mine Vendor')" />
                                <select id="mine_vendor_id" name="mine_vendor_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">Select Vendor</option>
                                    @foreach($mineVendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ old('mine_vendor_id', $stockAddition->mine_vendor_id) == $vendor->id ? 'selected' : '' }}>
                                            {{ $vendor->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('mine_vendor_id')" class="mt-2" />
                            </div>

                            <!-- Stone Type -->
                            <div>
                                <x-input-label for="stone" :value="__('Stone Type')" />
                                <x-text-input id="stone" name="stone" type="text" class="mt-1 block w-full" :value="old('stone', $stockAddition->stone)" required />
                                <x-input-error :messages="$errors->get('stone')" class="mt-2" />
                            </div>

                            <!-- Length -->
                            <div id="length-field">
                                <x-input-label for="length" :value="__('Length (cm)')" />
                                <x-text-input id="length" name="length" type="number" class="mt-1 block w-full {{ $stockAddition->hasBeenIssued() ? 'bg-gray-100' : '' }}" :value="old('length', $stockAddition->length)" placeholder="Enter length in cm" step="0.1" {{ $stockAddition->hasBeenIssued() ? 'disabled' : '' }} />
                                @if($stockAddition->hasBeenIssued())
                                    <p class="mt-1 text-xs text-yellow-600">Cannot modify after stock has been issued</p>
                                @endif
                                <x-input-error :messages="$errors->get('length')" class="mt-2" />
                            </div>

                            <!-- Height -->
                            <div id="height-field">
                                <x-input-label for="height" :value="__('Height (cm)')" />
                                <x-text-input id="height" name="height" type="number" class="mt-1 block w-full {{ $stockAddition->hasBeenIssued() ? 'bg-gray-100' : '' }}" :value="old('height', $stockAddition->height)" placeholder="Enter height in cm" step="0.1" {{ $stockAddition->hasBeenIssued() ? 'disabled' : '' }} />
                                @if($stockAddition->hasBeenIssued())
                                    <p class="mt-1 text-xs text-yellow-600">Cannot modify after stock has been issued</p>
                                @endif
                                <x-input-error :messages="$errors->get('height')" class="mt-2" />
                            </div>

                            <!-- Diameter -->
                            <div id="diameter-field">
                                <x-input-label for="diameter" :value="__('Diameter/Thickness (cm)')" />
                                <x-text-input id="diameter" name="diameter" type="text" class="mt-1 block w-full {{ $stockAddition->hasBeenIssued() ? 'bg-gray-100' : '' }}" :value="old('diameter', $stockAddition->diameter)" placeholder="e.g., 6cm, 2cm, 3.5cm" {{ $stockAddition->hasBeenIssued() ? 'disabled' : '' }} />
                                @if($stockAddition->hasBeenIssued())
                                    <p class="mt-1 text-xs text-yellow-600">Cannot modify after stock has been issued</p>
                                @endif
                                <p class="mt-1 text-xs text-gray-500">Enter the thickness or diameter of the product</p>
                                <x-input-error :messages="$errors->get('diameter')" class="mt-2" />
                            </div>

                            <!-- Weight (for Block condition) -->
                            <div id="weight-field" style="display: {{ strtolower(trim($stockAddition->condition_status)) === 'block' ? 'block' : 'none' }};">
                                <x-input-label for="weight" :value="__('Weight (kg)')" />
                                <x-text-input id="weight" name="weight" type="number" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 {{ $stockAddition->hasBeenIssued() ? 'bg-gray-100 cursor-not-allowed' : 'bg-white hover:border-gray-400' }}" value="{{ old('weight', $stockAddition->weight) }}" placeholder="Enter weight in kg" step="0.1" {{ $stockAddition->hasBeenIssued() ? 'disabled' : '' }} />
                                @if($stockAddition->hasBeenIssued())
                                    <p class="mt-1 text-xs text-yellow-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        Cannot modify after stock has been issued
                                    </p>
                                @else
                                    <p class="mt-1 text-xs text-gray-500">Enter the weight per piece in kilograms</p>
                                @endif
                                <x-input-error :messages="$errors->get('weight')" class="mt-2" />
                            </div>

                            <!-- Total Pieces -->
                            <div>
                                <x-input-label for="total_pieces" :value="__('Total Pieces')" />
                                <x-text-input id="total_pieces" name="total_pieces" type="number" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 {{ $stockAddition->hasBeenIssued() ? 'bg-gray-100 cursor-not-allowed' : 'bg-white hover:border-gray-400' }}" value="{{ old('total_pieces', $stockAddition->total_pieces) }}" min="1" required {{ $stockAddition->hasBeenIssued() ? 'disabled' : '' }} />
                                @if($stockAddition->hasBeenIssued())
                                    <p class="mt-1 text-xs text-yellow-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        Cannot modify after stock has been issued
                                    </p>
                                @else
                                    <p class="mt-1 text-xs text-gray-500">Enter the total number of pieces</p>
                                @endif
                                <x-input-error :messages="$errors->get('total_pieces')" class="mt-2" />
                            </div>

                            <!-- Size Information Display -->
                            <div id="size-info-section" class="md:col-span-2">
                                <div class="bg-gray-50 p-4 rounded-lg border">
                                    <h3 class="text-sm font-medium text-gray-900 mb-3">Size Information</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Single Piece Size</label>
                                            <div id="single_piece_size" class="text-sm text-gray-600 bg-white p-2 rounded border">
                                                <span class="text-gray-400">Enter dimensions to see size</span>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Single Piece (sqft)</label>
                                            <div id="single_piece_sqft" class="text-sm text-gray-600 bg-white p-2 rounded border">
                                                <span class="text-gray-400">Enter dimensions to see size</span>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Total Size (sqft)</label>
                                            <div id="total_size_display" class="text-sm text-gray-600 bg-white p-2 rounded border">
                                                <span class="text-gray-400">Enter dimensions and pieces</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Block Information Display -->
                            <div id="block-info-section" class="md:col-span-2" style="display: {{ strtolower(trim($stockAddition->condition_status)) === 'block' ? 'block' : 'none' }};">
                                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                    <h3 class="text-sm font-medium text-blue-900 mb-3">Block Information</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-blue-700 mb-1">Total Weight</label>
                                            <div id="total_weight_display" class="text-sm text-blue-600 bg-white p-2 rounded border">
                                                <span class="text-gray-400">Enter weight and pieces</span>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-blue-700 mb-1">Total Pieces</label>
                                            <div id="total_pieces_display" class="text-sm text-blue-600 bg-white p-2 rounded border">
                                                <span class="text-gray-400">Enter number of pieces</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Sqft (Auto-calculated) -->
                            <div id="total-sqft-field" style="display: {{ strtolower(trim($stockAddition->condition_status)) === 'block' ? 'none' : 'block' }};">
                                <x-input-label for="total_sqft" :value="__('Total Sqft')" />
                                <x-text-input id="total_sqft" name="total_sqft" type="number" class="mt-1 block w-full bg-gray-100" :value="old('total_sqft', $stockAddition->total_sqft)" readonly />
                                <p class="mt-1 text-sm text-gray-500">Auto-calculated based on size and pieces</p>
                                <x-input-error :messages="$errors->get('total_sqft')" class="mt-2" />
                            </div>

                            <!-- Condition Status -->
                            <div>
                                <x-input-label for="condition_status" :value="__('Condition Status')" />
                                <select id="condition_status" name="condition_status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">Select Condition</option>
                                    @foreach($conditionStatuses as $status)
                                        <option value="{{ $status->name }}" {{ old('condition_status', $stockAddition->condition_status) == $status->name ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('condition_status')" class="mt-2" />
                            </div>

                            <!-- Date -->
                            <div>
                                <x-input-label for="date" :value="__('Date')" />
                                <x-text-input id="date" name="date" type="date" class="mt-1 block w-full" :value="old('date', $stockAddition->date->format('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('date')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('stock-management.stock-additions.show', $stockAddition) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-6 rounded-lg mr-4 transition-colors duration-200">
                                Cancel
                            </a>
                            <x-primary-button class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors duration-200">
                                {{ __('Update Stock') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Debug: Log product details
            console.log('=== PRODUCT DETAILS DEBUG ===');
            console.log('Condition Status:', '{{ $stockAddition->condition_status }}');
            console.log('Condition Status Lower:', '{{ strtolower(trim($stockAddition->condition_status)) }}');
            console.log('Is Block:', '{{ strtolower(trim($stockAddition->condition_status)) === "block" }}');
            console.log('Weight:', '{{ $stockAddition->weight }}');
            console.log('Total Pieces:', '{{ $stockAddition->total_pieces }}');
            console.log('Length:', '{{ $stockAddition->length }}');
            console.log('Height:', '{{ $stockAddition->height }}');
            console.log('Total Sqft:', '{{ $stockAddition->total_sqft }}');
            console.log('Available Weight:', '{{ $stockAddition->available_weight }}');
            console.log('Has Been Issued:', '{{ $stockAddition->hasBeenIssued() }}');
            console.log('Can Be Updated:', '{{ $stockAddition->canBeUpdated() }}');
            console.log('================================');
            
            const conditionStatusSelect = document.getElementById('condition_status');
            const lengthInput = document.getElementById('length');
            const heightInput = document.getElementById('height');
            const weightInput = document.getElementById('weight');
            const totalPiecesInput = document.getElementById('total_pieces');
            const totalSqftInput = document.getElementById('total_sqft');
            
            // Debug: Check if elements exist
            console.log('=== ELEMENT CHECK ===');
            console.log('Weight input element:', weightInput);
            console.log('Total pieces input element:', totalPiecesInput);
            console.log('Weight input value:', weightInput ? weightInput.value : 'NOT FOUND');
            console.log('Total pieces input value:', totalPiecesInput ? totalPiecesInput.value : 'NOT FOUND');
            console.log('Weight input disabled:', weightInput ? weightInput.disabled : 'NOT FOUND');
            console.log('Total pieces input disabled:', totalPiecesInput ? totalPiecesInput.disabled : 'NOT FOUND');
            console.log('========================');
            
            // Field containers
            const lengthField = document.getElementById('length-field');
            const heightField = document.getElementById('height-field');
            const diameterField = document.getElementById('diameter-field');
            const weightField = document.getElementById('weight-field');
            const sizeInfoSection = document.getElementById('size-info-section');
            const blockInfoSection = document.getElementById('block-info-section');
            const totalSqftField = document.getElementById('total-sqft-field');
            
            // Display elements
            const singlePieceSizeDisplay = document.getElementById('single_piece_size');
            const singlePieceSqftDisplay = document.getElementById('single_piece_sqft');
            const totalSizeDisplay = document.getElementById('total_size_display');
            const totalWeightDisplay = document.getElementById('total_weight_display');
            const totalPiecesDisplay = document.getElementById('total_pieces_display');

            function toggleFields() {
                const conditionStatus = conditionStatusSelect.value.toLowerCase().trim();
                const isBlock = conditionStatus === 'block';

                if (isBlock) {
                    // Hide size fields and show weight field
                    lengthField.style.display = 'none';
                    heightField.style.display = 'none';
                    diameterField.style.display = 'none';
                    weightField.style.display = 'block';
                    sizeInfoSection.style.display = 'none';
                    blockInfoSection.style.display = 'block';
                    totalSqftField.style.display = 'none';
                    
                    // Remove required attributes from size fields
                    lengthInput.removeAttribute('required');
                    heightInput.removeAttribute('required');
                    weightInput.setAttribute('required', 'required');
                    
                    // Clear size calculations
                    clearSizeCalculations();
                } else {
                    // Show size fields and hide weight field
                    lengthField.style.display = 'block';
                    heightField.style.display = 'block';
                    diameterField.style.display = 'block';
                    weightField.style.display = 'none';
                    sizeInfoSection.style.display = 'block';
                    blockInfoSection.style.display = 'none';
                    totalSqftField.style.display = 'block';
                    
                    // Add required attributes to size fields
                    lengthInput.setAttribute('required', 'required');
                    heightInput.setAttribute('required', 'required');
                    weightInput.removeAttribute('required');
                    
                    // Clear block calculations
                    clearBlockCalculations();
                }
            }

            function calculateSize() {
                const length = parseFloat(lengthInput.value) || 0;
                const height = parseFloat(heightInput.value) || 0;
                const pieces = parseInt(totalPiecesInput.value) || 0;

                if (length > 0 && height > 0) {
                    // Convert cm to sqft (1 cm² = 0.00107639 sqft)
                    const cmToSqft = 0.00107639;
                    
                    // Calculate single piece size in cm²
                    const singlePieceSizeCm = length * height;
                    
                    // Calculate single piece size in sqft
                    const singlePieceSizeSqft = singlePieceSizeCm * cmToSqft;
                    
                    // Calculate total size in sqft
                    const totalSizeSqft = singlePieceSizeSqft * pieces;

                    // Update displays
                    singlePieceSizeDisplay.innerHTML = `<span class="font-medium text-blue-600">${singlePieceSizeCm.toFixed(2)} cm²</span><br><span class="text-xs text-gray-500">${length} × ${height} cm</span>`;
                    singlePieceSqftDisplay.innerHTML = `<span class="font-medium text-green-600">${singlePieceSizeSqft.toFixed(4)} sqft</span>`;
                    
                    if (pieces > 0) {
                        totalSizeDisplay.innerHTML = `<span class="font-medium text-purple-600">${totalSizeSqft.toFixed(4)} sqft</span><br><span class="text-xs text-gray-500">${pieces} pieces</span>`;
                        totalSqftInput.value = totalSizeSqft.toFixed(4);
                    } else {
                        totalSizeDisplay.innerHTML = '<span class="text-gray-400">Enter number of pieces</span>';
                        totalSqftInput.value = '';
                    }
                } else {
                    // Reset displays
                    singlePieceSizeDisplay.innerHTML = '<span class="text-gray-400">Enter dimensions to see size</span>';
                    singlePieceSqftDisplay.innerHTML = '<span class="text-gray-400">Enter dimensions to see size</span>';
                    totalSizeDisplay.innerHTML = '<span class="text-gray-400">Enter dimensions and pieces</span>';
                    totalSqftInput.value = '';
                }
            }

            function calculateBlock() {
                const weight = parseFloat(weightInput.value) || 0;
                const pieces = parseInt(totalPiecesInput.value) || 0;

                if (weight > 0 && pieces > 0) {
                    const totalWeight = weight * pieces;
                    
                    // Update displays
                    totalWeightDisplay.innerHTML = `<span class="font-medium text-blue-600">${totalWeight.toFixed(2)} kg</span><br><span class="text-xs text-gray-500">${weight} kg × ${pieces} pieces</span>`;
                    totalPiecesDisplay.innerHTML = `<span class="font-medium text-green-600">${pieces} pieces</span>`;
                } else {
                    // Reset displays
                    totalWeightDisplay.innerHTML = '<span class="text-gray-400">Enter weight and pieces</span>';
                    totalPiecesDisplay.innerHTML = '<span class="text-gray-400">Enter number of pieces</span>';
                }
            }

            function clearSizeCalculations() {
                singlePieceSizeDisplay.innerHTML = '<span class="text-gray-400">Enter dimensions to see size</span>';
                singlePieceSqftDisplay.innerHTML = '<span class="text-gray-400">Enter dimensions to see size</span>';
                totalSizeDisplay.innerHTML = '<span class="text-gray-400">Enter dimensions and pieces</span>';
                totalSqftInput.value = '';
            }

            function clearBlockCalculations() {
                totalWeightDisplay.innerHTML = '<span class="text-gray-400">Enter weight and pieces</span>';
                totalPiecesDisplay.innerHTML = '<span class="text-gray-400">Enter number of pieces</span>';
            }

            // Add event listeners
            conditionStatusSelect.addEventListener('change', toggleFields);
            lengthInput.addEventListener('input', calculateSize);
            heightInput.addEventListener('input', calculateSize);
            weightInput.addEventListener('input', calculateBlock);
            totalPiecesInput.addEventListener('input', function() {
                const conditionStatus = conditionStatusSelect.value.toLowerCase().trim();
                if (conditionStatus === 'block') {
                    calculateBlock();
                } else {
                    calculateSize();
                }
            });

            // Initialize fields on page load
            toggleFields();
            
            // Force display of correct fields based on current condition status
            const currentCondition = conditionStatusSelect.value.toLowerCase().trim();
            console.log('Forcing display for condition:', currentCondition);
            
            if (currentCondition === 'block') {
                // Ensure block fields are visible
                console.log('Showing block fields');
                weightField.style.display = 'block';
                blockInfoSection.style.display = 'block';
                sizeInfoSection.style.display = 'none';
                totalSqftField.style.display = 'none';
                lengthField.style.display = 'none';
                heightField.style.display = 'none';
                diameterField.style.display = 'none';
                
                // Force set values immediately
                const modelWeight = parseFloat('{{ $stockAddition->weight }}') || 0;
                const modelPieces = parseInt('{{ $stockAddition->total_pieces }}') || 0;
                
                console.log('Force setting values - Weight:', modelWeight, 'Pieces:', modelPieces);
                
                // Force enable fields if they're disabled
                if (weightInput && weightInput.disabled) {
                    weightInput.disabled = false;
                    console.log('Weight input enabled');
                }
                
                if (totalPiecesInput && totalPiecesInput.disabled) {
                    totalPiecesInput.disabled = false;
                    console.log('Total pieces input enabled');
                }
                
                if (modelWeight > 0 && weightInput) {
                    weightInput.value = modelWeight;
                    console.log('Weight input set to:', weightInput.value);
                }
                
                if (modelPieces > 0 && totalPiecesInput) {
                    totalPiecesInput.value = modelPieces;
                    console.log('Pieces input set to:', totalPiecesInput.value);
                }
            } else {
                console.log('Showing size fields');
                weightField.style.display = 'none';
                blockInfoSection.style.display = 'none';
                sizeInfoSection.style.display = 'block';
                totalSqftField.style.display = 'block';
                lengthField.style.display = 'block';
                heightField.style.display = 'block';
                diameterField.style.display = 'block';
            }

            // Load existing data on page load
            const conditionStatus = conditionStatusSelect.value.toLowerCase().trim();
            console.log('Current condition status:', conditionStatus);
            
            if (conditionStatus === 'block') {
                // For block condition, load weight data
                const existingWeight = parseFloat(weightInput.value) || 0;
                const existingPieces = parseInt(totalPiecesInput.value) || 0;
                
                console.log('Block condition - Weight:', existingWeight, 'Pieces:', existingPieces);
                console.log('Weight input value:', weightInput.value);
                console.log('Pieces input value:', totalPiecesInput.value);
                
                // Force set values if they exist in the model but not in inputs
                if (existingWeight === 0 && '{{ $stockAddition->weight }}' !== '') {
                    const modelWeight = parseFloat('{{ $stockAddition->weight }}') || 0;
                    console.log('Setting weight from model:', modelWeight);
                    weightInput.value = modelWeight;
                }
                
                if (existingPieces === 0 && '{{ $stockAddition->total_pieces }}' !== '') {
                    const modelPieces = parseInt('{{ $stockAddition->total_pieces }}') || 0;
                    console.log('Setting pieces from model:', modelPieces);
                    totalPiecesInput.value = modelPieces;
                }
                
                // Re-read values after setting
                const finalWeight = parseFloat(weightInput.value) || 0;
                const finalPieces = parseInt(totalPiecesInput.value) || 0;
                
                console.log('Final values - Weight:', finalWeight, 'Pieces:', finalPieces);
                
                if (finalWeight > 0 && finalPieces > 0) {
                    const totalWeight = finalWeight * finalPieces;
                    console.log('Calculating total weight:', totalWeight);
                    totalWeightDisplay.innerHTML = `<span class="font-medium text-blue-600">${totalWeight.toFixed(2)} kg</span><br><span class="text-xs text-gray-500">${finalWeight} kg × ${finalPieces} pieces</span>`;
                    totalPiecesDisplay.innerHTML = `<span class="font-medium text-green-600">${finalPieces} pieces</span>`;
                } else if (finalWeight > 0) {
                    console.log('Only weight available:', finalWeight);
                    totalWeightDisplay.innerHTML = `<span class="font-medium text-blue-600">${finalWeight} kg per piece</span>`;
                } else {
                    console.log('No weight data found');
                }
                
                // Calculate block information
                calculateBlock();
            } else {
                // For other conditions, load size data
                const existingLength = parseFloat(lengthInput.value) || 0;
                const existingHeight = parseFloat(heightInput.value) || 0;
                const existingPieces = parseInt(totalPiecesInput.value) || 0;
                
                if (existingLength > 0 && existingHeight > 0) {
                    const cmToSqft = 0.00107639;
                    const singlePieceSizeCm = existingLength * existingHeight;
                    const singlePieceSizeSqft = singlePieceSizeCm * cmToSqft;
                    const totalSizeSqft = singlePieceSizeSqft * existingPieces;
                    
                    singlePieceSizeDisplay.innerHTML = `<span class="font-medium text-blue-600">${singlePieceSizeCm.toFixed(2)} cm²</span><br><span class="text-xs text-gray-500">${existingLength} × ${existingHeight} cm</span>`;
                    singlePieceSqftDisplay.innerHTML = `<span class="font-medium text-green-600">${singlePieceSizeSqft.toFixed(4)} sqft</span>`;
                    
                    if (existingPieces > 0) {
                        totalSizeDisplay.innerHTML = `<span class="font-medium text-purple-600">${totalSizeSqft.toFixed(4)} sqft</span><br><span class="text-xs text-gray-500">${existingPieces} pieces</span>`;
                    }
                }
                
                // Calculate size information
                calculateSize();
            }
        });
    </script>
</x-app-layout>
