<?php

/**
 * API Entry Point
 * 
 * All API requests are routed through this file.
 * Configure your web server to rewrite /api/* requests here,
 * or access endpoints directly via /api/index.php/locais etc.
 */

// Sempre retorna JSON — nunca HTML de erro
header('Content-Type: application/json; charset=utf-8');

// Captura erros PHP fatais e os converte para JSON
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        if (!headers_sent()) {
            http_response_code(500);
        }
        echo json_encode([
            'success' => false,
            'error'   => 'Erro fatal no servidor',
            'message' => $error['message'],
            'file'    => basename($error['file']),
            'line'    => $error['line'],
        ]);
    }
});

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', '0');

// Load autoloader
require_once __DIR__ . '/../autoload.php';

// Load app config
$appConfig = require __DIR__ . '/../config/app.php';
date_default_timezone_set($appConfig['timezone']);

use CampusTrack\Core\Router;
use CampusTrack\Core\Response;
use CampusTrack\Controller\LocalController;
use CampusTrack\Controller\CaminhoController;
use CampusTrack\Controller\RotaController;
use CampusTrack\Controller\EventoController;
use CampusTrack\Controller\MapController;

// Global exception handler
set_exception_handler(function (Throwable $e) use ($appConfig) {
    $response = ['error' => 'Erro interno do servidor'];
    
    if ($appConfig['debug']) {
        $response['message'] = $e->getMessage();
        $response['file'] = $e->getFile();
        $response['line'] = $e->getLine();
    }

    Response::json($response, 500);
});

// Initialize router
$router = new Router('/campustrack/api');

// ---- ROUTES ----

// Locations
$router->get('/locais', [LocalController::class, 'index']);
$router->get('/locais/search', [LocalController::class, 'search']);
$router->get('/locais/{id}', [LocalController::class, 'show']);

// Paths
$router->get('/caminhos', [CaminhoController::class, 'index']);
$router->get('/caminhos/grafo', [CaminhoController::class, 'graph']);

// Routing
$router->post('/rota', [RotaController::class, 'calculate']);

// Events
$router->get('/eventos', [EventoController::class, 'index']);
$router->get('/eventos/proximos', [EventoController::class, 'upcoming']);
$router->get('/eventos/{id}', [EventoController::class, 'show']);

// Map (aggregated data)
$router->get('/mapa', [MapController::class, 'index']);

// Dispatch the request
$router->dispatch();
