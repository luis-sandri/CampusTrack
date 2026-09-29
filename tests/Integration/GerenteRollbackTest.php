<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Integration;

use CampusTrack\Tests\Support\DatabaseTestCase;

require_once dirname(__DIR__) . "/Support/DatabaseTestCase.php";
require_once dirname(__DIR__, 2) . "/src/php/gerentes/repositorio.php";

final class GerenteRollbackTest extends DatabaseTestCase
{
    private const EMAIL_TESTE = "ct23.rollback@campustrack.test";

    protected function setUp(): void
    {
        parent::setUp();
        $this->removerUsuarioDeTeste();
    }

    protected function tearDown(): void
    {
        if (isset($this->conexao)) {
            $this->removerUsuarioDeTeste();
        }

        parent::tearDown();
    }

    public function testCt23ReverteUsuarioQuandoCadastroDeGerenteFalha(): void
    {
        $gerente = [
            "nome" => "Gerente Rollback CT23",
            "email" => self::EMAIL_TESTE,
            "senha" => "Abcdef1!",
            "id_instituicao" => 999999,
            "escola" => "Tecnologia",
        ];

        $idUsuario = \gerente_inserir($this->conexao, $gerente);

        self::assertSame(0, $idUsuario);
        self::assertSame(0, $this->contarUsuariosDeTeste());
        self::assertSame(0, $this->contarGerentesDeTeste());
    }

    private function contarUsuariosDeTeste(): int
    {
        $stmt = $this->conexao->prepare("SELECT COUNT(*) FROM Usuario WHERE email = ?");
        $email = self::EMAIL_TESTE;
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($quantidade);
        $stmt->fetch();
        $stmt->close();

        return (int) $quantidade;
    }

    private function contarGerentesDeTeste(): int
    {
        $stmt = $this->conexao->prepare(
            "SELECT COUNT(*)
             FROM Gerente_Locais g
             INNER JOIN Usuario u ON u.id_usuario = g.id_gerente
             WHERE u.email = ?"
        );
        $email = self::EMAIL_TESTE;
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($quantidade);
        $stmt->fetch();
        $stmt->close();

        return (int) $quantidade;
    }

    private function removerUsuarioDeTeste(): void
    {
        $stmt = $this->conexao->prepare("DELETE FROM Usuario WHERE email = ?");
        $email = self::EMAIL_TESTE;
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->close();
    }
}
