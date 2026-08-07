<?php

namespace App\Http\Controllers\Api;

use App\Helpers\FileHandle;
use App\Http\Controllers\Controller;
use App\Models\SponsorApplication;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SponsorApplicationController extends Controller
{
    use ApiResponse;

    /**
     * Submit sponsor application
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'why_sponsor' => 'nullable|string|max:2000',
            'sponsor_title' => 'nullable|string|max:255',
            'sponsor_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:12000', // 12MB
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $data = $request->only(['full_name', 'email', 'phone_number', 'why_sponsor', 'sponsor_title']);
            
            if ($request->hasFile('sponsor_image')) {
                $data['sponsor_image'] = FileHandle::fileUpload($request->file('sponsor_image'), 'sponsor_applications');
            }

            $application = SponsorApplication::create($data);

            return $this->success('Your sponsorship application has been submitted successfully.', $application);
        } catch (Exception $e) {
            Log::error('Sponsor application submission error: ' . $e->getMessage());
            return $this->error('Failed to submit application. Please try again later.');
        }
    }
}
