@extends('emails.layouts.master')

@section('title', 'New Contact Submission')

@section('content')
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #ffffff; border-radius: 8px;">
    <tr>
        <td style="padding: 40px;">
            <h1 style="color: #333333; font-size: 24px; font-weight: bold; margin-bottom: 20px;">New Contact Message</h1>
            <p style="color: #666666; font-size: 16px; line-height: 1.5; margin-bottom: 30px;">
                You have received a new contact form submission from the website.
            </p>

            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #eeeeee; font-weight: bold; width: 150px;">From:</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #eeeeee;">{{ $contact->full_name }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #eeeeee; font-weight: bold;">Email:</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #eeeeee;">{{ $contact->email }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #eeeeee; font-weight: bold;">Subject:</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #eeeeee;">{{ $contact->subject }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #eeeeee; font-weight: bold; vertical-align: top;">Message:</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #eeeeee; line-height: 1.6;">{{ $contact->message }}</td>
                </tr>
                @if($contact->file_path)
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #eeeeee; font-weight: bold;">Attachment:</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #eeeeee;">
                        <a href="{{ asset('storage/' . $contact->file_path) }}" style="color: #007bff; text-decoration: none;">View Attached File</a>
                    </td>
                </tr>
                @endif
            </table>

            <div style="margin-top: 40px; text-align: center;">
                <a href="{{ route('admin.contacts.index') }}" style="background-color: #007bff; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">View in Admin Panel</a>
            </div>
        </td>
    </tr>
</table>
@endsection
