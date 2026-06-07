<?php
/**
 * Security Configuration
 *
 * Configure security headers and policies for the application.
 * These settings are used in public/index.php to set HTTP security headers.
 */

return [
    // Content Security Policy (CSP) directives
    // Adjust these based on your application's needs
    'csp' => [
        'default-src' => "'self'",

        // 'unsafe-inline' and 'unsafe-eval' are required by Smarty (inline event handlers)
        // and TinyMCE editor (uses eval() internally). Removing them requires significant
        // refactoring of both the template engine and the rich text editor integration.
        'script-src' => "'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net",

        // Allow inline styles and Bootstrap CDN for error pages
        'style-src' => "'self' 'unsafe-inline' https://cdn.jsdelivr.net",

        // Allow images from self, data URIs, and HTTPS sources
        'img-src' => "'self' data: https:",

        // Allow fonts from self and data URIs
        'font-src' => "'self' data: https://cdn.jsdelivr.net",

        // Allow AJAX requests to self only
        'connect-src' => "'self'",

        // Prevent framing from other origins
        'frame-ancestors' => "'self'"
    ],

    // HTTP Strict Transport Security (HSTS) settings
    'hsts' => [
        'enabled' => true,
        'max-age' => 31536000, // 1 year in seconds
        'includeSubDomains' => true,
        'preload' => false // Set to true only after testing and if you plan to submit to HSTS preload list
    ]
];
