<?php
use App\Models\User;
use App\Models\Subscription;
use App\Models\Admin;
use App\Models\Ticket;
use App\Models\Workspace;
use App\Models\LeaveRequest;
use Chatify\ChatifyMessenger;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
$user = getAuthenticatedUser();
$adminId = getAdminIdByUserRole();
$workspaces = Workspace::where('admin_id', $adminId)->skip(0)->take(5)->get();
$total_workspaces = Workspace::where('admin_id', $adminId)->count();
$current_workspace = Workspace::find(session()->get('workspace_id')); 
$current_workspace_title = $current_workspace->title ?? 'No project(s) found';
?>
<head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>

@if ($user->hasrole('superadmin') || $user->hasrole('manager'))
    <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme menu-container">
        <span class="app-brand-link d-flex justify-content-center align-items-center bg-secondary" style="height: 100px;"></span>
            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large d-block d-xl-none ms-auto">
                <i class="bx bx-chevron-left bx-sm align-middle"></i>
            </a>
        <div class="menu-inner-shadow"></div>
        <ul class="menu-inner py-1">
            <hr class="dropdown-divider" />
            <!-- Dashboard -->
            <li class="menu-item">
                <a href="{{ route('superadmin.panel') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-home-circle "></i>
                    <div><?= get_label('dashboard', 'Dashboard') ?></div>
                </a>
            </li>
            <li class="menu-item">
                <a href="{{ route('customers.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-user-circle "></i>
                    <div><?= get_label('adims', 'Admins') ?></div>
                </a>
            </li>
            <li class="menu-item">
                <a href="{{ route('plans.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-task "></i>
                    <div><?= get_label('plans', 'Plans') ?></div>
                </a>
            </li>
        </ul>
    </aside>
