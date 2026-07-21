<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Vote Purchase Request</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f7;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 24px;
            background-color: #ffffff;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 24px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 22px;
        }
        .content {
            padding: 24px;
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-top: none;
            border-radius: 0 0 8px 8px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .details-table th,
        .details-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e0e0e0;
            text-align: left;
        }
        .details-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            width: 140px;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
        }
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
        }
        .btn-primary {
            background-color: #667eea;
            color: #ffffff;
        }
        .btn-primary:hover {
            background-color: #5a67d8;
        }
        .footer {
            text-align: center;
            padding: 16px;
            color: #999;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New Vote Purchase Request</h1>
        </div>
        <div class="content">
            <p>Hello Admin,</p>
            <p>A new vote purchase request has been submitted and is awaiting your approval.</p>

            <table class="details-table">
                <tr>
                    <th>Request ID</th>
                    <td>#{{ $purchase->id }}</td>
                </tr>
                <tr>
                    <th>User</th>
                    <td>{{ $purchase->user?->profile?->name ?? $purchase->user?->email ?? '—' }}</td>
                </tr>
                <tr>
                    <th>User Email</th>
                    <td>{{ $purchase->user?->email ?? '—' }}</td>
                </tr>
                <tr>
                    <th>Package</th>
                    <td>
                        <strong>{{ $purchase->package?->name ?? $purchase->package }}</strong>
                        ({{ $purchase->votes_count }} vote(s))
                    </td>
                </tr>
                <tr>
                    <th>Amount</th>
                    <td><strong>${{ number_format($purchase->amount_paid, 2) }}</strong></td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td><span class="badge badge-warning">Pending Approval</span></td>
                </tr>
                <tr>
                    <th>Date</th>
                    <td>{{ $purchase->created_at->format('M d, Y h:i A') }}</td>
                </tr>
                <tr>
                    <th>Nominee</th>
                    <td>
                        @php
                            $spotlightable = $purchase->nominee?->spotlightable;
                            $name = $spotlightable?->business_name ?? $spotlightable?->brand_name ?? $spotlightable?->artist_stage_name ?? '—';
                        @endphp
                        {{ $name }}
                    </td>
                </tr>
                <tr>
                    <th>Week</th>
                    <td>Week {{ $purchase->nominee?->week?->week_number }} ({{ $purchase->nominee?->week?->year }})</td>
                </tr>
            </table>

            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ route('admin.spotlight.vote-purchases.show', $purchase->id) }}" class="btn btn-primary">
                    Review Purchase Request
                </a>
            </div>

            <p style="margin-top: 20px; color: #666; font-size: 14px;">
                Once approved, the user will be able to pay via Stripe to credit these votes to their spotlight.
            </p>
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} Our Spotlight Inc. All rights reserved.</p>
            <p>This is an automated notification. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
