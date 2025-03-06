<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenAI;
use Illuminate\Support\Facades\File;

class TripController extends Controller
{
    public function store(Request $request)
    {
        // Validate input
        $request->validate([
            'city' => 'required|string',
            'country' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'preference' => 'string|in:adventure,relaxation,culture,nature,food',
        ]);

        // Execute scrapers
        exec("cd " . base_path() . " && node scrapers/scraper.js", $output1, $return_var1);
        exec("cd " . base_path() . " && node scrapers/scraper-2.js", $output2, $return_var2);

        if ($return_var1 !== 0 || $return_var2 !== 0) {
            return response()->json(['error' => 'Failed to run scraper scripts'], 500);
        }

        // Retrieve input values
        $city = $request->input('city');
        $country = $request->input('country');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // OpenAI API setup
        $apiKey = env('OPENAI_API_KEY');
        if (!$apiKey) {
            return response()->json(['error' => 'OpenAI API key is not set'], 500);
        }
        $client = OpenAI::client($apiKey);

        // Retrieve scraped data
        $filePath1 = base_path('scrapers/selected_cards.json');
        $filePath2 = base_path('scrapers/wikivoyage_data.json');

        if (!File::exists($filePath1) || !File::exists($filePath2)) {
            return response()->json(['error' => 'Scraped data files do not exist'], 500);
        }

        $selectedCards = json_decode(File::get($filePath1), true);
        $wikiData = json_decode(File::get($filePath2), true);

        // Format content for AI request
        $cardsContent = "";
        foreach ($selectedCards as $card) {
            $timeSpent = isset($card['time_spent']) ? "{$card['time_spent']} minutes" : "Time not specified";
            $cardsContent .= "{$card['name']}\nDescription: {$card['description']}\nDuration: $timeSpent\n\n";
        }

        // Extract Wiki data
        $wikiContent = implode("\n", array_map(fn($item) => $item['description'] ?? '', $wikiData));

        // AI-generated travel plan
        $response = $client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a travel assistant that creates structured, engaging itineraries with full-day activities, walking times, and meal breaks.'],
                ['role' => 'user', 'content' => "Create a structured itinerary for $city, $country from $startDate to $endDate.
                Format it as follows:

                Day X:
                - 9:00 AM - Activity Name (Duration: X minutes)
                - 11:00 AM - Activity Name (Duration: X minutes, Walking Time: X minutes)
                - 1:00 PM - Lunch Break (Suggested restaurants: X, Y, Z)
                - 3:00 PM - Activity Name (Duration: X minutes, Walking Time: X minutes)
                - 6:00 PM - Dinner (Suggested restaurants: X, Y, Z)
                - 8:00 PM - Evening Activity (Duration: X minutes, Walking Time: X minutes)

                Use the following data:
                $cardsContent
                Additional info:
                $wikiContent

                Provide travel tips and best visit times for each place mentioned."],
            ],
            'max_tokens' => 4000,
        ]);

        $travelPlan = $response['choices'][0]['message']['content'] ?? 'No plan generated.';
        $locations = array_merge($selectedCards, array_map(fn($desc) => ['name' => 'Wikivoyage Info', 'description' => $desc], $wikiData));

        return view('trip.plan', compact('travelPlan', 'locations', 'city', 'country', 'startDate', 'endDate'));
    }
}
