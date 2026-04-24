@extends('layouts.app')

@section('title', 'Security Settings - FeedTan Pay')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-1">Security Settings</h4>
                        <p class="text-muted mb-0">Configure comprehensive security measures to protect your FeedTan Pay system</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="runSecurityAudit()">
                            <i class='bx bx-shield-check me-1'></i> Run Security Audit
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="exportSecurityReport()">
                            <i class='bx bx-download me-1'></i> Export Report
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Overview Dashboard -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Security Score</h6>
                                <h3 class="mb-0">92%</h3>
                            </div>
                            <div class="avatar avatar-lg">
                                <i class='bx bx-shield-alt bx-lg'></i>
                            </div>
                        </div>
                        <small>Excellent security posture</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Active Users</h6>
                                <h3 class="mb-0">247</h3>
                            </div>
                            <div class="avatar avatar-lg">
                                <i class='bx bx-user-check bx-lg'></i>
                            </div>
                        </div>
                        <small>All users secured</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Failed Attempts</h6>
                                <h3 class="mb-0">12</h3>
                            </div>
                            <div class="avatar avatar-lg">
                                <i class='bx bx-error bx-lg'></i>
                            </div>
                        </div>
                        <small>Last 24 hours</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">2FA Enabled</h6>
                                <h3 class="mb-0">89%</h3>
                            </div>
                            <div class="avatar avatar-lg">
                                <i class='bx bx-lock-alt bx-lg'></i>
                            </div>
                        </div>
                        <small>Compliance rate</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Authentication Security -->
        <div class="row mb-4">
            <div class="col-12">
                <h5 class="mb-3"><i class='bx bx-lock me-2'></i>Authentication Security</h5>
            </div>
            
            <!-- Password Policy -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Password Policy</h5>
                        <span class="badge bg-success">Active</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Minimum Password Length</label>
                            <input type="number" class="form-control" id="minPasswordLength" value="8" min="6" max="32">
                            <small class="text-muted">Recommended: 12+ characters for high security</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password Complexity</label>
                            <select class="form-select" id="passwordComplexity">
                                <option value="basic">Basic (8+ chars, letters only)</option>
                                <option value="medium" selected>Medium (1 uppercase, 1 lowercase, 1 number, 1 special)</option>
                                <option value="strong">Strong (2 uppercase, 2 lowercase, 2 numbers, 2 special)</option>
                                <option value="paranoid">Paranoid (3 uppercase, 3 lowercase, 3 numbers, 3 special, no common patterns)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password Expiry (days)</label>
                            <input type="number" class="form-control" id="passwordExpiry" value="90" min="30" max="365">
                            <small class="text-muted">Force password change after this period</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password History</label>
                            <input type="number" class="form-control" id="passwordHistory" value="5" min="0" max="20">
                            <small class="text-muted">Prevent reuse of last N passwords</small>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="preventReuse" checked>
                                <label class="form-check-label" for="preventReuse">Prevent Password Reuse</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="dictionaryCheck" checked>
                                <label class="form-check-label" for="dictionaryCheck">Dictionary Word Prevention</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="breachCheck" checked>
                                <label class="form-check-label" for="breachCheck">Check Against Known Breaches</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two-Factor Authentication -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Two-Factor Authentication</h5>
                        <span class="badge bg-success">Enabled</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">2FA Requirement Level</label>
                            <select class="form-select" id="2faRequirement">
                                <option value="disabled">Disabled</option>
                                <option value="optional" selected>Optional</option>
                                <option value="admin">Admin Users Only</option>
                                <option value="sensitive">Sensitive Operations Only</option>
                                <option value="required">Required for All Users</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Available 2FA Methods</label>
                            <div class="space-y-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="2faSms" checked>
                                    <label class="form-check-label" for="2faSms">
                                        <i class='bx bx-mobile-alt me-1'></i>SMS Authentication
                                        <small class="d-block text-muted">Send codes via SMS</small>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="2faEmail" checked>
                                    <label class="form-check-label" for="2faEmail">
                                        <i class='bx bx-envelope me-1'></i>Email Authentication
                                        <small class="d-block text-muted">Send codes via email</small>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="2faApp" checked>
                                    <label class="form-check-label" for="2faApp">
                                        <i class='bx bx-mobile me-1'></i>Authenticator App
                                        <small class="d-block text-muted">Google Authenticator, Authy, etc.</small>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="2faBackup">
                                    <label class="form-check-label" for="2faBackup">
                                        <i class='bx bx-key me-1'></i>Backup Codes
                                        <small class="d-block text-muted">One-time backup codes</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">2FA Code Validity (minutes)</label>
                            <input type="number" class="form-control" id="2faValidity" value="5" min="1" max="30">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">2FA Rate Limiting</label>
                            <input type="number" class="form-control" id="2faRateLimit" value="3" min="1" max="10">
                            <small class="text-muted">Max attempts per minute</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Session & Access Control -->
        <div class="row mb-4">
            <div class="col-12">
                <h5 class="mb-3"><i class='bx bx-shield me-2'></i>Session & Access Control</h5>
            </div>
            
            <!-- Session Management -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Session Management</h5>
                        <span class="badge bg-success">Configured</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Session Timeout (minutes)</label>
                            <input type="number" class="form-control" id="sessionTimeout" value="30" min="5" max="480">
                            <small class="text-muted">Auto-logout after inactivity</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Maximum Concurrent Sessions</label>
                            <input type="number" class="form-control" id="maxSessions" value="3" min="1" max="10">
                            <small class="text-muted">Per user limit</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Session Security</label>
                            <div class="space-y-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="rememberDevice" checked>
                                    <label class="form-check-label" for="rememberDevice">Remember Trusted Devices</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="autoLogout" checked>
                                    <label class="form-check-label" for="autoLogout">Auto-logout on suspicious activity</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="secureCookies" checked>
                                    <label class="form-check-label" for="secureCookies">Secure & HTTP-only Cookies</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sessionRotation" checked>
                                    <label class="form-check-label" for="sessionRotation">Session ID Rotation</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Idle Timeout (hours)</label>
                            <input type="number" class="form-control" id="idleTimeout" value="2" min="1" max="24">
                            <small class="text-muted">Force logout after idle period</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- IP & Access Control -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">IP & Access Control</h5>
                        <span class="badge bg-warning">Partial</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">IP Whitelist</label>
                            <textarea class="form-control" id="ipWhitelist" rows="3" placeholder="Enter allowed IP addresses (one per line)"></textarea>
                            <small class="text-muted">Only these IPs can access the system</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">IP Blacklist</label>
                            <textarea class="form-control" id="ipBlacklist" rows="3" placeholder="Enter blocked IP addresses (one per line)"></textarea>
                            <small class="text-muted">These IPs are blocked from access</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Geographic Restrictions</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="geoBlocking">
                                <label class="form-check-label" for="geoBlocking">Enable Geographic Blocking</label>
                            </div>
                            <select class="form-select mt-2" id="allowedCountries" multiple>
                                <option value="TZ" selected>Tanzania</option>
                                <option value="KE">Kenya</option>
                                <option value="UG">Uganda</option>
                                <option value="RW">Rwanda</option>
                                <option value="US">United States</option>
                                <option value="GB">United Kingdom</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rate Limiting</label>
                            <input type="number" class="form-control" id="rateLimit" value="100" min="10" max="1000">
                            <small class="text-muted">Requests per minute per IP</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Security Features -->
        <div class="row mb-4">
            <div class="col-12">
                <h5 class="mb-3"><i class='bx bx-shield-quarter me-2'></i>Advanced Security Features</h5>
            </div>
            
            <!-- Security Monitoring -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Security Monitoring</h5>
                        <span class="badge bg-success">Active</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Log Retention Period</label>
                            <select class="form-select" id="logRetention">
                                <option value="30">30 Days</option>
                                <option value="60">60 Days</option>
                                <option value="90" selected>90 Days</option>
                                <option value="180">180 Days</option>
                                <option value="365">1 Year</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Security Events to Log</label>
                            <div class="space-y-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="logFailedAttempts" checked>
                                    <label class="form-check-label" for="logFailedAttempts">Failed Login Attempts</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="logPasswordChanges" checked>
                                    <label class="form-check-label" for="logPasswordChanges">Password Changes</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="log2faChanges" checked>
                                    <label class="form-check-label" for="log2faChanges">2FA Changes</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="logPrivilegedActions" checked>
                                    <label class="form-check-label" for="logPrivilegedActions">Privileged Actions</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="logDataAccess" checked>
                                    <label class="form-check-label" for="logDataAccess">Data Access Events</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alert Threshold</label>
                            <select class="form-select" id="alertThreshold">
                                <option value="3" selected>3 failed attempts</option>
                                <option value="5">5 failed attempts</option>
                                <option value="10">10 failed attempts</option>
                                <option value="disabled">Disabled</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fraud Detection -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Fraud Detection</h5>
                        <span class="badge bg-warning">Basic</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Fraud Detection Level</label>
                            <select class="form-select" id="fraudDetection">
                                <option value="disabled">Disabled</option>
                                <option value="basic" selected>Basic</option>
                                <option value="standard">Standard</option>
                                <option value="advanced">Advanced</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Detection Rules</label>
                            <div class="space-y-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="detectUnusualLogin" checked>
                                    <label class="form-check-label" for="detectUnusualLogin">Unusual Login Patterns</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="detectHighRisk" checked>
                                    <label class="form-check-label" for="detectHighRisk">High-Risk Transactions</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="detectVelocity" checked>
                                    <label class="form-check-label" for="detectVelocity">Velocity Checks</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="detectDevice">
                                    <label class="form-check-label" for="detectDevice">Device Fingerprinting</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Action on Suspicious Activity</label>
                            <select class="form-select" id="suspiciousAction">
                                <option value="log" selected>Log Only</option>
                                <option value="alert">Send Alert</option>
                                <option value="block">Block Activity</option>
                                <option value="lockout">Temporary Lockout</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Security Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <p class="text-muted mb-3">Perform security-related actions and generate reports</p>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-primary" onclick="saveSecuritySettings()">
                                        <i class='bx bx-save me-1'></i> Save Security Settings
                                    </button>
                                    <a href="{{ route('system-settings.security-logs') }}" class="btn btn-outline-info">
                                        <i class='bx bx-file me-1'></i> View Security Logs
                                    </a>
                                    <button type="button" class="btn btn-outline-warning" onclick="forcePasswordReset()">
                                        <i class='bx bx-reset me-1'></i> Force Password Reset
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="generateBackupCodes()">
                                        <i class='bx bx-key me-1'></i> Generate Backup Codes
                                    </button>
                                    <button type="button" class="btn btn-outline-success" onclick="testSecurityConfig()">
                                        <i class='bx bx-test-tube me-1'></i> Test Configuration
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex flex-column align-items-end">
                                    <small class="text-muted">Last security audit: 2 days ago</small>
                                    <small class="text-muted">Next scheduled: In 7 days</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Security Modals -->
