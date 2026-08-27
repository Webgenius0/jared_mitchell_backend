<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Our Social Image Newsletter' }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #090d16;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #f1f5f9;
            line-height: 1.7;
        }
        .wrapper {
            width: 100%;
            padding: 40px 15px;
            background-color: #090d16;
        }
        .main-table {
            background-color: #0f172a;
            margin: 0 auto;
            max-width: 600px;
            border-radius: 16px;
            border: 1px solid #1e293b;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.6);
            overflow: hidden;
        }
        .header {
            background-color: #020617;
            padding: 36px 20px;
            text-align: center;
            border-bottom: 3px solid {{ $primaryColor ?? '#ec4899' }};
        }
        .brand-badge {
            font-size: 22px;
            font-weight: 900;
            letter-spacing: 3px;
            color: #ffffff;
            text-transform: uppercase;
            text-shadow: 0 0 15px {{ $primaryColor ?? '#ec4899' }};
        }
        .hero-banner {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            display: block;
        }
        .content-body {
            padding: 36px 30px;
            color: #cbd5e1;
            font-size: 16px;
        }
        .content-body h1, .content-body h2, .content-body h3 {
            color: #ffffff;
            font-weight: 700;
            margin-top: 24px;
            border-left: 4px solid {{ $primaryColor ?? '#ec4899' }};
            padding-left: 12px;
        }
        .cta-container {
            text-align: center;
            margin: 36px 0 20px 0;
        }
        .cta-button {
            display: inline-block;
            background-color: {{ $primaryColor ?? '#ec4899' }};
            color: #ffffff !important;
            font-size: 16px;
            font-weight: 700;
            text-decoration: none;
            padding: 16px 36px;
            border-radius: 50px;
            box-shadow: 0 0 20px {{ $primaryColor ?? '#ec4899' }};
        }
        .footer {
            background-color: #020617;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #1e293b;
            font-size: 12px;
            color: #64748b;
        }
        .footer a {
            color: {{ $primaryColor ?? '#ec4899' }};
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main-table" align="center" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td class="header">
                    <div class="brand-badge">⚡ OUR SOCIAL IMAGE</div>
                </td>
            </tr>

            @if(!empty($bannerImageUrl))
            <tr>
                <td>
                    <img src="{{ $bannerImageUrl }}" alt="Banner" class="hero-banner">
                </td>
            </tr>
            @endif

            <tr>
                <td class="content-body">
                    {!! $htmlContent !!}

                    @if(!empty($ctaButtonText) && !empty($ctaButtonUrl))
                    <div class="cta-container">
                        <a href="{{ $ctaButtonUrl }}" target="_blank" class="cta-button">
                            {{ $ctaButtonText }}
                        </a>
                    </div>
                    @endif
                </td>
            </tr>

            <tr>
                <td class="footer">
                    <p>© {{ date('Y') }} Our Social Image. All rights reserved.</p>
                    <p><a href="{{ $unsubscribeUrl ?? '#' }}" target="_blank">Unsubscribe from Newsletter</a></p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
