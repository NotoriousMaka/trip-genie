<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenAI;
use Illuminate\Support\Facades\File;

class TripController extends Controller
{
    // Country to currency mapping
    private $countryCurrency = [
        // ... (full mapping as in your code)
        'afghanistan' => 'AFN', 'albania' => 'ALL', 'algeria' => 'DZD', 'andorra' => 'EUR', 'angola' => 'AOA',
        'argentina' => 'ARS', 'armenia' => 'AMD', 'australia' => 'AUD', 'austria' => 'EUR', 'azerbaijan' => 'AZN',
        'bahamas' => 'BSD', 'bahrain' => 'BHD', 'bangladesh' => 'BDT', 'belarus' => 'BYN', 'belgium' => 'EUR',
        'belize' => 'BZD', 'benin' => 'XOF', 'bhutan' => 'BTN', 'bolivia' => 'BOB', 'bosnia and herzegovina' => 'BAM',
        'botswana' => 'BWP', 'brazil' => 'BRL', 'brunei' => 'BND', 'bulgaria' => 'BGN', 'burkina faso' => 'XOF',
        'burundi' => 'BIF', 'cambodia' => 'KHR', 'cameroon' => 'XAF', 'canada' => 'CAD', 'cape verde' => 'CVE',
        'central african republic' => 'XAF', 'chad' => 'XAF', 'chile' => 'CLP', 'china' => 'CNY', 'colombia' => 'COP',
        'comoros' => 'KMF', 'congo' => 'XAF', 'costa rica' => 'CRC', 'croatia' => 'EUR', 'cuba' => 'CUP',
        'cyprus' => 'EUR', 'czech republic' => 'CZK', 'denmark' => 'DKK', 'djibouti' => 'DJF', 'dominica' => 'XCD',
        'dominican republic' => 'DOP', 'east timor' => 'USD', 'ecuador' => 'USD', 'egypt' => 'EGP', 'el salvador' => 'USD',
        'equatorial guinea' => 'XAF', 'eritrea' => 'ERN', 'estonia' => 'EUR', 'eswatini' => 'SZL', 'ethiopia' => 'ETB',
        'fiji' => 'FJD', 'finland' => 'EUR', 'france' => 'EUR', 'gabon' => 'XAF', 'gambia' => 'GMD', 'georgia' => 'GEL',
        'germany' => 'EUR', 'ghana' => 'GHS', 'greece' => 'EUR', 'grenada' => 'XCD', 'guatemala' => 'GTQ',
        'guinea' => 'GNF', 'guinea-bissau' => 'XOF', 'guyana' => 'GYD', 'haiti' => 'HTG', 'honduras' => 'HNL',
        'hungary' => 'HUF', 'iceland' => 'ISK', 'india' => 'INR', 'indonesia' => 'IDR', 'iran' => 'IRR',
        'iraq' => 'IQD', 'ireland' => 'EUR', 'israel' => 'ILS', 'italy' => 'EUR', 'ivory coast' => 'XOF',
        'jamaica' => 'JMD', 'japan' => 'JPY', 'jordan' => 'JOD', 'kazakhstan' => 'KZT', 'kenya' => 'KES',
        'kiribati' => 'AUD', 'kosovo' => 'EUR', 'kuwait' => 'KWD', 'kyrgyzstan' => 'KGS', 'laos' => 'LAK',
        'latvia' => 'EUR', 'lebanon' => 'LBP', 'lesotho' => 'LSL', 'liberia' => 'LRD', 'libya' => 'LYD',
        'liechtenstein' => 'CHF', 'lithuania' => 'EUR', 'luxembourg' => 'EUR', 'madagascar' => 'MGA',
        'malawi' => 'MWK', 'malaysia' => 'MYR', 'maldives' => 'MVR', 'mali' => 'XOF', 'malta' => 'EUR',
        'marshall islands' => 'USD', 'mauritania' => 'MRU', 'mauritius' => 'MUR', 'mexico' => 'MXN',
        'micronesia' => 'USD', 'moldova' => 'MDL', 'monaco' => 'EUR', 'mongolia' => 'MNT', 'montenegro' => 'EUR',
        'morocco' => 'MAD', 'mozambique' => 'MZN', 'myanmar' => 'MMK', 'namibia' => 'NAD', 'nauru' => 'AUD',
        'nepal' => 'NPR', 'netherlands' => 'EUR', 'new zealand' => 'NZD', 'nicaragua' => 'NIO', 'niger' => 'XOF',
        'nigeria' => 'NGN', 'north korea' => 'KPW', 'north macedonia' => 'MKD', 'norway' => 'NOK', 'oman' => 'OMR',
        'pakistan' => 'PKR', 'palau' => 'USD', 'palestine' => 'ILS', 'panama' => 'PAB', 'papua new guinea' => 'PGK',
        'paraguay' => 'PYG', 'peru' => 'PEN', 'philippines' => 'PHP', 'poland' => 'PLN', 'portugal' => 'EUR',
        'qatar' => 'QAR', 'romania' => 'RON', 'russia' => 'RUB', 'rwanda' => 'RWF', 'saint kitts and nevis' => 'XCD',
        'saint lucia' => 'XCD', 'saint vincent and the grenadines' => 'XCD', 'samoa' => 'WST', 'san marino' => 'EUR',
        'sao tome and principe' => 'STN', 'saudi arabia' => 'SAR', 'senegal' => 'XOF', 'serbia' => 'RSD',
        'seychelles' => 'SCR', 'sierra leone' => 'SLL', 'singapore' => 'SGD', 'slovakia' => 'EUR', 'slovenia' => 'EUR',
        'solomon islands' => 'SBD', 'somalia' => 'SOS', 'south africa' => 'ZAR', 'south korea' => 'KRW',
        'south sudan' => 'SSP', 'spain' => 'EUR', 'sri lanka' => 'LKR', 'sudan' => 'SDG', 'suriname' => 'SRD',
        'sweden' => 'SEK', 'switzerland' => 'CHF', 'syria' => 'SYP', 'taiwan' => 'TWD', 'tajikistan' => 'TJS',
        'tanzania' => 'TZS', 'thailand' => 'THB', 'togo' => 'XOF', 'tonga' => 'TOP', 'trinidad and tobago' => 'TTD',
        'tunisia' => 'TND', 'turkey' => 'TRY', 'turkmenistan' => 'TMT', 'tuvalu' => 'AUD', 'uganda' => 'UGX',
        'ukraine' => 'UAH', 'united arab emirates' => 'AED', 'united kingdom' => 'GBP', 'united states' => 'USD',
        'uruguay' => 'UYU', 'uzbekistan' => 'UZS', 'vanuatu' => 'VUV', 'vatican city' => 'EUR', 'venezuela' => 'VES',
        'vietnam' => 'VND', 'yemen' => 'YER', 'zambia' => 'ZMW', 'zimbabwe' => 'ZWL'
    ];

