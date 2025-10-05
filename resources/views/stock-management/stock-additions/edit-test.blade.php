<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Edit Stock (SIMPLE TEST)</h1>
                <p class="mt-2 text-gray-600">Testing basic update functionality</p>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="p-6">
                    
                    @if($errors->any())
                        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                            <h3 class="text-sm font-medium text-red-800">Validation Errors</h3>
                            <ul class="list-disc pl-5 mt-2 text-sm text-red-700">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Debug Info -->
                    <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="text-sm font-medium text-blue-800">Debug Info</h3>
                        <div class="mt-2 text-xs text-blue-700">
                            <p><strong>Stock ID:</strong> {{ $stockAddition->id }}</p>
                            <p><strong>Condition Status:</strong> {{ $stockAddition->condition_status }}</p>
                            <p><strong>Current Weight:</strong> {{ $stockAddition->weight ?? 'NULL' }}</p>
                            <p><strong>Current Total Pieces:</strong> {{ $stockAddition->total_pieces ?? 'NULL' }}</p>
                            <p><strong>Has Been Issued:</strong> {{ $stockAddition->hasBeenIssued() ? 'YES' : 'NO' }}</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('stock-management.stock-additions.update', $stockAddition) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Product -->
                            <div>
                                <label for="product_id" class="block text-sm font-medium text-gray-700 mb-2">Product</label>
                                <select id="product_id" name="product_id" class="block w-full border-gray-300 rounded-lg">
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ $stockAddition->product_id == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Particulars -->
                            <div>
                                <label for="stone" class="block text-sm font-medium text-gray-700 mb-2">Particulars</label>
                                <input id="stone" name="stone" type="text" class="block w-full border-gray-300 rounded-lg" value="{{ $stockAddition->stone }}" />
                            </div>

                            <!-- Weight (ALWAYS VISIBLE FOR TESTING) -->
                            <div>
                                <label for="weight" class="block text-sm font-medium text-gray-700 mb-2">Weight (kg)</label>
                                <input id="weight" name="weight" type="number" step="0.1" class="block w-full border-gray-300 rounded-lg" value="{{ $stockAddition->weight ?? '' }}" placeholder="Enter weight" />
                            </div>

                            <!-- Total Pieces (ALWAYS VISIBLE FOR TESTING) -->
                            <div>
                                <label for="total_pieces" class="block text-sm font-medium text-gray-700 mb-2">Total Pieces</label>
                                <input id="total_pieces" name="total_pieces" type="number" min="1" class="block w-full border-gray-300 rounded-lg" value="{{ $stockAddition->total_pieces ?? '' }}" placeholder="Enter total pieces" />
                            </div>

                            <!-- Condition Status -->
                            <div>
                                <label for="condition_status" class="block text-sm font-medium text-gray-700 mb-2">Condition Status</label>
                                <select id="condition_status" name="condition_status" class="block w-full border-gray-300 rounded-lg">
                                    @foreach($conditionStatuses as $status)
                                        <option value="{{ $status->name }}" {{ $stockAddition->condition_status == $status->name ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Date -->
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                                <input id="date" name="date" type="date" class="block w-full border-gray-300 rounded-lg" value="{{ $stockAddition->date->format('Y-m-d') }}" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('stock-management.stock-additions.show', $stockAddition) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-6 rounded-lg mr-4">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg">
                                UPDATE STOCK (TEST)
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
