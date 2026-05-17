<?php

namespace CampusTrack\Core;

/**
 * HTTP Response Helper
 * 
 * Standardizes JSON responses across the application.
 */
class Response
{
    /**
     * Send a JSON response
     */
    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Send a success response with data
     */
    public static function success(mixed $data, string $message = 'OK', int $status = 200): void
    {
        self::json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * Send an error response
     */
    public static function error(string $message, int $status = 400, mixed $details = null): void
    {
        $response = [
            'status'  => 'error',
            'message' => $message,
        ];

        if ($details !== null) {
            $response['details'] = $details;
        }

        self::json($response, $status);
    }
}
