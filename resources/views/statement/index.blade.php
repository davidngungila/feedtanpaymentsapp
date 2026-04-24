@extends('layouts.app')

@section('title', 'Account Statement - FeedTan Pay')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Account Statement</h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm" onclick="exportStatement()">
                            <i class="bx bx-download me-1"></i>Export
                        </button>
                        <button class="btn btn-success btn-sm" onclick="refreshStatement()">
                            <i class="bx bx-refresh me-1"></i>Refresh
                        </button>
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

                    @if($error ?? null)
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ $error }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Balance Card -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <h6 class="card-title mb-2">Current Balance</h6>
                                            @if(!empty($balance))
                                                <h2 class="mb-0">
                                                    {{ number_format($balance[0]['balance'] ?? 0, 2) }} {{ $balance[0]['currency'] ?? 'TZS' }}
                                                </h2>
                                            @else
                                                <h2 class="mb-0">0.00 TZS</h2>
                                            @endif
                                        </div>
                                        <div class="col-md-6 text-md-end">
                                            <small class="d-block">Last Updated: {{ now()->format('M j, Y H:i') }}</small>
                                            <button class="btn btn-light btn-sm mt-2" onclick="refreshBalance()">
                                                <i class="bx bx-refresh"></i> Refresh Balance
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label for="startDate" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="startDate" value="{{ $startDate ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label for="endDate" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="endDate" value="{{ $endDate ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label for="currency" class="form-label">Currency</label>
                            <select class="form-select" id="currency">
                                <option value="TZS" {{ ($currency ?? 'TZS') === 'TZS' ? 'selected' : '' }}>TZS</option>
                                <option value="USD" {{ ($currency ?? 'TZS') === 'USD' ? 'selected' : '' }}>USD</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button class="btn btn-primary" onclick="filterStatement()">
                                    <i class="bx bx-filter me-1"></i>Apply Filters
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Account Details -->
                    @if(!empty($statement['accountDetails']))
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card border">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Account Summary</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <p><strong>Currency:</strong> {{ $statement['accountDetails']['currency'] ?? 'N/A' }}</p>
                                            </div>
                                            <div class="col-md-3">
                                                <p><strong>Opening Balance:</strong> {{ number_format($statement['accountDetails']['openingBalance'] ?? 0, 2) }} {{ $statement['accountDetails']['currency'] ?? 'N/A' }}</p>
                                            </div>
                                            <div class="col-md-3">
                                                <p><strong>Closing Balance:</strong> {{ number_format($statement['accountDetails']['closingBalance'] ?? 0, 2) }} {{ $statement['accountDetails']['currency'] ?? 'N/A' }}</p>
                                            </div>
                                            <div class="col-md-3">
                                                <p><strong>Total Credits:</strong> {{ number_format($statement['accountDetails']['totalCredits'] ?? 0, 2) }} {{ $statement['accountDetails']['currency'] ?? 'N/A' }}</p>
                                            </div>
                                            <div class="col-md-3">
                                                <p><strong>Total Debits:</strong> {{ number_format($statement['accountDetails']['totalDebits'] ?? 0, 2) }} {{ $statement['accountDetails']['currency'] ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Transactions Table -->
                    <div class="table-responsive">
                        <table class="table table-hover" id="statementTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th>Entry</th>
                                    <th>Amount</th>
                                    <th>Balance</th>
                                    <th>Order Ref</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(!empty($statement['transactions']))
                                    @foreach($statement['transactions'] as $transaction)
                                        <tr>
                                            <td>{{ $transaction['date'] ?? 'N/A' }}</td>
                                            <td>{{ $transaction['description'] ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-label-{{ ($transaction['type'] ?? '') === 'Payment' ? 'primary' : 'info' }}">
                                                    {{ $transaction['type'] ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ ($transaction['entry'] ?? '') === 'Credit' ? 'success' : 'danger' }}">
                                                    {{ $transaction['entry'] ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="{{ ($transaction['entry'] ?? '') === 'Credit' ? 'text-success' : 'text-danger' }}">
                                                    {{ ($transaction['entry'] ?? '') === 'Credit' ? '+' : '-' }}{{ number_format($transaction['amount'] ?? 0, 2) }} {{ $transaction['currency'] ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>{{ number_format($transaction['balance'] ?? 0, 2) }} {{ $transaction['currency'] ?? 'N/A' }}</td>
                                            <td>
                                                @if(!empty($transaction['orderReference']))
                                                    <code>{{ $transaction['orderReference'] }}</code>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <div class="py-4">
                                                <i class="bx bx-receipt fa-3x text-muted mb-3"></i>
                                                <p class="text-muted mb-0">No transactions found for the selected period</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function refreshBalance() {
    const btn = event.target;
    const originalContent = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="bx bx-refresh bx-spin"></i> Refreshing...';
    
    setTimeout(() => {
        window.location.reload();
    }, 1000);
}

function refreshStatement() {
    window.location.reload();
}

function filterStatement() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const currency = document.getElementById('currency').value;
    
    const params = new URLSearchParams();
    if (startDate) params.append('start_date', startDate);
    if (endDate) params.append('end_date', endDate);
    if (currency) params.append('currency', currency);
    
    window.location.href = '/statement?' + params.toString();
}

function exportStatement() {
    // Get current filters
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const currency = document.getElementById('currency').value;
    
    const params = new URLSearchParams();
    if (startDate) params.append('start_date', startDate);
    if (endDate) params.append('end_date', endDate);
    if (currency) params.append('currency', currency);
    params.append('export', 'pdf');
    
    window.open('/statement?' + params.toString(), '_blank');
}

// Auto-refresh balance every 5 minutes
setInterval(() => {
    const balanceElement = document.querySelector('.card.bg-primary h2');
    if (balanceElement) {
        // Add a subtle animation to indicate refresh
        balanceElement.style.opacity = '0.5';
        setTimeout(() => {
            balanceElement.style.opacity = '1';
        }, 500);
    }
}, 300000); // 5 minutes
</script>
@endsection
