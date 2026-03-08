
<div class="col-lg-3 col-xxl-2">
    <div class="card">
        <div class="card-body p-0">
            <ul class="list-group list-group-flush rounded">

                <a href="{{ route('admin.settings.general') }}"
                   class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-3 px-3
                          {{ request()->routeIs('admin.settings.general') ? 'active' : '' }}">
                    <i class="ri-settings-3-line"></i> General
                </a>

                <a href="{{ route('admin.settings.contact') }}"
                   class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-3 px-3
                          {{ request()->routeIs('admin.settings.contact') ? 'active' : '' }}">
                    <i class="ri-contacts-line"></i> Contact
                </a>

                <a href="{{ route('admin.settings.logo') }}"
                   class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-3 px-3
                          {{ request()->routeIs('admin.settings.logo') ? 'active' : '' }}">
                    <i class="ri-image-line"></i> Logo & Branding
                </a>

                <a href="{{ route('admin.settings.social') }}"
                   class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-3 px-3
                          {{ request()->routeIs('admin.settings.social') ? 'active' : '' }}">
                    <i class="ri-share-line"></i> Social Media
                </a>

                <a href="{{ route('admin.settings.seo') }}"
                   class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-3 px-3
                          {{ request()->routeIs('admin.settings.seo') ? 'active' : '' }}">
                    <i class="ri-search-eye-line"></i> Scripts
                </a>

                <a href="{{ route('admin.settings.mail') }}"
                   class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-3 px-3
                          {{ request()->routeIs('admin.settings.mail') ? 'active' : '' }}">
                    <i class="ri-mail-settings-line"></i> Mail
                </a>

                <a href="{{ route('admin.settings.maintenance') }}"
                   class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-3 px-3
                          {{ request()->routeIs('admin.settings.maintenance') ? 'active' : '' }}">
                    <i class="ri-tools-line"></i> Maintenance
                </a>

            </ul>
        </div>
    </div>
</div>
