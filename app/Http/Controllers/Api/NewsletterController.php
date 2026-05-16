<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Newsletter;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    use ApiResponse;

    /**
     * Subscribe to newsletter
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $newsletter = Newsletter::where('email', $request->email)->first();

            if ($newsletter) {
                if ($newsletter->status === 'active') {
                    return $this->success('You are already subscribed to our newsletter.');
                }
                
                $newsletter->update(['status' => 'active']);
                return $this->success('Your subscription has been reactivated successfully.');
            }

            Newsletter::create([
                'email' => $request->email,
                'status' => 'active',
            ]);

            return $this->success('Thank you for subscribing to our newsletter.');
        } catch (Exception $e) {
            Log::error('Newsletter subscription error: ' . $e->getMessage());
            return $this->error('Failed to subscribe. Please try again later.');
        }
    }
}
