@extends('layouts.app')

@section('title', 'My Profile - FeedTan Pay')
@section('description', 'FeedTan Pay - Manage your profile information')

@section('content')
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row">
    <div class="col-md-4">
        <!-- Profile Card -->
        <div class="card mb-6">
            <div class="card-body">
                <div class="text-center">
                    <div class="avatar avatar-xl mb-4">
                        <img src="{{ $user->avatar ? asset('uploads/avatars/' . $user->avatar) : asset('assets/img/avatars/1.png') }}" alt="Profile" class="rounded-circle">
                        <button class="avatar-edit-btn btn btn-sm btn-primary rounded-circle" onclick="changeAvatar()">
                            <i class="bx bx-camera"></i>
                        </button>
                    </div>
                    <h4 class="mb-1">{{ $user->name }}</h4>
                    <p class="text-muted">{{ $user->email }}</p>
                    <div class="d-flex justify-content-center gap-2 mb-4">
                        <span class="badge bg-success">Active</span>
                        <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : 'info' }}">{{ ucfirst($user->role) }}</span>
                    </div>
                    <p class="text-muted">
                        @if($user->role === 'admin')
                            System administrator with full access to FeedTan Pay features.
                        @else
                            Staff member managing payments and customer support.
                        @endif
                    </p>
                </div>
                
                <div class="row text-center">
                    <div class="col-4">
                        <h6 class="mb-0">{{ \App\Models\EmailMessage::where('user_id', $user->id)->count() }}</h6>
                        <small class="text-muted">Emails Sent</small>
                    </div>
                    <div class="col-4">
                        <h6 class="mb-0">{{ \App\Models\SmsMessage::where('user_id', $user->id)->count() }}</h6>
                        <small class="text-muted">SMS Sent</small>
                    </div>
                    <div class="col-4">
                        <h6 class="mb-0">98%</h6>
                        <small class="text-muted">Success Rate</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary" onclick="editProfile()">
                        <i class="bx bx-edit me-2"></i>Edit Profile
                    </button>
                    <button class="btn btn-outline-info" onclick="changePassword()">
                        <i class="bx bx-key me-2"></i>Change Password
                    </button>
                    <button class="btn btn-outline-success" onclick="downloadData()">
                        <i class="bx bx-download me-2"></i>Download My Data
                    </button>
                    <button class="btn btn-outline-warning" onclick="exportSettings()">
                        <i class="bx bx-export me-2"></i>Export Settings
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Profile Information -->
        <div class="card mb-6">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Profile Information</h5>
                <button class="btn btn-sm btn-primary" onclick="editProfile()">
                    <i class="bx bx-edit"></i> Edit
                </button>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label text-muted">Full Name</label>
                        <p class="mb-0">{{ $user->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Username</label>
                        <p class="mb-0">@{{ Str::slug($user->name) }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Email</label>
                        <p class="mb-0">{{ $user->email }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Role</label>
                        <p class="mb-0">
                            <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : 'info' }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Phone</label>
                        <p class="mb-0">{{ $user->phone ?? 'Not provided' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Date of Birth</label>
                        <p class="mb-0">{{ $user->date_of_birth ?? 'Not provided' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Location</label>
                        <p class="mb-0">{{ $user->location ?? 'Not provided' }}</p>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted">Bio</label>
                        <p class="mb-0">{{ $user->bio ?? 'No bio provided' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Settings -->
        <div class="card mb-6">
            <div class="card-header">
                <h5 class="card-title mb-0">Account Settings</h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label text-muted">Language</label>
                        <p class="mb-0">English (US)</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Timezone</label>
                        <p class="mb-0">Pacific Time (PT)</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Currency</label>
                        <p class="mb-0">USD ($)</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Date Format</label>
                        <p class="mb-0">MM/DD/YYYY</p>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted">Email Notifications</label>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="emailNotif" checked>
                            <label class="form-check-label" for="emailNotif">
                                Receive email notifications
                            </label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="marketingNotif">
                            <label class="form-check-label" for="marketingNotif">
                                Marketing emails
                            </label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="securityNotif" checked>
                            <label class="form-check-label" for="securityNotif">
                                Security alerts
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Log -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Activity</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-point bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Profile Updated</h6>
                            <p class="text-muted mb-0">You updated your profile information</p>
                            <small class="text-muted">2 hours ago</small>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-point bg-primary"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Password Changed</h6>
                            <p class="text-muted mb-0">You successfully changed your password</p>
                            <small class="text-muted">3 days ago</small>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-point bg-warning"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">New Device Login</h6>
                            <p class="text-muted mb-0">Login from Chrome on Windows</p>
                            <small class="text-muted">1 week ago</small>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-point bg-info"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Account Created</h6>
                            <p class="text-muted mb-0">Your account was successfully created</p>
                            <small class="text-muted">1 month ago</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editProfileForm">
                    <div class="mb-3">
                        <label for="editName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="editName" value="{{ $user->name }}">
                    </div>
                    <div class="mb-3">
                        <label for="editEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="editEmail" value="{{ $user->email }}">
                    </div>
                    <div class="mb-3">
                        <label for="editPhone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="editPhone" value="{{ $user->phone ?? '' }}">
                    </div>
                    <div class="mb-3">
                        <label for="editLocation" class="form-label">Location</label>
                        <input type="text" class="form-control" id="editLocation" value="{{ $user->location ?? '' }}">
                    </div>
                    <div class="mb-3">
                        <label for="editBio" class="form-label">Bio</label>
                        <textarea class="form-control" id="editBio" rows="3">{{ $user->bio ?? '' }}</textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveProfile()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="changePasswordForm">
                    <div class="mb-3">
                        <label for="currentPassword" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="currentPassword" required>
                    </div>
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="newPassword" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirmPassword" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updatePassword()">Change Password</button>
            </div>
        </div>
    </div>
</div>

<!-- Avatar Upload Modal -->
<div class="modal fade" id="avatarUploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Avatar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="avatarUploadForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="avatarFile" class="form-label">Choose Avatar Image</label>
                        <input type="file" class="form-control" id="avatarFile" accept="image/*" required>
                        <div class="form-text">Allowed formats: JPEG, PNG, GIF. Maximum size: 2MB</div>
                    </div>
                    <div class="mb-3">
                        <div id="avatarPreview" class="text-center" style="display: none;">
                            <img id="avatarPreviewImg" src="" alt="Avatar Preview" class="rounded-circle" style="max-width: 150px; max-height: 150px;">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="uploadAvatar()">Upload Avatar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-edit-btn {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 32px;
    height: 32px;
    border: 2px solid #fff;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
}

.timeline-point {
    position: absolute;
    left: -22px;
    top: 0;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
}
</style>
@endpush

@push('scripts')
<script>
// Load user profile data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadUserProfile();
});

function loadUserProfile() {
    fetch('/api/profile')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateUserDisplay(data.user);
            } else {
                showNotification('Error loading profile data', 'error');
            }
        })
        .catch(error => {
            showNotification('Error loading profile data', 'error');
        });
}

function updateUserDisplay(user) {
    // Update avatar
    const avatarImg = document.querySelector('.avatar img');
    if (user.avatar) {
        avatarImg.src = user.avatar;
    }
    
    // Update profile info
    if (document.getElementById('userName')) {
        document.getElementById('userName').textContent = user.name;
    }
    if (document.getElementById('userEmail')) {
        document.getElementById('userEmail').textContent = user.email;
    }
    if (document.getElementById('userPhone')) {
        document.getElementById('userPhone').textContent = user.phone || 'Not provided';
    }
    if (document.getElementById('userLocation')) {
        document.getElementById('userLocation').textContent = user.location || 'Not provided';
    }
    if (document.getElementById('userBio')) {
        document.getElementById('userBio').textContent = user.bio || 'No bio provided';
    }
}

function editProfile() {
    fetch('/api/profile')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Populate form with current data
                document.getElementById('editName').value = data.user.name;
                document.getElementById('editEmail').value = data.user.email;
                document.getElementById('editPhone').value = data.user.phone || '';
                document.getElementById('editLocation').value = data.user.location || '';
                document.getElementById('editBio').value = data.user.bio || '';
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('editProfileModal'));
                modal.show();
            } else {
                showNotification('Error loading profile data', 'error');
            }
        })
        .catch(error => {
            showNotification('Error loading profile data', 'error');
        });
}

