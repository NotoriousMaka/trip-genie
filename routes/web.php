<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TripController;
use App\Http\Controllers\Auth\GoogleController;

Auth::routes();

Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);


Route::get('/', function () {
    return view('home');
});

Route::middleware('auth')->group(function () {
    Route::post('/trips', [TripController::class, 'store'])->name('trips.store');
    Route::get('/trip/plan', [TripController::class, 'showTripPlan'])->name('trip.plan');
    Route::post('/create-plan', [TripController::class, 'store'])->name('create-plan');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/check-auth', function () {
    return response()->json(['authenticated' => Auth::check()]);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/');
})->middleware('auth')->name('logout');


require __DIR__.'/auth.php';
