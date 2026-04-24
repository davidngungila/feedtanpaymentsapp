@extends('layouts.app')

@section('title', 'Security Logs - FeedTan Pay')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-1">Security Logs</h4>
                        <p class="text-muted mb-0">Monitor and analyze security events across your FeedTan Pay system</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="exportLogs()">
                            <i class='bx bx-download me-1'></i> Export Logs
                        </button>
                        <button type="button" class="btn btn-outline-danger" onclick="clearOldLogs()">
                            <i class='bx bx-trash me-1'></i> Clear Old Logs
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Overview -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Total Events</h6>
                                <h3 class="mb-0">1,247</h3>
                            </div>
                            <div class="avatar avatar-lg">
                                <i class='bx bx-list-check bx-lg'></i>
                            </div>
                        </div>
                        <small>Last 24 hours</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Successful Logins</h6>
                                <h3 class="mb-0">892</h3>
                            </div>
                            <div class="avatar avatar-lg">
                                <i class='bx bx-log-in-circle bx-lg'></i>
                            </div>
                        </div>
                        <small>71.5% success rate</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Failed Attempts</h6>
                                <h3 class="mb-0">355</h3>
                            </div>
                            <div class="avatar avatar-lg">
                                <i class='bx bx-error-circle bx-lg'></i>
                            </div>
                        </div>
                        <small>28.5% failure rate</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Blocked IPs</h6>
                                <h3 class="mb-0">47</h3>
                            </div>
                            <div class="avatar avatar-lg">
                                <i class='bx bx-block bx-lg'></i>
                            </div>
                        </div>
                        <small>Currently blocked</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Filters</h5>
                    </div>
                    <div class="card-body">
                        <form id="filterForm">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Event Type</label>
                                    <select class="form-select" id="eventType">
                                        <option value="">All Events</option>
                                        <option value="login">Login Attempts</option>
                                        <option value="logout">Logouts</option>
                                        <option value="password_change">Password Changes</option>
                                        <option value="2fa">2FA Events</option>
                                        <option value="admin">Admin Actions</option>
                                        <option value="suspicious">Suspicious Activity</option>
                                        <option value="blocked">Blocked Attempts</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Severity</label>
                                    <select class="form-select" id="severity">
                                        <option value="">All Levels</option>
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="critical">Critical</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Date Range</label>
                                    <select class="form-select" id="dateRange">
                                        <option value="1h">Last Hour</option>
                                        <option value="24h" selected>Last 24 Hours</option>
                                        <option value="7d">Last 7 Days</option>
                                        <option value="30d">Last 30 Days</option>
                                        <option value="custom">Custom Range</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Search</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="searchTerm" placeholder="Search logs...">
                                        <button class="btn btn-outline-secondary" type="button" onclick="applyFilters()">
                                            <i class='bx bx-search'></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3" id="customDateRange" style="display: none;">
                                <div class="col-md-6">
                                    <label class="form-label">From Date</label>
                                    <input type="datetime-local" class="form-control" id="fromDate">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">To Date</label>
                                    <input type="datetime-local" class="form-control" id="toDate">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Logs Table -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Security Events</h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary" onclick="refreshLogs()">
                                <i class='bx bx-refresh me-1'></i> Refresh
                            </button>
                            <button class="btn btn-sm btn-outline-success" onclick="toggleAutoRefresh()">
                                <i class='bx bx-sync me-1'></i> Auto Refresh
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="securityLogsTable">
                                <thead>
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>Event Type</th>
                                        <th>User</th>
                                        <th>IP Address</th>
                                        <th>Details</th>
                                        <th>Severity</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Sample log entries -->
                                    <tr>
                                        <td>
                                            <small>2026-04-23 07:45:12</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">Login Success</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm bg-primary text-white me-2">
                                                    JD
                                                </div>
                                                <div>
                                                    <div class="fw-medium">John Doe</div>
                                                    <small class="text-muted">john.doe@feedtanpay.co.tz</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-monospace">192.168.1.100</span>
                                        </td>
                                        <td>
                                            <small>Login successful from trusted device</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">Low</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">Success</span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                    <i class='bx bx-dots-horizontal-rounded'></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#" onclick="viewLogDetails(1)">
                                                        <i class='bx bx-eye me-2'></i> View Details
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="blockIP('192.168.1.100')">
                                                        <i class='bx bx-block me-2'></i> Block IP
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="investigateEvent(1)">
                                                        <i class='bx bx-search me-2'></i> Investigate
                                                    </a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <small>2026-04-23 07:42:35</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger">Login Failed</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm bg-danger text-white me-2">
                                                    ?
                                                </div>
                                                <div>
                                                    <div class="fw-medium">Unknown User</div>
                                                    <small class="text-muted">admin@feedtanpay.co.tz</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-monospace">203.45.67.89</span>
                                        </td>
                                        <td>
                                            <small>Invalid password - 3rd attempt</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">Medium</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger">Failed</span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                    <i class='bx bx-dots-horizontal-rounded'></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#" onclick="viewLogDetails(2)">
                                                        <i class='bx bx-eye me-2'></i> View Details
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="blockIP('203.45.67.89')">
                                                        <i class='bx bx-block me-2'></i> Block IP
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="investigateEvent(2)">
                                                        <i class='bx bx-search me-2'></i> Investigate
                                                    </a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <small>2026-04-23 07:40:18</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">Password Changed</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm bg-info text-white me-2">
                                                    AS
                                                </div>
                                                <div>
                                                    <div class="fw-medium">Alice Smith</div>
                                                    <small class="text-muted">alice.smith@feedtanpay.co.tz</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-monospace">192.168.1.105</span>
                                        </td>
                                        <td>
                                            <small>Password changed successfully</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">Medium</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">Success</span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                    <i class='bx bx-dots-horizontal-rounded'></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#" onclick="viewLogDetails(3)">
                                                        <i class='bx bx-eye me-2'></i> View Details
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="investigateEvent(3)">
                                                        <i class='bx bx-search me-2'></i> Investigate
                                                    </a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <small>2026-04-23 07:38:45</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">2FA Enabled</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm bg-success text-white me-2">
                                                    BJ
                                                </div>
                                                <div>
                                                    <div class="fw-medium">Bob Johnson</div>
                                                    <small class="text-muted">bob.johnson@feedtanpay.co.tz</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-monospace">192.168.1.102</span>
                                        </td>
                                        <td>
                                            <small>2FA enabled via SMS</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">Low</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">Success</span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                    <i class='bx bx-dots-horizontal-rounded'></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#" onclick="viewLogDetails(4)">
                                                        <i class='bx bx-eye me-2'></i> View Details
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="investigateEvent(4)">
                                                        <i class='bx bx-search me-2'></i> Investigate
                                                    </a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <small>2026-04-23 07:35:22</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger">Suspicious Activity</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm bg-warning text-white me-2">
                                                    SYS
                                                </div>
                                                <div>
                                                    <div class="fw-medium">System Alert</div>
                                                    <small class="text-muted">Multiple failed attempts</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-monospace">203.45.67.89</span>
                                        </td>
                                        <td>
                                            <small>Multiple login attempts from different accounts</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger">High</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">Investigating</span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                    <i class='bx bx-dots-horizontal-rounded'></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#" onclick="viewLogDetails(5)">
                                                        <i class='bx bx-eye me-2'></i> View Details
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="blockIP('203.45.67.89')">
                                                        <i class='bx bx-block me-2'></i> Block IP
                                                    </a></li>
                                                    <li><a class="dropdown-item text-danger" href="#" onclick="emergencyLockdown()">
                                                        <i class='bx bx-shield-x me-2'></i> Emergency Lockdown
                                                    </a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <small class="text-muted">Showing 1-5 of 1,247 entries</small>
                            </div>
                            <nav>
                                <ul class="pagination pagination-sm mb-0">
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#" tabindex="-1">Previous</a>
                                    </li>
                                    <li class="page-item active">
                                        <a class="page-link" href="#">1</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">2</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">3</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Real-time Activity Feed -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Real-time Activity Feed</h5>
                        <span class="badge bg-success">Live</span>
                    </div>
                    <div class="card-body">
                        <div class="activity-feed" id="activityFeed" style="max-height: 400px; overflow-y: auto;">
                            <!-- Real-time activity items -->
                            <div class="d-flex align-items-start mb-3">
                                <div class="avatar avatar-sm bg-success text-white me-3">
                                    <i class='bx bx-check'></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">Login Success</h6>
                                        <small class="text-muted">Just now</small>
                                    </div>
                                    <p class="mb-1">John Doe logged in successfully</p>
                                    <small class="text-muted">IP: 192.168.1.100</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-start mb-3">
                                <div class="avatar avatar-sm bg-warning text-white me-3">
                                    <i class='bx bx-error'></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">Failed Login Attempt</h6>
                                        <small class="text-muted">2 minutes ago</small>
                                    </div>
                                    <p class="mb-1">Failed login attempt for admin account</p>
                                    <small class="text-muted">IP: 203.45.67.89</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-start mb-3">
                                <div class="avatar avatar-sm bg-info text-white me-3">
                                    <i class='bx bx-key'></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">Password Changed</h6>
                                        <small class="text-muted">5 minutes ago</small>
                                    </div>
                                    <p class="mb-1">Alice Smith changed her password</p>
                                    <small class="text-muted">IP: 192.168.1.105</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Threat Intelligence</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="mb-2">Active Threats</h6>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Brute Force Attack</span>
                                <span class="badge bg-danger">High</span>
                            </div>
                            <div class="progress mb-3" style="height: 8px;">
                                <div class="progress-bar bg-danger" style="width: 75%"></div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Suspicious IP Activity</span>
                                <span class="badge bg-warning">Medium</span>
                            </div>
                            <div class="progress mb-3" style="height: 8px;">
                                <div class="progress-bar bg-warning" style="width: 45%"></div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="mb-2">Blocked IPs</h6>
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-monospace small">203.45.67.89</span>
                                    <span class="badge bg-danger">Blocked</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-monospace small">198.51.100.42</span>
                                    <span class="badge bg-danger">Blocked</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-monospace small">192.0.2.123</span>
                                    <span class="badge bg-warning">Temporary</span>
                                </div>
                            </div>
                        </div>
                        
                        <button class="btn btn-sm btn-outline-danger w-100" onclick="viewThreatDetails()">
                            <i class='bx bx-shield-alt me-1'></i> View Threat Details
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Log Details Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Security Event Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="logDetailsContent"></div>
            </div>
        </div>
    </div>
