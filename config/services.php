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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'news' => [
        'enabled_providers' => ['the_guardian', 'news_api_org', 'ny_times'],
//        'enabled_providers' => ['ny_times'],
        'providers' => [
            'news_api_org' => [
                // https://newsapi.org/
                'key' => env('NEWSAPI_ORG_KEY')
            ],
            'the_guardian' => [
                // https://open-platform.theguardian.com/access/
                // https://open-platform.theguardian.com/documentation/search
                // https://content.guardianapis.com/search?api-key=<>
                'key' => env('THE_GUARDIAN')
            ],
            'ny_times' => [
                // https://developer.nytimes.com/get-started
                // https://developer.nytimes.com/my-apps
                'key' => env('NY_TIMES_KEY'),
                'secret' => env('NY_TIMES_SECRET')
            ],
        ]
    ]

];
