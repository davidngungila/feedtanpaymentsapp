@extends('layouts.app')

@section('title', 'Bill Pay Numbers - FeedTan Pay')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Bill Pay Numbers</h5>
                    <a href="{{ route('billpay.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create New Bill
                    </a>
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

                    @if($error ?? null)
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ $error }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="Search bills..." 
                                       value="{{ request('search') }}" name="search">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="d-flex gap-2">
                                <select class="form-select" name="status" onchange="this.form.submit()">
                                    <option value="">All Status</option>
                                    <option value="ACTIVE" {{ request('status') == 'ACTIVE' ? 'selected' : '' }}>Active</option>
                                    <option value="INACTIVE" {{ request('status') == 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                                    <option value="COMPLETED" {{ request('status') == 'COMPLETED' ? 'selected' : '' }}>Completed</option>
                                </select>
                                <select class="form-select" name="type" onchange="this.form.submit()">
                                    <option value="">All Types</option>
                                    <option value="order" {{ request('type') == 'order' ? 'selected' : '' }}>Order</option>
                                    <option value="customer" {{ request('type') == 'customer' ? 'selected' : '' }}>Customer</option>
                                </select>
                                <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                                    <i class="fas fa-times"></i> Clear
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Bills Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Bill Number</th>
                                    <th>Description</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Type</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bills as $bill)
                                    <tr>
                                        <td>
                                            <strong>{{ $bill->bill_pay_number }}</strong>
                                        </td>
                                        <td>{{ $bill->bill_description }}</td>
                                        <td>
                                            @if($bill->customer_name)
                                                <div>
                                                    <strong>{{ $bill->customer_name }}</strong>
                                                    @if($bill->customer_phone)
                                                        <br><small class="text-muted">{{ $bill->customer_phone }}</small>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">No customer</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($bill->bill_amount)
                                                <strong>{{ number_format($bill->bill_amount, 2) }} {{ $bill->bill_currency }}</strong>
                                            @else
                                                <span class="text-muted">No amount</span>
                                            @endif
                                        </td>
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
                                        <td>
                                            <span class="badge bg-label-primary">{{ ucfirst($bill->bill_type) }}</span>
                                        </td>
                                        <td>{{ $bill->created_at->format('M j, Y H:i') }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-h"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('billpay.show', $bill->bill_pay_number) }}">
                                                            <i class="fas fa-eye me-2"></i>View Details
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" onclick="copyBillNumber('{{ $bill->bill_pay_number }}')">
                                                            <i class="fas fa-copy me-2"></i>Copy Number
                                                        </a>
                                                    </li>
                                                    @if($bill->isFullyPaid())
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <span class="dropdown-item text-success">
                                                                <i class="fas fa-check-circle me-2"></i>Fully Paid
                                                            </span>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <div class="py-4">
                                                <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                                                <p class="text-muted mb-0">No bill pay numbers found</p>
                                                <a href="{{ route('billpay.create') }}" class="btn btn-primary btn-sm mt-2">
                                                    Create Your First Bill
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($bills->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $bills->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function clearFilters() {
    window.location.href = '{{ route('billpay.all') }}';
}

function copyBillNumber(billNumber) {
    navigator.clipboard.writeText(billNumber).then(function() {
        // Show success message
        const toast = document.createElement('div');
        toast.className = 'position-fixed top-0 end-0 p-3';
        toast.style.zIndex = '11';
        toast.innerHTML = `
            <div class="toast show" role="alert">
                <div class="toast-header">
                    <strong class="me-auto">Copied!</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    Bill number copied to clipboard.
                </div>
            </div>
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 3000);
    });
}
</script>
@endsection
