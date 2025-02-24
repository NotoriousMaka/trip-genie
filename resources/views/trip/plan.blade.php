<!-- resources/views/trip/plan.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TripGenie - AI Travel Planner</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        main {
            flex: 1;
        }
        .transparent-bg {
            background-color: transparent !important;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body class="text-gray-800" style="background-image: url('{{ asset('images/mountains.jpg') }}'); background-size: cover; background-position: center;">
<header class="bg-transparent text-white py-6 transparent-bg">
    <div class="container mx-auto flex justify-between items-center px-4">
        <img src="{{ asset('logos/logo-transparent-white.png') }}" alt="{{ config('app.name', 'Laravel') }}" class="h-16 w-auto">
        <nav>
            <ul class="flex space-x-4">
                <li><a href="/" class="bg-blue-600 text-white rounded-lg p-3 hover:bg-blue-700">Home</a></li>
                <li><a href="#about" class="bg-blue-600 text-white rounded-lg p-3 hover:bg-blue-700">About Us</a></li>
                <li><a href="#destinations" class="bg-blue-600 text-white rounded-lg p-3 hover:bg-blue-700">Destinations</a></li>
                <li><a href="{{ route('dashboard') }}" class="bg-blue-600 text-white rounded-lg p-3 hover:bg-blue-700">Profile</a></li>
            </ul>
        </nav>
    </div>
</header>

<main>
    <section id="about" class="bg-white bg-opacity-80 py-20 transparent-bg">
        <div class="container px-4 text-center bg-white shadow-lg rounded-lg p-8 max-w-5xl mx-auto">
            <h2 class="text-5xl font-extrabold mb-6">Your Travel Plan</h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Here is your travel plan, carefully crafted for your next adventure. Enjoy every moment and explore new horizons.
            </p>
        </div>
    </section>

    <section id="trip-plan" class="py-20 bg-white bg-opacity-80">
        <div class="container mx-auto px-4">
            <div class="bg-white shadow-lg rounded-lg p-8 max-w-2xl mx-auto">
                <h3 class="text-3xl font-bold mb-4">Your Customized Itinerary</h3>
                <div class="text-lg text-gray-600">
                    {!! nl2br(e($travelPlan)) !!}
                </div>
            </div>
        </div>
    </section>
</main>

<footer class="bg-blue-600 text-white py-2">
    <div class="container mx-auto flex flex-col items-center space-y-4">
        <div class="flex flex-col items-center space-y-1">
            <img src="{{ asset('logos/logo-transparent-white.png') }}" alt="{{ config('app.name', 'Laravel') }}" class="h-16 w-auto">
            <span class="text-l font-light">&copy; TripGenie - All Rights Reserved</span>
        </div>

        <div class="flex space-x-6">
            <a href="#" aria-label="Facebook" class="hover:text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="w-7 h-7" viewBox="0 0 24 24">
                    <path d="M22.675 0h-21.35c-.736 0-1.325.59-1.325 1.325v21.351c0 .735.59 1.324 1.325 1.324h11.495v-9.293h-3.125v-3.622h3.125v-2.672c0-3.097 1.891-4.788 4.655-4.788 1.325 0 2.462.099 2.795.143v3.243h-1.918c-1.506 0-1.796.717-1.796 1.767v2.307h3.591l-.467 3.622h-3.124v9.293h6.125c.735 0 1.325-.59 1.325-1.325v-21.35c0-.736-.59-1.326-1.325-1.326z"/>
                </svg>
            </a>
            <a href="#" aria-label="Instagram" class="hover:text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="w-7 h-7" viewBox="0 0 24 24">
                    <path d="M12 2.163c3.204 0 3.584.012 4.849.07 1.206.056 2.003.25 2.466.415a4.92 4.92 0 0 1 1.678.964c.42.391.766.859.964 1.678.165.463.36 1.26.415 2.466.059 1.266.071 1.646.071 4.849s-.012 3.584-.071 4.849c-.056 1.206-.25 2.003-.415 2.466a4.92 4.92 0 0 1-.964 1.678c-.391.42-.859.766-1.678.964-.463.165-1.26.36-2.466.415-1.266.059-1.646.071-4.849.071s-3.584-.012-4.849-.071c-1.206-.056-2.003-.25-2.466-.415a4.92 4.92 0 0 1-1.678-.964c-.42-.391-.766-.859-.964-1.678-.165-.463-.36-1.26-.415-2.466-.059-1.266-.071-1.646-.071-4.849s.012-3.584.071-4.849c.056-1.206.25-2.003.415-2.466a4.92 4.92 0 0 1 .964-1.678c.391-.42.859-.766 1.678-.964.463-.165 1.26-.36 2.466-.415 1.266-.059 1.646-.071 4.849-.071zm0-2.163c-3.271 0-3.67.013-4.947.072-1.262.059-2.129.252-2.87.532a6.992 6.992 0 0 0-2.509 1.568 6.992 6.992 0 0 0-1.568 2.509c-.28.741-.473 1.608-.532 2.87-.059 1.277-.072 1.676-.072 4.947s.013 3.67.072 4.947c.059 1.262.252 2.129.532 2.87a6.992 6.992 0 0 0 1.568 2.509 6.992 6.992 0 0 0 2.509 1.568c.741.28 1.608.473 2.87.532 1.277.059 1.676.072 4.947.072s3.67-.013 4.947-.072c1.262-.059 2.129-.252 2.87-.532a6.992 6.992 0 0 0 2.509-1.568 6.992 6.992 0 0 0 1.568-2.509c.28-.741.473-1.608.532-2.87.059-1.277.072-1.676.072-4.947s-.013-3.67-.072-4.947c-.059-1.262-.252-2.129-.532-2.87a6.992 6.992 0 0 0-1.568-2.509 6.992 6.992 0 0 0-2.509-1.568c-.741-.28-1.608-.473-2.87-.532-1.277-.059-1.676-.072-4.947-.072z"/>
                    <path d="M12 5.838a6.162 6.162 0 1 0 6.162 6.162 6.169 6.169 0 0 0-6.162-6.162zm0 10.162a4 4 0 1 1 4-4 4.007 4.007 0 0 1-4 4zm6.406-11.845a1.44 1.44 0 1 0 1.44 1.44 1.438 1.438 0 0 0-1.44-1.44z"/>
                </svg>
            </a>
        </div>
    </div>
</footer>
</body>
</html>
