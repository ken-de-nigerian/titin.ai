<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have a
    | conventional location to locate the various services credentials.
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
    | OpenAI (post-session coaching — uses a stronger model than live agent)
    |--------------------------------------------------------------------------
    */
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'feedback_model' => env('OPENAI_FEEDBACK_MODEL', 'gpt-4o'),
    ],

    'livekit' => [
        'url' => env('VITE_LIVEKIT_URL'),
        'token_server_url' => env('LIVEKIT_TOKEN_SERVER_URL', env('VITE_TOKEN_SERVER_URL', 'http://localhost:5001')),
        'internal_secret' => env('LIVEKIT_INTERNAL_TOKEN_SECRET'),
        'python_accessible_app_url' => env('PYTHON_ACCESSIBLE_APP_URL'),
    ],
];
