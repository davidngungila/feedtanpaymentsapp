@extends('layouts.app')

@section('title', 'Bill Details - ' . $bill->bill_pay_number)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Bill Details</h5>
                    <div>
                        <a href="{{ route('billpay.all') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Bills
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Bill Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="mb-3">Bill Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Bill Number:</strong></td>
                                    <td>
                                        <code>{{ $bill->bill_pay_number }}</code>
                                        <button type="button" class="btn btn-sm btn-outline-primary ms-2" onclick="copyBillNumber('{{ $bill->bill_pay_number }}')">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Description:</strong></td>
                                    <td>{{ $bill->bill_description }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @php
                                            $statusColor = match($bill->bill_status) {
                                                'ACTIVE' => 'success',
                                                'INACTIVE' => 'warning',
                                                'COMPLETED' => 'info',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $statusColor }}">{{ $bill->bill_status }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Type:</strong></td>
                                    <td>
                                        <span class="badge bg-label-primary">{{ ucfirst($bill->bill_type) }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Payment Mode:</strong></td>
                                    <td>{{ $bill->bill_payment_mode }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $bill->created_at->format('M j, Y H:i:s') }}</td>
                                </tr>
                                @if($bill->last_payment_at)
                                    <tr>
                                        <td><strong>Last Payment:</strong></td>
                                        <td>{{ $bill->last_payment_at->format('M j, Y H:i:s') }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Amount Details</h6>
                            <table class="table table-borderless">
                                @if($bill->bill_amount)
                                    <tr>
                                        <td><strong>Bill Amount:</strong></td>
                                        <td><strong>{{ number_format($bill->bill_amount, 2) }} {{ $bill->bill_currency }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total Paid:</strong></td>
                                        <td>{{ number_format($bill->total_paid, 2) }} {{ $bill->bill_currency }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Remaining:</strong></td>
                                        <td>
                                            <strong class="{{ $bill->getRemainingAmount() > 0 ? 'text-warning' : 'text-success' }}">
                                                {{ number_format($bill->getRemainingAmount(), 2) }} {{ $bill->bill_currency }}
                                            </strong>
                                        </td>
                                    </tr>
                                @else
                                    <tr>
                                        <td colspan="2" class="text-muted">No amount specified</td>
                                    </tr>
                                @endif
                            </table>

                            @if($bill->isFullyPaid())
                                <div class="alert alert-success mt-3">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Fully Paid</strong>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Customer Information -->
                    @if($bill->customer_name || $bill->customer_email || $bill->customer_phone)
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="mb-3">Customer Information</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        @if($bill->customer_name)
                                            <p><strong>Name:</strong> {{ $bill->customer_name }}</p>
                                        @endif
                                        @if($bill->customer_email)
                                            <p><strong>Email:</strong> {{ $bill->customer_email }}</p>
                                        @endif
                                    </div>
                                    <div class="col-md-4">
                                        @if($bill->customer_phone)
                                            <p><strong>Phone:</strong> {{ $bill->customer_phone }}</p>
                                        @endif
                                        @if($bill->bill_reference)
                                            <p><strong>Reference:</strong> {{ $bill->bill_reference }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Notes -->
                    @if($bill->notes)
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="mb-3">Notes</h6>
                                <div class="alert alert-info">
                                    {{ $bill->notes }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Update Form -->
                    <div class="row">
                        <div class="col-12">
                            <h6 class="mb-3">Update Status</h6>
                            <form action="{{ route('billpay.update', $bill->bill_pay_number) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="bill_status" class="form-label">Status</label>
                                            <select class="form-select" id="bill_status" name="bill_status" required>
                                                <option value="ACTIVE" {{ $bill->bill_status === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                                                <option value="INACTIVE" {{ $bill->bill_status === 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                                                <option value="COMPLETED" {{ $bill->bill_status === 'COMPLETED' ? 'selected' : '' }}>Completed</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="notes" class="form-label">Notes</label>
                                            <textarea class="form-control" id="notes" name="notes" rows="1" placeholder="Add notes...">{{ $bill->notes }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Bill
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="copyBillNumber('{{ $bill->bill_pay_number }}')">
                            <i class="fas fa-copy me-2"></i>Copy Bill Number
                        </button>
                        <a href="{{ route('payments.initiate') }}?bill_number={{ $bill->bill_pay_number }}" class="btn btn-success">
                            <i class="fas fa-money-bill-wave me-2"></i>Make Payment
                        </a>
                        @if(!$bill->isFullyPaid())
                            <button type="button" class="btn btn-info" onclick="shareBill()">
                                <i class="fas fa-share me-2"></i>Share Bill
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Payment Instructions -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Payment Instructions</h6>
                </div>
                <div class="card-body">
                    <ol class="small">
                        <li>Copy the bill number: <code>{{ $bill->bill_pay_number }}</code></li>
                        <li>Go to your mobile money app</li>
                        <li>Select "Pay Bill" or similar option</li>
                        <li>Enter the bill number</li>
                        <li>Enter the amount: {{ number_format($bill->getRemainingAmount(), 2) }} {{ $bill->bill_currency }}</li>
                        <li>Confirm payment</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function copyBillNumber(billNumber) {
    navigator.clipboard.writeText(billNumber).then(function() {
        showToast('Bill number copied to clipboard!');
    });
}

function shareBill() {
    const billNumber = '{{ $bill->bill_pay_number }}';
    const description = '{{ $bill->bill_description }}';
    const amount = '{{ number_format($bill->getRemainingAmount(), 2) }} {{ $bill->bill_currency }}';
    
    const text = `Bill Payment Details\nBill Number: ${billNumber}\nDescription: ${description}\nAmount: ${amount}\nPlease pay using mobile money.`;
    
    if (navigator.share) {
        navigator.share({
            title: 'Bill Payment - ' + billNumber,
            text: text
        }).catch(err => console.log('Share failed:', err));
    } else {
        navigator.clipboard.writeText(text).then(function() {
            showToast('Bill details copied to clipboard!');
        });
    }
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'position-fixed top-0 end-0 p-3';
    toast.style.zIndex = '11';
    toast.innerHTML = `
        <div class="toast show" role="alert">
            <div class="toast-header">
                <strong class="me-auto">Success!</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        document.body.removeChild(toast);
    }, 3000);
}
</script>
@endsection
