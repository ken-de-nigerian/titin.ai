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
    | Interview Settings
    |--------------------------------------------------------------------------
    |
    | Default interview types used by the AI coach.
    |
    */
    'interview' => [
        'default_mode' => env('DEFAULT_INTERVIEW_MODE', 'simulation'),
        'default_type' => env('DEFAULT_INTERVIEW_TYPE', 'mixed'),
        'default_question_count' => (int) env('DEFAULT_INTERVIEW_QUESTION_COUNT', 6),
        'minutes_per_primary_question' => max(1.0, (float) env('INTERVIEW_MINUTES_PER_PRIMARY_QUESTION', 2.5)),
        'primary_question_count_min' => max(3, min(30, (int) env('INTERVIEW_PRIMARY_QUESTION_MIN', 4))),
        'primary_question_count_max' => max(3, min(50, (int) env('INTERVIEW_PRIMARY_QUESTION_MAX', 20))),
        'default_duration_minutes' => max(5, min(120, (int) env('DEFAULT_INTERVIEW_DURATION_MINUTES', 25))),
        'min_duration_minutes' => 5,
        'max_duration_minutes' => 120,
        'duration_presets' => [15, 20, 25, 30, 40, 45, 60],
        'modes' => [
            'simulation' => 'Real Interview Simulation',
            'mock' => 'Practice / Coaching',
        ],

        'types' => [
            'behavioral' => 'Behavioral (Story-Based)',
            'technical' => 'Technical Knowledge',
            'coding' => 'Coding & Algorithms',
            'system_design' => 'System Design',
            'product_case' => 'Product / Case Study',
            'hr_screening' => 'HR Screening',
            'mixed' => 'Mixed / Adaptive Mode',
        ],
        'type_context' => [
            'behavioral' => 'Focus on past behavior and situational responses. Evaluate with STAR (Situation, Task, Action, Result).',
            'technical' => 'Focus on technical depth, trade-offs, architecture thinking, debugging, and practical implementation choices.',
            'coding' => 'Focus on coding fundamentals, data structures, algorithms, complexity, and clean implementation thinking.',
            'system_design' => 'Focus on system design clarity, scalability, reliability, performance, and operational trade-offs.',
            'product_case' => 'Focus on product judgment, structured problem solving, prioritization, and measurable impact.',
            'hr_screening' => 'Focus on motivation, communication, collaboration, role fit, and work-style alignment.',
            'mixed' => 'Blend behavioral and technical questions adaptively based on candidate responses and context.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Session feedback UI (score tiers on the feedback page)
    |--------------------------------------------------------------------------
    |
    | Overall and per-dimension scores use 0–10. Labels and band edges are configurable.
    | Weak: score < score_tier_weak_below (default 5 — scores under half the scale read as below bar).
    | Strong: score >= score_tier_strong_from. Mid: between (neutral wording; not “doing great”).
    |
    */
    'feedback' => [
        'score_tier_weak_below' => (float) env('FEEDBACK_SCORE_TIER_WEAK_BELOW', 5),
        'score_tier_strong_from' => (float) env('FEEDBACK_SCORE_TIER_STRONG_FROM', 7),
        'score_tier_labels' => [
            'weak' => env('FEEDBACK_SCORE_LABEL_WEAK', 'Needs focus'),
            'mid' => env('FEEDBACK_SCORE_LABEL_MID', 'Mixed signal'),
            'strong' => env('FEEDBACK_SCORE_LABEL_STRONG', 'Strong signal'),
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
