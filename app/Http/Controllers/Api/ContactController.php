<?php

namespace App\Http\Controllers\Api;

use App\Helpers\FileHandle;
use App\Http\Controllers\Controller;
use App\Mail\ContactSubmissionAdminNotification;
use App\Models\Contact;
use App\Models\User;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    use ApiResponse;

    /**
     * Submit contact form
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'file' => 'nullable|file|max:10240', // 10MB
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $data = $request->only(['first_name', 'last_name', 'email', 'subject', 'message']);
            
            if ($request->hasFile('file')) {
                $data['file_path'] = FileHandle::fileUpload($request->file('file'), 'contacts');
            }

            $contact = Contact::create($data);

            // Send email to admin
            $admin = User::role('admin')->first();
            if ($admin) {
                Mail::to($admin->email)->send(new ContactSubmissionAdminNotification($contact));
            } else {
                // Fallback to config if no admin user found
                Mail::to(config('mail.from.address'))->send(new ContactSubmissionAdminNotification($contact));
            }

            return $this->success('Your message has been sent successfully.', $contact);
        } catch (Exception $e) {
            Log::error('Contact submission error: ' . $e->getMessage());
            return $this->error('Failed to send your message. Please try again later.');
        }
    }
}
