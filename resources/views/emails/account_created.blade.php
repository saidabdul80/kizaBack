<!DOCTYPE html>
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
      .header img {
        width: 200px;
        padding-top: 30px;
      }
      .header {
        padding: 10px;
        text-align: center;
        color: white;
      }
      .content {
        padding: 10px;
        text-align: center;
      }
      .content h2 {
        color: #62645f;
        padding: 0px 10px;
      }
      .paragraph {
        padding: 0px 50px;
      }
      .content p {
        margin: 10px 0;
      }
      .content .credentials {
        padding: 10px;
        border-radius: 5px;
        display: inline-block;
        text-align: left;
        color: #000;
        font-weight: bold;
      }
      .credentials p {
        margin: 5px 0;
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
        <h2>
          Ajo Account Created<br />
          Successfully!
        </h2>
        <p>Dear {{$data['full_name']}},</p>
        <p class="paragraph">
          You have now been added as with the following
          credentials:
        </p>
        <div class="credentials">
          <p>Email: {{$data['email']}}</p>
          <p>Password: {{$data['password']}}</p>
        </div>
        <p>Procceed to login.</p>
        <a href="{{config('default.portal.domain')}}/login" style="color:white" class="button">Login Now</a>
      </div>
      <div class="footer">
        &copy;  <?= date('Y') ?> {{config('default.title')}}. All rights reserved.
      </div>
    </div>
  </body>
</html>