<div class="modal fade" id="securityAuditModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Security Audit Results</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="auditResults"></div>
            </div>
        </div>
    </div>
</div>

<script>
// Security Settings JavaScript
function saveSecuritySettings() {
    const settings = {
        passwordPolicy: {
            minLength: document.getElementById('minPasswordLength').value,
            complexity: document.getElementById('passwordComplexity').value,
            expiry: document.getElementById('passwordExpiry').value,
            history: document.getElementById('passwordHistory').value,
            preventReuse: document.getElementById('preventReuse').checked,
            dictionaryCheck: document.getElementById('dictionaryCheck').checked,
            breachCheck: document.getElementById('breachCheck').checked
        },
        twoFactorAuth: {
            requirement: document.getElementById('2faRequirement').value,
            methods: {
                sms: document.getElementById('2faSms').checked,
                email: document.getElementById('2faEmail').checked,
                app: document.getElementById('2faApp').checked,
                backup: document.getElementById('2faBackup').checked
            },
            validity: document.getElementById('2faValidity').value,
            rateLimit: document.getElementById('2faRateLimit').value
        },
        sessionManagement: {
            timeout: document.getElementById('sessionTimeout').value,
            maxSessions: document.getElementById('maxSessions').value,
            rememberDevice: document.getElementById('rememberDevice').checked,
            autoLogout: document.getElementById('autoLogout').checked,
            secureCookies: document.getElementById('secureCookies').checked,
            sessionRotation: document.getElementById('sessionRotation').checked,
            idleTimeout: document.getElementById('idleTimeout').value
        },
        accessControl: {
            ipWhitelist: document.getElementById('ipWhitelist').value,
            ipBlacklist: document.getElementById('ipBlacklist').value,
            geoBlocking: document.getElementById('geoBlocking').checked,
            allowedCountries: Array.from(document.getElementById('allowedCountries').selectedOptions).map(opt => opt.value),
            rateLimit: document.getElementById('rateLimit').value
        },
        monitoring: {
            logRetention: document.getElementById('logRetention').value,
            events: {
                failedAttempts: document.getElementById('logFailedAttempts').checked,
                passwordChanges: document.getElementById('logPasswordChanges').checked,
                twoFactorChanges: document.getElementById('log2faChanges').checked,
                privilegedActions: document.getElementById('logPrivilegedActions').checked,
                dataAccess: document.getElementById('logDataAccess').checked
            },
            alertThreshold: document.getElementById('alertThreshold').value
        },
        fraudDetection: {
            level: document.getElementById('fraudDetection').value,
            rules: {
                unusualLogin: document.getElementById('detectUnusualLogin').checked,
                highRisk: document.getElementById('detectHighRisk').checked,
                velocity: document.getElementById('detectVelocity').checked,
                device: document.getElementById('detectDevice').checked
            },
            suspiciousAction: document.getElementById('suspiciousAction').value
        }
    };
    
    // Show loading state
    showNotification('Saving security settings...', 'info');
    
    // Simulate API call
    setTimeout(() => {
        showNotification('Security settings saved successfully!', 'success');
        console.log('Security settings saved:', settings);
    }, 1500);
}

