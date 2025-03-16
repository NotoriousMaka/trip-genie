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

        // Retrieve input values
        $city = $request->input('city');
        $country = $request->input('country');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Define cache file paths
        $cityCountryKey = strtolower($city) . '-' . strtolower($country);
        $atlasCache = base_path("scrapers/cache/{$cityCountryKey}-atlas.json");
        $wikiCache = base_path("scrapers/cache/{$cityCountryKey}-wikivoyage.json");
        $lonelyPlanetCache = base_path("scrapers/cache/{$cityCountryKey}-lonelyplanet.json");

        $atlasDataPath = base_path('scrapers/atlas-data.json');
        $wikiDataPath = base_path('scrapers/wikivoyage_data.json');
        $lonelyPlanetDataPath = base_path('scrapers/lonelyplanet_data.json');

        // Check if cache exists for Atlas Obscura data
        $runAtlasScraper = true;
        if (File::exists($atlasCache)) {
            File::copy($atlasCache, $atlasDataPath);
            $runAtlasScraper = false;
            \Log::info("Using cached Atlas Obscura data for {$city}, {$country}");
        }

        // Check if cache exists for Wikivoyage data
        $runWikiScraper = true;
        if (File::exists($wikiCache)) {
            File::copy($wikiCache, $wikiDataPath);
            $runWikiScraper = false;
            \Log::info("Using cached Wikivoyage data for {$city}, {$country}");
        }

        // Check if cache exists for Lonely Planet data
        $runLonelyPlanetScraper = true;
        if (File::exists($lonelyPlanetCache)) {
            File::copy($lonelyPlanetCache, $lonelyPlanetDataPath);
            $runLonelyPlanetScraper = false;
            \Log::info("Using cached Lonely Planet data for {$city}, {$country}");
        }

        // Run scrapers only if needed
        $cityArg = escapeshellarg($city);
        $countryArg = escapeshellarg($country);

        if ($runAtlasScraper) {
            \Log::info("Running Atlas Obscura scraper for {$city}, {$country}");
            exec("cd " . base_path() . " && node scrapers/scraper.js {$cityArg} {$countryArg}", $output1, $return_var1);
            if ($return_var1 !== 0) {
                return response()->json(['error' => 'Failed to run Atlas Obscura scraper script'], 500);
            }
        }

        if ($runWikiScraper) {
            \Log::info("Running Wikivoyage scraper for {$city}");
            exec("cd " . base_path() . " && node scrapers/scraper-2.js {$cityArg} {$countryArg}", $output2, $return_var2);
            if ($return_var2 !== 0) {
                return response()->json(['error' => 'Failed to run Wikivoyage scraper script'], 500);
            }
        }

        if ($runLonelyPlanetScraper) {
            \Log::info("Running Lonely Planet scraper for {$city}, {$country}");
            exec("cd " . base_path() . " && node scrapers/scraper-3.js {$cityArg} {$countryArg}", $output3, $return_var3);
            if ($return_var3 !== 0) {
                return response()->json(['error' => 'Failed to run Lonely Planet scraper script'], 500);
            }
        }

        // OpenAI API setup
        $apiKey = env('OPENAI_API_KEY');
        if (!$apiKey) {
            return response()->json(['error' => 'OpenAI API key is not set'], 500);
        }
        $client = OpenAI::client($apiKey);

        // Check if scraped data files exist
        if (!File::exists($atlasDataPath) || !File::exists($wikiDataPath) || !File::exists($lonelyPlanetDataPath)) {
            return response()->json(['error' => 'Scraped data files do not exist'], 500);
        }

        $selectedCards = json_decode(File::get($atlasDataPath), true);
        $wikiData = json_decode(File::get($wikiDataPath), true);
        $lonelyPlanetData = json_decode(File::get($lonelyPlanetDataPath), true);

        // Format content for AI request
        $cardsContent = "";
        foreach ($selectedCards as $card) {
            $timeSpent = isset($card['time_spent']) ? "{$card['time_spent']} minutes" : "Time not specified";
            $cardsContent .= "{$card['name']}\nDescription: {$card['description']}\nDuration: $timeSpent\n\n";
        }

        // Extract Wiki data
        $wikiContent = "";
        foreach ($wikiData as $section => $items) {
            $wikiContent .= "--- {$section} ---\n";
            foreach ($items as $item) {
                $wikiContent .= "• {$item}\n";
            }
            $wikiContent .= "\n";
        }

        // Extract Lonely Planet data
        $lonelyPlanetContent = "";
        foreach ($lonelyPlanetData as $place) {
            $name = $place['name'] ?? 'Unknown Name';
            $description = $place['description'] ?? 'No description available';
            $lonelyPlanetContent .= "{$name}\nDescription: {$description}\n\n";
        }

        // AI-generated travel plan
        $response = $client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a travel assistant that creates structured, engaging itineraries with full-day activities, walking times, and meal breaks.'],
                ['role' => 'user', 'content' => "Create a structured itinerary for $city, $country from $startDate to $endDate.
        Format it as follows:

        Day X:
        - 9:00 AM - Activity Name
        - 11:00 AM - Activity Name
        - 1:00 PM - Lunch Break
        - 3:00 PM - Activity Name
        - 6:00 PM - Dinner
        - 8:00 PM - Evening Activity

        I want you to give suggestions from the data I provide.
        For each activity, lunch break and dinner provide a brief description, the best time to visit, address and phone number.
        Make sure every day has all of the above elements.

        Use the following data:
        $cardsContent
        $wikiContent
        $lonelyPlanetContent

        Generate a detailed itinerary for each day from $startDate to $endDate."],
            ],
            'max_tokens' => 4096,
        ]);

        $travelPlan = $response['choices'][0]['message']['content'] ?? 'No plan generated.';

        // Prepare location data for the view
        $locations = [];
        foreach ($selectedCards as $card) {
            $locations[] = [
                'name' => $card['name'],
                'description' => $card['description']
            ];
        }

        // Add wiki data to locations array
        foreach ($wikiData as $section => $items) {
            foreach ($items as $item) {
                $locations[] = [
                    'name' => "Wikivoyage: {$section}",
                    'description' => $item
                ];
            }
        }

        // Add Lonely Planet data to locations array
        foreach ($lonelyPlanetData as $place) {
            $name = $place['name'] ?? 'Unknown Name';
            $description = $place['description'] ?? 'No description available';
            $locations[] = [
                'name' => "Lonely Planet: {$name}",
                'description' => $description
            ];
        }

        return view('trip.plan', compact('travelPlan', 'locations', 'city', 'country', 'startDate', 'endDate'));
    }
}
