<?php

namespace CampusTrack\Service;

use CampusTrack\Repository\LocalRepository;

/**
 * Map Service
 * 
 * Business logic for map-related operations including
 * coordinate normalization and spatial queries.
 */
class MapService
{
    private LocalRepository $localRepo;

    public function __construct()
    {
        $this->localRepo = new LocalRepository();
    }

    /**
     * Get all map data needed for initial rendering
     * 
     * Returns locations organized by type for map visualization.
     */
    public function getMapData(): array
    {
        $allLocais = $this->localRepo->findAll();
        
        $locations = [];
        $waypoints = [];

        foreach ($allLocais as $local) {
            $item = [
                'id'            => (int) $local['id'],
                'nome'          => $local['nome'],
                'descricao'     => $local['descricao'],
                'tipo'          => $local['tipo'],
                'x'             => (float) $local['x'],
                'y'             => (float) $local['y'],
                'icone'         => $local['icone'] ?? 'pin',
                'edificio_nome' => $local['edificio_nome'] ?? null,
                'edificio_cor'  => $local['edificio_cor'] ?? null,
            ];

            if ((bool) $local['is_waypoint']) {
                $waypoints[] = $item;
            } else {
                $locations[] = $item;
            }
        }

        return [
            'locations' => $locations,
            'waypoints' => $waypoints,
            'config'    => $this->getMapConfig(),
        ];
    }

    /**
     * Get map configuration
     */
    private function getMapConfig(): array
    {
        $appConfig = require __DIR__ . '/../../config/app.php';
        
        return [
            'image'  => $appConfig['map']['default_image'],
            'width'  => $appConfig['map']['width'],
            'height' => $appConfig['map']['height'],
        ];
    }
}
