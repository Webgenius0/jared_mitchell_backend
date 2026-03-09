<div class="app-menu navbar-menu">
    <div class="navbar-brand-box">
        <a href="{{ route('show.admin.dashboard') }}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ asset('admin/assets/images/default/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('admin/assets/images/default/logo-dark.png') }}" alt="" height="17">
            </span>
        </a>
        <a href="{{ route('show.admin.dashboard') }}" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ asset('admin/assets/images/default/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('admin/assets/images/default/logo.png') }}" alt="" height="50">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    {{-- Sidebar user dropdown --}}
    <div class="dropdown sidebar-user m-1 rounded">
        <button type="button" class="btn material-shadow-none" id="sidebar-user-dropdown" data-bs-toggle="dropdown"
            aria-haspopup="true" aria-expanded="false">
            <span class="d-flex align-items-center gap-2">
                <img class="rounded header-profile-user"
                    src="{{ auth('admin')->user()?->profile?->avatar_url ?? asset('admin/assets/images/default/user.jpg') }}"
                    alt="Avatar">
                <span class="text-start">
                    <span class="d-block fw-medium sidebar-user-name-text">
                        {{ auth('admin')->user()?->profile?->name ?? auth('admin')->user()?->email }}
                    </span>
                    <span class="d-block fs-14 sidebar-user-name-sub-text">
                        <i class="ri ri-circle-fill fs-10 text-success align-baseline"></i>
                        <span class="align-middle">Online</span>
                    </span>
                </span>
            </span>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            <h6 class="dropdown-header">
                Welcome {{ auth('admin')->user()?->profile?->name ?? 'Admin' }}!
            </h6>
            <a class="dropdown-item" href="{{ route('admin.profile.index') }}">
                <i class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i>
                <span class="align-middle">Profile</span>
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="javascript:void(0);" id="sidebarLogoutBtn">
                <i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i>
                <span class="align-middle">Logout</span>
            </a>
        </div>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu"></div>

            <ul class="navbar-nav" id="navbar-nav">

                <li class="menu-title"><span data-key="t-menu">Menu</span></li>

                {{-- Dashboard --}}
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('show.admin.dashboard') ? 'active' : '' }}"
                        href="{{ route('show.admin.dashboard') }}">
                        <i class="ri-dashboard-2-line"></i>
                        <span>Dashboards</span>
                    </a>
                </li>

                {{-- Contact --}}
                @php
                    // System Settings dropdown is open when any child route is active
                    $contactOpen = request()->routeIs('admin.chat.index', 'admin.chat.*');
                @endphp

                <li class="nav-item">
                    <a class="nav-link menu-link {{ $contactOpen ? 'active' : '' }}" href="#sidebarMessaging"
                        data-bs-toggle="collapse" role="button" aria-expanded="{{ $contactOpen ? 'true' : 'false' }}"
                        aria-controls="sidebarMessaging">
                        <i class="ri-kakao-talk-line"></i>
                        <span>Messaging</span>
                    </a>

                    <div class="collapse menu-dropdown {{ $contactOpen ? 'show' : '' }}" id="sidebarMessaging">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('admin.profile.index') }}"
                                    class="nav-link {{ request()->routeIs('admin.profile.index') ? 'active' : '' }}">
                                    <i class="ri-wechat-line"></i> Chat
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('admin.mail.index') }}"
                                    class="nav-link {{ request()->routeIs('admin.mail.index') ? 'active' : '' }}">
                                    <i class="ri-wechat-line"></i> Mail
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                {{-- ── Settings group ───────────────────────────────────── --}}
                <li class="menu-title">
                    <i class="ri-more-fill"></i>
                    <span>Settings</span>
                </li>

                @php
                    // System Settings dropdown is open when any child route is active
                    $systemSettingsOpen = request()->routeIs('admin.profile.index', 'admin.settings.*');
                @endphp

                <li class="nav-item">
                    <a class="nav-link menu-link {{ $systemSettingsOpen ? 'active' : '' }}"
                        href="#sidebarSystemSettings" data-bs-toggle="collapse" role="button"
                        aria-expanded="{{ $systemSettingsOpen ? 'true' : 'false' }}"
                        aria-controls="sidebarSystemSettings">
                        <i class="ri-settings-5-line"></i>
                        <span>System Settings</span>
                    </a>

                    <div class="collapse menu-dropdown {{ $systemSettingsOpen ? 'show' : '' }}"
                        id="sidebarSystemSettings">
                        <ul class="nav nav-sm flex-column">

                            <li class="nav-item">
                                <a href="{{ route('admin.profile.index') }}"
                                    class="nav-link {{ request()->routeIs('admin.profile.index') ? 'active' : '' }}">
                                    <i class="ri-user-settings-line"></i> Profile Settings
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('admin.settings.general') }}"
                                    class="nav-link {{ request()->routeIs('admin.settings.general') ? 'active' : '' }}">
                                    <i class="ri-settings-3-line"></i> General Settings
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('admin.settings.contact') }}"
                                    class="nav-link {{ request()->routeIs('admin.settings.contact') ? 'active' : '' }}">
                                    <i class="ri-contacts-line"></i> Contact Settings
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('admin.settings.logo') }}"
                                    class="nav-link {{ request()->routeIs('admin.settings.logo') ? 'active' : '' }}">
                                    <i class="ri-image-line"></i> Logo Settings
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('admin.settings.social') }}"
                                    class="nav-link {{ request()->routeIs('admin.settings.social') ? 'active' : '' }}">
                                    <i class="ri-share-line"></i> Social Settings
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('admin.settings.seo') }}"
                                    class="nav-link {{ request()->routeIs('admin.settings.seo') ? 'active' : '' }}">
                                    <i class="ri-search-eye-line"></i> SEO Settings
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('admin.settings.mail') }}"
                                    class="nav-link {{ request()->routeIs('admin.settings.mail') ? 'active' : '' }}">
                                    <i class="ri-mail-settings-line"></i> Mail Settings
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('admin.settings.stripe') }}"
                                    class="nav-link {{ request()->routeIs('admin.settings.stripe') ? 'active' : '' }}">
                                    <i class="ri-bank-card-line"></i> Stripe Settings
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('admin.settings.reverb') }}"
                                    class="nav-link {{ request()->routeIs('admin.settings.reverb') ? 'active' : '' }}">
                                    <i class="ri-broadcast-line"></i> Reverb Settings
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('admin.settings.aws') }}"
                                    class="nav-link {{ request()->routeIs('admin.settings.aws') ? 'active' : '' }}">
                                    <i class="ri-cloud-line"></i> AWS Settings
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('admin.settings.imap') }}"
                                    class="nav-link {{ request()->routeIs('admin.settings.imap') ? 'active' : '' }}">
                                    <i class="ri-mail-send-line"></i> IMAP Settings
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('admin.settings.maintenance') }}"
                                    class="nav-link {{ request()->routeIs('admin.settings.maintenance') ? 'active' : '' }}">
                                    <i class="ri-tools-line"></i> Maintenance
                                </a>
                            </li>

                        </ul>
                    </div>
                </li>
            </ul>
        </div>
    </div>

    <div class="sidebar-background"></div>
</div>

{{-- Sidebar logout (mirrors header logout) --}}
<script>
    document.getElementById('sidebarLogoutBtn').addEventListener('click', function() {
        Alert.confirm('You will be returned to the login screen.', {
            title: 'Log out?',
            icon: 'warning',
            type: 'danger',
            confirmText: 'Yes, log me out',
            cancelText: 'Stay',
        }).then(function(confirmed) {
            if (!confirmed) return;

            axios.post('{{ route('admin.logout') }}')
                .then(function(res) {
                    Toast.success(res.data.message || 'Logged out successfully.');
                    setTimeout(() => window.location.href = res.data.redirect, 1000);
                })
                .catch(function() {
                    window.location.href = '{{ route('show.admin.login') }}';
                });
        });
    });
</script>
