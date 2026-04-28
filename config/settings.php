<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Registration Settings
    |--------------------------------------------------------------------------
    |
    | This option controls whether a new user registration is enabled.
    | When disabled, the registration routes will be unavailable.
    |
    */
    'register' => [
        'enabled' => env('REGISTRATION_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Login Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for login-related features including social authentication.
    |
    */
    'login' => [
        'social_enabled' => env('SOCIAL_LOGIN_ENABLED', true),
    ],
];
