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
    <script>
        function validateForm() {
            const location = document.getElementById('location').value;
            const date = document.getElementById('date').value;
            if (!location || !date) {
                alert('Please fill in all fields before exploring.');
                return false;
            }
            return true;
        }
    </script>
</head>
<body class="text-gray-800" style="background-image: url('{{ asset('images/mountains.jpg') }}'); background-size: cover; background-position: center;">
<header class="bg-transparent text-white py-6 transparent-bg">
    <div class="container mx-auto flex justify-between items-center px-4">
        <img src="{{ asset('logos/logo-transparent-white.png') }}" alt="{{ config('app.name', 'Laravel') }}" class="h-16 w-auto">
        <nav>
            <ul class="flex space-x-4">
                <li><a href="#about" class="bg-blue-600 text-white rounded-lg p-3 hover:bg-blue-700">About Us</a></li>
                <li><a href="#destinations" class="bg-blue-600 text-white rounded-lg p-3 hover:bg-blue-700">Destinations</a></li>
            </ul>
        </nav>
    </div>
</header>

<main>
    <section id="about" class="bg-white bg-opacity-80 py-20 transparent-bg">
        <div class="container px-4 text-center bg-white shadow-lg rounded-lg p-8 max-w-5xl mx-auto">
            <h2 class="text-5xl font-extrabold mb-6">Your Gateway to Amazing Adventures</h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Discover the world with TripGenie, your trusted AI travel companion.<br>
                Plan your trips effortlessly and explore new horizons like never before.
            </p>
        </div>
    </section>

    <section id="search-form" class="bg-gray-100 bg-opacity-0 py-20">
        <div class="container mx-auto px-4 text-center">
            <form class="bg-white shadow-lg rounded-lg p-8 max-w-2xl mx-auto">
                <h3 class="text-4xl font-bold mb-6">Plan Your Next Trip</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label for="location" class="block text-gray-700 font-medium mb-2">Location</label>
                        <input type="text" id="location" name="location" placeholder="Enter destination" class="w-full border border-gray-300 rounded-lg p-3">
                    </div>
                    <div>
                        <label for="start-date" class="block text-gray-700 font-medium mb-2">Start Date</label>
                        <input type="date" id="start-date" name="start-date" class="w-full border border-gray-300 rounded-lg p-3">
                    </div>
                    <div>
                        <label for="end-date" class="block text-gray-700 font-medium mb-2">End Date</label>
                        <input type="date" id="end-date" name="end-date" class="w-full border border-gray-300 rounded-lg p-3">
                    </div>
                </div>
                <div class="flex justify-center">
                    <button type="submit" class="explore-now-btn bg-blue-600 text-white rounded-lg p-3 hover:bg-blue-700" onclick="return validateForm()">Explore Now</button>
                </div>
            </form>
        </div>
    </section>

    <section id="destinations" class="py-20">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Destination Card -->
                <div class="card bg-white shadow-lg rounded-lg overflow-hidden transition-transform duration-300">
                    <img src="/images/paris.jpg" alt="Paris" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h4 class="text-2xl font-bold mb-2">Paris</h4>
                        <p class="text-gray-600">The city of lights awaits with its iconic landmarks and rich culture.</p>
                    </div>
                </div>
                <!-- Destination Card -->
                <div class="card bg-white shadow-lg rounded-lg overflow-hidden transition-transform duration-300">
                    <img src="/images/bali.jpg" alt="Bali" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h4 class="text-2xl font-bold mb-2">Bali</h4>
                        <p class="text-gray-600">Immerse yourself in the serene beauty of beaches and tropical landscapes.</p>
                    </div>
                </div>
                <!-- Destination Card -->
                <div class="card bg-white shadow-lg rounded-lg overflow-hidden transition-transform duration-300">
                    <img src="/images/new-york.jpg" alt="New York" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h4 class="text-2xl font-bold mb-2">New York</h4>
                        <p class="text-gray-600">Experience the hustle and bustle of the Big Apple, a city that never sleeps.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<footer class="bg-blue-600 text-white py-2">
    <div class="container mx-auto flex flex-col items-center space-y-4">
        <!-- Logo -->
        <div class="flex flex-col items-center space-y-1">
            <img src="{{ asset('logos/logo-transparent-white.png') }}" alt="{{ config('app.name', 'Laravel') }}" class="h-16 w-auto">
            <span class="text-l font-light">&copy; TripGenie - All Rights Reserved</span>
        </div>

        <!-- Social Links -->
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

