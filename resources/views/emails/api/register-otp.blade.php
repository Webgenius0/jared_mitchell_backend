@extends('emails.layouts.master', [
	'subject' => $subject ?? ($header_message ?? config('app.name')),
	'preheader' => $preheader ?? ('Your OTP is ' . $otp . '. It expires in ' . ($expiresIn ?? 60) . ' minutes.'),
	'headerTitle' => $header_title ?? ($header_message ?? 'One-Time Password'),
])

@section('content')
	@php
		$displayName = $recipientName ?? (isset($user) && $user && $user->email ? \Illuminate\Support\Str::before($user->email, '@') : 'there');
		$expiryMinutes = $expiresIn ?? 60;
		$buttonLabel = $actionLabel ?? 'Continue';
		$bodyText = $bodyMessage ?? 'Use the OTP below to continue.';
	@endphp

	<p style="margin:0 0 8px;font-size:15px;font-weight:600;color:#343a40;line-height:1.5;">
		Hello, {{ $displayName }}!
	</p>

	<p style="margin:0 0 28px;font-size:14px;color:#6c757d;line-height:1.7;">
		{{ $bodyText }}
		This code expires in <strong style="color:#343a40;">{{ $expiryMinutes }} minutes</strong>.
	</p>

	<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 28px;">
		<tr>
			<td align="center">
				<!--[if mso]>
					<table role="presentation" align="center" cellpadding="0" cellspacing="0" border="0">
					<tr><td style="background-color:#f0fdf9;border:2px dashed #0ab39c;border-radius:8px;padding:24px 48px;">
					<![endif]-->

				<div style="display:inline-block;background-color:#f0fdf9;border:2px dashed #0ab39c;border-radius:8px;padding:20px 48px;text-align:center;">
					<p style="margin:0 0 4px;font-size:11px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:#0ab39c;">
						One-Time Password
					</p>
					<p style="margin:0;font-size:38px;font-weight:700;letter-spacing:10px;color:#0ab39c;font-family:'Courier New',Courier,monospace;line-height:1.2;">
						{{ $otp }}
					</p>
				</div>

				<!--[if mso]>
					</td></tr>
					</table>
					<![endif]-->
			</td>
		</tr>
	</table>

	<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 28px;">
		<tr>
			<td style="background-color:#fff8ec;border-left:4px solid #f7b84b;border-radius:0 6px 6px 0;padding:14px 18px;">
				<p style="margin:0;font-size:13px;color:#856404;line-height:1.6;">
					<strong>Security notice:</strong>
					Never share this OTP with anyone. If you did not request it, you can safely ignore this email.
				</p>
			</td>
		</tr>
	</table>

	<p style="margin:0 0 12px;font-size:13px;font-weight:600;color:#495057;text-transform:uppercase;letter-spacing:0.5px;">
		What to do next
	</p>

	<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 28px;">
		@foreach ([
			['01', 'Return to the verification or password reset form.'],
			['02', 'Enter the 4-digit OTP shown above.'],
			['03', 'Complete the request before the code expires.'],
		] as [$num, $text])
			<tr>
				<td style="padding:6px 0;">
					<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td width="36" valign="top">
								<div style="width:28px;height:28px;background-color:#0ab39c;border-radius:50%;text-align:center;line-height:28px;font-size:11px;font-weight:700;color:#ffffff;">
									{{ $num }}
								</div>
							</td>
							<td style="padding-left:10px;font-size:13px;color:#6c757d;line-height:1.6;vertical-align:middle;">
								{{ $text }}
							</td>
						</tr>
					</table>
				</td>
			</tr>
		@endforeach
	</table>

	@if (!empty($actionUrl))
		<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 28px;">
			<tr>
				<td align="center">
					<a href="{{ $actionUrl }}" class="btn-main" style="display:inline-block;background:linear-gradient(135deg,#0ab39c,#099884);color:#ffffff;font-size:14px;font-weight:600;padding:13px 36px;border-radius:6px;text-decoration:none;letter-spacing:0.3px;mso-hide:all;">
						{{ $buttonLabel }}
					</a>
				</td>
			</tr>
		</table>

		<p style="margin:0;font-size:12px;color:#adb5bd;line-height:1.6;text-align:center;word-break:break-all;">
			If the button does not work, open this link in your browser:<br>
			<a href="{{ $actionUrl }}" style="color:#0ab39c;font-size:11px;">{{ $actionUrl }}</a>
		</p>
	@endif
@endsection
