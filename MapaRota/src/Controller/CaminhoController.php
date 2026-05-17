<?php

namespace CampusTrack\Controller;

use CampusTrack\Core\Response;
use CampusTrack\Repository\CaminhoRepository;

/**
 * Caminho Controller
 * 
 * Handles HTTP requests for path/edge endpoints.
 */
class CaminhoController
{
    private CaminhoRepository $repository;

    public function __construct()
    {
        $this->repository = new CaminhoRepository();
    }

    /**
     * GET /api/caminhos
     * 
     * Returns all paths with origin and destination details.
     */
    public function index(): void
    {
        $caminhos = $this->repository->findAll();
        Response::success($caminhos);
    }

    /**
     * GET /api/caminhos/grafo
     * 
     * Returns the full adjacency list for the graph.
     */
    public function graph(): void
    {
        $acessivelOnly = isset($_GET['acessivel']) && $_GET['acessivel'] === '1';
        $adjacency = $this->repository->buildAdjacencyList($acessivelOnly);
        Response::success($adjacency);
    }
}
