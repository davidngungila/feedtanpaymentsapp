@extends('layouts.app')

@section('title', 'SMS Logs - FeedTan Pay')
@section('description', 'Advanced SMS logs with filtering and search capabilities')

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
                                    <i class="bx bx-history me-2 text-primary"></i>
                                    SMS Logs
                                </h4>
                                <p class="text-muted mb-0">Advanced SMS logs with filtering and search capabilities</p>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('messaging.sms') }}" class="btn btn-outline-secondary">
                                    <i class="bx bx-arrow-back me-2"></i>Back to SMS
                                </a>
                                <button type="button" class="btn btn-outline-success" onclick="refreshLogs()">
                                    <i class="bx bx-refresh me-2"></i>Refresh
                                </button>
                                <button type="button" class="btn btn-outline-info" onclick="exportLogs()">
                                    <i class="bx bx-download me-2"></i>Export
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="row mb-6">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-filter me-2"></i>
                            Filters
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="logsFilterForm">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="filterFrom" class="form-label">Sender ID</label>
                                    <input type="text" class="form-control" id="filterFrom" placeholder="e.g., FEEDTAN">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="filterTo" class="form-label">Phone Number</label>
                                    <input type="text" class="form-control" id="filterTo" placeholder="e.g., 255716718040">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="filterSentSince" class="form-label">From Date</label>
                                    <input type="date" class="form-control" id="filterSentSince">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="filterSentUntil" class="form-label">To Date</label>
                                    <input type="date" class="form-control" id="filterSentUntil">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="filterReference" class="form-label">Reference</label>
                                    <input type="text" class="form-control" id="filterReference" placeholder="Reference ID">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="filterLimit" class="form-label">Limit</label>
                                    <select class="form-select" id="filterLimit">
                                        <option value="25">25</option>
                                        <option value="50" selected>50</option>
                                        <option value="100">100</option>
                                        <option value="200">200</option>
                                        <option value="500">500</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bx bx-search me-2"></i>Search
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                                            <i class="bx bx-x me-2"></i>Clear
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-list-ul me-2"></i>
                                SMS Logs
                            </h5>
                            <div class="d-flex align-items-center">
                                <span class="text-muted me-3" id="logsCount">Loading...</span>
                                <div class="spinner-border spinner-border-sm text-primary d-none" id="logsLoader" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="logsTable">
                                <thead>
                                    <tr>
                                        <th>Message ID</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Status</th>
                                        <th>Channel</th>
                                        <th>Sent At</th>
                                        <th>Done At</th>
                                        <th>SMS Count</th>
                                        <th>Reference</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="logsTableBody">
                                    <tr>
                                        <td colspan="10" class="text-center">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <nav aria-label="Logs pagination" id="logsPagination" class="d-none">
                            <ul class="pagination justify-content-center">
                                <!-- Pagination will be generated here -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Log Details Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1" aria-labelledby="logDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logDetailsModalLabel">
                    <i class="bx bx-info-circle me-2"></i>SMS Log Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="logDetailsContent">
                <!-- Log details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentPage = 1;
let currentFilters = {};
let currentLogs = []; // Store current logs data

document.addEventListener('DOMContentLoaded', function() {
    loadLogs();
});

// Filter form submission
document.getElementById('logsFilterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    currentPage = 1;
    currentFilters = getFilters();
    loadLogs();
});

