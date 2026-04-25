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

                <!-- Selected Month Transactions -->
                @if($selectedMonthTransactions->count() > 0)
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                All Transactions for {{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}
                                <small class="text-muted">({{ $selectedMonthTransactions->count() }} transactions)</small>
                            </h5>
                            <div>
                                <button class="btn btn-sm btn-outline-success" onclick="exportMonthData('{{ $selectedMonth }}', 'pdf')">
                                    <i class="bx bx-file"></i> PDF
                                </button>
                                <button class="btn btn-sm btn-outline-primary" onclick="exportMonthData('{{ $selectedMonth }}', 'excel')">
                                    <i class="bx bx-spreadsheet"></i> Excel
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Order Ref</th>
                                            <th>Payer</th>
                                            <th>Phone</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Method</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($selectedMonthTransactions as $transaction)
                                            <tr>
                                                <td>{{ $transaction->created_at->format('M j, Y H:i') }}</td>
                                                <td><code>{{ $transaction->order_reference }}</code></td>
                                                <td>{{ $transaction->payer_name ?? 'Unknown' }}</td>
                                                <td>{{ $transaction->phone ?? 'N/A' }}</td>
                                                <td><strong>{{ number_format($transaction->amount, 2) }} {{ $transaction->currency }}</strong></td>
                                                <td>
                                                    <span class="badge bg-{{ $transaction->status === 'SUCCESS' || $transaction->status === 'SETTLED' ? 'success' : ($transaction->status === 'PROCESSING' || $transaction->status === 'PENDING' ? 'warning' : 'danger') }}">
                                                        {{ $transaction->status }}
                                                    </span>
                                                </td>
                                                <td>{{ $transaction->payment_method ?? 'N/A' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No transactions found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
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

<!-- Detailed Transaction View Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="transactionDetailsOffcanvas" aria-labelledby="transactionDetailsOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="transactionDetailsOffcanvasLabel">
            <i class="bx bx-receipt me-2"></i>
            <span id="offcanvasMonthTitle">Transaction Details</span>
        </h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="card border-success">
                    <div class="card-body">
                        <h6 class="card-title text-success">Amount Entered</h6>
                        <h4 class="mb-0" id="totalEnteredAmount">0.00 TZS</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-info">
                    <div class="card-body">
                        <h6 class="card-title text-info">Amount Cashed Out</h6>
                        <h4 class="mb-0" id="totalCashedOutAmount">0.00 TZS</h4>
                    </div>
                </div>
            </div>
        </div>
        
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
    <div class="offcanvas-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="offcanvas">Close</button>
        <button type="button" class="btn btn-primary" onclick="downloadTransactionPDF()">
            <i class="bx bx-download me-1"></i>Download PDF
        </button>
    </div>
</div>

@push('scripts')
<script>
let currentMonthTransactions = [];
let currentMonth = '';

function viewMonthDetails(month) {
    currentMonth = month;
    const offcanvas = new bootstrap.Offcanvas(document.getElementById('transactionDetailsOffcanvas'));
    const monthData = @json($monthlyStatements);
    const monthInfo = monthData.find(m => m.month === month);
    
    if (monthInfo) {
        document.getElementById('offcanvasMonthTitle').textContent = `All Transactions for ${monthInfo.month_name} (${monthInfo.transaction_count} transactions)`;
        document.getElementById('totalEnteredAmount').textContent = `${number_format(monthInfo.total_amount, 2)} TZS`;
        document.getElementById('totalCashedOutAmount').textContent = `${number_format(monthInfo.total_settled_amount, 2)} TZS`;
    }
    
    offcanvas.show();
    
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
document.addEventListener('DOMContentLoaded', function() {
    const periodSelect = document.getElementById('periodSelect');
    const customRange = document.getElementById('customRange');
    
    periodSelect.addEventListener('change', function() {
        if (this.value === 'custom') {
            customRange.style.display = 'block';
        } else {
            customRange.style.display = 'none';
        }
    });
});

function filterStatements() {
    const period = document.getElementById('periodSelect').value;
    const account = document.getElementById('accountSelect').value;
    
    const rows = document.querySelectorAll('#statementsTable tbody tr');
    
    rows.forEach(row => {
        let showRow = true;
        
        // Account filter
        if (account !== 'all') {
            const accountBadge = row.cells[1].textContent.toLowerCase();
            if (!accountBadge.includes(account)) {
                showRow = false;
            }
        }
        
        // Period filter (simplified)
        if (period !== 'all') {
            // In a real application, this would filter by actual dates
            console.log('Filtering by period:', period);
        }
        
        row.style.display = showRow ? '' : 'none';
    });
}

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

function sendEmail() {
    const emailAddress = document.getElementById('emailAddress').value;
    const selectedStatements = Array.from(document.querySelectorAll('.statement-checkbox:checked'))
        .map(cb => cb.value);
    
    if (!emailAddress) {
        alert('Please enter an email address');
        return;
    }
    
    if (selectedStatements.length === 0) {
        alert('Please select at least one statement');
        return;
    }
    
    const format = document.querySelector('input[name="format"]:checked').value;
    const message = document.getElementById('emailMessage').value;
    
    alert(`Sending ${selectedStatements.length} statement(s) in ${format.toUpperCase()} format to: ${emailAddress}`);
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('emailModal'));
    modal.hide();
}

function selectMonth() {
    const monthSelect = document.getElementById('monthSelect');
    const currency = document.getElementById('currency').value;
    
    if (monthSelect.value) {
        window.location.href = `/report/statement?month=${monthSelect.value}&currency=${currency}`;
    }
}

function updateStatement() {
    const monthSelect = document.getElementById('monthSelect');
    const currency = document.getElementById('currency').value;
    
    window.location.href = `/report/statement?month=${monthSelect.value}&currency=${currency}`;
}

function viewMonthDetails(month) {
    window.location.href = `/report/statement?month=${month}`;
}

function exportPDF() {
    const month = document.getElementById('monthSelect').value;
    const currency = document.getElementById('currency').value;
    
    window.open(`/report/statement/export?format=pdf&month=${month}&currency=${currency}`, '_blank');
}

function exportExcel() {
    const month = document.getElementById('monthSelect').value;
    const currency = document.getElementById('currency').value;
    
    window.open(`/report/statement/export?format=excel&month=${month}&currency=${currency}`, '_blank');
}

function toggleAllStatements() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.statement-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}
</script>
@endpush
