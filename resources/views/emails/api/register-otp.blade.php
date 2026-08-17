@extends('emails.layouts.master', [
    'subject' => $header_message ?? 'Verify Your Email Address',
    'preheader' => 'Your OTP is ' . $otp . '. It expires in 60 minutes.',
    'headerTitle' => 'Email Verification',
])

@section('content')
    {{-- Greeting --}}
    <p style="margin:0 0 8px;font-size:15px;font-weight:600;color:#343a40;line-height:1.5;">
        Hello, {{ $user->profile->name ?? 'User' }}!
    </p>

    <p style="margin:0 0 28px;font-size:14px;color:#6c757d;line-height:1.7;">
        Thank you for registering with <strong style="color:#343a40;">{{ config('app.name') }}</strong>.
        Please use the OTP code below to verify your email address. It is valid for
        <strong style="color:#343a40;">60 minutes</strong>.
    </p>

    {{-- OTP Block --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 28px;">
        <tr>
            <td align="center">
                <div
                    style="display:inline-block;background-color:#f0fdf9;border:2px dashed #0ab39c;border-radius:8px;padding:20px 48px;text-align:center;">
                    <p
                        style="margin:0 0 4px;font-size:11px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:#0ab39c;">
                        Your Verification Code
                    </p>
                    <p
                        style="margin:0;font-size:38px;font-weight:700;letter-spacing:10px;color:#0ab39c;font-family:'Courier New',Courier,monospace;line-height:1.2;">
                        {{ $otp }}
                    </p>
                </div>
            </td>
        </tr>
    </table>

    {{-- Security Warning --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 28px;">
        <tr>
            <td style="background-color:#fff8ec;border-left:4px solid #f7b84b;border-radius:0 6px 6px 0;padding:14px 18px;">
                <p style="margin:0;font-size:13px;color:#856404;line-height:1.6;">
                    <strong>⚠ Security notice:</strong>
                    Never share this OTP with anyone. If you did not request this registration, please ignore this email.
                </p>
            </td>
        </tr>
    </table>
@endsection
