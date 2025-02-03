<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Trips</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f9fafb;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        header {
            background: transparent;
            padding: 1.5rem 0;
            text-align: center;
        }
        header h2 {
            font-size: 2rem;
            font-weight: bold;
        }
        .bg-green-100 {
            background-color: #d1fae5;
        }
        .bg-green-700 {
            color: #165e33;
        }
        .bg-white {
            background-color: #ffffff;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 1rem;
            border: 1px solid #e5e7eb;
        }
        .table th {
            text-align: left;
        }
        .table td {
            color: #4b5563;
        }
    </style>
</head>
<body>
<header>
    <h2>My Trips</h2>
</header>

<div class="container">
    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 mb-4 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if($trips->isEmpty())
        <p class="text-gray-600">No trips found. Start planning your next adventure!</p>
    @else
        <div class="bg-white shadow-md rounded-lg p-6">
            <table class="table">
                <thead>
                <tr>
                    <th>City</th>
                    <th>Country</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($trips as $trip)
                    <tr>
                        <td>{{ $trip->city }}</td>
                        <td>{{ $trip->country }}</td>
                        <td>{{ $trip->start_date }}</td>
                        <td>{{ $trip->end_date }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
</body>
</html>
