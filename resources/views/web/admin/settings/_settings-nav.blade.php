<div class="col-lg-3 col-xxl-2">
    <div class="card">
        <div class="card-body p-0">
            <div class="list-group list-group-flush rounded">

                {{-- App Settings --}}
                <div class="list-group-item py-2 px-3 bg-light">
                    <span class="text-muted text-uppercase fw-semibold fs-11">Application</span>
                </div>
                <a href="{{ route('admin.settings.general') }}"
                    class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2 {{ request()->routeIs('admin.settings.general') ? 'active' : '' }}">
                    <i class="ri-settings-3-line fs-15"></i> General
                </a>
                <a href="{{ route('admin.settings.contact') }}"
                    class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2 {{ request()->routeIs('admin.settings.contact') ? 'active' : '' }}">
                    <i class="ri-phone-line fs-15"></i> Contact
                </a>
                <a href="{{ route('admin.settings.logo') }}"
                    class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2 {{ request()->routeIs('admin.settings.logo') ? 'active' : '' }}">
                    <i class="ri-image-line fs-15"></i> Logo & Branding
                </a>
                <a href="{{ route('admin.settings.social') }}"
                    class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2 {{ request()->routeIs('admin.settings.social') ? 'active' : '' }}">
                    <i class="ri-share-line fs-15"></i> Social Media
                </a>
                <a href="{{ route('admin.settings.seo') }}"
                    class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2 {{ request()->routeIs('admin.settings.seo') ? 'active' : '' }}">
                    <i class="ri-seo-line fs-15"></i> SEO
                </a>

                {{-- Integrations --}}
                <div class="list-group-item py-2 px-3 bg-light">
                    <span class="text-muted text-uppercase fw-semibold fs-11">Integrations</span>
                </div>
                <a href="{{ route('admin.settings.mail') }}"
                    class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2 {{ request()->routeIs('admin.settings.mail') ? 'active' : '' }}">
                    <i class="ri-mail-settings-line fs-15"></i> Mail (SMTP)
                </a>
                <a href="{{ route('admin.settings.stripe') }}"
                    class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2 {{ request()->routeIs('admin.settings.stripe') ? 'active' : '' }}">
                    <i class="ri-bank-card-line fs-15"></i> Stripe
                </a>
                <a href="{{ route('admin.settings.reverb') }}"
                    class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2 {{ request()->routeIs('admin.settings.reverb') ? 'active' : '' }}">
                    <i class="ri-broadcast-line fs-15"></i> Reverb (WebSocket)
                </a>
                <a href="{{ route('admin.settings.aws') }}"
                    class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2 {{ request()->routeIs('admin.settings.aws') ? 'active' : '' }}">
                    <i class="ri-cloud-line fs-15"></i> AWS S3
                </a>
                <a href="{{ route('admin.settings.imap') }}"
                    class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2 {{ request()->routeIs('admin.settings.imap') ? 'active' : '' }}">
                    <i class="ri-inbox-line fs-15"></i> IMAP
                </a>

                {{-- System --}}
                <div class="list-group-item py-2 px-3 bg-light">
                    <span class="text-muted text-uppercase fw-semibold fs-11">System</span>
                </div>
                <a href="{{ route('admin.settings.maintenance') }}"
                    class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-3 py-2 {{ request()->routeIs('admin.settings.maintenance') ? 'active' : '' }}">
                    <i class="ri-tools-line fs-15"></i> Maintenance
                </a>

            </div>
        </div>
    </div>
</div>