// Load logs function
function loadLogs(page = 1) {
    showLoader();
    
    const filters = getFilters();
    const params = new URLSearchParams({
        page: page,
        limit: 50,
        ...filters
    });
    
    fetch('/api/sms-logs?' + params.toString())
        .then(response => response.json())
        .then(data => {
            hideLoader();
            
            if (data.success) {
                displayLogs(data.data.results);
                updateLogsCount(data.data.results.length);
                
                // Show data source indicator
                if (data.data.source === 'local_database') {
                    showNotification('📊 Data from local database (' + data.data.total + ' messages)', 'info');
                }
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            hideLoader();
            showError('Error loading logs: ' + error.message);
        });
}

// Get current filters
function getFilters() {
    const filters = {};
    
    const from = document.getElementById('filterFrom').value.trim();
    if (from) filters.from = from;
    
    const to = document.getElementById('filterTo').value.trim();
    if (to) filters.to = to;
    
    const sentSince = document.getElementById('filterSentSince').value;
    if (sentSince) filters.sentSince = sentSince;
    
    const sentUntil = document.getElementById('filterSentUntil').value;
    if (sentUntil) filters.sentUntil = sentUntil;
    
    const reference = document.getElementById('filterReference').value.trim();
    if (reference) filters.reference = reference;
    
    const limit = document.getElementById('filterLimit').value;
    if (limit) filters.limit = parseInt(limit);
    
    return filters;
}

// Display logs in table
function displayLogs(logs) {
    const tbody = document.getElementById('logsTableBody');
    
    // Store current logs data for details view
    currentLogs = logs;
    
    if (logs.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted">No SMS logs found</td></tr>';
        return;
    }
    
    tbody.innerHTML = logs.map(log => `
        <tr>
            <td><small class="text-monospace">${log.messageId || 'N/A'}</small></td>
            <td>${log.from || 'N/A'}</td>
            <td>${log.to || 'N/A'}</td>
            <td><span class="badge bg-${getStatusBadgeClass(log.status?.groupName || '')}">${log.status?.name || 'N/A'}</span></td>
            <td><small>${log.channel || 'N/A'}</small></td>
            <td><small>${log.sentAt || 'N/A'}</small></td>
            <td><small>${log.doneAt || 'N/A'}</small></td>
            <td>${log.smsCount || 0}</td>
            <td><small class="text-monospace">${log.reference || '-'}</small></td>
            <td>
                <button class="btn btn-sm btn-outline-info" onclick="showLogDetails('${log.messageId}')">
                    <i class="bx bx-eye"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

// Show log details
function showLogDetails(messageId) {
    // Debug: Log the messageId and currentLogs
    console.log('showLogDetails called with messageId:', messageId);
    console.log('currentLogs length:', currentLogs.length);
    console.log('currentLogs:', currentLogs);
    
    // Find the log from current logs data
    const log = currentLogs.find(l => l.messageId === messageId);
    if (!log) {
        console.log('Log not found for messageId:', messageId);
        showNotification('Log details not found', 'error');
        return;
    }
    
    // Debug: Log the found log
    console.log('Found log:', log);
    console.log('log.text:', log.text);
    console.log('log.text exists:', !!log.text);
    console.log('log.text empty:', !log.text);
    
    const content = `
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Message ID</label>
                    <div class="fw-bold text-monospace">${log.messageId || 'N/A'}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">From</label>
                    <div>${log.from || 'N/A'}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">To</label>
                    <div>${log.to || 'N/A'}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <div><span class="badge bg-${getStatusBadgeClass(log.status?.groupName || '')}">${log.status?.name || 'N/A'}</span></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Channel</label>
                    <div>${log.channel || 'N/A'}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Sent At</label>
                    <div>${log.sentAt || 'N/A'}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Done At</label>
                    <div>${log.doneAt || 'N/A'}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">SMS Count</label>
                    <div>${log.smsCount || 0}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Reference</label>
                    <div class="text-monospace">${log.reference || '-'}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Delivery</label>
                    <div>${log.delivery || 'N/A'}</div>
                </div>
            </div>
        </div>
        ${log.text ? `
        <div class="row">
            <div class="col-12">
                <div class="mb-3">
                    <label class="form-label">Message Content</label>
                    <div class="bg-light p-3 rounded border">
                        <pre class="mb-0" style="white-space: pre-wrap; word-wrap: break-word; font-family: inherit;">${log.text}</pre>
                    </div>
                </div>
            </div>
        </div>
        ` : ''}
        ${log.status ? `
        <div class="row">
            <div class="col-12">
                <div class="mb-3">
                    <label class="form-label">Status Details</label>
                    <div class="bg-light p-3 rounded">
                        <strong>Group:</strong> ${log.status.groupName || 'N/A'} (ID: ${log.status.groupId || 'N/A'})<br>
                        <strong>Status:</strong> ${log.status.name || 'N/A'} (ID: ${log.status.id || 'N/A'})<br>
                        <strong>Description:</strong> ${log.status.description || 'N/A'}
                    </div>
                </div>
            </div>
        </div>
        ` : ''}
    `;
    
    // Debug: Log the final content
    console.log('Generated content length:', content.length);
    console.log('Content includes message content:', content.includes('Message Content'));
    console.log('Final content:', content);
    
    document.getElementById('logDetailsContent').innerHTML = content;
    new bootstrap.Modal(document.getElementById('logDetailsModal')).show();
}

// Get status badge class
function getStatusBadgeClass(status) {
    switch (status?.toLowerCase()) {
        case 'delivered':
            return 'success';
        case 'sent':
        case 'enroute':
            return 'info';
        case 'failed':
        case 'rejected':
            return 'danger';
        case 'pending':
        case 'accepted':
            return 'warning';
        default:
            return 'secondary';
    }
}

// Clear filters
function clearFilters() {
    document.getElementById('logsFilterForm').reset();
    currentFilters = {};
    currentPage = 1;
    loadLogs();
}

// Refresh logs
function refreshLogs() {
    loadLogs();
}

// Export logs
function exportLogs() {
    const params = new URLSearchParams(currentFilters);
    window.open('/api/sms-logs/export?' + params.toString(), '_blank');
}

// Show/hide loader
function showLoader(show) {
    const loader = document.getElementById('logsLoader');
    const tbody = document.getElementById('logsTableBody');
    
    if (show) {
        loader.classList.remove('d-none');
        tbody.innerHTML = '<tr><td colspan="10" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';
    } else {
        loader.classList.add('d-none');
    }
}

function hideLoader() {
    showLoader(false);
}

// Update logs count
function updateLogsCount(count) {
    document.getElementById('logsCount').textContent = `${count} logs found`;
}

// Show error
function showError(message) {
    const tbody = document.getElementById('logsTableBody');
    tbody.innerHTML = `<tr><td colspan="10" class="text-center text-danger">${message}</td></tr>`;
    document.getElementById('logsCount').textContent = 'Error';
}

// Show notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}
</script>
@endpush
