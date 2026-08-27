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
            background-color: #fff7ed;
            font-family: 'Segoe UI', Roboto, sans-serif;
            color: #1c1917;
            line-height: 1.7;
        }
        .wrapper {
            width: 100%;
            padding: 40px 15px;
            background-color: #fff7ed;
        }
        .main-table {
            background-color: #ffffff;
            margin: 0 auto;
            max-width: 600px;
            border-radius: 16px;
            border: 2px solid #fed7aa;
            box-shadow: 0 10px 25px rgba(251, 146, 60, 0.15);
            overflow: hidden;
        }
        .header {
            background-color: #ea580c;
            padding: 32px 20px;
            text-align: center;
        }
        .brand-badge {
            font-size: 24px;
            font-weight: 900;
            color: #ffffff;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .promo-tag {
            background-color: #ffffff;
            color: #ea580c;
            font-size: 12px;
            font-weight: 800;
            padding: 4px 14px;
            border-radius: 20px;
            display: inline-block;
            margin-top: 8px;
            text-transform: uppercase;
        }
        .hero-banner {
            width: 100%;
            max-height: 280px;
            object-fit: cover;
            display: block;
        }
        .content-body {
            padding: 36px 30px;
            color: #292524;
            font-size: 16px;
        }
        .cta-container {
            text-align: center;
            margin: 36px 0 20px 0;
        }
        .cta-button {
            display: inline-block;
            background-color: {{ $primaryColor ?? '#ea580c' }};
            color: #ffffff !important;
            font-size: 17px;
            font-weight: 800;
            text-decoration: none;
            padding: 16px 40px;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(234, 88, 12, 0.4);
        }
        .footer {
            background-color: #7c2d12;
            padding: 30px;
            text-align: center;
            color: #ffedd5;
            font-size: 12px;
        }
        .footer a {
            color: #ffffff;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main-table" align="center" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td class="header">
                    <div class="brand-badge">🔥 OUR SOCIAL IMAGE</div>
                    <div class="promo-tag">Featured Announcement</div>
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
                    <p><a href="{{ $unsubscribeUrl ?? '#' }}" target="_blank">Unsubscribe</a></p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
