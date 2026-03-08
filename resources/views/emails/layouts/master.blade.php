<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"
    xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="format-detection" content="telephone=no,address=no,email=no,date=no,url=no">
    <title>{{ $subject ?? config('app.name') }}</title>

    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->

    {{--
        Gmail strips <style> from <head> but keeps media queries in some clients.
        We keep only critical resets + mobile responsive here.
        All visual styles are inline.
    --}}
    <style type="text/css">
        /* ── Reset ─────────────────────────────────── */
        * {
            box-sizing: border-box;
        }

        body,
        table,
        td,
        a {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table,
        td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
            border-collapse: collapse !important;
        }

        img {
            -ms-interpolation-mode: bicubic;
            border: 0;
            outline: none;
            text-decoration: none;
        }

        a {
            text-decoration: none;
        }

        /* ── Mobile ────────────────────────────────── */
        @media only screen and (max-width: 620px) {
            .email-wrapper {
                width: 100% !important;
                min-width: 100% !important;
            }

            .email-content {
                width: 100% !important;
                padding: 24px 20px !important;
            }

            .email-header {
                padding: 24px 20px !important;
            }

            .email-footer {
                padding: 20px !important;
            }

            .btn-main {
                width: 100% !important;
                display: block !important;
                text-align: center !important;
            }

            .hide-mobile {
                display: none !important;
            }

            h1 {
                font-size: 22px !important;
            }
        }
    </style>
</head>

<body
    style="margin:0;padding:0;background-color:#f3f4f6;font-family:'Segoe UI',Roboto,Arial,sans-serif;-webkit-font-smoothing:antialiased;">

    {{-- Preheader (hidden preview text) --}}
    <div style="display:none;max-height:0;overflow:hidden;mso-hide:all;">
        {{ $preheader ?? '' }}&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;
    </div>

    {{-- Outer wrapper table --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background-color:#f3f4f6;margin:0;padding:0;">
        <tr>
            <td align="center" style="padding:40px 16px;">

                <!--[if mso]>
                <table role="presentation" width="600" align="center" cellpadding="0" cellspacing="0" border="0">
                <tr><td>
                <![endif]-->

                {{-- Email card --}}
                <table role="presentation" class="email-wrapper" width="600" cellpadding="0" cellspacing="0"
                    border="0"
                    style="max-width:600px;width:100%;background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

                    {{-- ── HEADER ──────────────────────────────────────────── --}}
                    <tr>
                        <td class="email-header" align="center"
                            style="background:linear-gradient(135deg,#0ab39c 0%,#099884 100%);padding:32px 40px;">

                            {{-- Logo --}}
                            <a href="{{ config('app.url') }}" style="display:inline-block;text-decoration:none;">
                                <img src="{{ config('app.url') }}/admin/assets/images/default/logo-light.png"
                                    alt="{{ config('app.name') }}" width="140" height="auto"
                                    style="display:block;max-width:140px;height:auto;border:0;outline:none;">
                            </a>

                            @isset($headerTitle)
                                <p
                                    style="margin:14px 0 0;font-size:13px;color:rgba(255,255,255,0.85);letter-spacing:0.5px;text-transform:uppercase;font-weight:500;">
                                    {{ $headerTitle }}
                                </p>
                            @endisset

                        </td>
                    </tr>

                    {{-- ── BODY ────────────────────────────────────────────── --}}
                    <tr>
                        <td class="email-content" style="padding:40px 48px;">
                            @yield('content')
                        </td>
                    </tr>

                    {{-- ── DIVIDER ──────────────────────────────────────────── --}}
                    <tr>
                        <td style="padding:0 48px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="border-top:1px solid #e9ebec;font-size:0;line-height:0;">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- ── FOOTER ───────────────────────────────────────────── --}}
                    <tr>
                        <td class="email-footer" align="center"
                            style="padding:28px 40px 32px;background-color:#f8f9fa;">

                            <p style="margin:0 0 6px;font-size:12px;color:#adb5bd;line-height:1.6;">
                                This email was sent by
                                <a href="{{ config('app.url') }}"
                                    style="color:#0ab39c;text-decoration:none;font-weight:500;">
                                    {{ config('app.name') }}
                                </a>
                            </p>

                            <p style="margin:0 0 6px;font-size:12px;color:#adb5bd;line-height:1.6;">
                                If you did not request this email, you can safely ignore it.
                            </p>

                            <p style="margin:16px 0 0;font-size:11px;color:#ced4da;">
                                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                            </p>

                        </td>
                    </tr>

                </table>
                {{-- /email card --}}

                <!--[if mso]>
                </td></tr>
                </table>
                <![endif]-->

            </td>
        </tr>
    </table>

</body>

</html>
