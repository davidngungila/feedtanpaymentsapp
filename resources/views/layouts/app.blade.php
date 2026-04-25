<!DOCTYPE html>
<html lang="en" class="layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>@yield('title', 'Dashboard') | FeedTan Pay - Bootstrap Dashboard FREE</title>
    <meta name="description" content="@yield('description', '')" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/iconify-icons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />

    <!-- Page CSS -->
    @stack('styles')

<style>
:root {
    --bs-primary: #198754;
    --bs-primary-rgb: 25, 135, 84;
    --bs-primary-text-emphasis: #ffffff;
}

.text-primary {
    color: #198754 !important;
}

.bg-primary {
    background-color: #198754 !important;
}

.btn-primary {
    background-color: #198754 !important;
    border-color: #198754 !important;
}

.btn-primary:hover {
    background-color: #157347 !important;
    border-color: #157347 !important;
}

.card-title.text-primary {
    color: #198754 !important;
}

.avatar-initial.bg-label-primary {
    background-color: rgba(25, 135, 84, 0.1) !important;
    color: #198754 !important;
}

.nav-link.active {
    color: #198754 !important;
}

.menu-item.active .menu-link {
    color: #198754 !important;
}

.progress-bar.bg-success {
    background-color: #198754 !important;
}

.badge.bg-label-primary {
    background-color: rgba(25, 135, 84, 0.1) !important;
    color: #198754 !important;
}

/* Fix icon display issues */
.dropdown-menu {
    z-index: 1050;
}

.avatar {
    display: flex;
    align-items: center;
    justify-content: center;
}

.avatar img {
    width: 32px;
    height: 32px;
    object-fit: cover;
}

/* Fix modal backdrop issues */
.modal {
    z-index: 1060;
}

.modal-backdrop {
    z-index: 1055;
}

/* Fix table action dropdown positioning */
.dropdown {
    position: relative;
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 1060;
}
</style>

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    
    <!-- Config: Mandatory theme config file -->
    <script src="{{ asset('assets/js/config.js') }}"></script>
    
    <!-- Timezone Configuration -->
    <script>
        // Set system timezone to TZS
        window.TZS_TIMEZONE = 'America/New_York';
        window.TZS_FORMAT = 'MM/DD/YYYY hh:mm A';
        
        // Initialize timezone settings
        if (typeof moment !== 'undefined') {
            moment.tz.setDefault(window.TZS_TIMEZONE);
        }
    </script>
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            @include('components.sidebar')
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                @include('components.header')
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        @yield('content')
                    </div>
                    <!-- / Content -->

                    <!-- Footer -->
                    @include('components.footer')
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    
    <!-- Core JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>

    <!-- Vendors JS -->
    <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>

    <!-- Main JS -->
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <!-- Page JS -->
    @stack('scripts')

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055">
        <!-- Toasts will be dynamically added here -->
    </div>

    <!-- Toast Helper Functions -->
    <script>
    // Toast notification helper functions
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
    
    // Specific toast functions for common use cases
    function showSuccessToast(message, title = 'Success') {
        showToast(message, 'success', title, 4000);
    }
    
    function showErrorToast(message, title = 'Error') {
        showToast(message, 'danger', title, 6000);
    }
    
    function showWarningToast(message, title = 'Warning') {
        showToast(message, 'warning', title, 5000);
    }
    
    function showInfoToast(message, title = 'Information') {
        showToast(message, 'info', title, 4000);
    }
    
    // Auto-show session messages (if any)
    document.addEventListener('DOMContentLoaded', function() {
        // Check for any session messages and show them as toasts
        const successMessages = @json(session()->get('success', []));
        const errorMessages = @json(session()->get('error', []));
        const warningMessages = @json(session()->get('warning', []));
        const infoMessages = @json(session()->get('info', []));
        
        successMessages.forEach(message => showSuccessToast(message));
        errorMessages.forEach(message => showErrorToast(message));
        warningMessages.forEach(message => showWarningToast(message));
        infoMessages.forEach(message => showInfoToast(message));
        
        // Clear session messages
        @php
            session()->forget(['success', 'error', 'warning', 'info']);
        @endphp
    });
    </script>

    <!-- Place this tag before closing body tag for github widget button. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
</body>
</html>
