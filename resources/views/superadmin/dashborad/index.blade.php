@extends('layout')
@section('title')
    <?= get_label('home', 'Home') ?>
@endsection
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center py-4">
                <nav aria-label="breadcrumb" class="mb-2 mb-sm-0">
                    <ol class="breadcrumb bg-transparent p-0 mb-0">
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

    <div class="row g-4 mb-5">
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div class="rounded-circle p-3 bg-warning text-white">
                                <i class="bx bxs-user fs-3"></i>
                            </div>
                        </div>
                        <div>
                            <h2 class="text-warning mb-1 fw-bold">{{ $thisMonthAdminCount }}</h2>
                            <div class="text-muted small">{!! get_label('monthly_admin', 'Total Admins <span class="d-block d-sm-inline fw-medium">(Monthly)</span>') !!}</div>
                            <span class="badge bg-warning text-dark">
                                <i class="bx bx-trending-up me-1"></i>Active Growth
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div class="rounded-circle p-3 bg-danger text-white">
                                <i class="bx bx-task fs-3"></i>
                            </div>
                        </div>
                        <div>
                            <h2 class="text-danger mb-1 fw-bold">{{ $totalPlans }}</h2>
                            <div class="text-muted small">{{ get_label('totalPlans', 'Total Plans') }}</div>
                            <span class="badge bg-danger">
                                <i class="bx bx-package me-1"></i>Available
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div class="rounded-circle p-3 bg-primary text-white">
                                <i class="bx bxs-group fs-3"></i>
                            </div>
                        </div>
                        <div>
                            <h2 class="text-primary mb-1 fw-bold">{{ $adminCounts }}</h2>
                            <div class="text-muted small">Total Admins</div>
                            <span class="badge bg-primary">
                                <i class="bx bx-check-circle me-1"></i>All Time
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-12 col-xl-7">
            <div class="card shadow h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="d-flex align-items-center">
                        <div class="me-2">
                            <div class="rounded-circle p-2 bg-primary text-white">
                                <i class="bx bx-bar-chart"></i>
                            </div>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold text-dark">{{ get_label('total_admins', 'Total Admins') }}</h4>
                            <p class="text-muted small mb-0">{{ get_label('total_count_of_admins', 'Total Count of Admins') }}</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="bg-light p-3 rounded">
                        <div id="customerChart" style="min-height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="card shadow h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="d-flex align-items-center">
                        <div class="me-2">
                            <div class="rounded-circle p-2 bg-success text-white">
                                <i class="bx bx-pie-chart"></i>
                            </div>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold text-dark">{{ get_label('plans', 'Plans') }}</h4>
                            <p class="text-muted small mb-0">{{ get_label('get_active_subscription_per_plan', 'Active Subscriptions Per Plans') }}</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="bg-light p-3 rounded">
                        <div id="planSalesChart" style="min-height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var adminMonthlyCountUrl = "{{ route('chart.admin_monthly_count') }}";
    var getActiveSubscriptionPerPlanUrl = "{{ route('chart.activeSubscriptionPerPlan') }}";
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="{{ asset('assets/js/pages/dashboard-charts.js') }}"></script>
@endsection
