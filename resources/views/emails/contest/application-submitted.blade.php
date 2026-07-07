@extends('emails.layouts.master')

@section('title', 'New Application')

@section('content')
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding: 0 0 24px;">
                <h1 style="margin:0;font-size:26px;font-weight:700;color:#1a1a2e;letter-spacing:-0.5px;">
                    New Application Received
                </h1>
            </td>
        </tr>
        <tr>
            <td style="padding: 0 0 8px;">
                <p style="margin:0;font-size:14px;color:#868e96;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;">
                    {{ $seasonTitle }}
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 0 0 24px;">
                <p style="margin:0;font-size:16px;color:#495057;line-height:1.7;">
                    A new application has been submitted by
                    <strong>{{ $applicantName }}</strong> for
                    <strong>{{ $seasonTitle }}</strong>.
                </p>
            </td>
        </tr>

        {{-- CTA button --}}
        <tr>
            <td align="center" style="padding: 8px 0 32px;">
                <table border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="center" style="border-radius:8px;">
                            <a href="{{ $adminUrl }}" target="_blank"
                                style="display:inline-block;padding:14px 36px;font-size:15px;font-weight:600;color:#ffffff;background:linear-gradient(135deg,#0ab39c 0%,#099884 100%);border-radius:8px;text-decoration:none;">
                                Review Application
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
@endsection
