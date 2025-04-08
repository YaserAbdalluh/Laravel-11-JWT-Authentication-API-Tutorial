<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'freelancing' => [
        'api_url' => env('FREELANCING_API_URL', 'http://universities.hipolabs.com'),
        'api_key' => env('FREELANCING_API_KEY', ''),
    ],
    'whatsapp' => [
        'api_url' => env('api_url', 'https://click.one.sky4system.com/api/user/create-text'),
        'device_id' => env('device_id', '3GH4XGR8P'),
        'device_token' => env('device_token', 'lLWd1H8xHSw6cjaGwD2BhPE04e1yPBBq05QRgYnx44lTiyxyieCbCfMeA1V9uxeuoYIGjsyAo3zhYJ288ruGoN7LTIwozarbbR6p'),
    ],


    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
