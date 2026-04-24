@extends('layouts.app')

@section('title', 'Balance Report - FeedTan Pay')
@section('description', 'FeedTan Pay - Account balance and transaction history')

@section('content')
<div class="row">
    <!-- Balance Overview -->
    <div class="col-md-12">
        <div class="card mb-6">
            <div class="card-header">
                <h5 class="card-title mb-0">Balance Overview</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center">
                            @if(!empty($balance))
                                <h2 class="mb-2 text-primary">{{ number_format($balance[0]['balance'] ?? 0, 2) }} {{ $balance[0]['currency'] ?? 'TZS' }}</h2>
                            @else
                                <h2 class="mb-2 text-primary">0.00 TZS</h2>
                            @endif
                            <p class="text-muted">Current Balance</p>
                            <div class="d-flex justify-content-center gap-2">
                                <span class="badge bg-success">
                                    <i class="bx bx-info-circle"></i> Live Data
                                </span>
                                <small class="text-muted">from ClickPesa API</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <canvas id="balanceChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Account Details -->
    <div class="col-md-8">
        <!-- Currency Statistics -->
        <div class="row mb-6">
            @forelse($currencyStats as $stat)
                <div class="col-md-6 mb-4">
                    <div class="card border-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="mb-0">{{ $stat->currency }} Account</h6>
                                    <small class="text-muted">{{ $stat->count }} transactions</small>
                                </div>
                                <div class="avatar bg-primary bg-opacity-10 rounded-circle">
                                    <i class="bx bx-wallet text-primary"></i>
                                </div>
                            </div>
                            <h4 class="mb-3">{{ number_format($stat->total, 2) }} {{ $stat->currency }}</h4>
                            <div class="d-flex justify-content-between mb-2">
                                <small class="text-muted">Transaction Count</small>
                                <strong>{{ $stat->count }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">Average Amount</small>
                                <strong>{{ number_format($stat->count > 0 ? $stat->total / $stat->count : 0, 2) }} {{ $stat->currency }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-md-12">
                    <div class="card border-secondary">
                        <div class="card-body text-center">
                            <i class="bx bx-info-circle fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No currency statistics available</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Balance History -->
        <div class="card mb-6">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Balance History</h5>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-outline-primary active" onclick="changePeriod('7d')">7 Days</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="changePeriod('30d')">30 Days</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="changePeriod('90d')">90 Days</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="changePeriod('1y')">1 Year</button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="historyChart" height="300"></canvas>
            </div>
        </div>

        <!-- Transaction List -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Recent Transactions</h5>
                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm" style="width: auto;">
                        <option>All Accounts</option>
                        <option>Primary Account</option>
                        <option>Savings Account</option>
                    </select>
                    <button class="btn btn-sm btn-outline-primary">
                        <i class="bx bx-filter"></i> Filter
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th>Account</th>
                                <th>Amount</th>
                                <th>Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Dec 15, 2024</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-success bg-opacity-10 rounded-circle me-2" style="width: 32px; height: 32px;">
                                            <i class="bx bx-dollar text-success"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Salary Deposit</h6>
                                            <small class="text-muted">Monthly salary</small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-label-success">Income</span></td>
                                <td>Primary</td>
                                <td class="text-success">+$5,000.00</td>
                                <td>$8,234.75</td>
                            </tr>
                            <tr>
                                <td>Dec 14, 2024</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-primary bg-opacity-10 rounded-circle me-2" style="width: 32px; height: 32px;">
                                            <i class="bx bx-shopping-bag text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Walmart</h6>
                                            <small class="text-muted">Grocery shopping</small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-label-primary">Food</span></td>
                                <td>Primary</td>
                                <td class="text-danger">-$245.50</td>
                                <td>$3,234.75</td>
                            </tr>
                            <tr>
                                <td>Dec 13, 2024</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-warning bg-opacity-10 rounded-circle me-2" style="width: 32px; height: 32px;">
                                            <i class="bx bx-bolt text-warning"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Con Edison</h6>
                                            <small class="text-muted">Electric bill payment</small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-label-warning">Utilities</span></td>
                                <td>Primary</td>
                                <td class="text-danger">-$145.50</td>
                                <td>$3,480.25</td>
                            </tr>
                            <tr>
                                <td>Dec 12, 2024</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-success bg-opacity-10 rounded-circle me-2" style="width: 32px; height: 32px;">
                                            <i class="bx bx-laptop text-success"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Freelance Payment</h6>
                                            <small class="text-muted">Web design project</small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-label-success">Income</span></td>
                                <td>Primary</td>
                                <td class="text-success">+$1,250.00</td>
                                <td>$3,625.75</td>
                            </tr>
                            <tr>
                                <td>Dec 11, 2024</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-info bg-opacity-10 rounded-circle me-2" style="width: 32px; height: 32px;">
                                            <i class="bx bx-play-circle text-info"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Netflix</h6>
                                            <small class="text-muted">Monthly subscription</small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-label-info">Entertainment</span></td>
                                <td>Primary</td>
                                <td class="text-danger">-$19.99</td>
                                <td>$2,375.75</td>
                            </tr>
                            <tr>
                                <td>Dec 10, 2024</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-success bg-opacity-10 rounded-circle me-2" style="width: 32px; height: 32px;">
                                            <i class="bx bx-piggy-bank text-success"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Transfer to Savings</h6>
                                            <small class="text-muted">Monthly savings</small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-label-secondary">Transfer</span></td>
                                <td>Primary</td>
                                <td class="text-danger">-$500.00</td>
                                <td>$2,395.74</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">Previous</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Next</a>
                        </li>
                    </ul>
                </nav>
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
                        <span>Today's Change</span>
                        <strong class="text-success">+$125.50</strong>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: 75%"></div>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>This Week</span>
                        <strong class="text-success">+$1,250.00</strong>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-info" style="width: 60%"></div>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>This Month</span>
                        <strong class="text-danger">-$890.25</strong>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-warning" style="width: 35%"></div>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Year to Date</span>
                        <strong class="text-success">+$3,458.50</strong>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-primary" style="width: 85%"></div>
                    </div>
                </div>
            </div>
        </div>

        
        <!-- Budget Status -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Budget Status</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <div>
                            <h6 class="mb-0">Food & Dining</h6>
                            <small class="text-muted">$845.50 / $1,000</small>
                        </div>
                        <span class="badge bg-success">On Track</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: 84.5%"></div>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <div>
                            <h6 class="mb-0">Entertainment</h6>
                            <small class="text-muted">$320.00 / $300</small>
                        </div>
                        <span class="badge bg-warning">Over Budget</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-warning" style="width: 106.7%"></div>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <div>
                            <h6 class="mb-0">Transportation</h6>
                            <small class="text-muted">$180.00 / $400</small>
                        </div>
                        <span class="badge bg-success">Good</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: 45%"></div>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between mb-2">
                        <div>
                            <h6 class="mb-0">Shopping</h6>
                            <small class="text-muted">$245.50 / $500</small>
                        </div>
                        <span class="badge bg-success">On Track</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: 49.1%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
<script>
// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts after a small delay to ensure DOM is ready
    setTimeout(function() {
        initializeCharts();
    }, 100);
});

