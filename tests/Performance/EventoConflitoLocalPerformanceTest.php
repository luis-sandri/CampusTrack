<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Performance;

use CampusTrack\Tests\Support\DatabaseTestCase;

require_once dirname(__DIR__) . "/Support/DatabaseTestCase.php";
require_once dirname(__DIR__, 2) . "/src/php/eventos/repositorio.php";

final class EventoConflitoLocalPerformanceTest extends DatabaseTestCase
{
    private const EVENTOS_ESPERADOS = 10000;
    private const LOCAIS_ESPERADOS = 100;
    private const AQUECIMENTOS = 5;
    private const MEDICOES = 30;
    private const CONSULTA_SEM_CONFLITO = "2030-01-01 00:00:00";

    public function testCt32MedeConsultaDeConflitoEmPerfilDeDezMilEventos(): void
    {
        $totalEventos = (int) $this->conexao->query("SELECT COUNT(*) FROM Evento")->fetch_row()[0];
        $totalLocais = (int) $this->conexao->query("SELECT COUNT(*) FROM Locais")->fetch_row()[0];

        self::assertSame(self::EVENTOS_ESPERADOS, $totalEventos);
        self::assertSame(self::LOCAIS_ESPERADOS, $totalLocais);
        self::assertSame(true, \evento_tem_conflito_local($this->conexao, 1, "2026-01-01 00:01:00"));
        self::assertSame(false, \evento_tem_conflito_local($this->conexao, 1, "2025-12-31 22:01:00"));
        self::assertSame(false, \evento_tem_conflito_local($this->conexao, 100, "2025-12-31 23:40:00"));
        self::assertSame(false, \evento_tem_conflito_local($this->conexao, 2, self::CONSULTA_SEM_CONFLITO));
        self::assertSame(false, \evento_tem_conflito_local($this->conexao, 100, self::CONSULTA_SEM_CONFLITO));

        for ($indice = 0; $indice < self::AQUECIMENTOS; $indice++) {
            self::assertSame(
                false,
                \evento_tem_conflito_local($this->conexao, 100, self::CONSULTA_SEM_CONFLITO)
            );
        }

        $duracoesMs = [];

        for ($indice = 0; $indice < self::MEDICOES; $indice++) {
            $inicio = hrtime(true);
            $resultado = \evento_tem_conflito_local(
                $this->conexao,
                100,
                self::CONSULTA_SEM_CONFLITO
            );
            $duracaoNs = hrtime(true) - $inicio;
            self::assertSame(false, $resultado);
            $duracoesMs[] = $duracaoNs / 1_000_000;
        }

        sort($duracoesMs, SORT_NUMERIC);
        $medianaMs = ($duracoesMs[14] + $duracoesMs[15]) / 2;
        $p95Ms = $duracoesMs[(int) ceil(self::MEDICOES * 0.95) - 1];
        $maximoMs = $duracoesMs[self::MEDICOES - 1];

        $explain = $this->conexao->query(
            "EXPLAIN SELECT id_evento
             FROM Evento
             WHERE id_local = 100
               AND status = 'ativo'
               AND ABS(TIMESTAMPDIFF(MINUTE, data, '2030-01-01 00:00:00')) < 120
             LIMIT 1"
        )->fetch_assoc();
        $versao = $this->conexao->query("SELECT VERSION()")->fetch_row()[0];

        fwrite(STDERR, "CT32_RESULT " . json_encode([
            "database" => "campustrack_test",
            "server_version" => $versao,
            "events" => $totalEventos,
            "locations" => $totalLocais,
            "warmups" => self::AQUECIMENTOS,
            "measurements" => self::MEDICOES,
            "query_case" => "sem_conflito_local_100_2030-01-01",
            "median_ms" => round($medianaMs, 3),
            "p95_ms" => round($p95Ms, 3),
            "maximum_ms" => round($maximoMs, 3),
            "explain" => [
                "type" => $explain["type"] ?? null,
                "possible_keys" => $explain["possible_keys"] ?? null,
                "key" => $explain["key"] ?? null,
                "rows" => isset($explain["rows"]) ? (int) $explain["rows"] : null,
                "extra" => $explain["Extra"] ?? null,
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
    }
}
