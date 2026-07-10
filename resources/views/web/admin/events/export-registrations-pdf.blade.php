<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Event Registrations Export</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #1B2A4A;
        }
        .header h1 {
            font-size: 18px;
            color: #1B2A4A;
            margin: 0 0 5px 0;
        }
        .header p {
            font-size: 11px;
            color: #666;
            margin: 0;
        }
        .filter-info {
            font-size: 9px;
            color: #888;
            margin-bottom: 15px;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background-color: #1B2A4A;
            color: #ffffff;
            padding: 6px 4px;
            text-align: left;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        td {
            padding: 5px 4px;
            border-bottom: 1px solid #dee2e6;
            font-size: 8px;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .status-paid      { color: #10b981; font-weight: bold; }
        .status-pending    { color: #f59e0b; font-weight: bold; }
        .status-failed     { color: #ef4444; font-weight: bold; }
        .status-refunded   { color: #6366f1; font-weight: bold; }
        .status-confirmed  { color: #10b981; font-weight: bold; }
        .status-cancelled  { color: #ef4444; font-weight: bold; }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #999;
            padding: 10px;
            border-top: 1px solid #dee2e6;
        }
        .page-number {
            position: fixed;
            bottom: 10px;
            right: 20px;
            font-size: 8px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Event Registrations Report</h1>
        <p>Generated on {{ now()->format('F d, Y h:i A') }}</p>
    </div>

    @php
        $filters = [];
        if (request('status'))           $filters[] = 'Status: ' . ucfirst(request('status'));
        if (request('payment_status'))   $filters[] = 'Payment: ' . ucfirst(request('payment_status'));
        if (request('date_from'))        $filters[] = 'From: ' . request('date_from');
        if (request('date_to'))          $filters[] = 'To: ' . request('date_to');
        if (request('search_query'))     $filters[] = 'Search: "' . request('search_query') . '"';
    @endphp

    @if(!empty($filters))
        <div class="filter-info">
            Filters applied: {{ implode(' | ', $filters) }} | Total records: {{ $registrations->count() }}
        </div>
    @else
        <div class="filter-info">
            All records | Total: {{ $registrations->count() }}
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Booking Ref</th>
                <th>Event</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Tier</th>
                <th>Qty</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Registered</th>
            </tr>
        </thead>
        <tbody>
            @forelse($registrations as $reg)
            <tr>
                <td>{{ $reg->booking_reference }}</td>
                <td>{{ $reg->event?->title ?? 'N/A' }}</td>
                <td>{{ trim($reg->first_name . ' ' . $reg->last_name) ?: '—' }}</td>
                <td>{{ $reg->email }}</td>
                <td>{{ $reg->ticketTier?->name ?? 'N/A' }}</td>
                <td style="text-align: center;">{{ $reg->quantity ?? 1 }}</td>
                <td style="text-align: right;">{{ number_format($reg->total ?? 0, 2) }}</td>
                <td class="status-{{ $reg->payment_status ?? 'unpaid' }}">{{ $reg->payment_status ? ucfirst($reg->payment_status) : 'Unpaid' }}</td>
                <td class="status-{{ $reg->status }}">{{ ucfirst($reg->status) }}</td>
                <td>{{ $reg->created_at->format('Y-m-d H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align: center; padding: 20px; color: #999;">
                    No records found matching the filters.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Event Registrations Report — {{ config('app.name') }}
    </div>
    <div class="page-number">
        Page {PAGE_NUM} of {PAGE_COUNT}
    </div>
</body>
</html>
