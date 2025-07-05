<!-- Navbar -->
@php
    use App\Models\Language;
    $authenticatedUser = getAuthenticatedUser();
    $current_language = Language::where('code', app()->getLocale())->get(['name', 'code']);
    $default_language = getAuthenticatedUser()->lang;
    $unreadNotificationsCount = $authenticatedUser->notifications->where('pivot.read_at', null)->count();
    $unreadNotifications = $authenticatedUser
        ->notifications()
        ->wherePivot('read_at', null)
        ->getQuery()
        ->orderBy('id', 'desc')
        ->take(3)
        ->get();
    $unreadAnnouncementsCount = $authenticatedUser->announcements
        ? $authenticatedUser->announcements->where('pivot.read_at', null)->count()
        : null;
    if ($authenticatedUser->hasRole('client')) {
        $unreadAnnouncements = null;
    } else {
        $unreadAnnouncements = $authenticatedUser
            ->announcements()
            ->wherePivot('read_at', null)
            ->getQuery()
            ->orderBy('announcements.id', 'desc')
            ->take(3)
            ->get();
    }

@endphp
{{-- @dd($unreadNotifications) --}}
@authBoth
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<div id="section-not-to-print">
    <nav class="layout-navbar container-fluid navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
        id="layout-navbar">
        <div class="layout-menu-toggle navbar-nav align-items-xl-center me-xl-0 d-xl-none me-3">
            <a class="nav-item nav-link me-xl-4 px-0" href="javascript:void(0)">
                <i class="bx bx-menu bx-sm"></i>
            </a>
        </div>
        <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
            <ul class="navbar-nav align-items-center ms-auto flex-row">
                <a href="{{ route('calendar.index') }}">
                  <i class="fas fa-calendar-alt text-secondary fa-lg"></i>
                </a>
                <li class="nav-item navbar-dropdown dropdown-user dropdown mx-2 mt-3">
                    <p class="nav-item">
                        <span class="nav-mobile-hidden">
                            {{ get_label('hi', 'Hi') }} 👋
                        </span>
                        <span class="nav-mobile-hidden">{{ getAuthenticatedUser()->first_name }}</span>
                    </p>
                </li>
                <!-- User -->
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                        data-bs-toggle="dropdown">
                        <div class="avatar avatar-online">
                            <img src="{{ getAuthenticatedUser()->photo ? asset('storage/' . getAuthenticatedUser()->photo) : asset('storage/photos/no-image.jpg') }}"
                                alt class="w-px-40 rounded-circle" />
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="#">
                                <div class="d-flex">
                                    <div class="me-3 flex-shrink-0">
                                        <div class="avatar avatar-online">
                                            <img src="{{ getAuthenticatedUser()->photo ? asset('storage/' . getAuthenticatedUser()->photo) : asset('storage/photos/no-image.jpg') }}"
                                                alt class="w-px-40 rounded-circle" />
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <span class="fw-semibold d-block">{{ getAuthenticatedUser()->first_name }}
                                            {{ getAuthenticatedUser()->last_name }}</span>
                                        <small class="text-muted text-capitalize">
                                            {{ getAuthenticatedUser()->getRoleNames()->first() }}
                                        </small>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <div class="dropdown-divider"></div>
                        </li>
                        <li>
                            <a class="dropdown-item"
                                href="{{ Request::segment(1) === 'superadmin'
                                    ? route('profile_superadmin.show', ['user' => getAuthenticatedUser()->id])
                                    : route('profile.show', ['user' => getAuthenticatedUser()->id]) }}">
                                <i class="bx bx-user me-2"></i>
                                <span class="align-middle">
                                    {{ get_label('my_profile', 'My Profile') }}
                                </span>
                            </a>
                        </li>
                        @if (!(Request::segment(1) == 'superadmin'))
                            <li>
                                <a class="dropdown-item" href="{{ route('preferences.index') }}">
                                    <i class='bx bx-cog me-2'></i>
                                    <span class="align-middle">
                                        {{ get_label('preferences', 'Preferences') }}
                                    </span>
                                </a>
                            </li>
                        @endif
                        <li>
                            <a class="dropdown-item" href="{{ route('clear.cache') }}"
                                onclick="event.preventDefault(); document.getElementById('clear-cache-form').submit();">
                                <i class='bx bx-refresh me-2'></i>
                                <span class="align-middle">
                                    {{ get_label('clear_system_cache', 'Clear System Cache') }}
                                </span>
                            </a>
                            <form id="clear-cache-form" action="{{ route('clear.cache') }}" method="POST"
                                style="display: none;">
                                @csrf
                            </form>
                        </li>
                        <li>
                            <div class="dropdown-divider"></div>
                        </li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" class="dropdown-item">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i
                                        class="bx bx-log-out-circle"></i>
                                    {{ get_label('logout', 'Logout') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
</div>
@endauth
<script>
    var labelSearch = '{{ get_label('search', 'Search') }}';
</script>
<script src="{{ asset('assets/js/pages/navbar.js') }}"></script>
<!-- / Navbar -->
