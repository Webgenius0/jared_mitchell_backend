@extends('emails.layouts.master')

@section('title', 'Round Results')

@section('content')
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding: 0 0 24px;">
                <h1 style="margin:0;font-size:26px;font-weight:700;color:#1a1a2e;letter-spacing:-0.5px;">
                    Round {{ $roundNumber }} Results
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
                    Thank you for competing in <strong>Round {{ $roundNumber }}</strong> of
                    <strong>{{ $seasonTitle }}</strong>. Unfortunately, you were not selected to advance
                    to the next round.
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
                                            Final Rank</p>
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
            <td style="padding: 16px 0 24px;">
                <p style="margin:0;font-size:16px;color:#495057;line-height:1.7;">
                    Don't be discouraged — we'd love to see you apply for future seasons. Keep an eye on
                    your dashboard for upcoming opportunities!
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
                                View Results
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
@endsection
