<?php
    return [
        'paths' => ['api/*'], // Specify routes that should allow CORS (e.g., API routes)
        'allowed_methods' => ['*'], // Allow all HTTP methods
        'allowed_origins' => ['*'], // Allow your frontend's origin
        'allowed_origins_patterns' => [],
        'allowed_headers' => ['*'], // Allow all headers
        'exposed_headers' => [],
        'max_age' => 0,
        'supports_credentials' => true, // If using cookies for authentication
    ];
    

