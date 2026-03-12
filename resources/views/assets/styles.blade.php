<!-- jsvectormap css -->
<link href="{{ asset('admin/assets/libs/jsvectormap/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
<!-- Swiper slider css -->
<link href="{{ asset('admin/assets/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />
<!-- Bootstrap Css -->
<link href="{{ asset('admin/assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
<!-- Icons Css -->
<link href="{{ asset('admin/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
<!-- App Css -->
<link href="{{ asset('admin/assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
<!-- Custom Css -->
<link href="{{ asset('admin/assets/css/custom.min.css') }}" rel="stylesheet" type="text/css" />

{{-- DataTables --}}
<link href="{{ asset('admin/assets/libs/DataTables/datatables.min.css') }}" rel="stylesheet" type="text/css" />

<!-- NProgress — top progress bar -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css" />

<!-- Toastify -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css" />

<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" />

<link href="{{ asset('admin/assets/libs/quill/quill.core.css') }} " rel="stylesheet" type="text/css" />
<link href="{{ asset('admin/assets/libs/quill/quill.bubble.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('admin/assets/libs/quill/quill.snow.css') }}" rel="stylesheet" type="text/css" />

<style>
    /* ── NProgress bar ─────────────────────────────────────────────────── */
    #nprogress .bar {
        background: #0ab39c !important;
        height: 3px !important;
    }

    #nprogress .peg {
        box-shadow: 0 0 10px #0ab39c, 0 0 5px #0ab39c !important;
    }

    /* ── Toastify ──────────────────────────────────────────────────────── */
    .toastify {
        font-family: inherit;
        font-size: 0.875rem;
        font-weight: 500;
        border-radius: 6px;
        padding: 12px 20px;
        min-width: 280px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, .15);
    }

    .toastify.toast-success {
        background: linear-gradient(135deg, #0ab39c, #099884) !important;
    }

    .toastify.toast-error {
        background: linear-gradient(135deg, #f06548, #d9513a) !important;
    }

    .toastify.toast-warning {
        background: linear-gradient(135deg, #f7b84b, #e0a33a) !important;
    }

    .toastify.toast-info {
        background: linear-gradient(135deg, #299cdb, #1d86c0) !important;
    }

    /* ── SweetAlert2 — Velzon theme override ───────────────────────────── */
    .swal2-popup {
        font-family: inherit !important;
        border-radius: 10px !important;
        padding: 2rem !important;
    }

    .swal2-title {
        font-size: 1.25rem !important;
        font-weight: 600 !important;
        color: var(--vz-heading-color, #495057) !important;
    }

    .swal2-html-container {
        font-size: 0.9rem !important;
        color: var(--vz-body-color, #878a99) !important;
    }

    /* Confirm — teal (default) */
    .swal2-confirm.swal-btn-confirm {
        background-color: #0ab39c !important;
        border: none !important;
        border-radius: 5px !important;
        padding: 8px 22px !important;
        font-weight: 500 !important;
        box-shadow: none !important;
    }

    .swal2-confirm.swal-btn-confirm:focus {
        box-shadow: 0 0 0 3px rgba(10, 179, 156, .3) !important;
    }

    /* Confirm — danger */
    .swal2-confirm.swal-btn-danger {
        background-color: #f06548 !important;
        border: none !important;
        border-radius: 5px !important;
        padding: 8px 22px !important;
        font-weight: 500 !important;
        box-shadow: none !important;
    }

    .swal2-confirm.swal-btn-danger:focus {
        box-shadow: 0 0 0 3px rgba(240, 101, 72, .3) !important;
    }

    /* Confirm — warning */
    .swal2-confirm.swal-btn-warning {
        background-color: #f7b84b !important;
        border: none !important;
        border-radius: 5px !important;
        padding: 8px 22px !important;
        font-weight: 500 !important;
        box-shadow: none !important;
    }

    /* Cancel */
    .swal2-cancel {
        background-color: #e9ebec !important;
        color: #495057 !important;
        border: none !important;
        border-radius: 5px !important;
        padding: 8px 22px !important;
        font-weight: 500 !important;
        box-shadow: none !important;
    }

    .swal2-cancel:hover {
        background-color: #d4d8da !important;
    }

    /* Icons */
    .swal2-icon.swal2-warning {
        border-color: #f7b84b !important;
        color: #f7b84b !important;
    }

    .swal2-icon.swal2-error {
        border-color: #f06548 !important;
    }

    .swal2-icon.swal2-success {
        border-color: #0ab39c !important;
        color: #0ab39c !important;
    }

    .swal2-icon.swal2-success [class^='swal2-success-line'] {
        background: #0ab39c !important;
    }

    .swal2-icon.swal2-success .swal2-success-ring {
        border-color: rgba(10, 179, 156, .3) !important;
    }

    /* Dark mode */
    [data-bs-theme="dark"] .swal2-popup {
        background: #2a2f3a !important;
    }

    [data-bs-theme="dark"] .swal2-title {
        color: #ced4da !important;
    }

    [data-bs-theme="dark"] .swal2-html-container {
        color: #878a99 !important;
    }

    [data-bs-theme="dark"] .swal2-cancel {
        background-color: #3d4350 !important;
        color: #ced4da !important;
    }
</style>
