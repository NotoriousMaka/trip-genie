<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes(['register' => true, 'login' => true]);

Route::get('/', function () {
    return view('home');
});