function saveProfile() {
    const formData = new FormData(document.getElementById('editProfileForm'));
    
    const saveBtn = document.querySelector('#editProfileModal .btn-primary');
    const originalText = saveBtn.innerHTML;
    
    // Show loading state
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-2"></i>Saving...';
    
    fetch('/api/profile/update', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
        
        if (data.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('editProfileModal'));
            modal.hide();
            
            // Update display
            updateUserDisplay(data.user);
            
            showNotification('Profile updated successfully!', 'success');
        } else {
            showNotification('Error updating profile: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
        showNotification('Error updating profile', 'error');
    });
}

function changeAvatar() {
    const modal = new bootstrap.Modal(document.getElementById('avatarUploadModal'));
    modal.show();
}

function uploadAvatar() {
    const form = document.getElementById('avatarUploadForm');
    const formData = new FormData(form);
    
    const uploadBtn = document.querySelector('#avatarUploadModal .btn-primary');
    const originalText = uploadBtn.innerHTML;
    
    // Show loading state
    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-2"></i>Uploading...';
    
    fetch('/api/profile/upload-avatar', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = originalText;
        
        if (data.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('avatarUploadModal'));
            modal.hide();
            
            // Update avatar display
            const avatarImg = document.querySelector('.avatar img');
            avatarImg.src = data.avatar_url;
            
            showNotification('Avatar uploaded successfully!', 'success');
        } else {
            showNotification('Error uploading avatar: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = originalText;
        showNotification('Error uploading avatar', 'error');
    });
}

