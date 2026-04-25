@extends('layouts.app')

@section('title', 'Dashboard - FeedTan Pay Analytics')
@section('description', 'FeedTan Pay analytics dashboard with revenue, orders, and transaction statistics')

@section('content')
<div class="row">
    <!-- Welcome Section -->
    <div class="col-xxl-8 mb-6 order-0">
        <div class="card">
            <div class="d-flex align-items-start row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">Welcome {{ Auth::user()->name ?? 'John' }}! &#127881;</h5>
                        <p class="mb-4">
                            Your comprehensive financial overview for <span id="currentDate"></span>.<br>
                            You have <strong>TZS 28,458,500</strong> total balance with <strong>18</strong> active transactions this month.
                        </p>

                        <!-- Quick Stats -->
                        <div class="row mb-4">
                            <div class="col-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar bg-success bg-opacity-10 rounded-circle me-2" style="width: 32px; height: 32px;">
                                        <i class="bx bx-trending-up text-success"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Monthly Growth</h6>
                                        <small class="text-success">+12.5%</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar bg-primary bg-opacity-10 rounded-circle me-2" style="width: 32px; height: 32px;">
                                        <i class="bx bx-user text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Active Members</h6>
                                        <small class="text-primary">247</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar bg-warning bg-opacity-10 rounded-circle me-2" style="width: 32px; height: 32px;">
                                        <i class="bx bx-dollar text-warning"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Total Loans</h6>
                                        <small class="text-warning">TZS 15,200,000</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar bg-info bg-opacity-10 rounded-circle me-2" style="width: 32px; height: 32px;">
                                        <i class="bx bx-pie-chart text-info"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Investments</h6>
                                        <small class="text-info">TZS 8,450,000</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('payments.initiate') }}" class="btn btn-sm btn-primary">
                                <i class="bx bx-send me-1"></i> Send Payment
                            </a>
                            <a href="{{ route('payouts.initiate') }}" class="btn btn-sm btn-outline-success">
                                <i class="bx bx-download me-1"></i> Request Payout
                            </a>
                            <a href="{{ route('billpay.create') }}" class="btn btn-sm btn-outline-info">
                                <i class="bx bx-file me-1"></i> Add Bill
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-sm-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-6">
                        <img src="{{ asset('assets/img/illustrations/man-with-laptop.png') }}" height="175" alt="View Badge User" />
                        <div class="mt-3">
                            <div class="alert alert-info d-inline-block">
                                <small><i class="bx bx-info-circle me-1"></i> Last login: 2 hours ago</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xxl-4 col-lg-12 col-md-4 order-1">
        <div class="row">
            <div class="col-lg-6 col-md-12 col-6 mb-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between mb-4">
                            <div class="avatar flex-shrink-0">
                                <div class="avatar bg-success bg-opacity-10 rounded-circle" style="width: 40px; height: 40px;">
                                    <i class="bx bx-trending-up text-success"></i>
                                </div>
                            </div>
                            <div class="dropdown">
                                <button class="btn p-0" type="button" id="cardOpt3" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="icon-base bx bx-dots-vertical-rounded text-body-secondary"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt3">
                                    <a class="dropdown-item" href="{{ route('report.statement') }}">View Statement</a>
                                    <a class="dropdown-item" href="javascript:void(0);">Export Data</a>
                                </div>
                            </div>
                        </div>
                        <p class="mb-1">Total Profit</p>
                        <h4 class="card-title mb-3">TZS 28,628,000</h4>
                        <small class="text-success fw-medium">
                            <i class="icon-base bx bx-up-arrow-alt"></i> +72.80%
                        </small>
                        <div class="mt-2">
                            <small class="text-muted">vs last month</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-12 col-6 mb-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between mb-4">
                            <div class="avatar flex-shrink-0">
                                <div class="avatar bg-primary bg-opacity-10 rounded-circle" style="width: 40px; height: 40px;">
                                    <i class="bx bx-wallet text-primary"></i>
                                </div>
                            </div>
                            <div class="dropdown">
                                <button class="btn p-0" type="button" id="cardOpt6" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="icon-base bx bx-dots-vertical-rounded text-body-secondary"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt6">
                                    <a class="dropdown-item" href="{{ route('payments.history') }}">View Details</a>
                                    <a class="dropdown-item" href="javascript:void(0);">Export Data</a>
                                </div>
                            </div>
                        </div>
                        <p class="mb-1">Total Revenue</p>
                        <h4 class="card-title mb-3">TZS 45,679,000</h4>
                        <small class="text-success fw-medium">
                            <i class="icon-base bx bx-up-arrow-alt"></i> +28.42%
                        </small>
                        <div class="mt-2">
                            <small class="text-muted">vs last month</small>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Additional Stats Cards -->
            <div class="col-lg-6 col-md-12 col-6 mb-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between mb-4">
                            <div class="avatar flex-shrink-0">
                                <div class="avatar bg-warning bg-opacity-10 rounded-circle" style="width: 40px; height: 40px;">
                                    <i class="bx bx-dollar text-warning"></i>
                                </div>
                            </div>
                            <div class="dropdown">
                                <button class="btn p-0" type="button" id="cardOpt7" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="icon-base bx bx-dots-vertical-rounded text-body-secondary"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt7">
                                    <a class="dropdown-item" href="{{ route('loans.my') }}">View Loans</a>
                                    <a class="dropdown-item" href="javascript:void(0);">Export Data</a>
                                </div>
                            </div>
                        </div>
                        <p class="mb-1">Active Loans</p>
                        <h4 class="card-title mb-3">TZS 15,200,000</h4>
                        <small class="text-warning fw-medium">
                            <i class="icon-base bx bx-minus"></i> -5.2%
                        </small>
                        <div class="mt-2">
                            <small class="text-muted">vs last month</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-12 col-6 mb-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between mb-4">
                            <div class="avatar flex-shrink-0">
                                <div class="avatar bg-info bg-opacity-10 rounded-circle" style="width: 40px; height: 40px;">
                                    <i class="bx bx-group text-info"></i>
                                </div>
                            </div>
                            <div class="dropdown">
                                <button class="btn p-0" type="button" id="cardOpt8" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="icon-base bx bx-dots-vertical-rounded text-body-secondary"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt8">
                                    <a class="dropdown-item" href="{{ route('members.all') }}">View Members</a>
                                    <a class="dropdown-item" href="javascript:void(0);">Export Data</a>
                                </div>
                            </div>
                        </div>
                        <p class="mb-1">Total Members</p>
                        <h4 class="card-title mb-3">247</h4>
                        <small class="text-success fw-medium">
                            <i class="icon-base bx bx-up-arrow-alt"></i> +12.5%
                        </small>
                        <div class="mt-2">
                            <small class="text-muted">vs last month</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Total Revenue -->
    <div class="col-12 col-xxl-8 order-2 order-md-3 order-xxl-2 mb-6 total-revenue">
        <div class="card">
            <div class="row row-bordered g-0">
                <div class="col-lg-8">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div class="card-title mb-0">
                            <h5 class="m-0 me-2">Total Revenue Overview</h5>
                            <small class="text-muted">Financial performance analysis</small>
                        </div>
                        <div class="dropdown">
                            <button class="btn p-0" type="button" id="totalRevenue" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="icon-base bx bx-dots-vertical-rounded icon-lg text-body-secondary"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="totalRevenue">
                                <a class="dropdown-item" href="{{ route('report.statement') }}">View Statement</a>
                                <a class="dropdown-item" href="javascript:void(0);">Refresh Data</a>
                                <a class="dropdown-item" href="javascript:void(0);">Export Chart</a>
                                <a class="dropdown-item" href="javascript:void(0);">Share Report</a>
                            </div>
                        </div>
                    </div>
                    <div id="totalRevenueChart" class="px-3"></div>
                    <!-- Revenue Summary -->
                    <div class="card-body border-top">
                        <div class="row text-center">
                            <div class="col-4">
                                <h5 class="text-primary mb-1">TZS 45.6M</h5>
                                <small class="text-muted">Total Revenue</small>
                            </div>
                            <div class="col-4">
                                <h5 class="text-success mb-1">TZS 28.6M</h5>
                                <small class="text-muted">Net Profit</small>
                            </div>
                            <div class="col-4">
                                <h5 class="text-warning mb-1">62.8%</h5>
                                <small class="text-muted">Profit Margin</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card-body px-xl-9 py-12 d-flex align-items-center flex-column">
                        <div class="text-center mb-6">
                            <h6 class="mb-2">Revenue Growth</h6>
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-primary active">2024</button>
                                <button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="visually-hidden">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="javascript:void(0);">2023</a></li>
                                    <li><a class="dropdown-item" href="javascript:void(0);">2022</a></li>
                                    <li><a class="dropdown-item" href="javascript:void(0);">2021</a></li>
                                </ul>
                            </div>
                        </div>

                        <div id="growthChart"></div>
                        <div class="text-center fw-medium my-6">
                            <h4 class="mb-1">62% Company Growth</h4>
                            <small class="text-success">Year-over-Year</small>
                        </div>

                        <div class="d-flex gap-11 justify-content-between w-100">
                            <div class="d-flex">
                                <div class="avatar me-2">
                                    <span class="avatar-initial rounded-2 bg-label-primary">
                                        <i class="icon-base bx bx-dollar icon-lg text-primary"></i>
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <small>2023 Revenue</small>
                                    <h6 class="mb-0">TZS 32.5M</h6>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="avatar me-2">
                                    <span class="avatar-initial rounded-2 bg-label-info">
                                        <i class="icon-base bx bx-wallet icon-lg text-info"></i>
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <small>2022 Revenue</small>
                                    <h6 class="mb-0">TZS 41.2M</h6>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Additional Metrics -->
                        <div class="mt-4 w-100">
                            <div class="d-flex justify-content-between mb-2">
                                <small>Monthly Average</small>
                                <strong>TZS 3.8M</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <small>Quarterly Growth</small>
                                <strong class="text-success">+18.5%</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <small>Projected Annual</small>
                                <strong class="text-primary">TZS 54.7M</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--/ Total Revenue -->
    <div class="col-12 col-md-8 col-lg-12 col-xxl-4 order-3 order-md-2 profile-report">
        <div class="row">
            <div class="col-6 mb-6 payments">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between mb-4">
                            <div class="avatar flex-shrink-0">
                                <div class="avatar bg-primary bg-opacity-10 rounded-circle" style="width: 40px; height: 40px;">
                                    <i class="bx bx-credit-card text-primary"></i>
                                </div>
                            </div>
                            <div class="dropdown">
                                <button class="btn p-0" type="button" id="cardOpt4" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="icon-base bx bx-dots-vertical-rounded text-body-secondary"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt4">
                                    <a class="dropdown-item" href="{{ route('payments.history') }}">View History</a>
                                    <a class="dropdown-item" href="javascript:void(0);">Export Data</a>
                                </div>
                            </div>
                        </div>
                        <p class="mb-1">Monthly Payments</p>
                        <h4 class="card-title mb-3">TZS 2,456,000</h4>
                        <small class="text-danger fw-medium">
                            <i class="icon-base bx bx-down-arrow-alt"></i> -14.82%
                        </small>
                        <div class="mt-2">
                            <small class="text-muted">vs last month</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 mb-6 transactions">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between mb-4">
                            <div class="avatar flex-shrink-0">
                                <div class="avatar bg-success bg-opacity-10 rounded-circle" style="width: 40px; height: 40px;">
                                    <i class="bx bx-exchange text-success"></i>
                                </div>
                            </div>
                            <div class="dropdown">
                                <button class="btn p-0" type="button" id="cardOpt1" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="icon-base bx bx-dots-vertical-rounded text-body-secondary"></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="cardOpt1">
                                    <a class="dropdown-item" href="{{ route('report.statement') }}">View Statement</a>
                                    <a class="dropdown-item" href="javascript:void(0);">Export Data</a>
                                </div>
                            </div>
                        </div>
                        <p class="mb-1">Total Transactions</p>
                        <h4 class="card-title mb-3">TZS 14,857,000</h4>
                        <small class="text-success fw-medium">
                            <i class="icon-base bx bx-up-arrow-alt"></i> +28.14%
                        </small>
                        <div class="mt-2">
                            <small class="text-muted">vs last month</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 mb-6 profile-report">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-sm-row flex-column gap-10 flex-wrap">
                            <div class="d-flex flex-sm-column flex-row align-items-start justify-content-between">
                                <div class="card-title mb-6">
                                    <h5 class="text-nowrap mb-1">Financial Performance</h5>
                                    <span class="badge bg-label-warning">YEAR {{ date('Y') }}</span>
                                </div>
                                <div class="mt-sm-auto">
                                    <span class="text-success text-nowrap fw-medium">
                                        <i class="icon-base bx bx-up-arrow-alt"></i> 68.2%
                                    </span>
                                    <h4 class="mb-0">TZS 84,686,000</h4>
                                </div>
                            </div>
                            <div id="profileReportChart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity Section -->
