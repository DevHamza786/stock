<x-app-layout>
    <div class="py-8">
            <div class="w-full px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Edit Stock Issuance</h1>
                <p class="mt-2 text-gray-600">Update stock issuance information</p>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="p-6">
                    <form method="POST" action="{{ route('stock-management.stock-issued.update', $stockIssued) }}" id="stock-issued-edit-form">
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
                                        <span class="text-gray-500">{{ $stockIssued->stockAddition->stone }} - 
                                            @if(in_array(strtolower(trim($stockIssued->stockAddition->condition_status)), ['block', 'monuments']))
                                                Weight: {{ number_format($stockIssued->stockAddition->weight, 2) }} kg
                                            @else
                                                @if($stockIssued->stockAddition->length && $stockIssued->stockAddition->height)
                                                    {{ $stockIssued->stockAddition->length }} × {{ $stockIssued->stockAddition->height }} cm
                                                @else
                                                    {{ $stockIssued->stockAddition->size_3d }}
                                                @endif
                                            @endif
                                        </span>
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

                            <!-- Square Footage Issued (for non-block/monuments conditions) -->
                            <div id="sqft-field" style="display: {{ in_array(strtolower(trim($stockIssued->stockAddition->condition_status)), ['block', 'monuments']) ? 'none' : 'block' }};">
                                <x-input-label for="sqft_issued" :value="__('Square Footage Issued')" />
                                <x-text-input id="sqft_issued" name="sqft_issued" type="number" class="mt-1 block w-full bg-gray-100" :value="old('sqft_issued', $stockIssued->sqft_issued)" readonly />
                                <p class="mt-1 text-sm text-gray-500">Auto-calculated based on quantity and size</p>
                                <x-input-error :messages="$errors->get('sqft_issued')" class="mt-2" />
                            </div>

                            <!-- Weight Issued (for Block and Monuments conditions) -->
                            <div id="weight-field" style="display: {{ in_array(strtolower(trim($stockIssued->stockAddition->condition_status)), ['block', 'monuments']) ? 'block' : 'none' }};">
                                <x-input-label for="weight_issued" :value="__('Weight Issued (kg)')" />
                                <x-text-input id="weight_issued" name="weight_issued" type="number" step="0.01" class="mt-1 block w-full bg-gray-100" :value="old('weight_issued', $stockIssued->weight_issued)" readonly />
                                <p class="mt-1 text-sm text-gray-500">Auto-calculated based on quantity and weight per piece</p>
                                <x-input-error :messages="$errors->get('weight_issued')" class="mt-2" />
                            </div>

                            <!-- Purpose -->
                            <div>
                                <x-input-label for="purpose" :value="__('Purpose')" />
                                <select id="purpose" name="purpose" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Select purpose...</option>
                                    <option value="Production" {{ old('purpose', $stockIssued->purpose) == 'Production' ? 'selected' : '' }}>Production</option>
                                    <option value="Processing" {{ old('purpose', $stockIssued->purpose) == 'Processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="Cutting" {{ old('purpose', $stockIssued->purpose) == 'Cutting' ? 'selected' : '' }}>Cutting</option>
                                    <option value="Polishing" {{ old('purpose', $stockIssued->purpose) == 'Polishing' ? 'selected' : '' }}>Polishing</option>
                                    <option value="Other" {{ old('purpose', $stockIssued->purpose) == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                <x-input-error :messages="$errors->get('purpose')" class="mt-2" />
                            </div>

                            <!-- Machine -->
                            <div>
                                <x-input-label for="machine_id" :value="__('Machine')" />
                                <select id="machine_id" name="machine_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Select machine...</option>
                                    @foreach($machines as $machine)
                                        <option value="{{ $machine->id }}" {{ old('machine_id', $stockIssued->machine_id) == $machine->id ? 'selected' : '' }}>
                                            {{ $machine->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('machine_id')" class="mt-2" />
                            </div>

                            <!-- Operator -->
                            <div>
                                <x-input-label for="operator_id" :value="__('Operator')" />
                                <select id="operator_id" name="operator_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Select operator...</option>
                                    @foreach($operators as $operator)
                                        <option value="{{ $operator->id }}" {{ old('operator_id', $stockIssued->operator_id) == $operator->id ? 'selected' : '' }}>
                                            {{ $operator->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('operator_id')" class="mt-2" />
                            </div>

                            <!-- Particulars -->
                            <div>
                                <x-input-label for="stone" :value="__('Particulars')" />
                                <x-text-input id="stone" name="stone" type="text" class="mt-1 block w-full bg-gray-100" :value="old('stone', $stockIssued->stone)" readonly />
                                <p class="mt-1 text-sm text-gray-500">Automatically filled from stock addition</p>
                                <x-input-error :messages="$errors->get('stone')" class="mt-2" />
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
                                @if(in_array(strtolower(trim($stockIssued->stockAddition->condition_status)), ['block', 'monuments']))
                                    <div>
                                        <dt class="text-sm font-medium text-blue-700">Available Weight</dt>
                                        <dd class="text-sm text-blue-900">{{ number_format($stockIssued->stockAddition->available_weight + $stockIssued->weight_issued, 2) }} kg</dd>
                                    </div>
                                @else
                                    <div>
                                        <dt class="text-sm font-medium text-blue-700">Available Sqft</dt>
                                        <dd class="text-sm text-blue-900">{{ number_format($stockIssued->stockAddition->available_sqft + $stockIssued->sqft_issued, 2) }}</dd>
                                    </div>
                                @endif
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
            const weightInput = document.getElementById('weight_issued');
            const stockAddition = @json($stockIssued->stockAddition);
            const conditionStatus = stockAddition.condition_status.toLowerCase().trim();

            function calculateValues() {
                const quantity = parseInt(quantityInput.value) || 0;

                if (quantity > 0) {
                    if (conditionStatus === 'block' || conditionStatus === 'monuments') {
                        // Calculate weight for Block and Monuments conditions
                        const weightPerPiece = parseFloat(stockAddition.weight) || 0;
                        const totalWeight = weightPerPiece * quantity;
                        if (weightInput) {
                            weightInput.value = totalWeight.toFixed(2);
                        }
                    } else {
                        // Calculate sqft for regular conditions
                        // Use total_sqft and total_pieces to calculate sqft per piece
                        if (sqftInput && stockAddition.total_sqft && stockAddition.total_pieces > 0) {
                            const sqftPerPiece = parseFloat(stockAddition.total_sqft) / parseFloat(stockAddition.total_pieces);
                            const totalSqft = sqftPerPiece * quantity;
                            sqftInput.value = totalSqft.toFixed(2);
                        } else if (sqftInput && stockAddition.length && stockAddition.height) {
                            // Fallback: Calculate from length and height if available
                            const length = parseFloat(stockAddition.length) || 0;
                            const height = parseFloat(stockAddition.height) || 0;
                            if (length > 0 && height > 0) {
                                // Convert cm² to sqft (1 sqft = 929.0304 cm²)
                                const areaCm = length * height;
                                const sqftPerPiece = areaCm / 929.0304;
                                const totalSqft = sqftPerPiece * quantity;
                                sqftInput.value = totalSqft.toFixed(2);
                            }
                        }
                    }
                } else {
                    // Clear values when quantity is 0
                    if (sqftInput) sqftInput.value = '';
                    if (weightInput) weightInput.value = '';
                }
            }

            quantityInput.addEventListener('input', calculateValues);

            // Calculate on page load
            calculateValues();

            // Clear unused field before form submission
            const form = document.getElementById('stock-issued-edit-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (conditionStatus === 'block' || conditionStatus === 'monuments') {
                        // For block/monuments, clear sqft_issued
                        if (sqftInput) sqftInput.value = '';
                    } else {
                        // For other conditions, clear weight_issued
                        if (weightInput) weightInput.value = '';
                    }
                });
            }
        });
    </script>
</x-app-layout>
