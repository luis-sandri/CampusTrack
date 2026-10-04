<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Integration;

use CampusTrack\Tests\Support\DatabaseTestCase;

require_once dirname(__DIR__) . "/Support/DatabaseTestCase.php";
require_once dirname(__DIR__, 2) . "/src/php/locais/repositorio.php";

final class LocalPersistenciaTest extends DatabaseTestCase
{
    private const NOME_TESTE = "Local CT07 Persistencia";

    protected function setUp(): void
    {
        parent::setUp();
        $this->removerLocalDeTeste();
    }

    protected function tearDown(): void
    {
        if (isset($this->conexao)) {
            $this->removerLocalDeTeste();
        }

        parent::tearDown();
    }

    public function testCT07PersistirLocalERecuperarDados(): void
    {
        $local = [
            "id_instituicao" => 1,
            "tipo_escola" => "Tecnologia",
            "tipo" => "Laboratório",
            "nome" => self::NOME_TESTE,
            "capacidade" => 30,
            "longitude" => "-49.25",
            "latitude" => "-25.45",
        ];

        $idLocal = \local_inserir($this->conexao, $local);

        self::assertGreaterThan(0, $idLocal);

        $resultado = \local_buscar_por_id($this->conexao, $idLocal);

        self::assertIsArray($resultado);
        self::assertCount(1, $resultado);

        $localRecuperado = $resultado[0];

        self::assertSame(self::NOME_TESTE, $localRecuperado["nome"]);
        self::assertSame("Laboratório", $localRecuperado["tipo"]);
        self::assertSame(30, (int) $localRecuperado["capacidade"]);
        self::assertSame("-49.250000000000000", $localRecuperado["longitude"]);
        self::assertSame("-25.450000000000000", $localRecuperado["latitude"]);
    }

    private function removerLocalDeTeste(): void
    {
        $stmt = $this->conexao->prepare(
            "DELETE FROM Locais WHERE nome = ?"
        );

        $nome = self::NOME_TESTE;

        $stmt->bind_param("s", $nome);
        $stmt->execute();
        $stmt->close();
    }
}