<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TripGenie - AI Travel Planner</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        main {
            flex: 1;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">
<header class="bg-blue-600 text-white py-6">
    <div class="container mx-auto flex justify-between items-center px-4">
        <h1 class="text-3xl font-bold">TripGenie</h1>
        <nav>
            <ul class="flex space-x-4">
                <li><a href="#about" class="hover:text-blue-300">About</a></li>
                <li><a href="#features" class="hover:text-blue-300">Features</a></li>
                <li><a href="#contact" class="hover:text-blue-300">Contact</a></li>
            </ul>
        </nav>
    </div>
</header>

<main>
    <section id="about" class="bg-white flex-grow flex items-center justify-center">
        <div class="text-center">
            <h2 class="text-4xl font-bold mb-6">Your Personal Travel Companion</h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                TripGenie is an AI-powered platform that simplifies travel planning. From personalised itineraries to real-time updates, TripGenie makes your travel experience seamless and enjoyable.
            </p>
        </div>
    </section>

    <section id="contact" class="py-16 bg-white">
        <div class="container mx-auto px-4 text-center">
            <h3 class="text-3xl font-bold mb-6">Get in Touch</h3>
            <p class="text-lg text-gray-600">Have questions? Reach out to us and start planning your next adventure with TripGenie!</p>
            <a href="mailto:info@tripgenie.com" class="mt-4 inline-block bg-blue-600 text-white px-6 py-3 rounded-lg shadow hover:bg-blue-700">Contact Us</a>
        </div>
    </section>
</main>

<footer class="bg-blue-600 text-white py-6">
    <div class="container mx-auto text-center">
        <p>&copy; 2025 TripGenie. All Rights Reserved.</p>
    </div>
</footer>
</body>
</html>
