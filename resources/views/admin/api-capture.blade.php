@extends('layouts.app')

@section('title', 'API Capture - FeedTan Pay')
@section('description', 'FeedTan Pay - Automatic API Transaction Capture System')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bx bx-sync me-2"></i>
                    Automatic API Capture System
                </h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-success btn-sm" onclick="manualCapture()" id="manualCaptureBtn">
                        <i class="bx bx-download me-1"></i>Manual Capture
                    </button>
                    <button class="btn btn-outline-primary btn-sm" onclick="refreshStatus()">
                        <i class="bx bx-refresh me-1"></i>Refresh Status
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Status Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card border-primary">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-lg bg-primary bg-opacity-10 rounded-circle me-3">
                                        <i class="bx bx-list-ul text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Total Transactions</h6>
                                        <h4 class="mb-0 text-primary" id="totalTransactions">-</h4>
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
                                        <i class="bx bx-calendar text-success"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Today's Transactions</h6>
                                        <h4 class="mb-0 text-success" id="todayTransactions">-</h4>
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
                                        <i class="bx bx-check-circle text-info"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Settled Transactions</h6>
                                        <h4 class="mb-0 text-info" id="settledTransactions">-</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-warning">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-lg bg-warning bg-opacity-10 rounded-circle me-3">
                                        <i class="bx bx-time text-warning"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Last Sync</h6>
                                        <h6 class="mb-0 text-warning" id="lastSyncTime">-</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Status -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">System Status</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="me-3">
                                        <h6 class="mb-0">Auto Capture Status</h6>
                                        <span class="badge bg-success" id="autoCaptureStatus">
                                            <i class="bx bx-check-circle me-1"></i>Active
                                        </span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="me-3">
                                        <h6 class="mb-0">Schedule</h6>
                                        <small class="text-muted">Every 2 minutes</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="me-3">
                                        <h6 class="mb-0">API Connection</h6>
                                        <span class="badge bg-success" id="apiStatus">
                                            <i class="bx bx-wifi me-1"></i>Connected
                                        </span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="me-3">
                                        <h6 class="mb-0">Last Manual Capture</h6>
                                        <small class="text-muted" id="lastManualCapture">Never</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Log -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Recent Activity</h6>
                    </div>
                    <div class="card-body">
                        <div id="activityLog">
                            <div class="text-center text-muted">
                                <i class="bx bx-loader-alt bx-spin me-2"></i>
                                Loading recent activity...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055">
    <!-- Toasts will be dynamically added here -->
</div>
@endsection

@push('scripts')
<script>
let lastManualCaptureTime = null;

// Load initial status
document.addEventListener('DOMContentLoaded', function() {
    refreshStatus();
    // Auto-refresh every 30 seconds
    setInterval(refreshStatus, 30000);
});

function refreshStatus() {
    fetch('/api-capture/status')
        .then(response => response.json())
        .then(data => {
            updateStatusDisplay(data);
            loadActivityLog();
        })
        .catch(error => {
            console.error('Error fetching status:', error);
            showErrorToast('Failed to fetch status', 'Status Error');
        });
}

function updateStatusDisplay(data) {
    document.getElementById('totalTransactions').textContent = data.total_transactions || 0;
    document.getElementById('todayTransactions').textContent = data.today_transactions || 0;
    document.getElementById('settledTransactions').textContent = data.settled_transactions || 0;
    document.getElementById('lastSyncTime').textContent = data.last_sync_time ? formatDateTime(data.last_sync_time) : 'Never';
    
    // Update auto capture status
    const autoStatus = document.getElementById('autoCaptureStatus');
    if (data.auto_capture_enabled) {
        autoStatus.className = 'badge bg-success';
        autoStatus.innerHTML = '<i class="bx bx-check-circle me-1"></i>Active';
    } else {
        autoStatus.className = 'badge bg-danger';
        autoStatus.innerHTML = '<i class="bx bx-x-circle me-1"></i>Inactive';
    }
}

function manualCapture() {
    const btn = document.getElementById('manualCaptureBtn');
    const originalContent = btn.innerHTML;
    
    // Show loading state
    btn.disabled = true;
    btn.innerHTML = '<i class="bx bx-sync bx-spin me-1"></i>Capturing...';
    
    fetch('/api-capture/manual')
        .then(response => {
            if (response.ok) {
                showSuccessToast('Manual capture completed successfully', 'Capture Success');
                lastManualCaptureTime = new Date();
                document.getElementById('lastManualCapture').textContent = 'Just now';
                refreshStatus();
            } else {
                throw new Error('Manual capture failed');
            }
        })
        .catch(error => {
            console.error('Manual capture error:', error);
            showErrorToast('Manual capture failed. Please try again.', 'Capture Error');
        })
        .finally(() => {
            // Restore button state
            btn.disabled = false;
            btn.innerHTML = originalContent;
        });
}

function loadActivityLog() {
    // This would typically load from a log file or database
    // For now, showing a placeholder
    const activityLog = document.getElementById('activityLog');
    activityLog.innerHTML = `
        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-point bg-success"></div>
                <div class="timeline-content">
                    <small class="text-muted">${new Date().toLocaleString()}</small>
                    <p class="mb-0">Automatic API capture system is running</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-point bg-info"></div>
                <div class="timeline-content">
                    <small class="text-muted">${new Date().toLocaleString()}</small>
                    <p class="mb-0">System initialized and monitoring for new transactions</p>
                </div>
            </div>
        </div>
    `;
}

function formatDateTime(dateTimeString) {
    const date = new Date(dateTimeString);
    return date.toLocaleString();
}

// Toast notification functions
function showToast(message, type = 'primary', title = 'Notification', duration = 5000) {
    const toastContainer = document.querySelector('.toast-container');
    const toastId = 'toast-' + Date.now();
    
    const iconMap = {
        'primary': 'bx bx-bell',
        'success': 'bx bx-check-circle',
        'danger': 'bx bx-error-circle',
        'warning': 'bx bx-error',
        'info': 'bx bx-info-circle',
        'secondary': 'bx bx-bell'
    };
    
    const toastHtml = `
        <div id="${toastId}" class="bs-toast toast toast-placement-ex m-2 bg-${type}" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="${duration}">
            <div class="toast-header">
                <i class="icon-base ${iconMap[type]} me-2"></i>
                <div class="me-auto fw-medium">${title}</div>
                <small>Just now</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    
    // Remove toast element after it's hidden
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

function showSuccessToast(message, title = 'Success') {
    showToast(message, 'success', title, 4000);
}

function showErrorToast(message, title = 'Error') {
    showToast(message, 'danger', title, 6000);
}
</script>
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-point {
    position: absolute;
    left: -25px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-content {
    background: #f8f9fa;
    padding: 10px 15px;
    border-radius: 5px;
    border-left: 3px solid #007bff;
}
</style>
@endpush
