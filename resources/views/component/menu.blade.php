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
$isWorkspaceMissing = is_null($current_workspace);
?>

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</head>

@if ($user->role === 'superadmin')
    <aside id="layout-menu" class="layout-menu menu-vertical menu bg-light d-flex flex-column h-100 position-fixed shadow-sm" style="width: 260px; z-index: 1000;">
        <div class="app-brand-link d-flex justify-content-between align-items-center bg-primary text-white p-3">
            <span class="fw-bold fs-5">SuperAdmin Panel</span>
            <button class="btn btn-sm btn-outline-light d-xl-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu">
                <i class="bx bx-menu"></i>
            </button>
        </div>

        <div class="flex-grow-1 overflow-auto">
            <ul class="nav nav-pills flex-column p-3">
                <li class="nav-item mb-2">
                    <a href="{{ route('superadmin.panel') }}" class="nav-link d-flex align-items-center">
                        <i class="bx bx-home-circle fs-5 me-2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <li class="nav-item mb-2">
                    <a href="{{ route('admins.index') }}" class="nav-link d-flex align-items-center">
                        <i class="bx bx-user-circle fs-5 me-2"></i>
                        <span>Admins</span>
                    </a>
                </li>
                
                <li class="nav-item mb-2">
                    <a href="{{ route('plans.index') }}" class="nav-link d-flex align-items-center">
                        <i class="bx bx-task fs-5 me-2"></i>
                        <span>Plans</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="p-3 border-top">
            <form action="{{ route('logout') }}" method="POST" class="w-100">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center">
                    <i class="bx bx-log-out fs-5 me-2"></i>
                    <span>Log Out</span>
                </button>
            </form>
        </div>
    </aside>
