<?php

namespace CampusTrack\Controller;

use CampusTrack\Core\Response;
use CampusTrack\Repository\LocalRepository;

/**
 * Local Controller
 * 
 * Handles HTTP requests for location endpoints.
 */
class LocalController
{
    private LocalRepository $repository;

    public function __construct()
    {
        $this->repository = new LocalRepository();
    }

    /**
     * GET /api/locais
     * 
     * Returns all selectable (non-waypoint) locations.
     * Optionally filter by type with ?tipo=sala
     */
    public function index(): void
    {
        $tipo = $_GET['tipo'] ?? null;

        if ($tipo) {
            $locais = $this->repository->findByType($tipo);
        } else {
            $locais = $this->repository->findSelectableLocations();
        }

        Response::success($locais);
    }

    /**
     * GET /api/locais/{id}
     * 
     * Returns a single location by ID.
     */
    public function show(string $id): void
    {
        $local = $this->repository->findById((int) $id);

        if (!$local) {
            Response::error('Local não encontrado', 404);
        }

        Response::success($local);
    }

    /**
     * GET /api/locais/search?q=...
     * 
     * Search locations by name or description.
     */
    public function search(): void
    {
        $query = $_GET['q'] ?? '';

        if (strlen($query) < 2) {
            Response::error('A busca precisa ter pelo menos 2 caracteres', 400);
        }

        $results = $this->repository->search($query);
        Response::success($results);
    }
}
