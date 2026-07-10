@extends('emails.layouts.master', [
    'subject' => $status === 'confirmed'
        ? '✅ Registration Confirmed — ' . $event?->title
        : '❌ Registration Cancelled — ' . $event?->title,
    'preheader' => $status === 'confirmed'
        ? 'Your registration for ' . $event?->title . ' has been confirmed.'
        : 'Your registration for ' . $event?->title . ' has been cancelled.',
    'headerTitle' => $status === 'confirmed' ? 'Registration Confirmed' : 'Registration Cancelled',
])

@section('content')
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding: 0 0 24px;">
                <h1 style="margin:0;font-size:26px;font-weight:700;color:#1a1a2e;letter-spacing:-0.5px;">
                    @if ($status === 'confirmed')
                        🎉 Registration Confirmed!
                    @else
                        Registration Cancelled
                    @endif
                </h1>
            </td>
        </tr>
        <tr>
            <td style="padding: 0 0 16px;">
                <p style="margin:0;font-size:16px;color:#495057;line-height:1.7;">
                    Dear <strong>{{ $customerName }}</strong>,
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 0 0 16px;">
                @if ($status === 'confirmed')
                    <p style="margin:0;font-size:16px;color:#495057;line-height:1.7;">
                        Great news! Your registration for <strong>{{ $event?->title }}</strong>
                        has been <strong style="color:#0ab39c;">confirmed</strong>. We look forward to seeing you there!
                    </p>
                @else
                    <p style="margin:0;font-size:16px;color:#495057;line-height:1.7;">
                        Your registration for <strong>{{ $event?->title }}</strong>
                        has been <strong style="color:#ef4444;">cancelled</strong>.
                    </p>
                @endif
            </td>
        </tr>

        {{-- Event Details Card --}}
        <tr>
            <td style="padding: 0 0 24px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%"
                    style="background-color:#f8f9fa;border-radius:8px;">
                    <tr>
                        <td style="padding: 20px 24px;">
                            <p style="margin:0 0 12px;font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;">
                                Event Details</p>
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td style="padding:4px 0;font-size:14px;color:#6c757d;width:90px;">Event</td>
                                    <td style="padding:4px 0;font-size:14px;color:#343a40;font-weight:600;">
                                        {{ $event?->title ?? 'N/A' }}</td>
                                </tr>
                                @if ($event?->starts_at)
                                <tr>
                                    <td style="padding:4px 0;font-size:14px;color:#6c757d;">Date</td>
                                    <td style="padding:4px 0;font-size:14px;color:#343a40;">
                                        {{ $event->starts_at->format('F d, Y h:i A') }}</td>
                                </tr>
                                @endif
                                @if ($event?->venue_name)
                                <tr>
                                    <td style="padding:4px 0;font-size:14px;color:#6c757d;">Venue</td>
                                    <td style="padding:4px 0;font-size:14px;color:#343a40;">
                                        {{ $event->venue_name }}, {{ $event->city }}, {{ $event->state }}</td>
                                </tr>
                                @endif
                                @if ($ticketTier)
                                <tr>
                                    <td style="padding:4px 0;font-size:14px;color:#6c757d;">Ticket</td>
                                    <td style="padding:4px 0;font-size:14px;color:#343a40;">
                                        {{ $ticketTier->name }} × {{ $registration->quantity ?? 1 }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td style="padding:4px 0;font-size:14px;color:#6c757d;">Reference</td>
                                    <td style="padding:4px 0;font-size:14px;color:#0ab39c;font-weight:600;">
                                        {{ $registration->booking_reference }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        @if ($status === 'cancelled' && $registration->cancellation_reason)
            <tr>
                <td style="padding: 0 0 24px;">
                    <table border="0" cellpadding="0" cellspacing="0" width="100%"
                        style="background-color:#fef2f2;border-left:4px solid #ef4444;border-radius:0 8px 8px 0;">
                        <tr>
                            <td style="padding: 16px 20px;">
                                <p style="margin:0 0 4px;font-size:12px;color:#991b1b;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;">
                                    Reason for Cancellation</p>
                                <p style="margin:0;font-size:14px;color:#7f1d1d;line-height:1.6;">
                                    {{ $registration->cancellation_reason }}
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        @endif

        @if ($status === 'confirmed')
            <tr>
                <td style="padding: 0 0 16px;">
                    <p style="margin:0;font-size:16px;color:#495057;line-height:1.7;">
                        If you have any questions about the event, please don't hesitate to
                        reach out to our support team.
                    </p>
                </td>
            </tr>
        @endif

        @if ($status === 'cancelled')
            <tr>
                <td style="padding: 0 0 16px;">
                    <p style="margin:0;font-size:16px;color:#495057;line-height:1.7;">
                        If you believe this cancellation was made in error, please contact our
                        support team for assistance.
                    </p>
                </td>
            </tr>
        @endif

        {{-- CTA --}}
        <tr>
            <td align="center" style="padding: 8px 0 32px;">
                <table border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="center" style="border-radius:8px;">
                            <a href="{{ $event?->id ? url('/events/' . $event->id) : config('app.url') }}"
                                target="_blank"
                                style="display:inline-block;padding:14px 36px;font-size:15px;font-weight:600;color:#ffffff;background:linear-gradient(135deg,#0ab39c 0%,#099884 100%);border-radius:8px;text-decoration:none;">
                                @if ($status === 'confirmed')
                                    View Event Details
                                @else
                                    Browse Events
                                @endif
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
@endsection
