<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex">
                <!-- LOGO -->
                <div class="navbar-brand-box horizontal-logo">
                    <a href="{{ route('show.admin.dashboard') }}" class="logo logo-dark">
                        <span class="logo-sm">
                            <img src="{{ asset('admin/assets/images/default/logo-sm.png') }}" alt=""
                                height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ asset('admin/assets/images/default/logo-dark.png') }}" alt=""
                                height="17">
                        </span>
                    </a>
                    <a href="{{ route('show.admin.dashboard') }}" class="logo logo-light">
                        <span class="logo-sm">
                            <img src="{{ asset('admin/assets/images/default/logo-sm.png') }}" alt=""
                                height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ asset('admin/assets/images/default/logo-light.png') }}" alt=""
                                height="17">
                        </span>
                    </a>
                </div>

                <button type="button"
                    class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger material-shadow-none"
                    id="topnav-hamburger-icon">
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>

                {{-- Search form --}}
                <form class="app-search d-none d-md-block">
                    <div class="position-relative">
                        <input type="text" class="form-control" placeholder="Search..." autocomplete="off"
                            id="search-options" value="">
                        <span class="mdi mdi-magnify search-widget-icon"></span>
                        <span class="mdi mdi-close-circle search-widget-icon search-widget-icon-close d-none"
                            id="search-close-options"></span>
                    </div>
                    <div class="dropdown-menu dropdown-menu-lg" id="search-dropdown">
                        <div data-simplebar style="max-height: 320px;">
                            <div class="dropdown-header">
                                <h6 class="text-overflow text-muted mb-0 text-uppercase">Recent Searches</h6>
                            </div>
                            <div class="dropdown-item bg-transparent text-wrap">
                                <a href="#" class="btn btn-soft-secondary btn-sm rounded-pill">how to setup <i
                                        class="mdi mdi-magnify ms-1"></i></a>
                                <a href="#" class="btn btn-soft-secondary btn-sm rounded-pill">buttons <i
                                        class="mdi mdi-magnify ms-1"></i></a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="d-flex align-items-center">

                {{-- Mobile search --}}
                <div class="dropdown d-md-none topbar-head-dropdown header-item">
                    <button type="button"
                        class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle"
                        id="page-header-search-dropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">
                        <i class="bx bx-search fs-22"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                        aria-labelledby="page-header-search-dropdown">
                        <form class="p-3">
                            <div class="form-group m-0">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search ..."
                                        aria-label="Search">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="mdi mdi-magnify"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Fullscreen --}}
                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button"
                        class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle"
                        data-toggle="fullscreen">
                        <i class='bx bx-fullscreen fs-22'></i>
                    </button>
                </div>

                {{-- Dark / light mode --}}
                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button"
                        class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle light-dark-mode">
                        <i class='bx bx-moon fs-22'></i>
                    </button>
                </div>

                {{-- Notifications --}}
                <div class="dropdown topbar-head-dropdown ms-1 header-item d-none" id="notificationDropdown">
                    <button type="button"
                        class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle"
                        id="page-header-notifications-dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                        aria-haspopup="true" aria-expanded="false">
                        <i class='bx bx-bell fs-22'></i>
                        <span class="position-absolute topbar-badge fs-10 translate-middle badge rounded-pill bg-danger d-none">
                            3<span class="visually-hidden">unread messages</span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                        aria-labelledby="page-header-notifications-dropdown">
                        <div class="dropdown-head bg-primary bg-pattern rounded-top">
                            <div class="p-3">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="m-0 fs-16 fw-semibold text-white">Notifications</h6>
                                    </div>
                                    <div class="col-auto dropdown-tabs">
                                        <span class="badge bg-light text-body fs-13">4 New</span>
                                    </div>
                                </div>
                            </div>
                            <div class="px-2 pt-2">
                                <ul class="nav nav-tabs dropdown-tabs nav-tabs-custom" data-dropdown-tabs="true"
                                    id="notificationItemsTab" role="tablist">
                                    <li class="nav-item waves-effect waves-light">
                                        <a class="nav-link active" data-bs-toggle="tab" href="#all-noti-tab"
                                            role="tab">All (4)</a>
                                    </li>
                                    <li class="nav-item waves-effect waves-light">
                                        <a class="nav-link" data-bs-toggle="tab" href="#messages-tab"
                                            role="tab">Messages</a>
                                    </li>
                                    <li class="nav-item waves-effect waves-light">
                                        <a class="nav-link" data-bs-toggle="tab" href="#alerts-tab"
                                            role="tab">Alerts</a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="tab-content position-relative" id="notificationItemsTabContent">
                            <div class="tab-pane fade show active py-2 ps-2" id="all-noti-tab" role="tabpanel">
                                <div data-simplebar style="max-height: 300px;" class="pe-2">
                                    {{-- Notification items — populate dynamically --}}
                                    <div class="my-3 text-center view-all">
                                        <button type="button" class="btn btn-soft-success waves-effect waves-light">
                                            View All Notifications <i class="ri-arrow-right-line align-middle"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade py-2 ps-2" id="messages-tab" role="tabpanel">
                                <div data-simplebar style="max-height: 300px;" class="pe-2">
                                    <div class="my-3 text-center view-all">
                                        <button type="button" class="btn btn-soft-success waves-effect waves-light">
                                            View All Messages <i class="ri-arrow-right-line align-middle"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade p-4" id="alerts-tab" role="tabpanel"></div>
                        </div>
                    </div>
                </div>

                {{-- User profile --}}
                <div class="dropdown ms-sm-3 header-item topbar-user">
                    <button type="button" class="btn material-shadow-none" id="page-header-user-dropdown"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <img class="rounded-circle header-profile-user"
                                src="{{ auth('admin')->user()->profile?->avatar
                                    ? asset('storage/' . auth('admin')->user()->profile->avatar)
                                    : asset('admin/default/user.jpg') }}"
                                alt="{{ auth('admin')->user()->profile?->name ?? 'Admin' }}">
                            <span class="text-start ms-xl-2">
                                <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">
                                    {{ auth('admin')->user()->profile->name ?? 'Admin' }}
                                </span>
                                <span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text">
                                    {{ auth('admin')->user()?->getRoleNames()?->first() ?? 'Administrator' }}
                                </span>
                            </span>
                        </span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end">
                        <h6 class="dropdown-header">
                            Welcome {{ auth('admin')->user()->name ?? 'Admin' }}!
                        </h6>

                        <a class="dropdown-item" href="{{ route('admin.profile.index') }}">
                            <i class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i>
                            <span class="align-middle">Profile</span>
                        </a>

                        <a class="dropdown-item" href="#">
                            <i class="mdi mdi-cog-outline text-muted fs-16 align-middle me-1"></i>
                            <span class="align-middle">Settings</span>
                        </a>

                        <div class="dropdown-divider"></div>

                        {{-- ── Logout button ── --}}
                        <a class="dropdown-item" href="javascript:void(0);" id="logoutBtn">
                            <i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i>
                            <span class="align-middle">Logout</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</header>

