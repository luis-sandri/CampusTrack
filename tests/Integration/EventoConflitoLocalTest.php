<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Integration;

use CampusTrack\Tests\Support\TransactionalDatabaseTestCase;

require_once dirname(__DIR__) . "/Support/TransactionalDatabaseTestCase.php";
require_once dirname(__DIR__, 2) . "/src/php/eventos/repositorio.php";

final class EventoConflitoLocalTest extends TransactionalDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $stmt = $this->conexao->prepare(
            "INSERT INTO Evento (nome, data, status, id_local, id_organizacao, id_organizador)
             VALUES (?, ?, 'ativo', ?, ?, ?)"
        );
        $nome = "Evento Conflito CT31";
        $data = "2026-10-07 14:00:00";
        $idLocal = 1;
        $idOrganizacao = 1;
        $idOrganizador = 1;
        $stmt->bind_param("ssiii", $nome, $data, $idLocal, $idOrganizacao, $idOrganizador);
        $stmt->execute();
        $stmt->close();
    }

    public function testCt31DetectaConflitoNaMesmaLocalidadeAteLimiteDeDuasHoras(): void
    {
        self::assertTrue(\evento_tem_conflito_local($this->conexao, 1, "2026-10-07 15:59:00"));
        self::assertFalse(\evento_tem_conflito_local($this->conexao, 1, "2026-10-07 16:00:00"));
        self::assertFalse(\evento_tem_conflito_local($this->conexao, 2, "2026-10-07 15:59:00"));
    }
}
