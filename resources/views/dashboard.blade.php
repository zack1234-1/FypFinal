@extends('layout')

@section('content')
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>

<div class="container-fluid px-3 px-md-4 py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center">
                <h2 class="fw-bold text-dark mb-2 mb-sm-0">Dashboard</h2>
                <div class="text-muted small">
                    {{ date('M d, Y') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 g-md-4 mb-4 mb-md-5">
        <!-- Workspaces -->
        @if(auth()->user()->hasRole('admin'))
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-primary text-white border-0">
                    <h6 class="mb-0 fw-semibold">Workspaces</h6>
                </div>
                <div class="card-body text-center py-4">
                    <h2 class="display-4 fw-bold text-primary mb-2">{{ $workspaces->count() }}</h2>
                    <p class="text-muted mb-0 small text-uppercase fw-medium">Total Workspaces</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Todos -->
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-success text-white border-0">
                    <h6 class="mb-0 fw-semibold">Todos</h6>
                </div>
                <div class="card-body text-center py-4">
                    <h2 class="display-4 fw-bold text-success mb-2">{{ $todos->count() }}</h2>
                    <p class="text-muted mb-3 small text-uppercase fw-medium">Total Todos</p>
                    
                    @php
                        $doneCount = $todos->where('status', 'done')->count();
                        $pendingCount = $todos->where('status', 'pending')->count();
                    @endphp
                    
                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                        <span class="badge bg-success fs-6 py-2 px-3">Done: {{ $doneCount }}</span>
                        <span class="badge bg-warning fs-6 py-2 px-3">Pending: {{ $pendingCount }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users -->
         @if(auth()->user()->hasRole('admin'))
        <div class="col-12 col-sm-6 col-lg-4 mx-auto mx-lg-0">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-info text-white border-0">
                    <h6 class="mb-0 fw-semibold">Users</h6>
                </div>
                <div class="card-body text-center py-4">
                    <h2 class="display-4 fw-bold text-info mb-2">{{ $users->count() }}</h2>
                    <p class="text-muted mb-0 small text-uppercase fw-medium">Total Users</p>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Main Content Row -->
     @if(auth()->user()->hasRole('admin'))
    <div class="row g-3 g-md-4">
        <!-- My Workspaces with Chart -->
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-dark text-white border-0">
                    <h5 class="mb-0 fw-semibold">My Workspaces</h5>
                </div>
                <div class="card-body p-0">
                    @if($workspaces->count())
                        <div class="nav nav-tabs border-bottom" id="workspaces-tab" role="tablist">
                            @foreach($workspaces as $index => $workspace)
                                <button class="nav-link {{ $index === 0 ? 'active' : '' }}" 
                                        id="workspace-{{ $workspace->id }}-tab" 
                                        data-bs-toggle="tab" 
                                        data-bs-target="#workspace-{{ $workspace->id }}" 
                                        type="button" 
                                        role="tab" 
                                        aria-controls="workspace-{{ $workspace->id }}" 
                                        aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                                    <div class="d-flex align-items-center">
                                        <div class="me-2">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center workspace-color-{{ $index % 6 }}" style="width: 24px; height: 24px;">
                                                <span class="text-white fw-bold" style="font-size: 0.75rem;">{{ strtoupper(substr($workspace->title, 0, 1)) }}</span>
                                            </div>
                                        </div>
                                        <span>{{ \Illuminate\Support\Str::limit($workspace->title, 15) }}</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                        
                        <div class="tab-content p-3" id="workspaces-tabContent">
                            @foreach($workspaces as $index => $workspace)
                                <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" 
                                    id="workspace-{{ $workspace->id }}" 
                                    role="tabpanel" 
                                    aria-labelledby="workspace-{{ $workspace->id }}-tab">
                                    <h6 class="fw-medium mb-2">{{ $workspace->title }}</h6>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">Created: {{ $workspace->created_at->format('M d, Y') }}</small>
                                        <span class="badge bg-light text-dark border">Active</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="text-muted mb-3">
                                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="opacity-25">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </div>
                            <h6 class="text-muted mb-2">No Workspaces Found</h6>
                            <p class="text-muted mb-0">You are not part of any workspaces.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if(auth()->user()->hasRole('admin'))
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-secondary text-white border-0">
                    <h5 class="mb-0 fw-semibold d-flex justify-content-between align-items-center">
                        {{ get_label('recent_activities', 'Recent Activities') }}
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 3v5h5"/>
                            <path d="M21 21v-5h-5"/>
                            <path d="M21 3a16 16 0 0 0-13.3 7.3"/>
                            <path d="M11 14.7A16 16 0 0 0 21 21"/>
                        </svg>
                    </h5>
                </div>
                
                <div class="card-body p-0">
                    <div class="overflow-auto" style="max-height: 450px;" id="recent-activity">
                        @forelse ($activities as $activity)
                            <div class="d-flex align-items-start p-3 border-bottom">
                                <div class="me-3 mt-1">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center 
                                        @switch($activity->activity)
                                        @case('created') bg-success @break
                                        @case('updated') bg-info @break
                                        @case('deleted') bg-danger @break
                                        @case('updated status') bg-warning @break
                                        @default bg-primary
                                        @endswitch" 
                                        style="width: 8px; height: 8px;">
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <h6 class="fw-semibold mb-1 fs-6">{{ $activity->message }}</h6>
                                        <small class="text-muted ms-2">{{ $activity->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="text-muted mb-0 small">{{ format_date($activity->created_at, true) }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <div class="text-muted mb-3">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="opacity-25">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                        <polyline points="14,2 14,8 20,8"/>
                                        <line x1="16" y1="13" x2="8" y2="13"/>
                                        <line x1="16" y1="17" x2="8" y2="17"/>
                                        <polyline points="10,9 9,9 8,9"/>
                                    </svg>
                                </div>
                                <h6 class="text-muted mb-0">{{ get_label('no_activities', 'No recent activities') }}</h6>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif
</div>

<style>
.workspace-color-0 { background-color: #0d6efd; }
.workspace-color-1 { background-color: #198754; }
.workspace-color-2 { background-color: #0dcaf0; }
.workspace-color-3 { background-color: #ffc107; }
.workspace-color-4 { background-color: #dc3545; }
.workspace-color-5 { background-color: #6f42c1; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($workspaces->count())
    const workspaceNames = @json($workspaces->pluck('title')->toArray());
    const workspaceColors = ['#0d6efd', '#198754', '#0dcaf0', '#ffc107', '#dc3545', '#6f42c1'];
    
    const chartData = {
        labels: workspaceNames,
        datasets: [{
            data: workspaceNames.map(() => 1),
            backgroundColor: workspaceNames.map((_, index) => workspaceColors[index % workspaceColors.length]),
            borderWidth: 0,
            hoverOffset: 4
        }]
    };

    const config = {
        type: 'doughnut',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 12
                        }
                    }
                }
            },
            cutout: '60%'
        }
    };

    const ctx = document.getElementById('workspacesChart').getContext('2d');
    new Chart(ctx, config);
    @endif
});
</script>

@endsection