{{-- Remove Notification Modal --}}
<div id="removeNotificationModal" class="modal fade zoomIn" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mt-2 text-center">
                    <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop"
                        colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon>
                    <div class="mt-4 pt-2 fs-15 mx-4 mx-sm-5">
                        <h4>Are you sure?</h4>
                        <p class="text-muted mx-4 mb-0">Are you sure you want to remove this notification?</p>
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                    <button type="button" class="btn w-sm btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn w-sm btn-danger" id="delete-notification">Yes, Delete
                        It!</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Logout logic ──────────────────────────────────────────────────────── --}}
<script>
    document.getElementById('logoutBtn').addEventListener('click', function() {

        Alert.confirm('You will be returned to the login screen.', {
            title: 'Log out?',
            icon: 'warning',
            type: 'danger',
            confirmText: 'Yes, log me out',
            cancelText: 'Stay',
        }).then(function(confirmed) {
            if (!confirmed) return;

            // Disable the button while request is in-flight
            document.getElementById('logoutBtn').style.pointerEvents = 'none';

            axios.post('{{ route('admin.logout') }}')
                .then(function(res) {
                    Toast.success(res.data.message || 'Logged out successfully.');
                    setTimeout(function() {
                        window.location.href = res.data.redirect;
                    }, 1000);
                })
                .catch(function() {
                    // Fallback — hard redirect to login
                    window.location.href = '{{ route('show.admin.login') }}';
                });
        });
    });
</script>