</div>

<!-- Threat Details Modal -->
<div class="modal fade" id="threatDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Threat Intelligence Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="threatDetailsContent"></div>
            </div>
        </div>
    </div>
</div>

<script>
let autoRefreshInterval = null;
let isAutoRefresh = false;

// Security Logs JavaScript
function applyFilters() {
    const filters = {
        eventType: document.getElementById('eventType').value,
        severity: document.getElementById('severity').value,
        dateRange: document.getElementById('dateRange').value,
        searchTerm: document.getElementById('searchTerm').value
    };
    
    showNotification('Applying filters...', 'info');
    
    // Simulate API call to apply filters
    setTimeout(() => {
        showNotification('Filters applied successfully!', 'success');
        console.log('Applied filters:', filters);
        refreshLogs();
    }, 1000);
}

function refreshLogs() {
    showNotification('Refreshing security logs...', 'info');
    
    // Simulate API call to refresh logs
    setTimeout(() => {
        showNotification('Security logs refreshed!', 'success');
        // In real implementation, this would fetch fresh data from the server
    }, 1500);
}

function toggleAutoRefresh() {
    isAutoRefresh = !isAutoRefresh;
    
    if (isAutoRefresh) {
        autoRefreshInterval = setInterval(() => {
            refreshLogs();
        }, 30000); // Refresh every 30 seconds
        showNotification('Auto-refresh enabled', 'success');
    } else {
        clearInterval(autoRefreshInterval);
        showNotification('Auto-refresh disabled', 'info');
    }
}

