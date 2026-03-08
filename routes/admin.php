<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Admin\Dashboard\AdminDashboardController;

Route::get('/', [AdminDashboardController::class, 'index'])->name('show.admin.dashboard'); // show admin dashboard

