@extends('layout.guest-layout')

@section('title', '400 Forbidden')

@section('content')
    <div class="auth-page-wrapper py-5 d-flex justify-content-center align-items-center min-vh-100">
        <div class="auth-page-content overflow-hidden p-0">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-xl-7 col-lg-8">
                        <div class="text-center">

                            <img src="{{ asset('admin/assets/images/errors/403.gif') }}" alt="403 error img"
                                class="img-fluid">

                            <div class="mt-3">
                                <h3 class="text-uppercase">403 - Forbidden</h3>
                                <p class="text-muted mb-4">
                                    Sorry, you do not have permission to access this page.
                                </p>

                                <a href="/" class="btn btn-success">
                                    <i class="mdi mdi-home me-1"></i>
                                    Back to Home
                                </a>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
