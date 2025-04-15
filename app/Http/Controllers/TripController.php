<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
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
            'preference' => 'string|in:adventure,relaxation,culture,nature,food', // Not used right now.
        ]);

        $city = $request->input('city');
        $country = $request->input('country');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $combined_location = strtolower($city) . '-' . strtolower($country);
        $atlasCache = base_path("scrapers/cache/{$combined_location}-atlas.json");
        $wikiCache = base_path("scrapers/cache/{$combined_location}-wikivoyage.json");
        $lonelyPlanetCache = base_path("scrapers/cache/{$combined_location}-lonelyplanet.json");
        $yelpCache = base_path("scrapers/cache-play/{$combined_location}-yelp.json");

        $atlas_path = base_path('scrapers/atlas-data.json');
        $wiki_path = base_path('scrapers/wikivoyage_data.json');
        $lonely_path = base_path('scrapers/lonelyplanet_data.json');
        $yelp_path = base_path('scrapers/yelp_data.json');

        $run_atlas = true;
        if (File::exists($atlasCache)) {
            File::copy($atlasCache, $atlas_path);
            $run_atlas = false;
        }

        $run_wiki = true;
        if (File::exists($wikiCache)) {
            File::copy($wikiCache, $wiki_path);
            $run_wiki = false;
        }

        $run_lonely = true;
        if (File::exists($lonelyPlanetCache)) {
            File::copy($lonelyPlanetCache, $lonely_path);
            $run_lonely = false;
        }

        $run_yelp = true;
        if (File::exists($yelpCache)) {
            File::copy($yelpCache, $yelp_path);
            $run_yelp = false;
        }

        $city_argument = escapeshellarg($city);
        $country_argument = escapeshellarg($country);

        if ($run_atlas) {
            exec("cd " . base_path() . " && node scrapers/scraper.js {$city_argument} {$country_argument}", $output1, $return_var1);
        }

        if ($run_wiki) {
            exec("cd " . base_path() . " && node scrapers/scraper-2.js {$city_argument} {$country_argument}", $output2, $return_var2);
        }

        if ($run_lonely) {
            exec("cd " . base_path() . " && node scrapers/scraper-3.js {$city_argument} {$country_argument}", $output3, $return_var3);
        }

        if ($run_yelp) {
            exec("cd " . base_path() . " && node scrapers/scraper-4-play.js {$city_argument} {$country_argument}", $output4, $return_var4);
        }

        $api = env('OPENAI_API_KEY');
        $client = OpenAI::client($api);

        if (!File::exists($atlas_path) || !File::exists($wiki_path) || !File::exists($lonely_path) || !File::exists($yelp_path)) {
            return response()->json(['error' => 'Scraped data files do not exist'], 500);
        }

        $atlas_data = json_decode(File::get($atlas_path), true);
        $wikiData = json_decode(File::get($wiki_path), true);
        $lonely_data = json_decode(File::get($lonely_path), true);
        $yelp_data = json_decode(File::get($yelp_path), true);

        $cards = "";
        foreach ($atlas_data as $card) {
            $timeSpent = isset($card['time_spent']) ? "{$card['time_spent']} minutes" : "Time not specified";
            $cards .= "{$card['name']}\nDescription: {$card['description']}\nDuration: $timeSpent\n\n";
        }

        $wiki_ifnromation = "";
        foreach ($wikiData as $section => $items) {
            $wiki_ifnromation .= "--- {$section} ---\n";
            foreach ($items as $item) {
                $wiki_ifnromation .= "• {$item}\n";
            }
            $wiki_ifnromation .= "\n";
        }

        $lonely_information = "";
        foreach ($lonely_data as $place) {
            $name = $place['name'] ?? 'Unknown Name';
            $description = $place['description'] ?? 'No description available';
            $lonely_information .= "{$name}\nDescription: {$description}\n\n";
        }

        $yelp_information = "";
        if (isset($yelp_data['restaurants'])) {
            foreach ($yelp_data['restaurants'] as $restaurant) {
                $name = $restaurant['name'] ?? 'Unknown Restaurant';
                $rating = $restaurant['rating'] ?? 'No rating available';
                $location = $restaurant['location'] ?? 'No location available';
                $yelp_information .= "{$name}\nRating: {$rating}\nLocation: {$location}\n\n";
            }
        }

        $start_date = Carbon::parse($startDate);
        $end_date = Carbon::parse($endDate);
        $date_range = $start_date->diffInDays($end_date) + 1;

        $travelPlan = '';
        $day_number = 1;
        for ($i = 0; $i < $date_range; $i++) {
            $today = $start_date->addDays($i)->format('Y-m-d');

            $response = $client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a travel assistant that creates structured, engaging itineraries with full-day activities, walking times, and meal breaks.'],
                    ['role' => 'user', 'content' => "Create a detailed itinerary for $city, $country on $today.
                    Format it as follows:

                    Day {$day_number}:
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
                    $cards
                    $wiki_ifnromation
                    $lonely_information
                    $yelp_information
                    "],
                ],
                'max_tokens' => 4096,
            ]);

            $dayPlan = $response['choices'][0]['message']['content'] ?? 'No plan generated for this day.';
            $travelPlan .= "\n\n$dayPlan";
            $day_number++;
        }

        $locations = [];
        foreach ($atlas_data as $card) {
            $locations[] = [
                'name' => $card['name'],
                'description' => $card['description']
            ];
        }

        foreach ($wikiData as $section => $items) {
            foreach ($items as $item) {
                $locations[] = [
                    'name' => "Wikivoyage: {$section}",
                    'description' => $item
                ];
            }
        }

        foreach ($lonely_data as $place) {
            $name = $place['name'] ?? 'Unknown Name';
            $description = $place['description'] ?? 'No description available';
            $locations[] = [
                'name' => "Lonely Planet: {$name}",
                'description' => $description
            ];
        }

        foreach ($yelp_data as $restaurant) {
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
