@extends('layouts.app')

@section('title', 'SMS Messaging - FeedTan Pay')
@section('description', 'Send and manage SMS messages with API V2 integration')

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
                                    <i class="bx bx-mobile-alt me-2 text-primary"></i>
                                    SMS Messaging
                                </h4>
                                <p class="text-muted mb-0">Send and manage SMS messages with API V2 integration</p>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('messaging.dashboard') }}" class="btn btn-outline-secondary">
                                    <i class="bx bx-arrow-back me-2"></i>Dashboard
                                </a>
                                <button type="button" class="btn btn-outline-info" onclick="showSmsBalance()">
                                    <i class="bx bx-wallet me-2"></i>Balance
                                </button>
                                <a href="{{ route('messaging.sms.logs') }}" class="btn btn-outline-primary">
                                    <i class="bx bx-history me-2"></i>Logs
                                </a>
                                <button type="button" class="btn btn-outline-success" onclick="refreshSmsMessages()">
                                    <i class="bx bx-refresh me-2"></i>Refresh
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Send SMS Form -->
        <div class="row mb-6">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-send me-2"></i>
                            Send SMS Message
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="smsForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="service_id" class="form-label">Messaging Service</label>
                                    <select class="form-select" id="service_id" required>
                                        <option value="">Select Service</option>
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}" data-cost="{{ $service->cost_per_message }}" data-currency="{{ $service->currency }}">
                                                {{ $service->name }} @if($service->test_mode)<span class="badge bg-warning ms-2">TEST</span>@endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="sms_to" class="form-label">Recipient Phone Number(s)</label>
                                    <input type="text" class="form-control" id="sms_to" placeholder="255712345678 or 255712345678,255722345678" required>
                                    <small class="text-muted">For multiple recipients, separate with commas</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="template_id" class="form-label">Template (Optional)</label>
                                    <select class="form-select" id="template_id">
                                        <option value="">Select Template</option>
                                        @foreach($templates as $template)
                                            <option value="{{ $template->id }}" data-content="{{ $template->content }}">
                                                {{ $template->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="sender_id" class="form-label">Sender ID (Optional)</label>
                                    <input type="text" class="form-control" id="sender_id" placeholder="FeedTanPay" maxlength="11">
                                    <small class="text-muted">Leave empty to use default sender ID</small>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="sms_message" class="form-label">Message</label>
                                <textarea class="form-control" id="sms_message" rows="4" placeholder="Enter your message..." required maxlength="1600"></textarea>
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-muted">
                                        <span id="char_count">0</span>/1600 characters | 
                                        <span id="sms_count">1</span> SMS(s) | 
                                        Cost: <span id="total_cost">0.0000</span> <span id="currency">TZS</span>
                                    </small>
                                    <small class="text-muted" id="delivery_estimate"></small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="test_mode">
                                        <label class="form-check-label" for="test_mode">
                                            Test Mode (No charges, dummy response)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="schedule_later">
                                        <label class="form-check-label" for="schedule_later">
                                            Schedule for later
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row" id="schedule_row" style="display: none;">
                                <div class="col-md-6 mb-3">
                                    <label for="schedule_time" class="form-label">Schedule Time</label>
                                    <input type="datetime-local" class="form-control" id="schedule_time">
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-send me-2"></i>Send SMS
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="clearForm()">
                                    <i class="bx bx-x me-2"></i>Clear
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- SMS Messages Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">
                                <i class="bx bx-list-ul me-2"></i>
                                SMS Messages
                            </h5>
                            <small class="text-muted">Message history and delivery status</small>
                        </div>
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control form-control-sm" id="searchSms" placeholder="Search messages..." style="width: 200px;">
                            <select class="form-select form-select-sm" id="filterStatus" style="width: 150px;">
                                <option value="">All Status</option>
                                <option value="PENDING">Pending</option>
                                <option value="DELIVERY">Delivered</option>
                                <option value="FAILED">Failed</option>
                                <option value="REJECTED">Rejected</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="smsTable">
                                <thead>
                                    <tr>
                                        <th>
                                            <i class="bx bx-hash me-1"></i>
                                            ID
                                        </th>
                                        <th>
                                            <i class="bx bx-user me-1"></i>
                                            Recipient
                                        </th>
                                        <th>
                                            <i class="bx bx-message me-1"></i>
                                            Message
                                        </th>
                                        <th>
                                            <i class="bx bx-cog me-1"></i>
                                            Service
                                        </th>
                                        <th>
                                            <i class="bx bx-check-circle me-1"></i>
                                            Status
                                        </th>
                                        <th>
                                            <i class="bx bx-time me-1"></i>
                                            Sent
                                        </th>
                                        <th>
                                            <i class="bx bx-dollar me-1"></i>
                                            Cost
                                        </th>
                                        <th>
                                            <i class="bx bx-cog me-1"></i>
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($messages as $message)
                                        <tr data-message-id="{{ $message->id }}">
                                            <td>
                                                <small class="text-muted">#{{ $message->id }}</small>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $message->getFormattedRecipient() }}</strong>
                                                    @if($message->user)
                                                        <br><small class="text-muted">by {{ $message->user->name }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    {{ Str::limit($message->message, 50) }}
                                                    @if(strlen($message->message) > 50)
                                                        <br><small class="text-muted">{{ $message->sms_count }} SMS(s)</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $message->messagingService->name }}</span>
                                                @if($message->is_test)
                                                    <span class="badge bg-warning ms-1">TEST</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $message->getStatusBadgeColor() }}">
                                                    {{ $message->status_name }}
                                                </span>
                                            </td>
                                            <td>
                                                <small>{{ $message->sent_at ? $message->sent_at->format('M j, Y H:i') : '-' }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $message->currency }} {{ number_format($message->price, 4) }}</small>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-horizontal-rounded"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick="viewSmsMessage({{ $message->id }})"><i class="bx bx-eye me-2"></i>View Details</a></li>
                                                        @if($message->isFailed())
                                                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="retrySms({{ $message->id }})"><i class="bx bx-refresh me-2"></i>Retry</a></li>
                                                        @endif
                                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick="exportSms({{ $message->id }})"><i class="bx bx-download me-2"></i>Export</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <small class="text-muted">Showing {{ $messages->firstItem() }} to {{ $messages->lastItem() }} of {{ $messages->total() }} messages</small>
                            </div>
                            <div>
                                {{ $messages->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SMS Details Modal -->
<div class="modal fade" id="smsDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bx bx-mobile-alt me-2"></i>
                    SMS Message Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="smsDetailsContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentCost = 0;
let currentCurrency = 'TZS';

// Service selection change
document.getElementById('service_id').addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    currentCost = parseFloat(option.dataset.cost) || 0;
    currentCurrency = option.dataset.currency || 'TZS';
    updateCostCalculation();
});

