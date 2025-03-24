<?php

return [
    'password'=>"password",
    'prefix'=>"KZ",

    /*
    |--------------------------------------------------------------------------
    | Pagination Length
    |--------------------------------------------------------------------------
    |
    | This value determines how many items are shown per page in paginated
    | views. Adjust this as needed for your application's pagination needs.
    |
    */
    'pagination_length' => 6,

    /*
    |--------------------------------------------------------------------------
    | SMS Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the SMS settings including the base URL and default parameters.
    |
    */
    'sms_base_url' => 'https://v3.api.termii.com',
    'sms' => [
        'api_key' => env('SMS_API_KEY'),
        'to' => '',
        'from' => 'N-Alert',
        'sms' => 'Hi there, testing ajo',
        'type' => 'plain',
        'channel' => 'dnd'
    ],
    'aws'=>[
        'bucket'=>env('AWS_BUCKET')
    ],
    "cowrispay"=>[
        'client_id'=>null,
        'client_secrete'=>null,
    ],
    /*
    |--------------------------------------------------------------------------
    | Portal URLs
    |--------------------------------------------------------------------------
    |
    | Define URLs for different portals based on the application environment.
    |
    */
    'portal' => [
        'backend_base_url'=> env('APP_ENV') == 'local' ? 'https://ajo-stage.cowris.com':'https://ajo-app.com',
        'backend'=> env('APP_ENV') == 'local' ? 'https://ajo-stage.cowris.com/api':'https://ajo-app.com/api',
        'domain' => env('APP_ENV') == 'local' ? 'https://ajo2-zom3.vercel.app/' : 'https://ajo2-zom3.vercel.app',
    ],
    "title"=>"Kiza",
    "email"=>"info@com",
    "twilio_account_sid" => env('TWILIO_ACCOUNT_SID'),
    "twilio_auth_token" => env('TWILIO_AUTH_TOKEN'),
    "twilio_message_sid" => env('TWILIO_MESSAGE_SID'),
    "fiat_api_key" => env('FIAT_API_KEY'),
    "fiat_base_url" => env('FIAT_BASE_URL'),
    "currencies" => [
        "CAD" => [  
            "name"=>"Canadian Dollar",
            "slug"=>"CAD",
            "symbol"=>"$",
            "decimal_places"=>2,
            "thousands_separator"=>",",
            "format"=>"{amount} {symbol}",
            "html"=>"{amount} {symbol}",
        ],
        "USD" => [  
            "name"=>"United States Dollar",
            "slug"=>"USD",
            "symbol"=>"$",
            "decimal_places"=>2,
            "thousands_separator"=>",",
            "format"=>"{amount} {symbol}",
            "html"=>"{amount} {symbol}",
        ],
    ],

    "account_details" => [
        "number" => "333 222 3322",
        "name" => "Rhoda Ogunesan",
        "bank_name" => "Wema Bank",
    ],
   


];
