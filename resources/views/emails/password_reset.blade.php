<!DOCTYPE html>

<?php 
use App\Services\Util;
$logo = 'logo_full';
?>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Password Reset Notification</title>
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
        p, span, b{
          font-family: 'Netflix Sans', Helvetica, Roboto, Segoe UI, sans-serif;
          font-weight: 400;
          font-size: 16px;
          line-height: 21px;
          color: #232323;
        }
        .email-container {
            max-width: 90%;
            width:500px;
            margin: auto;
            background-color: #ffffff;
            border-radius: 0px 0px 10px 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border-bottom: 5px solid #000;
        }
        .header {
            text-transform: uppercase;
            color: #000;
            padding: 10px 0;
            margin: 0 auto;
            
        }
        img{
          margin: 0 auto !important;
        }
        .content {
            padding: 20px;
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
            background-color: #2c6;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            width: 80%;
        }
        .content .otp-code {
            font-size: 32px;
            letter-spacing: 5px; /* Reduced spacing for better appearance */
            font-weight: bold;
            color: #000;
            margin: 20px 0;
        }
        .email {
            color: #000;
            font-weight: bold;
        }
        .footer p{
          font-size: 12px !important;
          color: #888888 !important;
        }
        .footer {
            padding: 10px;
        }
    </style>
</head>
<body>
  <div class="body">
    
    <div class="email-container">
      <div style="padding:10px;">
        <img src="<?= $logo ?>" style="width: 96%" alt="Ajo Logo">
        <h2 class="header" >PASSWORD RESET NOTIFICATION</h2>
      </div>
      <div class="content">
            <p>Dear {{ $data['full_name'] }},</p>
            <p>Your password has been reset to:</p>
            <p class="otp-code">{{ $data['plainPassword'] }}</p>
            <p>Please change it to something more secure after logging in.</p>
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