    public function store(Request $request)
    {
        $request->validate([
            'city' => 'required|string',
            'country' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'preference' => 'string|in:adventure,relaxation,culture,nature,food',
        ]);

        $city = $request->input('city');
        $country = $request->input('country');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $combined_location = strtolower($city) . '-' . strtolower($country);
        $city_argument = escapeshellarg($city);
        $country_argument = escapeshellarg($country);

        $atlas_path = base_path("scrapers/cache/{$combined_location}-atlas.json");
        $wiki_path = base_path("scrapers/cache/{$combined_location}-wikivoyage.json");
        $lonely_path = base_path("scrapers/cache/{$combined_location}-lonelyplanet.json");
        $yelp_path = base_path("scrapers/cache-play/{$combined_location}-yelp.json");

        // Ensure all data is scraped
        if (!File::exists($atlas_path)) {
            exec("cd " . base_path() . " && node scrapers/scraper.js {$city_argument} {$country_argument}");
        }
        if (!File::exists($wiki_path)) {
            exec("cd " . base_path() . " && node scrapers/scraper-2.js {$city_argument} {$country_argument}");
        }
        if (!File::exists($lonely_path)) {
            exec("cd " . base_path() . " && node scrapers/scraper-3.js {$city_argument} {$country_argument}");
        }
        if (!File::exists($yelp_path)) {
            exec("cd " . base_path() . " && node scrapers/scraper-4-play.js {$city_argument} {$country_argument}");
        }

        if (!File::exists($atlas_path) || !File::exists($wiki_path) || !File::exists($lonely_path) || !File::exists($yelp_path)) {
            return response()->json(['error' => 'Scraped data files do not exist'], 500);
        }

        $api = env('OPENAI_API_KEY');
        $client = OpenAI::client($api);

        $atlas_data = json_decode(File::get($atlas_path), true) ?? [];
        $wikiData = json_decode(File::get($wiki_path), true) ?? [];
        $lonely_data = json_decode(File::get($lonely_path), true) ?? [];
        $yelp_data = json_decode(File::get($yelp_path), true) ?? [];

        // Weather scraper
        $weather_output = [];
        exec("cd " . base_path() . " && node scrapers/weather-play.js {$country_argument} {$city_argument}", $weather_output);
        $weather_json = implode("", $weather_output);
        $weather_data = json_decode($weather_json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Weather JSON decode error: ' . json_last_error_msg(), ['output' => $weather_json]);
            $weather_data = null;
        }

        // Currency scraper
        $countryKey = strtolower($country);
        $toCurrency = $this->countryCurrency[$countryKey] ?? 'USD';
        $currency_output = [];
        exec("cd " . base_path() . " && node scrapers/currency-play.js GBP {$toCurrency} 1", $currency_output);
        $currency_json = implode("", $currency_output);
        $currency_data = json_decode($currency_json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Currency JSON decode error: ' . json_last_error_msg(), ['output' => $currency_json]);
            $currency_data = null;
        }

        // --- Itinerary and locations logic (as in your original code) ---
        $atlas_information = "";
        foreach ($atlas_data as $card) {
            $timeSpent = isset($card['time_spent']) ? "{$card['time_spent']} minutes" : "Time not specified";
            $atlas_information .= "{$card['name']}\nDescription: {$card['description']}\nDuration: $timeSpent\n\n";
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

        // Total number of days
        $start_date = Carbon::parse($startDate);
        $end_date = Carbon::parse($endDate);
        $date_range = $start_date->diffInDays($end_date) + 1;

        $travelPlan = '';
        $day_number = 1;
        $used_activities = [];
        for ($i = 0; $i < $date_range; $i++) {
            $today = $start_date->copy()->addDays($i)->format('Y-m-d');
            $previously_used = implode(", ", $used_activities);

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

            I want you to give suggestions ONLY from the data I provide.
            For each activity, lunch break, and dinner, provide a brief description, the best time to visit, address, and phone number.

            IMPORTANT: DO NOT use any of these previously suggested activities: $previously_used
            The activities should be completely different from each other and not repeated twice across the itinerary.

            For the lunch break and dinner, suggest a restaurant from the Yelp data.
            Use the following data:
            $atlas_information
            $wiki_ifnromation
            $lonely_information
            $yelp_information
            "],
                ],
                'max_tokens' => 4096,
            ]);

            $dayPlan = $response['choices'][0]['message']['content'] ?? 'No plan generated for this day.';

            preg_match_all('/- \d+:\d+ [AP]M - (.*?)[\r\n]/', $dayPlan, $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $activity) {
                    $used_activities[] = trim($activity);
                }
            }

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
        if (isset($yelp_data['restaurants'])) {
            foreach ($yelp_data['restaurants'] as $restaurant) {
                $name = $restaurant['name'] ?? 'Unknown Restaurant';
                $rating = $restaurant['rating'] ?? 'No rating available';
                $location = $restaurant['location'] ?? 'No location available';
                $locations[] = [
                    'name' => "Yelp: {$name}",
                    'description' => "Rating: {$rating}\nLocation: {$location}",
                ];
            }
        }

        return view('trip.plan', compact(
            'travelPlan',
            'locations',
            'city',
            'country',
            'startDate',
            'endDate',
            'weather_data',
            'currency_data',
            'toCurrency'
        ));
    }
}
