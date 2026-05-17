<?php

namespace CampusTrack\Service;

use CampusTrack\Repository\CaminhoRepository;
use CampusTrack\Repository\LocalRepository;

/**
 * Routing Service
 * 
 * Implements Dijkstra's algorithm on the server side for optimal pathfinding.
 * Builds the graph from the database and computes shortest paths.
 */
class RoutingService
{
    private CaminhoRepository $caminhoRepo;
    private LocalRepository $localRepo;

    public function __construct()
    {
        $this->caminhoRepo = new CaminhoRepository();
        $this->localRepo = new LocalRepository();
    }

    /**
     * Find the shortest path between two locations using Dijkstra's algorithm
     * 
     * @param int  $origemId        Origin location ID
     * @param int  $destinoId       Destination location ID
     * @param bool $acessivelOnly   Only use accessible paths
     * @return array Route result with path, distance, and step details
     */
    public function findRoute(int $origemId, int $destinoId, bool $acessivelOnly = false): array
    {
        // Validate locations exist
        $origem = $this->localRepo->findById($origemId);
        $destino = $this->localRepo->findById($destinoId);

        if (!$origem) {
            return ['error' => 'Origem não encontrada', 'code' => 404];
        }
        if (!$destino) {
            return ['error' => 'Destino não encontrado', 'code' => 404];
        }
        if ($origemId === $destinoId) {
            return ['error' => 'Origem e destino são iguais', 'code' => 400];
        }

        // Build adjacency list from database
        $graph = $this->caminhoRepo->buildAdjacencyList($acessivelOnly);

        // Run Dijkstra
        $result = $this->dijkstra($graph, $origemId, $destinoId);

        if ($result === null) {
            return ['error' => 'Nenhuma rota encontrada entre os pontos', 'code' => 404];
        }

        // Enrich path with location details
        $steps = $this->enrichPath($result['path']);

        return [
            'origem'          => $origem,
            'destino'         => $destino,
            'distancia_total' => round($result['distance'], 2),
            'num_passos'      => count($result['path']),
            'caminho'         => $result['path'],
            'passos'          => $steps,
        ];
    }

    /**
     * Dijkstra's shortest path algorithm
     * 
     * @param array $graph      Adjacency list [nodeId => [{node, weight, type}, ...]]
     * @param int   $start      Start node ID
     * @param int   $end        End node ID
     * @return array|null       ['path' => [...], 'distance' => float] or null if no path
     */
    private function dijkstra(array $graph, int $start, int $end): ?array
    {
        $dist = [];
        $prev = [];
        $visited = [];

        // Initialize all nodes with infinite distance
        foreach (array_keys($graph) as $nodeId) {
            $dist[$nodeId] = PHP_FLOAT_MAX;
            $prev[$nodeId] = null;
        }

        // Ensure start and end are in the graph
        if (!isset($graph[$start]) || !isset($graph[$end])) {
            return null;
        }

        $dist[$start] = 0;

        $pq = new \SplPriorityQueue();
        $pq->setExtractFlags(\SplPriorityQueue::EXTR_DATA);
        $pq->insert($start, 0);

        while (!$pq->isEmpty()) {
            $current = $pq->extract();

            // Reached destination
            if ($current === $end) {
                break;
            }

            // Node might have been visited already with a shorter path
            if (isset($visited[$current])) {
                continue;
            }
            $visited[$current] = true;

            // Relax neighbors
            if (!isset($graph[$current])) {
                continue;
            }

            foreach ($graph[$current] as $edge) {
                $neighbor = $edge['node'];
                $weight   = $edge['weight'];
                $alt      = $dist[$current] + $weight;

                if ($alt < $dist[$neighbor]) {
                    $dist[$neighbor] = $alt;
                    $prev[$neighbor] = $current;
                    // Negative weight because SplPriorityQueue is max-heap by default
                    $pq->insert($neighbor, -$alt); 
                }
            }
        }

        // Reconstruct path
        if (!isset($prev[$end]) && $start !== $end) {
            return null; // No path found
        }

        $path = [];
        $current = $end;

        while ($current !== null) {
            array_unshift($path, $current);
            $current = $prev[$current] ?? null;
        }

        // Verify path starts at origin
        if ($path[0] !== $start) {
            return null;
        }

        return [
            'path'     => $path,
            'distance' => $dist[$end],
        ];
    }

    /**
     * Enrich a path of IDs with full location details
     */
    private function enrichPath(array $pathIds): array
    {
        $steps = [];
        foreach ($pathIds as $index => $id) {
            $local = $this->localRepo->findById($id);
            if ($local) {
                $steps[] = [
                    'order'    => $index + 1,
                    'local_id' => $local['id'],
                    'nome'     => $local['nome'],
                    'tipo'     => $local['tipo'],
                    'x'        => (float) $local['x'],
                    'y'        => (float) $local['y'],
                    'icone'    => $local['icone'] ?? 'pin',
                ];
            }
        }
        return $steps;
    }
}
