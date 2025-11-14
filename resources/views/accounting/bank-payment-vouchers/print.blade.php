<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $bankPaymentVoucher->voucher_number }} - {{ $bankPaymentVoucher->isPayment() ? 'Bank Payment' : 'Bank Receipt' }} Voucher</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            background: white;
        }

        .voucher-container {
            width: 50%;
            height: 100vh;
            padding: 15mm;
            border-right: 1px dashed #ccc;
            float: left;
            page-break-after: always;
        }

        .voucher-container:last-child {
            border-right: none;
        }

        .voucher-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .voucher-header h1 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .voucher-header .voucher-type {
            font-size: 12px;
            font-weight: normal;
        }

        .voucher-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 12px;
            font-size: 10px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
        }

        .info-label {
            font-weight: bold;
        }

        .voucher-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 10px;
        }

        .voucher-table th,
        .voucher-table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
        }

        .voucher-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .voucher-table .account-code {
            width: 15%;
            font-weight: bold;
        }

        .voucher-table .account-name {
            width: 35%;
        }

        .voucher-table .particulars {
            width: 30%;
        }

        .voucher-table .amount {
            width: 20%;
            text-align: right;
            font-weight: bold;
        }

        .total-row {
            font-weight: bold;
            background-color: #f0f0f0;
        }

        .total-row td {
            text-align: right;
        }

        .voucher-footer {
            margin-top: 20px;
            border-top: 1px solid #000;
            padding-top: 12px;
        }

        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            margin-top: 30px;
        }

        .signature-box {
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 8px;
            min-height: 50px;
        }

        .signature-label {
            font-size: 9px;
            font-weight: bold;
            margin-top: 4px;
        }

        .notes-section {
            margin-top: 12px;
            font-size: 10px;
        }

        .notes-section strong {
            display: block;
            margin-bottom: 4px;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .voucher-container {
                width: 50%;
                height: 100vh;
                page-break-after: always;
            }

            .no-print {
                display: none;
            }
        }

        @media screen {
            body {
                background: #f5f5f5;
                padding: 20px;
            }

            .voucher-container {
                background: white;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="voucher-container">
        <div class="voucher-header">
            <h1>{{ $bankPaymentVoucher->isPayment() ? 'Bank Payment Voucher' : 'Bank Receipt Voucher' }}</h1>
            <div class="voucher-type">Voucher No: {{ $bankPaymentVoucher->voucher_number }}</div>
        </div>

        <div class="voucher-info">
            <div class="info-item">
                <span class="info-label">Date:</span>
                <span>{{ $bankPaymentVoucher->payment_date->format('d/m/Y') }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Bank Account:</span>
                <span>{{ optional($bankPaymentVoucher->bankAccount)->account_code ?? '—' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Account Name:</span>
                <span>{{ optional($bankPaymentVoucher->bankAccount)->account_name ?? '—' }}</span>
            </div>
            @if($bankPaymentVoucher->reference_number)
            <div class="info-item">
                <span class="info-label">Reference:</span>
                <span>{{ $bankPaymentVoucher->reference_number }}</span>
            </div>
            @endif
        </div>

        <table class="voucher-table">
            <thead>
                <tr>
                    <th class="account-code">Account Code</th>
                    <th class="account-name">Account Name</th>
                    <th class="particulars">Particulars</th>
                    <th class="amount">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($debitLines as $line)
                <tr>
                    <td class="account-code">{{ optional($line->account)->account_code ?? '—' }}</td>
                    <td class="account-name">{{ optional($line->account)->account_name ?? '—' }}</td>
                    <td class="particulars">{{ $line->particulars ?? '—' }}</td>
                    <td class="amount">{{ number_format($line->amount, 2) }}</td>
                </tr>
                @endforeach
                @foreach($creditLines as $line)
                <tr>
                    <td class="account-code">{{ optional($line->account)->account_code ?? '—' }}</td>
                    <td class="account-name">{{ optional($line->account)->account_name ?? '—' }}</td>
                    <td class="particulars">{{ $line->particulars ?? '—' }}</td>
                    <td class="amount">{{ number_format($line->amount, 2) }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3" style="text-align: right; padding-right: 8px;">Total:</td>
                    <td class="amount">{{ number_format($bankPaymentVoucher->amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        @if($bankPaymentVoucher->notes)
        <div class="notes-section">
            <strong>Notes:</strong>
            <div>{{ $bankPaymentVoucher->notes }}</div>
        </div>
        @endif

        <div class="voucher-footer">
            <div class="signatures">
                <div class="signature-box">
                    <div class="signature-label">Posted By</div>
                </div>
                <div class="signature-box">
                    <div class="signature-label">Verified By</div>
                </div>
                <div class="signature-box">
                    <div class="signature-label">Authorized By</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>

