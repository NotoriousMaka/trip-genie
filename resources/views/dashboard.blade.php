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
            background-color: #000;
            color: #fff;
            justify-content: center;
            align-items: center;
        }

        main {
            flex: 1;
            width: 100%;
        }

        .form-container {
            background: #111;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(255, 255, 255, 0.1);
            width: 100%;
            max-width: 500px;
            text-align: center;
            margin: 0 auto;
        }

        .btn {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            background: #fff;
            color: #000;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn:hover {
            background: #ddd;
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

        .submit-btn
        {
            background-color: #333;
            color: #fff;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .submit-btn:hover
        {
            background-color: #555;
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
                <li><a href="#profile">Profile</a></li>
                <li><a href="#settings">Settings</a></li>
            </ul>
        </nav>
    </div>
</header>

<main>
    <section id="dashboard" class="bg-black py-20">
        <div class="container px-4 text-center shadow-lg rounded-lg p-8 max-w-5xl mx-auto" style="background: #111;">
            <h2 class="text-5xl font-extrabold mb-6">Welcome to Your Dashboard</h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Manage your trips, view your profile, and adjust your settings all in one place.
            </p>
            <p class="text-lg text-gray-600">Name: {{ Auth::user()->name }}</p>
            <p class="text-lg text-gray-600">Email: {{ Auth::user()->email }}</p>
        </div>
    </section>

    <section id="create-plan" class="bg-black py-20">
        <div class="container px-4 text-center shadow-lg rounded-lg p-8 max-w-5xl mx-auto" style="background: #111;">
            <h2 class="text-5xl font-extrabold mb-6">Create Your Travel Plan</h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto mb-8">
                Discover the world with TripGenie, your trusted AI travel friend.
            </p>

            <form action="{{ route('create-plan') }}" method="POST">
                @csrf
                <div class="space-y-6">
                    <div class="flex space-x-6">
                        <div class="w-1/2">
                            <label for="city" class="block text-gray-600 font-medium mb-2">City</label>
                            <input type="text" id="city" name="city" placeholder="Enter city" class="w-full border border-gray-300 rounded-lg p-3 bg-white text-gray-600">
                        </div>

                        <div class="w-1/2">
                            <label for="country" class="block text-gray-600 font-medium mb-2">Country</label>
                            <input type="text" id="country" name="country" placeholder="Enter country" class="w-full border border-gray-300 rounded-lg p-3 bg-white text-gray-600">
                        </div>
                    </div>

                    <div class="flex space-x-6">
                        <div class="w-1/2">
                            <label for="start_date" class="block text-gray-600 font-medium mb-2">Start Date</label>
                            <input type="date" id="start_date" name="start_date" class="w-full border border-gray-300 rounded-lg p-3 bg-white text-gray-600">
                        </div>

                        <div class="w-1/2">
                            <label for="end_date" class="block text-gray-600 font-medium mb-2">End Date</label>
                            <input type="date" id="end_date" name="end_date" class="w-full border border-gray-300 rounded-lg p-3 bg-white text-gray-600">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-center">
                        <button type="submit" class="submit-btn">
                            Explore Now
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>


    <section id="settings" class="py-20 flex justify-center items-center">
        <div class="container mx-auto px-4">
            <div class="form-container text-center">
                <h3 class="text-4xl font-bold mb-6">Settings</h3>
                <form method="POST" action="{{ route('logout') }}" class="mb-4">
                    @csrf
                    <button type="submit" class="btn">Logout</button>
                </form>
                <form method="GET" action="{{}}">

                </form>
            </div>
        </div>
    </section>
</main>
</body>
</html>