function exportLogs() {
    showNotification('Preparing log export...', 'info');
    
    setTimeout(() => {
        // Create a sample CSV export
        const csvContent = "Timestamp,Event Type,User,IP Address,Details,Severity,Status\n" +
            "2026-04-23 07:45:12,Login Success,John Doe,192.168.1.100,Login successful from trusted device,Low,Success\n" +
            "2026-04-23 07:42:35,Login Failed,Unknown User,203.45.67.89,Invalid password - 3rd attempt,Medium,Failed";
        
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'security_logs_' + new Date().toISOString().split('T')[0] + '.csv';
        a.click();
        
        showNotification('Security logs exported successfully!', 'success');
    }, 1500);
}

function clearOldLogs() {
    if (confirm('Are you sure you want to clear logs older than 90 days? This action cannot be undone.')) {
        showNotification('Clearing old logs...', 'warning');
        
        setTimeout(() => {
            showNotification('Old logs cleared successfully!', 'success');
        }, 2000);
    }
}

function viewLogDetails(logId) {
    const logDetails = {
        id: logId,
        timestamp: '2026-04-23 07:45:12',
        eventType: 'Login Success',
        user: {
            name: 'John Doe',
            email: 'john.doe@feedtanpay.co.tz',
            id: 'USR001'
        },
        ipAddress: '192.168.1.100',
        userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        location: 'Dar es Salaam, Tanzania',
        device: 'Windows Desktop',
        details: 'Login successful from trusted device',
        severity: 'Low',
        status: 'Success',
        sessionId: 'SES_' + Math.random().toString(36).substr(2, 9),
        additionalInfo: {
            loginMethod: 'Password',
            twoFactorUsed: false,
            previousLogin: '2026-04-22 09:30:15',
            deviceTrusted: true
        }
    };
    
    const detailsHtml = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="mb-3">Event Information</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Event ID:</strong></td>
                        <td>${logDetails.id}</td>
                    </tr>
                    <tr>
                        <td><strong>Timestamp:</strong></td>
                        <td>${logDetails.timestamp}</td>
                    </tr>
                    <tr>
                        <td><strong>Event Type:</strong></td>
                        <td><span class="badge bg-success">${logDetails.eventType}</span></td>
                    </tr>
                    <tr>
                        <td><strong>Severity:</strong></td>
                        <td><span class="badge bg-success">${logDetails.severity}</span></td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td><span class="badge bg-success">${logDetails.status}</span></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="mb-3">User Information</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Name:</strong></td>
                        <td>${logDetails.user.name}</td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>${logDetails.user.email}</td>
                    </tr>
                    <tr>
                        <td><strong>User ID:</strong></td>
                        <td>${logDetails.user.id}</td>
                    </tr>
                    <tr>
                        <td><strong>Session ID:</strong></td>
                        <td>${logDetails.sessionId}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <h6 class="mb-3">Connection Details</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>IP Address:</strong></td>
                        <td><code>${logDetails.ipAddress}</code></td>
                    </tr>
                    <tr>
                        <td><strong>Location:</strong></td>
                        <td>${logDetails.location}</td>
                    </tr>
                    <tr>
                        <td><strong>Device:</strong></td>
                        <td>${logDetails.device}</td>
                    </tr>
                    <tr>
                        <td><strong>User Agent:</strong></td>
                        <td><small>${logDetails.userAgent}</small></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="mb-3">Additional Information</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Login Method:</strong></td>
                        <td>${logDetails.additionalInfo.loginMethod}</td>
                    </tr>
                    <tr>
                        <td><strong>2FA Used:</strong></td>
                        <td>${logDetails.additionalInfo.twoFactorUsed ? 'Yes' : 'No'}</td>
                    </tr>
                    <tr>
                        <td><strong>Previous Login:</strong></td>
                        <td>${logDetails.additionalInfo.previousLogin}</td>
                    </tr>
                    <tr>
                        <td><strong>Device Trusted:</strong></td>
                        <td>${logDetails.additionalInfo.deviceTrusted ? 'Yes' : 'No'}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="mt-4">
            <h6 class="mb-3">Raw Event Data</h6>
            <pre class="bg-light p-3 rounded"><code>${JSON.stringify(logDetails, null, 2)}</code></pre>
        </div>
    `;
    
    document.getElementById('logDetailsContent').innerHTML = detailsHtml;
    new bootstrap.Modal(document.getElementById('logDetailsModal')).show();
}

function blockIP(ipAddress) {
    if (confirm(`Are you sure you want to block IP address ${ipAddress}?`)) {
        showNotification(`Blocking IP ${ipAddress}...`, 'warning');
        
        setTimeout(() => {
            showNotification(`IP ${ipAddress} blocked successfully!`, 'success');
        }, 1500);
    }
}

function investigateEvent(eventId) {
    showNotification(`Investigating event ${eventId}...`, 'info');
    
    setTimeout(() => {
        showNotification('Investigation completed. No threats detected.', 'success');
    }, 2000);
}

function emergencyLockdown() {
    if (confirm('EMERGENCY: Are you sure you want to initiate system lockdown? This will block all non-admin access immediately.')) {
        showNotification('EMERGENCY LOCKDOWN INITIATED!', 'danger');
        
        // In real implementation, this would trigger immediate security lockdown
        setTimeout(() => {
            showNotification('System lockdown activated. Only admin access allowed.', 'danger');
        }, 1000);
    }
}

function viewThreatDetails() {
    const threatData = {
        activeThreats: [
            {
                type: 'Brute Force Attack',
                severity: 'High',
                sourceIPs: ['203.45.67.89', '198.51.100.42'],
                targetAccounts: ['admin', 'root', 'administrator'],
                attempts: 47,
                timeframe: 'Last 2 hours'
            },
            {
                type: 'Suspicious IP Activity',
                severity: 'Medium',
                sourceIPs: ['192.0.2.123'],
                targetAccounts: ['john.doe', 'alice.smith'],
                attempts: 12,
                timeframe: 'Last 6 hours'
            }
        ],
        blockedIPs: [
            { ip: '203.45.67.89', reason: 'Brute force attack', blockedAt: '2026-04-23 07:30:00', duration: 'Permanent' },
            { ip: '198.51.100.42', reason: 'Multiple failed attempts', blockedAt: '2026-04-23 06:45:00', duration: 'Permanent' },
            { ip: '192.0.2.123', reason: 'Suspicious activity', blockedAt: '2026-04-23 05:20:00', duration: '24 hours' }
        ]
    };
    
    const threatHtml = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="mb-3">Active Threats</h6>
                ${threatData.activeThreats.map(threat => `
                    <div class="card mb-3 border-${threat.severity === 'High' ? 'danger' : 'warning'}">
                        <div class="card-body">
                            <h6 class="card-title">${threat.type}</h6>
                            <p class="card-text">
                                <strong>Severity:</strong> <span class="badge bg-${threat.severity === 'High' ? 'danger' : 'warning'}">${threat.severity}</span><br>
                                <strong>Attempts:</strong> ${threat.attempts}<br>
                                <strong>Timeframe:</strong> ${threat.timeframe}<br>
                                <strong>Source IPs:</strong> ${threat.sourceIPs.join(', ')}<br>
                                <strong>Target Accounts:</strong> ${threat.targetAccounts.join(', ')}
                            </p>
                        </div>
                    </div>
                `).join('')}
            </div>
            <div class="col-md-6">
                <h6 class="mb-3">Blocked IPs</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>IP Address</th>
                                <th>Reason</th>
                                <th>Blocked At</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${threatData.blockedIPs.map(ip => `
                                <tr>
                                    <td><code>${ip.ip}</code></td>
                                    <td>${ip.reason}</td>
                                    <td>${ip.blockedAt}</td>
                                    <td><span class="badge bg-${ip.duration === 'Permanent' ? 'danger' : 'warning'}">${ip.duration}</span></td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <h6 class="mb-3">Recommended Actions</h6>
            <div class="alert alert-warning">
                <h6><i class='bx bx-error-circle me-2'></i>Security Recommendations</h6>
                <ul class="mb-0">
                    <li>Consider implementing rate limiting for login attempts</li>
                    <li>Enable geographic blocking for suspicious regions</li>
                    <li>Review and update password policies</li>
                    <li>Monitor for additional suspicious activity</li>
                </ul>
            </div>
        </div>
    `;
    
    document.getElementById('threatDetailsContent').innerHTML = threatHtml;
    new bootstrap.Modal(document.getElementById('threatDetailsModal')).show();
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}

// Handle date range change
document.getElementById('dateRange').addEventListener('change', function() {
    const customRange = document.getElementById('customDateRange');
    if (this.value === 'custom') {
        customRange.style.display = 'block';
    } else {
        customRange.style.display = 'none';
    }
});

// Simulate real-time activity updates
setInterval(() => {
    if (isAutoRefresh) {
        const feed = document.getElementById('activityFeed');
        const newActivity = document.createElement('div');
        newActivity.className = 'd-flex align-items-start mb-3';
        newActivity.innerHTML = `
            <div class="avatar avatar-sm bg-info text-white me-3">
                <i class='bx bx-refresh'></i>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between">
                    <h6 class="mb-1">System Update</h6>
                    <small class="text-muted">Just now</small>
                </div>
                <p class="mb-1">Security logs updated automatically</p>
                <small class="text-muted">Real-time monitoring active</small>
            </div>
        `;
        
        feed.insertBefore(newActivity, feed.firstChild);
        
        // Keep only the latest 10 items
        while (feed.children.length > 10) {
            feed.removeChild(feed.lastChild);
        }
    }
}, 10000); // Add new activity every 10 seconds
</script>
@endsection
