@extends('layouts.app')

@section('title', 'Account Statement - FeedTan Pay')
@section('description', 'FeedTan Pay - Download and view account statements')

@section('content')
<div class="alert alert-info">
    <strong>Debug Info:</strong>
    Monthly Statements Count: {{ $monthlyStatements->count() ?? 0 }} |
    Selected Month: {{ $selectedMonth ?? 'None' }} |
    Currency: {{ $currency ?? 'TZS' }}
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Account Statements</h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary btn-sm" onclick="downloadAllStatements()">
                        <i class="bx bx-download me-1"></i>Download All
                    </button>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#emailModal">
                        <i class="bx bx-envelope me-1"></i>Email Statements
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Period Selection -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Select Month</label>
                        <select class="form-select" id="monthSelect" onchange="selectMonth()">
                            @forelse($monthlyStatements as $monthly)
                                <option value="{{ $monthly['month'] }}" {{ ($selectedMonth ?? '') === $monthly['month'] ? 'selected' : '' }}>
                                    {{ $monthly['month_name'] }} ({{ $monthly['transaction_count'] }} transactions)
                                </option>
                            @empty
                                <option value="">No data available</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Currency</label>
                        <select class="form-select" id="currency" onchange="updateStatement()">
                            <option value="TZS" {{ ($currency ?? 'TZS') === 'TZS' ? 'selected' : '' }}>TZS</option>
                            <option value="USD" {{ ($currency ?? 'TZS') === 'USD' ? 'selected' : '' }}>USD</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary btn-sm" onclick="exportPDF()">
                                <i class="bx bx-file-blank me-1"></i>PDF
                            </button>
                            <button class="btn btn-outline-success btn-sm" onclick="exportExcel()">
                                <i class="bx bx-spreadsheet me-1"></i>Excel
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Account Summary -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Account Statement</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="me-3">
                                        <h6 class="mb-0">Current Balance</h6>
                                        <h4 class="mb-0 text-primary">{{ number_format($accountSummary['current_balance'], 2) }} {{ $currency }}</h4>
                                        <small class="text-muted">Last Updated: {{ $accountSummary['last_updated'] ? \Carbon\Carbon::parse($accountSummary['last_updated'])->format('M j, Y H:i') : 'N/A' }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" class="form-control" value="{{ $accountSummary['start_date'] }}" id="startDate">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">End Date</label>
                                    <input type="date" class="form-control" value="{{ $accountSummary['end_date'] }}" id="endDate">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Currency</label>
                                    <select class="form-select" id="currency">
                                        <option value="TZS" {{ $currency == 'TZS' ? 'selected' : '' }}>TZS</option>
                                        <option value="USD" {{ $currency == 'USD' ? 'selected' : '' }}>USD</option>
                                        <option value="EUR" {{ $currency == 'EUR' ? 'selected' : '' }}>EUR</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Account Summary Details -->
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title mb-3">Account Summary</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong>Currency:</strong> {{ $currency }}</p>
                                        <p class="mb-2"><strong>Opening Balance:</strong> {{ number_format($accountSummary['opening_balance'], 2) }} {{ $currency }}</p>
                                        <p class="mb-2"><strong>Closing Balance:</strong> {{ number_format($accountSummary['closing_balance'], 2) }} {{ $currency }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong>Total Credits:</strong> {{ number_format($accountSummary['total_credits'], 2) }} {{ $currency }}</p>
                                        <p class="mb-2"><strong>Total Debits:</strong> {{ number_format($accountSummary['total_debits'], 2) }} {{ $currency }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Statements -->
                <div class="table-responsive mb-4">
                    <table class="table table-hover" id="monthlyStatementsTable">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Transactions</th>
                                <th>Total Amount</th>
                                <th>Success</th>
                                <th>Settled</th>
                                <th>Total Settled</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($monthlyStatements as $monthly)
                                <tr>
                                    <td>
                                        <div>
                                            <h6 class="mb-0">{{ $monthly['month_name'] }}</h6>
                                            <small class="text-muted">{{ $monthly['month'] }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $monthly['has_data'] ? 'primary' : 'secondary' }}">
                                            {{ $monthly['transaction_count'] }}
                                        </span>
                                    </td>
                                    <td><strong>{{ number_format($monthly['total_amount'], 2) }} {{ $currency }}</strong></td>
                                    <td>
                                        <span class="text-success">{{ number_format($monthly['success_amount'], 2) }}</span>
                                    </td>
                                    <td>
                                        <span class="text-info">{{ number_format($monthly['settled_amount'], 2) }}</span>
                                    </td>
                                    <td>
                                        <span class="text-primary fw-bold">{{ number_format($monthly['total_settled_amount'], 2) }}</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewMonthDetails('{{ $monthly['month'] }}')">
                                            <i class="bx bx-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <div class="py-4">
                                            <i class="bx bx-calendar fa-3x text-muted mb-3"></i>
                                            <p class="text-muted mb-0">No monthly data available</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Monthly Reconciliation Summary -->
                @if(isset($monthlyTotals) && ($monthlyTotals['total_count'] ?? 0) > 0)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                Monthly Reconciliation - {{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">Total Transactions</h6>
                                            <h3 class="text-primary">{{ $monthlyTotals['total_count'] }}</h3>
                                            <p class="text-muted mb-0">{{ number_format($monthlyTotals['total_amount'], 2) }} {{ $currency }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">Successful</h6>
                                            <h3 class="text-success">{{ $monthlyTotals['success_count'] }}</h3>
                                            <p class="text-muted mb-0">{{ number_format($monthlyTotals['success_amount'], 2) }} {{ $currency }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-warning">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">Pending</h6>
                                            <h3 class="text-warning">{{ $monthlyTotals['pending_count'] }}</h3>
                                            <p class="text-muted mb-0">{{ number_format($monthlyTotals['pending_amount'], 2) }} {{ $currency }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-danger">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">Failed</h6>
                                            <h3 class="text-danger">{{ $monthlyTotals['failed_count'] }}</h3>
                                            <p class="text-muted mb-0">{{ number_format($monthlyTotals['failed_amount'], 2) }} {{ $currency }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                            </div>
        </div>
    </div>
</div>

<!-- Statement Preview Modal -->
<div class="modal fade" id="statementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Statement Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="statementContent">
                    <!-- Statement content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="downloadCurrentStatement()">
                    <i class="bx bx-download me-2"></i>Download PDF
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Email Modal -->
<div class="modal fade" id="emailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Email Statements</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="emailAddress" placeholder="Enter email address">
                </div>
                <div class="mb-4">
                    <label class="form-label">Select Statements</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectAll" onchange="toggleAllStatements()">
                        <label class="form-check-label" for="selectAll">
                            Select All Statements
                        </label>
                    </div>
                    <div class="mt-2">
                        <div class="form-check">
                            <input class="form-check-input statement-checkbox" type="checkbox" value="2024-12-primary" checked>
                            <label class="form-check-label">December 2024 - Primary Account</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input statement-checkbox" type="checkbox" value="2024-11-primary">
                            <label class="form-check-label">November 2024 - Primary Account</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input statement-checkbox" type="checkbox" value="2024-10-primary">
                            <label class="form-check-label">October 2024 - Primary Account</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input statement-checkbox" type="checkbox" value="2024-12-savings" checked>
                            <label class="form-check-label">December 2024 - Savings Account</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input statement-checkbox" type="checkbox" value="2024-11-savings">
                            <label class="form-check-label">November 2024 - Savings Account</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input statement-checkbox" type="checkbox" value="2024-10-savings">
                            <label class="form-check-label">October 2024 - Savings Account</label>
                        </div>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Message (Optional)</label>
                    <textarea class="form-control" id="emailMessage" rows="3" placeholder="Add a message to the email"></textarea>
                </div>
                <div class="mb-4">
                    <label class="form-label">Format</label>
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="format" id="pdf" value="pdf" checked>
                        <label class="btn btn-outline-primary" for="pdf">PDF</label>
                        
                        <input type="radio" class="btn-check" name="format" id="excel" value="excel">
                        <label class="btn btn-outline-primary" for="excel">Excel</label>
                        
                        <input type="radio" class="btn-check" name="format" id="csv" value="csv">
                        <label class="btn btn-outline-primary" for="csv">CSV</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="sendEmail()">
                    <i class="bx bx-send me-2"></i>Send Email
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Transaction View Modal -->
<div class="modal fade" id="transactionDetailsModal" tabindex="-1" aria-labelledby="transactionDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transactionDetailsModalLabel">
                    <i class="bx bx-receipt me-2"></i>
                    <span id="modalMonthTitle">Transaction Details</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Monthly Reconciliation Summary -->
                <div class="card mb-4" id="monthlyReconciliationCard">
                    <div class="card-header">
                        <h6 class="card-title mb-0" id="reconciliationTitle">Monthly Reconciliation</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card border-primary">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Total Transactions</h6>
                                        <h3 class="text-primary" id="totalCount">0</h3>
                                        <p class="text-muted mb-0" id="totalAmount">0.00 TZS</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Successful</h6>
                                        <h3 class="text-success" id="successCount">0</h3>
                                        <p class="text-muted mb-0" id="successAmount">0.00 TZS</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-warning">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Pending</h6>
                                        <h3 class="text-warning" id="pendingCount">0</h3>
                                        <p class="text-muted mb-0" id="pendingAmount">0.00 TZS</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-danger">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Failed</h6>
                                        <h3 class="text-danger" id="failedCount">0</h3>
                                        <p class="text-muted mb-0" id="failedAmount">0.00 TZS</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transaction Details Table -->
                <div class="table-responsive">
                    <table class="table table-striped" id="transactionDetailsTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Order Reference</th>
                                <th>Payer Name</th>
                                <th>Phone</th>
                                <th>Amount Entered</th>
                                <th>Amount Cashed Out</th>
                                <th>Status</th>
                                <th>Payment Method</th>
                            </tr>
                        </thead>
                        <tbody id="transactionDetailsBody">
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Loading transaction details...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="downloadTransactionPDF()">
                    <i class="bx bx-download me-1"></i>Download PDF
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentMonthTransactions = [];
let currentMonth = '';

// JavaScript equivalent of PHP's number_format function
function number_format(number, decimals = 2, dec_point = '.', thousands_sep = ',') {
    // Convert number to string and handle negative numbers
    const strNumber = parseFloat(number).toFixed(decimals);
    const parts = strNumber.split('.');
    
    // Add thousands separator
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousands_sep);
    
    // Join with decimal point
    return parts.join(dec_point);
}

function viewMonthDetails(month) {
    currentMonth = month;
    const modal = new bootstrap.Modal(document.getElementById('transactionDetailsModal'));
    const monthData = @json($monthlyStatements);
    const monthInfo = monthData.find(m => m.month === month);
    
    if (monthInfo) {
        // Update modal title
        document.getElementById('modalMonthTitle').textContent = `All Transactions for ${monthInfo.month_name} (${monthInfo.transaction_count} transactions)`;
        
        // Update Monthly Reconciliation title
        document.getElementById('reconciliationTitle').textContent = `Monthly Reconciliation - ${monthInfo.month_name}`;
        
        // Populate reconciliation summary
        document.getElementById('totalCount').textContent = monthInfo.transaction_count || 0;
        document.getElementById('totalAmount').textContent = `${number_format(monthInfo.total_amount || 0, 2)} TZS`;
        document.getElementById('successCount').textContent = monthInfo.success_count || 0;
        document.getElementById('successAmount').textContent = `${number_format(monthInfo.success_amount || 0, 2)} TZS`;
        document.getElementById('pendingCount').textContent = monthInfo.pending_count || 0;
        document.getElementById('pendingAmount').textContent = `${number_format(monthInfo.pending_amount || 0, 2)} TZS`;
        document.getElementById('failedCount').textContent = monthInfo.failed_count || 0;
        document.getElementById('failedAmount').textContent = `${number_format(monthInfo.failed_amount || 0, 2)} TZS`;
    }
    
    modal.show();
    
    // Load transaction details for the month
    loadTransactionDetails(month);
}

function loadTransactionDetails(month) {
    const tbody = document.getElementById('transactionDetailsBody');
    tbody.innerHTML = `
        <tr>
            <td colspan="8" class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading transaction details...</p>
            </td>
        </tr>
    `;
    
    fetch(`/report/statement/transactions?month=${month}`)
        .then(response => response.json())
        .then(data => {
            currentMonthTransactions = data.transactions;
            displayTransactionDetails(data.transactions);
        })
        .catch(error => {
            console.error('Error loading transaction details:', error);
            showErrorToast('Failed to load transaction details. Please try again.', 'Load Error');
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center text-muted">
                        <i class="bx bx-info-circle me-2"></i>
                        No transactions found for this month
                    </td>
                </tr>
            `;
        });
}

function displayTransactionDetails(transactions) {
    const tbody = document.getElementById('transactionDetailsBody');
    
    if (transactions.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center text-muted">
                    <i class="bx bx-info-circle me-2"></i>
                    No transactions found for this month
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = transactions.map(transaction => `
        <tr>
            <td>${formatDate(transaction.created_at)}</td>
            <td><code>${transaction.order_reference}</code></td>
            <td>${transaction.payer_name || 'Unknown'}</td>
            <td>${transaction.phone || 'N/A'}</td>
            <td class="text-success">${number_format(transaction.amount, 2)} TZS</td>
            <td class="text-info">${number_format(transaction.amount, 2)} TZS</td>
            <td>
                <span class="badge bg-${getStatusColor(transaction.status)}">
                    ${transaction.status}
                </span>
            </td>
            <td>${transaction.payment_method || 'N/A'}</td>
        </tr>
    `).join('');
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getStatusColor(status) {
    switch(status) {
        case 'SUCCESS': return 'success';
        case 'SETTLED': return 'info';
        case 'PROCESSING':
        case 'PENDING': return 'warning';
        case 'FAILED': return 'danger';
        default: return 'secondary';
    }
}

function downloadTransactionPDF() {
    const url = `/report/statement/export?format=pdf&month=${currentMonth}&currency=TZS`;
    window.open(url, '_blank');
    showSuccessToast('PDF statement download started. Check your downloads folder.', 'Download Started');
}
</script>
@endpush

</div>
@endsection

@push('scripts')
<script>
// Removed filter functionality as elements don't exist in current view

function viewStatement(statementId) {
    // Load statement preview
    const statementContent = document.getElementById('statementContent');
    statementContent.innerHTML = `
        <div class="text-center py-8">
            <i class="bx bx-loader-alt bx-spin" style="font-size: 3rem;"></i>
            <p class="mt-3">Loading statement...</p>
        </div>
    `;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('statementModal'));
    modal.show();
    
    // Simulate loading statement content
    setTimeout(() => {
        statementContent.innerHTML = `
            <div class="statement-preview">
                <div class="text-center mb-4">
                    <h4>Account Statement</h4>
                    <p class="text-muted">${statementId}</p>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>Account Information</h6>
                        <p><strong>Account Number:</strong> ****1234</p>
                        <p><strong>Account Type:</strong> Primary Account</p>
                        <p><strong>Period:</strong> December 1-15, 2024</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Summary</h6>
                        <p><strong>Opening Balance:</strong> $3,234.75</p>
                        <p><strong>Closing Balance:</strong> $8,234.75</p>
                        <p><strong>Net Change:</strong> +$5,000.00</p>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Debit</th>
                                <th>Credit</th>
                                <th>Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Dec 1, 2024</td>
                                <td>Opening Balance</td>
                                <td>-</td>
                                <td>-</td>
                                <td>$3,234.75</td>
                            </tr>
                            <tr>
                                <td>Dec 15, 2024</td>
                                <td>Salary Deposit</td>
                                <td>-</td>
                                <td>$5,000.00</td>
                                <td>$8,234.75</td>
                            </tr>
                            <tr>
                                <td>Dec 14, 2024</td>
                                <td>Grocery Shopping</td>
                                <td>$245.50</td>
                                <td>-</td>
                                <td>$3,234.75</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }, 1000);
}

function exportMonthData(month, format) {
    const currency = document.getElementById('currency').value;
    const url = `/report/statement/export?month=${month}&format=${format}&currency=${currency}`;
    window.open(url, '_blank');
}

function downloadStatement(statementId) {
    alert('Downloading statement: ' + statementId);
}

function downloadCurrentStatement() {
    alert('Downloading current statement as PDF...');
}

function emailStatement(statementId) {
    const email = prompt('Enter email address:');
    if (email) {
        alert('Statement ' + statementId + ' will be emailed to: ' + email);
    }
}

function downloadAllStatements() {
    if (confirm('Download all available statements? This may take a moment.')) {
        alert('Downloading all statements...');
    }
}

// Removed all functions that reference non-existent DOM elements
</script>
@endpush
