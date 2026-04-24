@extends('layouts.app')

@section('title', 'Payment History - FeedTan Pay')
@section('description', 'FeedTan Pay - View and manage your payment transactions')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Payment History</h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-success btn-sm" onclick="syncPayments()" id="syncBtn">
                        <i class="bx bx-sync me-1"></i>Sync from API
                    </button>
                    <button class="btn btn-outline-primary btn-sm" onclick="exportHistory()">
                        <i class="bx bx-download me-1"></i>Export
                    </button>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#filterModal">
                        <i class="bx bx-filter me-1"></i>Filter
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Stats Cards -->
                <div class="row mb-6">
                    <div class="col-md-3">
                        <div class="card border-primary">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-lg bg-primary bg-opacity-10 rounded-circle me-3">
                                        <i class="bx bx-trending-up text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Total Payments</h6>
                                        <h4 class="mb-0 text-primary">{{ $totalCount ?? 0 }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-success">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-lg bg-success bg-opacity-10 rounded-circle me-3">
                                        <i class="bx bx-check-circle text-success"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Successful</h6>
                                        <h4 class="mb-0 text-success">{{ $successCount ?? 0 }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-success">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-lg bg-success bg-opacity-10 rounded-circle me-3">
                                        <i class="bx bx-check-double text-success"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Settled</h6>
                                        <h4 class="mb-0 text-success">{{ $settledCount ?? 0 }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-info">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-lg bg-info bg-opacity-10 rounded-circle me-3">
                                        <i class="bx bx-wallet text-info"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Current Balance</h6>
                                        <h4 class="mb-0 text-info">
                                            @if(!empty($balance))
                                                {{ number_format($balance[0]['balance'] ?? 0, 2) }} {{ $balance[0]['currency'] ?? 'TZS' }}
                                            @else
                                                0.00 TZS
                                            @endif
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search and Date Range -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" class="form-control" id="searchInput" placeholder="Search by reference, name, or phone..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" id="dateFrom" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" id="dateTo" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-5">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary" onclick="filterPayments()">
                                <i class="bx bx-filter me-1"></i>Filter
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                                <i class="bx bx-x me-1"></i>Clear
                            </button>
                            <button type="button" class="btn btn-success" id="syncBtn" onclick="syncFromAPI()">
                                <i class="bx bx-sync me-1"></i>Sync from API
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Payments Table -->
                @if(isset($error))
                    <div class="alert alert-danger">
                        {{ $error }}
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover" id="paymentsTable">
                            <thead>
                                <tr>
                                    <th>Order Reference</th>
                                    <th>Payer</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $payment)
                                    <tr>
                                        <td>
                                            <span class="text-muted">{{ $payment->order_reference ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar me-2">
                                                    <div class="avatar-initial rounded-circle bg-primary text-white">
                                                        {{ substr($payment->payer_name ?? 'Unknown', 0, 1) }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $payment->payer_name ?? 'Unknown' }}</h6>
                                                    <small class="text-muted">{{ $payment->phone ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><strong>{{ number_format($payment->amount ?? 0, 2) }} {{ $payment->currency ?? 'TZS' }}</strong></td>
                                        <td>
                                            <span class="badge bg-label-primary">{{ $payment->payment_method ?? 'Unknown' }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $status = $payment->status ?? 'UNKNOWN';
                                                $statusColor = match($status) {
                                                    'SUCCESS', 'SETTLED' => 'success',
                                                    'PENDING', 'PROCESSING' => 'warning',
                                                    'FAILED' => 'danger',
                                                    default => 'secondary'
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $statusColor }}">{{ $status }}</span>
                                        </td>
                                        <td>{{ $payment->created_at ? \Carbon\Carbon::parse($payment->created_at)->format('M j, Y H:i:s') : 'N/A' }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    <i class="bx bx-dots-horizontal-rounded"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('payments.status') }}?reference={{ $payment->order_reference }}">
                                                            <i class="bx bx-eye me-2"></i>View Status
                                                        </a>
                                                    </li>
                                                    @if(in_array($status, ['SUCCESS', 'SETTLED']))
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('payments.export.pdf') }}?order_reference={{ $payment->order_reference }}" target="_blank">
                                                                <i class="bx bx-download me-2"></i>Download PDF Receipt
                                                            </a>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <p class="text-muted mb-0">No payments found</p>
                                            <a href="{{ route('payments.initiate') }}" class="btn btn-primary btn-sm mt-2">Initiate Payment</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- Transaction Count -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Showing all {{ $payments->count() }} settled transactions
                    </div>
                    <div class="text-muted">
                        <i class="bx bx-check-circle"></i> Only settled payments shown
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transaction Details Modal -->
<div class="modal fade" id="transactionDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Transaction Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="mb-2">Transaction Information</h6>
                        <div class="mb-3">
                            <label class="form-label">Transaction ID</label>
                            <input type="text" class="form-control" id="modalTransactionId" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date & Time</label>
                            <input type="text" class="form-control" id="modalDateTime" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <span class="badge bg-success" id="modalStatus">Completed</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-2">Payment Details</h6>
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="text" class="form-control" id="modalAmount" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fee</label>
                            <input type="text" class="form-control" id="modalFee" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Total Amount</label>
                            <input type="text" class="form-control" id="modalTotal" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <input type="text" class="form-control" id="modalMethod" readonly>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <h6 class="mb-2">Recipient Information</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" id="modalRecipientName" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="text" class="form-control" id="modalRecipientEmail" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" id="modalRecipientPhone" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Account Number</label>
                                <input type="text" class="form-control" id="modalRecipientAccount" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <h6 class="mb-2">Additional Information</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <input type="text" class="form-control" id="modalCategory" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Reference</label>
                                <input type="text" class="form-control" id="modalReference" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" id="modalDescription" rows="3" readonly></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" id="modalNotes" rows="2" readonly></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="downloadReceipt()">Download Receipt</button>
                <button type="button" class="btn btn-success" onclick="payAgain()">Pay Again</button>
            </div>
        </div>
    </div>
</div>

<!-- Download Receipt Modal -->
<div class="modal fade" id="downloadReceiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Download Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <h6 class="mb-3">Choose Receipt Format</h6>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="receiptFormat" id="pdfFormat" value="pdf" checked>
                        <label class="form-check-label" for="pdfFormat">
                            <i class="bx bx-file me-2"></i>PDF Document
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="receiptFormat" id="excelFormat" value="excel">
                        <label class="form-check-label" for="excelFormat">
                            <i class="bx bx-table me-2"></i>Excel Spreadsheet
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="receiptFormat" id="csvFormat" value="csv">
                        <label class="form-check-label" for="csvFormat">
                            <i class="bx bx-file me-2"></i>CSV File
                        </label>
                    </div>
                </div>
                
                <div class="mb-4">
                    <h6 class="mb-3">Email Options</h6>
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="receiptEmail" value="john.doe@example.com">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="includeDetails" checked>
                        <label class="form-check-label" for="includeDetails">
                            Include full transaction details
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmDownloadReceipt()">Download</button>
            </div>
        </div>
    </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filter Payments</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select class="form-select" id="modalStatusFilter">
                        <option value="">All Status</option>
                        <option value="successful">Successful</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label">Payment Method</label>
                    <select class="form-select" id="modalMethodFilter">
                        <option value="">All Methods</option>
                        <option value="wallet">Wallet</option>
                        <option value="card">Card</option>
                        <option value="bank">Bank</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label">Date Range</label>
                    <div class="d-flex gap-2">
                        <input type="date" class="form-control" id="modalDateFrom">
                        <input type="date" class="form-control" id="modalDateTo">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Amount Range</label>
                    <div class="d-flex gap-2">
                        <input type="number" class="form-control" placeholder="Min" id="modalAmountMin">
                        <input type="number" class="form-control" placeholder="Max" id="modalAmountMax">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="applyFilters()">Apply Filters</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize timezone for date displays
    if (typeof moment !== 'undefined' && window.TZS_TIMEZONE) {
        const now = moment().tz(window.TZS_TIMEZONE);
        const dateFormat = window.TZS_FORMAT || 'MM/DD/YYYY hh:mm A';
        
        // Update all transaction date displays
        const dateElements = document.querySelectorAll('.transaction-date');
        dateElements.forEach(element => {
            if (element && element.dataset.date) {
                const originalDate = element.dataset.date;
                const formattedDate = moment(originalDate, 'YYYY-MM-DD HH:mm').tz(window.TZS_TIMEZONE).format(dateFormat);
                element.textContent = formattedDate;
            }
        });
    }

    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');
    
    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const status = statusFilter.value;
        const fromDate = dateFrom.value;
        const toDate = dateTo.value;
        
        const rows = document.querySelectorAll('#paymentsTable tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const statusBadge = row.querySelector('.badge');
            const rowStatus = statusBadge ? statusBadge.textContent.toLowerCase() : '';
            const dateCell = row.cells[5].textContent;
            
            let showRow = true;
            
            // Search filter
            if (searchTerm && !text.includes(searchTerm)) {
                showRow = false;
            }
            
            // Status filter
            if (status && rowStatus !== status.toLowerCase()) {
                showRow = false;
            }
            
            // Date filter (simplified)
            if (fromDate && dateCell < fromDate) {
                showRow = false;
            }
            
            row.style.display = showRow ? '' : 'none';
        });
    }
    
    searchInput.addEventListener('input', filterTable);
    statusFilter.addEventListener('change', filterTable);
    dateFrom.addEventListener('change', filterTable);
    dateTo.addEventListener('change', filterTable);
});

