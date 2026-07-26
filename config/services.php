<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Remove Background Microservice (Python + rembg / U2Net)
    |--------------------------------------------------------------------------
    */
    'rembg' => [
        'url' => env('REMBG_SERVICE_URL', 'http://127.0.0.1:8001'),
        'timeout' => env('REMBG_TIMEOUT', 60),
    ],

];