<div class="row">
    <div class="col-12 col-xxl-8 mb-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title mb-0">
                    <h5 class="mb-1">Recent Activity</h5>
                    <p class="card-subtitle">Latest transactions and system activities</p>
                </div>
                <div class="dropdown">
                    <button class="btn text-body-secondary p-0" type="button" id="recentActivity" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-base bx bx-dots-vertical-rounded icon-lg"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="recentActivity">
                        <a class="dropdown-item" href="{{ route('report.statement') }}">View All</a>
                        <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
                        <a class="dropdown-item" href="javascript:void(0);">Export</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Member</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div>
                                        <h6 class="mb-0">Dec 22, 2024</h6>
                                        <small class="text-muted">2:45 PM</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-success">Payment</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-success bg-opacity-10 rounded-circle me-2" style="width: 32px; height: 32px;">
                                            <i class="bx bx-dollar text-success"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Loan Repayment</h6>
                                            <small class="text-muted">Monthly installment</small>
                                        </div>
                                    </div>
                                </td>
                                <td>John Smith</td>
                                <td class="text-success">+TZS 450,000</td>
                                <td><span class="badge bg-success">Completed</span></td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-horizontal-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="javascript:void(0);">View Details</a>
                                            <a class="dropdown-item" href="javascript:void(0);">Download Receipt</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div>
                                        <h6 class="mb-0">Dec 22, 2024</h6>
                                        <small class="text-muted">1:30 PM</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-primary">Deposit</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-primary bg-opacity-10 rounded-circle me-2" style="width: 32px; height: 32px;">
                                            <i class="bx bx-wallet text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Savings Deposit</h6>
                                            <small class="text-muted">Regular savings</small>
                                        </div>
                                    </div>
                                </td>
                                <td>Sarah Johnson</td>
                                <td class="text-success">+TZS 200,000</td>
                                <td><span class="badge bg-success">Completed</span></td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-horizontal-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="javascript:void(0);">View Details</a>
                                            <a class="dropdown-item" href="javascript:void(0);">Download Receipt</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div>
                                        <h6 class="mb-0">Dec 22, 2024</h6>
                                        <small class="text-muted">11:15 AM</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-warning">Withdrawal</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-warning bg-opacity-10 rounded-circle me-2" style="width: 32px; height: 32px;">
                                            <i class="bx bx-money-withdraw text-warning"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Savings Withdrawal</h6>
                                            <small class="text-muted">Emergency withdrawal</small>
                                        </div>
                                    </div>
                                </td>
                                <td>Michael Brown</td>
                                <td class="text-danger">-TZS 150,000</td>
                                <td><span class="badge bg-warning">Processing</span></td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-horizontal-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="javascript:void(0);">View Details</a>
                                            <a class="dropdown-item" href="javascript:void(0);">Approve</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div>
                                        <h6 class="mb-0">Dec 22, 2024</h6>
                                        <small class="text-muted">9:45 AM</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-info">Investment</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-info bg-opacity-10 rounded-circle me-2" style="width: 32px; height: 32px;">
                                            <i class="bx bx-trending-up text-info"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">New Investment</h6>
                                            <small class="text-muted">Fixed deposit</small>
                                        </div>
                                    </div>
                                </td>
                                <td>Emily Davis</td>
                                <td class="text-success">+TZS 1,000,000</td>
                                <td><span class="badge bg-success">Completed</span></td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-horizontal-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="javascript:void(0);">View Details</a>
                                            <a class="dropdown-item" href="javascript:void(0);">Download Receipt</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div>
                                        <h6 class="mb-0">Dec 21, 2024</h6>
                                        <small class="text-muted">4:20 PM</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-secondary">Welfare</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-secondary bg-opacity-10 rounded-circle me-2" style="width: 32px; height: 32px;">
                                            <i class="bx bx-heart text-secondary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Welfare Contribution</h6>
                                            <small class="text-muted">Monthly contribution</small>
                                        </div>
                                    </div>
                                </td>
                                <td>Robert Wilson</td>
                                <td class="text-success">+TZS 50,000</td>
                                <td><span class="badge bg-success">Completed</span></td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-horizontal-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="javascript:void(0);">View Details</a>
                                            <a class="dropdown-item" href="javascript:void(0);">Download Receipt</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions Panel -->
    <div class="col-12 col-xxl-4 mb-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-3">
                    <a href="{{ route('payments.initiate') }}" class="btn btn-primary">
                        <i class="bx bx-send me-2"></i> Send Payment
                    </a>
                    <a href="{{ route('savings.deposit') }}" class="btn btn-outline-success">
                        <i class="bx bx-plus-circle me-2"></i> Make Deposit
                    </a>
                    <a href="{{ route('loans.apply') }}" class="btn btn-outline-warning">
                        <i class="bx bx-file me-2"></i> Apply for Loan
                    </a>
                    <a href="{{ route('investment.new') }}" class="btn btn-outline-info">
                        <i class="bx bx-trending-up me-2"></i> New Investment
                    </a>
                    <a href="{{ route('members.add') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-user-plus me-2"></i> Add Member
                    </a>
                </div>
            </div>
        </div>
        
        <!-- System Status -->
        <div class="card mt-6">
            <div class="card-header">
                <h5 class="card-title mb-0">System Status</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar bg-success bg-opacity-10 rounded-circle me-3" style="width: 12px; height: 12px;"></div>
                    <span class="fw-medium">Payment Gateway</span>
                    <small class="text-success ms-auto">Online</small>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar bg-success bg-opacity-10 rounded-circle me-3" style="width: 12px; height: 12px;"></div>
                    <span class="fw-medium">Database</span>
                    <small class="text-success ms-auto">Connected</small>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar bg-success bg-opacity-10 rounded-circle me-3" style="width: 12px; height: 12px;"></div>
                    <span class="fw-medium">Email Service</span>
                    <small class="text-success ms-auto">Active</small>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar bg-warning bg-opacity-10 rounded-circle me-3" style="width: 12px; height: 12px;"></div>
                    <span class="fw-medium">Backup Service</span>
                    <small class="text-warning ms-auto">Scheduled</small>
                </div>
                <div class="d-flex align-items-center">
                    <div class="avatar bg-success bg-opacity-10 rounded-circle me-3" style="width: 12px; height: 12px;"></div>
                    <span class="fw-medium">API Services</span>
                    <small class="text-success ms-auto">Operational</small>
                </div>
                
                <div class="mt-4 pt-4 border-top">
                    <div class="text-center">
                        <small class="text-muted">Last updated: 2 minutes ago</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Health Overview -->
