<?php

namespace CampusTrack\Core;

use PDO;
use PDOException;

/**
 * Database Singleton
 * 
 * Provides a single PDO connection instance throughout the application.
 * Uses the configuration from config/database.php.
 */
class Database
{
    private static ?PDO $instance = null;

    /**
     * Prevent direct instantiation
     */
    private function __construct() {}

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Get the PDO connection instance
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../../config/database.php';

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['dbname'],
                $config['charset']
            );

            try {
                self::$instance = new PDO($dsn, $config['user'], $config['pass'], $config['options']);
            } catch (PDOException $e) {
                throw new PDOException('Database connection failed: ' . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