@else
    @php
        $modules = get_subscriptionModules();
    @endphp
     <aside id="layout-menu" class="layout-menu menu-vertical menu bg-light menu-container shadow-sm">
     <span class="app-brand-link d-flex justify-content-center align-items-center bg-secondary" style="height: 100px;"></span>
    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large d-block d-xl-none ms-auto">
                <i class="bx bx-chevron-left bx-sm align-middle"></i>
    </a>
    
    <div class="d-flex flex-column gap-2 px-3 mt-2">
        <div class="btn-group dropend w-100">
            <button type="button" class="btn btn-primary dropdown-toggle w-100 text-start text-truncate"
                data-bs-toggle="dropdown" aria-expanded="false">
                {{ $current_workspace_title }}
            </button>
            <ul class="dropdown-menu w-100">
                @foreach ($workspaces as $workspace)
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="{{ route('workspaces.switch', ['id' => $workspace->id]) }}">
                            <i class='bx bx-{{ $workspace->id == session()->get('workspace_id') ? 'check-square' : 'square' }} me-2'></i>
                            <span class="text-truncate">{{ $workspace->title }}</span>
                            @if($workspace->is_primary)
                                <span class="badge bg-success ms-auto">{{ get_label('primary', 'Primary') }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
                <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="{{ route('workspaces.index') }}">
                            <i class='bx bx-bar-chart-alt-2 text-success me-2'></i>
                            <span>{{ get_label('manage_projects', 'Manage projects') }}</span>
                            @if($total_workspaces > 5)
                                <span class="badge bg-primary ms-auto">+{{ $total_workspaces - 5 }}</span>
                            @endif
                        </a>
                </li>
            </ul>
        </div>
    </div>
    <ul class="menu-inner py-2">
        <hr class="dropdown-divider mx-3">
        <ul class="nav nav-pills flex-column mb-auto w-100" id="menu">
            <li class="px-3 mb-2">
                <small class="text-muted text-uppercase">Main Menu</small>
            </li>
            
            @if (auth()->user()->hasRole('admin')) 
            <li class="nav-item w-100">
                <a href="{{ route('home.index') }}" class="nav-link rounded-0 d-flex align-items-center px-3 py-2 {{ Request::is('home') ? 'active bg-primary text-white' : 'text-dark' }}">
                    <i class="bx bx-home fs-5 me-2"></i>
                    <span class="d-none d-sm-inline">Dashboard</span>
                </a>
            </li>
            @endif
            <li class="nav-item w-100">
                <a href="{{ route('notes.index') }}" class="nav-link rounded-0 d-flex align-items-center px-3 py-2 {{ request()->routeIs('notes.*') ? 'active bg-primary text-white' : 'text-dark' }}">
                    <i class="bx bx-note fs-5 me-2"></i>
                    <span class="d-none d-sm-inline">Card Table</span>
                </a>
            </li>

            <li class="nav-item w-100">
                <a href="{{ route('todos.index') }}" class="nav-link rounded-0 d-flex align-items-center px-3 py-2 {{ request()->routeIs('todos.*') ? 'active bg-primary text-white' : 'text-dark' }}">
                    <i class="bx bx-task fs-5 me-2"></i>
                    <span class="d-none d-sm-inline flex-grow-1">Todo</span>
                </a>
            </li>

            @if (auth()->user()->hasRole('admin')) 
            <li class="nav-item w-100">
                <a href="{{ route('status.index') }}" class="nav-link rounded-0 d-flex align-items-center px-3 py-2 {{ request()->routeIs('status.*') ? 'active bg-primary text-white' : 'text-dark' }}">
                    <i class="bx bx-card fs-5 me-2"></i>
                    <span class="d-none d-sm-inline">Status Cards</span>
                </a>
            </li>
            @endif

            <li class="nav-item w-100">
                <a href="{{ route('meetings.index') }}" class="nav-link rounded-0 d-flex align-items-center px-3 py-2 {{ request()->routeIs('meetings.*') ? 'active bg-primary text-white' : 'text-dark' }}">
                    <i class="bx bx-video fs-5 me-2"></i>
                    <span class="d-none d-sm-inline flex-grow-1">Meetings</span>
                </a>
            </li>

            <li class="nav-item w-100">
                <a href="{{ route('message-board.index') }}" class="nav-link rounded-0 d-flex align-items-center px-3 py-2 {{ request()->routeIs('message-board.*') ? 'active bg-primary text-white' : 'text-dark' }}">
                    <i class="bx bx-message-square-dots fs-5 me-2"></i>
                    <span class="d-none d-sm-inline flex-grow-1">Message Board</span>

                </a>
            </li>

            <li class="nav-item w-100">
                <a href="{{ route('files.index') }}" class="nav-link rounded-0 d-flex align-items-center px-3 py-2 {{ request()->routeIs('files.*') ? 'active bg-primary text-white' : 'text-dark' }}">
                    <i class="bx bx-cloud-upload fs-5 me-2"></i>
                    <span class="d-none d-sm-inline">Upload Files</span>
                </a>
            </li>

            <li class="nav-item w-100">
                <a href="{{ route('chat.index') }}" class="nav-link rounded-0 d-flex align-items-center px-3 py-2 {{ Request::routeIs('chat.index') ? 'active bg-primary text-white' : 'text-dark' }}">
                    <i class="bx bx-chat fs-5 me-2"></i>
                    <span class="d-none d-sm-inline">Chat</span>
                </a>
            </li>
            
            @if (auth()->user()->hasRole('admin')) 
            <li class="px-3 my-2">
                <hr class="dropdown-divider">
                <small class="text-muted text-uppercase">Administration</small>
            </li>

            <li class="nav-item w-100">
                <a href="{{ route('users.index') }}" class="nav-link rounded-0 d-flex align-items-center px-3 py-2 {{ request()->routeIs('users.*') ? 'active bg-primary text-white' : 'text-dark' }}">
                    <i class="bx bx-group fs-5 me-2"></i>
                    <span class="d-none d-sm-inline">Staffs</span>
                </a>
            </li>

            <li class="nav-item w-100">
                <a href="{{ route('subscription-plan.index') }}" class="nav-link rounded-0 d-flex align-items-center px-3 py-2 {{ request()->routeIs('subscription-plan.*') ? 'active bg-primary text-white' : 'text-dark' }}">
                    <i class="bx bx-credit-card fs-5 me-2"></i>
                    <span class="d-none d-sm-inline">Choose Plans</span>
                </a>
            </li>
            @endif
        </ul>
    </ul>
</aside>
@endif