// Template selection
document.getElementById('template_id').addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    if (option.value) {
        document.getElementById('sms_message').value = option.dataset.content;
        updateCharCount();
    }
});

// Character counter and cost calculation
document.getElementById('sms_message').addEventListener('input', updateCharCount);
document.getElementById('sms_to').addEventListener('input', updateCostCalculation);

function updateCharCount() {
    const message = document.getElementById('sms_message').value;
    const charCount = message.length;
    const smsCount = Math.ceil(charCount / 160);
    
    document.getElementById('char_count').textContent = charCount;
    document.getElementById('sms_count').textContent = smsCount;
    
    if (charCount > 1600) {
        document.getElementById('char_count').classList.add('text-danger');
    } else {
        document.getElementById('char_count').classList.remove('text-danger');
    }
    
    updateCostCalculation();
}

function updateCostCalculation() {
    const message = document.getElementById('sms_message').value;
    const smsCount = Math.ceil(message.length / 160);
    const recipients = document.getElementById('sms_to').value.split(',').filter(r => r.trim()).length;
    const totalCost = currentCost * smsCount * recipients;
    
    document.getElementById('total_cost').textContent = totalCost.toFixed(4);
    document.getElementById('currency').textContent = currentCurrency;
    
    // Update delivery estimate
    if (recipients > 0) {
        document.getElementById('delivery_estimate').textContent = `Estimated delivery to ${recipients} recipient(s)`;
    } else {
        document.getElementById('delivery_estimate').textContent = '';
    }
}

