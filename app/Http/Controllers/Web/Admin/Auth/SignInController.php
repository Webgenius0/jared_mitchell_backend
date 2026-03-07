<?php

namespace App\Http\Controllers\Web\Admin\Auth;

use App\Http\Controllers\Controller;

class SignInController extends Controller
{
    /**
     * Show sing in page
     */
    public function index()
    {
        return view('pages.auth.login');
    }
}
