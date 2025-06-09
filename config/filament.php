<?php

return [
    'default_filesystem_disk' => env('FILAMENT_FILESYSTEM_DISK', 'public'),

    'layout' => [
        'max_content_width' => 'full',
        'tables' => [
            'is_striped' => true,
            'pagination' => [
                'default_records_per_page' => 10,
            ],
        ],
    ],

    'cache' => [
        // Enable query cache for better performance
        'enable' => true,
        'ttl' => 3600, // Cache for 1 hour
        'prefix' => 'filament_',
    ],

    'broadcasting' => [
        // Disable real-time broadcasting if not needed
        'enabled' => false,
    ],

    'assets' => [
        // Enable assets versioning
        'should_version_assets' => true,

        // Minify assets
        'should_minify_assets' => true,

        // Enable assets preloading
        'preload_assets' => true,
    ],

    'rendering' => [
        // Enable lazy loading for images
        'lazy_loading' => true,
    ],
];