<div class="row">
    <div class="col-12 mb-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">
                        <i class="bx bx-heartbeat me-2"></i>
                        System Health Monitor
                    </h5>
                    <small class="text-muted">Real-time system performance and health monitoring</small>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-success px-3 py-2">
                        <i class="bx bx-circle me-1"></i>
                        Health Score: 92% Good
                    </span>
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshSystemHealth()">
                        <i class="bx bx-refresh me-1"></i>Refresh
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- System Status -->
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar bg-success bg-opacity-10 rounded-circle me-3" style="width: 40px; height: 40px;">
                                        <i class="bx bx-server text-success"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">System Status</h6>
                                        <span class="badge bg-success">Online</span>
                                    </div>
                                </div>
                                <div class="small text-muted">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Uptime</span>
                                        <span class="text-success">99.9%</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Active Users</span>
                                        <span class="text-primary">247</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Server Performance -->
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar bg-warning bg-opacity-10 rounded-circle me-3" style="width: 40px; height: 40px;">
                                        <i class="bx bx-chip text-warning"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Server Performance</h6>
                                        <span class="badge bg-warning">Moderate</span>
                                    </div>
                                </div>
                                <div class="small text-muted">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>CPU</span>
                                        <span class="text-success">45%</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>RAM</span>
                                        <span class="text-warning">72%</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Load</span>
                                        <span class="text-success">2.4</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- API & Payment Status -->
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar bg-info bg-opacity-10 rounded-circle me-3" style="width: 40px; height: 40px;">
                                        <i class="bx bx-wifi text-info"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">API & Payments</h6>
                                        <span class="badge bg-success">Healthy</span>
                                    </div>
                                </div>
                                <div class="small text-muted">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>STK Push</span>
                                        <span class="text-success">Working</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>API Time</span>
                                        <span class="text-success">85ms</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Success Rate</span>
                                        <span class="text-success">99.2%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Status -->
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar bg-primary bg-opacity-10 rounded-circle me-3" style="width: 40px; height: 40px;">
                                        <i class="bx bx-shield text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Security Status</h6>
                                        <span class="badge bg-success">Secure</span>
                                    </div>
                                </div>
                                <div class="small text-muted">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>SSL</span>
                                        <span class="text-success">Valid</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Failed Logins</span>
                                        <span class="text-warning">3</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Firewall</span>
                                        <span class="text-success">Active</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Health Metrics -->
                <div class="row mt-4">
                    <!-- Transactions Health -->
                    <div class="col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="bx bx-credit-card me-2"></i>
                                    Transactions Health
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-4 mb-3">
                                        <h5 class="text-success mb-0">1,830</h5>
                                        <small class="text-muted">Successful</small>
                                    </div>
                                    <div class="col-4 mb-3">
                                        <h5 class="text-warning mb-0">8</h5>
                                        <small class="text-muted">Pending</small>
                                    </div>
                                    <div class="col-4 mb-3">
                                        <h5 class="text-danger mb-0">12</h5>
                                        <small class="text-muted">Failed</small>
                                    </div>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: 99.2%"></div>
                                </div>
                                <small class="text-muted mt-1 d-block">Success Rate: 99.2%</small>
                            </div>
                        </div>
                    </div>

                    <!-- Storage & Backup -->
                    <div class="col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="bx bx-cloud me-2"></i>
                                    Storage & Backup
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Disk Usage</span>
                                        <span class="text-warning">68%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-warning" style="width: 68%"></div>
                                    </div>
                                    <small class="text-muted">45GB / 66GB Used</small>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Last Backup</span>
                                    <span class="text-success">Success</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Auto Backup</span>
                                    <span class="badge bg-success">Enabled</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Alerts -->
                    <div class="col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="bx bx-bell me-2"></i>
                                    Recent Alerts
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="small">
                                    <div class="mb-2">
                                        <span class="text-warning">[10:45 AM]</span> - High RAM usage detected
                                    </div>
                                    <div class="mb-2">
                                        <span class="text-info">[09:30 AM]</span> - Airtel Money API slow response
                                    </div>
                                    <div class="mb-2">
                                        <span class="text-danger">[08:15 AM]</span> - Memory limit exceeded
                                    </div>
                                    <div>
                                        <span class="text-success">[07:00 AM]</span> - Daily backup completed
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="{{ route('system-settings.health') }}" class="btn btn-primary">
                                <i class="bx bx-heartbeat me-2"></i>View Full Health Report
                            </a>
                            <button class="btn btn-outline-success" onclick="runSystemCheck()">
                                <i class="bx bx-shield-check me-2"></i>Run System Check
                            </button>
                            <button class="btn btn-outline-info" onclick="exportHealthData()">
                                <i class="bx bx-download me-2"></i>Export Health Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Order Statistics -->
    <div class="col-md-6 col-lg-4 col-xl-4 order-0 mb-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between">
                <div class="card-title mb-0">
                    <h5 class="mb-1 me-2">Order Statistics</h5>
                    <p class="card-subtitle">42.82k Total Sales</p>
                </div>
                <div class="dropdown">
                    <button class="btn text-body-secondary p-0" type="button" id="orederStatistics" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-base bx bx-dots-vertical-rounded icon-lg"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="orederStatistics">
                        <a class="dropdown-item" href="javascript:void(0);">Select All</a>
                        <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
                        <a class="dropdown-item" href="javascript:void(0);">Share</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-6">
                    <div class="d-flex flex-column align-items-center gap-1">
                        <h3 class="mb-1">8,258</h3>
                        <small>Total Orders</small>
                    </div>
                    <div id="orderStatisticsChart"></div>
                </div>
                <ul class="p-0 m-0">
                    <li class="d-flex align-items-center mb-5">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="icon-base bx bx-mobile-alt"></i>
                            </span>
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2">
                                <h6 class="mb-0">Electronic</h6>
                                <small>Mobile, Earbuds, TV</small>
                            </div>
                            <div class="user-progress">
                                <h6 class="mb-0">82.5k</h6>
                            </div>
                        </div>
                    </li>
                    <li class="d-flex align-items-center mb-5">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="icon-base bx bx-closet"></i>
                            </span>
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2">
                                <h6 class="mb-0">Fashion</h6>
                                <small>T-shirt, Jeans, Shoes</small>
                            </div>
                            <div class="user-progress">
                                <h6 class="mb-0">23.8k</h6>
                            </div>
                        </div>
                    </li>
                    <li class="d-flex align-items-center mb-5">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="icon-base bx bx-home-alt"></i>
                            </span>
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2">
                                <h6 class="mb-0">Decor</h6>
                                <small>Fine Art, Dining</small>
                            </div>
                            <div class="user-progress">
                                <h6 class="mb-0">849k</h6>
                            </div>
                        </div>
                    </li>
                    <li class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-secondary">
                                <i class="icon-base bx bx-football"></i>
                            </span>
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2">
                                <h6 class="mb-0">Sports</h6>
                                <small>Football, Cricket Kit</small>
                            </div>
                            <div class="user-progress">
                                <h6 class="mb-0">99</h6>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!--/ Order Statistics -->

    <!-- Expense Overview -->
    <div class="col-md-6 col-lg-4 order-1 mb-6">
        <div class="card h-100">
            <div class="card-header nav-align-top">
                <ul class="nav nav-pills flex-wrap row-gap-2" role="tablist">
                    <li class="nav-item">
                        <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-tabs-line-card-income" aria-controls="navs-tabs-line-card-income" aria-selected="true">
                            Income
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" role="tab">Expenses</button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" role="tab">Profit</button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content p-0">
                    <div class="tab-pane fade show active" id="navs-tabs-line-card-income" role="tabpanel">
                        <div class="d-flex mb-6">
                            <div class="avatar flex-shrink-0 me-3">
                                <img src="{{ asset('assets/img/icons/unicons/wallet.png') }}" alt="User" />
                            </div>
                            <div>
                                <p class="mb-0">Total Balance</p>
                                <div class="d-flex align-items-center">
                                    <h6 class="mb-0 me-1">$459.10</h6>
                                    <small class="text-success fw-medium">
                                        <i class="icon-base bx bx-chevron-up icon-lg"></i>
                                        42.9%
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div id="incomeChart"></div>
                        <div class="d-flex align-items-center justify-content-center mt-6 gap-3">
                            <div class="flex-shrink-0">
                                <div id="expensesOfWeek"></div>
                            </div>
                            <div>
                                <h6 class="mb-0">Income this week</h6>
                                <small>$39k less than last week</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--/ Expense Overview -->

    <!-- Transactions -->
    <div class="col-md-6 col-lg-4 order-2 mb-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title m-0 me-2">Transactions</h5>
                <div class="dropdown">
                    <button class="btn text-body-secondary p-0" type="button" id="transactionID" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-base bx bx-dots-vertical-rounded icon-lg"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="transactionID">
                        <a class="dropdown-item" href="javascript:void(0);">Last 28 Days</a>
                        <a class="dropdown-item" href="javascript:void(0);">Last Month</a>
                        <a class="dropdown-item" href="javascript:void(0);">Last Year</a>
                    </div>
                </div>
            </div>
            <div class="card-body pt-4">
                <ul class="p-0 m-0">
                    <li class="d-flex align-items-center mb-6">
                        <div class="avatar flex-shrink-0 me-3">
                            <img src="{{ asset('assets/img/icons/unicons/paypal.png') }}" alt="User" class="rounded" />
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2">
                                <small class="d-block">Paypal</small>
                                <h6 class="fw-normal mb-0">Send money</h6>
                            </div>
                            <div class="user-progress d-flex align-items-center gap-2">
                                <h6 class="fw-normal mb-0">+82.6</h6>
                                <span class="text-body-secondary">USD</span>
                            </div>
                        </div>
                    </li>
                    <li class="d-flex align-items-center mb-6">
                        <div class="avatar flex-shrink-0 me-3">
                            <img src="{{ asset('assets/img/icons/unicons/wallet.png') }}" alt="User" class="rounded" />
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2">
                                <small class="d-block">Wallet</small>
                                <h6 class="fw-normal mb-0">Mac'D</h6>
                            </div>
                            <div class="user-progress d-flex align-items-center gap-2">
                                <h6 class="fw-normal mb-0">+270.69</h6>
                                <span class="text-body-secondary">USD</span>
                            </div>
                        </div>
                    </li>
                    <li class="d-flex align-items-center mb-6">
                        <div class="avatar flex-shrink-0 me-3">
                            <img src="{{ asset('assets/img/icons/unicons/chart.png') }}" alt="User" class="rounded" />
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2">
                                <small class="d-block">Transfer</small>
                                <h6 class="fw-normal mb-0">Refund</h6>
                            </div>
                            <div class="user-progress d-flex align-items-center gap-2">
                                <h6 class="fw-normal mb-0">+637.91</h6>
                                <span class="text-body-secondary">USD</span>
                            </div>
                        </div>
                    </li>
                    <li class="d-flex align-items-center mb-6">
                        <div class="avatar flex-shrink-0 me-3">
                            <img src="{{ asset('assets/img/icons/unicons/cc-primary.png') }}" alt="User" class="rounded" />
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2">
                                <small class="d-block">Credit Card</small>
                                <h6 class="fw-normal mb-0">Ordered Food</h6>
                            </div>
                            <div class="user-progress d-flex align-items-center gap-2">
                                <h6 class="fw-normal mb-0">-838.71</h6>
                                <span class="text-body-secondary">USD</span>
                            </div>
                        </div>
                    </li>
                    <li class="d-flex align-items-center mb-6">
                        <div class="avatar flex-shrink-0 me-3">
                            <img src="{{ asset('assets/img/icons/unicons/wallet.png') }}" alt="User" class="rounded" />
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2">
                                <small class="d-block">Wallet</small>
                                <h6 class="fw-normal mb-0">Starbucks</h6>
                            </div>
                            <div class="user-progress d-flex align-items-center gap-2">
                                <h6 class="fw-normal mb-0">+203.33</h6>
                                <span class="text-body-secondary">USD</span>
                            </div>
                        </div>
                    </li>
                    <li class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <img src="{{ asset('assets/img/icons/unicons/cc-warning.png') }}" alt="User" class="rounded" />
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2">
                                <small class="d-block">Mastercard</small>
                                <h6 class="fw-normal mb-0">Ordered Food</h6>
                            </div>
                            <div class="user-progress d-flex align-items-center gap-2">
                                <h6 class="fw-normal mb-0">-92.45</h6>
                                <span class="text-body-secondary">USD</span>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!--/ Transactions -->
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize date display with TZS timezone
    if (typeof moment !== 'undefined' && window.TZS_TIMEZONE) {
        const now = moment().tz(window.TZS_TIMEZONE);
        const currentDate = now.format(window.TZS_FORMAT || 'MMMM Do, YYYY');
        
        // Update all date displays
        const dateElements = document.querySelectorAll('#currentDate');
        dateElements.forEach(element => {
            if (element) {
                element.textContent = currentDate;
            }
        });
    }
    
    // Auto-refresh system health data every 30 seconds
    setInterval(refreshSystemHealth, 30000);
});

