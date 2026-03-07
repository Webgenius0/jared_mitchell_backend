@extends('layout.guest-layout')

@section('title', 'Verify OTP')

@section('content')
<div class="auth-page-wrapper pt-5">
    <!-- auth page bg -->
    <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
        <div class="bg-overlay"></div>

        <div class="shape">
            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink"
                viewBox="0 0 1440 120">
                <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
            </svg>
        </div>
    </div>

    <!-- auth page content -->
    <div class="auth-page-content">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="text-center mt-sm-5 mb-4 text-white-50">
                        <div>
                            <a href="/" class="d-inline-block auth-logo">
                                <img src="" alt="" height="20">
                            </a>
                        </div>
                        <p class="mt-3 fs-15 fw-medium"></p>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card mt-4 card-bg-fill">
                        <div class="card-body p-3 p-md-4">
                            <div class="text-center mt-2">
                                <h5 class="text-primary">Verify OTP</h5>
                                <p class="text-muted">Enter the OTP sent to your email</p>

                                <lord-icon src="https://cdn.lordicon.com/rhvddzym.json" trigger="loop"
                                    colors="primary:#0ab39c" class="avatar-xl"></lord-icon>
                            </div>

                            <div class="alert border-0 alert-success text-center mb-3 mx-2 py-2" role="alert">
                                An OTP has been sent to your email <strong>user@example.com</strong>
                            </div>

                            <div class="p-2">
                                <form>
                                    <!-- OTP Input Fields -->
                                    <div class="row g-2 mb-4 justify-content-center">
                                        <div class="col-2 px-1">
                                            <input type="text"
                                                class="form-control form-control-lg text-center otp-input p-2"
                                                maxlength="1" autofocus>
                                        </div>
                                        <div class="col-2 px-1">
                                            <input type="text"
                                                class="form-control form-control-lg text-center otp-input p-2"
                                                maxlength="1">
                                        </div>
                                        <div class="col-2 px-1">
                                            <input type="text"
                                                class="form-control form-control-lg text-center otp-input p-2"
                                                maxlength="1">
                                        </div>
                                        <div class="col-2 px-1">
                                            <input type="text"
                                                class="form-control form-control-lg text-center otp-input p-2"
                                                maxlength="1">
                                        </div>
                                        <div class="col-2 px-1">
                                            <input type="text"
                                                class="form-control form-control-lg text-center otp-input p-2"
                                                maxlength="1">
                                        </div>
                                        <div class="col-2 px-1">
                                            <input type="text"
                                                class="form-control form-control-lg text-center otp-input p-2"
                                                maxlength="1">
                                        </div>
                                    </div>

                                    <!-- Timer & Resend Option -->
                                    <div class="text-center mb-3">
                                        <p class="text-muted mb-1 small">OTP expires in:
                                            <span class="fw-semibold text-primary" id="timer">02:00</span>
                                        </p>
                                        <p class="text-muted mb-0 small">
                                            Didn't receive code?
                                            <button type="button"
                                                class="btn btn-link p-0 text-primary fw-semibold text-decoration-underline border-0 bg-transparent"
                                                id="resendBtn">
                                                Resend OTP
                                            </button>
                                        </p>
                                    </div>

                                    <div class="text-center mt-3">
                                        <button class="btn btn-success w-100 py-2" type="submit">Verify OTP</button>
                                    </div>

                                    <div class="text-center mt-3">
                                        <a href="{{ route('show.forgot-password') }}" class="text-muted small">
                                            <i class="ri-arrow-left-line align-bottom"></i> Back to Forgot Password
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 text-center">
                        <p class="mb-0 small">Remember your password?
                            <a href="{{ route('show.admin.login') }}"
                                class="fw-semibold text-primary text-decoration-underline"> Login here </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    @include('navigations.footer')
</div>
@endsection

@push('styles')
<style>
    .otp-input {
        font-size: 1.3rem !important;
        font-weight: bold;
        text-align: center;
        border: 2px solid #e9e9ef;
        transition: all 0.3s ease;
        height: 50px !important;
        padding: 0.375rem 0 !important;
    }

    .otp-input:focus {
        border-color: #0ab39c;
        box-shadow: 0 0 0 0.2rem rgba(10, 179, 156, 0.25);
        outline: none;
    }

    #resendBtn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    #resendBtn:not(:disabled) {
        color: #0ab39c !important;
        font-weight: 600;
        cursor: pointer;
    }

    #resendBtn {
        background: none;
        border: none;
        font-size: inherit;
        padding: 0 !important;
        text-decoration: underline;
    }

    #resendBtn:hover:not(:disabled) {
        color: #088b7a !important;
    }

    .card-bg-fill {
        background: #ffffff;
    }

    /* Responsive adjustments */
    @media (max-width: 576px) {
        .otp-input {
            font-size: 1.1rem !important;
            height: 45px !important;
        }

        .col-2 {
            padding: 0 2px !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const otpInputs = document.querySelectorAll('.otp-input');
        const timerDisplay = document.getElementById('timer');
        const resendBtn = document.getElementById('resendBtn');
        let timeLeft = 120;
        let timerInterval;

        // OTP input handling
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', function() {
                if (this.value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });

            input.addEventListener('keypress', function(e) {
                if (!/^\d$/.test(e.key)) {
                    e.preventDefault();
                }
            });

            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pasteData = e.clipboardData.getData('text').slice(0, otpInputs.length);
                if (/^\d+$/.test(pasteData)) {
                    for (let i = 0; i < pasteData.length; i++) {
                        if (otpInputs[i]) {
                            otpInputs[i].value = pasteData[i];
                            if (i < otpInputs.length - 1) {
                                otpInputs[i + 1].focus();
                            }
                        }
                    }
                }
            });
        });

        // Timer functions
        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerDisplay.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                timerDisplay.textContent = '00:00';
                resendBtn.disabled = false;
            }
        }

        function startTimer() {
            timeLeft = 120;
            updateTimer();
            timerInterval = setInterval(() => {
                timeLeft--;
                updateTimer();
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                }
            }, 1000);
        }

        // Resend OTP functionality
        resendBtn.addEventListener('click', function() {
            if (timeLeft > 0) {
                alert('Please wait for the timer to expire before resending OTP');
                return;
            }

            // Reset timer
            startTimer();
            this.disabled = true;

            // Clear all OTP inputs
            otpInputs.forEach(input => input.value = '');
            otpInputs[0].focus();

            // Show success message
            alert('New OTP has been sent to your email');
        });

        // Initial timer setup
        startTimer();
        resendBtn.disabled = true;
    });
</script>
@endpush
