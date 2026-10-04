<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Security;

use CampusTrack\Tests\Support\DatabaseTestCase;

require_once dirname(__DIR__) . "/Support/DatabaseTestCase.php";
require_once dirname(__DIR__, 2) . "/src/php/locais/repositorio.php";

final class LocalInstituicaoTest extends DatabaseTestCase
{
    private const NOME_TESTE = "Local CT08 Seguranca";

    protected function setUp(): void
    {
        parent::setUp();

        $this->conexao->begin_transaction();
        $this->removerLocalDeTeste();
    }

    protected function tearDown(): void
    {
        if (isset($this->conexao)) {
            $this->conexao->rollback();
        }

        parent::tearDown();
    }

    public function testCT08RestringirEdicaoAInstituicaoDoGerente(): void
    {
        $localOriginal = [
            "id_instituicao" => 2,
            "tipo_escola" => "Tecnologia",
            "tipo" => "Laboratório",
            "nome" => self::NOME_TESTE,
            "capacidade" => 30,
            "longitude" => "-49.25",
            "latitude" => "-25.45",
        ];

        $idLocal = \local_inserir($this->conexao, $localOriginal);

        self::assertGreaterThan(0, $idLocal);

        $dadosAlterados = [
            "id_instituicao" => 2,
            "tipo_escola" => "Tecnologia",
            "tipo" => "Sala",
            "nome" => "Local CT08 Alterado",
            "capacidade" => 99,
            "longitude" => "-50.00",
            "latitude" => "-26.00",
        ];

        // Gerente pertence à instituição 1,
        // mas o local pertence à instituição 2.
        \local_atualizar(
            $this->conexao,
            $idLocal,
            1,
            $dadosAlterados
        );

        $resultado = \local_buscar_por_id($this->conexao, $idLocal);

        self::assertIsArray($resultado);
        self::assertCount(1, $resultado);

        $localDepois = $resultado[0];

        self::assertSame(self::NOME_TESTE, $localDepois["nome"]);
        self::assertSame("Laboratório", $localDepois["tipo"]);
        self::assertSame(30, (int) $localDepois["capacidade"]);
        self::assertSame("-49.250000000000000", $localDepois["longitude"]);
        self::assertSame("-25.450000000000000", $localDepois["latitude"]);
        self::assertSame(2, (int) $localDepois["id_instituicao"]);
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