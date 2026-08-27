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
            background-color: #f4f6f9;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            color: #1f2937;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f4f6f9;
            padding-bottom: 40px;
        }
        .main-table {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            border-spacing: 0;
            font-family: sans-serif;
            color: #1f2937;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #111827;
            padding: 30px 20px;
            text-align: center;
        }
        .header img {
            max-height: 48px;
            width: auto;
        }
        .header-title {
            color: #ffffff;
            font-size: 22px;
            font-weight: 700;
            margin: 10px 0 0 0;
            letter-spacing: 0.5px;
        }
        .hero-banner {
            width: 100%;
            max-height: 280px;
            object-fit: cover;
            display: block;
        }
        .content-body {
            padding: 35px 30px;
            font-size: 16px;
            line-height: 1.6;
            color: #374151;
        }
        .content-body h1, .content-body h2, .content-body h3 {
            color: #111827;
            margin-top: 0;
        }
        .cta-container {
            text-align: center;
            margin: 35px 0 20px 0;
        }
        .cta-button {
            display: inline-block;
            background-color: {{ $primaryColor ?? '#6366f1' }};
            color: #ffffff !important;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
            transition: all 0.3s ease;
        }
        .footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            font-size: 13px;
            color: #6b7280;
        }
        .footer a {
            color: {{ $primaryColor ?? '#6366f1' }};
            text-decoration: none;
        }
        .social-links {
            margin-bottom: 15px;
        }
        .social-links a {
            display: inline-block;
            margin: 0 8px;
            color: #4b5563;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main-table" align="center" width="100%" cellpadding="0" cellspacing="0">
            <!-- Header -->
            <tr>
                <td class="header">
                    <div class="header-title">OUR SOCIAL IMAGE</div>
                </td>
            </tr>

            <!-- Hero Banner Image (if provided) -->
            @if(!empty($bannerImageUrl))
            <tr>
                <td>
                    <img src="{{ $bannerImageUrl }}" alt="Newsletter Banner" class="hero-banner">
                </td>
            </tr>
            @endif

            <!-- Body Content -->
            <tr>
                <td class="content-body">
                    {!! $htmlContent !!}

                    <!-- CTA Button (if provided) -->
                    @if(!empty($ctaButtonText) && !empty($ctaButtonUrl))
                    <div class="cta-container">
                        <a href="{{ $ctaButtonUrl }}" target="_blank" class="cta-button">
                            {{ $ctaButtonText }}
                        </a>
                    </div>
                    @endif
                </td>
            </tr>

            <!-- Footer -->
            <tr>
                <td class="footer">
                    <div class="social-links">
                        <a href="https://oursocialimage.net" target="_blank">Website</a> •
                        <a href="https://instagram.com" target="_blank">Instagram</a> •
                        <a href="https://youtube.com" target="_blank">YouTube</a>
                    </div>
                    <p style="margin: 5px 0;">© {{ date('Y') }} Our Social Image. All rights reserved.</p>
                    <p style="margin: 5px 0; font-size: 12px; color: #9ca3af;">
                        You received this email because you subscribed to Our Social Image newsletters.<br>
                        <a href="{{ $unsubscribeUrl ?? '#' }}" target="_blank">Unsubscribe from this list</a>
                    </p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