function changePassword() {
    const modal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
    modal.show();
}

function updatePassword() {
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    if (!currentPassword || !newPassword || !confirmPassword) {
        showNotification('Please fill in all password fields', 'warning');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        showNotification('Passwords do not match', 'warning');
        return;
    }
    
    if (newPassword.length < 8) {
        showNotification('Password must be at least 8 characters long', 'warning');
        return;
    }
    
    const changeBtn = document.querySelector('#changePasswordModal .btn-primary');
    const originalText = changeBtn.innerHTML;
    
    // Show loading state
    changeBtn.disabled = true;
    changeBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-2"></i>Updating...';
    
    fetch('/api/change-password', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            current_password: currentPassword,
            new_password: newPassword,
            new_password_confirmation: confirmPassword
        })
    })
    .then(response => response.json())
    .then(data => {
        changeBtn.disabled = false;
        changeBtn.innerHTML = originalText;
        
        if (data.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('changePasswordModal'));
            modal.hide();
            
            showNotification('Password changed successfully!', 'success');
        } else {
            showNotification('Error changing password: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        changeBtn.disabled = false;
        changeBtn.innerHTML = originalText;
        showNotification('Error changing password', 'error');
    });
}

function downloadData() {
    if (confirm('Download all your personal data? This may take a moment.')) {
        const downloadBtn = event.target;
        const originalText = downloadBtn.innerHTML;
        
        downloadBtn.disabled = true;
        downloadBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-2"></i>Preparing...';
        
        fetch('/api/download-user-data', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (response.ok) {
                return response.blob();
            }
            throw new Error('Download failed');
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'feedtan_user_data_' + new Date().toISOString().split('T')[0] + '.json';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            downloadBtn.disabled = false;
            downloadBtn.innerHTML = originalText;
            
            showNotification('Data downloaded successfully!', 'success');
        })
        .catch(error => {
            downloadBtn.disabled = false;
            downloadBtn.innerHTML = originalText;
            showNotification('Error downloading data', 'error');
        });
    }
}

function exportSettings() {
    showNotification('Exporting your account settings...', 'info');
    // This would open settings export modal or trigger download
}

// Avatar preview functionality
document.getElementById('avatarFile')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatarPreview');
            const previewImg = document.getElementById('avatarPreviewImg');
            
            preview.style.display = 'block';
            previewImg.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
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