function runSecurityAudit() {
    showNotification('Running security audit...', 'info');
    
    setTimeout(() => {
        const auditResults = `
            <div class="alert alert-success">
                <h6><i class='bx bx-check-circle me-2'></i>Security Audit Complete</h6>
                <p class="mb-0">Overall Security Score: 92/100</p>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <h6 class="mb-3">Strengths</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class='bx bx-check text-success me-2'></i>Strong password policy</li>
                        <li class="mb-2"><i class='bx bx-check text-success me-2'></i>2FA properly configured</li>
                        <li class="mb-2"><i class='bx bx-check text-success me-2'></i>Session management active</li>
                        <li class="mb-2"><i class='bx bx-check text-success me-2'></i>Comprehensive logging enabled</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="mb-3">Recommendations</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class='bx bx-info-circle text-warning me-2'></i>Enable geographic blocking</li>
                        <li class="mb-2"><i class='bx bx-info-circle text-warning me-2'></i>Consider advanced fraud detection</li>
                        <li class="mb-2"><i class='bx bx-info-circle text-warning me-2'></i>Implement device fingerprinting</li>
                        <li class="mb-2"><i class='bx bx-info-circle text-warning me-2'></i>Set up automated security scans</li>
                    </ul>
                </div>
            </div>
            
            <div class="mt-3">
                <h6 class="mb-2">Security Checklist</h6>
                <div class="progress mb-2" style="height: 25px;">
                    <div class="progress-bar bg-success" style="width: 92%">92% Complete</div>
                </div>
            </div>
        `;
        
        document.getElementById('auditResults').innerHTML = auditResults;
        new bootstrap.Modal(document.getElementById('securityAuditModal')).show();
    }, 2000);
}

function viewSecurityLogs() {
    showNotification('Opening security logs...', 'info');
    // Redirect to security logs page
    window.location.href = '/system-settings/security-logs';
}

function forcePasswordReset() {
    if (confirm('Are you sure you want to force password reset for all users? This will require all users to change their passwords on next login.')) {
        showNotification('Password reset initiated for all users...', 'warning');
        setTimeout(() => {
            showNotification('Password reset completed successfully!', 'success');
        }, 2000);
    }
}

function generateBackupCodes() {
    showNotification('Generating backup codes...', 'info');
    setTimeout(() => {
        showNotification('Backup codes generated and sent to admin email!', 'success');
    }, 1500);
}

function testSecurityConfig() {
    showNotification('Testing security configuration...', 'info');
    setTimeout(() => {
        showNotification('Security configuration test passed!', 'success');
    }, 1500);
}

function exportSecurityReport() {
    showNotification('Generating security report...', 'info');
    setTimeout(() => {
        showNotification('Security report downloaded successfully!', 'success');
    }, 1500);
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
</script>
@endsection
