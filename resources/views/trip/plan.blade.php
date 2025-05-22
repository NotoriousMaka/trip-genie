<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TripGenie - AI Travel Planner</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css" rel="stylesheet">
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js"></script>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #000;
            color: #fff;
            justify-content: center;
            align-items: center;
        }

        main {
            flex: 1;
            width: 100%;
        }

        section {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        header nav ul li a {
            background-color: #333;
            color: #fff;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        header nav ul li a:hover {
            background-color: #555;
        }

        .map-container {
            width: 100%;
            height: 500px;
        }

        .map-container {
            width: 100%;
            height: 400px;
            border-radius: 10px;
            overflow: hidden;
        }
    </style>
</head>
<body class="text-white">

<header class="bg-transparent text-white py-6">
    <div class="container mx-auto flex justify-between items-center px-4">
        <img src="{{ asset('logos/logo-transparent-white.png') }}" alt="{{ config('app.name', 'Laravel') }}" class="h-16 w-auto">
        <nav>
            <ul class="flex space-x-4">
                <li><a href="/">Home</a></li>
                <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li><a href="#settings">Settings</a></li>
            </ul>
        </nav>
    </div>
</header>

<main>
    <section id="trip-plan" class="bg-black py-20">
        <div class="container px-4 text-center shadow-lg rounded-lg p-8 max-w-5xl mx-auto" style="background: #111;">
            <h2 class="text-5xl font-extrabold mb-6">Your Travel Plan</h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Here is your travel plan, carefully created for your next adventure.
            </p>
        </div>
    </section>

    <section id="itinerary-section" class="bg-black py-20">
        <div class="container px-4 shadow-lg rounded-lg p-8 max-w-5xl mx-auto" style="background: #111;">
            <h3 class="text-3xl font-bold mb-6 text-center">Your Customised Itinerary</h3>
            <div class="text-lg text-gray-600">
                {!! nl2br(e($travelPlan)) !!} {{-- !! output raw, unescaped HTML --}}
            </div>
        </div>
    </section>

    <section id="map-section" class="bg-black py-20">
        <div class="container px-4 shadow-lg rounded-lg p-8 max-w-5xl mx-auto" style="background: #111;">
            <h3 class="text-3xl font-bold mb-6 text-center">Trip Map</h3>
            <div id="map" class="map-container"></div>
        </div>
    </section>
</main>
<script>
    mapboxgl.accessToken = 'pk.eyJ1Ijoibm90b3Jpb3VzbWFrYSIsImEiOiJjbWF5bThkNXcwOTBpMmtxdXF4OTgxaHQwIn0.hf_wgqMowPyesvpIS6XtNA';

    const city = @json($city);
    const country = @json($country);

    fetch(`https://api.mapbox.com/geocoding/v5/mapbox.places/${city},${country}.json?access_token=${mapboxgl.accessToken}`)
        .then(response => response.json())
        .then(data => {
            if (data.features && data.features.length > 0) {
                const center = data.features[0].center;

                const map = new mapboxgl.Map({
                    container: 'map',
                    style: 'mapbox://styles/mapbox/dark-v10',
                    center: center,
                    zoom: 11.5
                });

                new mapboxgl.Marker({ color: '#FFFFFF' })
                    .setLngLat(center)
                    .setPopup(new mapboxgl.Popup().setText(`${city}, ${country}`))
                    .addTo(map);
            } else {
                console.error('City not found on Mapbox.');
            }
        })
        .catch(error => console.error('Error fetching geolocation:', error));
</script>
</body>
</html>
