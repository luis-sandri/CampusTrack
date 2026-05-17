<?php

namespace CampusTrack\Repository;

use CampusTrack\Core\Database;
use PDO;

/**
 * Evento Repository
 * 
 * Data access layer for event-related database operations.
 */
class EventoRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Fetch all active events with location data
     * 
     * @return array
     */
    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT e.*, l.nome AS local_nome, l.x AS local_x, l.y AS local_y
             FROM eventos e
             JOIN locais l ON e.local_id = l.id
             WHERE e.ativo = 1
             ORDER BY e.data_inicio ASC'
        );
        return $stmt->fetchAll();
    }

    /**
     * Fetch upcoming events (from now onwards)
     * 
     * @return array
     */
    public function findUpcoming(int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            'SELECT e.*, l.nome AS local_nome, l.x AS local_x, l.y AS local_y
             FROM eventos e
             JOIN locais l ON e.local_id = l.id
             WHERE e.ativo = 1 AND e.data_inicio >= NOW()
             ORDER BY e.data_inicio ASC
             LIMIT :limit'
        );
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Find events at a specific location
     * 
     * @return array
     */
    public function findByLocation(int $localId): array
    {
        $stmt = $this->db->prepare(
            'SELECT e.*, l.nome AS local_nome
             FROM eventos e
             JOIN locais l ON e.local_id = l.id
             WHERE e.local_id = :local_id AND e.ativo = 1
             ORDER BY e.data_inicio ASC'
        );
        $stmt->execute(['local_id' => $localId]);
        return $stmt->fetchAll();
    }

    /**
     * Find event by ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT e.*, l.nome AS local_nome, l.x AS local_x, l.y AS local_y
             FROM eventos e
             JOIN locais l ON e.local_id = l.id
             WHERE e.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
