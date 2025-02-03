<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trip;
use Illuminate\Support\Facades\Auth;

class TripController extends Controller
{
    public function store(Request $request)
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'You must be logged in to submit a trip.');
        }

        // Validate the form data
        $request->validate([
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // Store trip data
        Trip::create([
            'user_id' => Auth::id(),
            'city' => $request->city,
            'country' => $request->country,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return redirect()->back()->with('success', 'Trip added successfully!');
    }
}

