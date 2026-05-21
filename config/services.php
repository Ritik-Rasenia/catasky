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

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | DoubleTick.io WhatsApp Business API
    |--------------------------------------------------------------------------
    |
    | Set DOUBLETICK_API_KEY and DOUBLETICK_SENDER_NUMBER in your .env file.
    | DOUBLETICK_BASE_URL defaults to the public DoubleTick API endpoint.
    |
    */
    'doubletick' => [
        'api_key'       => env('DOUBLETICK_API_KEY'),
        'sender_number' => env('DOUBLETICK_SENDER_NUMBER'),
        'base_url'      => env('DOUBLETICK_BASE_URL', 'https://public.doubletick.io'),
    ],

];
