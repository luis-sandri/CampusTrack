<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Integration;

use CampusTrack\Tests\Support\TransactionalDatabaseTestCase;

require_once dirname(__DIR__, 2) . "/src/php/favoritos/repositorio.php";

final class FavoritoRepositorioTest extends TransactionalDatabaseTestCase
{
    private const ID_ALUNO = 3;
    private const ID_LOCAL = 1;

    public function testCt39InsereListaERemoveFavorito(): void
    {
        self::assertFalse(
            \favorito_existe($this->conexao, self::ID_ALUNO, self::ID_LOCAL)
        );

        $idFavorito = \favorito_inserir(
            $this->conexao,
            self::ID_ALUNO,
            self::ID_LOCAL
        );

        self::assertGreaterThan(0, $idFavorito);
        self::assertTrue(
            \favorito_existe($this->conexao, self::ID_ALUNO, self::ID_LOCAL)
        );

        $favoritos = \favorito_listar_por_aluno($this->conexao, self::ID_ALUNO);
        self::assertIsArray($favoritos);
        self::assertContains(
            self::ID_LOCAL,
            array_map(
                static fn (array $favorito): int => (int) $favorito["id_local"],
                $favoritos
            )
        );

        self::assertTrue(
            \favorito_remover($this->conexao, self::ID_ALUNO, self::ID_LOCAL)
        );
        self::assertFalse(
            \favorito_existe($this->conexao, self::ID_ALUNO, self::ID_LOCAL)
        );

        $favoritos = \favorito_listar_por_aluno($this->conexao, self::ID_ALUNO);
        self::assertIsArray($favoritos);
        self::assertNotContains(
            self::ID_LOCAL,
            array_map(
                static fn (array $favorito): int => (int) $favorito["id_local"],
                $favoritos
            )
        );
    }
}