function syncPayments() {
    const syncBtn = document.getElementById('syncBtn');
    const originalContent = syncBtn.innerHTML;
    
    // Show loading state
    syncBtn.disabled = true;
    syncBtn.innerHTML = '<i class="bx bx-sync bx-spin me-1"></i>Syncing...';
    
    // Make AJAX request to sync payments
    fetch('/sync/payments', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showToast('success', data.message || 'Payments synced successfully!');
            
            // Reload page after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showToast('error', data.message || 'Failed to sync payments');
        }
    })
    .catch(error => {
        console.error('Sync error:', error);
        showToast('error', 'An error occurred while syncing payments');
    })
    .finally(() => {
        // Restore button state
        syncBtn.disabled = false;
        syncBtn.innerHTML = originalContent;
    });
}

function changePerPage(perPage) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', perPage);
    window.location.href = url.toString();
}

function syncFromAPI() {
    const syncBtn = document.getElementById('syncBtn');
    const originalContent = syncBtn.innerHTML;
    
    // Show loading state
    syncBtn.innerHTML = '<i class="bx bx-sync bx-spin me-1"></i>Syncing...';
    syncBtn.disabled = true;
    
    // Make AJAX request to sync from API
    fetch('/payments/sync-api', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', `Successfully synced ${data.synced} transactions from API`);
            // Reload page to show updated data
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast('error', data.message || 'Sync failed');
        }
    })
    .catch(error => {
        showToast('error', 'Sync failed: ' + error.message);
    })
    .finally(() => {
        // Restore button state
        syncBtn.innerHTML = originalContent;
        syncBtn.disabled = false;
    });
}