<!-- Registration Modal -->
<div id="registrationModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white p-8 rounded-lg shadow-lg relative max-w-2xl w-full">
        <div class="title_container text-center mb-4">
            <h3 class="title text-2xl font-bold">Register Your Account</h3>
            <button id="closeRegistrationModal" class="absolute top-0 right-0 mt-2 mr-2 text-gray-500 hover:text-gray-700 text-2xl">
                &times;
            </button>
        </div>
        <form id="registrationForm" method="POST" action="{{ route('register') }}">
            @csrf

            <div class="mb-4 input_container">
                <label for="name" class="input_label block text-gray-700 font-medium mb-2">Name</label>
                <div class="input-group">
                    <input id="name" type="text"
                           class="form-control input_field w-full border border-gray-300 rounded-lg p-3 @error('name') is-invalid @enderror"
                           name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="John Doe">
                    @error('name')
                    <div class="invalid-feedback text-red-500 mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-4 input_container">
                <label for="email" class="input_label block text-gray-700 font-medium mb-2">Email Address</label>
                <div class="input-group">
                    <input id="email" type="email"
                           class="form-control input_field w-full border border-gray-300 rounded-lg p-3 @error('email') is-invalid @enderror"
                           name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="name@mail.com">
                    @error('email')
                    <div class="invalid-feedback text-red-500 mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-4 input_container">
                <label for="password" class="input_label block text-gray-700 font-medium mb-2">Password</label>
                <div class="input-group">
                    <input id="password" type="password"
                           class="form-control input_field w-full border border-gray-300 rounded-lg p-3 @error('password') is-invalid @enderror"
                           name="password" required autocomplete="new-password" placeholder="********">
                    @error('password')
                    <div class="invalid-feedback text-red-500 mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-4 input_container">
                <label for="password_confirmation" class="input_label block text-gray-700 font-medium mb-2">Confirm Password</label>
                <div class="input-group">
                    <input id="password_confirmation" type="password"
                           class="form-control input_field w-full border border-gray-300 rounded-lg p-3 @error('password_confirmation') is-invalid @enderror"
                           name="password_confirmation" required autocomplete="new-password" placeholder="********">
                    @error('password_confirmation')
                    <div class="invalid-feedback text-red-500 mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary sign-in_btn w-full bg-blue-600 text-white rounded-lg p-3 hover:bg-blue-700">Register</button>
            </div>

            <div class="text-center mt-4">
                <button type="button" class="btn btn-light sign-in_ggl w-full bg-gray-100 text-gray-700 rounded-lg p-3 hover:bg-gray-200">
                    <img src="/logos/google.png" alt="Google" class="inline-block w-5 h-5 mr-2">
                    Sign up with Google
                </button>
            </div>

            <div class="text-center mt-4">
                <p>Already have an account? <a href="#" id="showLoginModal" class="text-blue-600 hover:underline">Sign in</a></p>
            </div>
        </form>
    </div>
</div>

<!-- Login Modal -->
<div id="loginModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white p-8 rounded-lg shadow-lg relative max-w-2xl w-full">
        <div class="title_container text-center mb-4">
            <h3 class="title text-2xl font-bold">Sign In to Your Account</h3>
            <button id="closeLoginModal" class="absolute top-0 right-0 mt-2 mr-2 text-gray-500 hover:text-gray-700 text-2xl">
                &times;
            </button>
        </div>
        <form id="loginForm" method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-4 input_container">
                <label for="email" class="input_label block text-gray-700 font-medium mb-2">Email Address</label>
                <div class="input-group">
                    <input id="email" type="email"
                           class="form-control input_field w-full border border-gray-300 rounded-lg p-3 @error('email') is-invalid @enderror"
                           name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="name@mail.com">
                    @error('email')
                    <div class="invalid-feedback text-red-500 mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-4 input_container">
                <label for="password" class="input_label block text-gray-700 font-medium mb-2">Password</label>
                <div class="input-group">
                    <input id="password" type="password"
                           class="form-control input_field w-full border border-gray-300 rounded-lg p-3 @error('password') is-invalid @enderror"
                           name="password" required autocomplete="current-password" placeholder="********">
                    @error('password')
                    <div class="invalid-feedback text-red-500 mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary sign-in_btn w-full bg-blue-600 text-white rounded-lg p-3 hover:bg-blue-700">Sign In</button>
            </div>

            <div class="text-center mt-4">
                <button type="button" class="btn btn-light sign-in_ggl w-full bg-gray-100 text-gray-700 rounded-lg p-3 hover:bg-gray-200">
                    <img src="/logos/google.png" alt="Google" class="inline-block w-5 h-5 mr-2">
                    Sign in with Google
                </button>
            </div>

            <div class="text-center mt-4">
                <p>Don't have an account? <a href="#" id="showRegistrationModal" class="text-blue-600 hover:underline">Register</a></p>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const exploreNowButton = document.querySelector('.explore-now-btn');
        const registrationModal = document.getElementById('registrationModal');
        const loginModal = document.getElementById('loginModal');
        const closeRegistrationModal = document.getElementById('closeRegistrationModal');
        const closeLoginModal = document.getElementById('closeLoginModal');
        const showLoginModal = document.getElementById('showLoginModal');
        const showRegistrationModal = document.getElementById('showRegistrationModal');

        exploreNowButton.addEventListener('click', function (event) {
            event.preventDefault();
            registrationModal.classList.remove('hidden');
        });

        closeRegistrationModal.addEventListener('click', function () {
            registrationModal.classList.add('hidden');
        });

        closeLoginModal.addEventListener('click', function () {
            loginModal.classList.add('hidden');
        });

        showLoginModal.addEventListener('click', function (event) {
            event.preventDefault();
            registrationModal.classList.add('hidden');
            loginModal.classList.remove('hidden');
        });

        showRegistrationModal.addEventListener('click', function (event) {
            event.preventDefault();
            loginModal.classList.add('hidden');
            registrationModal.classList.remove('hidden');
        });
    });
</script>

</body>
</html>
