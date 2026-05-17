<?php

namespace CampusTrack\Core;

/**
 * Simple Router
 * 
 * Matches incoming requests to registered route handlers.
 * Supports GET, POST, PUT, DELETE methods and URL parameters.
 */
class Router
{
    private array $routes = [];
    private string $basePath;

    public function __construct(string $basePath = '')
    {
        $this->basePath = rtrim($basePath, '/');
    }

    /**
     * Register a GET route
     */
    public function get(string $path, callable|array $handler): self
    {
        return $this->addRoute('GET', $path, $handler);
    }

    /**
     * Register a POST route
     */
    public function post(string $path, callable|array $handler): self
    {
        return $this->addRoute('POST', $path, $handler);
    }

    /**
     * Register a PUT route
     */
    public function put(string $path, callable|array $handler): self
    {
        return $this->addRoute('PUT', $path, $handler);
    }

    /**
     * Register a DELETE route
     */
    public function delete(string $path, callable|array $handler): self
    {
        return $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * Add a route to the collection
     */
    private function addRoute(string $method, string $path, callable|array $handler): self
    {
        $this->routes[] = [
            'method'  => $method,
            'path'    => $path,
            'handler' => $handler,
        ];
        return $this;
    }

    /**
     * Dispatch the current request to a matching route handler
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];

        // Support PATH_INFO (when mod_rewrite rewrites to index.php/$1)
        // and REQUEST_URI (when mod_rewrite rewrites to index.php transparently)
        if (!empty($_SERVER['PATH_INFO'])) {
            $uri = $_SERVER['PATH_INFO'];
        } else {
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        }

        // Remove base path from URI
        if ($this->basePath && str_starts_with($uri, $this->basePath)) {
            $uri = substr($uri, strlen($this->basePath));
        }

        $uri = '/' . trim($uri, '/');

        // Handle CORS preflight
        if ($method === 'OPTIONS') {
            $this->sendCorsHeaders();
            http_response_code(204);
            exit;
        }

        foreach ($this->routes as $route) {
            $params = $this->matchRoute($route['path'], $uri);

            if ($params !== false && $route['method'] === $method) {
                $this->sendCorsHeaders();
                $this->callHandler($route['handler'], $params);
                return;
            }
        }

        // No route matched
        $this->sendCorsHeaders();
        Response::json(['error' => 'Route not found', 'path' => $uri], 404);
    }

    /**
     * Match a route pattern against a URI
     * 
     * @return array|false Matched parameters or false
     */
    private function matchRoute(string $pattern, string $uri): array|false
    {
        // Convert {param} to regex groups
        $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $uri, $matches)) {
            return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        }

        return false;
    }

    /**
     * Call the route handler with matched parameters
     */
    private function callHandler(callable|array $handler, array $params): void
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $instance = new $class();
            $instance->$method(...array_values($params));
        } else {
            $handler(...array_values($params));
        }
    }

    /**
     * Send CORS headers
     */
    private function sendCorsHeaders(): void
    {
        $config = require __DIR__ . '/../../config/app.php';
        $cors = $config['cors'];

        header('Access-Control-Allow-Origin: ' . implode(', ', $cors['allowed_origins']));
        header('Access-Control-Allow-Methods: ' . implode(', ', $cors['allowed_methods']));
        header('Access-Control-Allow-Headers: ' . implode(', ', $cors['allowed_headers']));
    }
}
