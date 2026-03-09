<?php

namespace App\Http\Controllers\Web\Admin\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    /**
     * Show Admin Dashboard
     */
    public function index(){
        return view('pages.dashboard.dashboard');
    }
}
