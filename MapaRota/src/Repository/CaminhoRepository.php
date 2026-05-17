<?php

namespace CampusTrack\Repository;

use CampusTrack\Core\Database;
use PDO;

/**
 * Caminho Repository
 * 
 * Data access layer for path/edge-related database operations.
 */
class CaminhoRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Fetch all paths
     * 
     * @return array
     */
    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT c.*, 
                    lo.nome AS origem_nome, lo.x AS origem_x, lo.y AS origem_y,
                    ld.nome AS destino_nome, ld.x AS destino_x, ld.y AS destino_y
             FROM caminhos c
             JOIN locais lo ON c.origem_id = lo.id
             JOIN locais ld ON c.destino_id = ld.id
             ORDER BY c.origem_id'
        );
        return $stmt->fetchAll();
    }

    /**
     * Build an adjacency list from all paths
     * 
     * Returns an array where each key is a location ID, and the value is
     * an array of [neighbor_id, weight, type] tuples.
     * Bidirectional paths are expanded into both directions.
     * 
     * @param bool $acessivelOnly Only include accessible paths
     * @return array
     */
    public function buildAdjacencyList(bool $acessivelOnly = false): array
    {
        $sql = 'SELECT origem_id, destino_id, peso, tipo, bidirecional, acessivel FROM caminhos';
        
        if ($acessivelOnly) {
            $sql .= ' WHERE acessivel = 1';
        }

        $stmt = $this->db->query($sql);
        $adjacency = [];

        while ($row = $stmt->fetch()) {
            $origemId  = (int) $row['origem_id'];
            $destinoId = (int) $row['destino_id'];
            $peso      = (float) $row['peso'];
            $tipo      = $row['tipo'];

            // Initialize arrays if needed
            if (!isset($adjacency[$origemId])) {
                $adjacency[$origemId] = [];
            }
            if (!isset($adjacency[$destinoId])) {
                $adjacency[$destinoId] = [];
            }

            // Add forward edge
            $adjacency[$origemId][] = [
                'node'   => $destinoId,
                'weight' => $peso,
                'type'   => $tipo,
            ];

            // Add reverse edge if bidirectional
            if ((bool) $row['bidirecional']) {
                $adjacency[$destinoId][] = [
                    'node'   => $origemId,
                    'weight' => $peso,
                    'type'   => $tipo,
                ];
            }
        }

        return $adjacency;
    }

    /**
     * Find paths connected to a specific location
     * 
     * @return array
     */
    public function findByLocation(int $localId): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, 
                    lo.nome AS origem_nome, ld.nome AS destino_nome
             FROM caminhos c
             JOIN locais lo ON c.origem_id = lo.id
             JOIN locais ld ON c.destino_id = ld.id
             WHERE c.origem_id = :id OR (c.destino_id = :id AND c.bidirecional = 1)'
        );
        $stmt->execute(['id' => $localId]);
        return $stmt->fetchAll();
    }
}
