@extends('layouts.app')

@section('title', 'System Health - FeedTan Pay')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Page Header -->
        <div class="row">
            <div class="col-12">
                <div class="card mb-6">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div class="mb-3 mb-md-0">
                                <h4 class="fw-bold mb-2">
                                    <i class="bx bx-heartbeat me-2 text-primary"></i>
                                    System Health Monitor
                                </h4>
                                <p class="text-muted mb-0">Real-time system performance and health monitoring dashboard</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success" onclick="refreshHealthData()">
                                    <i class="bx bx-refresh me-2"></i>Refresh Data
                                </button>
                                <button type="button" class="btn btn-primary" onclick="exportHealthReport()">
                                    <i class="bx bx-download me-2"></i>Export Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Health Score Overview -->
        <div class="row mb-6">
            <div class="col-12">
                <div class="card bg-gradient-primary text-white">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="card-title mb-3">Overall System Health Score</h5>
                                <div class="d-flex align-items-center mb-4">
                                    <h1 class="display-4 fw-bold mb-0">92%</h1>
                                    <span class="badge bg-success bg-opacity-25 text-success ms-3 px-3 py-2">Good</span>
                                </div>
                                <p class="mb-0">System is performing optimally with minor issues detected</p>
                            </div>
                            <div class="col-md-6 text-center">
                                <div class="position-relative d-inline-block">
                                    <svg width="200" height="200">
                                        <circle cx="100" cy="100" r="80" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="20"/>
                                        <circle cx="100" cy="100" r="80" fill="none" stroke="#fff" stroke-width="20" 
                                                stroke-dasharray="502" stroke-dashoffset="40" 
                                                transform="rotate(-90 100 100)" stroke-linecap="round"/>
                                    </svg>
                                    <div class="position-absolute top-50 start-50 translate-middle">
                                        <i class="bx bx-shield-check bx-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Status Section -->
        <div class="row mb-6">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-server me-2"></i>
                            System Status
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar bg-success bg-opacity-10 rounded-circle me-3" style="width: 48px; height: 48px;">
                                        <i class="bx bx-circle text-success fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">System Status</h6>
                                        <span class="badge bg-success">Online</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar bg-info bg-opacity-10 rounded-circle me-3" style="width: 48px; height: 48px;">
                                        <i class="bx bx-time-five text-info fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Uptime</h6>
                                        <h5 class="mb-0">99.9%</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar bg-primary bg-opacity-10 rounded-circle me-3" style="width: 48px; height: 48px;">
                                        <i class="bx bx-user text-primary fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Active Users</h6>
                                        <h5 class="mb-0">247</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar bg-warning bg-opacity-10 rounded-circle me-3" style="width: 48px; height: 48px;">
                                        <i class="bx bx-exchange text-warning fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Total Transactions (Today)</h6>
                                        <h5 class="mb-0">1,842</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar bg-danger bg-opacity-10 rounded-circle me-3" style="width: 48px; height: 48px;">
                                        <i class="bx bx-x-circle text-danger fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Failed Transactions</h6>
                                        <h5 class="mb-0">12</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Server Performance -->
        <div class="row mb-6">
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-chip me-2"></i>
                            Server Performance
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span>CPU Usage</span>
                                <span class="text-success">45%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: 45%"></div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span>RAM Usage</span>
                                <span class="text-warning">72%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-warning" style="width: 72%"></div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Disk Space</span>
                                <span class="text-info">68% Used / 32% Free</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-info" style="width: 68%"></div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Server Load</span>
                                <span class="text-success">2.4</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: 40%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Response Time</span>
                                <span class="text-success">120ms</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: 20%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- API & Payment Status -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-wifi me-2"></i>
                            API & Payment Status
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar bg-success bg-opacity-10 rounded-circle me-2" style="width: 12px; height: 12px;"></div>
                                    <span>STK Push Status</span>
                                </div>
                                <span class="badge bg-success">Working</span>
                            </div>
                        </div>
                        <div class="mb-4">
                            <h6 class="mb-3">Mobile Money APIs</h6>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span>M-Pesa</span>
                                <span class="badge bg-success">Online</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span>Tigo Pesa</span>
                                <span class="badge bg-success">Online</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span>Airtel Money</span>
                                <span class="badge bg-warning">Slow</span>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span>Callback/Webhook Status</span>
                                <span class="badge bg-success">Active</span>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span>API Response Time</span>
                                <span class="text-success">85ms</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Status -->
        <div class="row mb-6">
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-shield me-2"></i>
                            Security Status
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <span>SSL Certificate Status</span>
                                <span class="badge bg-success">Valid</span>
                            </div>
                            <small class="text-muted">Expires: Dec 15, 2024</small>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <span>Failed Login Attempts</span>
                                <span class="text-warning">3 (Last 24h)</span>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <span>Suspicious Activities</span>
                                <span class="text-success">None Detected</span>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <span>Firewall Status</span>
                                <span class="badge bg-success">Active</span>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span>2FA Status</span>
                                <span class="badge bg-success">Enabled</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transactions Health -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-credit-card me-2"></i>
                            Transactions Health
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <span>Successful Transactions</span>
                                <span class="text-success">1,830</span>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <span>Pending Transactions</span>
                                <span class="text-warning">8</span>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <span>Failed Transactions</span>
                                <span class="text-danger">12</span>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <span>Reversed Transactions</span>
                                <span class="text-info">2</span>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span>Success Rate</span>
                                <span class="text-success">99.2%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logs & Errors -->
        <div class="row mb-6">
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-file me-2"></i>
                            Logs & Errors
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h6 class="mb-3">System Logs</h6>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span>Info Logs</span>
                                <span class="text-info">1,234</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span>Warning Logs</span>
                                <span class="text-warning">45</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span>Error Logs</span>
                                <span class="text-danger">3</span>
                            </div>
                        </div>
                        <div class="mb-4">
                            <h6 class="mb-3">Payment Logs</h6>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span>Payment Requests</span>
                                <span class="text-success">1,842</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span>Payment Responses</span>
                                <span class="text-success">1,830</span>
                            </div>
                        </div>
                        <div>
                            <h6 class="mb-3">Recent Errors</h6>
                            <div class="small">
                                <div class="mb-2">
                                    <span class="text-danger">[10:45 AM]</span> - Database connection timeout
                                </div>
                                <div class="mb-2">
                                    <span class="text-warning">[09:30 AM]</span> - Airtel Money API slow response
                                </div>
                                <div>
                                    <span class="text-danger">[08:15 AM]</span> - Memory limit exceeded
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Backup & Storage -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-cloud me-2"></i>
                            Backup & Storage
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <span>Last Backup Date</span>
                                <span class="text-success">Dec 22, 2024 02:00 AM</span>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <span>Backup Status</span>
                                <span class="badge bg-success">Success</span>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <span>Storage Usage</span>
                                <span class="text-warning">68% (45GB / 66GB)</span>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span>Auto Backup Status</span>
                                <span class="badge bg-success">Enabled</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts & Notifications -->
        <div class="row mb-6">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-bell me-2"></i>
                            Alerts & Notifications
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span>Downtime Alerts</span>
                                    <span class="badge bg-success">Active</span>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span>Failed Payment Alerts</span>
                                    <span class="badge bg-success">Active</span>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span>Security Alerts</span>
                                    <span class="badge bg-success">Active</span>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span>Server Overload Alerts</span>
                                    <span class="badge bg-warning">Threshold: 80%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Features -->
        <div class="row mb-6">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-rocket me-2"></i>
                            Advanced Features
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span>Auto Restart Services</span>
                                    <span class="badge bg-success">Enabled</span>
                                </div>
                                <small class="text-muted">Auto-restart on failure detection</small>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span>Real-time Monitoring</span>
                                    <span class="badge bg-success">Live</span>
                                </div>
                                <small class="text-muted">Live updates every 30 seconds</small>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span>Email/SMS Alerts</span>
                                    <span class="badge bg-success">Active</span>
                                </div>
                                <small class="text-muted">Admin notifications enabled</small>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span>Health Score</span>
                                    <span class="badge bg-success">92% Good</span>
                                </div>
                                <small class="text-muted">Overall system health rating</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>




@push('scripts')
<script>
// Auto-refresh data every 30 seconds
setInterval(refreshHealthData, 30000);

function refreshHealthData() {
    // Simulate data refresh - in real implementation, this would fetch from API
    console.log('Refreshing health data...');
    
    // Add loading animation
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.style.opacity = '0.7';
    });
    
    setTimeout(() => {
        cards.forEach(card => {
            card.style.opacity = '1';
        });
    }, 500);
}

function exportHealthReport() {
    // Simulate report export
    const reportData = {
        timestamp: new Date().toISOString(),
        health_score: 92,
        system_status: 'Online',
        uptime: '99.9%',
        active_users: 247,
        total_transactions: 1842,
        failed_transactions: 12,
        cpu_usage: 45,
        ram_usage: 72,
        disk_usage: 68,
        server_load: 2.4,
        response_time: 120
    };
    
    // Create downloadable JSON file
    const dataStr = JSON.stringify(reportData, null, 2);
    const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
    
    const exportFileDefaultName = `health-report-${new Date().toISOString().split('T')[0]}.json`;
    
    const linkElement = document.createElement('a');
    linkElement.setAttribute('href', dataUri);
    linkElement.setAttribute('download', exportFileDefaultName);
    linkElement.click();
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
@endsection