// System Health Functions
function refreshSystemHealth() {
    console.log('Refreshing system health data...');
    
    // Add loading animation to health cards
    const healthCards = document.querySelectorAll('.card-body');
    healthCards.forEach(card => {
        card.style.opacity = '0.7';
    });
    
    // Simulate API call - in real implementation, this would fetch actual data
    setTimeout(() => {
        healthCards.forEach(card => {
            card.style.opacity = '1';
        });
        
        // Update timestamp
        const timestampElements = document.querySelectorAll('.text-muted');
        timestampElements.forEach(element => {
            if (element.textContent.includes('Last updated')) {
                element.textContent = 'Last updated: Just now';
            }
        });
    }, 500);
}

function runSystemCheck() {
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="bx bx-hourglass bx-spin me-2"></i>Running Check...';
    button.disabled = true;
    
    // Simulate system check
    setTimeout(() => {
        button.innerHTML = '<i class="bx bx-check-circle me-2"></i>Check Complete';
        button.classList.remove('btn-outline-success');
        button.classList.add('btn-success');
        
        // Show success message
        showNotification('System check completed successfully!', 'success');
        
        // Reset button after 3 seconds
        setTimeout(() => {
            button.innerHTML = originalText;
            button.disabled = false;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-success');
        }, 3000);
    }, 2000);
}

