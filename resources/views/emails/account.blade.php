<!DOCTYPE html>
<html lang="en">
<?php 
use App\Services\Util;
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Ajo!</title>
    <style>
        /* CSS styles for email */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .logo {
            text-align: center;
            margin-bottom: 0px;
            padding-bottom: 0px;
        }

        p {
            color: #666;
        }

        .button {
            display: inline-block;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
            text-align: center;
        }

        .footer p {
            margin: 5px 0;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="logo">
            <!-- Replace the src attribute value with the actual path to your company logo -->
            <img src="<?= asset('logo_full') ?>" alt="Ajo Logo" width="200">
        
        </div>
        <h2>Welcome to the Ajo Portal!</h2>
        <p>Dear {{ $name }},</p>
        <p>Welcome to (Ajo) Portal! We're thrilled to have you join our platform.</p>
        <p>For reference, visit our portal url <a href='https://ajo.cowris.com'>www.portal.Ajo.gov.ng</a></p>
        
        <div class="footer">
            <p>Best regards,<br>{{config('default.title')}}</p>
            <p>Contact us at: support@ajo.cowris.com</p>
        </div>
    </div>
</body>

</html>
