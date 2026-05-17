<?php

namespace CampusTrack\Controller;

use CampusTrack\Core\Response;
use CampusTrack\Service\MapService;
use CampusTrack\Repository\CaminhoRepository;

/**
 * Map Controller
 * 
 * Provides all data needed for map rendering in a single request.
 */
class MapController
{
    private MapService $mapService;
    private CaminhoRepository $caminhoRepo;

    public function __construct()
    {
        $this->mapService = new MapService();
        $this->caminhoRepo = new CaminhoRepository();
    }

    /**
     * GET /api/mapa
     * 
     * Returns all map data: locations, waypoints, paths, and config.
     * Single endpoint to initialize the frontend map.
     */
    public function index(): void
    {
        $mapData = $this->mapService->getMapData();
        $caminhos = $this->caminhoRepo->findAll();

        $mapData['paths'] = $caminhos;

        Response::success($mapData);
    }
}
