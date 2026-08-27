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
            background-color: #f4f6f8;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            color: #1e293b;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f4f6f8;
            padding: 20px 0;
        }
        .main-table {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 720px;
            border-spacing: 0;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        /* Crisp Full Header */
        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            padding: 32px 30px;
            text-align: center;
            border-bottom: 4px solid {{ $primaryColor ?? '#6366f1' }};
            border-top-left-radius: 0px;
            border-top-right-radius: 0px;
        }
        .brand-badge {
            display: inline-block;
            color: #ffffff;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            background: rgba(255, 255, 255, 0.08);
            padding: 8px 24px;
            border-radius: 4px;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .header-tagline {
            color: #94a3b8;
            font-size: 13px;
            font-weight: 500;
            margin-top: 8px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        /* Hero Banner Container */
        .banner-container {
            padding: 0;
        }
        .hero-banner {
            width: 100%;
            max-height: 350px;
            object-fit: cover;
            display: block;
        }
        /* Content Body */
        .content-body {
            padding: 42px 40px;
            font-size: 16.5px;
            line-height: 1.8;
            color: #334155;
        }
        .content-body h1, .content-body h2, .content-body h3 {
            color: #0f172a;
            font-weight: 700;
            margin-top: 24px;
            margin-bottom: 14px;
            padding-left: 12px;
            border-left: 4px solid {{ $primaryColor ?? '#6366f1' }};
        }
        .content-body p {
            margin-bottom: 18px;
        }
        .content-body ul, .content-body ol {
            padding-left: 22px;
            margin-bottom: 20px;
        }
        .content-body li {
            margin-bottom: 8px;
        }
        .content-body blockquote {
            background-color: #f8fafc;
            border-left: 4px solid {{ $primaryColor ?? '#6366f1' }};
            margin: 20px 0;
            padding: 16px 20px;
            font-style: italic;
            color: #475569;
            border-radius: 0 4px 4px 0;
        }
        .content-body img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
        }
        /* CTA Button */
        .cta-container {
            text-align: center;
            margin: 38px 0 18px 0;
        }
        .cta-button {
            display: inline-block;
            background: {{ $primaryColor ?? '#6366f1' }};
            color: #ffffff !important;
            font-size: 16.5px;
            font-weight: 700;
            text-decoration: none;
            padding: 16px 40px;
            border-radius: 4px;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);
            letter-spacing: 0.5px;
        }
        /* Footer */
        .footer {
            background-color: #0f172a;
            padding: 38px 30px;
            text-align: center;
            color: #94a3b8;
            font-size: 13px;
        }
        .footer-logo {
            font-size: 17px;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 12px;
            letter-spacing: 1px;
        }
        .social-links {
            margin-bottom: 20px;
        }
        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #cbd5e1;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }
        .footer-divider {
            height: 1px;
            background-color: #334155;
            margin: 20px 0;
        }
        .footer a.unsubscribe-link {
            color: #64748b;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main-table" align="center" width="100%" cellpadding="0" cellspacing="0">
            <!-- Header -->
            <tr>
                <td class="header">
                    <div class="brand-badge">OUR SOCIAL IMAGE</div>
                    <div class="header-tagline">Official Community & Artist Newsletter</div>
                </td>
            </tr>

            <!-- Hero Banner Image (if provided) -->
            @if(!empty($bannerImageUrl))
            <tr>
                <td class="banner-container">
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
                            {{ $ctaButtonText }} →
                        </a>
                    </div>
                    @endif
                </td>
            </tr>

            <!-- Footer -->
            <tr>
                <td class="footer">
                    <div class="footer-logo">OUR SOCIAL IMAGE</div>
                    <div class="social-links">
                        <a href="https://admin.oursocialimage.net" target="_blank">Platform</a> •
                        <a href="https://instagram.com" target="_blank">Instagram</a> •
                        <a href="https://youtube.com" target="_blank">YouTube</a>
                    </div>
                    <div class="footer-divider"></div>
                    <p style="margin: 6px 0;">© {{ date('Y') }} Our Social Image. All rights reserved.</p>
                    <p style="margin: 6px 0; font-size: 12px; color: #64748b;">
                        You received this email because you subscribed to Our Social Image platform news.<br>
                        Need to stop receiving these emails? <a href="{{ $unsubscribeUrl ?? '#' }}" target="_blank" class="unsubscribe-link">Unsubscribe here</a>.
                    </p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
