@extends('layouts.app')

@section('title', 'Email Messaging - FeedTan Pay')
@section('description', 'Send and manage Email messages with API V2 integration')

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
                                    <i class="bx bx-envelope me-2 text-primary"></i>
                                    Email Messaging
                                </h4>
                                <p class="text-muted mb-0">Send and manage Email messages with API V2 integration</p>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('messaging.dashboard') }}" class="btn btn-outline-secondary">
                                    <i class="bx bx-arrow-back me-2"></i>Dashboard
                                </a>
                                <button type="button" class="btn btn-outline-success" onclick="refreshEmailMessages()">
                                    <i class="bx bx-refresh me-2"></i>Refresh
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Send Email Form -->
        <div class="row mb-6">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-send me-2"></i>
                            Send Email Message
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="emailForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email_service_id" class="form-label">Messaging Service</label>
                                    <select class="form-select" id="email_service_id" required>
                                        <option value="">Select Service</option>
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}" @if($service->test_mode)data-test="true"@endif>
                                                {{ $service->name }} @if($service->test_mode)<span class="badge bg-warning ms-2">TEST</span>@endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email_from_name" class="form-label">From Name</label>
                                    <input type="text" class="form-control" id="email_from_name" value="FeedTan Pay" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email_to" class="form-label">Recipient Email(s)</label>
                                    <input type="text" class="form-control" id="email_to" placeholder="user@example.com or user1@example.com,user2@example.com" required>
                                    <small class="text-muted">For multiple recipients, separate with commas</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email_to_name" class="form-label">Recipient Name(s)</label>
                                    <input type="text" class="form-control" id="email_to_name" placeholder="John Doe or John Doe,Jane Smith">
                                    <small class="text-muted">Optional, for multiple recipients separate with commas</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email_template_id" class="form-label">Email Template</label>
                                    <select class="form-select" id="email_template_id">
                                        <option value="">Select Template</option>
                                        @foreach($templates as $template)
                                            <option value="{{ $template->id }}" data-category="{{ $template->category }}" data-subject="{{ $template->subject }}">
                                                {{ $template->name }} ({{ $template->category }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="previewEmailTemplate()">
                                        <i class="bx bx-eye me-1"></i>Preview Template
                                    </button>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email_cc" class="form-label">CC (Optional)</label>
                                    <input type="text" class="form-control" id="email_cc" placeholder="cc@example.com">
                                    <small class="text-muted">Multiple addresses separated by commas</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email_bcc" class="form-label">BCC (Optional)</label>
                                    <input type="text" class="form-control" id="email_bcc" placeholder="bcc@example.com">
                                    <small class="text-muted">Multiple addresses separated by commas</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email_reply_to" class="form-label">Reply To (Optional)</label>
                                    <input type="text" class="form-control" id="email_reply_to" placeholder="reply@example.com">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="email_subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="email_subject" placeholder="Email subject" required>
                            </div>
                            <div class="mb-3">
                                <label for="email_message" class="form-label">Message (HTML)</label>
                                <textarea class="form-control" id="email_message" rows="8" placeholder="Enter your message (HTML supported)..." required></textarea>
                                <small class="text-muted">
                                    <span id="char_count_email">0</span> characters | 
                                    <button type="button" class="btn btn-sm btn-outline-primary ms-2" onclick="toggleEmailEditor()">
                                        <i class="bx bx-code me-1"></i>Toggle Editor
                                    </button>
                                </small>
                            </div>
                            <div class="mb-3" id="email_attachments" style="display: none;">
                                <label class="form-label">Attachments</label>
                                <div class="border rounded p-3 bg-light">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bx bx-paperclip me-2"></i>
                                        <span>Attachment functionality will be implemented in backend</span>
                                    </div>
                                    <small class="text-muted">Maximum 10MB per attachment, 5 attachments total</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="email_test_mode">
                                        <label class="form-check-label" for="email_test_mode">
                                            Test Mode (No charges)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="email_schedule_later">
                                        <label class="form-check-label" for="email_schedule_later">
                                            Schedule for later
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="email_track_opens">
                                        <label class="form-check-label" for="email_track_opens">
                                            Track opens and clicks
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row" id="email_schedule_row" style="display: none;">
                                <div class="col-md-6 mb-3">
                                    <label for="email_schedule_time" class="form-label">Schedule Time</label>
                                    <input type="datetime-local" class="form-control" id="email_schedule_time">
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="bx bx-send me-2"></i>Send Email
                                </button>
                                <button type="button" class="btn btn-outline-primary" onclick="previewEmail()">
                                    <i class="bx bx-eye me-2"></i>Preview
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="clearEmailForm()">
                                    <i class="bx bx-x me-2"></i>Clear
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Messages Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">
                                <i class="bx bx-list-ul me-2"></i>
                                Email Messages
                            </h5>
                            <small class="text-muted">Message history and delivery status</small>
                        </div>
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control form-control-sm" id="searchEmail" placeholder="Search messages..." style="width: 200px;">
                            <select class="form-select form-select-sm" id="filterEmailStatus" style="width: 150px;">
                                <option value="">All Status</option>
                                <option value="PENDING">Pending</option>
                                <option value="DELIVERY">Delivered</option>
                                <option value="FAILED">Failed</option>
                                <option value="REJECTED">Rejected</option>
                                <option value="BOUNCED">Bounced</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="emailTable">
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
                                            <i class="bx bx-text me-1"></i>
                                            Subject
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
                                            <i class="bx bx-envelope-open me-1"></i>
                                            Opened
                                        </th>
                                        <th>
                                            <i class="bx bx-time me-1"></i>
                                            Sent
                                        </th>
                                        <th>
                                            <i class="bx bx-cog me-1"></i>
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($messages as $message)
                                        <tr>
                                            <td>
                                                <small class="text-muted">#{{ $message->id }}</small>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $message->getFullRecipient() }}</strong>
                                                    @if($message->user)
                                                        <br><small class="text-muted">by {{ $message->user->name }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    {{ Str::limit($message->subject, 40) }}
                                                    @if($message->hasAttachments())
                                                        <br><small class="text-muted"><i class="bx bx-paperclip"></i> {{ $message->getAttachmentCount() }} attachment(s)</small>
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
                                                @if($message->isOpened())
                                                    <span class="badge bg-success">Yes</span>
                                                    <br><small class="text-muted">{{ $message->opened_at->diffForHumans() }}</small>
                                                @else
                                                    <span class="badge bg-secondary">No</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small>{{ $message->sent_at ? $message->sent_at->format('M j, Y H:i') : '-' }}</small>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-horizontal-rounded"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick="viewEmailMessage({{ $message->id }})"><i class="bx bx-eye me-2"></i>View Details</a></li>
                                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick="viewEmailContent({{ $message->id }})"><i class="bx bx-file me-2"></i>View Content</a></li>
                                                        @if($message->isFailed())
                                                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="retryEmail({{ $message->id }})"><i class="bx bx-refresh me-2"></i>Retry</a></li>
                                                        @endif
                                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick="exportEmail({{ $message->id }})"><i class="bx bx-download me-2"></i>Export</a></li>
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

<!-- Email Details Modal -->
<div class="modal fade" id="emailDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bx bx-envelope me-2"></i>
                    Email Message Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="emailDetailsContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Email Content Modal -->
<div class="modal fade" id="emailContentModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bx bx-file me-2"></i>
                    Email Content
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="emailContentBody"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Email Preview Modal -->
<div class="modal fade" id="emailPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bx bx-eye me-2"></i>
                    Email Preview
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <iframe id="emailPreviewFrame" style="width: 100%; height: 500px; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Template selection
document.getElementById('email_template_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
        // Load template details
        fetch(`/api/email-template/${selectedOption.value}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('email_subject').value = data.data.subject || '';
                    document.getElementById('email_message').value = data.data.html_content || '';
                    updateCharCount();
                    showNotification('Template loaded successfully', 'success');
                }
            })
            .catch(error => {
                showNotification('Error loading template', 'error');
            });
    } else {
        document.getElementById('email_subject').value = '';
        document.getElementById('email_message').value = '';
        updateCharCount();
    }
});

// Preview email template - make globally accessible
window.previewEmailTemplate = function() {
    const templateId = document.getElementById('email_template_id').value;
    
    if (!templateId) {
        showNotification('Please select a template first', 'warning');
        return;
    }

    // Get current form values for variables
    const variables = {
        memberName: document.getElementById('email_to_name').value || 'John Doe',
        currentDate: new Date().toISOString().split('T')[0],
        companyName: 'FeedTan Community Microfinance Group',
        loanAmount: '500,000',
        loanNumber: 'LN2026001',
        approvalDate: new Date().toISOString().split('T')[0],
        interestRate: '15',
        repaymentPeriod: '12',
        monthlyPayment: '45,000',
        firstPaymentDate: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        lastPaymentDate: new Date(Date.now() + 365 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        acceptLink: '#',
        detailsLink: '#',
        dueDate: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        daysRemaining: '7',
        paymentAmount: '45,000',
        paymentType: 'Monthly',
        outstandingBalance: '450,000',
        bankAccountNumber: '0123456789012',
        mobileNumber: '+255712345678',
        lateFee: '5,000',
        lateFeeDate: new Date(Date.now() + 14 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        paymentLink: '#',
        supportNumber: '+255712345678',
        statementDate: new Date().toISOString().split('T')[0],
        currentBalance: '100,000',
        monthlyChange: '10,000',
        totalSavings: '100,000',
        accountNumber: 'SA001',
        accountType: 'Savings Account',
        monthlySavings: '10,000',
        interestEarned: '500',
        recentTransactions: '<div class="transaction-item"><span>Deposit</span><span>TZS 10,000</span></div>',
        portalLink: '#',
        meetingType: 'General Meeting',
        meetingDate: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        meetingTime: '10:00 AM',
        duration: '2 hours',
        organizer: 'Board of Directors',
        expectedAttendees: '50',
        buildingName: 'FeedTan CMG Office',
        roomNumber: 'Conference Room A',
        address: 'P.O.Box 7744, Ushirika Sokoine Road, Moshi, Kilimanjaro, Tanzania',
        additionalDirections: 'Near Moshi Municipality offices',
        agendaItems: '<li>Opening remarks</li><li>Financial report</li><li>New business</li><li>Closing remarks</li>',
        rsvpLink: '#',
        calendarLink: '#',
        rsvpDeadline: new Date(Date.now() + 3 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]
    };

    // Show loading
    showNotification('Loading template preview...', 'info');

    // Fetch template preview
    fetch('/api/email-template/preview', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            template_id: templateId,
            variables: variables
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showEmailPreview(data.data.html);
            showNotification('Template preview loaded', 'success');
        } else {
            showNotification('Error loading template preview', 'error');
        }
    })
    .catch(error => {
        showNotification('Error loading template preview', 'error');
    });
}

// Show email preview in iframe
function showEmailPreview(htmlContent) {
    const previewFrame = document.getElementById('emailPreviewFrame');
    previewFrame.srcdoc = htmlContent;
    new bootstrap.Modal(document.getElementById('emailPreviewModal')).show();
}

// Character counter
document.getElementById('email_message').addEventListener('input', updateCharCountEmail);

function updateCharCountEmail() {
    const message = document.getElementById('email_message').value;
    const charCount = message.length;
    
    document.getElementById('char_count_email').textContent = charCount;
}

// Schedule toggle
document.getElementById('email_schedule_later').addEventListener('change', function() {
    const scheduleRow = document.getElementById('email_schedule_row');
    scheduleRow.style.display = this.checked ? 'block' : 'none';
    
    if (this.checked) {
        // Set minimum datetime to now
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        document.getElementById('email_schedule_time').min = now.toISOString().slice(0, 16);
    }
});

// Form submission
document.getElementById('emailForm').addEventListener('submit', function(e) {
    e.preventDefault();
    sendEmail();
});

window.sendEmail = function() {
    const formData = new FormData(document.getElementById('emailForm'));
    
    // Prepare variables for template processing
    const variables = {
        memberName: formData.get('to_name') || 'Valued Member',
        currentDate: new Date().toISOString().split('T')[0],
        companyName: 'FeedTan Community Microfinance Group'
    };
    
    const data = {
        service_id: formData.get('service_id'),
        to: formData.get('to'),
        subject: formData.get('subject'),
        message: formData.get('message'),
        cc: formData.get('cc'),
        bcc: formData.get('bcc'),
        reply_to: formData.get('reply_to'),
        template_id: formData.get('template_id'),
        variables: variables,
        is_test: document.getElementById('email_test_mode').checked
    };

    // Show loading with progress tracking
    const submitBtn = document.querySelector('#emailForm button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    
    // Create progress indicator
    const progressDiv = document.createElement('div');
    progressDiv.className = 'progress-container mt-3';
    progressDiv.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="progress-text">Preparing email...</span>
            <span class="progress-percent">0%</span>
        </div>
        <div class="progress" style="height: 6px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
        </div>
    `;
    
    // Insert progress indicator after the button
    submitBtn.parentNode.insertBefore(progressDiv, submitBtn.nextSibling);
    
    const progressBar = progressDiv.querySelector('.progress-bar');
    const progressText = progressDiv.querySelector('.progress-text');
    const progressPercent = progressDiv.querySelector('.progress-percent');
    
    // Progress tracking function
    function updateProgress(percent, text) {
        progressBar.style.width = percent + '%';
        progressPercent.textContent = percent + '%';
        progressText.textContent = text;
        submitBtn.innerHTML = `<i class="bx bx-loader-alt bx-spin me-2"></i>${text}`;
    }
    
    // Start progress tracking
    let progress = 0;
    const progressSteps = [
        { percent: 10, text: 'Validating data...' },
        { percent: 25, text: 'Connecting to email service...' },
        { percent: 40, text: 'Preparing email content...' },
        { percent: 60, text: 'Sending email...' },
        { percent: 80, text: 'Confirming delivery...' },
        { percent: 95, text: 'Finalizing...' },
        { percent: 100, text: 'Complete!' }
    ];
    
    // Simulate progress updates
    let currentStep = 0;
    const progressInterval = setInterval(() => {
        if (currentStep < progressSteps.length) {
            const step = progressSteps[currentStep];
            updateProgress(step.percent, step.text);
            currentStep++;
        } else {
            clearInterval(progressInterval);
        }
    }, 800);
    
    // Set timeout for long-running operations
    const timeout = setTimeout(() => {
        updateProgress(50, 'Still sending... Please wait...');
        showNotification('Email sending is taking longer than expected. Please wait...', 'warning');
    }, 10000);

    fetch('/messaging/email/send', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        clearTimeout(timeout);
        clearInterval(progressInterval);
        updateProgress(90, 'Processing response...');
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        updateProgress(100, 'Complete!');
        
        if (data.success) {
            showNotification('Email sent successfully!', 'success');
            document.getElementById('emailForm').reset();
            updateCharCountEmail();
            refreshEmailMessages();
            
            // Show success splash
            setTimeout(() => {
                progressDiv.innerHTML = `
                    <div class="alert alert-success">
                        <i class="bx bx-check-circle me-2"></i>
                        <strong>Email sent successfully!</strong><br>
                        <small>Your email has been delivered to the recipient.</small>
                    </div>
                `;
                
                // Remove success message after 3 seconds
                setTimeout(() => {
                    if (progressDiv.parentNode) {
                        progressDiv.parentNode.removeChild(progressDiv);
                    }
                }, 3000);
            }, 500);
            
        } else {
            showNotification('Failed to send email: ' + data.message, 'error');
            
            // Show error message
            progressDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bx bx-error-circle me-2"></i>
                    <strong>Email sending failed</strong><br>
                    <small>${data.message}</small>
                </div>
            `;
            
            // Remove error message after 5 seconds
            setTimeout(() => {
                if (progressDiv.parentNode) {
                    progressDiv.parentNode.removeChild(progressDiv);
                }
            }, 5000);
        }
    })
    .catch(error => {
        clearTimeout(timeout);
        clearInterval(progressInterval);
        updateProgress(0, 'Failed');
        
        showNotification('Error sending email: ' + error.message, 'error');
        
        // Show error message
        progressDiv.innerHTML = `
            <div class="alert alert-danger">
                <i class="bx bx-error-circle me-2"></i>
                <strong>Connection error</strong><br>
                <small>${error.message}</small>
            </div>
        `;
        
        // Remove error message after 5 seconds
        setTimeout(() => {
            if (progressDiv.parentNode) {
                progressDiv.parentNode.removeChild(progressDiv);
            }
        }, 5000);
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        
        // Clean up progress indicator if still present after 10 seconds
        setTimeout(() => {
            if (progressDiv.parentNode) {
                progressDiv.parentNode.removeChild(progressDiv);
            }
        }, 10000);
    });
}

window.viewEmailMessage = function(messageId) {
    fetch(`/api/email-messages/${messageId}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                showNotification('Error loading message details', 'error');
                return;
            }
            
            const content = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Message ID</label>
                            <div class="fw-bold">${data.message_id}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">From Email</label>
                            <div>${data.from_email}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">From Name</label>
                            <div>${data.from_name || 'N/A'}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">To Email</label>
                            <div>${data.to_email}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">To Name</label>
                            <div>${data.to_name || 'N/A'}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <div><strong>${data.subject}</strong></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Service</label>
                            <div>${data.service?.name || 'N/A'}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <div><span class="badge bg-success">${data.status_name}</span></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Status Description</label>
                            <div>${data.status_description || 'N/A'}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sent At</label>
                            <div>${data.sent_at ? new Date(data.sent_at).toLocaleString() : 'Not sent'}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Created At</label>
                            <div>${new Date(data.created_at).toLocaleString()}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">User</label>
                            <div>${data.user ? data.user.name : 'System'}</div>
                        </div>
                        ${data.failed_at ? `
                        <div class="mb-3">
                            <label class="form-label">Failed At</label>
                            <div class="text-danger">${new Date(data.failed_at).toLocaleString()}</div>
                        </div>
                        ` : ''}
                    </div>
                </div>
                ${data.status_description && data.status_name === 'failed' ? `
                <div class="mb-3">
                    <label class="form-label">Error Message</label>
                    <div class="text-danger">${data.status_description}</div>
                </div>
                ` : ''}
                <div class="mb-3">
                    <button type="button" class="btn btn-outline-primary" onclick="viewEmailContent(${messageId})">
                        <i class="bx bx-file me-2"></i>View Full Content
                    </button>
                </div>
            `;
            
            document.getElementById('emailDetailsContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('emailDetailsModal')).show();
        })
        .catch(error => {
            showNotification('Error loading message details', 'error');
        });
}

window.viewEmailContent = function(messageId) {
    fetch(`/api/email-messages/${messageId}/content`)
        .then(response => response.json())
        .then(data => {
            const content = `
                <div class="mb-3">
                    <h6>Subject: ${data.subject}</h6>
                    <hr>
                    <div style="border: 1px solid #ddd; padding: 20px; background: white;">
                        ${data.body_html}
                    </div>
                </div>
            `;
            
            document.getElementById('emailContentBody').innerHTML = content;
            new bootstrap.Modal(document.getElementById('emailContentModal')).show();
        })
        .catch(error => {
            showNotification('Error loading email content', 'error');
        });
}

function retryEmail(messageId) {
    if (confirm('Are you sure you want to retry this Email?')) {
        showNotification('Retrying Email...', 'info');
        
        fetch(`/api/email-messages/${messageId}/retry`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Email retry initiated successfully', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification('Failed to retry Email: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showNotification('Error retrying Email', 'error');
        });
    }
}

window.exportEmail = function(messageId) {
    window.open(`/api/email-messages/${messageId}/export`, '_blank');
}

function previewEmail() {
    const subject = document.getElementById('email_subject').value;
    const content = document.getElementById('email_message').value;
    
    if (!subject || !content) {
        showNotification('Please fill in subject and message fields', 'warning');
        return;
    }
    
    const previewHtml = `
        <html>
        <head>
            <title>${subject}</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
            </style>
        </head>
        <body>
            ${content}
        </body>
        </html>
    `;
    
    document.getElementById('emailPreviewFrame').srcdoc = previewHtml;
    new bootstrap.Modal(document.getElementById('emailPreviewModal')).show();
}

function toggleEmailEditor() {
    // This would integrate with a rich text editor like TinyMCE or CKEditor
    showNotification('Rich text editor integration coming soon', 'info');
}

function clearEmailForm() {
    document.getElementById('emailForm').reset();
    document.getElementById('char_count_email').textContent = '0';
    document.getElementById('email_schedule_row').style.display = 'none';
}

function refreshEmailMessages() {
    showNotification('Refreshing messages...', 'info');
    setTimeout(() => location.reload(), 1000);
}

// Search functionality
document.getElementById('searchEmail').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#emailTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Status filter
document.getElementById('filterEmailStatus').addEventListener('change', function() {
    const status = this.value;
    const rows = document.querySelectorAll('#emailTable tbody tr');
    
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
</script>
@endpush
@endsection
