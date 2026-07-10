<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Events Export</title>
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
            padding: 8px 6px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        td {
            padding: 6px;
            border-bottom: 1px solid #dee2e6;
            font-size: 9px;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .status-draft      { color: #f59e0b; font-weight: bold; }
        .status-published  { color: #10b981; font-weight: bold; }
        .status-cancelled  { color: #ef4444; font-weight: bold; }
        .status-completed  { color: #6366f1; font-weight: bold; }
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
        <h1>Events Report</h1>
        <p>Generated on {{ now()->format('F d, Y h:i A') }}</p>
    </div>

    @php
        $filters = [];
        if (request('status'))     $filters[] = 'Status: ' . ucfirst(request('status'));
        if (request('event_type')) $filters[] = 'Type: ' . ucwords(str_replace('_', ' ', request('event_type')));
        if (request('date_from'))  $filters[] = 'From: ' . request('date_from');
        if (request('date_to'))    $filters[] = 'To: ' . request('date_to');
        if (request('search'))     $filters[] = 'Search: "' . request('search') . '"';
    @endphp

    @if(!empty($filters))
        <div class="filter-info">
            Filters applied: {{ implode(' | ', $filters) }} | Total records: {{ $events->count() }}
        </div>
    @else
        <div class="filter-info">
            All records | Total: {{ $events->count() }}
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Type</th>
                <th>Status</th>
                <th>City</th>
                <th>Venue</th>
                <th>Hosted By</th>
                <th>Starts At</th>
                <th>Ends At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($events as $event)
            <tr>
                <td>{{ $event->id }}</td>
                <td>{{ $event->title }}</td>
                <td>{{ ucwords(str_replace('_', ' ', $event->event_type)) }}</td>
                <td class="status-{{ $event->status }}">{{ ucfirst($event->status) }}</td>
                <td>{{ $event->city ?? '—' }}</td>
                <td>{{ $event->venue_name ?? '—' }}</td>
                <td>{{ $event->hosted_by ?? '—' }}</td>
                <td>{{ $event->starts_at?->format('Y-m-d H:i') ?? '—' }}</td>
                <td>{{ $event->ends_at?->format('Y-m-d H:i') ?? '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center; padding: 20px; color: #999;">
                    No records found matching the filters.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Events Report — {{ config('app.name') }}
    </div>
    <div class="page-number">
        Page {PAGE_NUM} of {PAGE_COUNT}
    </div>
</body>
</html>