function showToast(type, message) {
    const toast = document.createElement('div');
    toast.className = `position-fixed top-0 end-0 p-3`;
    toast.style.zIndex = '9999';
    toast.innerHTML = `
        <div class="toast show" role="alert">
            <div class="toast-header">
                <strong class="me-auto">${type === 'success' ? 'Success!' : 'Error!'}</strong>
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
    }, 5000);
}

// Sample transaction data
const transactions = [
    {
        id: 'TRX001',
        date: 'Dec 15, 2024',
        description: 'Salary Payment',
        amount: 5000.00,
        fee: 25.00,
        total: 5025.00,
        status: 'Completed',
        method: 'Bank Transfer',
        category: 'Income',
        recipient: {
            name: 'John Smith',
            email: 'john.smith@company.com',
            phone: '+1 (555) 123-4567',
            account: '****1234'
        },
        reference: 'REF-2024-001',
        notes: 'Monthly salary payment',
        additionalInfo: 'Direct deposit from employer'
    },
    {
        id: 'TRX002',
        date: 'Dec 14, 2024',
        description: 'Online Shopping',
        amount: 245.50,
        fee: 7.37,
        total: 252.87,
        status: 'Completed',
        method: 'Credit Card',
        category: 'Shopping',
        recipient: {
            name: 'Amazon',
            email: 'orders@amazon.com',
            phone: '',
            account: ''
        },
        reference: 'AMZ-2024-002',
        notes: 'Electronics and books purchase',
        additionalInfo: '2-day shipping selected'
    }
];

function viewDetails(transactionId) {
    const transaction = transactions.find(t => t.id === transactionId);
    if (transaction) {
        // Populate modal with transaction data
        document.getElementById('modalTransactionId').value = transaction.id;
        document.getElementById('modalDateTime').value = transaction.date + ' at 2:30 PM';
        document.getElementById('modalStatus').textContent = transaction.status;
        document.getElementById('modalStatus').className = transaction.status === 'Completed' ? 'badge bg-success' : 'badge bg-warning';
        document.getElementById('modalAmount').value = '$' + transaction.amount.toFixed(2);
        document.getElementById('modalFee').value = '$' + transaction.fee.toFixed(2);
        document.getElementById('modalTotal').value = '$' + transaction.total.toFixed(2);
        document.getElementById('modalMethod').textContent = transaction.method;
        document.getElementById('modalCategory').textContent = transaction.category;
        document.getElementById('modalRecipientName').value = transaction.recipient.name;
        document.getElementById('modalRecipientEmail').value = transaction.recipient.email;
        document.getElementById('modalRecipientPhone').value = transaction.recipient.phone;
        document.getElementById('modalRecipientAccount').value = transaction.recipient.account;
        document.getElementById('modalReference').value = transaction.reference;
        document.getElementById('modalDescription').value = transaction.description;
        document.getElementById('modalNotes').value = transaction.notes;
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('transactionDetailsModal'));
        modal.show();
    }
}

function downloadReceipt(transactionId) {
    const transaction = transactions.find(t => t.id === transactionId);
    if (transaction) {
        // Set current transaction for download modal
        document.getElementById('receiptEmail').value = 'john.doe@example.com';
        
        // Show download receipt modal
        const modal = new bootstrap.Modal(document.getElementById('downloadReceiptModal'));
        modal.show();
    }
}

function confirmDownloadReceipt() {
    const format = document.querySelector('input[name="receiptFormat"]:checked').value;
    const email = document.getElementById('receiptEmail').value;
    const includeDetails = document.getElementById('includeDetails').checked;
    
    alert(`Downloading receipt in ${format.toUpperCase()} format to ${email}${includeDetails ? ' with full details' : ''}...`);
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('downloadReceiptModal'));
    modal.hide();
}

function payAgain(transactionId) {
    const transaction = transactions.find(t => t.id === transactionId);
    if (transaction) {
        alert(`Initiating new payment to ${transaction.recipient.name} (${transaction.recipient.email})...`);
        
        // Close details modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('transactionDetailsModal'));
        modal.hide();
        
        // Redirect to payment page
        window.location.href = '/payments/initiate';
    }
}

function cancelPayment(transactionId) {
    if (confirm('Are you sure you want to cancel this payment?')) {
        alert('Payment cancelled: ' + transactionId);
    }
}

function retryPayment(transactionId) {
    alert('Retrying payment: ' + transactionId);
}

function disputePayment(transactionId) {
    alert('Opening dispute form for transaction: ' + transactionId);
}

function sendAgain(transactionId) {
    alert('Preparing to send payment again: ' + transactionId);
}

function exportHistory() {
    alert('Exporting payment history...');
}

function applyFilters() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('filterModal'));
    modal.hide();
    
    // Apply filters to main table
    const status = document.getElementById('modalStatusFilter').value;
    const method = document.getElementById('modalMethodFilter').value;
    
    document.getElementById('statusFilter').value = status;
    
    // Trigger filter
    document.getElementById('statusFilter').dispatchEvent(new Event('change'));
}
</script>
@endpush
