@extends('emails.layouts.master')

@section('title', 'You Advanced!')

@section('content')
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding: 0 0 24px;">
                <h1 style="margin:0;font-size:26px;font-weight:700;color:#1a1a2e;letter-spacing:-0.5px;">
                    🎉 You Advanced!
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
                <p style="margin:0;font-size:16px;color:#495057;line-height:1.7;">
                    Great news! You've advanced past <strong>Round {{ $fromRound }}</strong> in
                    <strong>{{ $seasonTitle }}</strong> and are moving on to
                    <strong>Round {{ $toRound }}</strong>!
                </p>
            </td>
        </tr>

        {{-- Stats card --}}
        <tr>
            <td style="padding: 24px 0;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%"
                    style="background-color:#f8f9fa;border-radius:8px;">
                    <tr>
                        <td style="padding: 20px 24px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center" style="width:50%;padding:0 12px 0 0;">
                                        <p style="margin:0 0 4px;font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;">
                                            Rank</p>
                                        <p style="margin:0;font-size:24px;font-weight:700;color:#0ab39c;">#{{ $rank }}</p>
                                    </td>
                                    <td align="center" style="width:50%;padding:0 0 0 12px;">
                                        <p style="margin:0 0 4px;font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;">
                                            Score</p>
                                        <p style="margin:0;font-size:24px;font-weight:700;color:#1a1a2e;">{{ $score }}</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td style="padding: 0 0 24px;">
                <p style="margin:0;font-size:16px;color:#495057;line-height:1.7;">
                    Round {{ $toRound }} will be opening soon — get ready to compete!
                </p>
            </td>
        </tr>

        {{-- CTA button --}}
        <tr>
            <td align="center" style="padding: 8px 0 32px;">
                <table border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="center" style="border-radius:8px;">
                            <a href="{{ $seasonUrl }}" target="_blank"
                                style="display:inline-block;padding:14px 36px;font-size:15px;font-weight:600;color:#ffffff;background:linear-gradient(135deg,#0ab39c 0%,#099884 100%);border-radius:8px;text-decoration:none;">
                                View Competition
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td style="padding: 0 0 8px;">
                <p style="margin:0;font-size:14px;color:#868e96;line-height:1.6;">
                    Keep up the great work — we're excited to see what you bring to the next round!
                </p>
            </td>
        </tr>
    </table>
@endsection
