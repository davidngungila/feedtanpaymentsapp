@extends('layouts.app')

@section('title', 'Initiate Payment')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Initiate Payment</h5>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('warning_type') === 'insufficient_funds')
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <strong>Payment Failed - Insufficient Funds</strong><br>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('payments.store') }}" method="POST" id="paymentForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payer_name" class="form-label">Payer Name *</label>
                                    <input type="text" class="form-control" id="payer_name" name="payer_name" 
                                           value="{{ old('payer_name') }}" required>
                                    @error('payer_name')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone_number" class="form-label">Phone Number *</label>
                                    <input type="text" class="form-control" id="phone_number" name="phone_number" 
                                           value="{{ old('phone_number') }}" placeholder="255712345678" required>
                                    @error('phone_number')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Format: 255712345678 (Tanzania numbers only)</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Amount (TZS) *</label>
                                    <input type="number" class="form-control" id="amount" name="amount" 
                                           value="{{ old('amount') }}" min="100" max="1000000" required>
                                    @error('amount')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Minimum: 100 TZS, Maximum: 1,000,000 TZS</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <input type="text" class="form-control" id="description" name="description" 
                                           value="{{ old('description') }}" placeholder="Payment description">
                                    @error('description')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-paper-plane"></i> Initiate Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 mb-0">Processing payment...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
    
    // Show loading modal
    loadingModal.show();
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Processing...';
    
    // Handle form submission via AJAX
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route('payments.store') }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        loadingModal.hide();
        
        if (data.success) {
            // Show toast notification based on response type
            if (data.fallback_mode) {
                // Fallback mode - show warning toast
                showToast(data.message, 'warning', 'Payment Queued', 8000);
                
                // Show additional warning toast
                showToast(data.warning || 'API temporarily unavailable - payment queued for processing', 'info', 'System Status', 6000);
            } else {
                // Normal success - show success toast
                showToast(data.message, 'success', 'Payment Initiated', 5000);
            }
            
            // Reset form
            this.reset();
            
            // Optionally redirect to status page after showing toast
            setTimeout(() => {
                window.location.href = '{{ route('payments.status') }}?reference=' + data.order_reference;
            }, data.fallback_mode ? 3000 : 2000);
        } else {
            // Show error toast
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Initiate Payment';
            
            let toastType = 'danger';
            if (data.warning_type === 'insufficient_funds') {
                toastType = 'warning';
            }
            
            showToast(data.message, toastType, 'Payment Failed', 6000);
        }
    })
    .catch(error => {
        loadingModal.hide();
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Initiate Payment';
        
        console.error('Error:', error);
        
        // Show generic error toast
        showToast('An error occurred while processing your payment. Please try again.', 'danger', 'System Error', 6000);
    });
});

// Toast notification function
function showToast(message, type = 'primary', title = 'Notification', duration = 5000) {
    // Create toast container if it doesn't exist
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '1055';
        document.body.appendChild(toastContainer);
    }
    
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
        <div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="${duration}">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="${iconMap[type]} me-2"></i>
                    <strong>${title}:</strong> ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
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

// Phone number formatting
document.getElementById('phone_number').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    
    // Ensure it starts with 255 for Tanzania
    if (value.length === 9 && (value.startsWith('6') || value.startsWith('7'))) {
        value = '255' + value;
    }
    
    e.target.value = value;
});

// Amount formatting
document.getElementById('amount').addEventListener('input', function(e) {
    let value = parseInt(e.target.value);
    
    if (value < 100) {
        e.target.setCustomValidity('Minimum amount is 100 TZS');
    } else if (value > 1000000) {
        e.target.setCustomValidity('Maximum amount is 1,000,000 TZS');
    } else {
        e.target.setCustomValidity('');
    }
});
</script>
@endsection
