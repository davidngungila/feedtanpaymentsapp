@extends('layouts.app')

@section('title', 'Messaging Services - FeedTan Pay')
@section('description', 'Configure and manage messaging service providers')

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
                                    <i class="bx bx-cog me-2 text-primary"></i>
                                    Messaging Services
                                </h4>
                                <p class="text-muted mb-0">Configure and manage SMS and Email service providers with API V2 integration</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary" onclick="addService()">
                                    <i class="bx bx-plus me-2"></i>Add Service
                                </button>
                                <button type="button" class="btn btn-outline-success" onclick="refreshServices()">
                                    <i class="bx bx-refresh me-2"></i>Refresh
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Services Overview -->
        <div class="row mb-6">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-primary bg-opacity-10 rounded-circle me-3" style="width: 48px; height: 48px;">
                                <i class="bx bx-mobile-alt text-primary fs-4"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">SMS Services</h6>
                                <h4 class="mb-0">{{ $services->where('type', 'SMS')->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-success bg-opacity-10 rounded-circle me-3" style="width: 48px; height: 48px;">
                                <i class="bx bx-envelope text-success fs-4"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Email Services</h6>
                                <h4 class="mb-0">{{ $services->where('type', 'EMAIL')->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-success bg-opacity-10 rounded-circle me-3" style="width: 48px; height: 48px;">
                                <i class="bx bx-check-circle text-success fs-4"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Active Services</h6>
                                <h4 class="mb-0">{{ $services->where('is_active', true)->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-warning bg-opacity-10 rounded-circle me-3" style="width: 48px; height: 48px;">
                                <i class="bx bx-test-tube text-warning fs-4"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Test Mode</h6>
                                <h4 class="mb-0">{{ $services->where('test_mode', true)->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Services Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">
                                <i class="bx bx-list-ul me-2"></i>
                                Service Configurations
                            </h5>
                            <small class="text-muted">Manage your messaging service providers</small>
                        </div>
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control form-control-sm" id="searchServices" placeholder="Search services..." style="width: 200px;">
                            <select class="form-select form-select-sm" id="filterType" style="width: 120px;">
                                <option value="">All Types</option>
                                <option value="SMS">SMS</option>
                                <option value="EMAIL">Email</option>
                                <option value="WHATSAPP">WhatsApp</option>
                                <option value="MOBILE">Mobile</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="servicesTable">
                                <thead>
                                    <tr>
                                        <th>
                                            <i class="bx bx-tag me-1"></i>
                                            Name
                                        </th>
                                        <th>
                                            <i class="bx bx-category me-1"></i>
                                            Type
                                        </th>
                                        <th>
                                            <i class="bx bx-globe me-1"></i>
                                            Provider
                                        </th>
                                        <th>
                                            <i class="bx bx-link me-1"></i>
                                            Base URL
                                        </th>
                                        <th>
                                            <i class="bx bx-check-circle me-1"></i>
                                            Status
                                        </th>
                                        <th>
                                            <i class="bx bx-message me-1"></i>
                                            Messages
                                        </th>
                                        <th>
                                            <i class="bx bx-dollar me-1"></i>
                                            Cost/Msg
                                        </th>
                                        <th>
                                            <i class="bx bx-cog me-1"></i>
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($services as $service)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $service->name }}</strong>
                                                    @if($service->sender_id)
                                                        <br><small class="text-muted">Sender: {{ $service->sender_id }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $service->type === 'SMS' ? 'primary' : ($service->type === 'EMAIL' ? 'success' : 'info') }}">
                                                    {{ $service->type }}
                                                </span>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $service->provider }}</strong>
                                                    <br><small class="text-muted">v{{ $service->api_version }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ Str::limit($service->base_url, 30) }}</small>
                                            </td>
                                            <td>
                                                <div>
                                                    @if($service->is_active)
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-secondary">Inactive</span>
                                                    @endif
                                                    @if($service->test_mode)
                                                        <span class="badge bg-warning ms-1">TEST</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <small class="text-muted">
                                                        SMS: {{ $service->sms_messages_count ?? 0 }}
                                                        @if($service->type === 'EMAIL')
                                                        <br>Email: {{ $service->email_messages_count ?? 0 }}
                                                        @endif
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <small>{{ $service->currency }} {{ number_format($service->cost_per_message, 4) }}</small>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-horizontal-rounded"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick="viewService({{ $service->id }})"><i class="bx bx-eye me-2"></i>View Details</a></li>
                                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick="editService({{ $service->id }})"><i class="bx bx-edit me-2"></i>Edit</a></li>
                                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick="testService({{ $service->id }})"><i class="bx bx-test-tube me-2"></i>Test Connection</a></li>
                                                        @if($service->is_active)
                                                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="toggleServiceStatus({{ $service->id }}, false)"><i class="bx bx-pause me-2"></i>Deactivate</a></li>
                                                        @else
                                                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="toggleServiceStatus({{ $service->id }}, true)"><i class="bx bx-play me-2"></i>Activate</a></li>
                                                        @endif
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="deleteService({{ $service->id }})"><i class="bx bx-trash me-2"></i>Delete</a></li>
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
                                <small class="text-muted">Showing {{ $services->firstItem() }} to {{ $services->lastItem() }} of {{ $services->total() }} services</small>
                            </div>
                            <div>
                                {{ $services->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Service Modal -->
<div class="modal fade" id="serviceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="serviceModalTitle">
                    <i class="bx bx-cog me-2"></i>
                    Add Messaging Service
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="serviceForm">
                    <input type="hidden" id="service_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="service_name" class="form-label">Service Name *</label>
                            <input type="text" class="form-control" id="service_name" required placeholder="e.g., Main SMS Service">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="service_type" class="form-label">Service Type *</label>
                            <select class="form-select" id="service_type" required>
                                <option value="">Select Type</option>
                                <option value="SMS">SMS</option>
                                <option value="EMAIL">Email</option>
                                <option value="WHATSAPP">WhatsApp</option>
                                <option value="MOBILE">Mobile</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="service_provider" class="form-label">Provider *</label>
                            <input type="text" class="form-control" id="service_provider" required placeholder="e.g., messaging-service.co.tz">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="service_base_url" class="form-label">Base URL *</label>
                            <input type="url" class="form-control" id="service_base_url" required placeholder="https://messaging-service.co.tz">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="service_api_version" class="form-label">API Version</label>
                            <select class="form-select" id="service_api_version">
                                <option value="v2">v2</option>
                                <option value="v1">v1</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="service_sender_id" class="form-label">Sender ID</label>
                            <input type="text" class="form-control" id="service_sender_id" placeholder="FeedTanPay" maxlength="11">
                            <small class="text-muted">For SMS services only</small>
                        </div>
                    </div>
                    
                    <!-- Authentication Section -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Authentication</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Authentication Method</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="auth_method" id="auth_bearer" value="bearer" checked>
                                    <label class="form-check-label" for="auth_bearer">
                                        Bearer Token (Recommended)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="auth_method" id="auth_basic" value="basic">
                                    <label class="form-check-label" for="auth_basic">
                                        Basic Authentication (Username/Password)
                                    </label>
                                </div>
                            </div>
                            
                            <div id="bearer_auth_fields">
                                <div class="mb-3">
                                    <label for="service_bearer_token" class="form-label">Bearer Token *</label>
                                    <textarea class="form-control" id="service_bearer_token" rows="3" placeholder="d983d9d1d54176047e68547aba079ba4"></textarea>
                                    <small class="text-muted">Get this from your provider dashboard</small>
                                </div>
                            </div>
                            
                            <div id="basic_auth_fields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="service_username" class="form-label">Username *</label>
                                        <input type="text" class="form-control" id="service_username" placeholder="username">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="service_password" class="form-label">Password *</label>
                                        <input type="password" class="form-control" id="service_password" placeholder="password">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="service_rate_limit" class="form-label">Rate Limit (per hour)</label>
                            <input type="number" class="form-control" id="service_rate_limit" value="100" min="1">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="service_cost" class="form-label">Cost per Message</label>
                            <input type="number" class="form-control" id="service_cost" value="0.0000" step="0.0001" min="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="service_currency" class="form-label">Currency</label>
                            <select class="form-select" id="service_currency">
                                <option value="TZS">TZS</option>
                                <option value="USD">USD</option>
                                <option value="EUR">EUR</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="service_webhook_url" class="form-label">Webhook URL</label>
                            <input type="url" class="form-control" id="service_webhook_url" placeholder="https://your-domain.com/webhook">
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="service_test_mode">
                                <label class="form-check-label" for="service_test_mode">
                                    Test Mode (No charges, dummy responses)
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="service_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="service_notes" rows="2" placeholder="Additional notes about this service..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveService()">Save Service</button>
            </div>
        </div>
    </div>
</div>

<!-- Service Details Modal -->
<div class="modal fade" id="serviceDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bx bx-eye me-2"></i>
                    Service Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="serviceDetailsContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Test Result Modal -->
<div class="modal fade" id="testResultModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bx bx-test-tube me-2"></i>
                    Connection Test Result
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="testResultContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Authentication method toggle
document.querySelectorAll('input[name="auth_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const bearerFields = document.getElementById('bearer_auth_fields');
        const basicFields = document.getElementById('basic_auth_fields');
        
        if (this.value === 'bearer') {
            bearerFields.style.display = 'block';
            basicFields.style.display = 'none';
        } else {
            bearerFields.style.display = 'none';
            basicFields.style.display = 'block';
        }
    });
});

function addService() {
    document.getElementById('serviceModalTitle').innerHTML = '<i class="bx bx-plus me-2"></i>Add Messaging Service';
    document.getElementById('serviceForm').reset();
    document.getElementById('service_id').value = '';
    document.getElementById('bearer_auth_fields').style.display = 'block';
    document.getElementById('basic_auth_fields').style.display = 'none';
    document.getElementById('auth_bearer').checked = true;
    
    const modal = new bootstrap.Modal(document.getElementById('serviceModal'));
    modal.show();
}

function editService(serviceId) {
    document.getElementById('serviceModalTitle').innerHTML = '<i class="bx bx-edit me-2"></i>Edit Messaging Service';
    
    // Fetch service data from database
    fetch(`/messaging/services/${serviceId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const serviceData = data.data;
                
                // Populate form with real data
                document.getElementById('service_id').value = serviceData.id;
                document.getElementById('service_name').value = serviceData.name || '';
                document.getElementById('service_type').value = serviceData.type || '';
                document.getElementById('service_provider').value = serviceData.provider || '';
                document.getElementById('service_base_url').value = serviceData.base_url || '';
                document.getElementById('service_api_version').value = serviceData.api_version || '';
                document.getElementById('service_sender_id').value = serviceData.sender_id || '';
                document.getElementById('service_bearer_token').value = serviceData.bearer_token || '';
                document.getElementById('service_username').value = serviceData.username || '';
                document.getElementById('service_password').value = serviceData.password || '';
                document.getElementById('service_rate_limit').value = serviceData.rate_limit_per_hour || '';
                document.getElementById('service_cost').value = serviceData.cost_per_message || '';
                document.getElementById('service_currency').value = serviceData.currency || '';
                document.getElementById('service_webhook_url').value = serviceData.webhook_url || '';
                document.getElementById('service_test_mode').checked = serviceData.test_mode || false;
                document.getElementById('service_notes').value = serviceData.notes || '';
                
                // Show appropriate auth fields
                if (serviceData.bearer_token) {
                    document.getElementById('auth_bearer').checked = true;
                    document.getElementById('bearer_auth_fields').style.display = 'block';
                    document.getElementById('basic_auth_fields').style.display = 'none';
                } else {
                    document.getElementById('auth_basic').checked = true;
                    document.getElementById('bearer_auth_fields').style.display = 'none';
                    document.getElementById('basic_auth_fields').style.display = 'block';
                }
                
                const modal = new bootstrap.Modal(document.getElementById('serviceModal'));
                modal.show();
            } else {
                showNotification('Failed to load service data: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showNotification('Error loading service data: ' + error.message, 'error');
        });
}

function saveService() {
    const form = document.getElementById('serviceForm');
    if (!form.checkValidity()) {
        showNotification('Please fill in all required fields', 'warning');
        return;
    }
    
    const serviceId = document.getElementById('service_id').value;
    const isEdit = serviceId !== '';
    
    const formData = {
        name: document.getElementById('service_name').value,
        type: document.getElementById('service_type').value,
        provider: document.getElementById('service_provider').value,
        base_url: document.getElementById('service_base_url').value,
        api_version: document.getElementById('service_api_version').value,
        sender_id: document.getElementById('service_sender_id').value,
        rate_limit_per_hour: parseInt(document.getElementById('service_rate_limit').value),
        cost_per_message: parseFloat(document.getElementById('service_cost').value),
        currency: document.getElementById('service_currency').value,
        webhook_url: document.getElementById('service_webhook_url').value,
        test_mode: document.getElementById('service_test_mode').checked,
        notes: document.getElementById('service_notes').value,
        is_active: true
    };
    
    // Add authentication based on selected method
    if (document.getElementById('auth_bearer').checked) {
        formData.bearer_token = document.getElementById('service_bearer_token').value;
    } else {
        formData.username = document.getElementById('service_username').value;
        formData.password = document.getElementById('service_password').value;
    }
    
    showNotification(isEdit ? 'Updating service...' : 'Creating service...', 'info');
    
    const url = isEdit 
        ? `/messaging/services/${serviceId}`
        : '/messaging/services';
    
    const method = isEdit ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`Service ${isEdit ? 'updated' : 'created'} successfully!`, 'success');
            bootstrap.Modal.getInstance(document.getElementById('serviceModal')).hide();
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(`Failed to ${isEdit ? 'update' : 'create'} service: ` + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification(`Error ${isEdit ? 'updating' : 'creating'} service: ` + error.message, 'error');
    });
}

function viewService(serviceId) {
    // Fetch service data from database
    fetch(`/messaging/services/${serviceId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const serviceData = data.data;
                
                const content = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Service Name</label>
                                <div class="fw-bold">${serviceData.name || '-'}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Type</label>
                                <div><span class="badge bg-primary">${serviceData.type || '-'}</span></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Provider</label>
                                <div>${serviceData.provider || '-'}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Base URL</label>
                                <div><small>${serviceData.base_url || '-'}</small></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">API Version</label>
                                <div>${serviceData.api_version || '-'}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Sender ID</label>
                                <div>${serviceData.sender_id || '-'}</div>
                            </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <div>
                        <span class="badge bg-${serviceData.is_active ? 'success' : 'secondary'}">${serviceData.is_active ? 'Active' : 'Inactive'}</span>
                        ${serviceData.test_mode ? '<span class="badge bg-warning ms-1">TEST MODE</span>' : ''}
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Rate Limit</label>
                    <div>${serviceData.rate_limit_per_hour || 0} messages/hour</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Cost per Message</label>
                    <div>${serviceData.currency || 'TZS'} ${parseFloat(serviceData.cost_per_message || 0).toFixed(4)}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Messages Sent</label>
                    <div>
                        SMS: ${serviceData.sms_messages_count || 0}
                        ${serviceData.email_messages_count ? '<br>Email: ' + serviceData.email_messages_count : ''}
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Last Sync</label>
                    <div>${serviceData.last_sync_at ? new Date(serviceData.last_sync_at).toLocaleString() : 'Never'}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Created</label>
                    <div>${serviceData.created_at ? new Date(serviceData.created_at).toLocaleString() : 'Unknown'}</div>
                </div>
            </div>
        </div>
        ${serviceData.notes ? `
        <div class="mb-3">
            <label class="form-label">Notes</label>
            <div class="bg-light p-2 rounded">${serviceData.notes}</div>
        </div>
        ` : ''}
    `;
    
    document.getElementById('serviceDetailsContent').innerHTML = content;
    new bootstrap.Modal(document.getElementById('serviceDetailsModal')).show();
} else {
    showNotification('Failed to load service details: ' + data.message, 'error');
}
})
.catch(error => {
    showNotification('Error loading service details: ' + error.message, 'error');
});
}

function testService(serviceId) {
    showNotification('Testing service connection...', 'info');
    
    fetch(`/messaging/services/${serviceId}/test`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showTestResult(true, data.message, data.response);
        } else {
            showTestResult(false, data.message, data.error);
        }
    })
    .catch(error => {
        showTestResult(false, 'Connection test failed', error.message);
    });
}

function showTestResult(success, message, details) {
    const content = `
        <div class="text-center mb-3">
            <div class="avatar ${success ? 'bg-success bg-opacity-10' : 'bg-danger bg-opacity-10'} rounded-circle mx-auto" style="width: 64px; height: 64px;">
                <i class="bx ${success ? 'bx-check-circle text-success' : 'bx-x-circle text-danger'} fs-1"></i>
            </div>
        </div>
        <div class="text-center">
            <h5 class="${success ? 'text-success' : 'text-danger'}">${message}</h5>
            ${details ? `<pre class="bg-light p-2 rounded text-start mt-3"><small>${JSON.stringify(details, null, 2)}</small></pre>` : ''}
        </div>
    `;
    
    document.getElementById('testResultContent').innerHTML = content;
    new bootstrap.Modal(document.getElementById('testResultModal')).show();
}

function toggleServiceStatus(serviceId, activate) {
    const action = activate ? 'activate' : 'deactivate';
    if (confirm(`Are you sure you want to ${action} this service?`)) {
        showNotification(`${action.charAt(0).toUpperCase() + action.slice(1)}ing service...`, 'info');
        
        fetch(`/messaging/services/${serviceId}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ is_active: activate })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(`Service ${action}d successfully`, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification(`Failed to ${action} service: ` + data.message, 'error');
            }
        })
        .catch(error => {
            showNotification(`Error ${action}ing service: ` + error.message, 'error');
        });
    }
}

function deleteService(serviceId) {
    if (confirm('Are you sure you want to delete this service? This action cannot be undone.')) {
        showNotification('Deleting service...', 'info');
        
        fetch(`/messaging/services/${serviceId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Service deleted successfully', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification('Failed to delete service: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showNotification('Error deleting service: ' + error.message, 'error');
        });
    }
}

function refreshServices() {
    showNotification('Refreshing services...', 'info');
    setTimeout(() => location.reload(), 1000);
}

// Search functionality
document.getElementById('searchServices').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#servicesTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Type filter
document.getElementById('filterType').addEventListener('change', function() {
    const type = this.value;
    const rows = document.querySelectorAll('#servicesTable tbody tr');
    
    rows.forEach(row => {
        if (!type) {
            row.style.display = '';
        } else {
            const typeBadge = row.querySelector('.badge');
            const rowType = typeBadge ? typeBadge.textContent : '';
            row.style.display = rowType === type ? '' : 'none';
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
