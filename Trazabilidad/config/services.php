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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'plantacruds' => [
        'api_url' => env('PLANTACRUDS_API_URL', 'http://localhost:8001/api'),
    ],

    'planta' => [
        'nombre' => env('PLANTA_NOMBRE', 'Planta Principal'),
        'direccion' => env('PLANTA_DIRECCION', 'Av. Ejemplo 123, Santa Cruz de la Sierra, Bolivia'),
        'latitud' => env('PLANTA_LATITUD', '-17.8146'),
        'longitud' => env('PLANTA_LONGITUD', '-63.1561'),
    ],

];
