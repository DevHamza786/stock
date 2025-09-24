<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Edit Stock Issuance</h1>
                <p class="mt-2 text-gray-600">Update stock issuance information</p>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="p-6">
                    <form method="POST" action="{{ route('stock-management.stock-issued.update', $stockIssued) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Stock Addition (Read-only) -->
                            <div>
                                <x-input-label for="stock_addition_id" :value="__('Stock Addition')" />
                                <div class="mt-1 p-3 bg-gray-100 border border-gray-300 rounded-lg">
                                    <div class="text-sm text-gray-900">
                                        <strong>{{ $stockIssued->stockAddition->product->name }}</strong><br>
                                        <span class="text-gray-600">{{ $stockIssued->stockAddition->mineVendor->name }}</span><br>
                                        <span class="text-gray-500">{{ $stockIssued->stockAddition->stone }} - {{ $stockIssued->stockAddition->size_3d }}</span>
                                    </div>
                                </div>
                                <input type="hidden" name="stock_addition_id" value="{{ $stockIssued->stock_addition_id }}">
                            </div>

                            <!-- Quantity Issued -->
                            <div>
                                <x-input-label for="quantity_issued" :value="__('Quantity Issued')" />
                                <x-text-input id="quantity_issued" name="quantity_issued" type="number" class="mt-1 block w-full" :value="old('quantity_issued', $stockIssued->quantity_issued)" min="1" :max="$stockIssued->stockAddition->available_pieces + $stockIssued->quantity_issued" required />
                                <p class="mt-1 text-sm text-gray-500">Available: {{ number_format($stockIssued->stockAddition->available_pieces + $stockIssued->quantity_issued) }} pieces</p>
                                <x-input-error :messages="$errors->get('quantity_issued')" class="mt-2" />
                            </div>

                            <!-- Square Footage Issued (Auto-calculated) -->
                            <div>
                                <x-input-label for="sqft_issued" :value="__('Square Footage Issued')" />
                                <x-text-input id="sqft_issued" name="sqft_issued" type="number" class="mt-1 block w-full bg-gray-100" :value="old('sqft_issued', $stockIssued->sqft_issued)" readonly />
                                <p class="mt-1 text-sm text-gray-500">Auto-calculated based on quantity and size</p>
                                <x-input-error :messages="$errors->get('sqft_issued')" class="mt-2" />
                            </div>

                            <!-- Purpose -->
                            <div>
                                <x-input-label for="purpose" :value="__('Purpose')" />
                                <select id="purpose" name="purpose" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Select Purpose</option>
                                    <option value="Production" {{ old('purpose', $stockIssued->purpose) == 'Production' ? 'selected' : '' }}>Production</option>
                                    <option value="Sale" {{ old('purpose', $stockIssued->purpose) == 'Sale' ? 'selected' : '' }}>Sale</option>
                                    <option value="Transfer" {{ old('purpose', $stockIssued->purpose) == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                                    <option value="Other" {{ old('purpose', $stockIssued->purpose) == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                <x-input-error :messages="$errors->get('purpose')" class="mt-2" />
                            </div>

                            <!-- Machine Name -->
                            <div>
                                <x-input-label for="machine_name" :value="__('Machine Name')" />
                                <x-text-input id="machine_name" name="machine_name" type="text" class="mt-1 block w-full" :value="old('machine_name', $stockIssued->machine_name)" placeholder="Enter machine name..." />
                                <x-input-error :messages="$errors->get('machine_name')" class="mt-2" />
                            </div>

                            <!-- Operator Name -->
                            <div>
                                <x-input-label for="operator_name" :value="__('Operator Name')" />
                                <x-text-input id="operator_name" name="operator_name" type="text" class="mt-1 block w-full" :value="old('operator_name', $stockIssued->operator_name)" placeholder="Enter operator name..." />
                                <x-input-error :messages="$errors->get('operator_name')" class="mt-2" />
                            </div>

                            <!-- Date -->
                            <div>
                                <x-input-label for="date" :value="__('Issue Date')" />
                                <x-text-input id="date" name="date" type="date" class="mt-1 block w-full" :value="old('date', $stockIssued->date->format('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('date')" class="mt-2" />
                            </div>

                            <!-- Notes -->
                            <div class="md:col-span-2">
                                <x-input-label for="notes" :value="__('Notes')" />
                                <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('notes', $stockIssued->notes) }}</textarea>
                                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Stock Information Display -->
                        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h3 class="text-lg font-medium text-blue-900 mb-3">Stock Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-blue-700">Total Pieces</dt>
                                    <dd class="text-sm text-blue-900">{{ number_format($stockIssued->stockAddition->total_pieces) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-blue-700">Available Pieces</dt>
                                    <dd class="text-sm text-blue-900">{{ number_format($stockIssued->stockAddition->available_pieces + $stockIssued->quantity_issued) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-blue-700">Available Sqft</dt>
                                    <dd class="text-sm text-blue-900">{{ number_format($stockIssued->stockAddition->available_sqft + $stockIssued->sqft_issued, 2) }}</dd>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('stock-management.stock-issued.show', $stockIssued) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-6 rounded-lg mr-4 transition-colors duration-200">
                                Cancel
                            </a>
                            <x-primary-button class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors duration-200">
                                {{ __('Update Issuance') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const quantityInput = document.getElementById('quantity_issued');
            const sqftInput = document.getElementById('sqft_issued');
            const stockAddition = @json($stockIssued->stockAddition);

            function calculateSqft() {
                const quantity = parseInt(quantityInput.value) || 0;

                if (quantity > 0 && stockAddition.size_3d) {
                    // Extract dimensions from size_3d (e.g., 20143 = 20x14x3)
                    const size3d = stockAddition.size_3d;
                    if (size3d.length >= 3) {
                        const length = parseInt(size3d.substring(0, 2));
                        const width = parseInt(size3d.substring(2, 4));
                        const height = parseInt(size3d.substring(4, 5));

                        if (length && width) {
                            const sqftPerPiece = length * width;
                            const totalSqft = sqftPerPiece * quantity;
                            sqftInput.value = totalSqft.toFixed(2);
                        }
                    }
                } else {
                    sqftInput.value = '';
                }
            }

            quantityInput.addEventListener('input', calculateSqft);

            // Calculate on page load
            calculateSqft();
        });
    </script>
</x-app-layout>
