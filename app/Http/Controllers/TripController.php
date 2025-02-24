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
        ]);

        // Run the scraper.js script
        $scriptPath = 'D:\University\travel-planner\scrapers\scraper.js';
        $command = "cd D:\\University\\travel-planner && node scrapers\\scraper.js";
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

        // Check if the selected_cards.json file exists
        $filePath = 'D:\University\travel-planner\scrapers\selected_cards.json';
        if (!File::exists($filePath)) {
            return response()->json(['error' => 'Selected cards file does not exist'], 500);
        }

        // Read the selected cards from the JSON file
        $selectedCards = json_decode(File::get($filePath), true);

        // Format the selected cards into a string
        $cardsContent = '';
        foreach ($selectedCards as $card) {
            $cardsContent .= "Name: {$card['name']}\nDescription: {$card['description']}\n\n";
        }

        // Using the gpt-3.5-turbo chat model
        $response = $client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a travel assistant.'],
                ['role' => 'user', 'content' => "Create a travel plan for a trip to $city, $country from $startDate to $endDate. Here are some places to visit:\n\n$cardsContent"],
            ],
            'max_tokens' => 150,
        ]);

        // Safely access the content
        $travelPlan = $response['choices'][0]['message']['content'] ?? 'No plan generated.';

        return view('trip.plan', compact('travelPlan'));
    }
}
