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

    'sso' => [
        'base_url' => env('SSO_BASE_URL'),
        'app_code' => env('SSO_APP_CODE'),
        'ticket_secret' => env('SSO_TICKET_SECRET'),
        'home_url' => env('SSO_HOME_URL'),
        'pull_token' => env('SSO_PULL_TOKEN'),
        'pull_tokens' => env('SSO_PULL_TOKENS'),
        'pull_secrets' => env('SSO_PULL_SECRETS'),
        'token' => env('SSO_API_TOKEN'),
        'users_endpoint' => env('SSO_USERS_ENDPOINT', '/api/sso/users'),
        'opds_endpoint' => env('SSO_OPDS_ENDPOINT', '/api/sso/opds'),
    ],

];
