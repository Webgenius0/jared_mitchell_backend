@extends('emails.layouts.master')

@section('title', 'Application Review')

@section('content')
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding: 0 0 24px;">
                <h1 style="margin:0;font-size:26px;font-weight:700;color:#1a1a2e;letter-spacing:-0.5px;">
                    @if ($isApproved)
                        🎉 Application Approved!
                    @else
                        Application Update
                    @endif
                </h1>
            </td>
        </tr>
        <tr>
            <td style="padding: 0 0 16px;">
                <p style="margin:0;font-size:16px;color:#495057;line-height:1.7;">
                    Hey <strong>{{ $name }}</strong>,
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 0 0 16px;">
                @if ($isApproved)
                    <p style="margin:0;font-size:16px;color:#495057;line-height:1.7;">
                        Your application for <strong>{{ $seasonTitle }}</strong> has been
                        <strong style="color:#0ab39c;">approved</strong>! We're thrilled to have you
                        on board.
                    </p>
                @else
                    <p style="margin:0;font-size:16px;color:#495057;line-height:1.7;">
                        Thank you for your interest in <strong>{{ $seasonTitle }}</strong>. After careful
                        review, we're unable to approve your application at this time.
                    </p>
                @endif
            </td>
        </tr>

        @if ($adminNote)
            <tr>
                <td style="padding: 0 0 24px;">
                    <table border="0" cellpadding="0" cellspacing="0" width="100%"
                        style="background-color:#f8f9fa;border-radius:8px;">
                        <tr>
                            <td style="padding: 20px 24px;">
                                <p style="margin:0 0 8px;font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;">
                                    Admin Note</p>
                                <p style="margin:0;font-size:15px;color:#495057;line-height:1.7;">
                                    {{ $adminNote }}
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        @endif

        @if ($isApproved)
            <tr>
                <td style="padding: 0 0 16px;">
                    <p style="margin:0;font-size:16px;color:#495057;line-height:1.7;">
                        Get ready to compete! The competition will begin soon, and you'll receive
                        further instructions via email and your dashboard.
                    </p>
                </td>
            </tr>
        @endif

        {{-- CTA button --}}
        <tr>
            <td align="center" style="padding: 8px 0 32px;">
                <table border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="center" style="border-radius:8px;">
                            <a href="{{ $seasonUrl }}" target="_blank"
                                style="display:inline-block;padding:14px 36px;font-size:15px;font-weight:600;color:#ffffff;background:linear-gradient(135deg,#0ab39c 0%,#099884 100%);border-radius:8px;text-decoration:none;">
                                @if ($isApproved)
                                    View Competition
                                @else
                                    Browse Other Seasons
                                @endif
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
@endsection
