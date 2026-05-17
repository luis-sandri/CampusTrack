<?php

namespace CampusTrack\Controller;

use CampusTrack\Core\Response;
use CampusTrack\Repository\EventoRepository;

/**
 * Evento Controller
 * 
 * Handles HTTP requests for event endpoints.
 */
class EventoController
{
    private EventoRepository $repository;

    public function __construct()
    {
        $this->repository = new EventoRepository();
    }

    /**
     * GET /api/eventos
     * 
     * Returns all active events.
     */
    public function index(): void
    {
        $eventos = $this->repository->findAll();
        Response::success($eventos);
    }

    /**
     * GET /api/eventos/proximos
     * 
     * Returns upcoming events.
     */
    public function upcoming(): void
    {
        $limit = (int) ($_GET['limit'] ?? 10);
        $eventos = $this->repository->findUpcoming($limit);
        Response::success($eventos);
    }

    /**
     * GET /api/eventos/{id}
     * 
     * Returns a single event by ID.
     */
    public function show(string $id): void
    {
        $evento = $this->repository->findById((int) $id);

        if (!$evento) {
            Response::error('Evento não encontrado', 404);
        }

        Response::success($evento);
    }
}
