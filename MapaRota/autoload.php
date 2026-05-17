<?php

/**
 * PSR-4 Style Autoloader
 * 
 * Maps the CampusTrack namespace to the src/ directory.
 * In production, use Composer's autoloader instead.
 */

spl_autoload_register(function (string $class) {
    // Only handle our namespace
    $prefix = 'CampusTrack\\';
    
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    // Remove namespace prefix and convert to file path
    $relativeClass = substr($class, strlen($prefix));
    $file = __DIR__ . '/src/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});