function exportHealthData() {
    // Collect health data
    const healthData = {
        timestamp: new Date().toISOString(),
        health_score: 92,
        system_status: 'Online',
        uptime: '99.9%',
        active_users: 247,
        server_performance: {
            cpu: 45,
            ram: 72,
            disk: 68,
            load: 2.4,
            response_time: 120
        },
        api_status: {
            stk_push: 'Working',
            api_response_time: 85,
            success_rate: 99.2
        },
        security: {
            ssl: 'Valid',
            failed_logins: 3,
            firewall: 'Active',
            two_fa: 'Enabled'
        },
        transactions: {
            successful: 1830,
            pending: 8,
            failed: 12,
            reversed: 2
        },
        storage: {
            disk_usage: '68%',
            last_backup: 'Success',
            auto_backup: 'Enabled'
        }
    };
    
    // Create downloadable JSON file
    const dataStr = JSON.stringify(healthData, null, 2);
    const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
    
    const exportFileDefaultName = `dashboard-health-report-${new Date().toISOString().split('T')[0]}.json`;
    
    const linkElement = document.createElement('a');
    linkElement.setAttribute('href', dataUri);
    linkElement.setAttribute('download', exportFileDefaultName);
    linkElement.click();
    
    showNotification('Health data exported successfully!', 'success');
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}
    }
});
</script>
<script src="{{ asset('assets/js/dashboards-analytics.js') }}"></script>
@endpush
