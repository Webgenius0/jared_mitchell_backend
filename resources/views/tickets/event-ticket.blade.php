<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Ticket - {{ $registration->booking_reference }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .ticket-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 40px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .event-details {
            margin-bottom: 30px;
        }
        .event-details h2 {
            margin-top: 0;
            color: #2c3e50;
        }
        .details-grid {
            width: 100%;
            margin-bottom: 20px;
        }
        .details-grid td {
            padding: 8px 0;
            vertical-align: top;
        }
        .label {
            font-weight: bold;
            color: #7f8c8d;
            width: 150px;
        }
        .value {
            color: #2c3e50;
        }
        .qr-section {
            text-align: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px dashed #ccc;
        }
        .qr-code {
            margin: 0 auto 15px;
            width: 200px;
            height: 200px;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 12px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="ticket-container">
        <div class="header">
            <h1>EVENT TICKET</h1>
            <p>Booking Reference: <strong>{{ $registration->booking_reference }}</strong></p>
        </div>

        <div class="event-details">
            <h2>{{ $registration->event->title ?? 'Event Name' }}</h2>
            
            <table class="details-grid">
                <tr>
                    <td class="label">Date & Time:</td>
                    <td class="value">
                        {{ $registration->event->starts_at?->format('F j, Y g:i A') }} - 
                        {{ $registration->event->ends_at?->format('F j, Y g:i A') }} 
                        ({{ $registration->event->timezone }})
                    </td>
                </tr>
                <tr>
                    <td class="label">Venue:</td>
                    <td class="value">
                        {{ $registration->event->venue_name ?? '' }}<br>
                        {{ $registration->event->address ?? '' }}<br>
                        {{ $registration->event->city ?? '' }}, {{ $registration->event->state ?? '' }}
                    </td>
                </tr>
                <tr>
                    <td class="label">Attendee:</td>
                    <td class="value">{{ $registration->first_name }} {{ $registration->last_name }}</td>
                </tr>
                <tr>
                    <td class="label">Ticket Type:</td>
                    <td class="value">{{ $registration->ticketTier->name ?? 'Standard' }} (x{{ $registration->quantity }})</td>
                </tr>
            </table>
        </div>

        <div class="qr-section">
            <p>Please present this QR code at the entrance.</p>
            <div class="qr-code">
                <!-- Embed SVG QR Code -->
                <img src="data:image/svg+xml;base64,{!! $qrCode !!}" alt="QR Code" width="200" height="200">
            </div>
            <p><strong>{{ $registration->booking_reference }}</strong></p>
        </div>

        <div class="footer">
            <p>This ticket is non-transferable. Please bring a valid ID matching the attendee name.</p>
        </div>
    </div>
</body>
</html>
