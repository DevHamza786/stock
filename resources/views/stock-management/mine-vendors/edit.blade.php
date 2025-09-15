<x-app-layout>
    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center">
                    <a href="{{ route('stock-management.mine-vendors.show', $mineVendor) }}" class="mr-4 text-gray-600 hover:text-gray-900 transition-colors duration-200">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Edit Vendor</h1>
                        <p class="mt-2 text-gray-600">Update vendor information</p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200">
                <div class="p-6">
                    <form method="POST" action="{{ route('stock-management.mine-vendors.update', $mineVendor) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 gap-6">
                            <!-- Vendor Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Vendor Name *</label>
                                <input type="text" id="name" name="name" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('name') border-red-500 @enderror" value="{{ old('name', $mineVendor->name) }}" placeholder="Enter vendor name" required>
                                @error('name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Contact Person -->
                            <div>
                                <label for="contact_person" class="block text-sm font-medium text-gray-700 mb-2">Contact Person</label>
                                <input type="text" id="contact_person" name="contact_person" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('contact_person') border-red-500 @enderror" value="{{ old('contact_person', $mineVendor->contact_person) }}" placeholder="Enter contact person name">
                                @error('contact_person')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                <input type="tel" id="phone" name="phone" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('phone') border-red-500 @enderror" value="{{ old('phone', $mineVendor->phone) }}" placeholder="Enter phone number">
                                @error('phone')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                                <input type="email" id="email" name="email" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('email') border-red-500 @enderror" value="{{ old('email', $mineVendor->email) }}" placeholder="Enter email address">
                                @error('email')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Address -->
                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                                <textarea id="address" name="address" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('address') border-red-500 @enderror" placeholder="Enter vendor address">{{ old('address', $mineVendor->address) }}</textarea>
                                @error('address')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <div class="flex items-center space-x-4">
                                    <label class="flex items-center">
                                        <input type="radio" name="is_active" value="1" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300" {{ old('is_active', $mineVendor->is_active ? '1' : '0') == '1' ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700">Active</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="is_active" value="0" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300" {{ old('is_active', $mineVendor->is_active ? '1' : '0') == '0' ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700">Inactive</span>
                                    </label>
                                </div>
                                @error('is_active')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Vendor Preview -->
                        <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Vendor Preview</h3>
                            <div class="bg-white border border-gray-200 rounded-xl p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="h-12 w-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                                        <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                    </div>
                                    <span id="status-preview" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $mineVendor->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $mineVendor->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                <h3 id="name-preview" class="text-lg font-semibold text-gray-900 mb-2">{{ $mineVendor->name }}</h3>
                                <p id="contact-preview" class="text-gray-600 text-sm mb-2">{{ $mineVendor->contact_person ?? 'Contact person will appear here...' }}</p>
                                <p id="phone-preview" class="text-gray-600 text-sm mb-4">{{ $mineVendor->phone ?? 'Phone number will appear here...' }}</p>
                                <div class="text-sm text-gray-500">
                                    <span class="font-medium">Email:</span>
                                    <span id="email-preview">{{ $mineVendor->email ?? 'Email will appear here...' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end space-x-4 mt-8">
                            <a href="{{ route('stock-management.mine-vendors.show', $mineVendor) }}" class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors duration-200">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                Update Vendor
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
            const contactInput = document.getElementById('contact_person');
            const phoneInput = document.getElementById('phone');
            const emailInput = document.getElementById('email');
            const statusInputs = document.querySelectorAll('input[name="is_active"]');

            const namePreview = document.getElementById('name-preview');
            const contactPreview = document.getElementById('contact-preview');
            const phonePreview = document.getElementById('phone-preview');
            const emailPreview = document.getElementById('email-preview');
            const statusPreview = document.getElementById('status-preview');

            // Update preview when inputs change
            function updatePreview() {
                // Update name
                namePreview.textContent = nameInput.value || 'Vendor Name';

                // Update contact person
                contactPreview.textContent = contactInput.value || 'Contact person will appear here...';

                // Update phone
                phonePreview.textContent = phoneInput.value || 'Phone number will appear here...';

                // Update email
                emailPreview.textContent = emailInput.value || 'Email will appear here...';

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
            contactInput.addEventListener('input', updatePreview);
            phoneInput.addEventListener('input', updatePreview);
            emailInput.addEventListener('input', updatePreview);
            statusInputs.forEach(input => {
                input.addEventListener('change', updatePreview);
            });

            // Initialize preview
            updatePreview();
        });
    </script>
</x-app-layout>
