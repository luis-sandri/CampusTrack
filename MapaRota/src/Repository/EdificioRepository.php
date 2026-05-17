<?php

namespace CampusTrack\Repository;

use CampusTrack\Core\Database;
use PDO;

/**
 * Edificio Repository
 * 
 * Data access layer for building-related database operations.
 */
class EdificioRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Fetch all buildings
     * 
     * @return array
     */
    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT e.*, 
                    (SELECT COUNT(*) FROM locais l WHERE l.edificio_id = e.id) AS total_locais,
                    (SELECT COUNT(*) FROM andares a WHERE a.edificio_id = e.id) AS total_andares
             FROM edificios e 
             ORDER BY e.nome'
        );
        return $stmt->fetchAll();
    }

    /**
     * Find building by ID with its floors
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM edificios WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $edificio = $stmt->fetch();

        if (!$edificio) {
            return null;
        }

        // Get floors for this building
        $stmtAndares = $this->db->prepare(
            'SELECT * FROM andares WHERE edificio_id = :id ORDER BY numero'
        );
        $stmtAndares->execute(['id' => $id]);
        $edificio['andares'] = $stmtAndares->fetchAll();

        // Get locations in this building
        $stmtLocais = $this->db->prepare(
            'SELECT * FROM locais WHERE edificio_id = :id ORDER BY nome'
        );
        $stmtLocais->execute(['id' => $id]);
        $edificio['locais'] = $stmtLocais->fetchAll();

        return $edificio;
    }
}
