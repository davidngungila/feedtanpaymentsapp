@extends('layouts.app')

@section('title', 'Payment Status')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Payment Status</h5>
                </div>
                <div class="card-body">
                    @if(isset($error))
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle"></i> Error</h6>
                            {{ $error }}
                        </div>
                    @elseif($paymentData)
                        <div class="row">
                            <div class="col-md-8">
                                <div class="payment-details">
                                    <h6>Payment Information</h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Order Reference:</strong></td>
                                            <td>{{ $paymentData['orderReference'] ?? $paymentData['order_reference'] ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Transaction ID:</strong></td>
                                            <td>{{ $paymentData['transaction_id'] ?? $paymentData['id'] ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                @php
                                                    $status = $paymentData['status'] ?? 'UNKNOWN';
                                                    $statusColor = match($status) {
                                                        'SUCCESS', 'SETTLED' => 'success',
                                                        'PENDING', 'PROCESSING' => 'warning',
                                                        'FAILED' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $statusColor }}">
                                                    {{ $status }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Amount:</strong></td>
                                            <td>{{ number_format($paymentData['amount'] ?? $paymentData['collectedAmount'] ?? 0, 2) }} {{ $paymentData['currency'] ?? $paymentData['collectedCurrency'] ?? 'TZS' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Payer Name:</strong></td>
                                            <td>{{ $paymentData['payer_name'] ?? $paymentData['customer']['customerName'] ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Phone Number:</strong></td>
                                            <td>{{ $paymentData['phone'] ?? $paymentData['paymentPhoneNumber'] ?? $paymentData['customer']['customerPhoneNumber'] ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Payment Method:</strong></td>
                                            <td>{{ $paymentData['payment_method'] ?? $paymentData['channel'] ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Description:</strong></td>
                                            <td>{{ $paymentData['description'] ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Created:</strong></td>
                                            <td>{{ \Carbon\Carbon::parse($paymentData['created_at'] ?? $paymentData['createdAt'])->format('M j, Y H:i:s') }}</td>
                                        </tr>
                                        @if(isset($paymentData['updated_at']) || isset($paymentData['updatedAt']))
                                            <tr>
                                                <td><strong>Last Updated:</strong></td>
                                                <td>{{ \Carbon\Carbon::parse($paymentData['updated_at'] ?? $paymentData['updatedAt'])->format('M j, Y H:i:s') }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="status-actions">
                                    <h6>Actions</h6>
                                    
                                    @if(in_array($status, ['PROCESSING', 'PENDING']))
                                        <div class="d-grid gap-2">
                                            <button type="button" class="btn btn-primary" onclick="checkStatus()">
                                                <i class="fas fa-sync-alt"></i> Refresh Status
                                            </button>
                                            <div class="text-center">
                                                <small class="text-muted">Auto-refreshing every 10 seconds...</small>
                                            </div>
                                        </div>
                                    @endif

                                    @if($status === 'SUCCESS' || $status === 'SETTLED')
                                        <div class="d-grid gap-2">
                                            <a href="{{ route('payments.export.pdf', ['order_reference' => $paymentData['orderReference'] ?? $paymentData['order_reference']]) }}" class="btn btn-success">
                                                <i class="fas fa-file-pdf"></i> Download Receipt
                                            </a>
                                            <button type="button" class="btn btn-info" onclick="shareReceipt()">
                                                <i class="fas fa-share"></i> Share Receipt
                                            </button>
                                        </div>
                                    @endif

                                    @if($status === 'FAILED')
                                        <div class="d-grid gap-2">
                                            <a href="{{ route('payments.initiate') }}" class="btn btn-warning">
                                                <i class="fas fa-redo"></i> Try Again
                                            </a>
                                            <button type="button" class="btn btn-info" onclick="contactSupport()">
                                                <i class="fas fa-headset"></i> Contact Support
                                            </button>
                                        </div>
                                    @endif

                                    <div class="d-grid gap-2 mt-3">
                                        <a href="{{ route('payments.history') }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-history"></i> Payment History
                                        </a>
                                        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
                                            <i class="fas fa-home"></i> Dashboard
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(isset($paymentData['message']))
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <strong>Message:</strong> {{ $paymentData['message'] }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-info-circle"></i> No Payment Data</h6>
                            <p>No payment information found for the provided reference.</p>
                            <a href="{{ route('payments.initiate') }}" class="btn btn-primary">Initiate New Payment</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if(isset($paymentData) && in_array($paymentData['status'] ?? 'UNKNOWN', ['PROCESSING', 'PENDING']))
<script>
let refreshInterval;

function checkStatus() {
    const reference = '{{ $paymentData['orderReference'] ?? $paymentData['order_reference'] ?? '' }}';
    
    fetch('{{ route('payments.status') }}?reference=' + reference)
        .then(response => response.text())
        .then(html => {
            // Create a temporary element to parse the HTML
            const temp = document.createElement('div');
            temp.innerHTML = html;
            
            // Find the payment details section and replace it
            const newContent = temp.querySelector('.card-body');
            const currentContent = document.querySelector('.card-body');
            
            if (newContent && currentContent) {
                currentContent.innerHTML = newContent.innerHTML;
                
                // Check if status has changed to stop auto-refresh
                const statusElement = currentContent.querySelector('.badge');
                if (statusElement && !statusElement.classList.contains('bg-warning')) {
                    clearInterval(refreshInterval);
                }
            }
        })
        .catch(error => {
            console.error('Error refreshing status:', error);
        });
}

// Auto-refresh every 10 seconds
refreshInterval = setInterval(checkStatus, 10000);

// Clean up interval when page is unloaded
window.addEventListener('beforeunload', function() {
    clearInterval(refreshInterval);
});

function shareReceipt() {
    const reference = '{{ $paymentData['orderReference'] ?? $paymentData['order_reference'] ?? '' }}';
    const amount = '{{ number_format($paymentData['amount'] ?? $paymentData['collectedAmount'] ?? 0, 2) }} {{ $paymentData['currency'] ?? $paymentData['collectedCurrency'] ?? 'TZS' }}';
    const status = '{{ $paymentData['status'] ?? 'UNKNOWN' }}';
    
    const text = `Payment Receipt\nReference: ${reference}\nAmount: ${amount}\nStatus: ${status}\nThank you for using FEEDTAN services!`;
    
    if (navigator.share) {
        navigator.share({
            title: 'Payment Receipt - FEEDTAN',
            text: text
        }).catch(err => console.log('Share failed:', err));
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(text).then(() => {
            alert('Receipt details copied to clipboard!');
        });
    }
}

function contactSupport() {
    // Implement contact support functionality
    alert('Please contact our support team at support@feedtancmg.org or call +255 123 456 789 for assistance.');
}
</script>
@endif
@endsection
