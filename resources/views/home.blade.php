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
            font-family: 'Roboto', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #000;
            color: #fff;
        }
        .form-container {
            background: #111;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(255, 255, 255, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .input-field {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #444;
            background: #222;
            color: #fff;
            margin-bottom: 1rem;
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
        .btn-light {
            background: #fff;
            color: #000;
        }
        .btn-light:hover {
            background: #ddd;
        }
        a {
            color: #bbb;
            text-decoration: underline;
        }
        a:hover {
            color: #fff;
        }
    </style>
</head>
<body>
<div class="form-container">
    <h3 class="text-2xl font-bold mb-4">Sign In</h3>
    <form id="loginForm" method="POST" action="{{ route('login') }}">
        @csrf
        <input id="email" type="email" class="input-field" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="Email Address">
        <input id="password" type="password" class="input-field" name="password" required autocomplete="current-password" placeholder="Password">
        <div class="text-left mb-2">
            <label for="remember_me" class="text-sm">
                <input id="remember_me" type="checkbox" class="mr-1"> Remember me
            </label>
        </div>
        <button type="submit" class="btn">Sign In</button>
        <div class="text-center mt-4">
            <button type="button" class="btn btn-light sign-in_ggl w-full bg-gray-100 text-gray-700 rounded-lg p-3 hover:bg-gray-200">
                <img src="/logos/google.png" alt="Google" class="inline-block w-5 h-5 mr-2">
                Sign in with Google
            </button>
        </div>
    </form>
    <p class="mt-4">Don't have an account? <a href="register">Register</a></p>
</div>
</body>
</html>
