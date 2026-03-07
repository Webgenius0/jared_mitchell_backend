<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.dashboard.dashboard');
});


Route::get('/chat', function () {
    return view('pages.messaging.chat.index');
});

Route::get('/mail', function () {
    return view('pages.messaging.mail.index');
});