// Schedule toggle
document.getElementById('schedule_later').addEventListener('change', function() {
    const scheduleRow = document.getElementById('schedule_row');
    scheduleRow.style.display = this.checked ? 'block' : 'none';
    
    if (this.checked) {
        // Set minimum datetime to now
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        document.getElementById('schedule_time').min = now.toISOString().slice(0, 16);
    }
});

// Form submission
document.getElementById('smsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        service_id: document.getElementById('service_id').value,
        to: document.getElementById('sms_to').value,
        message: document.getElementById('sms_message').value,
        template_id: document.getElementById('template_id').value || null,
        sender_id: document.getElementById('sender_id').value || null,
        is_test: document.getElementById('test_mode').checked,
        schedule_time: document.getElementById('schedule_later').checked ? document.getElementById('schedule_time').value : null
    };
    
    sendSms(formData);
});

function sendSms(formData) {
    showNotification('Sending SMS...', 'info');
    
    fetch('{{ route("messaging.sms.send") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('SMS sent successfully!', 'success');
            clearForm();
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification('Failed to send SMS: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Error sending SMS: ' + error.message, 'error');
    });
}

function viewSmsMessage(messageId) {
    console.log('viewSmsMessage called with messageId:', messageId);
    
    fetch(`/api/sms-messages/${messageId}`)
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            
            if (!response.ok) {
                console.error('Response not ok:', response.status, response.statusText);
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            return response.json();
        })
        .then(data => {
            console.log('Received data:', data);
            
            if (!data.success) {
                console.error('API returned success=false:', data.message);
                throw new Error(data.message || 'API request failed');
            }
            
            // Determine data source and show appropriate message
            const dataSource = data.source || 'unknown';
            const sourceMessage = dataSource === 'external_api' ? 
                '<div class="alert alert-success mb-3"><i class="bx bx-check-circle me-2"></i><strong>Live Data:</strong> Details from external SMS API</div>' :
                dataSource === 'local_database' ? 
                '<div class="alert alert-info mb-3"><i class="bx bx-info-circle me-2"></i><strong>Local Data:</strong> Details from local database</div>' :
                '';
            
            const content = `
                ${sourceMessage}
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Message ID</label>
                            <div class="fw-bold text-monospace">${data.message_id}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Recipient</label>
                            <div>${data.getFormattedRecipient()}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sender ID</label>
                            <div>${data.from}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Service</label>
                            <div>${data.messaging_service?.name || 'N/A'}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <div><span class="badge bg-${data.getStatusBadgeColor}">${data.status_name}</span></div>
                        </div>
                        ${data.channel ? `
                        <div class="mb-3">
                            <label class="form-label">Channel</label>
                            <div>${data.channel}</div>
                        </div>
                        ` : ''}
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <div class="bg-light p-2 rounded" style="max-height: 150px; overflow-y: auto;">${data.message}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">SMS Count</label>
                            <div>${data.sms_count || 1} message(s)</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cost</label>
                            <div>${data.currency} ${parseFloat(data.price).toFixed(4)}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Created</label>
                            <div>${new Date(data.created_at).toLocaleString()}</div>
                        </div>
                        ${data.sent_at ? `
                        <div class="mb-3">
                            <label class="form-label">Sent</label>
                            <div>${new Date(data.sent_at).toLocaleString()}</div>
                        </div>
                        ` : ''}
                        ${data.done_at ? `
                        <div class="mb-3">
                            <label class="form-label">Done At</label>
                            <div>${new Date(data.done_at).toLocaleString()}</div>
                        </div>
                        ` : ''}
                        ${data.reference ? `
                        <div class="mb-3">
                            <label class="form-label">Reference</label>
                            <div class="text-monospace">${data.reference}</div>
                        </div>
                        ` : ''}
                        ${data.delivery ? `
                        <div class="mb-3">
                            <label class="form-label">Delivery</label>
                            <div>${data.delivery}</div>
                        </div>
                        ` : ''}
                        ${data.error_message ? `
                        <div class="mb-3">
                            <label class="form-label">Error Message</label>
                            <div class="text-danger">${data.error_message}</div>
                        </div>
                        ` : ''}
                    </div>
                </div>
                ${data.status_group_name ? `
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label">Status Details</label>
                            <div class="bg-light p-3 rounded">
                                <strong>Group:</strong> ${data.status_group_name}<br>
                                <strong>Status:</strong> ${data.status_name}<br>
                                <strong>Description:</strong> ${data.status_name}
                            </div>
                        </div>
                    </div>
                </div>
                ` : ''}
            `;
            
            document.getElementById('smsDetailsContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('smsDetailsModal')).show();
        })
        .catch(error => {
            console.error('Error in viewSmsMessage:', error);
            console.error('Error details:', error.message, error.stack);
            
            // Fallback: Try to get message data from the table
            try {
                const messageRow = document.querySelector(`tr[data-message-id="${messageId}"]`);
                if (messageRow) {
                    console.log('Using fallback data from table');
                    
                    // Extract data from the table row
                    const recipient = messageRow.querySelector('td:nth-child(2)')?.textContent?.trim() || 'N/A';
                    const messageText = messageRow.querySelector('td:nth-child(3)')?.textContent?.trim() || 'N/A';
                    const service = messageRow.querySelector('td:nth-child(4)')?.textContent?.trim() || 'N/A';
                    const status = messageRow.querySelector('td:nth-child(5)')?.textContent?.trim() || 'N/A';
                    const sent = messageRow.querySelector('td:nth-child(6)')?.textContent?.trim() || 'N/A';
                    const cost = messageRow.querySelector('td:nth-child(7)')?.textContent?.trim() || 'N/A';
                    const user = messageRow.querySelector('td:nth-child(3) small')?.textContent?.trim() || 'Admin User';
                    
                    const fallbackContent = `
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Message ID</label>
                                    <div class="fw-bold">#${messageId}</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Recipient</label>
                                    <div>${recipient}</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Sender ID</label>
                                    <div>FEEDTAN</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Service</label>
                                    <div>${service}</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <div class="badge bg-${getStatusBadgeColor(status)}">${status}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Message</label>
                                    <div class="bg-light p-2 rounded" style="max-height: 150px; overflow-y: auto;">${messageText}</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Sent</label>
                                    <div>${sent}</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Cost</label>
                                    <div>${cost}</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">User</label>
                                    <div>${user}</div>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-warning">
                            <i class="bx bx-info-circle me-2"></i>
                            <strong>Limited Details:</strong> Using fallback data due to API connection issues. Some details may be unavailable.
                        </div>
                    `;
                    
                    document.getElementById('smsDetailsContent').innerHTML = fallbackContent;
                    new bootstrap.Modal(document.getElementById('smsDetailsModal')).show();
                    showNotification('Showing limited message details (API unavailable)', 'warning');
                    return;
                }
            } catch (fallbackError) {
                console.error('Fallback also failed:', fallbackError);
            }
            
            // If both API and fallback fail
            showNotification('Error loading message details: ' + error.message, 'error');
        });
}

