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
            background-color: #ffffff;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #27272a;
            line-height: 1.7;
        }
        .wrapper {
            width: 100%;
            padding: 40px 15px;
            background-color: #f4f4f5;
        }
        .main-table {
            background-color: #ffffff;
            margin: 0 auto;
            max-width: 580px;
            border-radius: 8px;
            border: 1px solid #e4e4e7;
            padding: 40px 32px;
        }
        .brand-header {
            text-align: center;
            padding-bottom: 24px;
            border-bottom: 2px solid #f4f4f5;
            margin-bottom: 28px;
        }
        .brand-name {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 3px;
            color: #09090b;
            text-transform: uppercase;
        }
        .hero-banner {
            width: 100%;
            max-height: 280px;
            object-fit: cover;
            border-radius: 6px;
            margin-bottom: 28px;
        }
        .content-body h1, .content-body h2, .content-body h3 {
            color: #09090b;
            font-weight: 700;
            margin-top: 24px;
            margin-bottom: 12px;
        }
        .cta-container {
            text-align: center;
            margin: 32px 0 16px 0;
        }
        .cta-button {
            display: inline-block;
            background-color: {{ $primaryColor ?? '#18181b' }};
            color: #ffffff !important;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 6px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 24px;
            border-top: 1px solid #f4f4f5;
            text-align: center;
            font-size: 12px;
            color: #71717a;
        }
        .footer a {
            color: #27272a;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main-table" align="center" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <div class="brand-header">
                        <div class="brand-name">OUR SOCIAL IMAGE</div>
                    </div>

                    @if(!empty($bannerImageUrl))
                    <img src="{{ $bannerImageUrl }}" alt="Banner" class="hero-banner">
                    @endif

                    <div class="content-body">
                        {!! $htmlContent !!}

                        @if(!empty($ctaButtonText) && !empty($ctaButtonUrl))
                        <div class="cta-container">
                            <a href="{{ $ctaButtonUrl }}" target="_blank" class="cta-button">
                                {{ $ctaButtonText }}
                            </a>
                        </div>
                        @endif
                    </div>

                    <div class="footer">
                        <p>© {{ date('Y') }} Our Social Image. All rights reserved.</p>
                        <p>You received this message from Our Social Image. <a href="{{ $unsubscribeUrl ?? '#' }}" target="_blank">Unsubscribe</a></p>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
