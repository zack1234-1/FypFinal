@extends('layout')
@section('title')
    <?= get_label('home', 'Home') ?>
@endsection
@section('content')
    <div class="container-fluid">
        <!-- Breadcrumb Section -->
        <div class="row">
            <div class="col-12">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center py-4">
                    <nav aria-label="breadcrumb" class="mb-2 mb-sm-0">
                        <ol class="breadcrumb breadcrumb-style1 mb-0 bg-transparent p-0">
                            <li class="breadcrumb-item active">
                                <a href="{{ route('superadmin.panel') }}" class="text-decoration-none fw-semibold text-primary">
                                    <i class="bx bx-home-alt me-1"></i>
                                    <?= get_label('home', 'Home') ?>
                                </a>
                            </li>
                        </ol>
                    </nav>
                    <div class="text-muted small d-flex align-items-center">
                        <i class="bx bx-time-five me-1"></i>
                        Last updated: {{ date('M d, Y H:i') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards Section -->
        <div class="row g-4 mb-5">
            <!-- Monthly Customers Card -->
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card border-0 shadow-lg h-100 overflow-hidden position-relative">
                    <div class="position-absolute top-0 end-0 w-45 h-100 bg-warning opacity-10"></div>
                    <div class="card-body p-4 position-relative">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar avatar-lg shadow-sm">
                                    <span class="avatar-initial bg-gradient-warning rounded-3 text-white">
                                        <i class="bx bxs-user fs-3"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h2 class="text-warning mb-1 fw-bold display-6">{{ $thisMonthCustomerCount }}</h2>
                                        <div class="text-muted small mb-2">
                                            {!! get_label('monthly_admin', 'Total Admins <span class="d-block d-sm-inline fw-medium">(Monthly)</span>') !!}
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-25">
                                                <i class="bx bx-trending-up me-1"></i>Active Growth
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Plans Card -->
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card border-0 shadow-lg h-100 overflow-hidden position-relative">
                    <div class="position-absolute top-0 end-0 w-25 h-100 bg-danger opacity-10"></div>
                    <div class="card-body p-4 position-relative">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar avatar-lg shadow-sm">
                                    <span class="avatar-initial bg-gradient-danger rounded-3 text-white">
                                        <i class="bx bx-task fs-3"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h2 class="text-danger mb-1 fw-bold display-6">{{ $totalPlans }}</h2>
                                        <div class="text-muted small mb-2">
                                            {{ get_label('totalPlans', 'Total Plans') }}
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-25">
                                                <i class="bx bx-package me-1"></i>Available
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Info Cards for Balance -->
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card border-0 shadow-lg h-100 overflow-hidden position-relative">
                    <div class="position-absolute top-0 end-0 w-25 h-100 bg-primary opacity-10"></div>
                    <div class="card-body p-4 position-relative">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar avatar-lg shadow-sm">
                                    <span class="avatar-initial bg-gradient-primary rounded-3 text-white">
                                        <i class="bx bxs-group fs-3"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h2 class="text-primary mb-1 fw-bold display-6">{{ $customerCounts }}</h2>
                                        <div class="text-muted small mb-2">
                                            Total Admins
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25">
                                                <i class="bx bx-check-circle me-1"></i>All Time
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row g-4 mb-5">
            <!-- Customer Chart -->
            <div class="col-12 col-xl-7">
                <div class="card border-0 shadow-lg h-100">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center">
                            <div class="card-title mb-3 mb-sm-0">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="avatar avatar-sm me-2">
                                        <span class="avatar-initial bg-primary bg-opacity-10 rounded-2">
                                            <i class="bx bx-bar-chart text-primary"></i>
                                        </span>
                                    </div>
                                    <h4 class="mb-0 fw-bold text-dark">{{ get_label('total_admins', 'Total Admins') }}</h4>
                                </div>
                                <p class="text-muted mb-3 small">
                                    {{ get_label('total_count_of_admins', 'Total Count of Admins') }}
                                </p>
                                <div class="d-flex align-items-center flex-wrap">
                                    <h1 class="text-primary mb-0 fw-bold me-3">{{ $customerCounts }}</h1>
                                    <div class="d-flex flex-column">
                                        <span class="badge bg-primary bg-opacity-15 text-primary border border-primary border-opacity-25 mb-1">
                                            <i class="bx bx-user-check me-1"></i>Active Users
                                        </span>
                                        <small class="text-muted">Updated in real-time</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <div class="chart-container bg-light bg-opacity-25 rounded-3 p-3">
                            <div id="customerChart" class="min-height-250 min-height-md-300"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Plan Sales Chart -->
            <div class="col-12 col-xl-5">
                <div class="card border-0 shadow-lg h-100">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <div class="card-title">
                            <div class="d-flex align-items-center mb-2">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial bg-success bg-opacity-10 rounded-2">
                                        <i class="bx bx-pie-chart text-success"></i>
                                    </span>
                                </div>
                                <h4 class="mb-0 fw-bold text-dark">{{ get_label('plan_sales', 'Plan Sales') }}</h4>
                            </div>
                            <p class="text-muted mb-3 small">
                                {{ get_label('get_active_subscription_per_plan', 'Active Subscriptions Per Plans') }}
                            </p>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25">
                                    <i class="bx bx-trending-up me-1"></i>Distribution Overview
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <div class="chart-container bg-light bg-opacity-25 rounded-3 p-3">
                            <div id="planSalesChart" class="min-height-250 min-height-md-300"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Variables -->
    <script>
        var customerMonthlyCountUrl = "{{ route('chart.customer_monthly_count') }}";
        var getActiveSubscriptionPerPlanUrl = "{{ route('chart.activeSubscriptionPerPlan') }}";
    </script>

    <!-- Chart Scripts -->
    <script src="{{ asset('assets/js/apexcharts.js') }}"></script>
    <script src="{{ asset('assets/js/pages/dashboard-charts.js') }}"></script>

    <style>
        .avatar-lg {
            width: 3.5rem;
            height: 3.5rem;
        }
        
        .avatar-sm {
            width: 2rem;
            height: 2rem;
        }
        
        .avatar-initial {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            font-weight: 600;
        }
        
        .bg-gradient-primary {
            background: linear-gradient(135deg, var(--bs-primary) 0%, rgba(var(--bs-primary-rgb), 0.8) 100%);
        }
        
        .bg-gradient-warning {
            background: linear-gradient(135deg, var(--bs-warning) 0%, rgba(var(--bs-warning-rgb), 0.8) 100%);
        }
        
        .bg-gradient-danger {
            background: linear-gradient(135deg, var(--bs-danger) 0%, rgba(var(--bs-danger-rgb), 0.8) 100%);
        }
        
        .bg-gradient-success {
            background: linear-gradient(135deg, var(--bs-success) 0%, rgba(var(--bs-success-rgb), 0.8) 100%);
        }
        
        .card {
            transition: all 0.3s ease;
            border-radius: 1rem;
        }
        
        .card:hover {
            transform: translateY(-2px);
        }
        
        .shadow-lg {
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
        }
        
        .chart-container {
            position: relative;
            width: 100%;
            overflow: hidden;
        }
        
        .min-height-250 {
            min-height: 250px;
        }
        
        .min-height-md-300 {
            min-height: 300px;
        }
        
        @media (max-width: 576px) {
            .display-6 {
                font-size: 2rem;
            }
            
            .card-body {
                padding: 1.25rem;
            }
            
            .min-height-250 {
                min-height: 200px;
            }
        }
        
        @media (min-width: 768px) {
            .min-height-md-300 {
                min-height: 300px;
            }
        }
        
        .breadcrumb-item a:hover {
            color: var(--bs-primary) !important;
        }
        
        .badge {
            font-weight: 500;
            letter-spacing: 0.025em;
        }
    </style>
@endsection