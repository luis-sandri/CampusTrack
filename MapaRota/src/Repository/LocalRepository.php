<?php

namespace CampusTrack\Repository;

use CampusTrack\Core\Database;
use CampusTrack\Model\Local;
use PDO;

/**
 * Local Repository
 * 
 * Data access layer for location-related database operations.
 */
class LocalRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Fetch all navigable locations
     * 
     * @return Local[]
     */
    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT l.*, e.nome AS edificio_nome, e.cor AS edificio_cor 
             FROM locais l 
             LEFT JOIN edificios e ON l.edificio_id = e.id 
             WHERE l.is_navigable = 1 
             ORDER BY l.nome'
        );

        $locais = [];
        while ($row = $stmt->fetch()) {
            $locais[] = $row;
        }
        return $locais;
    }

    /**
     * Fetch only locations that are NOT waypoints (user-facing locations)
     * 
     * @return array
     */
    public function findSelectableLocations(): array
    {
        $stmt = $this->db->query(
            'SELECT l.*, e.nome AS edificio_nome, e.cor AS edificio_cor 
             FROM locais l 
             LEFT JOIN edificios e ON l.edificio_id = e.id 
             WHERE l.is_navigable = 1 AND l.is_waypoint = 0 
             ORDER BY l.nome'
        );

        return $stmt->fetchAll();
    }

    /**
     * Find a location by ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT l.*, e.nome AS edificio_nome, e.cor AS edificio_cor 
             FROM locais l 
             LEFT JOIN edificios e ON l.edificio_id = e.id 
             WHERE l.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Find locations by type
     * 
     * @return array
     */
    public function findByType(string $tipo): array
    {
        $stmt = $this->db->prepare(
            'SELECT l.*, e.nome AS edificio_nome, e.cor AS edificio_cor 
             FROM locais l 
             LEFT JOIN edificios e ON l.edificio_id = e.id 
             WHERE l.tipo = :tipo AND l.is_navigable = 1 
             ORDER BY l.nome'
        );
        $stmt->execute(['tipo' => $tipo]);
        return $stmt->fetchAll();
    }

    /**
     * Search locations by name
     * 
     * @return array
     */
    public function search(string $query): array
    {
        $stmt = $this->db->prepare(
            'SELECT l.*, e.nome AS edificio_nome, e.cor AS edificio_cor 
             FROM locais l 
             LEFT JOIN edificios e ON l.edificio_id = e.id 
             WHERE (l.nome LIKE :query OR l.descricao LIKE :query) 
               AND l.is_navigable = 1 AND l.is_waypoint = 0
             ORDER BY l.nome'
        );
        $stmt->execute(['query' => '%' . $query . '%']);
        return $stmt->fetchAll();
    }
}
