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

    /*
    |--------------------------------------------------------------------------
    | Interview Settings
    |--------------------------------------------------------------------------
    |
    | Default interview types used by the AI coach.
    |
    */
    'interview' => [
        'default_type' => env('DEFAULT_INTERVIEW_TYPE', 'mixed'),

        'types' => [
            'behavioral' => 'Behavioral (Story-Based)',
            'technical' => 'Technical Knowledge',
            'coding' => 'Coding & Algorithms',
            'system_design' => 'System Design',
            'product_case' => 'Product / Case Study',
            'hr_screening' => 'HR Screening',
            'mixed' => 'Mixed / Adaptive Mode',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Seniority Settings
    |--------------------------------------------------------------------------
    |
    | Seniority levels used across onboarding and interview preparation flows.
    |
    */
    'seniority' => [
        'default_level' => env('DEFAULT_SENIORITY_LEVEL', 'mid_level'),
        'levels' => [
            'junior' => [
                'label' => 'Junior',
                'description' => '0-2 years experience',
            ],
            'mid_level' => [
                'label' => 'Mid-Level',
                'description' => '2-5 years experience',
            ],
            'senior' => [
                'label' => 'Senior',
                'description' => '5-8 years experience',
            ],
            'lead_staff' => [
                'label' => 'Lead/Staff',
                'description' => '8+ years experience',
            ],
        ],
    ],
];
