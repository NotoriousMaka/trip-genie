<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenAI;
use Illuminate\Support\Facades\File;

class TripController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'city' => 'required|string',
            'country' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'preference' => 'string|in:adventure,relaxation,culture,nature,food',
        ]);

        $scriptPath = base_path('scrapers/scraper.js');
        $command = "cd " . base_path() . " && node scrapers/scraper.js";
        exec($command, $output, $return_var);

        if ($return_var !== 0) {
            return response()->json(['error' => 'Failed to run scraper script'], 500);
        }

        $city = $request->input('city');
        $country = $request->input('country');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $apiKey = env('OPENAI_API_KEY');
        if (!$apiKey) {
            return response()->json(['error' => 'OpenAI API key is not set'], 500);
        }

        $client = OpenAI::client($apiKey);

        $filePath = base_path('scrapers/selected_cards.json');
        if (!File::exists($filePath)) {
            return response()->json(['error' => 'Selected cards file does not exist'], 500);
        }

        $selectedCards = json_decode(File::get($filePath), true);

        $cardsContent = '';
        foreach ($selectedCards as $card) {
            $cardsContent .= "Name: {$card['name']}\nDescription: {$card['description']}\n\n";
        }

        // Using the gpt-3.5-turbo chat model
        $response = $client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a travel assistant. You need to provide detailed information about locations and travel plans. Also, provide travel tips for the location and take into consideration time, weather and any other relevant information.'],
                ['role' => 'user', 'content' => "Create a travel plan for a trip to $city, $country from $startDate to $endDate. Here are some places to visit:\n\n$cardsContent"],
            ],
            'max_tokens' => 700,
        ]);

        $travelPlan = $response['choices'][0]['message']['content'] ?? 'No plan generated.';

        $locations = $selectedCards;

        return view('trip.plan', compact('travelPlan', 'locations', 'city', 'country', 'startDate', 'endDate'));
    }

    public function showTripPlan()
    {
        $filePath = base_path('scrapers/selected_cards.json');

        if (!File::exists($filePath)) {
            return response()->json(['error' => 'Selected cards file does not exist'], 500);
        }

        $jsonData = file_get_contents($filePath);

        $locations = json_decode($jsonData, true);

        if (!is_array($locations)) {
            $locations = [];
        }

        return view('trip.plan', compact('locations'));
    }
}
