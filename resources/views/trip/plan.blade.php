<!-- resources/views/trip/plan.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Plan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
        }
        h1 {
            color: #333;
        }
        .day-plan {
            margin-bottom: 20px;
        }
        .day-plan h2 {
            color: #555;
        }
        .day-plan ul {
            list-style-type: disc;
            margin-left: 20px;
        }
    </style>
</head>
<body>
<h1>Your Travel Plan</h1>
{!! nl2br(e($travelPlan)) !!}
</html>
