<?php

namespace App\Http\Controllers\Web\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /**
     * Show forgot password page
     */
    public function index(){
        return view('pages.auth.forgot_password');
    }


    /**
     * Show otp verification page
     */
    public function showVerifyOtp(){
        return view('pages.auth.verity_otp');
    }


    /**
     * Show set new password page
     */
    public function showSetNewPassword(){
        return view('pages.auth.set_new_password');
    }
}
