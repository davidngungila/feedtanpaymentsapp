@extends('layouts.app')

@section('title', 'Report Overview - FeedTan Pay')
@section('description', 'FeedTan Pay - Financial reports and analytics overview')

@section('content')
<div class="row">
    <!-- Summary Cards -->
    <div class="col-md-12">
        <div class="row mb-6">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-0">Total Transactions</h6>
                                <h3 class="mb-0">{{ number_format($totalTransactions ?? 0) }}</h3>
                                <small class="text-success">
                                    <i class="bx bx-trending-up"></i> {{ number_format($successAmount ?? 0, 2) }} TZS successful
                                </small>
                            </div>
                            <div class="avatar avatar-lg bg-primary bg-opacity-10 rounded-circle">
                                <i class="bx bx-wallet text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-0">Payment History</h6>
                                <h3 class="mb-0">{{ number_format($totalTransactions ?? 0) }}</h3>
                                <small class="text-primary">
                                    <i class="bx bx-list-ul"></i> Total Payments
                                </small>
                            </div>
                            <div class="avatar avatar-lg bg-primary bg-opacity-10 rounded-circle">
                                <i class="bx bx-list-ul text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-0">Successful</h6>
                                <h3 class="mb-0">{{ number_format($successCount ?? 0) }}</h3>
                                <small class="text-success">
                                    <i class="bx bx-check-circle"></i> {{ number_format($successAmount ?? 0, 2) }} TZS
                                </small>
                            </div>
                            <div class="avatar avatar-lg bg-success bg-opacity-10 rounded-circle">
                                <i class="bx bx-check-circle text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-0">Settled</h6>
                                <h3 class="mb-0">{{ number_format($settledCount ?? 0) }}</h3>
                                <small class="text-info">
                                    <i class="bx bx-check-double"></i> {{ number_format($settledAmount ?? 0, 2) }} TZS
                                </small>
                            </div>
                            <div class="avatar avatar-lg bg-info bg-opacity-10 rounded-circle">
                                <i class="bx bx-check-double text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-0">Total Settled</h6>
                                <h3 class="mb-0">{{ number_format(($successCount ?? 0) + ($settledCount ?? 0)) }}</h3>
                                <small class="text-success">
                                    <i class="bx bx-dollar"></i> {{ number_format(($successAmount ?? 0) + ($settledAmount ?? 0), 2) }} TZS
                                </small>
                            </div>
                            <div class="avatar avatar-lg bg-success bg-opacity-10 rounded-circle">
                                <i class="bx bx-dollar text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-0">Net Savings</h6>
                                <h3 class="mb-0">{{ number_format(($successAmount ?? 0) + ($settledAmount ?? 0), 2) }} TZS</h3>
                                <small class="text-success">
                                    <i class="bx bx-trending-up"></i> Total Settled Amount
                                </small>
                            </div>
                            <div class="avatar avatar-lg bg-info bg-opacity-10 rounded-circle">
                                <i class="bx bx-piggy-bank text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Charts Section -->
    <div class="col-md-8">
        <!-- Revenue Chart -->
        <div class="card mb-6">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Revenue Overview</h5>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-outline-primary active" onclick="changeChartPeriod('week')">Week</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="changeChartPeriod('month')">Month</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="changeChartPeriod('year')">Year</button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="300"></canvas>
            </div>
        </div>

        <!-- Expense Breakdown -->
        <div class="card mb-6">
            <div class="card-header">
                <h5 class="card-title mb-0">Expense Breakdown</h5>
            </div>
            <div class="card-body">
                <canvas id="expenseChart" height="300"></canvas>
            </div>
        </div>

            </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Quick Stats -->
        <div class="card mb-6">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Stats</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Transactions Today</span>
                        <strong>12</strong>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-primary" style="width: 60%"></div>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Pending Payments</span>
                        <strong>3</strong>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-warning" style="width: 25%"></div>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Overdue Bills</span>
                        <strong>1</strong>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-danger" style="width: 8%"></div>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Savings Goal</span>
                        <strong>78%</strong>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: 78%"></div>
                    </div>
                </div>
            </div>
        </div>

            </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Income',
                data: [1200, 1900, 1500, 2500, 2200, 3000, 2800],
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4
            }, {
                label: 'Expenses',
                data: [800, 1200, 900, 1500, 1300, 1800, 1600],
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value;
                        }
                    }
                }
            }
        }
    });

    // Expense Chart
    const expenseCtx = document.getElementById('expenseChart').getContext('2d');
    const expenseChart = new Chart(expenseCtx, {
        type: 'doughnut',
        data: {
            labels: ['Housing', 'Food & Dining', 'Utilities', 'Transportation', 'Shopping', 'Other'],
            datasets: [{
                data: [1200, 845.50, 425.75, 320, 245.50, 198],
                backgroundColor: [
                    '#007bff',
                    '#28a745',
                    '#ffc107',
                    '#17a2b8',
                    '#6c757d',
                    '#e83e8c'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                }
            }
        }
    });
});

function changeChartPeriod(period) {
    // Update button states
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // In a real application, this would fetch new data based on the period
    console.log('Changing chart period to:', period);
    
    // For demo purposes, just show an alert
    alert('Chart period changed to: ' + period);
}
</script>
@endpush
