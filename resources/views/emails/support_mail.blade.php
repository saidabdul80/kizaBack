<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Mail</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        .header {
            background-color: #0040ee;
            color: #fff;
            padding: 10px;
            text-align: center;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .content {
            padding: 20px;
        }
        .details {
            background-color: #f7f7f7;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
        }
        .details h3 {
            margin-top: 0;
        }
        .details ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .details li {
            margin-bottom: 10px;
        }
        .details li:last-child {
            margin-bottom: 0;
        }
        .details strong {
            font-weight: bold;
        }
        .footer {
            background-color: #0040ee;
            color: #fff;
            padding: 10px;
            text-align: center;
            clear: both;
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Support Mail</h2>
        </div>
        <div class="content">
            <div class="details">
                <h3>Support Request Details</h3>
                <ul>
                    <li><strong>Name:</strong> {{ $data['name'] }}</li>
                    <li><strong>Email:</strong> {{ $data['email'] }}</li>
                    <li><strong>Message:</strong> {{ $data['message'] }}</li>
                </ul>
            </div>
        </div>
        <div class="footer">
            <p>Best regards,</p>
            <p>{{ config('app.name') }} Team</p>
        </div>
    </div>
</body>
</html>