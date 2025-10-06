<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gate Pass - GP-{{ str_pad($gatePass->id, 4, '0', STR_PAD_LEFT) }}</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            .print-break { page-break-after: always; }
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #f97316;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #f97316;
            margin-bottom: 10px;
        }

        .document-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .gate-pass-number {
            font-size: 18px;
            color: #666;
            font-weight: normal;
        }

        .content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .section {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #f97316;
            margin-bottom: 15px;
            border-bottom: 2px solid #f97316;
            padding-bottom: 5px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
            border-bottom: 1px dotted #d1d5db;
        }

        .info-label {
            font-weight: bold;
            color: #374151;
        }

        .info-value {
            color: #111827;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending { background: #fef3c7; color: #92400e; }
        .status-approved { background: #dbeafe; color: #1e40af; }
        .status-dispatched { background: #d1fae5; color: #065f46; }

        .signature-section {
            margin-top: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 30px;
        }

        .signature-box {
            text-align: center;
            border-top: 1px solid #d1d5db;
            padding-top: 10px;
        }

        .signature-label {
            font-weight: bold;
            color: #374151;
            margin-bottom: 40px;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #f97316;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .print-button:hover {
            background: #ea580c;
        }

        @media screen {
            .print-button { display: block; }
        }

        @media print {
            .print-button { display: none; }
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">🖨️ Print Gate Pass</button>

    <div class="header">
        <div class="company-name">STOCK MANAGEMENT SYSTEM</div>
        <div class="document-title">GATE PASS</div>
        <div class="gate-pass-number">Gate Pass #: GP-{{ str_pad($gatePass->id, 4, '0', STR_PAD_LEFT) }}</div>
    </div>

    <div class="content">
        <!-- Gate Pass Information -->
        <div class="section">
            <div class="section-title">Gate Pass Information</div>
            <div class="info-row">
                <span class="info-label">Gate Pass Number:</span>
                <span class="info-value">GP-{{ str_pad($gatePass->id, 4, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span class="info-value">
                    <span class="status-badge status-{{ strtolower($gatePass->status ?? 'pending') }}">
                        {{ $gatePass->status ?? 'Pending' }}
                    </span>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Date:</span>
                <span class="info-value">{{ $gatePass->date->format('M d, Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Time:</span>
                <span class="info-value">{{ $gatePass->date->format('h:i A') }}</span>
            </div>
        </div>

        <!-- Product Information -->
        <div class="section">
            <div class="section-title">Product Information</div>
            <div class="info-row">
                <span class="info-label">Product:</span>
                <span class="info-value">{{ $gatePass->stockIssued?->stockAddition?->product?->name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Particulars:</span>
                <span class="info-value">{{ $gatePass->stockIssued?->stockAddition?->stone ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Size (3D):</span>
                <span class="info-value">{{ $gatePass->stockIssued?->stockAddition?->size_3d ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Mine Vendor:</span>
                <span class="info-value">{{ $gatePass->stockIssued?->stockAddition?->mineVendor?->name ?? 'N/A' }}</span>
            </div>
        </div>

        <!-- Dispatch Information -->
        <div class="section">
            <div class="section-title">Dispatch Information</div>
            <div class="info-row">
                <span class="info-label">Quantity Dispatched:</span>
                <span class="info-value">{{ number_format($gatePass->quantity_issued) }} pieces</span>
            </div>
            <div class="info-row">
                <span class="info-label">Square Feet:</span>
                <span class="info-value">{{ number_format($gatePass->sqft_issued, 2) }} sqft</span>
            </div>
            <div class="info-row">
                <span class="info-label">Destination:</span>
                <span class="info-value">{{ $gatePass->destination ?? 'Not specified' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Vehicle Number:</span>
                <span class="info-value">{{ $gatePass->vehicle_number ?? 'Not specified' }}</span>
            </div>
        </div>

        <!-- Logistics Information -->
        <div class="section">
            <div class="section-title">Logistics Information</div>
            <div class="info-row">
                <span class="info-label">Driver Name:</span>
                <span class="info-value">{{ $gatePass->driver_name ?? 'Not specified' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Dispatch Date:</span>
                <span class="info-value">{{ $gatePass->date->format('M d, Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Dispatch Time:</span>
                <span class="info-value">{{ $gatePass->date->format('h:i A') }}</span>
            </div>
            @if($gatePass->notes)
            <div class="info-row">
                <span class="info-label">Notes:</span>
                <span class="info-value">{{ $gatePass->notes }}</span>
            </div>
            @endif
        </div>
    </div>

    <!-- Stock Details -->
    <div class="section" style="margin-bottom: 30px;">
        <div class="section-title">Source Stock Details</div>
        <div class="info-row">
            <span class="info-label">Stock Addition ID:</span>
            <span class="info-value">SA-{{ $gatePass->stockIssued?->stockAddition?->id ? str_pad($gatePass->stockIssued->stockAddition->id, 4, '0', STR_PAD_LEFT) : 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Stock Issued ID:</span>
            <span class="info-value">SI-{{ $gatePass->stockIssued?->id ? str_pad($gatePass->stockIssued->id, 4, '0', STR_PAD_LEFT) : 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total Stock Issued:</span>
            <span class="info-value">{{ $gatePass->stockIssued?->quantity_issued ? number_format($gatePass->stockIssued->quantity_issued) . ' pieces' : 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total Sqft Issued:</span>
            <span class="info-value">{{ $gatePass->stockIssued?->sqft_issued ? number_format($gatePass->stockIssued->sqft_issued, 2) . ' sqft' : 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Vendor Contact:</span>
            <span class="info-value">{{ $gatePass->stockIssued?->stockAddition?->mineVendor?->contact_person ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Vendor Phone:</span>
            <span class="info-value">{{ $gatePass->stockIssued?->stockAddition?->mineVendor?->phone ?? 'N/A' }}</span>
        </div>
    </div>

    <!-- Signatures -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-label">Prepared By</div>
            <div style="height: 50px; border-bottom: 1px solid #d1d5db;"></div>
            <div style="margin-top: 10px; font-size: 12px; color: #6b7280;">Name & Signature</div>
        </div>

        <div class="signature-box">
            <div class="signature-label">Approved By</div>
            <div style="height: 50px; border-bottom: 1px solid #d1d5db;"></div>
            <div style="margin-top: 10px; font-size: 12px; color: #6b7280;">Name & Signature</div>
        </div>

        <div class="signature-box">
            <div class="signature-label">Security Guard</div>
            <div style="height: 50px; border-bottom: 1px solid #d1d5db;"></div>
            <div style="margin-top: 10px; font-size: 12px; color: #6b7280;">Name & Signature</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><strong>Important:</strong> This gate pass must be presented at the security gate for verification.</p>
        <p>Generated on {{ now()->format('M d, Y \a\t h:i A') }} | Stock Management System</p>
        <p>For any queries, contact the stock management department.</p>
    </div>

    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() { window.print(); }

        // Print button functionality
        document.querySelector('.print-button').addEventListener('click', function() {
            window.print();
        });

        // Keyboard shortcut for printing
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>
