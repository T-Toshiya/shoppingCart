<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],
    
    'twitter' => [
        'client_id' => 'BgUCzhLCaJ9tTisgrIAXflofn',
        'client_secret' => 'N8apr76C9zNjFEDto3peX6diC4QlpLnalk8jshO3Yd3rBN0TEz',
        'redirect' => 'http://192.168.33.10/callback/twitter',
    ],
    
    'facebook' => [
        'client_id' => '1203789486382619',
        'client_secret' => '027c180c71872c0f3c1fe62467480ead',
        'redirect' => 'http://192.168.33.10/callback/facebook',
    ],

];
