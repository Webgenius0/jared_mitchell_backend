<?php

namespace App\Http\Controllers\Web\Admin\Contact;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AdminChattingController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Show Chatting Page
    |--------------------------------------------------------------------------
    */
    public function index(): View
    {
        // $user = auth('admin')->user();
        // $profile = $user->profile;

        return view('web.messaging.chat.index');
    }
}
