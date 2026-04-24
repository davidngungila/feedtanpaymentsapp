<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Statement - {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .summary {
            margin-bottom: 30px;
        }
        .summary table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary th, .summary td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .summary th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .transactions {
            margin-top: 30px;
        }
        .transactions table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .transactions th, .transactions td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            font-size: 11px;
        }
        .transactions th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .status-success {
            color: #28a745;
            font-weight: bold;
        }
        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }
        .status-failed {
            color: #dc3545;
            font-weight: bold;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Account Statement</h1>
        <p>FeedTan Payment System</p>
        <p>Period: {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}</p>
        <p>Generated: {{ now()->format('M j, Y H:i:s') }}</p>
    </div>

    <div class="summary">
        <h3>Summary</h3>
        <table>
            <tr>
                <th>Total Transactions</th>
                <td>{{ $transactions->count() }}</td>
            </tr>
            <tr>
                <th>Total Amount</th>
                <td>{{ number_format($transactions->sum('amount'), 2) }} {{ $currency }}</td>
            </tr>
            <tr>
                <th>Successful Amount</th>
                <td class="status-success">{{ number_format($transactions->whereIn('status', ['SUCCESS', 'SETTLED'])->sum('amount'), 2) }} {{ $currency }}</td>
            </tr>
            <tr>
                <th>Pending Amount</th>
                <td class="status-pending">{{ number_format($transactions->whereIn('status', ['PROCESSING', 'PENDING'])->sum('amount'), 2) }} {{ $currency }}</td>
            </tr>
            <tr>
                <th>Failed Amount</th>
                <td class="status-failed">{{ number_format($transactions->where('status', 'FAILED')->sum('amount'), 2) }} {{ $currency }}</td>
            </tr>
        </table>
    </div>

    <div class="transactions">
        <h3>Transaction Details</h3>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Order Reference</th>
                    <th>Payer Name</th>
                    <th>Phone</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Payment Method</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->created_at->format('M j, Y H:i') }}</td>
                        <td>{{ $transaction->order_reference }}</td>
                        <td>{{ $transaction->payer_name }}</td>
                        <td>{{ $transaction->phone }}</td>
                        <td>{{ number_format($transaction->amount, 2) }} {{ $transaction->currency }}</td>
                        <td class="status-{{ strtolower($transaction->status) }}">{{ $transaction->status }}</td>
                        <td>{{ $transaction->payment_method ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center;">No transactions found for this period</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>This statement was generated automatically from the FeedTan Payment System.</p>
        <p>For inquiries, please contact support.</p>
        <p>Page 1 of 1</p>
    </div>
</body>
</html>
