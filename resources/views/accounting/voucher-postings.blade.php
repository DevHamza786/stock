<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Voucher Postings</h1>
                        <p class="mt-2 text-gray-600">Manage all voucher types in one place</p>
                    </div>
                    <div class="flex space-x-4">
                        <a href="{{ route('accounting.bank-payment-vouchers.create', ['type' => 'payment']) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            New Bank Payment
                        </a>
                        <a href="{{ route('accounting.bank-payment-vouchers.create', ['type' => 'receipt']) }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            New Bank Receipt
                        </a>
                        <a href="{{ route('accounting.purchase-vouchers.create') }}" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            New Purchase Voucher
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px" aria-label="Tabs">
                        <a href="{{ route('accounting.voucher-postings.index', ['tab' => 'payment-receipt']) }}" 
                           class="{{ $activeTab === 'payment-receipt' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-200">
                            Receipt/Payment Voucher
                        </a>
                        <a href="{{ route('accounting.voucher-postings.index', ['tab' => 'purchase']) }}" 
                           class="{{ $activeTab === 'purchase' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-200">
                            Purchase Voucher
                        </a>
                        <a href="{{ route('accounting.voucher-postings.index', ['tab' => 'sales']) }}" 
                           class="{{ $activeTab === 'sales' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-200">
                            Sales Voucher
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                @if($activeTab === 'payment-receipt')
                    <!-- Receipt/Payment Voucher Tab -->
                    <div class="p-6">
                        <div class="mb-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">Bank Payment/Receipt Vouchers</h2>
                            
                            <!-- Sub-tabs for Bank and Cash -->
                            <div class="flex space-x-4 mb-4 border-b border-gray-200">
                                <button onclick="showBankVouchers()" id="bank-tab" class="px-4 py-2 border-b-2 border-blue-500 text-blue-600 font-medium text-sm">
                                    Bank Vouchers
                                </button>
                                <button onclick="showCashVouchers()" id="cash-tab" class="px-4 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium text-sm">
                                    Cash Vouchers
                                </button>
                            </div>
                        </div>

                        <!-- Bank Vouchers Table -->
                        <div id="bank-vouchers-section">
                            @if($bankVouchers->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Voucher #</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bank Account</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($bankVouchers as $voucher)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {{ $voucher->voucher_number }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $voucher->voucher_type === 'payment' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                                            {{ ucfirst($voucher->voucher_type) }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $voucher->payment_date->format('M d, Y') }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ optional($voucher->bankAccount)->account_code }} — {{ optional($voucher->bankAccount)->account_name }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {{ number_format($voucher->amount, 2) }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $voucher->reference_number ?? '—' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <a href="{{ route('accounting.bank-payment-vouchers.show', $voucher) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-4">
                                    {{ $bankVouchers->links() }}
                                </div>
                            @else
                                <div class="text-center py-12">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No bank vouchers</h3>
                                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new bank payment or receipt voucher.</p>
                                </div>
                            @endif
                        </div>

                        <!-- Cash Vouchers Table -->
                        <div id="cash-vouchers-section" class="hidden">
                            @if($cashVouchers->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Voucher #</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cash Account</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($cashVouchers as $voucher)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {{ $voucher->voucher_number }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $voucher->payment_date->format('M d, Y') }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ optional($voucher->cashAccount)->account_code }} — {{ optional($voucher->cashAccount)->account_name }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {{ number_format($voucher->amount, 2) }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $voucher->reference_number ?? '—' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <a href="{{ route('accounting.cash-payment-vouchers.index') }}" class="text-blue-600 hover:text-blue-900">View</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-4">
                                    {{ $cashVouchers->links() }}
                                </div>
                            @else
                                <div class="text-center py-12">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No cash vouchers</h3>
                                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new cash payment voucher.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                @elseif($activeTab === 'purchase')
                    <!-- Purchase Voucher Tab -->
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Purchase Vouchers</h2>
                        
                        @if($purchaseVouchers->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Voucher #</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bill Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($purchaseVouchers as $voucher)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $voucher->voucher_number }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ optional($voucher->bill)->bill_date?->format('M d, Y') ?? '—' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ optional($voucher->bill?->account)->account_code ?? '—' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $voucher->bill->vendor_reference ?? '—' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ number_format($voucher->total_amount, 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $voucher->status === 'posted' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                        {{ ucfirst($voucher->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    @if($voucher->status === 'draft')
                                                        <a href="{{ route('accounting.purchase-vouchers.edit', $voucher) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                                    @else
                                                        <span class="text-gray-400">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-4">
                                {{ $purchaseVouchers->links() }}
                            </div>
                        @else
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No purchase vouchers</h3>
                                <p class="mt-1 text-sm text-gray-500">Get started by creating a new purchase voucher.</p>
                            </div>
                        @endif
                    </div>

                @elseif($activeTab === 'sales')
                    <!-- Sales Voucher Tab -->
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Sales Vouchers</h2>
                        
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Sales Vouchers Coming Soon</h3>
                            <p class="mt-1 text-sm text-gray-500">Sales voucher functionality will be available in a future update.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function showBankVouchers() {
            document.getElementById('bank-vouchers-section').classList.remove('hidden');
            document.getElementById('cash-vouchers-section').classList.add('hidden');
            document.getElementById('bank-tab').classList.add('border-blue-500', 'text-blue-600');
            document.getElementById('bank-tab').classList.remove('border-transparent', 'text-gray-500');
            document.getElementById('cash-tab').classList.remove('border-blue-500', 'text-blue-600');
            document.getElementById('cash-tab').classList.add('border-transparent', 'text-gray-500');
        }

        function showCashVouchers() {
            document.getElementById('cash-vouchers-section').classList.remove('hidden');
            document.getElementById('bank-vouchers-section').classList.add('hidden');
            document.getElementById('cash-tab').classList.add('border-blue-500', 'text-blue-600');
            document.getElementById('cash-tab').classList.remove('border-transparent', 'text-gray-500');
            document.getElementById('bank-tab').classList.remove('border-blue-500', 'text-blue-600');
            document.getElementById('bank-tab').classList.add('border-transparent', 'text-gray-500');
        }
    </script>
</x-app-layout>

