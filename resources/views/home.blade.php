<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TripGenie - Travel Planner</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #000;
            color: #fff;
            flex-direction: column;
            text-align: center;
        }
        .logo-container {
            display: flex;
            flex-direction: column;
        }
        .logo-container img {
            width: 300px;
            height: auto;
            margin-bottom: 1rem;
        }
        .explore-btn {
            background: #fff;
            color: #000;
            font-weight: bold;
            padding: 12px 20px;
            border-radius: 5px;
            transition: 0.3s;
            cursor: pointer;
        }
        .explore-btn:hover {
            background: #ddd;
        }
    </style>
    <script>
        function redirectToPage() {
            fetch('/check-auth')
                .then(response => response.json())
                .then(data => {
                    if (data.authenticated) {
                        window.location.href = '/dashboard';
                    } else {
                        window.location.href = '/login';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
    </script>
</head>
<body>
<div class="logo-container">
    <img src="{{ asset('logos/logo-transparent-white.png') }}" alt="TripGenie Logo">
    <h2 class="text-2xl font-bold">Your Adventure Starts Here</h2>
    <p class="text-gray-400 mb-4">Plan and explore with TripGenie!</p>
    <button class="explore-btn" onclick="redirectToPage()">Explore Now</button>
</div>
</body>
</html>
