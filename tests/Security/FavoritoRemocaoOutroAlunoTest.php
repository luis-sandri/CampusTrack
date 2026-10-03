<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Security;

use CampusTrack\Tests\Support\TransactionalDatabaseTestCase;

require_once dirname(__DIR__, 2) . "/src/php/favoritos/repositorio.php";

final class FavoritoRemocaoOutroAlunoTest extends TransactionalDatabaseTestCase
{
    private const ID_ALUNO_A = 3;
    private const ID_ALUNO_B = 5;
    private const ID_LOCAL = 1;

    public function testCt40NaoRemoveFavoritoDeOutroAluno(): void
    {
        self::assertTrue(
            \favorito_existe($this->conexao, self::ID_ALUNO_B, self::ID_LOCAL)
        );
        self::assertFalse(
            \favorito_existe($this->conexao, self::ID_ALUNO_A, self::ID_LOCAL)
        );

        $removido = \favorito_remover(
            $this->conexao,
            self::ID_ALUNO_A,
            self::ID_LOCAL
        );

        self::assertFalse($removido);

        $stmt = $this->conexao->prepare(
            "SELECT COUNT(*) FROM Favorito WHERE id_aluno = ? AND id_local = ?"
        );
        $idAluno = self::ID_ALUNO_B;
        $idLocal = self::ID_LOCAL;
        $stmt->bind_param("ii", $idAluno, $idLocal);
        $stmt->execute();
        $stmt->bind_result($quantidade);
        $stmt->fetch();
        $stmt->close();

        self::assertSame(1, (int) $quantidade);
    }
}