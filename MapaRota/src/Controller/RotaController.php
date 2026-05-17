<?php

namespace CampusTrack\Controller;

use CampusTrack\Core\Response;
use CampusTrack\Service\RoutingService;

/**
 * Rota Controller
 * 
 * Handles route calculation requests.
 */
class RotaController
{
    private RoutingService $routingService;

    public function __construct()
    {
        $this->routingService = new RoutingService();
    }

    /**
     * POST /api/rota
     * 
     * Calculate the shortest route between two locations.
     * 
     * Request body (JSON):
     * {
     *   "origem_id": 1,
     *   "destino_id": 5,
     *   "acessivel": false
     * }
     */
    public function calculate(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            Response::error('JSON inválido no corpo da requisição', 400);
        }

        $origemId  = (int) ($input['origem_id'] ?? 0);
        $destinoId = (int) ($input['destino_id'] ?? 0);
        $acessivel = (bool) ($input['acessivel'] ?? false);

        if ($origemId <= 0 || $destinoId <= 0) {
            Response::error('IDs de origem e destino são obrigatórios', 400);
        }

        $result = $this->routingService->findRoute($origemId, $destinoId, $acessivel);

        if (isset($result['error'])) {
            Response::error($result['error'], $result['code'] ?? 400);
        }

        Response::success($result, 'Rota calculada com sucesso');
    }
}