// Helper function for status badge colors (fallback)
function getStatusBadgeColor(status) {
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

function retrySms(messageId) {
    if (confirm('Are you sure you want to retry this SMS?')) {
        showNotification('Retrying SMS...', 'info');
        
        fetch(`/api/sms-messages/${messageId}/retry`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('SMS retry initiated successfully', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification('Failed to retry SMS: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showNotification('Error retrying SMS', 'error');
        });
    }
}

function exportSms(messageId) {
    window.open(`/api/sms-messages/${messageId}/export`, '_blank');
}

function clearForm() {
    document.getElementById('smsForm').reset();
    document.getElementById('char_count').textContent = '0';
    document.getElementById('sms_count').textContent = '1';
    document.getElementById('total_cost').textContent = '0.0000';
    document.getElementById('schedule_row').style.display = 'none';
}

function refreshSmsMessages() {
    showNotification('Refreshing messages...', 'info');
    setTimeout(() => location.reload(), 1000);
}

// Search functionality
document.getElementById('searchSms').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#smsTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Status filter
document.getElementById('filterStatus').addEventListener('change', function() {
    const status = this.value;
    const rows = document.querySelectorAll('#smsTable tbody tr');
    
    rows.forEach(row => {
        if (!status) {
            row.style.display = '';
        } else {
            const statusBadge = row.querySelector('.badge');
            const rowStatus = statusBadge ? statusBadge.textContent : '';
            row.style.display = rowStatus.includes(status) ? '' : 'none';
        }
    });
});

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

// SMS Balance Modal
function showSmsBalance() {
    fetch('/api/sms-balance')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const balanceData = data.data;
                const content = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">SMS Balance</label>
                                <div class="fw-bold fs-3 text-primary">${balanceData.display || balanceData.sms_balance + ' ' + (balanceData.currency || 'TZS')}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Overdraft</label>
                                <div>${balanceData.over_draft || 0}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Balance Type</label>
                                <div>${balanceData.type || 'N/A'}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Default Balance</label>
                                <div>${balanceData.default_balance || 'N/A'}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Default Channel</label>
                                <div>${balanceData.default || 'N/A'}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Last Updated</label>
                                <div>${new Date().toLocaleString()}</div>
                            </div>
                        </div>
                    </div>
                `;
                
                document.getElementById('smsBalanceContent').innerHTML = content;
                new bootstrap.Modal(document.getElementById('smsBalanceModal')).show();
            } else {
                showNotification('Failed to load SMS balance: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showNotification('Error loading SMS balance: ' + error.message, 'error');
        });
}

// SMS Logs Modal
function showSmsLogs() {
    // Show loading state
    document.getElementById('smsLogsContent').innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    new bootstrap.Modal(document.getElementById('smsLogsModal')).show();
    
    // Fetch logs
    fetch('/api/sms-logs?limit=50')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const logsData = data.data;
                let content = '';
                
                if (logsData.results && logsData.results.length > 0) {
                    content = `
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Message ID</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Status</th>
                                        <th>Sent At</th>
                                        <th>Channel</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    logsData.results.forEach(log => {
                        const statusClass = getStatusBadgeClass(log.status?.groupName || '');
                        content += `
                            <tr>
                                <td><small>${log.messageId || 'N/A'}</small></td>
                                <td>${log.from || 'N/A'}</td>
                                <td>${log.to || 'N/A'}</td>
                                <td><span class="badge bg-${statusClass}">${log.status?.name || 'N/A'}</span></td>
                                <td><small>${log.sentAt || 'N/A'}</small></td>
                                <td><small>${log.channel || 'N/A'}</small></td>
                            </tr>
                        `;
                    });
                    
                    content += `
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">Showing ${logsData.results.length} recent logs</small>
                        </div>
                    `;
                } else {
                    content = '<div class="text-center text-muted">No SMS logs found</div>';
                }
                
                document.getElementById('smsLogsContent').innerHTML = content;
            } else {
                document.getElementById('smsLogsContent').innerHTML = '<div class="text-center text-danger">Failed to load SMS logs: ' + data.message + '</div>';
            }
        })
        .catch(error => {
            document.getElementById('smsLogsContent').innerHTML = '<div class="text-center text-danger">Error loading SMS logs: ' + error.message + '</div>';
        });
}

// Helper function to get status badge color
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
</script>

<!-- SMS Balance Modal -->
<div class="modal fade" id="smsBalanceModal" tabindex="-1" aria-labelledby="smsBalanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="smsBalanceModalLabel">
                    <i class="bx bx-wallet me-2"></i>SMS Balance
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="smsBalanceContent">
                <!-- Balance content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- SMS Logs Modal -->
<div class="modal fade" id="smsLogsModal" tabindex="-1" aria-labelledby="smsLogsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="smsLogsModalLabel">
                    <i class="bx bx-history me-2"></i>SMS Logs
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="smsLogsContent">
                <!-- Logs content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endpush
@endsection
