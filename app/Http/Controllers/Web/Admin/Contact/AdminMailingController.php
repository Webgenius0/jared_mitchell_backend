<?php

namespace App\Http\Controllers\Web\Admin\Contact;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AdminMailingController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Show Mailing Page
    |--------------------------------------------------------------------------
    */
    public function index(): View
    {
        // $user = auth('admin')->user();
        // $profile = $user->profile;

        return view('web.messaging.mail.index');
    }
}
