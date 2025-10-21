<x-app-layout>
    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Edit Stock</h1>
                <p class="mt-2 text-gray-600">Update stock addition information</p>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="p-6">

                    @if($errors->any())
                        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">Validation Errors</h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <ul class="list-disc pl-5">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Warning if stock has been issued -->
                    @if($stockAddition->hasBeenIssued())
                        @if($stockAddition->isFullyIssued())
                            <!-- Fully issued warning -->
                            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800">Stock Fully Issued</h3>
                                        <div class="mt-2 text-sm text-red-700">
                                            <p>This stock has been <strong>fully issued</strong> ({{ $stockAddition->stockIssued()->count() }} issuance(s)). No pieces are available for new issuances.</p>
                                            <p class="mt-2 font-medium">⚠️ <strong>Cannot modify quantity-related fields</strong> as this stock is completely issued. Available pieces: {{ $stockAddition->available_pieces }}</p>
                                            <p class="mt-2 font-medium text-green-600">✅ You can still edit: Product Name, Mine Vendor, and Particulars for record keeping purposes.</p>
                        @else
                            <!-- Partially issued warning -->
                        <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                        <h3 class="text-sm font-medium text-yellow-800">Stock Partially Issued</h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                            <p>This stock has been issued {{ $stockAddition->stockIssued()->count() }} time(s) but still has <strong>{{ $stockAddition->available_pieces }} pieces available</strong> for new issuances.</p>
                                        <p class="mt-2 font-medium">⚠️ Proceed with caution when modifying dimensions and quantities as this may affect existing stock issuances.</p>
                                        <p class="mt-2 font-medium text-green-600">✅ You can safely edit: Product Name, Mine Vendor, and Particulars without affecting existing issuances.</p>
                        @endif
                                        @if($stockAddition->stockIssued()->count() > 0)
                                            <div class="mt-3">
                                                <a href="{{ route('stock-management.stock-issued.index', ['search' => $stockAddition->product->name ?? 'N/A']) }}" class="inline-flex items-center text-sm font-medium text-yellow-800 hover:text-yellow-900">
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

                    <form method="POST" action="{{ route('stock-management.stock-additions.update', $stockAddition) }}" id="stock-edit-form" onsubmit="return confirm('Are you sure you want to update this stock?')">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Stock PID -->
                            <div class="md:col-span-2">
                                <x-input-label for="pid" :value="__('Stock PID')" />
                                <x-text-input id="pid" name="pid" type="text" class="mt-1 block w-full" :value="old('pid', $stockAddition->pid)" placeholder="e.g., STK-000001" />
                                <x-input-error :messages="$errors->get('pid')" class="mt-2" />
                                <p class="mt-1 text-sm text-gray-500">Unique identifier for this stock. Leave empty for auto-generation.</p>
                            </div>

                            <!-- Product -->
                            <div>
                                <x-input-label for="product_id" :value="__('Product')" />
                                <select id="product_id" name="product_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm {{ $stockAddition->isFullyIssued() ? 'bg-red-50 border-red-200' : ($stockAddition->hasBeenIssued() ? 'bg-yellow-50 border-yellow-200' : 'bg-white hover:border-gray-400') }}" required {{ $stockAddition->isFullyIssued() ? 'readonly' : '' }}>
                                    <option value="">Select Product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ old('product_id', $stockAddition->product_id) == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($stockAddition->isFullyIssued())
                                    <p class="mt-1 text-xs text-red-600">🚫 Cannot modify - stock is fully issued</p>
                                @elseif($stockAddition->hasBeenIssued())
                                    <p class="mt-1 text-xs text-yellow-600">⚠️ Modify with caution - affects existing stock issuances</p>
                                @endif
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
                                @if($stockAddition->hasBeenIssued())
                                    <p class="mt-1 text-xs text-blue-600">✅ Vendor can be updated even for issued stock</p>
                                @endif
                                <x-input-error :messages="$errors->get('mine_vendor_id')" class="mt-2" />
                            </div>

                            <!-- Particulars -->
                            <div>
                                <x-input-label for="stone" :value="__('Particulars')" />
                                <x-text-input id="stone" name="stone" type="text" class="mt-1 block w-full" :value="old('stone', $stockAddition->stone)" required />
                                @if($stockAddition->hasBeenIssued())
                                    <p class="mt-1 text-xs text-blue-600">✅ Particulars can be updated even for issued stock</p>
                                @endif
                                <x-input-error :messages="$errors->get('stone')" class="mt-2" />
                            </div>

                            <!-- Length -->
                            <div id="length-field" style="display: {{ in_array(strtolower(trim($stockAddition->condition_status)), ['block', 'monuments']) ? 'none' : 'block' }};">
                                <x-input-label for="length" :value="__('Length (cm)')" />
                                <input id="length" name="length" type="number" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 {{ $stockAddition->isFullyIssued() ? 'bg-red-50 border-red-200' : ($stockAddition->hasBeenIssued() ? 'bg-yellow-50 border-yellow-200' : 'bg-white hover:border-gray-400') }}" value="{{ old('length', $stockAddition->length ?? '') }}" placeholder="Enter length in cm" step="0.1" {{ $stockAddition->isFullyIssued() ? 'readonly' : '' }} />
                                @if($stockAddition->isFullyIssued())
                                    <p class="mt-1 text-xs text-red-600">🚫 Cannot modify - stock is fully issued</p>
                                @elseif($stockAddition->hasBeenIssued())
                                    <p class="mt-1 text-xs text-yellow-600">⚠️ Modify with caution - affects existing stock issuances</p>
                                @endif
                                <x-input-error :messages="$errors->get('length')" class="mt-2" />
                            </div>

                            <!-- Height -->
                            <div id="height-field" style="display: {{ in_array(strtolower(trim($stockAddition->condition_status)), ['block', 'monuments']) ? 'none' : 'block' }};">
                                <x-input-label for="height" :value="__('Height (cm)')" />
                                <input id="height" name="height" type="number" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 {{ $stockAddition->isFullyIssued() ? 'bg-red-50 border-red-200' : ($stockAddition->hasBeenIssued() ? 'bg-yellow-50 border-yellow-200' : 'bg-white hover:border-gray-400') }}" value="{{ old('height', $stockAddition->height ?? '') }}" placeholder="Enter height in cm" step="0.1" {{ $stockAddition->isFullyIssued() ? 'readonly' : '' }} />
                                @if($stockAddition->isFullyIssued())
                                    <p class="mt-1 text-xs text-red-600">🚫 Cannot modify - stock is fully issued</p>
                                @elseif($stockAddition->hasBeenIssued())
                                    <p class="mt-1 text-xs text-yellow-600">⚠️ Modify with caution - affects existing stock issuances</p>
                                @endif
                                <x-input-error :messages="$errors->get('height')" class="mt-2" />
                            </div>

                            <!-- Diameter -->
                            <div id="diameter-field" style="display: {{ in_array(strtolower(trim($stockAddition->condition_status)), ['block', 'monuments']) ? 'none' : 'block' }};">
                                <x-input-label for="diameter" :value="__('Diameter/Thickness (cm)')" />
                                <input id="diameter" name="diameter" type="text" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 {{ $stockAddition->isFullyIssued() ? 'bg-red-50 border-red-200' : ($stockAddition->hasBeenIssued() ? 'bg-yellow-50 border-yellow-200' : 'bg-white hover:border-gray-400') }}" value="{{ old('diameter', $stockAddition->diameter ?? '') }}" placeholder="e.g., 6cm, 2cm, 3.5cm" {{ $stockAddition->isFullyIssued() ? 'readonly' : '' }} />
                                @if($stockAddition->isFullyIssued())
                                    <p class="mt-1 text-xs text-red-600">🚫 Cannot modify - stock is fully issued</p>
                                @elseif($stockAddition->hasBeenIssued())
                                    <p class="mt-1 text-xs text-yellow-600">⚠️ Modify with caution - affects existing stock issuances</p>
                                @endif
                                <p class="mt-1 text-xs text-gray-500">Enter the thickness or diameter of the product</p>
                                <x-input-error :messages="$errors->get('diameter')" class="mt-2" />
                            </div>

                            <!-- Weight (for Block and Monuments conditions) -->
                            <div id="weight-field" style="display: {{ in_array(strtolower(trim($stockAddition->condition_status)), ['block', 'monuments']) ? 'block' : 'none' }};">
                                <x-input-label for="weight" :value="__('Weight (kg)')" />
                                <input id="weight" name="weight" type="number" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 {{ $stockAddition->isFullyIssued() ? 'bg-red-50 border-red-200' : ($stockAddition->hasBeenIssued() ? 'bg-yellow-50 border-yellow-200' : 'bg-white hover:border-gray-400') }}" value="{{ old('weight', $stockAddition->weight ?? '') }}" placeholder="Enter weight in kg" step="0.1" {{ $stockAddition->isFullyIssued() ? 'readonly' : '' }} oninput="if(window.updateBlockInfo) window.updateBlockInfo()" onchange="if(window.updateBlockInfo) window.updateBlockInfo()" />
                                @if($stockAddition->isFullyIssued())
                                    <p class="mt-1 text-xs text-red-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                        </svg>
                                        🚫 Cannot modify - stock is fully issued
                                    </p>
                                @elseif($stockAddition->hasBeenIssued())
                                    <p class="mt-1 text-xs text-yellow-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        ⚠️ Modify with caution - affects existing stock issuances
                                    </p>
                                @else
                                    <p class="mt-1 text-xs text-gray-500">Enter the weight per piece in kilograms</p>
                                @endif
                                <x-input-error :messages="$errors->get('weight')" class="mt-2" />
                            </div>

                            <!-- Total Pieces -->
                            <div>
                                <x-input-label for="total_pieces" :value="__('Total Pieces')" />
                                <input id="total_pieces" name="total_pieces" type="number" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 {{ $stockAddition->isFullyIssued() ? 'bg-red-50 border-red-200' : ($stockAddition->hasBeenIssued() ? 'bg-yellow-50 border-yellow-200' : 'bg-white hover:border-gray-400') }}" value="{{ old('total_pieces', $stockAddition->total_pieces) }}" min="1" required {{ $stockAddition->isFullyIssued() ? 'readonly' : '' }} oninput="if(window.updateBlockInfo) window.updateBlockInfo()" onchange="if(window.updateBlockInfo) window.updateBlockInfo()" />
                                @if($stockAddition->isFullyIssued())
                                    <p class="mt-1 text-xs text-red-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                        </svg>
                                        🚫 Cannot modify - stock is fully issued
                                    </p>
                                @elseif($stockAddition->hasBeenIssued())
                                    <p class="mt-1 text-xs text-yellow-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        ⚠️ Modify with caution - affects existing stock issuances
                                    </p>
                                @else
                                    <p class="mt-1 text-xs text-gray-500">Enter the total number of pieces</p>
                                @endif
                                <x-input-error :messages="$errors->get('total_pieces')" class="mt-2" />
                            </div>

                            <!-- Available Pieces (Editable) -->
                            <div>
                                <x-input-label for="available_pieces" :value="__('Available Pieces')" />
                                <input id="available_pieces" name="available_pieces" type="number" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-colors duration-200 bg-green-50 hover:bg-green-100" value="{{ old('available_pieces', $stockAddition->available_pieces) }}" min="0" max="{{ $stockAddition->total_pieces }}" />
                                <p class="mt-1 text-xs text-green-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    Edit to manually correct available pieces (Max: <span id="max_available_pieces">{{ $stockAddition->total_pieces }}</span>)
                                </p>
                                <x-input-error :messages="$errors->get('available_pieces')" class="mt-2" />
                            </div>

                            <!-- Available Sqft (Editable) -->
                            <div>
                                <x-input-label for="available_sqft" :value="__('Available Sqft')" />
                                <input id="available_sqft" name="available_sqft" type="number" step="0.01" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-colors duration-200 bg-green-50 hover:bg-green-100" value="{{ old('available_sqft', $stockAddition->available_sqft) }}" min="0" max="{{ $stockAddition->total_sqft ?? 0 }}" />
                                <p class="mt-1 text-xs text-green-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    Edit to manually correct available sqft (Max: <span id="max_available_sqft">{{ number_format($stockAddition->total_sqft ?? 0, 2) }}</span>)
                                </p>
                                <x-input-error :messages="$errors->get('available_sqft')" class="mt-2" />
                            </div>

                            <!-- Available Weight (Editable) -->
                            <div>
                                <x-input-label for="available_weight" :value="__('Available Weight (kg)')" />
                                @php
                                    $totalWeight = ($stockAddition->weight ?? 0) * ($stockAddition->total_pieces ?? 0);
                                @endphp
                                <input id="available_weight" name="available_weight" type="number" step="0.01" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-colors duration-200 bg-green-50 hover:bg-green-100" value="{{ old('available_weight', $stockAddition->available_weight) }}" min="0" max="{{ $totalWeight }}" />
                                <p class="mt-1 text-xs text-green-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    Edit to manually correct available weight (Max: <span id="max_available_weight">{{ number_format($totalWeight, 2) }}</span> kg)
                                </p>
                                <x-input-error :messages="$errors->get('available_weight')" class="mt-2" />
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

                            <!-- Size Information Display -->
                            <div id="size-info-section" class="md:col-span-2" style="display: {{ in_array(strtolower(trim($stockAddition->condition_status)), ['block', 'monuments']) ? 'none' : 'block' }};">
                                <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                                    <h3 class="text-sm font-medium text-green-900 mb-3">Size Information</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-green-700 mb-1">Single Piece Size</label>
                                            <div id="single_piece_size" class="text-sm text-green-600 bg-white p-2 rounded border">
                                                @if($stockAddition->length && $stockAddition->height)
                                                    <span class="font-medium text-blue-600">{{ number_format($stockAddition->length * $stockAddition->height, 2) }} cm²</span><br>
                                                    <span class="text-xs text-gray-500">{{ $stockAddition->length }} × {{ $stockAddition->height }} cm</span>
                                                @else
                                                    <span class="text-gray-400">Enter dimensions to see size</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-green-700 mb-1">Single Piece (sqft)</label>
                                            <div id="single_piece_sqft" class="text-sm text-green-600 bg-white p-2 rounded border">
                                                @if($stockAddition->length && $stockAddition->height)
                                                    @php
                                                        $cmToSqft = 0.00107639;
                                                        $singlePieceSizeCm = $stockAddition->length * $stockAddition->height;
                                                        $singlePieceSizeSqft = $singlePieceSizeCm * $cmToSqft;
                                                    @endphp
                                                    <span class="font-medium text-green-600">{{ number_format($singlePieceSizeSqft, 4) }} sqft</span>
                                                @else
                                                    <span class="text-gray-400">Enter dimensions to see size</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-green-700 mb-1">Total Size (sqft)</label>
                                            <div id="total_size_display" class="text-sm text-green-600 bg-white p-2 rounded border">
                                                @if($stockAddition->length && $stockAddition->height && $stockAddition->total_pieces)
                                                    @php
                                                        $cmToSqft = 0.00107639;
                                                        $singlePieceSizeCm = $stockAddition->length * $stockAddition->height;
                                                        $singlePieceSizeSqft = $singlePieceSizeCm * $cmToSqft;
                                                        $totalSizeSqft = $singlePieceSizeSqft * $stockAddition->total_pieces;
                                                    @endphp
                                                    <span class="font-medium text-purple-600">{{ number_format($totalSizeSqft, 4) }} sqft</span><br>
                                                    <span class="text-xs text-gray-500">{{ $stockAddition->total_pieces }} pieces</span>
                                                @else
                                                    <span class="text-gray-400">Enter dimensions and pieces</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Weight Information Display -->
                            <div id="block-info-section" class="md:col-span-2" style="display: {{ in_array(strtolower(trim($stockAddition->condition_status)), ['block', 'monuments']) ? 'block' : 'none' }};">
                                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                    <h3 class="text-sm font-medium text-blue-900 mb-3">
                                        @if(strtolower(trim($stockAddition->condition_status)) === 'monuments')
                                            Monuments Information
                                        @else
                                            Block Information
                                        @endif
                                    </h3>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-blue-700 mb-1">Per Piece Weight</label>
                                            <div id="single_piece_weight" class="text-sm text-blue-600 bg-white p-2 rounded border">
                                                <span class="text-gray-400">{{ $stockAddition->weight ?? '0' }} kg</span>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-blue-700 mb-1">Total Weight</label>
                                            <div id="total_weight_display" class="text-sm text-blue-600 bg-white p-2 rounded border">
                                                <span class="font-medium text-blue-600">{{ isset($stockAddition->weight, $stockAddition->total_pieces) ? number_format($stockAddition->weight * $stockAddition->total_pieces, 2) : '0' }} kg</span>
                                                @if($stockAddition->weight && $stockAddition->total_pieces)
                                                <br><span class="text-xs text-gray-500">{{ $stockAddition->weight }} kg × {{ $stockAddition->total_pieces }} pieces</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-blue-700 mb-1">Total Pieces</label>
                                            <div id="total_pieces_display" class="text-sm text-blue-600 bg-white p-2 rounded border">
                                                <span class="font-medium text-green-600">{{ $stockAddition->total_pieces ?? '0' }} pieces</span>
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
            const conditionStatusSelect = document.getElementById('condition_status');
            const lengthInput = document.getElementById('length');
            const heightInput = document.getElementById('height');
            const weightInput = document.getElementById('weight');
            const totalPiecesInput = document.getElementById('total_pieces');
            const totalSqftInput = document.getElementById('total_sqft');
            
            // Check if elements exist
            console.log('Elements found:');
            console.log('- Weight input:', weightInput ? 'YES' : 'NO');
            console.log('- Total pieces input:', totalPiecesInput ? 'YES' : 'NO');
            console.log('- Condition status select:', conditionStatusSelect ? 'YES' : 'NO');
            
            // Get current values
            const currentCondition = conditionStatusSelect ? conditionStatusSelect.value.toLowerCase().trim() : '';
            const modelWeight = parseFloat('{{ $stockAddition->weight }}') || 0;
            const modelPieces = parseInt('{{ $stockAddition->total_pieces }}') || 0;
            
            console.log('Current condition:', currentCondition);
            console.log('Model weight:', modelWeight);
            console.log('Model pieces:', modelPieces);
            
            // SIMPLE APPROACH: Directly set values for Block condition
            if (currentCondition === 'block' && weightInput && totalPiecesInput) {
                console.log('Setting values for Block condition...');
                
                // Set values directly
                weightInput.value = modelWeight;
                totalPiecesInput.value = modelPieces;
                
                console.log('Values set:');
                console.log('- Weight input value:', weightInput.value);
                console.log('- Total pieces input value:', totalPiecesInput.value);
                
                // Force display of block fields
            const weightField = document.getElementById('weight-field');
            const blockInfoSection = document.getElementById('block-info-section');
                const sizeInfoSection = document.getElementById('size-info-section');
                
                if (weightField) weightField.style.display = 'block';
                if (blockInfoSection) blockInfoSection.style.display = 'block';
                if (sizeInfoSection) sizeInfoSection.style.display = 'none';
                
                console.log('Block fields displayed');
            }
            
            // Simple toggle function for condition changes
            function toggleFields() {
                const conditionStatus = conditionStatusSelect.value.toLowerCase().trim();
                const isBlock = conditionStatus === 'block' || conditionStatus === 'monuments';

                const lengthField = document.getElementById('length-field');
                const heightField = document.getElementById('height-field');
                const diameterField = document.getElementById('diameter-field');
                const weightField = document.getElementById('weight-field');
                const sizeInfoSection = document.getElementById('size-info-section');
                const blockInfoSection = document.getElementById('block-info-section');
                const totalSqftField = document.getElementById('total-sqft-field');

                if (isBlock) {
                    // Show block fields
                    if (lengthField) lengthField.style.display = 'none';
                    if (heightField) heightField.style.display = 'none';
                    if (diameterField) diameterField.style.display = 'none';
                    if (weightField) weightField.style.display = 'block';
                    if (sizeInfoSection) sizeInfoSection.style.display = 'none';
                    if (blockInfoSection) blockInfoSection.style.display = 'block';
                    if (totalSqftField) totalSqftField.style.display = 'none';
                } else {
                    // Show size fields
                    if (lengthField) lengthField.style.display = 'block';
                    if (heightField) heightField.style.display = 'block';
                    if (diameterField) diameterField.style.display = 'block';
                    if (weightField) weightField.style.display = 'none';
                    if (sizeInfoSection) sizeInfoSection.style.display = 'block';
                    if (blockInfoSection) blockInfoSection.style.display = 'none';
                    if (totalSqftField) totalSqftField.style.display = 'block';
                }
            }

            function calculateSize() {
                const length = parseFloat(lengthInput.value) || 0;
                const height = parseFloat(heightInput.value) || 0;
                const pieces = parseInt(totalPiecesInput.value) || 0;

                console.log('=== CALCULATE SIZE CALLED ===');
                console.log('Length:', length, 'Height:', height, 'Pieces:', pieces);

                // Get display elements
                const singlePieceSizeDisplay = document.getElementById('single_piece_size');
                const singlePieceSqftDisplay = document.getElementById('single_piece_sqft');
                const totalSizeDisplay = document.getElementById('total_size_display');
                const totalSqftInput = document.getElementById('total_sqft');
                
                console.log('Display elements found:');
                console.log('- singlePieceSizeDisplay:', singlePieceSizeDisplay ? 'YES' : 'NO');
                console.log('- singlePieceSqftDisplay:', singlePieceSqftDisplay ? 'YES' : 'NO');
                console.log('- totalSizeDisplay:', totalSizeDisplay ? 'YES' : 'NO');
                console.log('- totalSqftInput:', totalSqftInput ? 'YES' : 'NO');

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
                    if (singlePieceSizeDisplay) {
                    singlePieceSizeDisplay.innerHTML = `<span class="font-medium text-blue-600">${singlePieceSizeCm.toFixed(2)} cm²</span><br><span class="text-xs text-gray-500">${length} × ${height} cm</span>`;
                        console.log('Updated singlePieceSizeDisplay');
                    }
                    if (singlePieceSqftDisplay) {
                    singlePieceSqftDisplay.innerHTML = `<span class="font-medium text-green-600">${singlePieceSizeSqft.toFixed(4)} sqft</span>`;
                        console.log('Updated singlePieceSqftDisplay');
                    }
                    
                    if (pieces > 0) {
                        if (totalSizeDisplay) {
                        totalSizeDisplay.innerHTML = `<span class="font-medium text-purple-600">${totalSizeSqft.toFixed(4)} sqft</span><br><span class="text-xs text-gray-500">${pieces} pieces</span>`;
                            console.log('Updated totalSizeDisplay');
                        }
                        if (totalSqftInput) {
                        totalSqftInput.value = totalSizeSqft.toFixed(4);
                            console.log('Updated totalSqftInput:', totalSqftInput.value);
                        }
                    } else {
                        if (totalSizeDisplay) {
                        totalSizeDisplay.innerHTML = '<span class="text-gray-400">Enter number of pieces</span>';
                        }
                        if (totalSqftInput) {
                        totalSqftInput.value = '';
                    }
                    }
                    
                    console.log('Size calculation completed successfully');
                } else {
                    // Reset displays
                    if (singlePieceSizeDisplay) {
                    singlePieceSizeDisplay.innerHTML = '<span class="text-gray-400">Enter dimensions to see size</span>';
                    }
                    if (singlePieceSqftDisplay) {
                    singlePieceSqftDisplay.innerHTML = '<span class="text-gray-400">Enter dimensions to see size</span>';
                    }
                    if (totalSizeDisplay) {
                    totalSizeDisplay.innerHTML = '<span class="text-gray-400">Enter dimensions and pieces</span>';
                    }
                    if (totalSqftInput) {
                    totalSqftInput.value = '';
                    }
                }
            }

            function calculateBlock() {
                const weight = parseFloat(weightInput.value) || 0;
                const pieces = parseInt(totalPiecesInput.value) || 0;

                console.log('calculateBlock called - Weight:', weight, 'Pieces:', pieces);
                console.log('weightInput.value:', weightInput.value);
                console.log('totalPiecesInput.value:', totalPiecesInput.value);

                // Get display elements
                const singlePieceWeightDisplay = document.getElementById('single_piece_weight');
                const totalWeightDisplay = document.getElementById('total_weight_display');
                const totalPiecesDisplay = document.getElementById('total_pieces_display');

                if (weight > 0 && pieces > 0) {
                    const totalWeight = weight * pieces;
                    
                    // Update displays with real-time calculations
                    if (singlePieceWeightDisplay) {
                        singlePieceWeightDisplay.innerHTML = `<span class="font-medium text-blue-600">${weight.toFixed(2)} kg</span>`;
                    }
                    
                    if (totalWeightDisplay) {
                    totalWeightDisplay.innerHTML = `<span class="font-medium text-blue-600">${totalWeight.toFixed(2)} kg</span><br><span class="text-xs text-gray-500">${weight} kg × ${pieces} pieces</span>`;
                    }
                    
                    if (totalPiecesDisplay) {
                    totalPiecesDisplay.innerHTML = `<span class="font-medium text-green-600">${pieces} pieces</span>`;
                    }
                    
                    console.log('Block calculation updated - Total Weight:', totalWeight);
                } else {
                    // Reset displays
                    if (singlePieceWeightDisplay) {
                        singlePieceWeightDisplay.innerHTML = `<span class="text-gray-400">0 kg</span>`;
                    }
                    if (totalWeightDisplay) {
                    totalWeightDisplay.innerHTML = '<span class="font-medium text-blue-600">0 kg</span><br><span class="text-xs text-gray-500">Enter weight and pieces</span>';
                    }
                    if (totalPiecesDisplay) {
                    totalPiecesDisplay.innerHTML = '<span class="text-gray-400">0 pieces</span>';
                    }
                    console.log('Block calculation reset - values too low');
                }
            }

            function clearSizeCalculations() {
                const singlePieceSizeDisplay = document.getElementById('single_piece_size');
                const singlePieceSqftDisplay = document.getElementById('single_piece_sqft');
                const totalSizeDisplay = document.getElementById('total_size_display');
                const totalSqftInput = document.getElementById('total_sqft');
                
                if (singlePieceSizeDisplay) {
                singlePieceSizeDisplay.innerHTML = '<span class="text-gray-400">Enter dimensions to see size</span>';
                }
                if (singlePieceSqftDisplay) {
                singlePieceSqftDisplay.innerHTML = '<span class="text-gray-400">Enter dimensions to see size</span>';
                }
                if (totalSizeDisplay) {
                totalSizeDisplay.innerHTML = '<span class="text-gray-400">Enter dimensions and pieces</span>';
                }
                if (totalSqftInput) {
                totalSqftInput.value = '';
                }
            }

            function clearBlockCalculations() {
                const singlePieceWeightDisplay = document.getElementById('single_piece_weight');
                const totalWeightDisplay = document.getElementById('total_weight_display');
                const totalPiecesDisplay = document.getElementById('total_pieces_display');
                
                if (singlePieceWeightDisplay) {
                    singlePieceWeightDisplay.innerHTML = '<span class="text-gray-400">0 kg</span>';
                }
                if (totalWeightDisplay) {
                totalWeightDisplay.innerHTML = '<span class="font-medium text-blue-600">0 kg</span><br><span class="text-xs text-gray-500">Enter weight and pieces</span>';
                }
                if (totalPiecesDisplay) {
                totalPiecesDisplay.innerHTML = '<span class="text-gray-400">0 pieces</span>';
                }
            }

            // Add event listeners with enhanced debugging
            conditionStatusSelect.addEventListener('change', function() {
                console.log('Condition status changed to:', conditionStatusSelect.value);
                toggleFields();
            });
            
            if (lengthInput) {
                lengthInput.addEventListener('input', function() {
                    console.log('Length input changed:', lengthInput.value);
                    calculateSize();
                });
                lengthInput.addEventListener('change', function() {
                    console.log('Length input changed:', lengthInput.value);
                    calculateSize();
                });
            }
            
            if (heightInput) {
                heightInput.addEventListener('input', function() {
                    console.log('Height input changed:', heightInput.value);
                    calculateSize();
                });
                heightInput.addEventListener('change', function() {
                    console.log('Height input changed:', heightInput.value);
                    calculateSize();
                });
            }
            
            if (weightInput) {
                weightInput.addEventListener('input', function() {
                    console.log('Weight input changed:', weightInput.value);
                    calculateBlock();
                });
                weightInput.addEventListener('change', function() {
                    console.log('Weight input changed:', weightInput.value);
                    calculateBlock();
                });
                
                // Also trigger on keyup for immediate feedback
                weightInput.addEventListener('keyup', function() {
                    console.log('Weight keyup:', weightInput.value);
                    calculateBlock();
                });
            }
            
            if (totalPiecesInput) {
                totalPiecesInput.addEventListener('input', function() {
                    console.log('Total pieces input changed:', totalPiecesInput.value);
                    const conditionStatus = conditionStatusSelect.value.toLowerCase().trim();
                    if (conditionStatus === 'block' || conditionStatus === 'monuments') {
                        console.log('Triggering Block calculation from pieces input');
                        calculateBlock();
                    } else {
                        console.log('Triggering Size calculation from pieces input');
                        calculateSize();
                    }
                });
                
                totalPiecesInput.addEventListener('change', function() {
                    console.log('Total pieces changed:', totalPiecesInput.value);
                    const conditionStatus = conditionStatusSelect.value.toLowerCase().trim();
                    if (conditionStatus === 'block' || conditionStatus === 'monuments') {
                        calculateBlock();
                    } else {
                        calculateSize();
                    }
                });
                
                // Also trigger on keyup for immediate feedback
                totalPiecesInput.addEventListener('keyup', function() {
                    console.log('Total pieces keyup:', totalPiecesInput.value);
                    const conditionStatus = conditionStatusSelect.value.toLowerCase().trim();
                    if (conditionStatus === 'block' || conditionStatus === 'monuments') {
                        calculateBlock();
                    } else {
                        calculateSize();
                    }
                });
            }

            // Initialize fields on page load (preserve existing values)
            toggleFields(true);
            
            // Immediately trigger calculations based on condition
            setTimeout(function() {
                const currentCondition = conditionStatusSelect.value.toLowerCase().trim();
                
                if (currentCondition === 'block' || currentCondition === 'monuments') {
                    console.log('=== IMMEDIATE BLOCK CALCULATION ===');
                    console.log('Weight input value:', weightInput ? weightInput.value : 'NOT FOUND');
                    console.log('Pieces input value:', totalPiecesInput ? totalPiecesInput.value : 'NOT FOUND');
                    calculateBlock();
                    
                    // Force calculation multiple times to ensure it works
                    setTimeout(calculateBlock, 50);
                    setTimeout(calculateBlock, 150);
                    setTimeout(calculateBlock, 300);
                } else {
                    console.log('=== IMMEDIATE SIZE CALCULATION ===');
                    
                    // Get input elements
                    const lengthInput = document.getElementById('length');
                    const heightInput = document.getElementById('height');
                    const diameterInput = document.getElementById('diameter');
                    const totalPiecesInput = document.getElementById('total_pieces');
                    
                    console.log('Length input value:', lengthInput ? lengthInput.value : 'NOT FOUND');
                    console.log('Height input value:', heightInput ? heightInput.value : 'NOT FOUND');
                    console.log('Pieces input value:', totalPiecesInput ? totalPiecesInput.value : 'NOT FOUND');
                    
                    // Set initial values for non-block conditions
                    const modelLength = parseFloat('{{ $stockAddition->length }}') || 0;
                    const modelHeight = parseFloat('{{ $stockAddition->height }}') || 0;
                    const modelDiameter = '{{ $stockAddition->diameter }}';
                    const modelPieces = parseInt('{{ $stockAddition->total_pieces }}') || 0;
                    
                    console.log('Model values - Length:', modelLength, 'Height:', modelHeight, 'Diameter:', modelDiameter, 'Pieces:', modelPieces);
                    
                    if (lengthInput && !lengthInput.value && modelLength > 0) {
                        lengthInput.value = modelLength;
                        console.log('Force setting length to:', modelLength);
                    }
                    
                    if (heightInput && !heightInput.value && modelHeight > 0) {
                        heightInput.value = modelHeight;
                        console.log('Force setting height to:', modelHeight);
                    }
                    
                    if (diameterInput && !diameterInput.value && modelDiameter) {
                        diameterInput.value = modelDiameter;
                        console.log('Force setting diameter to:', modelDiameter);
                    }
                    
                    if (totalPiecesInput && !totalPiecesInput.value && modelPieces > 0) {
                        totalPiecesInput.value = modelPieces;
                        console.log('Force setting pieces to:', modelPieces);
                    }
                    
                    // Trigger size calculation
                    calculateSize();
                    
                    // Force calculation multiple times to ensure it works
                    setTimeout(calculateSize, 50);
                    setTimeout(calculateSize, 150);
                    setTimeout(calculateSize, 300);
                }
            }, 100);
            
            // Force display of correct fields based on current condition status
            const conditionForDisplay = conditionStatusSelect.value.toLowerCase().trim();
            console.log('Forcing display for condition:', conditionForDisplay);
            console.log('Raw condition status value:', conditionStatusSelect.value);
            
            // Get all field elements
            const weightField = document.getElementById('weight-field');
            const blockInfoSection = document.getElementById('block-info-section');
            const sizeInfoSection = document.getElementById('size-info-section');
            const totalSqftField = document.getElementById('total-sqft-field');
            const lengthField = document.getElementById('length-field');
            const heightField = document.getElementById('height-field');
            const diameterField = document.getElementById('diameter-field');
            
            if (conditionForDisplay === 'block' || conditionForDisplay === 'monuments') {
                console.log('Showing block/monuments fields');
                if (weightField) weightField.style.display = 'block';
                if (blockInfoSection) blockInfoSection.style.display = 'block';
                if (sizeInfoSection) sizeInfoSection.style.display = 'none';
                if (totalSqftField) totalSqftField.style.display = 'none';
                if (lengthField) lengthField.style.display = 'none';
                if (heightField) heightField.style.display = 'none';
                if (diameterField) diameterField.style.display = 'none';
                
                // Clear dimension fields and set to empty for Block/Monuments condition
                if (lengthInput) lengthInput.value = '';
                if (heightInput) heightInput.value = '';
                if (totalSqftInput) totalSqftInput.value = '';
                
                // Set weight and pieces values - FORCE SET VALUES
                const modelWeight = parseFloat('{{ $stockAddition->weight }}') || 0;
                const modelPieces = parseInt('{{ $stockAddition->total_pieces }}') || 0;
                
                console.log('Setting Block values - Weight:', modelWeight, 'Pieces:', modelPieces);
                console.log('Raw weight from model: {{ $stockAddition->weight }}');
                console.log('Raw pieces from model: {{ $stockAddition->total_pieces }}');
                
                // Force set values multiple times to ensure they stick
                setTimeout(function() {
                    if (weightInput && modelWeight > 0) {
                        weightInput.value = modelWeight.toString();
                        console.log('Weight input FORCED set to:', weightInput.value);
                    }
                    
                    if (totalPiecesInput && modelPieces > 0) {
                        totalPiecesInput.value = modelPieces.toString();
                        console.log('Pieces input FORCED set to:', totalPiecesInput.value);
                    }
                }, 50);
                
                setTimeout(function() {
                    if (weightInput && modelWeight > 0) {
                        weightInput.value = modelWeight.toString();
                        console.log('Weight input FORCED set AGAIN to:', weightInput.value);
                    }
                    
                    if (totalPiecesInput && modelPieces > 0) {
                        totalPiecesInput.value = modelPieces.toString();
                        console.log('Pieces input FORCED set AGAIN to:', totalPiecesInput.value);
                    }
                }, 200);
                
                // Immediate set
                if (weightInput && modelWeight > 0) {
                    weightInput.value = modelWeight.toString();
                    console.log('Weight input IMMEDIATE set to:', weightInput.value);
                }
                
                if (totalPiecesInput && modelPieces > 0) {
                    totalPiecesInput.value = modelPieces.toString();
                    console.log('Pieces input IMMEDIATE set to:', totalPiecesInput.value);
                }
            } else {
                console.log('Showing size fields');
                if (weightField) weightField.style.display = 'none';
                if (blockInfoSection) blockInfoSection.style.display = 'none';
                if (sizeInfoSection) sizeInfoSection.style.display = 'block';
                if (totalSqftField) totalSqftField.style.display = 'block';
                if (lengthField) lengthField.style.display = 'block';
                if (heightField) heightField.style.display = 'block';
                if (diameterField) diameterField.style.display = 'block';
                
                // Clear weight field for non-Block conditions
                if (weightInput) weightInput.value = '';
                
                // Set dimension values
                const modelLength = parseFloat('{{ $stockAddition->length }}') || 0;
                const modelHeight = parseFloat('{{ $stockAddition->height }}') || 0;
                const modelTotalSqft = parseFloat('{{ $stockAddition->total_sqft }}') || 0;
                
                if (lengthInput) lengthInput.value = modelLength > 0 ? modelLength : '';
                if (heightInput) heightInput.value = modelHeight > 0 ? modelHeight : '';
                if (totalSqftInput) totalSqftInput.value = modelTotalSqft > 0 ? modelTotalSqft : '';
            }

            // Load existing data on page load
            const conditionStatus = conditionStatusSelect.value.toLowerCase().trim();
            console.log('Current condition status:', conditionStatus);
            
            if (conditionStatus === 'block' || conditionStatus === 'monuments') {
                // For block condition, load weight data
                const existingWeight = parseFloat(weightInput.value) || 0;
                const existingPieces = parseInt(totalPiecesInput.value) || 0;
                
                console.log('Block condition - Weight:', existingWeight, 'Pieces:', existingPieces);
                console.log('Weight input value:', weightInput.value);
                console.log('Pieces input value:', totalPiecesInput.value);
                
                // Force set values ALWAYS for Block condition
                const modelWeight = parseFloat('{{ $stockAddition->weight }}') || 0;
                const modelPieces = parseInt('{{ $stockAddition->total_pieces }}') || 0;
                
                console.log('Setting values from model - Weight:', modelWeight, 'Pieces:', modelPieces);
                
                if (weightInput && weightInput.value !== modelWeight.toString()) {
                    weightInput.value = modelWeight;
                    console.log('Weight input set to:', weightInput.value);
                }
                
                if (totalPiecesInput && totalPiecesInput.value !== modelPieces.toString()) {
                    totalPiecesInput.value = modelPieces;
                    console.log('Pieces input set to:', totalPiecesInput.value);
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
                
                // Force trigger calculation immediately
                setTimeout(function() {
                    calculateBlock();
                }, 100);
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
                    
                    // Get display elements
                    const singlePieceSizeDisplay = document.getElementById('single_piece_size');
                    const singlePieceSqftDisplay = document.getElementById('single_piece_sqft');
                    const totalSizeDisplay = document.getElementById('total_size_display');
                    
                    if (singlePieceSizeDisplay) {
                    singlePieceSizeDisplay.innerHTML = `<span class="font-medium text-blue-600">${singlePieceSizeCm.toFixed(2)} cm²</span><br><span class="text-xs text-gray-500">${existingLength} × ${existingHeight} cm</span>`;
                    }
                    if (singlePieceSqftDisplay) {
                    singlePieceSqftDisplay.innerHTML = `<span class="font-medium text-green-600">${singlePieceSizeSqft.toFixed(4)} sqft</span>`;
                    }
                    
                    if (existingPieces > 0) {
                        if (totalSizeDisplay) {
                        totalSizeDisplay.innerHTML = `<span class="font-medium text-purple-600">${totalSizeSqft.toFixed(4)} sqft</span><br><span class="text-xs text-gray-500">${existingPieces} pieces</span>`;
                        }
                    }
                }
                
                // Calculate size information
                calculateSize();
            }
            
            // Final check and force display
            setTimeout(function() {
                console.log('=== FINAL DEBUG CHECK ===');
                console.log('Weight input value:', weightInput ? weightInput.value : 'NOT FOUND');
                console.log('Total pieces value:', totalPiecesInput ? totalPiecesInput.value : 'NOT FOUND');
                console.log('Condition status:', conditionStatusSelect.value.toLowerCase().trim());
                
                // Force display for Block condition ALWAYS
                if (conditionStatusSelect.value.toLowerCase().trim() === 'block' || conditionStatusSelect.value.toLowerCase().trim() === 'monuments') {
                    const modelWeight = parseFloat('{{ $stockAddition->weight }}') || 0;
                    const modelPieces = parseInt('{{ $stockAddition->total_pieces }}') || 0;
                    
                    console.log('Model values - Weight:', modelWeight, 'Pieces:', modelPieces);
                    
                    if (weightInput && !weightInput.value) {
                        weightInput.value = modelWeight;
                        console.log('Force setting weight to:', modelWeight);
                    }
                    
                    if (totalPiecesInput && !totalPiecesInput.value) {
                        totalPiecesInput.value = modelPieces;
                        console.log('Force setting pieces to:', modelPieces);
                    }
                    
                    // Trigger multiple calculations
                    calculateBlock();
                    
                    // Force display values
                    const finalWeight = parseFloat(weightInput.value) || 0;
                    const finalPieces = parseInt(totalPiecesInput.value) || 0;
                    
                    console.log('Final values after setting - Weight:', finalWeight, 'Pieces:', finalPieces);
                    
                    if (finalWeight > 0) {
                        const totalWeight = finalWeight * finalPieces;
                        totalWeightDisplay.innerHTML = `<span class="font-medium text-blue-600">${totalWeight.toFixed(2)} kg</span><br><span class="text-xs text-gray-500">${finalWeight} kg × ${finalPieces} pieces</span>`;
                        console.log('Total Weight display updated:', totalWeight);
                    }
                    
                    if (finalPieces > 0) {
                        totalPiecesDisplay.innerHTML = `<span class="font-medium text-green-600">${finalPieces} pieces</span>`;
                        console.log('Total Pieces display updated:', finalPieces);
                    }
                    
                    // Also trigger input events to make sure calculations run
                    if (weightInput) {
                        weightInput.dispatchEvent(new Event('input'));
                    }
                    if (totalPiecesInput) {
                        totalPiecesInput.dispatchEvent(new Event('input'));
                    }
                }
                console.log('=== END DEBUG CHECK ===');
            }, 1000);
            
            // MANUAL TEST TRIGGER - Remove this later
            console.log('=== MANUAL TEST SETUP ===');
            window.testBlockCalculation = function() {
                console.log('Manual test called');
                calculateBlock();
            };
            window.triggerCalculation = function() {
                if (conditionStatusSelect.value.toLowerCase().trim() === 'block' || conditionStatusSelect.value.toLowerCase().trim() === 'monuments') {
                    console.log('Triggering Block calculation manually');
                    calculateBlock();
                } else {
                    console.log('Triggering Size calculation manually');
                    calculateSize();
                }
            };
            
            // Make functions globally available
            window.calculateSize = calculateSize;
            window.calculateBlock = calculateBlock;
            console.log('Manual test functions available:');
            console.log('- testBlockCalculation()');
            console.log('- triggerCalculation()');
            console.log('Input values - Weight:', weightInput ? weightInput.value : 'NULL', 'Pieces:', totalPiecesInput ? totalPiecesInput.value : 'NULL');
            
            // FINAL CHECK - Force set values one more time if they're empty
            setTimeout(function() {
                const conditionForFinalCheck = conditionStatusSelect.value.toLowerCase().trim();
                if (conditionForFinalCheck === 'block' || conditionForFinalCheck === 'monuments') {
                    const modelWeight = parseFloat('{{ $stockAddition->weight }}') || 0;
                    const modelPieces = parseInt('{{ $stockAddition->total_pieces }}') || 0;
                    
                    console.log('=== FINAL CHECK - BLOCK ===');
                    console.log('Current weight value:', weightInput ? weightInput.value : 'NULL');
                    console.log('Current pieces value:', totalPiecesInput ? totalPiecesInput.value : 'NULL');
                    console.log('Model weight:', modelWeight);
                    console.log('Model pieces:', modelPieces);
                    
                    if (weightInput && (!weightInput.value || weightInput.value === '') && modelWeight > 0) {
                        weightInput.value = modelWeight.toString();
                        console.log('FINAL: Weight input set to:', weightInput.value);
                    }
                    
                    if (totalPiecesInput && (!totalPiecesInput.value || totalPiecesInput.value === '') && modelPieces > 0) {
                        totalPiecesInput.value = modelPieces.toString();
                        console.log('FINAL: Pieces input set to:', totalPiecesInput.value);
                    }
                    
                    // Trigger calculation
                    calculateBlock();
                } else {
                    // Final check for non-block conditions
                    const modelLength = parseFloat('{{ $stockAddition->length }}') || 0;
                    const modelHeight = parseFloat('{{ $stockAddition->height }}') || 0;
                    const modelDiameter = '{{ $stockAddition->diameter }}';
                    const modelPieces = parseInt('{{ $stockAddition->total_pieces }}') || 0;
                    
                    console.log('=== FINAL CHECK - SIZE ===');
                    console.log('Current length value:', lengthInput ? lengthInput.value : 'NULL');
                    console.log('Current height value:', heightInput ? heightInput.value : 'NULL');
                    console.log('Current diameter value:', diameterInput ? diameterInput.value : 'NULL');
                    console.log('Current pieces value:', totalPiecesInput ? totalPiecesInput.value : 'NULL');
                    console.log('Model values - Length:', modelLength, 'Height:', modelHeight, 'Diameter:', modelDiameter, 'Pieces:', modelPieces);
                    
                    if (lengthInput && (!lengthInput.value || lengthInput.value === '') && modelLength > 0) {
                        lengthInput.value = modelLength.toString();
                        console.log('FINAL: Length input set to:', lengthInput.value);
                    }
                    
                    if (heightInput && (!heightInput.value || heightInput.value === '') && modelHeight > 0) {
                        heightInput.value = modelHeight.toString();
                        console.log('FINAL: Height input set to:', heightInput.value);
                    }
                    
                    if (diameterInput && (!diameterInput.value || diameterInput.value === '') && modelDiameter) {
                        diameterInput.value = modelDiameter;
                        console.log('FINAL: Diameter input set to:', diameterInput.value);
                    }
                    
                    if (totalPiecesInput && (!totalPiecesInput.value || totalPiecesInput.value === '') && modelPieces > 0) {
                        totalPiecesInput.value = modelPieces.toString();
                        console.log('FINAL: Pieces input set to:', totalPiecesInput.value);
                    }
                    
                    // Trigger calculation
                    calculateSize();
                }
            }, 500);
        });
        
        // FORCE SET VALUES FOR NON-BLOCK CONDITIONS
        document.addEventListener('DOMContentLoaded', function() {
            console.log('=== FORCE SETTING VALUES ===');
            
            // Wait for all elements to be available
            setTimeout(function() {
                // Get the condition status
                const conditionStatusSelect = document.getElementById('condition_status');
                const currentCondition = conditionStatusSelect ? conditionStatusSelect.value.toLowerCase().trim() : '';
                
                console.log('Current condition:', currentCondition);
                
                // For non-block conditions, force set the values
                if (currentCondition !== 'block' && currentCondition !== 'monuments') {
                    const lengthInput = document.getElementById('length');
                    const heightInput = document.getElementById('height');
                    const diameterInput = document.getElementById('diameter');
                    const totalPiecesInput = document.getElementById('total_pieces');
                    
                    // Get model values
                    const modelLength = parseFloat('{{ $stockAddition->length }}') || 0;
                    const modelHeight = parseFloat('{{ $stockAddition->height }}') || 0;
                    const modelDiameter = '{{ $stockAddition->diameter }}';
                    const modelPieces = parseInt('{{ $stockAddition->total_pieces }}') || 0;
                    
                    console.log('Model values - Length:', modelLength, 'Height:', modelHeight, 'Diameter:', modelDiameter, 'Pieces:', modelPieces);
                    
                    // Force set values
                    if (lengthInput && modelLength > 0) {
                        lengthInput.value = modelLength;
                        console.log('FORCE SET: Length set to', modelLength);
                    }
                    
                    if (heightInput && modelHeight > 0) {
                        heightInput.value = modelHeight;
                        console.log('FORCE SET: Height set to', modelHeight);
                    }
                    
                    if (diameterInput && modelDiameter) {
                        diameterInput.value = modelDiameter;
                        console.log('FORCE SET: Diameter set to', modelDiameter);
                    }
                    
                    if (totalPiecesInput && modelPieces > 0) {
                        totalPiecesInput.value = modelPieces;
                        console.log('FORCE SET: Pieces set to', modelPieces);
                    }
                    
                    // Trigger size calculation after a delay
                    setTimeout(function() {
                        if (window.calculateSize && typeof window.calculateSize === 'function') {
                            window.calculateSize();
                            console.log('FORCE SET: Size calculation triggered');
                        } else {
                            console.log('FORCE SET: calculateSize function not available yet');
                        }
                    }, 500);
                }
            }, 1000);
        });
        
        // SIMPLE REAL-TIME CALCULATION
        document.addEventListener('DOMContentLoaded', function() {
            console.log('=== SIMPLE BLOCK CALCULATION INITIALIZED ===');
            
            // Function to update block information
            function updateBlockInfo() {
                const conditionStatusSelect = document.getElementById('condition_status');
                const currentCondition = conditionStatusSelect ? conditionStatusSelect.value.toLowerCase().trim() : '';
                
                // Only update block info for Block or Monuments conditions
                if (currentCondition !== 'block' && currentCondition !== 'monuments') {
                    console.log('Not a block/monuments condition, skipping block info update');
                    return;
                }
                
                const weightInput = document.getElementById('weight');
                const totalPiecesInput = document.getElementById('total_pieces');
                
                if (!weightInput || !totalPiecesInput) {
                    console.log('Inputs not found');
                    return;
                }
                
                const weight = parseFloat(weightInput.value) || 0;
                const pieces = parseInt(totalPiecesInput.value) || 0;
                
                console.log('Updating block info - Weight:', weight, 'Pieces:', pieces);
                
                // Update displays
                const singlePieceWeightDisplay = document.getElementById('single_piece_weight');
                const totalWeightDisplay = document.getElementById('total_weight_display');
                const totalPiecesDisplay = document.getElementById('total_pieces_display');
                
                if (singlePieceWeightDisplay) {
                    singlePieceWeightDisplay.innerHTML = `<span class="font-medium text-blue-600">${weight.toFixed(2)} kg</span>`;
                }
                
                if (totalWeightDisplay) {
                    const totalWeight = weight * pieces;
                    totalWeightDisplay.innerHTML = `<span class="font-medium text-blue-600">${totalWeight.toFixed(2)} kg</span><br><span class="text-xs text-gray-500">${weight} kg × ${pieces} pieces</span>`;
                }
                
                if (totalPiecesDisplay) {
                    totalPiecesDisplay.innerHTML = `<span class="font-medium text-green-600">${pieces} pieces</span>`;
                }
                
                console.log('Block info updated');
            }
            
            // Add event listeners after a short delay
            setTimeout(function() {
                const weightInput = document.getElementById('weight');
                const totalPiecesInput = document.getElementById('total_pieces');
                
                if (weightInput && totalPiecesInput) {
                    console.log('Adding event listeners');
                    
                    weightInput.addEventListener('input', updateBlockInfo);
                    weightInput.addEventListener('change', updateBlockInfo);
                    totalPiecesInput.addEventListener('input', updateBlockInfo);
                    totalPiecesInput.addEventListener('change', updateBlockInfo);
                    
                    // Initial calculation
                    updateBlockInfo();
                    
                    console.log('Event listeners added successfully');
                } else {
                    console.log('Inputs not found, retrying...');
                    setTimeout(arguments.callee, 500);
                }
            }, 1000);
            
            // Make function globally available
            window.updateBlockInfo = updateBlockInfo;
            
            // Function to update max values for available fields
            function updateAvailableMaxValues() {
                const totalPiecesInput = document.getElementById('total_pieces');
                const availablePiecesInput = document.getElementById('available_pieces');
                const maxAvailablePiecesSpan = document.getElementById('max_available_pieces');
                
                // Update available pieces max
                if (totalPiecesInput && availablePiecesInput && maxAvailablePiecesSpan) {
                    const totalPieces = parseInt(totalPiecesInput.value) || 0;
                    availablePiecesInput.setAttribute('max', totalPieces);
                    maxAvailablePiecesSpan.textContent = totalPieces;
                }
                
                // Update available sqft max (for non-block conditions)
                const lengthInput = document.getElementById('length');
                const heightInput = document.getElementById('height');
                const availableSqftInput = document.getElementById('available_sqft');
                const maxAvailableSqftSpan = document.getElementById('max_available_sqft');
                
                if (lengthInput && heightInput && totalPiecesInput && availableSqftInput && maxAvailableSqftSpan) {
                    const length = parseFloat(lengthInput.value) || 0;
                    const height = parseFloat(heightInput.value) || 0;
                    const pieces = parseInt(totalPiecesInput.value) || 0;
                    
                    if (length > 0 && height > 0 && pieces > 0) {
                        const cmToSqft = 0.00107639;
                        const singlePieceSizeCm = length * height;
                        const singlePieceSizeSqft = singlePieceSizeCm * cmToSqft;
                        const totalSqft = singlePieceSizeSqft * pieces;
                        
                        availableSqftInput.setAttribute('max', totalSqft.toFixed(2));
                        maxAvailableSqftSpan.textContent = totalSqft.toFixed(2);
                    }
                }
                
                // Update available weight max (for block conditions)
                const weightInput = document.getElementById('weight');
                const availableWeightInput = document.getElementById('available_weight');
                const maxAvailableWeightSpan = document.getElementById('max_available_weight');
                
                if (weightInput && totalPiecesInput && availableWeightInput && maxAvailableWeightSpan) {
                    const weight = parseFloat(weightInput.value) || 0;
                    const pieces = parseInt(totalPiecesInput.value) || 0;
                    const totalWeight = weight * pieces;
                    
                    availableWeightInput.setAttribute('max', totalWeight.toFixed(2));
                    maxAvailableWeightSpan.textContent = totalWeight.toFixed(2);
                }
            }
            
            // Add event listeners for updating available max values
            setTimeout(function() {
                const totalPiecesInput = document.getElementById('total_pieces');
                const lengthInput = document.getElementById('length');
                const heightInput = document.getElementById('height');
                const weightInput = document.getElementById('weight');
                
                if (totalPiecesInput) {
                    totalPiecesInput.addEventListener('input', updateAvailableMaxValues);
                    totalPiecesInput.addEventListener('change', updateAvailableMaxValues);
                }
                
                if (lengthInput) {
                    lengthInput.addEventListener('input', updateAvailableMaxValues);
                    lengthInput.addEventListener('change', updateAvailableMaxValues);
                }
                
                if (heightInput) {
                    heightInput.addEventListener('input', updateAvailableMaxValues);
                    heightInput.addEventListener('change', updateAvailableMaxValues);
                }
                
                if (weightInput) {
                    weightInput.addEventListener('input', updateAvailableMaxValues);
                    weightInput.addEventListener('change', updateAvailableMaxValues);
                }
                
                // Initial update
                updateAvailableMaxValues();
            }, 1000);
            
            // Make sure calculateSize is available globally
            if (typeof window.calculateSize !== 'function') {
                console.log('calculateSize not available globally, waiting...');
                setTimeout(function() {
                    if (typeof window.calculateSize === 'function') {
                        console.log('calculateSize now available globally');
                    } else {
                        console.log('calculateSize still not available');
                    }
                }, 2000);
            }
        });
        
    </script>
</x-app-layout>