@else
    @php
        $modules = get_subscriptionModules();
    @endphp
    
    <aside id="layout-menu" class="layout-menu menu-vertical menu bg-light d-flex flex-column h-100 position-fixed shadow-sm" style="width: 260px; z-index: 1000;">
        <!-- Brand Header -->
        <div class="app-brand-link d-flex justify-content-between align-items-center bg-primary text-white p-3">
            <span class="fw-bold fs-5">Dashboard</span>
            <button class="btn btn-sm btn-outline-light d-xl-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu">
                <i class="bx bx-menu"></i>
            </button>
        </div>
        
        <!-- Current Workspace Display -->
        <div class="p-3 border-bottom bg-light">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <small class="text-muted text-uppercase fw-bold">Current Project</small>
                <i class="bx bx-briefcase text-primary"></i>
            </div>
            <div class="fw-semibold text-dark mb-3">{{ $current_workspace_title }}</div>
            <a href="{{ route('workspaces.index') }}" class="btn btn-outline-primary btn-sm w-100">
                <i class="bx bx-cog me-1"></i>
                Manage Projects
            </a>
        </div>
        
        <!-- Menu Items -->
        <div class="flex-grow-1 overflow-auto">
            <ul class="nav nav-pills flex-column p-3">
                <!-- Main Menu Section -->
                <li class="nav-item mb-2">
                    <small class="text-muted text-uppercase fw-bold">Main Menu</small>
                </li>
                
                <li class="nav-item mb-2">
                    <a href="{{ route('home.index') }}" class="nav-link d-flex align-items-center">
                        <i class="bx bx-home fs-5 me-2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                @if (in_array('notes', $modules))
                    <li class="nav-item mb-2">
                        <a href="{{ $isWorkspaceMissing ? 'javascript:void(0)' : route('cards.index') }}"
                            class="nav-link d-flex align-items-center {{ $isWorkspaceMissing ? 'disabled text-muted' : '' }}"
                            onclick="{{ $isWorkspaceMissing ? 'alert(`Please select a project first.`)' : '' }}">
                                <i class="bx bx-note fs-5 me-2"></i>
                                <span>Card Table</span>
                        </a>

                    </li>
                @endif

               @if (in_array('todos', $modules))
                    <li class="nav-item mb-2">
                        <a href="{{ $isWorkspaceMissing ? 'javascript:void(0)' : route('todos.index') }}"
                        class="nav-link d-flex align-items-center {{ $isWorkspaceMissing ? 'disabled text-muted' : '' }}"
                        onclick="{{ $isWorkspaceMissing ? 'alert(`Please select a project first.`)' : '' }}">
                            <i class="bx bx-task fs-5 me-2"></i>
                            <span>Todo</span>
                        </a>
                    </li>
                @endif

                @if (in_array('meetings', $modules))
                    <li class="nav-item mb-2">
                        <a href="{{ $isWorkspaceMissing ? 'javascript:void(0)' : route('meetings.index') }}"
                        class="nav-link d-flex align-items-center {{ $isWorkspaceMissing ? 'disabled text-muted' : '' }}"
                        onclick="{{ $isWorkspaceMissing ? 'alert(`Please select a project first.`)' : '' }}">
                            <i class="bx bx-video fs-5 me-2"></i>
                            <span>Meetings</span>
                        </a>
                    </li>
                @endif

                @if (in_array('messageBoards', $modules))
                    <li class="nav-item mb-2">
                        <a href="{{ $isWorkspaceMissing ? 'javascript:void(0)' : route('message-board.index') }}"
                        class="nav-link d-flex align-items-center {{ $isWorkspaceMissing ? 'disabled text-muted' : '' }}"
                        onclick="{{ $isWorkspaceMissing ? 'alert(`Please select a project first.`)' : '' }}">
                            <i class="bx bx-message-square-dots fs-5 me-2"></i>
                            <span>Message Board</span>
                        </a>
                    </li>
                @endif

                @if (in_array('files', $modules))
                    <li class="nav-item mb-2">
                        <a href="{{ $isWorkspaceMissing ? 'javascript:void(0)' : route('files.index') }}"
                        class="nav-link d-flex align-items-center {{ $isWorkspaceMissing ? 'disabled text-muted' : '' }}"
                        onclick="{{ $isWorkspaceMissing ? 'alert(`Please select a project first.`)' : '' }}">
                            <i class="bx bx-cloud-upload fs-5 me-2"></i>
                            <span>Upload Files</span>
                        </a>
                    </li>
                @endif

                @if (in_array('chat', $modules))
                    <li class="nav-item mb-2">
                        <a href="{{ $isWorkspaceMissing ? 'javascript:void(0)' : route('chat.index') }}"
                        class="nav-link d-flex align-items-center {{ $isWorkspaceMissing ? 'disabled text-muted' : '' }}"
                        onclick="{{ $isWorkspaceMissing ? 'alert(`Please select a project first.`)' : '' }}">
                            <i class="bx bx-chat fs-5 me-2"></i>
                            <span>Chat</span>
                        </a>
                    </li>
                @endif

                <li class="nav-item mb-2">
                    <a href="{{ $isWorkspaceMissing ? 'javascript:void(0)' : route('calendar.index') }}"
                    class="nav-link d-flex align-items-center {{ $isWorkspaceMissing ? 'disabled text-muted' : '' }}"
                    onclick="{{ $isWorkspaceMissing ? 'alert(`Please select a project first.`)' : '' }}">
                        <i class="bx bx-credit-card fs-5 me-2"></i>
                        <span>Events</span>
                    </a>
                </li>
                
                @if (auth()->user()->hasRole('admin'))
                    <!-- Administration Section -->
                    <li class="nav-item mt-4 mb-2">
                        <hr class="dropdown-divider">
                        <small class="text-muted text-uppercase fw-bold">Administration</small>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="{{ route('users.index') }}" class="nav-link d-flex align-items-center ">
                            <i class="bx bx-group fs-5 me-2"></i>
                            <span>Staffs</span>
                        </a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="{{ route('subscription-plan.index') }}" class="nav-link d-flex align-items-center ">
                            <i class="bx bx-credit-card fs-5 me-2"></i>
                            <span>Choose Plans</span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
        
        <!-- Logout Button -->
        <div class="p-3 border-top">
            <form action="{{ route('logout') }}" method="POST" class="w-100">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center">
                    <i class="bx bx-log-out fs-5 me-2"></i>
                    <span>{{ get_label('logout', 'Logout') }}</span>
                </button>
            </form>
        </div>
    </aside>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() 
{
    const mobileMenuItems = document.querySelectorAll('#mobileMenu .list-group-item');
    mobileMenuItems.forEach(item => {
        item.addEventListener('click', function() {
            const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('mobileMenu'));
            if (offcanvas) {
                offcanvas.hide();
            }
        });
    });
});
</script>