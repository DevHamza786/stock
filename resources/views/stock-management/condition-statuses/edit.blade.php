<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Edit Condition Status</h1>
                        <p class="mt-2 text-gray-600">Update condition status information</p>
                    </div>
                    <a href="{{ route('stock-management.condition-statuses.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center transition-colors duration-200">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>git init
                        Back to Condition Statuses
                    </a>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="p-6">
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('stock-management.condition-statuses.update', $conditionStatus) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Form Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                                <input type="text" id="name" name="name" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('name') border-red-500 @enderror" value="{{ old('name', $conditionStatus->name) }}" placeholder="e.g., Block, Slabs, Polished" required>
                                @error('name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Sort Order -->
                            <div>
                                <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                                <input type="number" id="sort_order" name="sort_order" min="0" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('sort_order') border-red-500 @enderror" value="{{ old('sort_order', $conditionStatus->sort_order) }}" placeholder="Leave empty for auto-assignment">
                                @error('sort_order')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-gray-500 text-sm mt-1">Lower numbers appear first</p>
                            </div>

                            <!-- Color -->
                            <div>
                                <label for="color" class="block text-sm font-medium text-gray-700 mb-2">Color *</label>
                                <div class="flex items-center space-x-3">
                                    <input type="color" id="color" name="color" class="h-10 w-16 border border-gray-300 rounded-lg cursor-pointer @error('color') border-red-500 @enderror" value="{{ old('color', $conditionStatus->color) }}" required>
                                    <input type="text" id="color_text" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" value="{{ old('color', $conditionStatus->color) }}" placeholder="#3B82F6" readonly>
                                </div>
                                @error('color')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-gray-500 text-sm mt-1">Used for badges and UI elements</p>
                            </div>

                            <!-- Active Status -->
                            <div class="flex items-center">
                                <div class="flex items-center h-10">
                                    <input type="checkbox" id="is_active" name="is_active" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" {{ old('is_active', $conditionStatus->is_active) ? 'checked' : '' }}>
                                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                        Active Status
                                    </label>
                                </div>
                                <p class="text-gray-500 text-sm ml-4">Only active statuses appear in dropdowns</p>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea id="description" name="description" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('description') border-red-500 @enderror" placeholder="Describe this condition status...">{{ old('description', $conditionStatus->description) }}</textarea>
                            @error('description')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Usage Information -->
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="text-sm font-medium text-blue-900 mb-2">Usage Information</h3>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-blue-700">Stock Additions:</span>
                                    <span class="text-blue-600">{{ $conditionStatus->stockAdditions()->count() }} records</span>
                                </div>
                                <div>
                                    <span class="font-medium text-blue-700">Daily Production:</span>
                                    <span class="text-blue-600">{{ $conditionStatus->dailyProductionItems()->count() }} records</span>
                                </div>
                            </div>
                            @if($conditionStatus->stockAdditions()->count() > 0 || $conditionStatus->dailyProductionItems()->count() > 0)
                                <p class="text-blue-600 text-sm mt-2">
                                    <svg class="h-4 w-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    This condition status is currently being used. Changes will affect existing records.
                                </p>
                            @endif
                        </div>

                        <!-- Preview -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Preview</h3>
                            <div class="flex items-center space-x-4">
                                <div class="h-10 w-10 rounded-lg flex items-center justify-center" id="preview-icon" style="background-color: {{ old('color', $conditionStatus->color) }};">
                                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900" id="preview-name">{{ old('name', $conditionStatus->name) }}</div>
                                    <div class="text-sm text-gray-500" id="preview-description">{{ old('description', $conditionStatus->description) ?: 'Description will appear here' }}</div>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" id="preview-badge" style="background-color: {{ old('color', $conditionStatus->color) }}; color: white;">
                                    Badge Preview
                                </span>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end space-x-4 mt-8">
                            <a href="{{ route('stock-management.condition-statuses.index') }}" class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors duration-200">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                Update Condition Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const colorInput = document.getElementById('color');
            const colorText = document.getElementById('color_text');
            const previewIcon = document.getElementById('preview-icon');
            const previewBadge = document.getElementById('preview-badge');
            const nameInput = document.getElementById('name');
            const descriptionInput = document.getElementById('description');
            const previewName = document.getElementById('preview-name');
            const previewDescription = document.getElementById('preview-description');

            // Update color preview
            function updateColorPreview() {
                const color = colorInput.value;
                colorText.value = color;
                previewIcon.style.backgroundColor = color;
                previewBadge.style.backgroundColor = color;
            }

            // Update text preview
            function updateTextPreview() {
                previewName.textContent = nameInput.value || 'Condition Status';
                previewDescription.textContent = descriptionInput.value || 'Description will appear here';
            }

            // Event listeners
            colorInput.addEventListener('input', updateColorPreview);
            nameInput.addEventListener('input', updateTextPreview);
            descriptionInput.addEventListener('input', updateTextPreview);

            // Initial update
            updateColorPreview();
            updateTextPreview();
        });
    </script>
</x-app-layout>
