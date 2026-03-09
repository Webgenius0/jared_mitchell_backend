<?php

use Illuminate\Support\Facades\Route;

Route::get('403', function(){
    return view('errors.404');
});

require __DIR__ . '/admin_auth.php';
