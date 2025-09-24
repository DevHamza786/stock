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

                            <!-- Size 3D -->
                            <div>
                                <x-input-label for="size_3d" :value="__('Size (3D)')" />
                                <x-text-input id="size_3d" name="size_3d" type="text" class="mt-1 block w-full" :value="old('size_3d', $stockAddition->size_3d)" placeholder="e.g., 20143 (20x14x3)" required />
                                <p class="mt-1 text-sm text-gray-500">Format: Length x Width x Height (e.g., 20143 = 20x14x3)</p>
                                <x-input-error :messages="$errors->get('size_3d')" class="mt-2" />
                            </div>

                            <!-- Total Pieces -->
                            <div>
                                <x-input-label for="total_pieces" :value="__('Total Pieces')" />
                                <x-text-input id="total_pieces" name="total_pieces" type="number" class="mt-1 block w-full" :value="old('total_pieces', $stockAddition->total_pieces)" min="1" required />
                                <x-input-error :messages="$errors->get('total_pieces')" class="mt-2" />
                            </div>

                            <!-- Total Sqft (Auto-calculated) -->
                            <div>
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
            const size3dInput = document.getElementById('size_3d');
            const totalPiecesInput = document.getElementById('total_pieces');
            const totalSqftInput = document.getElementById('total_sqft');

            function calculateSqft() {
                const size3d = size3dInput.value;
                const pieces = parseInt(totalPiecesInput.value) || 0;

                if (size3d && pieces > 0) {
                    // Extract dimensions from size_3d (e.g., 20143 = 20x14x3)
                    if (size3d.length >= 3) {
                        const length = parseInt(size3d.substring(0, 2));
                        const width = parseInt(size3d.substring(2, 4));
                        const height = parseInt(size3d.substring(4, 5));

                        if (length && width) {
                            const sqftPerPiece = length * width;
                            const totalSqft = sqftPerPiece * pieces;
                            totalSqftInput.value = totalSqft.toFixed(2);
                        }
                    }
                } else {
                    totalSqftInput.value = '';
                }
            }

            size3dInput.addEventListener('input', calculateSqft);
            totalPiecesInput.addEventListener('input', calculateSqft);

            // Calculate on page load if values exist
            calculateSqft();
        });
    </script>
</x-app-layout>
