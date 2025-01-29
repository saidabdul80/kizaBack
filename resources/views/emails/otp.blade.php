<!DOCTYPE html>

<?php 
use App\Services\Util;
?>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Email Template</title>
    <style>
      body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
      }
      .email-container {
        max-width: 600px;
        margin: 50px auto;
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        border-bottom: 5px solid #000;
        border-top: 5px solid #000;
      }
      .header {
        padding: 20px;
        text-align: center;
        color: white;
      }
      .header img {
        width: 200px;
        padding-top: 30px;
      }
      .content {
        padding: 10px;
        text-align: center;
      }
      .content h2 {
        color: #62645f;
        padding: 0px 10px;
      }
      .content p {
        margin: 10px 0;
      }

      .button {
        display: inline-block;
        margin: 20px 0;
        padding: 10px 0px;
        background-color: #2c6;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        width: 80%;
      }
      .content .otp-code {
        font-size: 32px;
        letter-spacing: 20px;
        font-weight: bold;
        color: #000;
        margin: 20px 0;
      }
      .email {
        color: #000;
        font-weight: bold;
      }
      .footer {
        padding: 10px;
        text-align: center;
        color: #888888;
        font-size: 12px;
      }
    </style>
  </head>
  <body>
    <div class="email-container">
      <div class="header">
       
        <img src="<?= asset('logo_full') ?>" alt="Ajo Logo" width="200">
        
      </div>
      <div class="content">
        <h2>Verify your Account.</h2>
        <p>
         Your OTP code is:
        </p>

        <div class="otp-code"> {{$data['otp']}}</div>
        <p>This code expires after 10 minutes.</p>
        <p style="margin:6px auto; font-size:16px; font-weight:bold;">OR</p>
        <p>Complete your account verification using the button below :</p>
        <a class="button" style="color:white;" href="{{config('default.portal.backend')}}/verify_email/{{$data['token']}}">Verify Email Here</a>
        <p>If you didn't initiate this OTP, please ignore this mail.</p>
      </div>
      <div class="footer">
        &copy;  <?= date('Y') ?> {{config('default.title')}}. All rights reserved.
      </div>
    </div>
  </body>
</html>