function initializeCharts() {
    try {
        // Balance Chart
        const balanceCanvas = document.getElementById('balanceChart');
        if (balanceCanvas) {
            const balanceCtx = balanceCanvas.getContext('2d');
            new Chart(balanceCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Balance',
                        data: [8000, 8500, 9200, 8800, 9500, 10200, 11000, 10500, 11200, 11800, 11500, 12458],
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2,
                        pointBackgroundColor: '#198754',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#198754',
                            borderWidth: 1,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return 'Balance: $' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            },
                            ticks: {
                                callback: function(value) {
                                    return '$' + (value / 1000).toFixed(0) + 'k';
                                },
                                color: '#6c757d',
                                font: {
                                    size: 11
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#6c757d',
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });
        }

        // History Chart
        const historyCanvas = document.getElementById('historyChart');
        if (historyCanvas) {
            const historyCtx = historyCanvas.getContext('2d');
            new Chart(historyCtx, {
                type: 'bar',
                data: {
                    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                    datasets: [{
                        label: 'Income',
                        data: [2500, 3200, 2800, 3500],
                        backgroundColor: '#198754',
                        borderColor: '#198754',
                        borderWidth: 1,
                        borderRadius: 4
                    }, {
                        label: 'Expenses',
                        data: [1800, 2100, 1900, 2300],
                        backgroundColor: '#dc3545',
                        borderColor: '#dc3545',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 15,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#198754',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': $' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            },
                            ticks: {
                                callback: function(value) {
                                    return '$' + (value / 1000).toFixed(1) + 'k';
                                },
                                color: '#6c757d',
                                font: {
                                    size: 11
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#6c757d',
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });
        }

        // Spending Chart
        const spendingCanvas = document.getElementById('spendingChart');
        if (spendingCanvas) {
            const spendingCtx = spendingCanvas.getContext('2d');
            new Chart(spendingCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Food', 'Transport', 'Entertainment', 'Shopping', 'Utilities', 'Other'],
                    datasets: [{
                        data: [845.50, 320, 320, 245.50, 425.75, 198],
                        backgroundColor: [
                            '#198754',
                            '#0d6efd',
                            '#ffc107',
                            '#6c757d',
                            '#fd7e14',
                            '#e83e8c'
                        ],
                        borderWidth: 2,
                        borderColor: '#fff',
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true,
                                font: {
                                    size: 11
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#198754',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = '$' + context.parsed.toLocaleString();
                                    const percentage = Math.round((context.parsed / context.dataset.data.reduce((a, b) => a + b, 0)) * 100);
                                    return label + ': ' + value + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }

        console.log('All charts initialized successfully');
    } catch (error) {
        console.error('Error initializing charts:', error);
    }
}

function changePeriod(period) {
    try {
        // Update button states
        document.querySelectorAll('.btn-group .btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.classList.add('active');
        
        // In a real application, this would fetch new data based on the period
        console.log('Changing period to:', period);
        
        // Re-initialize charts with new data (placeholder for now)
        initializeCharts();
    } catch (error) {
        console.error('Error changing period:', error);
    }
}
</script>
@endpush
