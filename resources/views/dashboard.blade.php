<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TripGenie - Dashboard</title>
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
                <li><a href="#profile" class="bg-blue-600 text-white rounded-lg p-3 hover:bg-blue-700">Profile</a></li>
                <li><a href="#settings" class="bg-blue-600 text-white rounded-lg p-3 hover:bg-blue-700">Settings</a></li>
            </ul>
        </nav>
    </div>
</header>

<main>
    <section id="dashboard" class="bg-white bg-opacity-80 py-20 transparent-bg">
        <div class="container px-4 text-center bg-white shadow-lg rounded-lg p-8 max-w-5xl mx-auto">
            <h2 class="text-5xl font-extrabold mb-6">Welcome to Your Dashboard</h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Manage your trips, view your profile, and adjust your settings all in one place.
            </p>
        </div>
    </section>

    <section id="profile" class="bg-gray-100 bg-opacity-0 py-20">
        <div class="container mx-auto px-4 text-center">
            <div class="bg-white shadow-lg rounded-lg p-8 max-w-2xl mx-auto">
                <h3 class="text-4xl font-bold mb-6">Your Profile</h3>
                <p class="text-lg text-gray-600">Name: {{ Auth::user()->name }}</p>
                <p class="text-lg text-gray-600">Email: {{ Auth::user()->email }}</p>
            </div>
        </div>
    </section>

    <section id="settings" class="py-20 flex justify-center items-center">
        <div class="container mx-auto px-4">
            <div class="bg-white shadow-lg rounded-lg p-8 max-w-2xl mx-auto text-center">
                <h3 class="text-4xl font-bold mb-6">Settings</h3>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="bg-blue-600 text-white rounded-lg p-3 hover:bg-blue-700">Logout</button>
                </form>
                <a href="{{ route('my-trips') }}" class="text-blue-600 hover:text-blue-800">My Trips</a>
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
    </div>
</footer>
</body>
</html>
