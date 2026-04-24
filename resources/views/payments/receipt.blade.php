<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - {{ $transaction->order_reference }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        .receipt {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 28px;
        }
        .header p {
            color: #666;
            margin: 5px 0 0 0;
        }
        .receipt-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .info-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .info-section h3 {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 16px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
        }
        .info-section p {
            margin: 8px 0;
            display: flex;
            justify-content: space-between;
        }
        .info-section strong {
            color: #555;
        }
        .status-success {
            background: #d4edda;
            color: #155724;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
        }
        .status-failed {
            background: #f8d7da;
            color: #721c24;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 14px;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 72px;
            color: rgba(0, 123, 255, 0.1);
            font-weight: bold;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="watermark">FEEDTAN</div>
        
        <div class="header">
            <h1>Payment Receipt</h1>
            <p>FeedTan Payment System</p>
        </div>

        <div class="receipt-info">
            <div class="info-section">
                <h3>Transaction Details</h3>
                <p>
                    <strong>Order Reference:</strong>
                    <span>{{ $transaction->order_reference }}</span>
                </p>
                <p>
                    <strong>Transaction ID:</strong>
                    <span>{{ $transaction->transaction_id ?? 'N/A' }}</span>
                </p>
                <p>
                    <strong>Status:</strong>
                    <span>
                        @php
                            $status = $transaction->status;
                            $statusClass = match($status) {
                                'SUCCESS', 'SETTLED' => 'status-success',
                                'PENDING', 'PROCESSING' => 'status-pending',
                                'FAILED' => 'status-failed',
                                default => ''
                            };
                        @endphp
                        <span class="{{ $statusClass }}">{{ $status }}</span>
                    </span>
                </p>
                <p>
                    <strong>Payment Method:</strong>
                    <span>{{ $transaction->payment_method ?? 'N/A' }}</span>
                </p>
            </div>

            <div class="info-section">
                <h3>Payment Information</h3>
                <p>
                    <strong>Amount:</strong>
                    <span>{{ number_format($transaction->amount, 2) }} {{ $transaction->currency }}</span>
                </p>
                <p>
                    <strong>Payer Name:</strong>
                    <span>{{ $transaction->payer_name }}</span>
                </p>
                <p>
                    <strong>Phone Number:</strong>
                    <span>{{ $transaction->phone }}</span>
                </p>
                <p>
                    <strong>Description:</strong>
                    <span>{{ $transaction->description ?? 'Payment' }}</span>
                </p>
            </div>
        </div>

        <div class="info-section">
            <h3>Timestamps</h3>
            <p>
                <strong>Date Created:</strong>
                <span>{{ $transaction->created_at->format('M j, Y H:i:s') }}</span>
            </p>
            <p>
                <strong>Last Updated:</strong>
                <span>{{ $transaction->updated_at->format('M j, Y H:i:s') }}</span>
            </p>
        </div>

        @if($transaction->isSuccessful())
            <div class="info-section" style="background: #d4edda; border: 1px solid #c3e6cb;">
                <h3 style="color: #155724;">Payment Completed Successfully!</h3>
                <p style="color: #155724; text-align: center; margin: 10px 0;">
                    Thank you for your payment. This receipt serves as proof of your transaction.
                </p>
            </div>
        @endif

        <div class="footer">
            <p><strong>FeedTan Payment System</strong></p>
            <p>This is an automatically generated receipt. For inquiries, please contact support.</p>
            <p>Generated on: {{ now()->format('M j, Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
