<?php
/**
 * Database Configuration
 * 
 * Central configuration for database connection parameters.
 * In production, these should come from environment variables.
 */

return [
    'host'    => 'localhost',
    'port'    => 3306,
    'dbname'  => 'campustrack_mapa',
    'user'    => 'root',
    'pass'    => '',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
];
