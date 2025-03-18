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
        $yelpCache = base_path("scrapers/cache/{$cityCountryKey}-yelp.json");

        // Define data file paths
        $atlasDataPath = base_path('scrapers/atlas-data.json');
        $wikiDataPath = base_path('scrapers/wikivoyage_data.json');
        $lonelyPlanetDataPath = base_path('scrapers/lonelyplanet_data.json');
        $yelpDataPath = base_path('scrapers/yelp_data.json');

        // Check if cache exists for Atlas Obscura data
        $runAtlasScraper = true;
        if (File::exists($atlasCache)) {
            File::copy($atlasCache, $atlasDataPath);
            $runAtlasScraper = false;
        }

        // Check if cache exists for Wikivoyage data
        $runWikiScraper = true;
        if (File::exists($wikiCache)) {
            File::copy($wikiCache, $wikiDataPath);
            $runWikiScraper = false;
        }

        // Check if cache exists for Lonely Planet data
        $runLonelyPlanetScraper = true;
        if (File::exists($lonelyPlanetCache)) {
            File::copy($lonelyPlanetCache, $lonelyPlanetDataPath);
            $runLonelyPlanetScraper = false;
        }

        // Check if cache exists for Yelp data
        $runYelpScraper = true;
        if (File::exists($yelpCache)) {
            File::copy($yelpCache, $yelpDataPath);
            $runYelpScraper = false;
        }

        // Run scrapers only if needed
        $cityArg = escapeshellarg($city);
        $countryArg = escapeshellarg($country);

        if ($runAtlasScraper) {
            exec("cd " . base_path() . " && node scrapers/scraper.js {$cityArg} {$countryArg}", $output1, $return_var1);
        }

        if ($runWikiScraper) {
            exec("cd " . base_path() . " && node scrapers/scraper-2.js {$cityArg} {$countryArg}", $output2, $return_var2);
        }

        if ($runLonelyPlanetScraper) {
            exec("cd " . base_path() . " && node scrapers/scraper-3.js {$cityArg} {$countryArg}", $output3, $return_var3);
        }

        if ($runYelpScraper) {
            exec("cd " . base_path() . " && node scrapers/scraper-4.js {$cityArg} {$countryArg}", $output4, $return_var4);
        }

        // OpenAI API setup
        $apiKey = env('OPENAI_API_KEY');
        if (!$apiKey) {
            return response()->json(['error' => 'OpenAI API key is not set'], 500);
        }
        $client = OpenAI::client($apiKey);

        // Check if scraped data files exist
        if (!File::exists($atlasDataPath) || !File::exists($wikiDataPath) || !File::exists($lonelyPlanetDataPath) || !File::exists($yelpDataPath)) {
            return response()->json(['error' => 'Scraped data files do not exist'], 500);
        }

        $selectedCards = json_decode(File::get($atlasDataPath), true);
        $wikiData = json_decode(File::get($wikiDataPath), true);
        $lonelyPlanetData = json_decode(File::get($lonelyPlanetDataPath), true);
        $yelpData = json_decode(File::get($yelpDataPath), true);

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

        // Extract Yelp restaurant data
        $yelpContent = "";
        if (isset($yelpData['restaurants'])) {
            foreach ($yelpData['restaurants'] as $restaurant) {
                $name = $restaurant['name'] ?? 'Unknown Restaurant';
                $rating = $restaurant['rating'] ?? 'No rating available';
                $location = $restaurant['location'] ?? 'No location available';
                $yelpContent .= "{$name}\nRating: {$rating}\nLocation: {$location}\n\n";
            }
        }

        // Log Yelp content to the console
        error_log("Yelp Content: \n" . $yelpContent);

        // Calculate the date range
        $startDateObj = \Carbon\Carbon::parse($startDate);
        $endDateObj = \Carbon\Carbon::parse($endDate);
        $dateRange = $startDateObj->diffInDays($endDateObj) + 1; // Inclusive of both start and end dates

        // AI-generated travel plan for each day
        $travelPlan = '';
        $dayCounter = 1; // To handle day number
        for ($i = 0; $i < $dateRange; $i++) {
            $currentDate = $startDateObj->addDays($i)->format('Y-m-d');

            // Generate daily itinerary for each day
            $response = $client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a travel assistant that creates structured, engaging itineraries with full-day activities, walking times, and meal breaks.'],
                    ['role' => 'user', 'content' => "Create a detailed itinerary for $city, $country on $currentDate.
                    Format it as follows:

                    Day {$dayCounter}:
                    - 9:00 AM - Activity Name
                    - 11:00 AM - Activity Name
                    - 1:00 PM - Lunch Break
                    - 3:00 PM - Activity Name
                    - 6:00 PM - Dinner
                    - 8:00 PM - Evening Activity

                    I want you to give suggestions from the data I provide.
                    For each activity, lunch break, and dinner, provide a brief description, the best time to visit, address, and phone number.

                    For the lunch break and dinner, suggest a restaurant from the Yelp data.
                    Use the following data:
                    $cardsContent
                    $wikiContent
                    $lonelyPlanetContent
                    $yelpContent
                    "],
                ],
                'max_tokens' => 1024, // Adjust the token limit as necessary
            ]);

            $dayPlan = $response['choices'][0]['message']['content'] ?? 'No plan generated for this day.';
            $travelPlan .= "\n\n$dayPlan";
            $dayCounter++; // Increment day count
        }

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

        // Add Yelp data to locations array
        foreach ($yelpData as $restaurant) {
            $name = $restaurant['name'] ?? 'Unknown Restaurant';
            $rating = $restaurant['rating'] ?? 'No rating available';
            $location = $restaurant['location'] ?? 'No location available';
            $locations[] = [
                'name' => "Yelp: {$name}",
                'description' => "Rating: {$rating}\nLocation: {$location}",
            ];
        }

        return view('trip.plan', compact('travelPlan', 'locations', 'city', 'country', 'startDate', 'endDate'));
    }
}
