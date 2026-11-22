<?php

return [
    'genders' => ['Female', 'Male', 'Prefer not to say'],
    'front_desk' => [
        'opening_hours' => 'Mon to Fri, 9:00 AM to 5:00 PM',
        'what_to_bring' => 'Bring your membership reference number, a valid ID, and the exact membership fee.',
    ],
    'reactivation_requirements' => [
        'A valid government or school ID',
        'Any outstanding membership concern settled at the front desk',
    ],
    'documents' => [
        'max_kilobytes' => 5120,
        'mimetypes' => ['application/pdf', 'image/jpeg', 'image/png'],
        'extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
    ],
    'request_types' => [
        'reactivation' => 'Reactivation',
        'category_change' => 'Category change',
        'profile_change' => 'Profile change',
    ],
    'weather' => [
        'default_latitude' => env('WEATHER_DEFAULT_LATITUDE', 14.5995),
        'default_longitude' => env('WEATHER_DEFAULT_LONGITUDE', 120.9842),
    ],
    'initial_admin' => [
        'name' => env('INITIAL_ADMIN_NAME', 'Puihaha Administrator'),
        'email' => env('INITIAL_ADMIN_EMAIL'),
        'password' => env('INITIAL_ADMIN_PASSWORD'),
    ],
];
