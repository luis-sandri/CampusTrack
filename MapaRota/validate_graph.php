<?php
require_once 'autoload.php';

use CampusTrack\Repository\CaminhoRepository;
use CampusTrack\Repository\LocalRepository;

$caminhoRepo = new CaminhoRepository();
$localRepo = new LocalRepository();

$locais = $localRepo->findAll();
$grafo = $caminhoRepo->buildAdjacencyList();

if (empty($locais)) {
    die("Nenhum local no banco.\n");
}

// 1. Check Connected Components using BFS
$visited = [];
$components = 0;

foreach ($locais as $local) {
    $id = $local['id'];
    if (!isset($visited[$id])) {
        $components++;
        // BFS
        $queue = [$id];
        $visited[$id] = true;
        
        while (!empty($queue)) {
            $curr = array_shift($queue);
            if (isset($grafo[$curr])) {
                foreach ($grafo[$curr] as $edge) {
                    $neighbor = $edge['node'];
                    if (!isset($visited[$neighbor])) {
                        $visited[$neighbor] = true;
                        $queue[] = $neighbor;
                    }
                }
            }
        }
    }
}

echo "=== Relatório de Conectividade do Grafo ===\n";
echo "Total de nós: " . count($locais) . "\n";
echo "Total de arestas (direcionadas): " . array_reduce($grafo, fn($carry, $edges) => $carry + count($edges), 0) . "\n";
echo "Componentes conexas: $components\n\n";

if ($components === 1) {
    echo "✅ SUCESSO: O grafo é 100% conexo. Qualquer ponto alcança qualquer ponto.\n";
} else {
    echo "❌ AVISO: O grafo possui nós isolados ou ilhas desconexas.\n";
}
