<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenAI;

class TripController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'city' => 'required|string',
            'country' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $city = $request->input('city');
        $country = $request->input('country');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $apiKey = env('OPENAI_API_KEY');
        if (!$apiKey) {
            return response()->json(['error' => 'OpenAI API key is not set'], 500);
        }

        $client = OpenAI::client($apiKey);

        // Using the gpt-3.5-turbo chat model
        $response = $client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a travel assistant.'],
                ['role' => 'user', 'content' => "Create a travel plan for a trip to $city, $country from $startDate to $endDate."],
            ],
            'max_tokens' => 150,
        ]);

        // Safely access the content
        $travelPlan = $response['choices'][0]['message']['content'] ?? 'No plan generated.';

        return view('trip.plan', compact('travelPlan'));
    }
}
