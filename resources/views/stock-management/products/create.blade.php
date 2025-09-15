<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center">
                    <a href="{{ route('stock-management.products.index') }}" class="mr-4 text-gray-600 hover:text-gray-900 transition-colors duration-200">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Add New Product</h1>
                        <p class="mt-2 text-gray-600">Create a new product for your catalog</p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="p-6">
                    <form method="POST" action="{{ route('stock-management.products.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 gap-6">
                            <!-- Product Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Product Name *</label>
                                <input type="text" id="name" name="name" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror" value="{{ old('name') }}" placeholder="Enter product name" required>
                                @error('name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Category -->
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                                <select id="category" name="category" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('category') border-red-500 @enderror">
                                    <option value="">Select category...</option>
                                    <option value="Marble" {{ old('category') == 'Marble' ? 'selected' : '' }}>Marble</option>
                                    <option value="Granite" {{ old('category') == 'Granite' ? 'selected' : '' }}>Granite</option>
                                    <option value="Limestone" {{ old('category') == 'Limestone' ? 'selected' : '' }}>Limestone</option>
                                    <option value="Sandstone" {{ old('category') == 'Sandstone' ? 'selected' : '' }}>Sandstone</option>
                                    <option value="Quartz" {{ old('category') == 'Quartz' ? 'selected' : '' }}>Quartz</option>
                                    <option value="Travertine" {{ old('category') == 'Travertine' ? 'selected' : '' }}>Travertine</option>
                                    <option value="Slate" {{ old('category') == 'Slate' ? 'selected' : '' }}>Slate</option>
                                    <option value="Other" {{ old('category') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('category')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea id="description" name="description" rows="4" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror" placeholder="Enter product description...">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <div class="flex items-center space-x-4">
                                    <label class="flex items-center">
                                        <input type="radio" name="is_active" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700">Active</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="is_active" value="0" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300" {{ old('is_active') == '0' ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700">Inactive</span>
                                    </label>
                                </div>
                                @error('is_active')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Product Preview -->
                        <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Product Preview</h3>
                            <div class="bg-white border border-gray-200 rounded-xl p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                    </div>
                                    <span id="status-preview" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                </div>
                                <h3 id="name-preview" class="text-lg font-semibold text-gray-900 mb-2">Product Name</h3>
                                <p id="description-preview" class="text-gray-600 text-sm mb-4">Product description will appear here...</p>
                                <div class="text-sm text-gray-500">
                                    <span class="font-medium">Category:</span>
                                    <span id="category-preview">Not specified</span>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end space-x-4 mt-8">
                            <a href="{{ route('stock-management.products.index') }}" class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors duration-200">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                Create Product
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.getElementById('name');
            const categorySelect = document.getElementById('category');
            const descriptionInput = document.getElementById('description');
            const statusInputs = document.querySelectorAll('input[name="is_active"]');

            const namePreview = document.getElementById('name-preview');
            const categoryPreview = document.getElementById('category-preview');
            const descriptionPreview = document.getElementById('description-preview');
            const statusPreview = document.getElementById('status-preview');

            // Update preview when inputs change
            function updatePreview() {
                // Update name
                namePreview.textContent = nameInput.value || 'Product Name';

                // Update category
                const selectedCategory = categorySelect.value;
                categoryPreview.textContent = selectedCategory || 'Not specified';

                // Update description
                descriptionPreview.textContent = descriptionInput.value || 'Product description will appear here...';

                // Update status
                const activeStatus = document.querySelector('input[name="is_active"]:checked');
                if (activeStatus && activeStatus.value === '1') {
                    statusPreview.textContent = 'Active';
                    statusPreview.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800';
                } else {
                    statusPreview.textContent = 'Inactive';
                    statusPreview.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800';
                }
            }

            // Add event listeners
            nameInput.addEventListener('input', updatePreview);
            categorySelect.addEventListener('change', updatePreview);
            descriptionInput.addEventListener('input', updatePreview);
            statusInputs.forEach(input => {
                input.addEventListener('change', updatePreview);
            });

            // Initialize preview
            updatePreview();
        });
    </script>
</x-app-layout>
