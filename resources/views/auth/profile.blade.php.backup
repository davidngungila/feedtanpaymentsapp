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
                        <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Profile" class="rounded-circle">
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
                        <h6 class="mb-0">142</h6>
                        <small class="text-muted">Transactions</small>
                    </div>
                    <div class="col-4">
                        <h6 class="mb-0">98%</h6>
                        <small class="text-muted">Success Rate</small>
                    </div>
                    <div class="col-4">
                        <h6 class="mb-0">4.9</h6>
                        <small class="text-muted">Rating</small>
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
                        <p class="mb-0">+1 (555) 123-4567</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Date of Birth</label>
                        <p class="mb-0">January 15, 1990</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Location</label>
                        <p class="mb-0">San Francisco, CA</p>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted">Bio</label>
                        <p class="mb-0">Digital enthusiast and tech lover. Passionate about creating amazing user experiences and building innovative solutions.</p>
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

        <!-- Connected Accounts -->
        <div class="card mb-6">
            <div class="card-header">
                <h5 class="card-title mb-0">Connected Accounts</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center">
                        <div class="avatar bg-primary bg-opacity-10 rounded-circle me-3">
                            <i class="bx bxl-google text-primary"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Google</h6>
                            <small class="text-muted">john.doe@gmail.com</small>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-outline-danger">Disconnect</button>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center">
                        <div class="avatar bg-info bg-opacity-10 rounded-circle me-3">
                            <i class="bx bxl-facebook text-info"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Facebook</h6>
                            <small class="text-muted">John Doe</small>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-outline-danger">Disconnect</button>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="avatar bg-secondary bg-opacity-10 rounded-circle me-3">
                            <i class="bx bxl-github text-secondary"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">GitHub</h6>
                            <small class="text-muted">johndoe</small>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-outline-danger">Disconnect</button>
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
                    <div class="mb-4">
                        <label for="editName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="editName" value="John Doe">
                    </div>
                    <div class="mb-4">
                        <label for="editUsername" class="form-label">Username</label>
                        <input type="text" class="form-control" id="editUsername" value="@johndoe">
                    </div>
                    <div class="mb-4">
                        <label for="editEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="editEmail" value="john.doe@example.com">
                    </div>
                    <div class="mb-4">
                        <label for="editPhone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="editPhone" value="+1 (555) 123-4567">
                    </div>
                    <div class="mb-4">
                        <label for="editLocation" class="form-label">Location</label>
                        <input type="text" class="form-control" id="editLocation" value="San Francisco, CA">
                    </div>
                    <div class="mb-4">
                        <label for="editBio" class="form-label">Bio</label>
                        <textarea class="form-control" id="editBio" rows="3">Digital enthusiast and tech lover. Passionate about creating amazing user experiences and building innovative solutions.</textarea>
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
function changeAvatar() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = function(e) {
        const file = e.target.files[0];
        if (file) {
            alert('New avatar selected: ' + file.name);
            // In a real application, this would upload the file
        }
    };
    input.click();
}

function editProfile() {
    const modal = new bootstrap.Modal(document.getElementById('editProfileModal'));
    modal.show();
}

function saveProfile() {
    const name = document.getElementById('editName').value;
    const email = document.getElementById('editEmail').value;
    
    if (!name || !email) {
        alert('Please fill in all required fields');
        return;
    }
    
    // Show loading state
    const saveBtn = document.querySelector('#editProfileModal .btn-primary');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-2"></i>Saving...';
    
    // Simulate API call
    setTimeout(() => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = 'Save Changes';
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('editProfileModal'));
        modal.hide();
        
        alert('Profile updated successfully!');
    }, 1500);
}

function changePassword() {
    const currentPassword = prompt('Enter current password:');
    if (!currentPassword) return;
    
    const newPassword = prompt('Enter new password:');
    if (!newPassword) return;
    
    const confirmPassword = prompt('Confirm new password:');
    if (newPassword !== confirmPassword) {
        alert('Passwords do not match');
        return;
    }
    
    alert('Password changed successfully!');
}

function downloadData() {
    if (confirm('Download all your personal data? This may take a moment.')) {
        alert('Preparing your data for download...');
    }
}

function exportSettings() {
    alert('Exporting your account settings...');
}
</script>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <div class="mb-3">
                        <label for="editName" class="form-label">Full Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="editName" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="editEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="editEmail" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="editPhone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="editPhone" name="phone" value="{{ old('phone') }}" placeholder="+1 (555) 123-4567">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="editBio" class="form-label">Bio</label>
                        <textarea class="form-control @error('bio') is-invalid @enderror" id="editBio" name="bio" rows="3" placeholder="Tell us about yourself">{{ old('bio', $user->role === 'admin' ? 'System administrator with full access to FeedTan Pay features.' : 'Staff member managing payments and customer support.') }}</textarea>
                        @error('bio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush
