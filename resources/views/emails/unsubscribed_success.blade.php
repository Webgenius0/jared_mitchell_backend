<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribed — Our Social Image</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #0f172a;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .card {
            background-color: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 40px;
            max-width: 480px;
            width: 90%;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
        }
        .badge {
            display: inline-block;
            background: rgba(99, 102, 241, 0.15);
            color: #818cf8;
            font-size: 14px;
            font-weight: 700;
            padding: 6px 16px;
            border-radius: 50px;
            margin-bottom: 20px;
            letter-spacing: 1px;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 12px;
            color: #ffffff;
        }
        p {
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .btn {
            display: inline-block;
            background: #6366f1;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn:hover {
            background: #4f46e5;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="badge">OUR SOCIAL IMAGE</div>
        <h1>You've Been Unsubscribed</h1>
        <p>Your email <strong>{{ $email ?? '' }}</strong> has been successfully removed from our newsletter broadcast list. You will no longer receive marketing emails from us.</p>
        <a href="https://admin.oursocialimage.net" class="btn">Return to Platform</a>
    </div>
</body>
</html>
