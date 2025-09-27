<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Add Stock</h1>
                <p class="mt-2 text-gray-600">Add new stock entries to the system</p>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="p-6">
                    <form method="POST" action="{{ route('stock-management.stock-additions.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Product -->
                            <div>
                                <x-input-label for="product_id" :value="__('Product')" />
                                <select id="product_id" name="product_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">Select Product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
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
                                        <option value="{{ $vendor->id }}" {{ old('mine_vendor_id') == $vendor->id ? 'selected' : '' }}>
                                            {{ $vendor->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('mine_vendor_id')" class="mt-2" />
                            </div>

                            <!-- Stone Type -->
                            <div>
                                <x-input-label for="stone" :value="__('Stone Type')" />
                                <x-text-input id="stone" name="stone" type="text" class="mt-1 block w-full" :value="old('stone')" required />
                                <x-input-error :messages="$errors->get('stone')" class="mt-2" />
                            </div>

                            <!-- Length -->
                            <div>
                                <x-input-label for="length" :value="__('Length (cm)')" />
                                <x-text-input id="length" name="length" type="number" class="mt-1 block w-full" :value="old('length')" placeholder="Enter length in cm" step="0.1" required />
                                <x-input-error :messages="$errors->get('length')" class="mt-2" />
                            </div>

                            <!-- Height -->
                            <div>
                                <x-input-label for="height" :value="__('Height (cm)')" />
                                <x-text-input id="height" name="height" type="number" class="mt-1 block w-full" :value="old('height')" placeholder="Enter height in cm" step="0.1" required />
                                <x-input-error :messages="$errors->get('height')" class="mt-2" />
                            </div>

                            <!-- Total Pieces -->
                            <div>
                                <x-input-label for="total_pieces" :value="__('Total Pieces')" />
                                <x-text-input id="total_pieces" name="total_pieces" type="number" class="mt-1 block w-full" :value="old('total_pieces')" min="1" required />
                                <x-input-error :messages="$errors->get('total_pieces')" class="mt-2" />
                            </div>

                            <!-- Size Information Display -->
                            <div class="md:col-span-2">
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

                            <!-- Total Sqft (Auto-calculated) -->
                            <div>
                                <x-input-label for="total_sqft" :value="__('Total Sqft')" />
                                <x-text-input id="total_sqft" name="total_sqft" type="number" class="mt-1 block w-full bg-gray-100" readonly />
                                <p class="mt-1 text-sm text-gray-500">Auto-calculated based on size and pieces</p>
                                <x-input-error :messages="$errors->get('total_sqft')" class="mt-2" />
                            </div>

                            <!-- Condition Status -->
                            <div>
                                <x-input-label for="condition_status" :value="__('Condition Status')" />
                                <select id="condition_status" name="condition_status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">Select condition...</option>
                                    @foreach($conditionStatuses as $status)
                                        <option value="{{ $status->name }}" {{ old('condition_status') == $status->name ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('condition_status')" class="mt-2" />
                            </div>

                            <!-- Date -->
                            <div>
                                <x-input-label for="date" :value="__('Date')" />
                                <x-text-input id="date" name="date" type="date" class="mt-1 block w-full" :value="old('date', now()->format('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('date')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('stock-management.stock-additions.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-6 rounded-lg mr-4 transition-colors duration-200">
                                Cancel
                            </a>
                            <x-primary-button class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors duration-200">
                                {{ __('Add Stock') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const lengthInput = document.getElementById('length');
            const heightInput = document.getElementById('height');
            const totalPiecesInput = document.getElementById('total_pieces');
            const totalSqftInput = document.getElementById('total_sqft');
            
            // Display elements
            const singlePieceSizeDisplay = document.getElementById('single_piece_size');
            const singlePieceSqftDisplay = document.getElementById('single_piece_sqft');
            const totalSizeDisplay = document.getElementById('total_size_display');

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

            // Add event listeners
            lengthInput.addEventListener('input', calculateSize);
            heightInput.addEventListener('input', calculateSize);
            totalPiecesInput.addEventListener('input', calculateSize);

            // Calculate on page load if values exist
            calculateSize();
        });
    </script>
</x-app-layout>
