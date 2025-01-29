<!DOCTYPE html>

<?php 
use App\Services\Util;
?>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Password Reset Request</title>
    <style>
        @import url('https://fonts.cdnfonts.com/css/netflix-font');
    </style>
    <style>
        .body {
            font-family: 'Netflix Font', sans-serif;
            background-color: #e5e5e5;
            margin: 0;
            padding: 50px 0;
            font-size: 16px;
        }
        p, span, b {
            font-family: 'Netflix Sans', Helvetica, Roboto, Segoe UI, sans-serif;
            font-weight: 400;
            font-size: 16px;
            line-height: 21px;
            color: #232323;
        }
        .email-container {
            max-width: 90%;
            width: 500px;
            margin: auto;
            background-color: #ffffff;
            border-radius: 0px 0px 10px 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border-bottom: 5px solid #000;
            border-top: 5px solid #000;
        }
        .header {
            text-transform: uppercase;
            color: #000;
            padding: 20px;
            text-align: center; /* Center align the header text */
        }
        img {
            display: block; /* Center the image */
            margin: 0 auto !important;
            width: 200px; /* Set width directly for the logo */
        }
        .content {
            padding: 20px;
            text-align: center;
        }
        .content h2 {
            color: #62645f;
            padding: 0 10px;
        }
        .content p {
            margin: 10px 0;
        }
        .button {
            display: inline-block;
            margin: 20px 0;
            padding: 10px 0;
            background-color: #000;
            color: white !important;
            text-decoration: none;
            border-radius: 5px;
            width: 80%;
            text-decoration: none;
            text-align: center; /* Center button text */
        }
        .footer {
            padding: 10px;
            text-align: center; /* Center align footer text */
            color: #888888;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="body">
        <div class="email-container">
            <div class="header">
                <img src="<?= asset('logo_full') ?>" alt="Ajo Logo">
                <h2>Password Reset Request</h2>
            </div>
            <div class="content">
                <p>Dear User,</p>
                <p>A request to reset your password has been initiated.</p>
                <a style="color: white; text-decoration:none !important;" class="button" href="{{config('default.portal.backend')}}/confirm-forgot-password/{{$data['token']}}">Click Here to Confirm Change</a>
                <p>The link will expire after 15 minutes.</p>
            </div>
            <div class="footer">
              <p>If you did not request this change, please contact our support team immediately.</p>
              <p>Best regards,</p>
              <p>&copy; <?= date('Y') ?> {{ config('default.title') }}. All rights reserved.</p>
          </div>
        </div>
    </div>
</body>
</html>
